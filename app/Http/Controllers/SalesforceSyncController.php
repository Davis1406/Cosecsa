<?php

namespace App\Http\Controllers;

use App\Services\SalesforceCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesforceSyncController extends Controller
{
    protected SalesforceCrmService $salesforce;

    public function __construct(SalesforceCrmService $salesforce)
    {
        $this->salesforce = $salesforce;
    }

    /**
     * List Salesforce applications from the local cache table.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $stage  = $request->input('stage');
        $year   = $request->input('exam_year');

        $query = DB::table('salesforce_applications');

        if ($search) {
            $like = "%{$search}%";
            $query->where(function ($w) use ($like) {
                $w->where('applicant_name', 'like', $like)
                  ->orWhere('applicant_email', 'like', $like)
                  ->orWhere('name', 'like', $like)
                  ->orWhere('entry_number', 'like', $like);
            });
        }
        if ($stage) {
            $query->where('application_stage', $stage);
        }
        if ($year) {
            $query->where('exam_year', $year);
        }

        $applications = $query->orderByDesc('sf_modified_at')->paginate(50)->withQueryString();

        $stages = DB::table('salesforce_applications')
            ->whereNotNull('application_stage')
            ->distinct()
            ->orderBy('application_stage')
            ->pluck('application_stage');

        $years = DB::table('salesforce_applications')
            ->whereNotNull('exam_year')
            ->distinct()
            ->orderByDesc('exam_year')
            ->pluck('exam_year');

        $total     = DB::table('salesforce_applications')->count();
        $lastSync  = DB::table('salesforce_sync_log')
            ->where('status', 'completed')
            ->orderByDesc('synced_at')
            ->first();

        return view('admin.salesforce.index', compact(
            'applications', 'stages', 'years', 'search', 'stage', 'year', 'total', 'lastSync'
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
                        'sf_created_at'        => $r['CreatedDate'] ?? null,
                        'sf_modified_at'        => $r['LastModifiedDate'] ?? null,
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
