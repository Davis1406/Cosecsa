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
                $query->whereYear('date_of_application', $appYear);
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

        $years = DB::table('salesforce_applications')
            ->whereNotNull('date_of_application')
            ->selectRaw('DISTINCT YEAR(date_of_application) as yr')
            ->orderByDesc('yr')
            ->pluck('yr');
        if (! $years->contains(self::DEFAULT_APPLICATION_YEAR)) {
            $years = $years->push(self::DEFAULT_APPLICATION_YEAR)->sortByDesc(fn ($y) => $y)->values();
        }

        $total    = $applications->count();
        $lastSync = DB::table('salesforce_sync_log')->where('status', 'completed')->orderByDesc('synced_at')->first();

        // ── Visual report aggregates, computed over the filtered set ──
        $stageCounts = $applications->countBy('application_stage')->sortDesc();

        $programmeCounts = $applications->countBy('programme_name')->sortDesc()->take(8);

        $countryCounts = $applications->countBy('country')->sortDesc()->take(10);

        // Trend: by month if a single year is selected, by year if "All years".
        if ($appYear && $appYear !== 'all') {
            $trendLabels = collect(range(1, 12))->map(fn ($m) => \Carbon\Carbon::create()->month($m)->format('M'));
            $byMonth = $applications->groupBy(fn ($a) => $a->date_of_application ? (int) \Carbon\Carbon::parse($a->date_of_application)->format('n') : 0);
            $trendCounts = collect(range(1, 12))->map(fn ($m) => $byMonth->get($m, collect())->count());
        } else {
            $byYear = $applications->groupBy(fn ($a) => $a->date_of_application ? \Carbon\Carbon::parse($a->date_of_application)->format('Y') : 'Unknown');
            $trendLabels = $byYear->keys()->sort()->values();
            $trendCounts = $trendLabels->map(fn ($y) => $byYear->get($y)->count());
        }

        $receivedCount = $applications->where('application_received', true)->count();
        $approvedCount = $applications->where('application_approved', true)->count();
        $rejectedCount = $applications->whereIn('application_stage', ['Rejected', 'Withdrawn by applicant'])->count();

        return view('admin.salesforce.index', compact(
            'applications', 'stages', 'programmes', 'countries', 'levels', 'years',
            'search', 'stage', 'programme', 'country', 'level', 'received', 'approved', 'appYear',
            'total', 'lastSync', 'stageCounts', 'programmeCounts', 'countryCounts',
            'trendLabels', 'trendCounts', 'receivedCount', 'approvedCount', 'rejectedCount'
        ));
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
                DB::table('salesforce_applications')->updateOrInsert(
                    ['sf_id' => $r['Id']],
                    [
                        'name'                 => $r['Name'] ?? null,
                        'applicant_name'       => $r['Applicant__r']['Name'] ?? null,
                        'applicant_email'      => $r['Applicant__r']['Email__c'] ?? null,
                        'applicant_phone'      => $r['Applicant__r']['Phone_Number__c'] ?? null,
                        'application_level'    => $r['Application_Level__c'] ?? null,
                        'application_stage'    => $r['Application_Stage__c'] ?? null,
                        'programme_name'       => $r['COSECSA_Programme_applied_for__r']['Name'] ?? null,
                        'country'              => $r['Country__c'] ?? null,
                        'exam_year'            => $r['Exam_Year__c'] ?? null,
                        'date_of_application'  => $r['Date_of_Application__c'] ?? null,
                        'entry_number'         => $r['Entry_Number__c'] ?? null,
                        'application_received' => (bool) ($r['Application_Received__c'] ?? false),
                        'application_approved' => (bool) ($r['Application_Approved__c'] ?? false),
                        'sf_created_at'        => isset($r['CreatedDate']) ? \Carbon\Carbon::parse($r['CreatedDate']) : null,
                        'sf_modified_at'       => isset($r['LastModifiedDate']) ? \Carbon\Carbon::parse($r['LastModifiedDate']) : null,
                        'synced_at'            => now(),
                        'updated_at'           => now(),
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
}
