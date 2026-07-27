<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CapsuleSyncController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index()
    {
        $response = $this->api->get('capsule/');
        $data = $response->object();

        return view('admin.capsule.index', [
            'totalFellows' => $data->totalFellows ?? 0,
            'withEmail'    => $data->withEmail ?? 0,
            'withoutEmail' => $data->withoutEmail ?? 0,
            'lastSync'     => $data->lastSync ?? null,
            'running'      => $data->running ?? null,
            'capsuleTotal' => $data->capsuleTotal ?? null,
            'lastImport'   => $data->lastImport ?? null,
            'difference'   => $data->difference ?? null,
        ]);
    }

    public function differences(Request $request)
    {
        $response = $this->api->get('capsule/differences', $request->only(['q']));
        $data = $response->object();

        return view('admin.capsule.differences', [
            'fellows'      => $this->rebuildPaginator($data->fellows ?? null, $request),
            'totalMis'     => $data->total_mis ?? 0,
            'totalCapsule' => $data->total_capsule ?? 0,
            'search'       => $data->search ?? '',
        ]);
    }

    public function sync(Request $request)
    {
        $response = $this->api->post('capsule/sync', []);

        return response()->json($response->json(), $response->status());
    }

    public function status()
    {
        $response = $this->api->get('capsule/status');

        return response()->json($response->json(), $response->status());
    }

    public function contacts(Request $request)
    {
        $response = $this->api->get('capsule/contacts', $request->only(['q']));
        $data = $response->object();

        return view('admin.capsule.contacts', [
            'contacts'   => $this->rebuildPaginator($data->contacts ?? null, $request),
            'totalLocal' => $data->total_local ?? 0,
            'lastImport' => $data->last_import ?? null,
            'search'     => $data->search ?? '',
        ]);
    }

    public function importContacts(Request $request)
    {
        $response = $this->api->post('capsule/import-contacts', []);

        return response()->json($response->json(), $response->status());
    }

    public function syncOne(int $fellowId)
    {
        $response = $this->api->post("capsule/sync/{$fellowId}", []);

        return response()->json($response->json(), $response->status());
    }

    private function rebuildPaginator(?object $raw, Request $request): LengthAwarePaginator
    {
        if (! $raw) {
            return new LengthAwarePaginator([], 0, 50, 1, [
                'path' => $request->url(), 'query' => $request->query(),
            ]);
        }

        return new LengthAwarePaginator(
            collect($raw->data ?? []),
            $raw->total ?? 0,
            $raw->per_page ?? 50,
            $raw->current_page ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
