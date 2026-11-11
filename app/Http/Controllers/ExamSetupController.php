<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

// Examinations > Exam Groups & Stations (admin/exams/setup): the exam groups with their candidates
// and examiners, and the stations of each exam type that the examiner forms offer. The data and the
// rules live in cosecsa-api (internal/exam-setup/*); this controller is the page and a thin JSON proxy.
// Access follows the examiners permission (admin/exams prefix): GET needs view, POST needs manage.
class ExamSetupController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index()
    {
        $response = $this->api->get('exam-setup/overview');
        if ($response->failed()) {
            return redirect('admin/exams/examiners')->with('error', $response->json('message') ?? 'Could not load the exam set-up.');
        }

        $control = $this->api->get('exam-setup/exam-control');

        return view('admin.exams.setup', $response->json() + ['header_title' => 'Exam Groups & Stations', 'exam_control' => $control->successful() ? $control->json() : null]);
    }

    public function overview()
    {
        return $this->reply($this->api->get('exam-setup/overview'));
    }

    public function currentExamUpdate(Request $request)
    {
        return $this->reply($this->api->post('exam-setup/current-exam', ['exam_type' => $request->input('exam_type')]));
    }

    // Start the exam / lock it again / set the start time shown to examiners.
    public function examControl()
    {
        return $this->reply($this->api->get('exam-setup/exam-control'));
    }

    public function examControlUpdate(Request $request)
    {
        return $this->reply($this->api->post('exam-setup/exam-control', array_filter($request->only('action', 'scheduled_for'), fn ($v) => $v !== null)));
    }

    public function group($id)
    {
        return $this->reply($this->api->get("exam-setup/groups/{$id}"));
    }

    public function groupStore(Request $request)
    {
        return $this->reply($this->api->post('exam-setup/groups', $request->only('group_name')));
    }

    public function groupUpdate(Request $request, $id)
    {
        return $this->reply($this->api->post("exam-setup/groups/{$id}", $request->only('group_name')));
    }

    public function groupDestroy($id)
    {
        return $this->reply($this->api->delete("exam-setup/groups/{$id}"));
    }

    public function examinersAdd(Request $request, $id)
    {
        return $this->reply($this->api->post("exam-setup/groups/{$id}/examiners", ['examiner_ids' => (array) $request->input('examiner_ids', [])]));
    }

    public function examinerRemove($id, $examinerId)
    {
        return $this->reply($this->api->delete("exam-setup/groups/{$id}/examiners/{$examinerId}"));
    }

    public function candidatesMove(Request $request, $id)
    {
        return $this->reply($this->api->post("exam-setup/groups/{$id}/move-candidates", [
            'candidate_ids' => (array) $request->input('candidate_ids', []), 'to_group_id' => $request->input('to_group_id'),
        ]));
    }

    public function stations(Request $request)
    {
        return $this->reply($this->api->get('exam-setup/stations', ['exam_type' => $request->input('exam_type', 'mcs')]));
    }

    public function stationStore(Request $request)
    {
        return $this->reply($this->api->post('exam-setup/stations', $this->stationPayload($request)));
    }

    public function stationUpdate(Request $request, $id)
    {
        return $this->reply($this->api->post("exam-setup/stations/{$id}", $this->stationPayload($request)));
    }

    public function stationDestroy($id)
    {
        return $this->reply($this->api->delete("exam-setup/stations/{$id}"));
    }

    private function stationPayload(Request $request): array
    {
        return $request->only('exam_type', 'station_number', 'name', 'max_total') + [
            'questions' => (array) $request->input('questions', []),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function reply($response)
    {
        return response()->json($response->json() ?? ['message' => 'No answer from the server.'], $response->status());
    }
}
