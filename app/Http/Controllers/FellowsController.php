<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class FellowsController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('fellows/list-data');
        $data = $response->object();

        return view('admin.associates.fellows.list', [
            'header_title'      => 'Fellows',
            'getFellows'        => collect($data->fellows ?? []),
            'filterCountries'   => collect($data->filterCountries ?? []),
            'filterTypes'       => collect($data->filterTypes ?? []),
            'filterProgrammes'  => collect($data->filterProgrammes ?? []),
            'filterYears'       => collect($data->filterYears ?? []),
            'uniqueAlumniCount' => $data->uniqueAlumniCount ?? 0,
            'allAlumniCount'    => $data->allAlumniCount ?? 0,
            'extraAlumniRows'   => $data->extraAlumniRows ?? [],
        ]);
    }

    public function reports()
    {
        $response = $this->api->get('fellows/reports/filters');
        $data = $response->object();

        return view('admin.associates.fellows.reports', [
            'header_title'    => 'Fellows Analytics',
            'filterCountries' => collect($data->filterCountries ?? []),
            'filterTypes'     => collect($data->filterTypes ?? []),
            'filterYears'     => collect($data->filterYears ?? []),
        ]);
    }

    public function reportsData()
    {
        $response = $this->api->get('fellows/reports/data', request()->only([
            'country_id', 'category_id', 'year', 'gender', 'is_alumni',
        ]));

        return response()->json($response->json())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    public function view($id)
    {
        $response = $this->api->get("fellows/{$id}/detail");

        if ($response->status() === 404) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        $data = $response->object();

        if (empty($data->fellow)) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found or failed to load.');
        }

        return view('admin.associates.fellows.view', [
            'header_title'       => 'View Fellow',
            'fellow'             => $data->fellow,
            'subscriptions'      => collect($data->subscriptions ?? []),
            'fellowResults'      => collect($data->fellowResults ?? []),
            'capsuleExamResults' => collect($data->capsuleExamResults ?? []),
            'allLabels'          => collect($data->allLabels ?? []),
            'assignedLabels'     => collect($data->assignedLabels ?? []),
            'currentLabelIds'    => $data->currentLabelIds ?? [],
            'relatedProfiles'    => $data->relatedProfiles ?? null,
        ]);
    }

    public function add()
    {
        // Country list still comes from the local DB — lightweight reference data.
        $data['getCountry']   = \App\Models\Country::getCountry();
        $data['header_title'] = 'Add New Fellow';
        return view('admin.associates.fellows.add', $data);
    }

    public function insert(Request $request)
    {
        if ($request->hasFile('profile_image')) {
            $response = $this->api->postWithFile(
                'fellows',
                $request->file('profile_image'),
                'profile_image',
                $request->except('profile_image')
            );
        } else {
            $response = $this->api->post('fellows', $request->all());
        }

        if ($response->failed()) {
            return redirect()->back()->withInput()
                ->with('error', $response->json('message') ?? 'Failed to add fellow.');
        }

        $fellowId = $response->json('fellow_id');

        return redirect('admin/associates/fellows/subscriptions/' . $fellowId)
            ->with('success', 'Fellow added successfully! You can now add annual subscription records.');
    }

    public function import()
    {
        $data['header_title'] = 'Import Fellows';
        return view('admin.associates.fellows.import_fellows', $data);
    }

    public function downloadTemplate()
    {
        $headers = [
            'firstname','middlename','lastname','email','password','gender','status',
            'candidate_number','category_id','programme_id','country_id','cosecsa_region',
            'phone_number','personal_email','second_email','address','organization',
            'current_specialty','admission_year','mcs_qualification_year','fellowship_year',
            'is_promoted','supervised_by','registered_by','secretariat_registration_date',
            'sponsored_by','prog_entry_fee_year','prog_entry_mode_payment',
            'exam_fee_year','exam_fee_date_paid','exam_fee_amount_paid','exam_fee_mode_payment',
            'exam_fee_payment_verified','country_mcs_training','exam_year_upcoming',
            'exam_year_previous','profile_image',
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                'Lucy','Anne','Kaomba','l.kaomba@hospital.mw','Fellow@2024',
                'Female','Active','MW/2015/04','5','2','1','Eastern Africa',
                '+265999123456','lucy@gmail.com','','P.O. Box 100, Blantyre','Queen Elizabeth Central Hospital',
                'General Surgery','2015','2016','2018',
                '1','Dr W. Mulwafu','Secretariat','2015-01-15',
                'NORHED','2015','Bank Transfer',
                '2016','2016-03-10','500.00','Bank Transfer',
                '1','Malawi','2026','2025','',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fellows_import_template.csv"',
        ]);
    }

    public function importFellows(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $response = $this->api->postWithFile('fellows/import', $request->file('file'), 'file');

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Import failed.');
        }

        return redirect('admin/associates/fellows/list')->with('success', 'Fellows imported successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("fellows/{$id}/detail");

        if ($response->status() === 404) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        $data['getCountry']   = \App\Models\Country::getCountry();
        $data['header_title'] = 'Edit Fellow';
        $data['fellow']       = $response->object()->fellow;

        return view('admin.associates.fellows.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->hasFile('profile_image')) {
            $response = $this->api->postWithFile(
                "fellows/{$id}",
                $request->file('profile_image'),
                'profile_image',
                $request->except('profile_image')
            );
        } else {
            $response = $this->api->post("fellows/{$id}", $request->all());
        }

        if ($response->failed()) {
            return redirect()->back()->withInput()
                ->with('error', $response->json('message') ?? 'Failed to update fellow.');
        }

        return redirect('admin/associates/fellows/view/' . $id)->with('success', 'Fellow updated successfully');
    }

    public function updateLabels(Request $request, $id)
    {
        $response = $this->api->put("fellows/{$id}/labels", ['labels' => $request->labels ?? []]);

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Failed to update labels.');
        }

        return redirect('admin/associates/fellows/view/' . $id . '#tab-personal')
            ->with('success', 'Labels updated successfully');
    }

    // ── Subscriptions ──────────────────────────────────────────────────────────

    public function subscriptions($id)
    {
        $response = $this->api->get("fellows/{$id}/detail");

        if ($response->status() === 404) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }

        $data  = $response->object();
        $fellow = $data->fellow;
        $header_title = 'Subscriptions — ' . trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? ''));

        return view('admin.associates.fellows.subscriptions', [
            'fellow'        => $fellow,
            'subscriptions' => collect($data->subscriptions ?? []),
            'header_title'  => $header_title,
        ]);
    }

    public function storeSubscription(Request $request, $id)
    {
        $response = $this->api->post("fellows/{$id}/subscriptions", $request->all());

        if ($response->status() === 422) {
            return redirect()->back()->with('error', $response->json('message'));
        }

        if ($response->failed()) {
            return redirect()->back()->with('error', 'Failed to add subscription.');
        }

        return redirect()->back()->with('success', 'Subscription record for ' . $request->year . ' added successfully.');
    }

    public function updateSubscription(Request $request, $sub_id)
    {
        $response = $this->api->put("fellows/subscriptions/{$sub_id}", $request->all());

        if ($response->status() === 422) {
            return redirect()->back()->with('error', $response->json('message'));
        }

        if ($response->failed()) {
            return redirect()->back()->with('error', 'Failed to update subscription.');
        }

        $fellowId = $response->json('fellow_id');

        return redirect('admin/associates/fellows/subscriptions/' . $fellowId)
            ->with('success', 'Subscription for ' . $request->year . ' updated successfully.');
    }

    public function deleteSubscription($sub_id)
    {
        $response = $this->api->delete("fellows/subscriptions/{$sub_id}");

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Failed to delete subscription.');
        }

        $fellowId = $response->json('fellow_id');
        $year     = $response->json('year');

        return redirect('admin/associates/fellows/subscriptions/' . $fellowId)
            ->with('success', 'Subscription record for ' . $year . ' deleted.');
    }

    // ── Delete ─────────────────────────────────────────────────────────────────

    public function delete($id)
    {
        $response = $this->api->delete("fellows/{$id}");

        if ($response->failed()) {
            return redirect('admin/associates/fellows/list')
                ->with('error', $response->json('message') ?? 'Failed to delete fellow.');
        }

        return redirect('admin/associates/fellows/list')->with('success', 'Fellow successfully Deleted');
    }
}
