<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FellowsModel;
use Illuminate\Support\Facades\Hash;
use App\Models\Country;
use App\Models\ExamsModel;
use App\Models\Attendance;
use App\Models\ExamsShift;
use App\Models\ExaminerHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\ApiClient;

class ExamsController extends Controller
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    public function list(Request $request)
    {
        $response = $this->api->get('examiners', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.examiners', [
            'getExaminers'       => collect($d->examiners ?? []),
            'countries'          => collect($d->countries ?? []),
            'programmes'         => collect($d->programmes ?? []),
            'designationOptions' => collect($d->designation_options ?? []),
            'roleOptions'        => collect($d->role_options ?? []),
            'allExamYears'       => collect($d->all_exam_years ?? []),
            'selectedExamYear'   => $d->selected_exam_year ?? date('Y') - 1,
            'selectedYearId'     => $d->selected_year_id ?? 0,
            'noYearSelected'     => $d->no_year_selected ?? true,
            'currentYear'        => date('Y'),
            'lastYear'           => $d->selected_exam_year ?? date('Y') - 1,
            'header_title'       => 'Examiners',
        ]);

    }

    public function import()
    {
        $data['header_title'] = "Import Examiners";
        return view('admin.exams.import', $data);
    }

    public function importExaminers(Request $request)
    {
        if (! $request->hasFile('file')) {
            return redirect('admin/exams/examiners')->with('error', 'No file provided.');
        }

        $response = $this->api->postWithFile('examiners/import', [], [
            'file' => $request->file('file'),
        ]);

        if ($response->failed()) {
            return redirect('admin/exams/examiners')->with('error', $response->json('message') ?? 'Import failed.');
        }

        return redirect('admin/exams/examiners')->with('success', 'Examiners imported successfully');
    }

    // ── Upload Confirmation ──────────────────────────────────────────────────────

    public function uploadConfirmationForm()
    {
        $response = $this->api->get('examiners/upload-confirmation');
        $d = $response->object();

        return view('admin.exams.upload_confirmation', [
            'years'        => collect($d->years ?? []),
            'defaultYear'  => $d->default_year_id ?? 0,
            'header_title' => 'Upload Examiner Confirmation',
        ]);
    }

    public function processConfirmationUpload(Request $request)
    {
        if (! $request->hasFile('file')) {
            return back()->with('error', 'No file provided.');
        }

        $response = $this->api->postWithFile(
            'examiners/upload-confirmation',
            $request->only(['year_id']),
            ['file' => $request->file('file')]
        );

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Upload failed.');
        }

        $d        = $response->object();
        $results  = (array) ($d->results ?? []);
        $yearName = $d->year_name ?? '';

        return view('admin.exams.upload_confirmation_result', compact('results', 'yearName'));
    }

    public function add()
    {
        $response = $this->api->get('examiners/create');
        $d = $response->object();

        return view('admin.exams.add_examiner', [
            'header_title'       => 'Add New Examiner',
            'getCountry'         => collect($d->countries ?? []),
            'groups'             => collect($d->groups ?? []),
            'examYears'          => $d->exam_years ?? [],
            'currentYearName'    => $d->current_year_name ?? date('Y'),
            'programmeOptions'   => $d->programme_options ?? [],
            'specialtyOptions'   => collect($d->specialty_options ?? []),
            'designationOptions' => collect($d->designation_options ?? []),
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('examiners', $request->except('_token'));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Insert failed.');
        }

        return redirect('admin/exams/examiners')->with('success', 'Examiner added successfully');
    }



    public function edit($id, Request $request)
    {
        $response = $this->api->get("examiners/{$id}/edit");

        if ($response->status() === 404) {
            return redirect()->back()->with('error', 'Examiner not found');
        }

        $d = $response->object();

        $from    = $request->input('from');
        if ($from) {
            $query   = $request->except(['from', '_token']);
            $backUrl = url($from) . (count($query) ? '?' . http_build_query($query) : '');
        } else {
            $referer = $request->header('referer');
            $backUrl = ($referer && str_contains($referer, url('/')))
                ? $referer
                : url('admin/exams/examiners');
        }

        return view('admin.exams.edit_examiner', [
            'header_title'       => 'Edit Examiner',
            'examiner'           => $d->examiner,
            'getCountry'         => collect($d->countries ?? []),
            'groups'             => collect($d->groups ?? []),
            'backUrl'            => $backUrl,
            'examYears'          => $d->exam_years ?? [],
            'currentYearName'    => $d->current_year_name ?? date('Y'),
            'yearParticipations' => (array) ($d->year_participations ?? []),
            'yearRoles'          => json_decode(json_encode($d->year_roles ?? []), true),
            'programmeOptions'   => $d->programme_options ?? [],
            'specialtyOptions'   => collect($d->specialty_options ?? []),
            'designationOptions' => collect($d->designation_options ?? []),
        ]);
    }

    public function update(Request $request, $id)
    {
        $files = [];
        foreach (['curriculum_vitae', 'passport_image'] as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }
        }

        $response = $this->api->postWithFile(
            "examiners/{$id}/update",
            $request->except(['_token', 'curriculum_vitae', 'passport_image']),
            $files
        );

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Update failed.');
        }

        $backUrl = $request->input('back_url');
        $redirect = ($backUrl && str_starts_with($backUrl, url('/')))
            ? $backUrl
            : url('admin/exams/view_examiner/' . $id);

        return redirect($redirect)->with('success', 'Examiner updated successfully');
    }

    public function view($id, Request $request)
    {
        $response = $this->api->get("examiners/{$id}", $request->only(['from']));

        if ($response->notFound()) {
            return redirect()->back()->with('error', 'Examiner not found');
        }

        $d        = $response->object();
        $examiner = $d->examiner ?? null;

        if (!$examiner) return redirect()->back()->with('error', 'Examiner not found');

        $from    = $request->input('from', 'admin/exams/examiners');
        $query   = $request->except(['from', '_token']);
        $backUrl = url($from) . (count($query) ? '?' . http_build_query($query) : '');

        $confirmationUrl = $d->confirmation_url ?? url("/admin/exams/confirm-attendance/{$examiner->examin_id}");
        $qrCode = QrCode::size(70)->generate($confirmationUrl);

        return view('admin.exams.view_examiner', [
            'header_title'       => 'View Examiner',
            'examiner'           => $examiner,
            'getCountry'         => collect($d->countries ?? []),
            'groups'             => collect($d->groups ?? []),
            'qrCode'             => $qrCode,
            'backUrl'            => $backUrl,
            'yearProgrammes'     => json_decode(json_encode($d->year_programmes ?? []), true),
            'yearRoles'          => json_decode(json_encode($d->year_roles ?? []), true),
            'currentYearName'    => $d->current_year_name ?? date('Y'),
            'examYears'          => (array) ($d->exam_years ?? []),
            'exYears'            => (array) ($d->ex_years ?? []),
            'programmeOptions'   => self::$programmeOptions,
            'candidatesExamined' => collect($d->candidates_examined ?? []),
            'designationOptions' => collect($d->designation_options ?? []),
            'examinerDocuments'  => collect($d->examiner_documents ?? []),
            'relatedProfiles'    => $d->relatedProfiles ?? null,
        ]);
    }




    public function quickUpdate(Request $request, $id)
    {
        $response = $this->api->post("examiners/{$id}/quick-update", [
            'field' => $request->input('field'),
            'value' => $request->input('value'),
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function sendConfirmationEmail(Request $request, $id)
    {
        $response = $this->api->post("examiners/{$id}/send-confirmation", $this->emailSenderPayload());

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Failed to send email.');
        }

        return redirect()->back()->with('success', $response->json('message') ?? 'Confirmation email sent.');
    }

    public function delete($id)
    {
        $response = $this->api->post("examiners/{$id}/deactivate", []);

        if ($response->failed()) {
            return redirect('admin/exams/examiners')->with('error', $response->json('message') ?? 'Failed to deactivate examiner.');
        }

        return redirect('admin/exams/examiners')->with('success', $response->json('message') ?? 'Examiner successfully deactivated');
    }


    public function resetExaminerConfirmation(Request $request, $id)
    {
        $back = $request->input('back', url('admin/exams/examiner-confirmation'));

        $response = $this->api->post("examiners/{$id}/reset-confirmation", $request->only(['type']));

        if ($response->failed()) {
            return redirect($back)->with('error', $response->json('message') ?? 'Reset failed.');
        }

        return redirect($back)->with('success', $response->json('message') ?? 'Reset successful.');
    }

    public function destroyExaminer(Request $request, $id)
    {
        $back = $request->input('back', 'admin/exams/examiners');

        $response = $this->api->post("examiners/{$id}/destroy", $request->only(['type']));

        if ($response->failed()) {
            return redirect($back)->with('error', $response->json('message') ?? 'Delete failed.');
        }

        return redirect('admin/exams/examiners')->with('success', $response->json('message') ?? 'Examiner deleted.');
    }

    public function destroyAttendanceRecord(Request $request, $id)
    {
        $date = $request->input('date', \Carbon\Carbon::today()->toDateString());
        $this->api->delete("examiners/attendance/{$id}");

        return redirect(url('admin/exams/attendance') . '?date=' . $date)
            ->with('success', 'Attendance record deleted.');
    }

    public function destroyAttendanceByDate(Request $request)
    {
        $date = $request->input('date');
        if (!$date) {
            return redirect(url('admin/exams/attendance'))->with('error', 'No date specified.');
        }

        $this->api->delete('examiners/attendance/date?date=' . urlencode($date));

        return redirect(url('admin/exams/attendance') . '?date=' . $date)
            ->with('success', "Attendance records deleted for {$date}.");
    }

    /**
     * Generate visual report for examiner confirmations
     */
    public function generateVisualReport(Request $request)
    {
        $response = $this->api->get('examiners/visual-report', $request->only(['year_id', 'filter']));
        $d = $response->object();

        return view('admin.exams.visual_report', [
            'availabilityData'  => (array) ($d->availability_data ?? []),
            'participationData' => (array) ($d->participation_data ?? []),
            'countryData'       => (array) ($d->country_data ?? []),
            'header_title'      => 'Examiner Visual Report',
            'allYears'          => collect($d->all_years ?? []),
            'selectedYearId'    => $d->selected_year_id ?? 0,
            'selectedYearName'  => $d->selected_year_name ?? date('Y'),
            'filterMode'        => $d->filter_mode ?? 'all',
            'totalShown'        => $d->total_shown ?? 0,
            'invitationsSent'   => $d->invitations_sent ?? 0,
            'invitationsOpened' => $d->invitations_opened ?? 0,
        ]);
    }

    public function ExaminerconfirmationView()
    {
        $response = $this->api->get('examiners/confirmation');
        $d = $response->object();

        return view('admin.exams.examiner_confirmation', [
            'getExaminers' => collect($d->examiners ?? []),
            'header_title' => 'Examiner Confirmation',
        ]);
    }

    // ── Shared programme list ─────────────────────────────────────────────────
    private static array $programmeOptions = [
        'MCS',
        'FCS General Surgery',
        'FCS Cardiothoracic Surgery',
        'FCS Urology',
        'FCS Paediatric Surgery',
        'FCS Otorhinolaryngology',
        'FCS Plastic Surgery',
        'FCS Neurosurgery',
        'FCS Orthopaedic Surgery',
        'FCS Paediatric Orthopaedic Surgery',
    ];

    /**
     * Sync examiner_participations for the past years shown in the admin form.
     *
     * @param int   $examinerModelId   examiners.id
     * @param array $selectedYearNames ['2024','2025',…]
     * @param array $yearProgrammes    ['2024'=>['MCS'],'2025'=>['FCS Urology','FCS General Surgery']]
     */
    /**
     * @param array $yearRoles  ['2025' => ['MCS' => 'Examiner', 'FCS Plastic Surgery' => 'Observer']]
     */
    private function syncParticipations(int $examinerModelId, array $selectedYearNames, array $yearProgrammes, array $yearRoles = []): void
    {
        $selectedYearNames = array_map('strval', $selectedYearNames);

        $lastYearName = DB::table('years')
            ->where('id', User::getCurrentYearId() - 1)
            ->value('year_name') ?? (date('Y') - 1);

        // year_name → year_id for every year the form covers.
        // year_name is an ENUM column — must compare with strings, not integers.
        $allFormYears = array_map('strval', range(2020, (int) $lastYearName));
        $formYearIds  = DB::table('years')
            ->whereIn('year_name', $allFormYears)
            ->pluck('id', 'year_name');

        // Delete participation rows for years that were unchecked
        $uncheckedYearIds = $formYearIds
            ->filter(fn($id, $name) => !in_array((string) $name, $selectedYearNames))
            ->values()
            ->toArray();

        if (!empty($uncheckedYearIds)) {
            DB::table('examiner_participations')
                ->where('exm_id', $examinerModelId)
                ->whereIn('year_id', $uncheckedYearIds)
                ->delete();
        }

        // For each checked year: delete existing rows then insert one per selected programme
        foreach ($selectedYearNames as $yearName) {
            $programmes = array_filter((array) ($yearProgrammes[(string) $yearName] ?? []));
            $yearId     = $formYearIds[(string) $yearName] ?? null;
            if (!$yearId) {
                continue;
            }

            // Always delete existing records for this year so we can replace them cleanly
            DB::table('examiner_participations')
                ->where('exm_id', $examinerModelId)
                ->where('year_id', $yearId)
                ->delete();

            foreach ($programmes as $prog) {
                $role = $yearRoles[(string)$yearName][$prog] ?? 'Examiner';
                DB::table('examiner_participations')->insert([
                    'exm_id'     => $examinerModelId,
                    'year_id'    => $yearId,
                    'specialty'  => $prog,
                    'role'       => $role,
                    'source'     => 'manual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * GET  admin/exams/bulk-upload-docs
     */
    public function bulkUploadDocsView()
    {
        $response = $this->api->get('examiners/bulk-upload-docs', request()->only(['filter']));
        $d = $response->object();

        return view('admin.exams.bulk_upload_docs', [
            'examiners'    => collect($d->examiners ?? []),
            'filter'       => $d->filter ?? 'both',
            'totalNoCv'    => $d->total_no_cv ?? 0,
            'totalNoPhoto' => $d->total_no_photo ?? 0,
        ]);
    }

    public function bulkUploadDocs(Request $request)
    {
        $files = [];

        foreach ($request->file('cv', []) as $examId => $file) {
            if ($file && $file->isValid()) {
                $files['cv'][$examId] = $file;
            }
        }

        foreach ($request->file('photo', []) as $examId => $file) {
            if ($file && $file->isValid()) {
                $files['photo'][$examId] = $file;
            }
        }

        $response = $this->api->postWithFile('examiners/bulk-upload-docs', [], $files);

        if ($response->failed()) {
            return redirect()->route('examiners.bulk.upload.docs')
                ->with('error', $response->json('message') ?? 'Upload failed.');
        }

        return redirect()->route('examiners.bulk.upload.docs')
            ->with('success', $response->json('message') ?? 'Files uploaded successfully.');
    }

    public function uploadCv(Request $request, $id)
    {
        if (! $request->hasFile('curriculum_vitae')) {
            return redirect()->back()->with('error', 'No file provided.');
        }

        $response = $this->api->postWithFile("examiners/{$id}/upload-cv", [], [
            'curriculum_vitae' => $request->file('curriculum_vitae'),
        ]);

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Upload failed.');
        }

        return redirect()->back()->with('success', 'CV uploaded successfully.');
    }

    public function saveMemo(Request $request, $id)
    {
        $response = $this->api->post("examiners/{$id}/memo", $request->only(['internal_notes']));

        if ($response->failed()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $response->json('message') ?? 'Save failed.'], 422);
            }
            return redirect()->back()->with('error', $response->json('message') ?? 'Save failed.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Memo saved.']);
        }

        return redirect()->back()->with('success', 'Memo saved successfully.');
    }

    public function uploadPhoto(Request $request, $id)
    {
        if (! $request->hasFile('passport_image')) {
            return redirect()->back()->with('error', 'No file provided.');
        }

        $response = $this->api->postWithFile("examiners/{$id}/upload-photo", [], [
            'passport_image' => $request->file('passport_image'),
        ]);

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Upload failed.');
        }

        return redirect()->back()->with('success', 'Profile photo updated successfully.');
    }

    public function uploadDocument(Request $request, $id)
    {
        if (! $request->hasFile('doc_file')) {
            return redirect()->back()->with('error', 'No file provided.');
        }

        $response = $this->api->postWithFile(
            "examiners/{$id}/documents",
            $request->only(['doc_title']),
            ['doc_file' => $request->file('doc_file')]
        );

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Upload failed.');
        }

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument(Request $request, $id, $docId)
    {
        $response = $this->api->delete("examiners/{$id}/documents/{$docId}");

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Document not found.');
        }

        return redirect()->back()->with('success', 'Document deleted.');
    }

    // ── Specialty constants ───────────────────────────────────────────────────
    /** Map messy specialty values → canonical COSECSA programme names */
    const SPECIALTY_MAP = [
        'Cardiothoracic'                          => 'FCS Cardiothoracic Surgery',
        'Cardiothoracic Surgery'                  => 'FCS Cardiothoracic Surgery',
        'Cardiothoracic/General Surgery'          => 'FCS Cardiothoracic Surgery',
        'General Surgery'                         => 'FCS General Surgery',
        'general surgery'                         => 'FCS General Surgery',
        'General'                                 => 'FCS General Surgery',
        'FCS'                                     => 'FCS General Surgery',
        'general surgery/UROLOGY'                 => 'FCS General Surgery',
        'General/ Breast Surgery'                 => 'FCS General Surgery',
        'General/ Critical Care Trauma Surgery'   => 'FCS General Surgery',
        'General/ Gastroenterologist Surgery'     => 'FCS General Surgery',
        'General/ Paediatric Surgery'             => 'FCS General Surgery',
        'General/ Plastic Surgery'                => 'FCS General Surgery',
        'General/ Surgical Oncology Surgery'      => 'FCS General Surgery',
        'General/ Urology Surgery'                => 'FCS General Surgery',
        'General/ Vascular Surgery'               => 'FCS General Surgery',
        'General/HBP/Transplant Surgery'          => 'FCS General Surgery',
        'Colon & Rectal Gen surg'                 => 'FCS General Surgery',
        'Neurosurgery'                            => 'FCS Neurosurgery',
        'Orthopaedic Surgery'                     => 'FCS Orthopaedic Surgery',
        'Orthopaedics'                            => 'FCS Orthopaedic Surgery',
        'ORTHOPEDICS'                             => 'FCS Orthopaedic Surgery',
        'FCS orthopaedics'                        => 'FCS Orthopaedic Surgery',
        'Trauma & Orthopaedic Surgery'            => 'FCS Orthopaedic Surgery',
        'Ortho/P-O'                               => 'FCS Orthopaedic Surgery',
        'Orthopaedic/ Paed-Ortho Surgery'         => 'FCS Orthopaedic Surgery',
        'Otorhinolaryngology'                     => 'FCS Otorhinolaryngology',
        'Otorhinolaryngology(ENT)'                => 'FCS Otorhinolaryngology',
        'Paediatric Surgery'                      => 'FCS Paediatric Surgery',
        'Paediatric'                              => 'FCS Paediatric Surgery',
        'Paediatric  Surgery'                     => 'FCS Paediatric Surgery',
        'FCS Paediatrics'                         => 'FCS Paediatric Surgery',
        'Paediatric Orthopaedic Surgery'          => 'FCS Paediatric Orthopaedic Surgery',
        'Plastic Surgery'                         => 'FCS Plastic Surgery',
        'Urologic Surgery'                        => 'FCS Urologic Surgery',
        'FCS Urology'                             => 'FCS Urologic Surgery',
        'FCS  Urologic Surgery'                   => 'FCS Urologic Surgery',
        'Vascular Surgery'                        => 'FCS General Surgery',
        'MCS'                                     => 'MCS',
    ];

    /**
     * GET admin/exams/mass-update-specialty
     */
    // ── Designation Options Admin ────────────────────────────────────────────

    public function designationsIndex()
    {
        $response = $this->api->get('settings/designations');
        $d = $response->object();

        return view('admin.exams.designations', ['options' => collect($d->options ?? [])]);
    }

    public function designationsStore(Request $request)
    {
        $response = $this->api->post('settings/designations', $request->only(['name']));

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Error saving designation.');
        }

        return back()->with('success', $response->json('message') ?? 'Designation added.');
    }

    public function designationsDelete($id)
    {
        $response = $this->api->delete("settings/designations/{$id}");

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Could not delete designation.');
        }

        return back()->with('success', $response->json('message') ?? 'Designation deleted.');
    }

    public function massUpdateSpecialtyForm()
    {
        $response = $this->api->get('examiners/mass-update-specialty');
        $d = $response->object();

        return view('admin.exams.mass_update_specialty', [
            'header_title' => 'Mass Update Examiner Specialty',
            'current'      => collect($d->current ?? []),
            'programmes'   => collect($d->programmes ?? []),
        ]);
    }

    public function massUpdateSpecialtyProcess(Request $request)
    {
        $response = $this->api->post('examiners/mass-update-specialty', $request->except('_token'));

        if ($response->failed()) {
            return redirect()->route('exams.mass.specialty')
                ->with('error', $response->json('message') ?? 'Update failed.');
        }

        return redirect()->route('exams.mass.specialty')
            ->with('success', $response->json('message') ?? 'Specialty updated.');
    }

    public function manageParticipation(Request $request, $examiner_id)
    {
        $response = $this->api->post("examiners/{$examiner_id}/manage-participation", $request->except('_token'));

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Failed to update participation.');
        }

        $from = $request->input('from', 'admin/exams/examiners');

        return redirect("admin/exams/view_examiner/{$examiner_id}?from=" . urlencode($from))
            ->with('success', 'Participation history updated successfully');
    }

    /**
     * Build a lightweight single-examiner object for attendance methods.
     * Avoids loading all 700+ examiners via User::getExaminers().
     */
    private function fetchExaminerForAttendance($examiner_id)
    {
        $yearId = User::getCurrentYearId();

        $examiner = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('countries', 'countries.id', '=', 'examiners.country_id')
            ->leftJoin('exams_shifts', function ($join) use ($yearId) {
                $join->on('exams_shifts.exm_id', '=', 'examiners.id')
                     ->where('exams_shifts.year_id', '=', $yearId);
            })
            ->leftJoin('exams_groups', function ($join) use ($yearId) {
                $join->on('exams_groups.exm_id', '=', 'examiners.id')
                     ->where('exams_groups.year_id', '=', $yearId);
            })
            ->leftJoin('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
            ->where('examiners.id', $examiner_id)
            ->select(
                'examiners.id as examin_id',
                'examiners.user_id',
                'users.name as examiner_name',
                'users.email',
                'examiners.specialty',
                'examiners.subspecialty',
                'examiners.country_id',
                'countries.country_name',
                'examiners.mobile',
                'examiners.curriculum_vitae',
                'examiners.passport_image',
                'examiners.examiner_id',
                'examiners_groups.id as group_id',
                'examiners_groups.group_name',
                'exams_shifts.shift as shift_num'
            )
            ->first();

        if ($examiner) {
            $examiner->shift = $examiner->shift_num !== null
                ? User::getShiftName($examiner->shift_num)
                : null;
        }

        return $examiner;
    }

    public function showAttendanceConfirmation($examiner_id)
    {
        $response = $this->api->get("examiners/{$examiner_id}/confirm-attendance");

        if ($response->status() === 404) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }

        $d = $response->object();

        return view('admin.exams.confirm_attendance', [
            'header_title'       => 'Confirm Attendance Registration',
            'examiner'           => $d->examiner,
            'already_registered' => $d->already_registered ?? false,
            'registration_time'  => $d->registration_time ?? null,
        ]);
    }

    public function confirmAttendanceRegistration(Request $request, $examiner_id)
    {
        $response = $this->api->post("examiners/{$examiner_id}/confirm-attendance", []);

        if ($response->status() === 200 && str_contains($response->json('status') ?? '', 'already')) {
            return redirect()->back()->with('info', $response->json('message') ?? 'Already registered.');
        }

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Error registering attendance.');
        }

        return redirect()->back()->with('success', $response->json('message') ?? 'Attendance registered successfully.');
    }

    public function attendanceList(Request $request)
    {
        if ($request->get('export') === '1') {
            $response = $this->api->get('examiners/attendance', $request->only(['date', 'export']));
            $date = $request->get('date', 'all');

            return response($response->body(), 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"attendance_{$date}.csv\"",
            ]);
        }

        $response = $this->api->get('examiners/attendance', $request->only(['date']));
        $d = $response->object();

        return view('admin.exams.attendance_list', [
            'header_title'   => 'Examiner Attendance',
            'records'        => collect($d->records ?? []),
            'dateFilter'     => $d->date_filter ?? null,
            'availableDates' => collect($d->available_dates ?? []),
            'totalRecords'   => $d->total_records ?? 0,
        ]);
    }


    /**
     * Resolve the year filter from the request.
     * Returns [$yearId, $yearName, $allYears].
     */
    private function resolveYear(Request $request): array
    {
        $allYears = DB::table('years')->orderByDesc('id')->get(['id', 'year_name']);
        if ($request->input('year_id')) {
            $yearId = (int)$request->input('year_id');
        } else {
            // Default to the most recent year that has any OSCE exam data,
            // falling back to the current calendar year if none found.
            $examTables = ['mcs_results','gs_results','orthopaedic_results',
                           'cardiothoracic_results','urology_results','paediatric_results',
                           'ent_results','plastic_surgery_results','neurosurgery_results',
                           'paediatric_orthopaedics_results'];
            $latestYearId = null;
            foreach ($examTables as $tbl) {
                $max = DB::table($tbl)->max('exam_year');
                if ($max && ($latestYearId === null || $max > $latestYearId)) {
                    $latestYearId = $max;
                }
            }
            $yearId = $latestYearId ?? User::getCurrentYearId();
        }
        $yearRow  = $allYears->firstWhere('id', $yearId);
        $yearName = $yearRow ? $yearRow->year_name : (string)date('Y');
        return [$yearId, $yearName, $allYears];
    }

    public function adminResults(Request $request)
    {
        $response = $this->api->get('exam-results/mcs', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.exam_results', [
            'header_title'     => 'MCS Results',
            'getResults'       => collect($d->results ?? []),
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function overallResults(Request $request)
    {
        $response = $this->api->get('exam-results/overall', $request->only(['year', 'programme_id', 'result']));
        $d = $response->object();

        return view('admin.exams.overall_results', [
            'header_title'      => 'Overall Exam Results',
            'results'           => collect($d->results ?? []),
            'summary'           => (array) ($d->summary ?? []),
            'programmes'        => collect($d->programmes ?? []),
            'years'             => collect($d->years ?? []),
            'selectedYear'      => $d->selected_year ?? null,
            'selectedProgramme' => $d->selected_programme ?? null,
            'selectedResult'    => $d->selected_result ?? null,
        ]);
    }

    public function gsResults(Request $request)
    {
        $response = $this->api->get('exam-results/gs', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.gs_results', [
            'header_title'     => 'GS Results',
            'getResults'       => collect($d->results ?? []),
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function cardiothoracicResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/1/cardiothoracic_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_cardiothoracic_results', [
            'header_title'     => 'FCS Cardiothoracic Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Cardiothoracic Surgery',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function urologyResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/9/urology_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_urology_results', [
            'header_title'     => 'FCS Urology Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Urology',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function paediatricResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/7/paediatric_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_paediatric_results', [
            'header_title'     => 'FCS Paediatric Surgery Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Paediatric Surgery',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function entResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/5/ent_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_ent_results', [
            'header_title'     => 'FCS ENT Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'ENT Surgery',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function plasticSurgeryResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/8/plastic_surgery_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_plastic_surgery_results', [
            'header_title'     => 'FCS Plastic Surgery Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Plastic Surgery',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function neurosurgeryResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/3/neurosurgery_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_neurosurgery_results', [
            'header_title'     => 'FCS Neurosurgery Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Neurosurgery',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function orthopaedicsResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/4/orthopaedic_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_orthopaedics_results', [
            'header_title'     => 'FCS Orthopaedics Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Orthopaedics',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function paediatricOrthopaedicsResults(Request $request)
    {
        $response = $this->api->get('exam-results/fcs/6/paediatric_orthopaedics_results', $request->only(['year_id']));
        $d = $response->object();

        return view('admin.exams.fcs_paediatric_ortho_results', [
            'header_title'     => 'FCS Paediatric Orthopaedics Results',
            'getResults'       => collect($d->results ?? []),
            'programmeName'    => 'Paediatric Orthopaedics',
            'allYears'         => collect($d->all_years ?? []),
            'selectedYearId'   => $d->selected_year_id ?? 0,
            'selectedYearName' => $d->selected_year_name ?? date('Y'),
        ]);
    }

    public function viewFcsStationResults($candidate_id, $station_id, $exam_format, $table)
    {
        $response = $this->api->get(
            "exam-results/fcs-station/{$candidate_id}/{$station_id}/{$exam_format}/{$table}"
        );
        $d = $response->object();

        $candidateResult = $d->candidate_result ?? null;
        $allResults      = collect($d->all_results ?? []);
        $header_title    = ucfirst($exam_format) . ' Station Results';

        return view('admin.exams.fcs_station_results', compact('candidateResult', 'allResults', 'header_title'));
    }

    public function examinerCandidateResults($examiner_id, $candidate_id)
    {
        $response = $this->api->get(
            "exam-results/examiner/{$examiner_id}/candidate/{$candidate_id}"
        );
        return response()->json($response->json());
    }

    public function viewCandidateStationResult($candidate_id, $station_id)
    {
        $response = $this->api->get("exam-results/mcs-station/{$candidate_id}/{$station_id}");
        $d = $response->object();

        $candidateResult = $d->candidate_result ?? null;
        $allResults      = collect($d->all_results ?? []);
        $header_title    = 'Station Results';

        return view('admin.exams.station_results', compact('candidateResult', 'allResults', 'header_title'));
    }

    public function viewGsStationResult($candidate_id, $station_id)
    {
        $response = $this->api->get("exam-results/gs-station/{$candidate_id}/{$station_id}");
        $d = $response->object();

        $candidateResult = $d->candidate_result ?? null;
        $allResults      = collect($d->all_results ?? []);
        $header_title    = 'Station Results';

        return view('admin.exams.gs_station_results', compact('candidateResult', 'allResults', 'header_title'));
    }

    // Function to change password
    public function changePassword()
    {
        $data['header_title'] = "Change Password";
        return view('examiner.change_password', $data);
    }

    public function updatePassword(Request $request)
    {
        $user = User::getSingleId(Auth::user()->id);
        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return redirect()->back()->with('success', "Password successfully updated");
        } else {
            return redirect()->back()->with('error', "Old Password is not correct");
        }
    }

    public function examinerProfile()
    {
        $user = Auth::user();

        // Get examiner details using the same method as admin
        $examiner = User::getExaminers()->firstWhere('user_id', $user->id);

        if (!$examiner) {
            return redirect('examiner/dashboard')->with('error', 'Examiner profile not found');
        }

        // Generate QR code for the examiner using examiner-specific route
        $baseUrl = request()->getSchemeAndHttpHost();
        // $baseUrl = 'http://localhost/cosecsa';

        $confirmationUrl = $baseUrl . '/examiner/confirm-attendance/' . $examiner->examin_id;

        // Generate QR code with the confirmation URL
        $qrCode = \QrCode::size(70)->generate($confirmationUrl);

        // ── Participation history (same logic as admin viewExaminer) ──────────
        $examinerId = $examiner->examin_id;
        $history    = DB::table('examiners_history')->where('exm_id', $examinerId)->first();

        $rawYears     = $history->examination_years ?? null;
        $decodedYears = json_decode($rawYears, true);
        if (is_string($decodedYears)) { $decodedYears = json_decode($decodedYears, true); }
        $examinedYears = is_array($decodedYears) ? $decodedYears : [];

        $hasParticipations = \Illuminate\Support\Facades\Schema::hasTable('examiner_participations');
        $allEP = [];
        if ($hasParticipations) {
            $epRows = DB::table('examiner_participations')
                ->join('years', 'years.id', '=', 'examiner_participations.year_id')
                ->where('examiner_participations.exm_id', $examinerId)
                ->whereNotNull('examiner_participations.specialty')
                ->select('years.year_name', 'examiner_participations.specialty', 'examiner_participations.role')
                ->get();
            foreach ($epRows as $row) {
                $allEP[(string)$row->year_name][$row->specialty] = $row->role ?: null;
            }
        }

        $defaultRole  = ($examiner->role_id == 1) ? 'Examiner' : 'Observer';
        $yearProgrammes = [];
        $yearRoles      = [];

        foreach ($examinedYears as $yrName) {
            $yearRow = DB::table('years')->where('year_name', (string)$yrName)->first();
            if (!$yearRow) continue;
            $yid = $yearRow->id;

            $programmes = [];
            $roles      = [];

            if (!empty($allEP[(string)$yrName])) {
                foreach ($allEP[(string)$yrName] as $spec => $role) {
                    $programmes[] = $spec;
                    $roles[$spec] = $role ?? $defaultRole;
                }
            }

            if (!in_array('MCS', $programmes) &&
                DB::table('mcs_results')->where('examiner_id', $examinerId)->where('exam_year', $yid)->exists()) {
                $programmes[] = 'MCS';
                $roles['MCS'] = $defaultRole;
            }

            $hasFCS = !empty(array_filter($programmes, fn($p) => stripos($p, 'FCS') !== false));
            if (!$hasFCS &&
                DB::table('gs_results')->where('examiner_id', $examinerId)->where('exam_year', $yid)->exists()) {
                $programmes[] = 'FCS General Surgery';
                $roles['FCS General Surgery'] = $defaultRole;
            }

            $yearProgrammes[(string)$yrName] = array_unique($programmes);
            $yearRoles[(string)$yrName]      = $roles;
        }

        $data = [
            'header_title'   => 'Profile Settings',
            'examiner'       => $examiner,
            'getCountry'     => Country::getCountry(),
            'groups'         => DB::table('examiners_groups')->select('id', 'group_name')->get(),
            'qrCode'         => $qrCode,
            'exYears'        => $examinedYears,
            'yearProgrammes' => $yearProgrammes,
            'yearRoles'      => $yearRoles,
        ];

        return view('examiner.profile_settings', $data);
    }

    // Add this new method for generating examiner badge
    public function examinerBadge()
    {
        $user = Auth::user();

        // Get examiner details
        $examiner = User::getExaminers()->firstWhere('user_id', $user->id);

        if (!$examiner) {
            return redirect('examiner/profile_settings')->with('error', 'Examiner profile not found');
        }

        // Generate QR code using examiner-specific route
        $baseUrl = request()->getSchemeAndHttpHost();
        $confirmationUrl = $baseUrl . '/examiner/confirm-attendance/' . $examiner->examin_id;
        $qrCode = \QrCode::size(70)->generate($confirmationUrl);

        $data = [
            'header_title' => 'ID Badge',
            'examiner' => $examiner,
            'qrCode' => $qrCode
        ];

        return view('examiner.badge', $data);
    }

    /**
     * Show attendance confirmation page after QR scan (Examiner-specific version)
     */
    public function showExaminerAttendanceConfirmation($examiner_id)
    {
        $examiner = $this->fetchExaminerForAttendance($examiner_id);

        if (!$examiner) {
            return redirect('examiner/dashboard')->with('error', 'Examiner not found');
        }

        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $data = [
            'header_title'      => 'Confirm Attendance Registration',
            'examiner'          => $examiner,
            'already_registered'=> $existingAttendance ? true : false,
            'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null,
        ];

        return view('examiner.confirm_attendance', $data);
    }

    /**
     * Process attendance registration after confirmation (Examiner-specific version)
     */
    public function confirmExaminerAttendanceRegistration(Request $request, $examiner_id)
    {
        try {
            $examiner = $this->fetchExaminerForAttendance($examiner_id);

            if (!$examiner) {
                return redirect()->back()->with('error', 'Examiner not found');
            }

            $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingAttendance) {
                return redirect()->back()->with(
                    'info',
                    'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s')
                );
            }

            $attendance = Attendance::create([
                'user_id'          => $examiner->user_id ?? null,
                'examiner_id'      => $examiner->examin_id ?? null,
                'country_id'       => $examiner->country_id ?? null,
                'group_id'         => $examiner->group_id ?? null,
                'mobile'           => $examiner->mobile ?? null,
                'specialty'        => $examiner->specialty ?? null,
                'subspecialty'     => $examiner->subspecialty ?? null,
                'shift'            => $examiner->shift_num ?? null,
                'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
                'passport_image'   => $examiner->passport_image ?? null,
            ]);

            return redirect()->back()->with(
                'success',
                'Attendance registered successfully for ' . $examiner->examiner_name . ' at ' . $attendance->created_at->format('H:i:s')
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error registering attendance: ' . $e->getMessage());
        }
    }

    // ── Email template editor ─────────────────────────────────────────────────

    public function emailTemplate()
    {
        $response = $this->api->get('examiners/email-template');
        $d = $response->object();

        return view('admin.exams.email_template', ['template' => $d->template ?? null]);
    }

    public function saveEmailTemplate(Request $request)
    {
        $response = $this->api->post('examiners/email-template', $request->only(['subject', 'body']));

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Error saving template.');
        }

        return redirect()->back()->with('success', 'Email template saved successfully.');
    }

    public function sendBulkEmail(Request $request)
    {
        $response = $this->api->post('examiners/send-bulk-email', array_merge(
            $request->except('_token'),
            $this->emailSenderPayload(),
        ));

        if ($response->failed()) {
            return redirect()->back()->with('error', $response->json('message') ?? 'Bulk email failed.');
        }

        return redirect()->back()->with('success', $response->json('message') ?? 'Bulk email sent.');
    }

    private function emailSenderPayload(): array
    {
        $sender = Auth::user();

        return [
            'sender_name'  => $sender?->name,
            'sender_title' => $sender?->signature_title,
            'sender_phone' => $sender?->signature_phone,
            'sender_email' => $sender?->personal_email ?: $sender?->email,
        ];
    }

    // ── Email open tracking pixel ─────────────────────────────────────────────

    public function trackEmailOpen(string $token)
    {
        $apiResponse = $this->api->getPublic("public/examiners/track-open/{$token}");

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($gif, 200, [
            'Content-Type'  => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }

    // ── Public examiner availability form ────────────────────────────────────

    /**
     * Show the public availability confirmation form.
     * No authentication required — the URL can be shared directly with examiners.
     */
    public function availabilityForm()
    {
        $response  = $this->api->getPublic('public/examiner-availability');
        $d         = $response->object();

        $year      = $d->year ?? (int) date('Y');
        $examiners = collect($d->examiners ?? []);

        return view('public.examiner_availability', compact('year', 'examiners'));
    }

    public function availabilitySubmit(Request $request)
    {
        $request->validate([
            'exm_id'              => 'required|integer',
            'exam_availability'   => 'required|array|min:1',
            'exam_availability.*' => 'in:MCS,FCS,Tentative FCS,Tentative MCS,Tentative,Not Available',
            'mcs_shift'           => 'nullable|in:1,2,3',
        ]);

        $payload = array_merge(
            $request->only(['exm_id', 'mcs_shift']),
            ['exam_availability' => $request->input('exam_availability', [])]
        );

        $response = $this->api->postPublic('public/examiner-availability', $payload);

        if ($response->failed()) {
            return back()->withErrors($response->json('errors') ?? [])->with('error', $response->json('message') ?? 'Submission failed.');
        }

        return back()->with('success', $response->json('message'));
    }

    public function examinerChangePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->back()->with('error', 'Old password is incorrect');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function examinerEdit($id)
    {
        // Get the current logged-in examiner
        $currentExaminer = Auth::user();

        // Security check: make sure examiner can only edit their own profile
        $examiner = User::getExaminers()->where('examin_id', $id)->first();

        if (!$examiner || $examiner->user_id != $currentExaminer->id) {
            return redirect('examiner/profile_settings')->with('error', 'Unauthorized access');
        }

        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Profile";
        $data['examiner'] = $examiner;

        // Retrieve all groups and pass them to the view
        $data['groups'] = DB::table('examiners_groups')->select('id', 'group_name')->get();

        // Dynamic exam years: all completed years up to (but not including) current year
        $data['examYears'] = DB::table('years')
            ->where('id', '<', User::getCurrentYearId())
            ->orderByDesc('id')
            ->pluck('year_name');

        return view('examiner.edit_info', $data);
    }

    public function examinerUpdate(Request $request, $id)
    {
        $currentExaminer = Auth::user();
        $examiner = ExamsModel::find($id);

        if (!$examiner) {
            return redirect('examiner/profile_settings')->with('error', 'Examiner not found');
        }

        if ($examiner->user_id != $currentExaminer->id) {
            return redirect('examiner/profile_settings')->with('error', 'Unauthorized access');
        }

        // Maximum selectable year is always the last completed exam year
        $lastYearName = DB::table('years')
            ->where('id', User::getCurrentYearId() - 1)
            ->value('year_name') ?? (date('Y') - 1);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|min:6',
            'gender' => 'nullable|in:Male,Female',
            'curriculum_vitae' => 'nullable|file|mimes:pdf,doc,docx|max:3072', // 3MB = 3072 KB
            'passport_image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024', // 1MB = 1024 KB
            'examiner_id' => 'nullable|string|max:255',
            'group_id' => 'nullable|integer',
            'specialty' => 'nullable|string|max:255',
            'subspecialty' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'mobile' => 'nullable|string|max:20',
            'exam_availability' => 'nullable|array',
            'exam_availability.*' => 'in:MCS,FCS,Not Available',
            'shift' => 'nullable|in:1,2,3',
            'virtual_mcs_participated' => 'nullable|in:Yes,No',
            'fcs_participated' => 'nullable|in:Yes,No',
            'participation_type' => 'nullable|in:Examiner,Observer,None',
            'hospital_type' => 'nullable|in:Teaching Hospital,Non Teaching',
            'hospital_name' => 'nullable|string|max:255',
            'examination_years' => 'nullable|array',
            'examination_years.*' => 'integer|min:2020|max:' . $lastYearName,
        ], [
            // Custom error messages
            'curriculum_vitae.max' => 'The CV file must not be larger than 3MB.',
            'passport_image.max' => 'The profile image must not be larger than 1MB.',
            'curriculum_vitae.mimes' => 'The CV must be a PDF, DOC, or DOCX file.',
            'passport_image.mimes' => 'The profile image must be a JPEG, PNG, or JPG file.',
        ]);

        // dd(request()->all());

        try {
            \DB::beginTransaction();

            $user = User::find($examiner->user_id);
            if ($user) {
                $user->name = $validated['name'];
                $user->email = $validated['email'];

                if (!empty($validated['password'])) {
                    $user->password = Hash::make($validated['password']);
                }
                $user->save();
            }

            // ✅ Upload CV with user ID prefix
            if ($request->hasFile('curriculum_vitae')) {
                if ($examiner->curriculum_vitae && Storage::disk('public')->exists($examiner->curriculum_vitae)) {
                    Storage::disk('public')->delete($examiner->curriculum_vitae);
                }

                $file = $request->file('curriculum_vitae');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $sanitizedName = Str::slug($originalName);
                $extension = $file->getClientOriginalExtension();
                $finalName = $currentExaminer->id . '-' . $sanitizedName . '.' . $extension;

                $path = $file->storeAs('documents/cvs', $finalName, 'public');
                $examiner->curriculum_vitae = $path;
            }

            // ✅ Upload passport image with user ID prefix
            if ($request->hasFile('passport_image')) {
                if ($examiner->passport_image && Storage::disk('public')->exists($examiner->passport_image)) {
                    Storage::disk('public')->delete($examiner->passport_image);
                }

                $file = $request->file('passport_image');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $sanitizedName = Str::slug($originalName);
                $extension = $file->getClientOriginalExtension();
                $finalName = $currentExaminer->id . '-' . $sanitizedName . '.' . $extension;

                $passportPath = $file->storeAs('documents/passports', $finalName, 'public');
                $examiner->passport_image = $passportPath;
            }

            $examiner->gender = $validated['gender'] ?? $examiner->gender;
            $examiner->examiner_id = $validated['examiner_id'] ?? $examiner->examiner_id;
            $examiner->country_id = $validated['country_id'];
            $examiner->mobile = $validated['mobile'] ?? $examiner->mobile;
            $examiner->specialty = $validated['specialty'] ?? $examiner->specialty;
            $examiner->subspecialty = $validated['subspecialty'] ?? $examiner->subspecialty;

            if (isset($validated['participation_type'])) {
                if ($validated['participation_type'] === 'Examiner') {
                    $examiner->role_id = 1;
                } elseif ($validated['participation_type'] === 'Observer') {
                    $examiner->role_id = 2;
                } else {
                    $examiner->role_id = 3;
                }
            }

            $examiner->save();

            // Examiner history
            $historyData = [];

            if ($request->has('exam_availability') && is_array($request->exam_availability)) {
                $availability = $request->exam_availability;

                // If "Not Available" is selected, ignore all others
                if (in_array('Not Available', $availability)) {
                    $availability = ['Not Available'];
                }

                $historyData['exam_availability']    = $availability;
                $historyData['availability_year_id'] = User::getCurrentYearId();
            }

            if (isset($validated['virtual_mcs_participated'])) {
                $historyData['virtual_mcs_participated'] = $validated['virtual_mcs_participated'];
            }

            if (isset($validated['fcs_participated'])) {
                $historyData['fcs_participated'] = $validated['fcs_participated'];
            }

            if (isset($validated['hospital_type'])) {
                $historyData['hospital_type'] = $validated['hospital_type'];
            }

            if (isset($validated['hospital_name'])) {
                $historyData['hospital_name'] = $validated['hospital_name'];
            }

            if (isset($validated['examination_years'])) {
                $historyData['examination_years'] = $validated['examination_years'];
            }

            if (!empty($historyData)) {
                $historyData['source'] = 'self';
                \App\Models\ExaminerHistory::updateOrCreate(
                    ['exm_id' => $examiner->id],
                    $historyData
                );
            }

            $currentYear = User::getCurrentYearId();

            if (isset($validated['group_id'])) {
                \DB::table('exams_groups')
                    ->where('exm_id', $examiner->id)
                    ->where('year_id', $currentYear)
                    ->delete();

                \DB::table('exams_groups')->insert([
                    'exm_id' => $examiner->id,
                    'group_id' => $validated['group_id'],
                    'year_id' => $currentYear,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if (isset($validated['shift'])) {
                \DB::table('exams_shifts')
                    ->where('exm_id', $examiner->id)
                    ->where('year_id', $currentYear)
                    ->delete();

                \DB::table('exams_shifts')->insert([
                    'exm_id' => $examiner->id,
                    'year_id' => $currentYear,
                    'shift' => $request->filled('shift') ? $validated['shift'] : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            \DB::commit();
            return redirect('examiner/profile_settings')->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Examiner update failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the profile. Please try again. Error: ' . $e->getMessage());
        }
    }
}
