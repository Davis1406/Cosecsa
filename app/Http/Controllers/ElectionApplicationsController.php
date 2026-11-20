<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

// Fellowship by Election: the applications that came in through the Wufoo form. The data, the Wufoo sync and the approval rules live
// in cosecsa-api (Api\ElectionApplicationController); this controller only passes them through to the pages.
class ElectionApplicationsController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index()
    {
        $response = $this->api->get('election-applications');
        if ($response->failed()) {
            return redirect('admin/dashboard')->with('error', 'Could not load the applications: ' . ($response->json('message') ?: 'no answer from the server.'));
        }
        $data = $response->json();

        return view('admin.election_applications.index', [
            'header_title' => 'Fellowship by Election',
            'applications' => $data['applications'] ?? [],
            'counts'       => $data['counts'] ?? ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
            'lastSync'     => $data['last_sync'] ?? null,
            'configured'   => (bool) ($data['configured'] ?? false),
        ]);
    }

    public function show($id)
    {
        $response = $this->api->get("election-applications/{$id}");
        if ($response->status() === 404) {
            abort(404);
        }
        if ($response->failed()) {
            return redirect('admin/fellowship-by-election')->with('error', 'Could not load the application: ' . ($response->json('message') ?: 'no answer from the server.'));
        }
        $data = $response->json();

        return view('admin.election_applications.show', [
            'header_title' => 'Fellowship by Election',
            'app'          => $data['application'],
            'answers'      => $data['answers'] ?? [],
            'files'        => $data['files'] ?? [],
            'known'        => $data['known'] ?? null,
            'otherEntries' => $data['other_entries'] ?? [],
            'defaultYear'  => $data['default_year'] ?? date('Y'),
        ]);
    }

    public function sync()
    {
        $response = $this->api->post('election-applications/sync');

        return redirect('admin/fellowship-by-election')->with($response->successful() ? 'success' : 'error', $response->json('message') ?: 'No answer from the server.');
    }

    public function review(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,rejected', 'note' => 'nullable|string|max:2000']);
        $response = $this->api->post("election-applications/{$id}/review", $request->only('status', 'note'));

        return redirect("admin/fellowship-by-election/{$id}")->with($response->successful() ? 'success' : 'error', $response->json('message') ?: 'No answer from the server.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['fellowship_year' => 'required|digits:4', 'note' => 'nullable|string|max:2000']);
        $response = $this->api->post("election-applications/{$id}/approve", $request->only('fellowship_year', 'note') + ['force' => $request->boolean('force')]);

        $back = redirect("admin/fellowship-by-election/{$id}")->with($response->successful() ? 'success' : 'error', $response->json('message') ?: 'No answer from the server.');

        return $response->status() === 422 && $response->json('can_force') ? $back->with('can_force', true) : $back;
    }

    // An uploaded document, fetched from Wufoo by the API and shown in the browser.
    public function file($id, $slot)
    {
        $response = $this->api->get("election-applications/{$id}/file/{$slot}");
        if ($response->failed()) {
            return redirect("admin/fellowship-by-election/{$id}")->with('error', $response->json('message') ?: 'The file could not be opened.');
        }

        return response($response->body(), 200, [
            'Content-Type'        => $response->header('Content-Type') ?: 'application/octet-stream',
            'Content-Disposition' => $response->header('Content-Disposition') ?: 'inline',
            'Cache-Control'       => 'private, no-store',
        ]);
    }
}
