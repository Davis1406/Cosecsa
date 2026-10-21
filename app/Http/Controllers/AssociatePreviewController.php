<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Support\Facades\Auth;

// The short summary shown in the side drawer on the Associates lists (cosecsa-api internal/associate-preview/*).
// One controller for every associate type: routes/web.php binds a fixed `type` default per associate, so the
// permission middleware (matched by URL prefix) gates each one under its own module, like the notes routes.
class AssociatePreviewController extends Controller
{
    /** type => the permission module that allows editing it */
    private const MANAGE = [
        'trainee' => 'trainees', 'candidate' => 'candidates', 'fellow' => 'fellows', 'member' => 'members',
        'programme_director' => 'programme_directors', 'country_rep' => 'country_reps', 'examiner' => 'examiners',
    ];

    public function __construct(private ApiClient $api) {}

    public function show(string $id)
    {
        $type = request()->route('type');
        abort_unless(isset(self::MANAGE[$type]), 404);

        $r = $this->api->get("associate-preview/{$type}/".(int) $id);
        abort_unless($r->successful(), $r->status() === 404 ? 404 : 502);

        $data = $r->json();
        $canManage = Auth::user()->hasPermission(self::MANAGE[$type].'.manage');
        $data['links'] = collect($data['links'] ?? [])
            ->reject(fn ($l) => ! empty($l['manage']) && ! $canManage)
            ->map(fn ($l) => ['label' => $l['label'], 'url' => url($l['path']), 'primary' => (bool) ($l['primary'] ?? false)])
            ->values()->all();

        return response()->json($data);
    }
}
