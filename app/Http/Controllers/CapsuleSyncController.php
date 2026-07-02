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
     */
    public function index()
    {
        $totalFellows = DB::table('fellows')
            ->whereNotNull('personal_email')
            ->where('personal_email', '!=', '')
            ->count();

        $lastSync = DB::table('capsule_sync_log')
            ->orderByDesc('synced_at')
            ->first();

        return view('admin.capsule.index', compact('totalFellows', 'lastSync'));
    }

    /**
     * Run a full sync of all fellows to Capsule CRM.
     * Processes in batches, streams progress as JSON lines.
     */
    public function sync(Request $request)
    {
        set_time_limit(0);

        $fellows = DB::table('fellows')
            ->join('categories', 'fellows.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'fellows.country_id', '=', 'countries.id')
            ->whereNotNull('fellows.personal_email')
            ->where('fellows.personal_email', '!=', '')
            ->select([
                'fellows.id',
                'fellows.firstname',
                'fellows.lastname',
                'fellows.personal_email',
                'fellows.phone_number',
                'fellows.organization',
                'fellows.current_specialty',
                'fellows.address',
                'fellows.cosecsa_region',
                'fellows.status',
                'fellows.is_promoted',
                'fellows.fellowship_year',
                'fellows.candidate_number',
                'fellows.fcs_certificate_number',
                'fellows.mcs_certificate_number',
                'categories.category_name',
                'countries.country_name',
            ])
            ->get();

        $created = 0;
        $updated = 0;
        $failed  = 0;

        foreach ($fellows as $fellow) {
            try {
                $payload = CapsuleCrmService::fellowToPayload($fellow);
                $tags    = CapsuleCrmService::fellowTags($fellow);

                $existing = $this->capsule->findByEmail($fellow->personal_email);

                if ($existing) {
                    $ok = $this->capsule->updateContact($existing['id'], $payload);
                    if ($ok && $tags) {
                        $existingTagNames = array_column($existing['tags'] ?? [], 'name');
                        $mergedTags = array_unique(array_merge($existingTagNames, $tags));
                        $this->capsule->setTags($existing['id'], $mergedTags);
                    }
                    $ok ? $updated++ : $failed++;
                } else {
                    $created_party = $this->capsule->createContact($payload);
                    if ($created_party) {
                        if ($tags) {
                            $this->capsule->setTags($created_party['id'], $tags);
                        }
                        $created++;
                    } else {
                        $failed++;
                    }
                }

                // Small delay to respect Capsule rate limits (~4 req/s)
                usleep(250000);
            } catch (\Exception $e) {
                Log::error("Capsule sync failed for fellow {$fellow->id}: " . $e->getMessage());
                $failed++;
            }
        }

        // Log the sync
        DB::table('capsule_sync_log')->insert([
            'total'     => $fellows->count(),
            'created'   => $created,
            'updated'   => $updated,
            'failed'    => $failed,
            'synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'total'   => $fellows->count(),
            'created' => $created,
            'updated' => $updated,
            'failed'  => $failed,
        ]);
    }

    /**
     * Sync a single fellow to Capsule CRM (called from fellow view page).
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

        if (empty($fellow->personal_email)) {
            return response()->json(['success' => false, 'message' => 'Fellow has no email address'], 422);
        }

        $payload  = CapsuleCrmService::fellowToPayload($fellow);
        $tags     = CapsuleCrmService::fellowTags($fellow);
        $existing = $this->capsule->findByEmail($fellow->personal_email);

        if ($existing) {
            $ok = $this->capsule->updateContact($existing['id'], $payload);
            if ($ok && $tags) {
                $existingTagNames = array_column($existing['tags'] ?? [], 'name');
                $this->capsule->setTags($existing['id'], array_unique(array_merge($existingTagNames, $tags)));
            }
            $action = 'updated';
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
