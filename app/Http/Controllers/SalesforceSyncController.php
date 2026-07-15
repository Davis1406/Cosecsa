<?php

namespace App\Http\Controllers;

use App\Services\SalesforceCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesforceSyncController extends Controller
{
    // The active application intake window. Bump this at the start of each
    // new cycle — everything else (filters, defaults) reads from here.
    const DEFAULT_APPLICATION_YEAR = 2027;

    protected SalesforceCrmService $salesforce;

    public function __construct(SalesforceCrmService $salesforce)
    {
        $this->salesforce = $salesforce;
    }

    /**
     * List + visual report of Salesforce applications from the local cache table.
     * Defaults to the current application-year window (by Date_of_Application,
     * not Exam_Year__c) unless the admin picks a different year or "All years".
     */
    public function index(Request $request)
    {
        $search      = trim((string) $request->input('q'));
        $stage       = $request->input('stage');
        $programme   = $request->input('programme');
        $country     = $request->input('country');
        $level       = $request->input('level');
        $received    = $request->input('received'); // '1' | '0' | null
        $approved    = $request->input('approved');  // '1' | '0' | null

        // Application year defaults to the active window on first load; the
        // filter form always posts a value once touched, "all" opts out.
        $appYear = $request->input('application_year', (string) self::DEFAULT_APPLICATION_YEAR);

        $base = DB::table('salesforce_applications');

        $applyFilters = function ($query) use ($search, $stage, $programme, $country, $level, $received, $approved, $appYear) {
            if ($search) {
                $like = "%{$search}%";
                $query->where(function ($w) use ($like) {
                    $w->where('applicant_name', 'like', $like)
                      ->orWhere('applicant_email', 'like', $like)
                      ->orWhere('name', 'like', $like)
                      ->orWhere('entry_number', 'like', $like);
                });
            }
            if ($stage)     $query->where('application_stage', $stage);
            if ($programme) $query->where('programme_name', $programme);
            if ($country)   $query->where('country', $country);
            if ($level)     $query->where('application_level', $level);
            if ($received !== null && $received !== '') $query->where('application_received', (bool) $received);
            if ($approved !== null && $approved !== '') $query->where('application_approved', (bool) $approved);
            if ($appYear && $appYear !== 'all') {
                [$start, $end] = self::intakeWindow((int) $appYear);
                $query->whereBetween('date_of_application', [$start, $end]);
            }
            return $query;
        };

        $applications = $applyFilters(clone $base)
            ->orderByDesc('date_of_application')
            ->get();

        // ── Filter option lists — always drawn from the full table, not the
        //    filtered result, so options don't disappear as filters narrow. ──
        $stages     = DB::table('salesforce_applications')->whereNotNull('application_stage')->distinct()->orderBy('application_stage')->pluck('application_stage');
        $programmes = DB::table('salesforce_applications')->whereNotNull('programme_name')->distinct()->orderBy('programme_name')->pluck('programme_name');
        $countries  = DB::table('salesforce_applications')->whereNotNull('country')->distinct()->orderBy('country')->pluck('country');
        $levels     = DB::table('salesforce_applications')->whereNotNull('application_level')->distinct()->orderBy('application_level')->pluck('application_level');

        // Intake year runs 1 Jul (Y-1) → 30 Jun (Y) — e.g. "2026" applications
        // started July 2025. A date's intake year is therefore its calendar
        // year + 1 for Jul-Dec, or unchanged for Jan-Jun.
        $years = DB::table('salesforce_applications')
            ->whereNotNull('date_of_application')
            ->selectRaw('DISTINCT (CASE WHEN MONTH(date_of_application) >= 7 THEN YEAR(date_of_application) + 1 ELSE YEAR(date_of_application) END) as intake_yr')
            ->orderByDesc('intake_yr')
            ->pluck('intake_yr');
        if (! $years->contains(self::DEFAULT_APPLICATION_YEAR)) {
            $years = $years->push(self::DEFAULT_APPLICATION_YEAR)->sortByDesc(fn ($y) => $y)->values();
        }

        $total    = $applications->count();
        $lastSync = DB::table('salesforce_sync_log')->where('status', 'completed')->orderByDesc('synced_at')->first();

        // ── Visual report aggregates, computed over the filtered set ──
        $stageCounts = $applications->countBy('application_stage')->sortDesc();

        $programmeCounts = $applications->countBy('programme_name')->sortDesc()->take(8);

        $countryCounts = $applications->countBy('country')->sortDesc()->take(10);

        // Trend: by month (in intake-window order, Jul→Jun) if a single year
        // is selected, by intake year if "All years".
        if ($appYear && $appYear !== 'all') {
            $fiscalMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            $trendLabels = collect($fiscalMonths)->map(fn ($m) => \Carbon\Carbon::create()->month($m)->format('M'));
            $byMonth = $applications->groupBy(fn ($a) => $a->date_of_application ? (int) \Carbon\Carbon::parse($a->date_of_application)->format('n') : 0);
            $trendCounts = collect($fiscalMonths)->map(fn ($m) => $byMonth->get($m, collect())->count());
        } else {
            $byYear = $applications->groupBy(fn ($a) => $a->date_of_application ? self::intakeYearOf($a->date_of_application) : 'Unknown');
            $trendLabels = $byYear->keys()->sort()->values();
            $trendCounts = $trendLabels->map(fn ($y) => $byYear->get($y)->count());
        }

        // Application_Received__c is a manual checkbox that lags the real stage
        // (e.g. a brand-new application can already show stage "Application
        // Received" with the checkbox still unticked) — every synced record has
        // *some* stage, so "received" is better read as "still active in the
        // pipeline", i.e. not Rejected or Withdrawn.
        $rejectedCount  = $applications->whereIn('application_stage', ['Rejected', 'Withdrawn by applicant'])->count();
        $receivedCount  = $total - $rejectedCount;
        $approvedCount  = $applications->where('application_stage', 'Complete')->count();

        return view('admin.salesforce.index', compact(
            'applications', 'stages', 'programmes', 'countries', 'levels', 'years',
            'search', 'stage', 'programme', 'country', 'level', 'received', 'approved', 'appYear',
            'total', 'lastSync', 'stageCounts', 'programmeCounts', 'countryCounts',
            'trendLabels', 'trendCounts', 'receivedCount', 'approvedCount', 'rejectedCount'
        ));
    }

    /**
     * View a single synced Salesforce application in full detail.
     */
    public function show($id)
    {
        $application = DB::table('salesforce_applications')->find($id);
        if (! $application) {
            return redirect('admin/salesforce')->with('error', 'Application not found');
        }

        $intakeYear = $application->date_of_application ? self::intakeYearOf($application->date_of_application) : null;

        // If this application already produced a trainee record, link to it.
        $trainee = $application->trainee_id
            ? DB::table('trainees')->find($application->trainee_id)
            : ($application->pen ? DB::table('trainees')->where('entry_number', $application->pen)->first() : null);

        return view('admin.salesforce.show', compact('application', 'intakeYear', 'trainee'));
    }

    /**
     * The [start, end] SQL date bounds for intake year $y: 1 Jul (y-1) → 30 Jun (y).
     */
    protected static function intakeWindow(int $y): array
    {
        return [($y - 1) . '-07-01', $y . '-06-30'];
    }

    /**
     * Which intake year a given date falls into.
     */
    protected static function intakeYearOf(string $date): int
    {
        $d = \Carbon\Carbon::parse($date);
        return $d->month >= 7 ? $d->year + 1 : $d->year;
    }

    /**
     * Salesforce's Program_Entry_Number__c comes as "MW2023-73"; the MIS
     * convention used everywhere else (fellows/trainees/candidates) is
     * "MW/2023/73". Normalize so PEN matching works across tables.
     */
    protected static function normalizePen(?string $raw): ?string
    {
        if (! $raw) return null;
        if (preg_match('/^([A-Za-z]{2,3})(\d{4})-(\d+)$/', trim($raw), $m)) {
            return strtoupper($m[1]) . '/' . $m[2] . '/' . $m[3];
        }
        return $raw;
    }

    /**
     * Pull the latest applications from Salesforce into the local cache table.
     * Incremental by default (only records modified since the last successful sync);
     * pass ?full=1 to re-pull everything.
     */
    public function sync(Request $request)
    {
        $logId = DB::table('salesforce_sync_log')->insertGetId([
            'status'     => 'running',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $modifiedSince = null;
            if (! $request->boolean('full')) {
                $last = DB::table('salesforce_sync_log')
                    ->where('status', 'completed')
                    ->orderByDesc('synced_at')
                    ->first();
                if ($last && $last->synced_at) {
                    $modifiedSince = \Carbon\Carbon::parse($last->synced_at)->toIso8601String();
                }
            }

            $records = $this->salesforce->getApplications($modifiedSince);

            foreach ($records as $r) {
                $invoice = $r['Invoices__r']['records'][0] ?? null;

                DB::table('salesforce_applications')->updateOrInsert(
                    ['sf_id' => $r['Id']],
                    [
                        'name'                  => $r['Name'] ?? null,
                        'applicant_name'        => $r['Applicant__r']['Name'] ?? null,
                        'applicant_email'       => $r['Applicant__r']['Email__c'] ?? null,
                        'applicant_phone'       => $r['Applicant__r']['Phone_Number__c'] ?? null,
                        'applicant_gender'      => $r['Applicant__r']['Gender__c'] ?? null,
                        'application_level'     => $r['Application_Level__c'] ?? null,
                        'application_stage'     => $r['Application_Stage__c'] ?? null,
                        'programme_name'        => $r['COSECSA_Programme_applied_for__r']['Name'] ?? null,
                        'hospital_name'         => $r['Base_Hospital__r']['Name'] ?? null,
                        'country'               => $r['Country__c'] ?? null,
                        'exam_year'             => $r['Exam_Year__c'] ?? null,
                        'date_of_application'   => $r['Date_of_Application__c'] ?? null,
                        'entry_number'          => $r['Entry_Number__c'] ?? null,
                        'pen'                   => self::normalizePen($r['Program_Entry_Number__c'] ?? null),
                        'entry_invoice_number'  => $invoice['Name'] ?? null,
                        'entry_invoice_amount'  => $invoice['Invoiced_Amount__c'] ?? null,
                        'entry_payment_amount'  => $invoice['Payment_Amount__c'] ?? null,
                        'entry_payment_date'    => $invoice['Payment_Date__c'] ?? null,
                        'entry_payment_method'  => $invoice['Payment_Method__c'] ?? null,
                        'entry_invoice_status'  => $invoice['Status__c'] ?? null,
                        'application_received'  => (bool) ($r['Application_Received__c'] ?? false),
                        'application_approved'  => (bool) ($r['Application_Approved__c'] ?? false),
                        'sf_created_at'         => isset($r['CreatedDate']) ? \Carbon\Carbon::parse($r['CreatedDate']) : null,
                        'sf_modified_at'        => isset($r['LastModifiedDate']) ? \Carbon\Carbon::parse($r['LastModifiedDate']) : null,
                        'synced_at'             => now(),
                        'updated_at'            => now(),
                    ]
                );
            }

            DB::table('salesforce_sync_log')->where('id', $logId)->update([
                'status'         => 'completed',
                'records_synced' => count($records),
                'synced_at'      => now(),
                'updated_at'     => now(),
            ]);

            return redirect('admin/salesforce')->with('success', count($records) . ' application(s) synced from Salesforce');

        } catch (\Exception $e) {
            DB::table('salesforce_sync_log')->where('id', $logId)->update([
                'status'     => 'failed',
                'error'      => $e->getMessage(),
                'updated_at' => now(),
            ]);

            return redirect('admin/salesforce')->with('error', 'Salesforce sync failed: ' . $e->getMessage());
        }
    }

    // Country name aliases between Salesforce's picklist labels and our countries table.
    protected const COUNTRY_ALIASES = [
        'tanzania, united republic of' => 'tanzania',
        'united republic of tanzania'  => 'tanzania',
        'congo, the democratic republic of the' => 'drc',
    ];

    /**
     * Resolve every "Complete" application in the current intake window
     * (DEFAULT_APPLICATION_YEAR) that hasn't produced a trainee yet, without
     * writing anything — used by both the preview page and (for the final
     * resolved set) the apply action.
     *
     * PEN rule: a brand-new entrant gets the next sequential number for their
     * country + intake year ("TZ/2027/01", "TZ/2027/02", ...). Someone who
     * already exists elsewhere in the system (matched by email against
     * trainees/candidates/fellows — e.g. an MCS graduate now applying for
     * FCS) keeps their original PEN instead of getting a new one. A true
     * duplicate — same PEN *and* same programme already in trainees — is
     * skipped outright.
     */
    protected function resolveTraineeCandidates(bool $allYears = false): array
    {
        $programmesByName = DB::table('programmes')->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);
        $countriesByName = DB::table('countries')->pluck('id', 'country_name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);
        $countryCodes = DB::table('countries')->pluck('country_code', 'id');
        $hospitalsByName = DB::table('hospitals')->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);

        // Every known PEN this person could already hold, keyed by lowercased email.
        $penByEmail = [];
        foreach (DB::table('trainees')->whereNotNull('personal_email')->get(['personal_email', 'entry_number']) as $t) {
            $penByEmail[strtolower($t->personal_email)] ??= $t->entry_number;
        }
        foreach (DB::table('candidates')->whereNotNull('personal_email')->get(['personal_email', 'entry_number']) as $c) {
            $penByEmail[strtolower($c->personal_email)] ??= $c->entry_number;
        }
        foreach (DB::table('fellows')->whereNotNull('personal_email')->get(['personal_email', 'candidate_number']) as $f) {
            $penByEmail[strtolower($f->personal_email)] ??= $f->candidate_number;
        }

        $existingTraineePens = DB::table('trainees')->pluck('programme_id', 'entry_number');

        $applications = DB::table('salesforce_applications')
            ->where('application_stage', 'Complete')
            ->whereNull('trainee_id')
            ->get()
            ->filter(fn ($app) => $app->date_of_application && ($allYears || self::intakeYearOf($app->date_of_application) === self::DEFAULT_APPLICATION_YEAR))
            ->values();

        $ready = [];
        $skipped = [];
        $unresolved = [];
        $sequenceCounters = []; // "CC-YYYY" => last used number, so repeated calls in one run keep incrementing

        foreach ($applications as $app) {
            $problems = [];

            $programmeId = $app->programme_name ? ($programmesByName[strtolower(trim($app->programme_name))] ?? null) : null;
            if (! $programmeId) $problems[] = "No programme match for \"{$app->programme_name}\"";

            $countryKey = strtolower(trim($app->country ?? ''));
            $countryKey = self::COUNTRY_ALIASES[$countryKey] ?? $countryKey;
            $countryId = $countryKey ? ($countriesByName[$countryKey] ?? null) : null;
            if (! $countryId) $problems[] = "No country match for \"{$app->country}\"";

            $hospitalId = null;
            if ($app->hospital_name) {
                $hKey = strtolower(trim($app->hospital_name));
                $hospitalId = $hospitalsByName[$hKey] ?? null;
                if (! $hospitalId) {
                    $best = null; $bestPct = 0;
                    foreach ($hospitalsByName->keys() as $name) {
                        similar_text($hKey, $name, $pct);
                        if ($pct > $bestPct) { $bestPct = $pct; $best = $name; }
                    }
                    if ($best !== null && $bestPct >= 88) $hospitalId = $hospitalsByName[$best];
                }
            }
            if (! $hospitalId) $problems[] = "No hospital match for \"{$app->hospital_name}\"";

            $examYear = is_numeric($app->exam_year) ? (int) $app->exam_year : null;
            $examYearSource = 'Salesforce';
            if (! $examYear) {
                $capsule = DB::table('capsule_exam_results')
                    ->where('raw_note', 'like', "%PEN: {$app->pen}%")
                    ->whereNotNull('exam_year')
                    ->orderByDesc('exam_year')
                    ->first();
                if ($capsule) {
                    $examYear = $capsule->exam_year;
                    $examYearSource = 'Capsule';
                }
            }
            if (! $examYear) $problems[] = 'No exam year on Salesforce or in Capsule exam history';

            if ($problems) {
                $unresolved[] = ['app' => $app, 'reason' => implode('; ', $problems)];
                continue;
            }

            // Each application's own intake window, not the hardcoded current
            // one — matters once we're backfilling historical years too.
            $appIntakeYear = self::intakeYearOf($app->date_of_application);

            // ── PEN: reuse if this person already exists anywhere, else mint the next one ──
            $existingPen = $app->applicant_email ? ($penByEmail[strtolower($app->applicant_email)] ?? null) : null;
            $penSource = 'new';

            if ($existingPen) {
                $pen = $existingPen;
                $penSource = 'existing';
            } else {
                $code = $countryCodes[$countryId] ?? null;
                if (! $code) {
                    $unresolved[] = ['app' => $app, 'reason' => 'Country has no country_code to build a PEN from'];
                    continue;
                }
                $seqKey = "{$code}-{$appIntakeYear}";
                if (! isset($sequenceCounters[$seqKey])) {
                    $prefix = "{$code}/{$appIntakeYear}/";
                    $max = $existingTraineePens->keys()
                        ->filter(fn ($pen) => str_starts_with($pen, $prefix))
                        ->map(fn ($pen) => (int) substr($pen, strlen($prefix)))
                        ->max() ?? 0;
                    $sequenceCounters[$seqKey] = $max;
                }
                $sequenceCounters[$seqKey]++;
                $pen = sprintf('%s/%d/%02d', $code, $appIntakeYear, $sequenceCounters[$seqKey]);
            }

            // True duplicate: this exact PEN already has a trainee row for this exact programme.
            if (isset($existingTraineePens[$pen]) && $existingTraineePens[$pen] == $programmeId) {
                $skipped[] = ['app' => $app, 'reason' => "Trainee already exists for {$pen} in this programme", 'pen' => $pen];
                continue;
            }

            $ready[] = [
                'app' => $app,
                'pen' => $pen,
                'pen_source' => $penSource,
                'programme_id' => $programmeId,
                'country_id' => $countryId,
                'hospital_id' => $hospitalId,
                'exam_year' => $examYear,
                'exam_year_source' => $examYearSource,
                'admission_year' => $appIntakeYear,
            ];
        }

        return compact('ready', 'skipped', 'unresolved');
    }

    /**
     * Preview what "Populate Trainees" would do — no writes.
     */
    public function populateTraineesPreview(Request $request)
    {
        $allYears = $request->boolean('all_years');
        ['ready' => $ready, 'skipped' => $skipped, 'unresolved' => $unresolved] = $this->resolveTraineeCandidates($allYears);

        return view('admin.salesforce.populate_trainees', compact('ready', 'skipped', 'unresolved', 'allYears'));
    }

    /**
     * Create trainee records for every resolved "Complete" application.
     */
    public function populateTraineesApply(Request $request)
    {
        $allYears = $request->boolean('all_years');
        ['ready' => $ready] = $this->resolveTraineeCandidates($allYears);

        $created = 0;

        foreach ($ready as $row) {
            $app = $row['app'];

            DB::beginTransaction();
            try {
                $fullName = trim($app->applicant_name ?? $app->name ?? '');
                $parts = preg_split('/\s+/', $fullName);
                $firstname = $parts[0] ?? $fullName;
                $lastname  = count($parts) > 1 ? end($parts) : $firstname;
                $middlename = count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : null;

                $user = null;
                if ($app->applicant_email) {
                    $user = DB::table('users')->where('email', $app->applicant_email)->first();
                }
                if (! $user) {
                    $userId = DB::table('users')->insertGetId([
                        'name'       => $fullName,
                        'email'      => $app->applicant_email,
                        'password'   => bcrypt(\Illuminate\Support\Str::random(16)),
                        'user_type'  => 2,
                        'is_deleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $userId = $user->id;
                }

                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $userId, 'role_type' => 2],
                    ['is_active' => 1, 'updated_at' => now(), 'created_at' => now()]
                );

                $gender = in_array($app->applicant_gender, ['Male', 'Female']) ? $app->applicant_gender : null;

                // Entry fee invoice: prefer the linked Salesforce invoice (real
                // invoice #, amount, payment date/method); fall back to the
                // programme's default entry fee if this application has none.
                $invoiceAmount = $app->entry_invoice_amount
                    ?? DB::table('programmes')->where('id', $row['programme_id'])->value('entry_fee');
                $amountPaid    = $app->entry_payment_amount ?? 0;
                $feePaid       = strtolower($app->entry_invoice_status ?? '') === 'paid' ? 'Yes' : 'No';

                $traineeId = DB::table('trainees')->insertGetId([
                    'entry_number'             => $row['pen'],
                    'user_id'                  => $userId,
                    'admission_letter_status'  => 'Pending',
                    'invitation_letter_status' => 'Pending',
                    'firstname'                => $firstname,
                    'middlename'               => $middlename,
                    'lastname'                 => $lastname,
                    'personal_email'           => $app->applicant_email,
                    'gender'                   => $gender,
                    'programme_id'             => $row['programme_id'],
                    'hospital_id'              => $row['hospital_id'],
                    'country_id'               => $row['country_id'],
                    'exam_year'                => $row['exam_year'],
                    'admission_year'           => $row['admission_year'],
                    'training_year'            => 1,
                    'programme_period'         => 1,
                    'status'                   => 'Active',
                    'invoice_number'           => $app->entry_invoice_number,
                    'invoice_amount'           => $invoiceAmount,
                    'invoice_status'           => $app->entry_invoice_status ?: 'Pending',
                    'fee_paid'                 => $feePaid,
                    'amount_paid'              => $amountPaid,
                    'payment_date'             => $app->entry_payment_date,
                    'mode_of_payment'          => $app->entry_payment_method,
                    'is_promoted'              => '0',
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);

                DB::table('salesforce_applications')->where('id', $app->id)->update(['trainee_id' => $traineeId]);

                DB::commit();
                $created++;
            } catch (\Exception $e) {
                DB::rollBack();
                \Illuminate\Support\Facades\Log::error('populateTraineesApply failed for application ' . $app->id, ['error' => $e->getMessage()]);
            }
        }

        return redirect('admin/salesforce')->with('success', "{$created} trainee(s) created from Complete applications");
    }
}
