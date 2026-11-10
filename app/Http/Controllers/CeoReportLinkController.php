<?php

namespace App\Http\Controllers;

use App\Models\ProgressReportCeoLink;
use App\Models\ProgressReportParticipant;
use App\Models\ProgressReportPeriod;
use App\Models\ProgressReportTask;
use App\Models\ProgressReportTaskRevision;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * The CEO's private report link (emailed by "Share with CEO"): she opens the
 * monthly Secretariat report from the email, types into her own section and
 * submits it, without logging in. The token in the URL is the only credential,
 * so it only ever grants (a) a read-only view of that month's report and
 * (b) editing of the one CEO section the link was issued for.
 */
class CeoReportLinkController extends Controller
{
    private const FIELDS = ['activity_description', 'planned_activities', 'current_status', 'next_steps'];

    public function show(Request $request, string $token)
    {
        $link = $this->link($token);
        if (! $link) {
            return response()->view('progressive_reports.ceo_link_invalid', [], 410)->withHeaders($this->headers());
        }

        // Mail-security scanners and link-preview bots fetch URLs before the
        // person does; counting those would stop the reminders for someone who
        // never opened the email.
        if ($this->looksLikeAPerson($request)) {
            $link->recordOpen();
        }

        $participant = ProgressReportParticipant::with('tasks')->findOrFail($link->participant_id);
        $period = ProgressReportPeriod::with(['participants' => fn ($q) => $q->where('id', '!=', $participant->id), 'participants.tasks'])
            ->findOrFail($link->period_id);

        return response()->view('progressive_reports.ceo_link', [
            'link'        => $link,
            'token'       => $token,
            'period'      => $period,
            'participant' => $participant,
            'ceo'         => User::find($participant->user_id),
        ])->withHeaders($this->headers());
    }

    public function updateTask(Request $request, string $token, int $taskId)
    {
        $link = $this->link($token);
        if (! $link) {
            return response()->json(['ok' => false, 'message' => 'This link has expired.'], 410);
        }

        $request->validate([
            'activity_description' => 'nullable|string|max:1000',
            'planned_activities'   => 'nullable|string|max:5000',
            'current_status'       => 'nullable|string|max:5000',
            'next_steps'           => 'nullable|string|max:5000',
        ]);

        $task = ProgressReportTask::where('participant_id', $link->participant_id)->findOrFail($taskId);

        $old = $task->only(self::FIELDS);
        $new = $request->only(self::FIELDS);
        foreach (['planned_activities', 'current_status', 'next_steps'] as $bulletField) {
            if (array_key_exists($bulletField, $new)) {
                $new[$bulletField] = $this->normalizeBullets($new[$bulletField]);
            }
        }

        $ceoId = ProgressReportParticipant::find($link->participant_id)->user_id;
        $task->update(array_merge($new, ['updated_by' => $ceoId]));

        if (array_intersect_key($old, $new) != $new) {
            ProgressReportTaskRevision::create([
                'task_id'    => $task->id,
                'editor_id'  => $ceoId,
                'old_values' => $old,
                'new_values' => $new,
                'created_at' => now(),
            ]);
        }

        return response()->json(['ok' => true, 'task' => $task->fresh()]);
    }

    public function addTask(Request $request, string $token)
    {
        $link = $this->link($token);
        if (! $link) {
            return response()->json(['ok' => false, 'message' => 'This link has expired.'], 410);
        }

        $participant = ProgressReportParticipant::findOrFail($link->participant_id);
        $task = ProgressReportTask::create([
            'period_id'      => $participant->period_id,
            'participant_id' => $participant->id,
            'row_no'         => (ProgressReportTask::where('participant_id', $participant->id)->max('row_no') ?? 0) + 1,
            'updated_by'     => $participant->user_id,
        ]);

        return response()->json(['ok' => true, 'task' => $task]);
    }

    public function deleteTask(Request $request, string $token, int $taskId)
    {
        $link = $this->link($token);
        if (! $link) {
            return response()->json(['ok' => false, 'message' => 'This link has expired.'], 410);
        }

        ProgressReportTask::where('participant_id', $link->participant_id)->findOrFail($taskId)->delete();
        $this->renumber($link->participant_id);

        return response()->json(['ok' => true]);
    }

    public function submit(Request $request, string $token)
    {
        $link = $this->link($token);
        if (! $link) {
            return response()->json(['ok' => false, 'message' => 'This link has expired.'], 410);
        }

        ProgressReportParticipant::whereKey($link->participant_id)->update(['status' => 'submitted', 'submitted_at' => now()]);
        $link->update(['submitted_at' => now()]);

        return response()->json(['ok' => true, 'submitted_at' => now()->format('d M Y H:i')]);
    }

    protected function link(string $token): ?ProgressReportCeoLink
    {
        $link = ProgressReportCeoLink::findByToken($token);

        return $link && ! $link->isExpired() ? $link : null;
    }

    protected function looksLikeAPerson(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        return $request->isMethod('GET')
            && $ua !== ''
            && ! preg_match('/bot|crawl|spider|preview|scanner|safelinks|barracuda|proofpoint|mimecast|curl|wget|python|httpclient|go-http|java\//i', $ua);
    }

    protected function headers(): array
    {
        return [
            'Cache-Control'   => 'no-store, private',
            'X-Robots-Tag'    => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ];
    }

    // Same rule as ProgressiveReportController::normalizeBullets(): every
    // non-empty line is bulleted with "❖ ".
    protected function normalizeBullets(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $lines = array_map(function ($line) {
            $line = trim($line);

            return $line === '' ? null : (str_starts_with($line, '❖') ? $line : '❖ ' . $line);
        }, preg_split('/\r\n|\r|\n/', $text));

        return implode("\n", array_filter($lines, fn ($l) => $l !== null));
    }

    protected function renumber(int $participantId): void
    {
        $position = 0;
        foreach (ProgressReportTask::where('participant_id', $participantId)->orderBy('row_no')->orderBy('id')->get(['id', 'row_no']) as $task) {
            $position++;
            if ((int) $task->row_no !== $position) {
                ProgressReportTask::whereKey($task->id)->update(['row_no' => $position]);
            }
        }
    }
}
