<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

// Secretariat section for the FindASurgeon mobile app: overview + visual report,
// patient accounts, Fellow hospital review and Fellow-side profile changes.
// All data comes from the API (internal/findasurgeon/*).
class FindASurgeonController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function overview()
    {
        $response = $this->api->get('findasurgeon/overview');
        abort_unless($response->successful(), 500, 'Failed to load the Find A Surgeon overview.');

        return view('admin.findasurgeon.overview', [
            'header_title' => 'Find A Surgeon',
            'data'         => $response->object(),
        ]);
    }

    public function patients(Request $request)
    {
        $response = $this->api->get('findasurgeon/patients', $request->only(['q', 'status', 'with_favourites', 'page']));
        abort_unless($response->successful(), 500, 'Failed to load patients.');

        return view('admin.findasurgeon.patients', [
            'header_title' => 'Find A Surgeon · Patients',
            'patients'     => $this->paginator($response->object(), $request),
        ]);
    }

    public function fellows(Request $request)
    {
        $response = $this->api->get('findasurgeon/fellows', $request->only(['q', 'usage', 'country_id', 'page']));
        abort_unless($response->successful(), 500, 'Failed to load Fellows.');
        $data = $response->object();

        return view('admin.findasurgeon.fellows', [
            'header_title' => 'Find A Surgeon · Fellows',
            'fellows'      => $this->paginator($data->fellows ?? null, $request),
            'countries'    => collect($data->countries ?? []),
        ]);
    }

    // JSON for the patient detail modal.
    public function patient($id)
    {
        $response = $this->api->get("findasurgeon/patients/{$id}");

        return response()->json($response->object(), $response->status());
    }

    public function hospitals(Request $request)
    {
        $response = $this->api->get('findasurgeon/hospital-review', $request->only(['show', 'source', 'country_id', 'q']));
        abort_unless($response->successful(), 500, 'Failed to load the hospital review.');
        $data = $response->object();

        return view('admin.findasurgeon.hospital_review', [
            'header_title' => 'Find A Surgeon · Hospital Review',
            'groups'       => collect($data->groups ?? []),
            'countries'    => collect($data->countries ?? []),
            'hospitals'    => collect($data->hospitals ?? []),
            'showing'      => $request->input('show') === 'dismissed' ? 'dismissed' : 'pending',
        ]);
    }

    public function hospitalLink(Request $request)
    {
        return $this->act('hospital-review/link', $request->only(['name', 'country_id', 'hospital_id']));
    }

    public function hospitalAdd(Request $request)
    {
        return $this->act('hospital-review/add', $request->only(['name', 'country_id', 'hospital_name', 'hospital_country_id', 'hospital_type']));
    }

    public function hospitalDismiss(Request $request)
    {
        return $this->act('hospital-review/dismiss', $request->only(['name', 'country_id']));
    }

    public function hospitalRestore(Request $request)
    {
        return $this->act('hospital-review/restore', $request->only(['name', 'country_id']));
    }

    public function changes(Request $request)
    {
        $response = $this->api->get('findasurgeon/changes', $request->only(['field', 'source', 'q', 'from', 'to', 'page']));
        abort_unless($response->successful(), 500, 'Failed to load profile changes.');
        $data = $response->object();

        return view('admin.findasurgeon.changes', [
            'header_title' => 'Find A Surgeon · Profile Changes',
            'changes'      => $this->paginator($data, $request),
            'fields'       => collect($data->fields ?? []),
        ]);
    }

    private function act(string $path, array $data)
    {
        // Empty strings would be dropped by the HTTP client; the API treats absent as null.
        $response = $this->api->post("findasurgeon/{$path}", array_filter($data, fn ($v) => $v !== null && $v !== ''));

        $message = $response->json('message')
            ?? ($response->successful() ? 'Done.' : 'That did not work (HTTP ' . $response->status() . ').');

        return back()->with($response->successful() ? 'success' : 'error', $message);
    }

    // Rebuild a LengthAwarePaginator from the API's JSON paginator so Blade can call ->links().
    private function paginator(?object $raw, Request $request): LengthAwarePaginator
    {
        if (! $raw) {
            return new LengthAwarePaginator([], 0, 25, 1, ['path' => $request->url(), 'query' => $request->query()]);
        }

        return new LengthAwarePaginator(
            collect($raw->data ?? []),
            $raw->total ?? 0,
            $raw->per_page ?? 25,
            $raw->current_page ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
