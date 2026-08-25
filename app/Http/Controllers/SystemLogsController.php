<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\ApiClient;

class SystemLogsController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index(Request $request)
    {
        $response = $this->api->get('admin/logs', $request->only(['tab', 'q', 'model_type', 'action']));
        $data = $response->object();

        $records = $this->rebuildPaginator($data->records ?? null, $request);

        return view('admin.logs.index', [
            'header_title' => 'System Logs',
            'tab'          => $data->tab ?? 'logins',
            'records'      => $records,
            'modelTypes'   => collect($data->model_types ?? []),
        ]);
    }

    // Fetches one email_logs row's full subject/addresses/body for the
    // "View" action on the Emails Dispatched tab — kept off the main list
    // response since bodies can be large. Returned as JSON for the modal's
    // AJAX fetch in admin/logs/index.blade.php.
    public function showEmail($id)
    {
        $response = $this->api->get("admin/logs/emails/{$id}");

        return response()->json($response->object(), $response->status());
    }

    // Reconstruct a LengthAwarePaginator from the JSON paginator shape the
    // API returns, so Blade can call $records->links() as usual.
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
