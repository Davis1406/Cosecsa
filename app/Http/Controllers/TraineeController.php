<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TraineeController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('trainees/list-data');
        $data = $response->object();

        return view('admin.associates.trainees.trainees', [
            'getRecord'           => collect($data->trainees ?? []),
            'header_title'        => 'Trainees List',
            'filterCountries'     => collect($data->filterCountries ?? []),
            'filterProgrammes'    => collect($data->filterProgrammes ?? []),
            'filterYears'         => collect($data->filterYears ?? []),
            'filterAdmissionYears'=> collect($data->filterAdmissionYears ?? []),
            'filterStatuses'      => collect($data->filterStatuses ?? []),
            'filterHospitals'     => collect($data->filterHospitals ?? []),
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("trainees/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
        }
        $data = $response->object();

        return view('admin.associates.trainees.view', [
            'trainee'            => $data->trainee,
            'header_title'       => 'View Trainee',
            'linkedCandidate'    => $data->linkedCandidate ?? null,
            'entryFees'          => collect($data->entryFees ?? []),
            'examFees'           => collect($data->examFees ?? []),
            'capsuleExamResults' => collect($data->capsuleExamResults ?? []),
            'programmes'         => collect($data->programmes ?? []),
            'countries'          => collect($data->countries ?? []),
            'hospitals'          => HospitalModel::getHospital(),
            'examYears'          => collect($data->examYears ?? []),
            'firstAdmissionYear' => $data->firstAdmissionYear ?? null,
        ]);
    }

    public function quickUpdate(Request $request, $id)
    {
        $response = $this->api->post("trainees/{$id}/quick-update", [
            'field' => $request->input('field'),
            'value' => $request->input('value'),
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function add()
    {
        return view('admin.associates.trainees.add', [
            'getHospital'  => HospitalModel::getHospital(),
            'getProgramme' => Programme::getProgramme(),
            'getCountry'   => Country::getCountry(),
            'header_title' => 'Add New Trainee',
        ]);
    }

    public function import()
    {
        return view('admin.associates.trainees.import', [
            'header_title' => 'Import Trainees',
            'report'       => session('import_report'),
        ]);
    }

    public function importData(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:10240']);

        $this->api->postWithFile('trainees/import', [], ['file' => $request->file('file')]);

        return redirect('admin/associates/trainees/import')->with('import_done', true);
    }

    public function bulkUpdate()
    {
        $response = $this->api->get('trainees/bulk-update-data');
        $data = $response->object();

        return view('admin.associates.trainees.bulk_update', [
            'header_title' => 'Bulk Update Trainees',
            'report'       => session('bulk_update_report'),
            'trainees'     => collect($data->trainees ?? []),
            'programmes'   => collect($data->programmes ?? []),
            'countries'    => collect($data->countries ?? []),
            'examYears'    => collect($data->examYears ?? []),
        ]);
    }

    public function bulkUpdateProcess(Request $request)
    {
        $response = $this->api->post('trainees/bulk-update', $request->all());

        session(['bulk_update_report' => $response->json()]);

        return redirect('admin/associates/trainees/bulk-update')->with('bulk_done', true);
    }

    public function insert(Request $request)
    {
        $fields = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'password', 'personal_email',
            'gender', 'status', 'programme_id', 'hospital_id', 'country_id', 'entry_number',
            'admission_letter_status', 'invitation_letter_status', 'admission_year',
            'training_year', 'exam_year', 'programme_period', 'invoice_number',
            'invoice_date', 'invoice_amount', 'invoice_status', 'fee_paid', 'sponsor',
            'mode_of_payment', 'amount_paid', 'payment_date',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile('trainees/', $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post('trainees/', $fields);
        }

        return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee added successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("trainees/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
        }
        $data = $response->object();

        return view('admin.associates.trainees.edit_trainee', [
            'trainee'      => $data->trainee,
            'getHospital'  => HospitalModel::getHospital(),
            'getProgramme' => Programme::getProgramme(),
            'getCountry'   => Country::getCountry(),
            'getStudyYear' => DB::table('study_year')->orderBy('programme_id')->orderBy('id')->get(),
            'header_title' => 'Edit Trainee',
        ]);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'password', 'personal_email',
            'gender', 'status', 'programme_id', 'hospital_id', 'country_id', 'entry_number',
            'admission_letter_status', 'invitation_letter_status', 'sfs_username', 'sfs_password',
            'admission_year', 'exam_year', 'training_year', 'programme_period', 'invoice_number',
            'invoice_date', 'invoice_amount', 'invoice_status', 'fee_paid', 'sponsor',
            'mode_of_payment', 'amount_paid', 'payment_date',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile("trainees/{$id}", $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post("trainees/{$id}", $fields);
        }

        return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee updated successfully');
    }

    public function reports()
    {
        $response = $this->api->get('trainees/reports/filters');
        $data = $response->object();

        return view('admin.associates.trainees.reports', [
            'header_title'        => 'Trainees Analytics',
            'filterCountries'     => collect($data->filterCountries ?? []),
            'filterProgrammes'    => collect($data->filterProgrammes ?? []),
            'filterYears'         => collect($data->filterYears ?? []),
            'filterAdmissionYears'=> collect($data->filterAdmissionYears ?? []),
        ]);
    }

    public function reportsList(Request $request)
    {
        $response = $this->api->get('trainees/reports/list', $request->only(['filter']));
        return response()->json($response->json());
    }

    public function reportsData()
    {
        $response = $this->api->get('trainees/reports/data', request()->all());
        return response()->json($response->json());
    }

    public function delete($id)
    {
        $this->api->delete("trainees/{$id}");

        return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee information successfully deleted');
    }
}
