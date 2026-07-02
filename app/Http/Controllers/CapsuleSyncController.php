<?php

namespace App\Http\Controllers;

use App\Services\CapsuleCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CapsuleSyncController extends Controller
{
    protected CapsuleCrmService $capsule;

    public function __construct(CapsuleCrmService $capsule)
    {
        $this->capsule = $capsule;
    }

    /**
     * Show the Capsule CRM sync dashboard.
     * Capsule contact count is fetched lazily via JS to keep page load fast.
     */
    public function index()
    {
        $totalFellows = DB::table('fellows')->count();
        $withEmail    = DB::table('fellows')
            ->whereNotNull('personal_email')
            ->where('personal_email', '!=', '')
            ->count();
        $withoutEmail = $totalFellows - $withEmail;

        $lastSync = DB::table('capsule_sync_log')
            ->orderByDesc('synced_at')
            ->first();

        $running = DB::table('capsule_sync_log')
            ->where('status', 'running')
            ->orderByDesc('id')
            ->first();

        return view('admin.capsule.index', compact(
            'totalFellows', 'withEmail', 'withoutEmail', 'lastSync', 'running'
        ));
    }

    /**
     * Return the live Capsule contact count (called via AJAX on page load).
     * Cached for 1 hour.
     */
    public function capsuleCount()
    {
        $count = $this->capsule->getTotalContacts();
        return response()->json(['count' => $count]);
    }

    /**
     * Launch the sync as a background Artisan command so PHP's 30s web limit doesn't kill it.
     */
    public function sync(Request $request)
    {
        // Block if already running
        $running = DB::table('capsule_sync_log')->where('status', 'running')->exists();
        if ($running) {
            return response()->json(['success' => false, 'message' => 'A sync is already running.'], 409);
        }

        $artisan = base_path('artisan');
        $log     = storage_path('logs/capsule-sync.log');

        // Run in background — PHP web process returns immediately
        exec("nohup php {$artisan} capsule:sync-fellows >> {$log} 2>&1 &");

        // Small pause to let the command insert its log row
        usleep(500000);

        $row = DB::table('capsule_sync_log')
            ->where('status', 'running')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'log_id'  => $row->id ?? null,
            'message' => 'Sync started in background.',
        ]);
    }

    /**
     * Poll endpoint — frontend calls this every 5s to get progress.
     */
    public function status()
    {
        $running = DB::table('capsule_sync_log')
            ->where('status', 'running')
            ->orderByDesc('id')
            ->first();

        if ($running) {
            $pct = $running->total > 0
                ? round(($running->progress / $running->total) * 100)
                : 0;

            return response()->json([
                'status'   => 'running',
                'progress' => $running->progress,
                'total'    => $running->total,
                'percent'  => $pct,
                'created'  => $running->created,
                'updated'  => $running->updated,
                'failed'   => $running->failed,
            ]);
        }

        $last = DB::table('capsule_sync_log')
            ->whereIn('status', ['completed', 'failed'])
            ->orderByDesc('synced_at')
            ->first();

        return response()->json([
            'status'  => $last->status ?? 'idle',
            'total'   => $last->total   ?? 0,
            'created' => $last->created ?? 0,
            'updated' => $last->updated ?? 0,
            'failed'  => $last->failed  ?? 0,
        ]);
    }

    /**
     * Sync a single fellow to Capsule CRM.
     */
    public function syncOne(int $fellowId)
    {
        $fellow = DB::table('fellows')
            ->join('categories', 'fellows.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'fellows.country_id', '=', 'countries.id')
            ->where('fellows.id', $fellowId)
            ->select([
                'fellows.id', 'fellows.firstname', 'fellows.lastname',
                'fellows.personal_email', 'fellows.phone_number',
                'fellows.organization', 'fellows.current_specialty',
                'fellows.address', 'fellows.cosecsa_region',
                'fellows.status', 'fellows.is_promoted',
                'fellows.fellowship_year', 'fellows.candidate_number',
                'fellows.fcs_certificate_number', 'fellows.mcs_certificate_number',
                'categories.category_name', 'countries.country_name',
            ])
            ->first();

        if (! $fellow) {
            return response()->json(['success' => false, 'message' => 'Fellow not found'], 404);
        }

        $payload  = CapsuleCrmService::fellowToPayload($fellow);
        $tags     = CapsuleCrmService::fellowTags($fellow);
        $existing = null;

        if (! empty($fellow->personal_email)) {
            $existing = $this->capsule->findByEmail($fellow->personal_email);
        }
        if (! $existing) {
            $existing = $this->capsule->findByName($fellow->firstname, $fellow->lastname);
        }

        if ($existing) {
            $ok = $this->capsule->updateContact($existing['id'], $payload);
            if ($ok && $tags) {
                $existingTagNames = array_column($existing['tags'] ?? [], 'name');
                $this->capsule->setTags($existing['id'], array_unique(array_merge($existingTagNames, $tags)));
            }
            $action = $ok ? 'updated' : 'failed';
        } else {
            $created_party = $this->capsule->createContact($payload);
            if (! $created_party) {
                return response()->json(['success' => false, 'message' => 'Failed to create contact in Capsule']);
            }
            if ($tags) {
                $this->capsule->setTags($created_party['id'], $tags);
            }
            $action = 'created';
        }

        return response()->json(['success' => true, 'action' => $action]);
    }
}
