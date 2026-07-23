<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class SalesforceSyncController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index(Request $request)
    {
        $response = $this->api->get('salesforce/', $request->only([
            'q', 'stage', 'programme', 'country', 'level',
            'received', 'approved', 'application_year',
        ]));

        if ($response->failed()) {
            return redirect()->back()->with('error', 'Could not load Salesforce data: ' . $response->json('message'));
        }

        $data = $response->object();

        // The API returns stdClass; applications is an array of stdClass rows.
        // stageCounts / programmeCounts / countryCounts come back as stdClass
        // (JSON objects keyed by name) — cast to array so collect() iterates them.
        return view('admin.salesforce.index', [
            'applications'    => collect($data->applications ?? []),
            'stages'          => collect($data->stages ?? []),
            'programmes'      => collect($data->programmes ?? []),
            'countries'       => collect($data->countries ?? []),
            'levels'          => collect($data->levels ?? []),
            'years'           => collect($data->years ?? []),
            'search'          => $data->search ?? '',
            'stage'           => $data->stage ?? null,
            'programme'       => $data->programme ?? null,
            'country'         => $data->country ?? null,
            'level'           => $data->level ?? null,
            'received'        => $data->received ?? null,
            'approved'        => $data->approved ?? null,
            'appYear'         => $data->appYear ?? null,
            'total'           => $data->total ?? 0,
            'lastSync'        => $data->lastSync ?? null,
            'stageCounts'     => collect((array) ($data->stageCounts ?? [])),
            'programmeCounts' => collect((array) ($data->programmeCounts ?? [])),
            'countryCounts'   => collect((array) ($data->countryCounts ?? [])),
            'trendLabels'     => collect($data->trendLabels ?? []),
            'trendCounts'     => collect($data->trendCounts ?? []),
            'receivedCount'   => $data->receivedCount ?? 0,
            'approvedCount'   => $data->approvedCount ?? 0,
            'rejectedCount'   => $data->rejectedCount ?? 0,
        ]);
    }

    public function show($id)
    {
        $response = $this->api->get("salesforce/{$id}");

        if ($response->status() === 404) {
            return redirect('admin/salesforce')->with('error', 'Application not found');
        }

        $data = $response->object();

        return view('admin.salesforce.show', [
            'application' => $data->application,
            'intakeYear'  => $data->intakeYear ?? null,
            'trainee'     => $data->trainee ?? null,
        ]);
    }

    public function sync(Request $request)
    {
        $params = $request->boolean('full') ? ['full' => 1] : [];
        $response = $this->api->post('salesforce/sync', $params);

        if ($response->failed()) {
            return redirect('admin/salesforce')->with('error', 'Salesforce sync failed: ' . $response->json('message'));
        }

        return redirect('admin/salesforce')->with('success', $response->json('message'));
    }

    public function populateTraineesPreview(Request $request)
    {
        $params = $request->boolean('all_years') ? ['all_years' => 1] : [];
        $response = $this->api->get('salesforce/populate-trainees', $params);

        if ($response->failed()) {
            return redirect('admin/salesforce')->with('error', 'Could not load preview: ' . $response->json('message'));
        }

        $data = $response->object();
        $allYears = $data->allYears ?? false;

        // Each item in ready/skipped/unresolved has an 'app' key that is a
        // stdClass row — re-hydrate from the JSON array so Blade can use ->
        $ready     = $this->hydrateRows($data->ready ?? []);
        $skipped   = $this->hydrateRows($data->skipped ?? []);
        $unresolved = $this->hydrateRows($data->unresolved ?? []);

        return view('admin.salesforce.populate_trainees', compact('ready', 'skipped', 'unresolved', 'allYears'));
    }

    public function populateTraineesApply(Request $request)
    {
        $params = $request->boolean('all_years') ? ['all_years' => 1] : [];
        $response = $this->api->post('salesforce/populate-trainees', $params);

        if ($response->failed()) {
            return redirect('admin/salesforce')->with('error', 'Failed: ' . $response->json('message'));
        }

        return redirect('admin/salesforce')->with('success', $response->json('message'));
    }

    // Re-hydrate an array of rows where each row has an 'app' key that came
    // back from JSON as a plain object. Keeps all other keys (pen, pen_source,
    // programme_id, …) as-is so the Blade view can still access $row['pen'] etc.
    private function hydrateRows(array $rows): array
    {
        return array_map(function ($row) {
            $row = (array) $row;
            if (isset($row['app'])) {
                $row['app'] = (object) ((array) $row['app']);
            }
            return $row;
        }, $rows);
    }
}
