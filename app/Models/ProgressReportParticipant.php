<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReportParticipant extends Model
{
    protected $fillable = ['period_id', 'user_id', 'section_label', 'status', 'submitted_at', 'edit_unlocked', 'sort_order'];
    protected $casts = ['submitted_at' => 'datetime', 'edit_unlocked' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function period()
    {
        return $this->belongsTo(ProgressReportPeriod::class, 'period_id');
    }

    public function tasks()
    {
        return $this->hasMany(ProgressReportTask::class, 'participant_id')->orderBy('row_no');
    }

    public function accessRequests()
    {
        return $this->hasMany(ProgressReportAccessRequest::class, 'participant_id');
    }

    public function pendingAccessRequest()
    {
        return $this->hasOne(ProgressReportAccessRequest::class, 'participant_id')->where('status', 'pending')->latestOfMany();
    }

    // The CEO's user_id — see config/services.php's `progress_reports.ceo_user_id`
    // comment for why this is deliberately NOT looked up via
    // config('progress_report_sections') (she's excluded from that list on
    // purpose). She may still end up with a participant row some months
    // (e.g. Aug 2026, seeded by cosecsa-api's copy of the sections list,
    // which does include her) — when she does, this identifies it so it's
    // never locked by the deadline / never reminded / never counted as
    // pending (see isCeoSection()/isLocked() below).
    public static function ceoUserId(): ?int
    {
        $id = config('services.progress_reports.ceo_user_id');

        return $id ? (int) $id : null;
    }

    // Gives the CEO her own section in a period once it's consolidated /
    // shared with her, so she can fill in her part of the report. Needed
    // because this app's config/progress_report_sections.php leaves her
    // out at openPeriod() time (Sep 2026 had no CEO row as a result) —
    // only months opened via cosecsa-api's copy of the list seeded one.
    // Idempotent: returns the existing row if she already has one. Placed
    // first (sort_order 0, others shifted down) to match Aug 2026's layout;
    // the column is unsigned, so it can't just go negative.
    public static function ensureCeoSection(ProgressReportPeriod $period): ?self
    {
        $ceoId = static::ceoUserId();
        if (! $ceoId || ! User::where('id', $ceoId)->exists()) {
            return null;
        }

        $existing = static::where('period_id', $period->id)->where('user_id', $ceoId)->first();
        if ($existing) {
            return $existing;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($period, $ceoId) {
            static::where('period_id', $period->id)->increment('sort_order');

            $participant = static::create([
                'period_id'     => $period->id,
                'user_id'       => $ceoId,
                'section_label' => 'CEO',
                'sort_order'    => 0,
            ]);

            $templates = ProgressReportTaskTemplate::where('user_id', $ceoId)
                ->where('is_active', true)->orderBy('sort_order')->get();

            foreach ($templates as $ti => $template) {
                ProgressReportTask::create([
                    'period_id'            => $period->id,
                    'participant_id'       => $participant->id,
                    'template_id'          => $template->id,
                    'row_no'               => $ti + 1,
                    'activity_description' => $template->activity_description,
                    'planned_activities'   => $template->default_planned_activities,
                ]);
            }

            return $participant;
        });
    }

    public function isCeoSection(): bool
    {
        return $this->user_id === static::ceoUserId();
    }

    // A section locks once its period is no longer the current reporting
    // month, or once the period's own due date has passed — NOT simply
    // because it was submitted. Staff can freely keep editing/resubmitting
    // right up to the deadline even after an earlier submission; only past
    // the deadline does further editing require the Administrative
    // Officer to grant a fresh edit request (cleared on the next submit).
    //
    // The CEO's own section is exempt from the deadline half of this —
    // she isn't submitting on a schedule, so there's no "deadline passed"
    // for her to be locked out by. A past (no-longer-current) month is
    // still locked for her too, same as everyone else's history.
    public function isLocked(): bool
    {
        if ($this->edit_unlocked) {
            return false;
        }

        if (! $this->period->is_current) {
            return true;
        }

        if ($this->isCeoSection()) {
            return false;
        }

        return now()->startOfDay()->gt($this->period->due_date);
    }
}
