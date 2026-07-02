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
        $totalFellows  = DB::table('fellows')->count();
        $withEmail     = DB::table('fellows')
            ->whereNotNull('personal_email')
            ->where('personal_email', '!=', '')
            ->count();
        $withoutEmail  = $totalFellows - $withEmail;

        $capsuleTotal  = $this->capsule->getTotalContacts(); // null if API unreachable
        $difference    = ($capsuleTotal !== null) ? ($totalFellows - $capsuleTotal) : null;

        $lastSync = DB::table('capsule_sync_log')
            ->orderByDesc('synced_at')
            ->first();

        return view('admin.capsule.index', compact(
            'totalFellows', 'withEmail', 'withoutEmail',
            'capsuleTotal', 'difference', 'lastSync'
        ));
    }

    /**
     * Run a full sync of ALL fellows (with or without email) to Capsule CRM.
     * Match strategy: email first, then full-name fallback, then create.
     */
    public function sync(Request $request)
    {
        set_time_limit(0);

        $fellows = DB::table('fellows')
            ->join('categories', 'fellows.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'fellows.country_id', '=', 'countries.id')
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

                // Try to find existing contact: email first, name fallback
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

                // Respect Capsule rate limits (~4 req/s)
                usleep(250000);
            } catch (\Exception $e) {
                Log::error("Capsule sync failed for fellow {$fellow->id}: " . $e->getMessage());
                $failed++;
            }
        }

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
