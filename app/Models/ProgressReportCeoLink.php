<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * The private link the CEO receives by email to open the monthly Secretariat
 * report, fill in her own section and submit it without logging in.
 * See the create_progress_report_ceo_links_table migration (cosecsa-api).
 */
class ProgressReportCeoLink extends Model
{
    protected $fillable = [
        'period_id', 'participant_id', 'token_hash', 'token_encrypted', 'expires_at', 'sent_at',
        'first_opened_at', 'last_opened_at', 'open_count', 'reminder_count', 'last_reminder_at',
        'submitted_at', 'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime', 'sent_at' => 'datetime', 'first_opened_at' => 'datetime',
        'last_opened_at' => 'datetime', 'last_reminder_at' => 'datetime', 'submitted_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(ProgressReportParticipant::class, 'participant_id');
    }

    public function period()
    {
        return $this->belongsTo(ProgressReportPeriod::class, 'period_id');
    }

    // Creates the link for a CEO section, or — if she already has a still-valid
    // one for this month — keeps the same token (so a link she was already
    // emailed keeps working) and starts the open/reminder tracking afresh,
    // because this is a new send.
    public static function issue(ProgressReportParticipant $participant, ?int $createdBy = null): self
    {
        $existing = static::where('participant_id', $participant->id)->first();

        $token = $existing && ! $existing->isExpired() ? Crypt::decryptString($existing->token_encrypted) : Str::random(48);

        return static::updateOrCreate(['participant_id' => $participant->id], [
            'period_id'        => $participant->period_id,
            'token_hash'       => hash('sha256', $token),
            'token_encrypted'  => Crypt::encryptString($token),
            'expires_at'       => now()->addDays((int) config('services.progress_reports.ceo_link.expires_days', 45)),
            'sent_at'          => now(),
            'first_opened_at'  => null,
            'last_opened_at'   => null,
            'open_count'       => 0,
            'reminder_count'   => 0,
            'last_reminder_at' => null,
            'submitted_at'     => null,
            'created_by'       => $createdBy,
        ]);
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token_hash', hash('sha256', $token))->first();
    }

    public function url(): string
    {
        return url('ceo-report/' . Crypt::decryptString($this->token_encrypted));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // Records that a person (not a mail scanner) opened the link.
    public function recordOpen(): void
    {
        $this->forceFill([
            'first_opened_at' => $this->first_opened_at ?? now(),
            'last_opened_at'  => now(),
            'open_count'      => $this->open_count + 1,
        ])->save();
    }
}
