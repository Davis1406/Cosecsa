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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

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
        }, 'participants.user', 'participants.tasks', 'participants.pendingAccessRequest', 'participants.period'])->findOrFail($selected->id);

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

        // Only the single most recent period (by month, not creation order —
        // covers backfilling an older month later) is ever "current"; every
        // other period's pending sections become locked as a side effect.
        $latestId = ProgressReportPeriod::orderByDesc('period_month')->value('id');
        ProgressReportPeriod::where('id', '!=', $latestId)->update(['is_current' => false]);
        ProgressReportPeriod::where('id', $latestId)->update(['is_current' => true]);

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
        $period = ProgressReportPeriod::with(['participants.user', 'participants.tasks', 'participants.pendingAccessRequest', 'participants.period'])->findOrFail($periodId);

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

        foreach (['planned_activities', 'current_status', 'next_steps'] as $bulletField) {
            if (array_key_exists($bulletField, $new)) {
                $new[$bulletField] = $this->normalizeBullets($new[$bulletField]);
            }
        }

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

            if ($adminOfficer) {
                // TEMPORARY: routed to a test address while the Admin Officer
                // email is being verified (logo rendering, formatting) — switch
                // back to $adminOfficer->email once confirmed.
                $recipientEmail = 'dkondo146@gmail.com';

                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\ProgressReportAccessRequestMail(
                    Auth::user()->name,
                    $participant->section_label,
                    $participant->period->period_month->format('F Y'),
                    url('progressive-reports/' . $periodId),
                    Auth::user()->email
                ));
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

        // Acting on your own request (e.g. Master Admin approving their own
        // access request) has no one to notify — and a self/self lookup
        // below would otherwise match any conversation you're part of.
        if ($toUserId == $myId) {
            return;
        }

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
        $period = ProgressReportPeriod::with(['participants' => function ($q) {
            $q->where('user_id', Auth::id());
        }, 'participants.user', 'participants.tasks'])->findOrFail($periodId);

        $pdf = Pdf::loadView('progressive_reports.pdf', ['period' => $period])->setPaper('a4', 'landscape');

        $filename = 'COSECSA Secretariat Report - ' . $period->period_month->format('F Y') . '.pdf';
        $response = $pdf->stream($filename);
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->headers->remove('Pragma');

        return $response;
    }

    /**
     * Download the progressive report as a DOCX file.
     */
    public function downloadDocx($periodId)
    {
        $period = ProgressReportPeriod::with(['participants' => function ($q) {
            $q->where('user_id', Auth::id());
        }, 'participants.user', 'participants.tasks'])->findOrFail($periodId);

        [$binary, $filename] = $this->buildProgressReportDocx($period);

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, $filename, [
            'Content-Type'              => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
            'Content-Length'             => strlen($binary),
        ]);
    }

    /**
     * Render a ProgressReportPeriod (with participants.user/participants.tasks
     * already eager-loaded) to a .docx binary. Shared by the manual "Download
     * DOCX" button and the "Share with CEO" email attachment — the only
     * difference between the two is which participants the caller loaded
     * onto $period (the current user's own section vs. every section).
     *
     * @return array{0: string, 1: string} [$binary, $filename]
     */
    private function buildProgressReportDocx(ProgressReportPeriod $period): array
    {
        // Without this, PHPWord writes raw text into document.xml instead of
        // escaping it, so any "&", "<", or ">" in the data (e.g. "MCS & FCS")
        // produces invalid XML that Word refuses to open.
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'orientation'   => 'landscape',
            'pageSizeW'     => 14400,  // 10 inches in twips
            'pageSizeH'     => 10800,  // 7.5 inches in twips
            'marginLeft'    => 720,
            'marginRight'   => 720,
            'marginTop'     => 720,
            'marginBottom'  => 720,
        ]);

        // Title
        $section->addText('COSECSA SECRETARIAT MONTHLY REPORT', [
            'size'  => 14,
            'bold'  => true,
            'align' => 'center',
        ]);

        // Subtitle — use plain ASCII dash instead of bullet char
        $section->addText(
            strtoupper($period->period_month->format('F Y')) . '  -  Due ' . $period->due_date->format('d M Y'),
            ['size' => 10, 'color' => '555555', 'align' => 'center']
        );
        $section->addParagraph();

        // Table
        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '999999',
            'cellMargin'  => 40,
        ]);

        // Column widths (twips). Page is 14400 wide with 720 left/right
        // margins, so usable width is 14400 - 1440 = 12960 — these must sum
        // to at most that or the table overflows past the right margin (the
        // Next Steps column got clipped at the page edge when this summed
        // to 14280). Also the total here is what any gridSpan-merged
        // full-width cell (section header row, "No tasks recorded" row)
        // must be given so it renders as one full-width band and not just
        // the width of the grid's first column.
        $colNo       = 420;
        $colActivity = 2200;
        $colPlanned  = 3300;
        $colStatus   = 3300;
        $colNext     = 3300;
        $colFullWidth = $colNo + $colActivity + $colPlanned + $colStatus + $colNext; // 12520

        // Header row
        $headerStyle = ['size' => 9, 'bold' => true, 'bgColor' => 'F1F1F1'];
        $table->addRow();
        $table->addCell($colNo, $headerStyle)->addText('No');
        $table->addCell($colActivity, $headerStyle)->addText('Activity');
        $table->addCell($colPlanned, $headerStyle)->addText('Planned Activities');
        $table->addCell($colStatus, $headerStyle)->addText('Current Status');
        $table->addCell($colNext, $headerStyle)->addText('Next Steps');

        $rowStyle = ['size' => 9];
        $sectionCellStyle = ['bgColor' => 'A02626', 'gridSpan' => 5];
        $sectionFontStyle = ['size' => 9, 'bold' => true, 'color' => 'FFFFFF'];
        $noTasksCellStyle = ['gridSpan' => 5];
        foreach ($period->participants as $participant) {
            // Section header row — bgColor is a cell-level style, bold/color
            // are font-level styles and must go on addText()'s own style arg
            // or the text renders in the default (black, invisible-on-red)
            // font. gridSpan is required for the cell to actually span all
            // 5 grid columns — without it, a single cell in a row by itself
            // collapses to the width of the grid's first (narrowest) column
            // and the section name wraps letter-by-letter down the page.
            $table->addRow();
            $table->addCell($colFullWidth, $sectionCellStyle)->addText($participant->section_label, $sectionFontStyle);

            // Repeat the column header row under every section label so a
            // reader scrolling/paging through the report always has the
            // No/Activity/Planned Activities/Current Status/Next Steps
            // headings in view, not just once at the very top of the table.
            $table->addRow();
            $table->addCell($colNo, $headerStyle)->addText('No');
            $table->addCell($colActivity, $headerStyle)->addText('Activity');
            $table->addCell($colPlanned, $headerStyle)->addText('Planned Activities');
            $table->addCell($colStatus, $headerStyle)->addText('Current Status');
            $table->addCell($colNext, $headerStyle)->addText('Next Steps');

            if ($participant->tasks->isEmpty()) {
                $table->addRow();
                $table->addCell($colFullWidth, $noTasksCellStyle)->addText('No tasks recorded.', $rowStyle);
            } else {
                foreach ($participant->tasks as $task) {
                    $table->addRow();
                    $table->addCell($colNo, $rowStyle)->addText($task->row_no ? (string) $task->row_no : '');
                    $table->addCell($colActivity, $rowStyle)->addText($task->activity_description ?: '');
                    $this->addBulletedCellText($table->addCell($colPlanned, $rowStyle), $task->planned_activities, $rowStyle);
                    $this->addBulletedCellText($table->addCell($colStatus, $rowStyle), $task->current_status, $rowStyle);
                    $this->addBulletedCellText($table->addCell($colNext, $rowStyle), $task->next_steps, $rowStyle);
                }
            }
        }

        $filename = 'COSECSA Secretariat Report - ' . $period->period_month->format('F Y') . '.docx';

        $tempPath = tempnam(sys_get_temp_dir(), 'docx_') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        while (ob_get_level()) {
            ob_end_clean();
        }

        $binary = file_get_contents($tempPath);
        @unlink($tempPath);

        return [$binary, $filename];
    }

    /**
     * Render a task field (planned_activities / current_status / next_steps)
     * into a table cell as one bullet point per line instead of PhpWord's
     * default of dumping the raw "\n❖ "-joined string as one unbroken run —
     * addText() doesn't treat "\n" as a line break, so multi-bullet fields
     * used to render as all their bullets mashed onto a single line. Every
     * line is guaranteed to start with the "❖" bullet even if the stored
     * text is missing it (older data entered before the bullet-textarea
     * existed), and each bullet gets its own line via addTextBreak().
     */
    private function addBulletedCellText($cell, ?string $text, array $fontStyle): void
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values();

        if ($lines->isEmpty()) {
            $cell->addText('', $fontStyle);
            return;
        }

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $cell->addTextBreak(1, $fontStyle);
            }
            $bulleted = str_starts_with($line, '❖') ? $line : '❖ ' . $line;
            $cell->addText($bulleted, $fontStyle);
        }
    }

    public function shareWithCeo(Request $request, $periodId)
    {
        $this->authorizeManage();
        $period = ProgressReportPeriod::with(['participants.user', 'participants.tasks'])->findOrFail($periodId);

        $ceoSection = collect(config('progress_report_sections'))->firstWhere('label', 'CEO');
        $ceoUser = $ceoSection ? \App\Models\User::find($ceoSection['user_id']) : null;
        if (! $ceoUser) {
            return back()->with('error', 'No CEO account is configured to share with.');
        }
        $ceoId = $ceoUser->id;
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

        $emailed = false;
        if ($ceoUser->email) {
            [$docxBinary, $docxFilename] = $this->buildProgressReportDocx($period);

            Mail::to($ceoUser->email)->send(new \App\Mail\ProgressReportCeoShareMail(
                $period->period_month->format('F Y'),
                $docxFilename,
                $docxBinary,
                Auth::user(),
            ));
            $emailed = true;
        }

        $message = $emailed
            ? 'Report shared with the CEO via Messages and emailed to her as a Word document.'
            : 'Report shared with the CEO via Messages — no email was sent because the CEO account has no email address on file.';

        return redirect("progressive-reports/{$periodId}")->with($emailed ? 'success' : 'error', $message);
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

    // Creates one blank recurring-task row instantly, same idea as
    // addTaskRow() for the report tasks themselves — the row appears empty
    // in the table and every field autosaves on change from there via
    // templateUpdate(), so there's no separate "fill in a form, then submit"
    // step (and nothing to lose on an accidental page reload).
    public function templateAddBlank(Request $request)
    {
        $request->validate(['user_id' => 'nullable|integer']);

        $userId = ($this->canManage() && $request->filled('user_id')) ? (int) $request->user_id : Auth::id();

        $template = ProgressReportTaskTemplate::create([
            'user_id'                    => $userId,
            'activity_description'       => '',
            'default_planned_activities' => null,
            'is_active'                  => true,
            'sort_order'                 => (int) (ProgressReportTaskTemplate::where('user_id', $userId)->max('sort_order') ?? 0) + 1,
            'created_by'                 => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'template' => $template]);
        }

        return back();
    }

    // Partial/field-at-a-time update, mirroring updateTask() for report
    // tasks — the UI fires one request per field on change (autosave), not
    // one request with the whole row, so every field here is optional.
    public function templateUpdate(Request $request, $id)
    {
        $template = ProgressReportTaskTemplate::findOrFail($id);
        abort_unless($this->canManage() || $template->user_id == Auth::id(), 403, 'You can only edit your own recurring tasks.');

        $request->validate([
            'activity_description'       => 'sometimes|nullable|string|max:1000',
            'default_planned_activities' => 'sometimes|nullable|string|max:5000',
            'is_active'                  => 'sometimes|boolean',
        ]);

        $data = $request->only(['activity_description', 'default_planned_activities', 'is_active']);
        if (array_key_exists('activity_description', $data) && $data['activity_description'] === null) {
            $data['activity_description'] = '';
        }

        $template->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'template' => $template->fresh()]);
        }

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

    // Guarantees every non-empty line is bulleted regardless of how it was
    // typed — via the live "Enter inserts ❖" JS, pasted in, or a select-all
    // retype that wiped the auto-inserted marker — since relying on the
    // client-side keystroke behavior alone proved unreliable in practice.
    protected function normalizeBullets(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $lines = array_map(function ($line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                return null;
            }
            return str_starts_with($trimmed, '❖') ? $trimmed : '❖ ' . $trimmed;
        }, $lines);

        return implode("\n", array_filter($lines, fn ($l) => $l !== null));
    }

    protected function authorizeTaskEdit(ProgressReportTask $task): void
    {
        $this->authorizeParticipantEdit($task->participant);
    }
}