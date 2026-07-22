<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\ProgressReportAccessRequest;
use App\Models\ProgressReportParticipant;
use App\Models\ProgressReportPeriod;
use App\Models\ProgressReportSetting;
use App\Models\ProgressReportTask;
use App\Models\ProgressReportTaskRevision;
use App\Models\ProgressReportTaskTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProgressiveReportController extends Controller
{
    public function index()
    {
        $periods = ProgressReportPeriod::withCount(['participants as total_participants', 'participants as submitted_count' => function ($q) {
            $q->where('status', 'submitted');
        }])->orderByDesc('period_month')->get();

        return view('progressive_reports.index', [
            'header_title'  => 'Progressive Reports',
            'periods'       => $periods,
            'canManage'     => $this->canManage(),
            'settings'      => ProgressReportSetting::current(),
            'currentPeriod' => ProgressReportPeriod::where('status', 'open')->orderByDesc('period_month')->first(),
        ]);
    }

    // A focused view showing only the current user's own section — even
    // for a Super Admin / Administrative Officer, who otherwise see every
    // section under "Manage Progress Reports". Reuses the same template
    // as show(), just with the participants collection narrowed to one
    // and the period-level manage actions (Consolidate/Share with CEO)
    // hidden since those apply to the whole report, not a single section.
    // Accepts ?period_id=X so a past (or future) month can be selected
    // instead of always defaulting to the latest open period.
    public function myReport(Request $request)
    {
        $myPeriods = ProgressReportPeriod::whereHas('participants', fn ($q) => $q->where('user_id', Auth::id()))
            ->orderByDesc('period_month')->get();

        if ($myPeriods->isEmpty()) {
            $myTemplates = ProgressReportTaskTemplate::where('user_id', Auth::id())->orderBy('sort_order')->get();
            $isManager = $this->canManage();
            $canApproveAccess = Auth::user()->canApproveProgressReportAccess();

            return view('progressive_reports.show', [
                'header_title' => 'My Progress Report',
                'period'       => null,
                'myPeriods'    => $myPeriods,
                'selectedPeriodId' => null,
                'canManage'    => false,
                'isManager'    => $isManager,
                'canApproveAccess' => $canApproveAccess,
                'myUserId'     => Auth::id(),
                'backUrl'      => url('admin/dashboard'),
                'myTemplates'  => $myTemplates,
                'templatesByUser' => $myTemplates->where('is_active', true)->groupBy('user_id'),
                'pendingAccessRequests' => $canApproveAccess
                    ? ProgressReportAccessRequest::with(['participant.period', 'requester'])->where('status', 'pending')->latest()->get()
                    : collect(),
            ]);
        }

        $requestedId = $request->query('period_id');
        $selected = $requestedId ? $myPeriods->firstWhere('id', (int) $requestedId) : null;
        $selected = $selected ?? $myPeriods->firstWhere('status', 'open') ?? $myPeriods->first();

        $period = ProgressReportPeriod::with(['participants' => function ($q) {
            $q->where('user_id', Auth::id());
        }, 'participants.user', 'participants.tasks', 'participants.pendingAccessRequest'])->findOrFail($selected->id);

        $myTemplates = ProgressReportTaskTemplate::where('user_id', Auth::id())->orderBy('sort_order')->get();
        $isManager = $this->canManage();
        $canApproveAccess = Auth::user()->canApproveProgressReportAccess();

        return view('progressive_reports.show', [
            'header_title'     => 'My Progress Report',
            'period'           => $period,
            'myPeriods'        => $myPeriods,
            'selectedPeriodId' => $period->id,
            'canManage'        => false,
            'isManager'        => $isManager,
            'canApproveAccess' => $canApproveAccess,
            'myUserId'         => Auth::id(),
            'backUrl'          => url('admin/dashboard'),
            'myTemplates'      => $myTemplates,
            'templatesByUser'  => $myTemplates->where('is_active', true)->groupBy('user_id'),
            'pendingAccessRequests' => $canApproveAccess
                ? ProgressReportAccessRequest::with(['participant.period', 'requester'])->where('status', 'pending')->latest()->get()
                : collect(),
        ]);
    }

    public function openPeriod(Request $request)
    {
        $this->authorizeManage();
        $request->validate(['period_month' => 'required|date']);

        $monthStart = \Carbon\Carbon::parse($request->period_month)->startOfMonth();
        if (ProgressReportPeriod::where('period_month', $monthStart->toDateString())->exists()) {
            return back()->with('error', 'A report period already exists for that month.');
        }

        $settings = ProgressReportSetting::current();
        $dueDate = $monthStart->copy()->day(min($settings->due_day, $monthStart->daysInMonth));

        $period = ProgressReportPeriod::create([
            'period_month' => $monthStart->toDateString(),
            'due_date'     => $dueDate->toDateString(),
            'status'       => 'open',
            'created_by'   => Auth::id(),
        ]);

        // Seed one participant per configured section, and pre-populate
        // their task rows from the recurring task template library.
        $previousPeriod = ProgressReportPeriod::where('period_month', '<', $monthStart->toDateString())
            ->orderByDesc('period_month')->first();

        foreach (config('progress_report_sections') as $i => $section) {
            if (! \App\Models\User::where('id', $section['user_id'])->exists()) {
                continue;
            }

            $participant = ProgressReportParticipant::create([
                'period_id'     => $period->id,
                'user_id'       => $section['user_id'],
                'section_label' => $section['label'],
                'sort_order'    => $i,
            ]);

            $templates = ProgressReportTaskTemplate::where('user_id', $section['user_id'])
                ->where('is_active', true)->orderBy('sort_order')->get();

            foreach ($templates as $ti => $template) {
                ProgressReportTask::create([
                    'period_id'             => $period->id,
                    'participant_id'        => $participant->id,
                    'template_id'           => $template->id,
                    'row_no'                => $ti + 1,
                    'activity_description'  => $template->activity_description,
                    'planned_activities'    => $template->default_planned_activities,
                ]);
            }
        }

        return redirect("progressive-reports/{$period->id}")->with('success', 'Report period opened.');
    }

    public function show($periodId)
    {
        $period = ProgressReportPeriod::with(['participants.user', 'participants.tasks', 'participants.pendingAccessRequest'])->findOrFail($periodId);

        $templatesByUser = ProgressReportTaskTemplate::whereIn('user_id', $period->participants->pluck('user_id'))
            ->where('is_active', true)->orderBy('sort_order')->get()->groupBy('user_id');

        $isManager = $this->canManage();
        $canApproveAccess = Auth::user()->canApproveProgressReportAccess();

        return view('progressive_reports.show', [
            'header_title' => 'Progressive Reports',
            'period'       => $period,
            'canManage'    => $isManager,
            'isManager'    => $isManager,
            'canApproveAccess' => $canApproveAccess,
            'myUserId'     => Auth::id(),
            'templatesByUser' => $templatesByUser,
            'pendingAccessRequests' => $canApproveAccess
                ? ProgressReportAccessRequest::with(['participant.period', 'requester'])->where('status', 'pending')->latest()->get()
                : collect(),
        ]);
    }

    public function updateTask(Request $request, $periodId, $taskId)
    {
        $request->validate([
            'activity_description' => 'nullable|string|max:1000',
            'planned_activities'   => 'nullable|string|max:5000',
            'current_status'       => 'nullable|string|max:5000',
            'next_steps'           => 'nullable|string|max:5000',
        ]);

        $task = ProgressReportTask::with('participant')->where('period_id', $periodId)->findOrFail($taskId);
        $this->authorizeTaskEdit($task);

        $old = $task->only(['activity_description', 'planned_activities', 'current_status', 'next_steps']);
        $new = $request->only(['activity_description', 'planned_activities', 'current_status', 'next_steps']);

        $task->update(array_merge($new, ['updated_by' => Auth::id()]));

        if ($old != $new) {
            ProgressReportTaskRevision::create([
                'task_id'    => $task->id,
                'editor_id'  => Auth::id(),
                'old_values' => $old,
                'new_values' => $new,
                'created_at' => now(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'task' => $task->fresh(), 'participant_status' => $task->participant->fresh()->status]);
        }

        return back();
    }

    public function addTaskRow(Request $request, $periodId, $participantId)
    {
        $participant = ProgressReportParticipant::where('period_id', $periodId)->findOrFail($participantId);
        $this->authorizeParticipantEdit($participant);

        $maxRow = ProgressReportTask::where('participant_id', $participant->id)->max('row_no') ?? 0;

        $task = ProgressReportTask::create([
            'period_id'      => $periodId,
            'participant_id' => $participant->id,
            'row_no'         => $maxRow + 1,
            'updated_by'     => Auth::id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'task' => $task]);
        }

        return back();
    }

    public function deleteTaskRow(Request $request, $periodId, $taskId)
    {
        $task = ProgressReportTask::with('participant')->where('period_id', $periodId)->findOrFail($taskId);
        $this->authorizeTaskEdit($task);

        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function submitSection(Request $request, $periodId, $participantId)
    {
        $participant = ProgressReportParticipant::where('period_id', $periodId)->findOrFail($participantId);
        $this->authorizeParticipantEdit($participant);

        $participant->update(['status' => 'submitted', 'submitted_at' => now(), 'edit_unlocked' => false]);

        return back()->with('success', 'Section submitted.');
    }

    // ── Edit-access requests (submitted sections are locked) ──────────

    public function requestAccess(Request $request, $periodId, $participantId)
    {
        $participant = ProgressReportParticipant::where('period_id', $periodId)->findOrFail($participantId);
        $this->authorizeParticipantOwnerOrManager($participant);

        if (! $participant->isLocked()) {
            return back()->with('error', 'This section is not locked.');
        }

        if (! $participant->accessRequests()->where('status', 'pending')->exists()) {
            ProgressReportAccessRequest::create([
                'participant_id' => $participant->id,
                'requested_by'   => Auth::id(),
                'status'         => 'pending',
                'reason'         => $request->input('reason'),
            ]);

            $adminOfficer = \App\Models\User::whereHas('adminRole', fn ($q) => $q->where('name', 'Administrative Officer'))->first();
            if ($adminOfficer && $adminOfficer->id != Auth::id()) {
                $this->sendProgressReportNotice(
                    $adminOfficer->id,
                    Auth::user()->name . ' is requesting edit access to the submitted "' . $participant->section_label . '" section for ' . $participant->period->period_month->format('F Y') . '.'
                );
            }
        }

        return back()->with('success', 'Access request sent to the Administrative Officer.');
    }

    public function approveAccessRequest(Request $request, $id)
    {
        $this->authorizeAccessApproval();
        $accessRequest = ProgressReportAccessRequest::with('participant')->findOrFail($id);
        $accessRequest->update(['status' => 'approved', 'decided_by' => Auth::id(), 'decided_at' => now()]);
        $accessRequest->participant->update(['edit_unlocked' => true]);

        $this->sendProgressReportNotice(
            $accessRequest->requested_by,
            'Your edit access request for "' . $accessRequest->participant->section_label . '" has been approved — you can now edit that section again.'
        );

        return back()->with('success', 'Access request approved.');
    }

    public function denyAccessRequest(Request $request, $id)
    {
        $this->authorizeAccessApproval();
        $accessRequest = ProgressReportAccessRequest::with('participant')->findOrFail($id);
        $accessRequest->update(['status' => 'denied', 'decided_by' => Auth::id(), 'decided_at' => now()]);

        $this->sendProgressReportNotice(
            $accessRequest->requested_by,
            'Your edit access request for "' . $accessRequest->participant->section_label . '" was declined by the Administrative Officer.'
        );

        return back()->with('success', 'Access request declined.');
    }

    protected function sendProgressReportNotice(int $toUserId, string $body): void
    {
        $myId = Auth::id();

        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $toUserId))
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create(['type' => 'direct', 'created_by' => $myId]);
            ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $myId, 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conversation->id, 'user_id' => $toUserId, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $myId,
            'body'            => $body,
        ]);
        $conversation->update(['last_message_at' => now()]);
    }

    public function copyForward(Request $request, $periodId, $participantId)
    {
        $participant = ProgressReportParticipant::where('period_id', $periodId)->findOrFail($participantId);
        $this->authorizeParticipantEdit($participant);

        $previousPeriod = ProgressReportPeriod::where('period_month', '<', $participant->period->period_month)
            ->orderByDesc('period_month')->first();

        if (! $previousPeriod) {
            return back()->with('error', 'No previous period to copy from.');
        }

        $previousParticipant = ProgressReportParticipant::where('period_id', $previousPeriod->id)
            ->where('user_id', $participant->user_id)->first();

        if (! $previousParticipant) {
            return back()->with('error', 'You had no section in the previous period.');
        }

        $existingMax = ProgressReportTask::where('participant_id', $participant->id)->max('row_no') ?? 0;

        foreach ($previousParticipant->tasks as $i => $prevTask) {
            ProgressReportTask::create([
                'period_id'             => $periodId,
                'participant_id'        => $participant->id,
                'template_id'           => $prevTask->template_id,
                'row_no'                => $existingMax + $i + 1,
                'activity_description'  => $prevTask->activity_description,
                'planned_activities'    => $prevTask->planned_activities,
                // Current Status / Next Steps intentionally left blank —
                // those are this month's update, not last month's.
                'updated_by'            => Auth::id(),
            ]);
        }

        return back()->with('success', "Copied " . $previousParticipant->tasks->count() . ' task(s) from last month.');
    }

    public function consolidate(Request $request, $periodId)
    {
        $this->authorizeManage();
        $period = ProgressReportPeriod::findOrFail($periodId);
        $period->update(['status' => 'consolidated', 'consolidated_at' => now(), 'consolidated_by' => Auth::id()]);

        return back()->with('success', 'Report consolidated.');
    }

    public function unconsolidate(Request $request, $periodId)
    {
        $this->authorizeManage();
        $period = ProgressReportPeriod::findOrFail($periodId);
        $period->update(['status' => 'open', 'consolidated_at' => null, 'consolidated_by' => null]);

        return back()->with('success', 'Report reopened for editing.');
    }

    // Deleting an entire report period (and every section's data with it)
    // is deliberately stricter than the rest of the "manage" actions —
    // only a Super Admin can do this, not the Administrative Officer and
    // not the CEO, since it's irreversible and affects everyone's work.
    public function deletePeriod(Request $request, $periodId)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Only a Super Admin can delete a report period.');

        $period = ProgressReportPeriod::findOrFail($periodId);
        $label = $period->period_month->format('F Y');
        $period->delete(); // cascades participants/tasks/revisions via FK

        return redirect('progressive-reports')->with('success', "Deleted the {$label} report period.");
    }

    public function downloadPdf($periodId)
    {
        $period = ProgressReportPeriod::with(['participants.user', 'participants.tasks'])->findOrFail($periodId);

        $pdf = Pdf::loadView('progressive_reports.pdf', ['period' => $period])->setPaper('a4', 'landscape');

        $filename = 'COSECSA Secretariat Report - ' . $period->period_month->format('F Y') . '.pdf';
        $response = $pdf->stream($filename);
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->headers->remove('Pragma');

        return $response;
    }

    public function shareWithCeo(Request $request, $periodId)
    {
        $this->authorizeManage();
        $period = ProgressReportPeriod::with(['participants.user', 'participants.tasks'])->findOrFail($periodId);

        $ceoSection = collect(config('progress_report_sections'))->firstWhere('label', 'CEO');
        if (! $ceoSection || ! \App\Models\User::where('id', $ceoSection['user_id'])->exists()) {
            return back()->with('error', 'No CEO account is configured to share with.');
        }
        $ceoId = $ceoSection['user_id'];
        $myId = Auth::id();

        $pdf = Pdf::loadView('progressive_reports.pdf', ['period' => $period])->setPaper('a4', 'landscape');
        $filename = 'COSECSA Secretariat Report - ' . $period->period_month->format('F Y') . '.pdf';
        $path = 'messages/attachments/' . uniqid('progress_report_') . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $ceoId))
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create(['type' => 'direct', 'created_by' => $myId]);
            ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $myId, 'created_at' => now(), 'updated_at' => now()],
                ['conversation_id' => $conversation->id, 'user_id' => $ceoId, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $myId,
            'body'            => 'Consolidated Secretariat Report for ' . $period->period_month->format('F Y') . ' is ready for your review.',
        ]);
        MessageAttachment::create([
            'message_id'    => $message->id,
            'path'          => $path,
            'original_name' => $filename,
            'mime_type'     => 'application/pdf',
            'size'          => Storage::disk('public')->size($path),
            'kind'          => 'file',
        ]);
        $conversation->update(['last_message_at' => now()]);

        return redirect("progressive-reports/{$periodId}")->with('success', 'Report shared with the CEO via Messages.');
    }

    // ── Recurring task templates ─────────────────────────────────────

    public function templatesIndex()
    {
        $this->authorizeManage();

        $templates = ProgressReportTaskTemplate::with('user')->orderBy('user_id')->orderBy('sort_order')->get()->groupBy('user_id');

        return view('progressive_reports.templates', [
            'header_title' => 'Progressive Reports — Recurring Tasks',
            'templatesByUser' => $templates,
            'sections'     => config('progress_report_sections'),
        ]);
    }

    public function templateStore(Request $request)
    {
        $request->validate([
            'user_id'                    => 'nullable|integer',
            'activity_description'       => 'required|string|max:1000',
            'default_planned_activities' => 'nullable|string|max:5000',
        ]);

        $userId = ($this->canManage() && $request->filled('user_id')) ? (int) $request->user_id : Auth::id();

        ProgressReportTaskTemplate::create([
            'user_id'                    => $userId,
            'activity_description'       => $request->activity_description,
            'default_planned_activities' => $request->default_planned_activities,
            'is_active'                  => true,
            'sort_order'                 => ProgressReportTaskTemplate::where('user_id', $userId)->max('sort_order') + 1,
            'created_by'                 => Auth::id(),
        ]);

        return back()->with('success', 'Recurring task added.');
    }

    public function templateUpdate(Request $request, $id)
    {
        $template = ProgressReportTaskTemplate::findOrFail($id);
        abort_unless($this->canManage() || $template->user_id == Auth::id(), 403, 'You can only edit your own recurring tasks.');

        $request->validate([
            'activity_description'       => 'required|string|max:1000',
            'default_planned_activities' => 'nullable|string|max:5000',
            'is_active'                  => 'nullable|boolean',
        ]);

        $template->update([
            'activity_description'       => $request->activity_description,
            'default_planned_activities' => $request->default_planned_activities,
            'is_active'                  => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Recurring task updated.');
    }

    public function templateDelete($id)
    {
        $template = ProgressReportTaskTemplate::findOrFail($id);
        abort_unless($this->canManage() || $template->user_id == Auth::id(), 403, 'You can only remove your own recurring tasks.');
        $template->delete();

        return back()->with('success', 'Recurring task removed.');
    }

    // ── Settings (due day / reminder lead time) ──────────────────────

    public function settingsEdit()
    {
        $this->authorizeManage();

        return view('progressive_reports.settings', [
            'header_title' => 'Progressive Reports — Settings',
            'settings'     => ProgressReportSetting::current(),
        ]);
    }

    public function settingsUpdate(Request $request)
    {
        $this->authorizeManage();
        $request->validate([
            'due_day'              => 'required|integer|min:1|max:28',
            'reminder_days_before' => 'required|integer|min:0|max:27',
            'reminder_enabled'     => 'nullable|boolean',
        ]);

        $settings = ProgressReportSetting::current();
        $settings->update([
            'due_day'              => $request->due_day,
            'reminder_days_before' => $request->reminder_days_before,
            'reminder_enabled'     => $request->boolean('reminder_enabled'),
            'updated_by'           => Auth::id(),
        ]);

        return back()->with('success', 'Settings saved.');
    }

    // ── Authorization helpers ────────────────────────────────────────

    protected function canManage(): bool
    {
        $user = Auth::user();
        return $user && $user->isProgressReportManager();
    }

    protected function authorizeManage(): void
    {
        abort_unless($this->canManage(), 403, 'Only the Administrative Officer or a Super Admin can do this.');
    }

    protected function authorizeAccessApproval(): void
    {
        abort_unless(Auth::user()->canApproveProgressReportAccess(), 403, 'Only the Administrative Officer or the Master Admin can approve edit-access requests.');
    }

    protected function authorizeParticipantEdit(ProgressReportParticipant $participant): void
    {
        abort_unless($participant->user_id == Auth::id() || $this->canManage(), 403, 'You can only edit your own section.');
        abort_if($participant->isLocked(), 403, 'This section has been submitted and is locked. Request edit access from the Administrative Officer.');
    }

    // Only checks ownership/management — used when requesting access to an
    // already-submitted (locked) section, where authorizeParticipantEdit's
    // lock check would otherwise always block the request itself.
    protected function authorizeParticipantOwnerOrManager(ProgressReportParticipant $participant): void
    {
        abort_unless($participant->user_id == Auth::id() || $this->canManage(), 403, 'You can only request access to your own section.');
    }

    protected function authorizeTaskEdit(ProgressReportTask $task): void
    {
        $this->authorizeParticipantEdit($task->participant);
    }
}
