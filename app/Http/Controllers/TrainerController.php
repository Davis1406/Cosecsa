<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

// COSECSA ToT (Training of Trainers) roster — distinct from Programme
// Directors (see ProgrammeDirectorController, formerly named TrainerController).
// Read-only from this admin panel for now: the roster is seeded/maintained
// via `php artisan trainers:import-tot-list` in cosecsa-api.
class TrainerController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list(Request $request)
    {
        $response = $this->api->get('trainers/list-data');
        $data = $response->object();
        $trainers = collect($data->trainers ?? []);

        if ($request->filled('search')) {
            $search = strtolower($request->string('search'));
            $trainers = $trainers->filter(fn ($t) => str_contains(strtolower($t->name ?? ''), $search)
                || str_contains(strtolower($t->organisation ?? ''), $search)
                || str_contains(strtolower($t->email ?? ''), $search));
        }
        if ($request->filled('country')) {
            $trainers = $trainers->filter(fn ($t) => $t->country === $request->string('country'));
        }
        if ($request->filled('specialty')) {
            $trainers = $trainers->filter(fn ($t) => $t->specialty === $request->string('specialty'));
        }
        if ($request->boolean('master_only')) {
            $trainers = $trainers->filter(fn ($t) => ! empty($t->is_master_trainer));
        }

        return view('admin.associates.trainers.list', [
            'getRecord'    => $trainers->values(),
            'countries'    => collect($data->trainers ?? [])->pluck('country')->filter()->unique()->sort()->values(),
            'specialties'  => collect($data->trainers ?? [])->pluck('specialty')->filter()->unique()->sort()->values(),
            'filters'      => $request->only(['search', 'country', 'specialty', 'master_only']),
            'header_title' => 'Trainers List',
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("trainers/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }
        $data = $response->object();

        return view('admin.associates.trainers.view', [
            'trainer'      => $data->trainer,
            'header_title' => 'View Trainer',
        ]);
    }

    public function quickUpdate(Request $request, $id)
    {
        $response = $this->api->post("trainers/{$id}/quick-update", [
            'field' => $request->input('field'),
            'value' => $request->input('value'),
        ]);

        return response()->json($response->json(), $response->status());
    }
}
