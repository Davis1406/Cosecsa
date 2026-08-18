<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\ApiClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HospitalController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function dashboard(Request $request)
    {
        $params = $request->only(['country_id', 'programme_id', 'flag', 'search']);

        // Fetch accreditation rows, hospital list, and HP list in parallel via API.
        $dashRes  = $this->api->get('admin/hospitals/dashboard', $params);
        $hospRes  = $this->api->get('admin/hospitals');
        $hpRes    = $this->api->get('admin/hospital-programmes');

        $dash = $dashRes->object();
        $hosp = $hospRes->object();
        $hp   = $hpRes->object();

        // hospListData mirrors what buildHospitalListData() used to return.
        $hospListData = [
            'getRecord'       => collect($hosp->hospitals ?? []),
            'totalHospitals'  => $hosp->total_hospitals ?? 0,
            'totalActive'     => $hosp->total_active ?? 0,
            'totalInactive'   => $hosp->total_inactive ?? 0,
            'countGovt'       => $hosp->count_govt ?? 0,
            'countNGO'        => $hosp->count_ngo ?? 0,
            'countPrivate'    => $hosp->count_private ?? 0,
            'countUniversity' => $hosp->count_university ?? 0,
            'byCountry'       => collect((array) ($hosp->by_country ?? [])),
            'byType'          => collect((array) ($hosp->by_type ?? [])),
        ];

        // hpListData mirrors buildHospitalProgrammesListData().
        $hpListData = [
            'getHospitalProgrammes' => collect($hp->hospital_programmes ?? []),
            'totalAccreditations'   => $hp->total_accreditations ?? 0,
            'totalActive'           => $hp->total_active ?? 0,
            'totalExpired'          => $hp->total_expired ?? 0,
            'byProgramme'           => collect((array) ($hp->by_programme ?? [])),
            'byCountry'             => collect((array) ($hp->by_country ?? [])),
        ];

        return view('admin.hospital.dashboard', [
            'header_title'        => 'Hospital Accreditation',
            'rows'                => collect($dash->rows ?? []),
            'countries'           => collect($dash->countries ?? []),
            'programmes'          => collect($dash->programmes ?? []),
            'filters'             => $request->only(['country_id', 'programme_id', 'flag', 'search']),
            'warningDays'         => $dash->warning_days ?? 90,
            'totalHospitals'      => $dash->total_hospitals ?? 0,
            'totalAccreditations' => $dash->total_accreditations ?? 0,
            'countActive'         => $dash->count_active ?? 0,
            'countExpiringSoon'   => $dash->count_expiring_soon ?? 0,
            'countExpired'        => $dash->count_expired ?? 0,
            'hospListData'        => $hospListData,
            'hpListData'          => $hpListData,
        ]);
    }

    public function savePd(Request $request, $hospitalProgrammeId)
    {
        $response = $this->api->post("admin/hospitals/pd/{$hospitalProgrammeId}/save", $request->only([
            'programme_director_id', 'name', 'email', 'phone', 'assistant_pd', 'assistant_email',
        ]));

        if ($response->failed()) {
            return back()->with('error', $response->json('message'));
        }

        return back()->with('success', $response->json('message'));
    }

    public function sendReminder($hospitalProgrammeId)
    {
        $response = $this->api->post("admin/hospitals/reminders/{$hospitalProgrammeId}/send", []);

        if ($response->failed()) {
            return back()->with('error', $response->json('message'));
        }

        return back()->with('success', $response->json('message'));
    }

    public function sendBulkReminders(Request $request)
    {
        $response = $this->api->post('admin/hospitals/reminders/send-bulk', [
            'hospital_programme_ids' => $request->input('hospital_programme_ids', []),
        ]);

        if ($response->failed()) {
            return back()->with('error', $response->json('message'));
        }

        return back()->with('success', $response->json('message'));
    }

    // Activate/reaccredit one or more programmes at a hospital in one go.
    // The chosen month/year is the new accreditation (reaccreditation) date
    // — expiry is computed as 3 years from that date, the college's standard
    // accreditation cycle. cosecsa-api handles the actual reaccreditation
    // vs. first-time-accreditation distinction (and logs history) per
    // programme depending on whether it already had an accreditation row.
    public function reaccredit(Request $request)
    {
        $validated = $request->validate([
            'hospital_id'    => 'required|integer',
            'programme_id'   => 'required|array|min:1',
            'programme_id.*' => 'integer',
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2020|max:2040',
        ]);

        $accreditedDate = Carbon::createFromDate($validated['year'], $validated['month'], 1)->startOfMonth();
        $expiryDate = $accreditedDate->copy()->addYears(3)->endOfMonth();

        $response = $this->api->post('admin/hospital-programmes/reaccredit', [
            'hospital_id'     => $validated['hospital_id'],
            'programme_id'    => $validated['programme_id'],
            'accredited_date' => $accreditedDate->format('Y-m-d'),
            'expiry_date'     => $expiryDate->format('Y-m-d'),
        ]);

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Update failed'));
        }

        return back()->with('success', $response->json('message'));
    }

    // Deactivate — marks one accreditation row Expired as of today.
    public function markExpired(Request $request)
    {
        $validated = $request->validate([
            'hospital_programme_id' => 'required|integer',
        ]);

        $response = $this->api->put(
            "admin/hospital-programmes/{$validated['hospital_programme_id']}/toggle-status",
            ['expiry_date' => now()->format('Y-m-d'), 'status' => 'Expired']
        );

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Update failed'));
        }

        return back()->with('success', $response->json('message'));
    }

    // Lightweight JSON: the hospital's currently-accredited programmes, for
    // the reaccreditation modal's programme checklist (dashboard.blade.php).
    public function programmesJson($id)
    {
        $response = $this->api->get("admin/hospitals/{$id}");
        $programmes = collect($response->object()->programmes ?? [])
            ->map(fn ($p) => ['programme_id' => $p->programme_id, 'programme_name' => $p->programme_name])
            ->values();

        return response()->json(['programmes' => $programmes]);
    }

    public function hospital()
    {
        $response = $this->api->get('admin/hospitals');
        $data = $response->object();

        return view('admin.hospital.list', [
            'header_title'    => 'Hospitals',
            'getRecord'       => collect($data->hospitals ?? []),
            'totalHospitals'  => $data->total_hospitals ?? 0,
            'totalActive'     => $data->total_active ?? 0,
            'totalInactive'   => $data->total_inactive ?? 0,
            'countGovt'       => $data->count_govt ?? 0,
            'countNGO'        => $data->count_ngo ?? 0,
            'countPrivate'    => $data->count_private ?? 0,
            'countUniversity' => $data->count_university ?? 0,
            'byCountry'       => collect((array) ($data->by_country ?? [])),
            'byType'          => collect((array) ($data->by_type ?? [])),
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("admin/hospitals/{$id}");

        if ($response->status() === 404) {
            return redirect('admin/hospital/list')->with('error', 'Hospital not found');
        }

        $data = $response->object();

        return view('admin.hospital.view_hospital', [
            'header_title'  => $data->hospital->name ?? 'View Hospital',
            'hospital'      => $data->hospital,
            'programmes'    => collect($data->programmes ?? []),
            'programmeDirectors' => collect($data->programme_directors ?? []),
            'trainees'      => collect($data->trainees ?? []),
            'fellows'       => collect($data->fellows ?? []),
            'trainers'      => collect($data->trainers ?? []),
            'accreditationHistory' => collect($data->accreditation_history ?? []),
            'allProgrammes' => \App\Models\Programme::getProgramme(),
            'allCountries'  => \App\Models\Country::getCountry(),
        ]);
    }

    // ── Programmes / PD / Fellow-mapping widgets on the hospital view page ──

    public function addProgramme(Request $request, $id)
    {
        $response = $this->api->post('admin/hospital-programmes', [
            'hospital_id'     => $id,
            'programme_id'    => $request->input('programme_id', []),
            'accredited_date' => $request->input('accredited_date'),
            'expiry_date'     => $request->input('expiry_date'),
            'status'          => $request->input('status', 'Active'),
        ]);

        Cache::forget('lookup:programmes');

        return response()->json($response->json(), $response->status());
    }

    public function mapFellow(Request $request, $id)
    {
        $response = $this->api->post("admin/hospitals/{$id}/fellows", $request->only(['fellow_id']));

        return response()->json($response->json(), $response->status());
    }

    public function unmapFellow($id, $fellowId)
    {
        $response = $this->api->delete("admin/hospitals/{$id}/fellows/{$fellowId}");

        return response()->json($response->json(), $response->status());
    }

    public function add()
    {
        return view('admin.hospital.add_hospital', [
            'header_title' => 'Add New Hospital',
            'countries'    => Country::orderBy('country_name')->get(),
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('admin/hospitals', $request->only([
            'name', 'country_id', 'hospital_type', 'status', 'contact_email',
        ]));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message', 'Failed to create hospital'));
        }

        Cache::forget('lookup:hospitals');

        return redirect('admin/hospital/list')->with('success', 'Hospital successfully created');
    }

    public function edit($id)
    {
        $response = $this->api->get("admin/hospitals/{$id}");

        if ($response->status() === 404) {
            abort(404);
        }

        $data = $response->object();

        return view('admin.hospital.edit_hospital', [
            'header_title' => 'Edit Hospital',
            'getRecord'    => $data->hospital,
            'countries'    => Country::orderBy('country_name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = $this->api->post("admin/hospitals/{$id}", $request->only([
            'name', 'country_id', 'hospital_type', 'status', 'contact_email',
        ]));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message', 'Failed to update hospital'));
        }

        Cache::forget('lookup:hospitals');

        return redirect('admin/hospital/list')->with('success', 'Hospital successfully updated');
    }

    public function delete($id)
    {
        $response = $this->api->delete("admin/hospitals/{$id}");

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Failed to delete hospital'));
        }

        Cache::forget('lookup:hospitals');

        return redirect('admin/hospital/list')->with('success', 'Hospital successfully deleted');
    }
}
