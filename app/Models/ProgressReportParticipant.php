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
