<?php

namespace App\Console\Commands;

use App\Mail\ProgressReportCeoLinkReminderMail;
use App\Models\ProgressReportCeoLink;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCeoReportLinkReminders extends Command
{
    protected $signature = 'progress-reports:remind-ceo-link {--dry-run : List who would be reminded without sending}';
    protected $description = 'Email the CEO a reminder while she has not opened her monthly Secretariat report link.';

    public function handle(): int
    {
        $every = max(1, (int) config('services.progress_reports.ceo_link.reminder_every_days', 2));
        $max   = (int) config('services.progress_reports.ceo_link.max_reminders', 5);
        $today = now()->startOfDay();

        $links = ProgressReportCeoLink::with(['period', 'participant'])
            ->whereNull('first_opened_at')
            ->whereNull('submitted_at')
            ->where('reminder_count', '<', $max)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get();

        $sent = 0;
        foreach ($links as $link) {
            // Count from the last reminder, or from the day the link was sent.
            $since = $link->last_reminder_at ?? $link->sent_at ?? $link->created_at;
            if (! $since || $since->copy()->startOfDay()->diffInDays($today) < $every) {
                continue;
            }

            $ceo = $link->participant ? User::find($link->participant->user_id) : null;
            if (! $ceo || ! $ceo->email) {
                $this->warn("Link #{$link->id}: the CEO account has no email address — skipped.");
                continue;
            }

            $month = $link->period->period_month->format('F Y');
            if ($this->option('dry-run')) {
                $this->line("Would remind {$ceo->email} about {$month} (reminder " . ($link->reminder_count + 1) . " of {$max}).");
                continue;
            }

            Mail::to($ceo->email)->send(new ProgressReportCeoLinkReminderMail(
                $month,
                $link->url(),
                $link->created_by ? User::find($link->created_by) : null,
            ));
            $link->update(['reminder_count' => $link->reminder_count + 1, 'last_reminder_at' => now()]);
            $sent++;
            $this->info("Reminded {$ceo->email} about {$month} (reminder {$link->reminder_count} of {$max}).");
        }

        $this->info($sent ? "{$sent} reminder(s) sent." : 'No reminders due.');

        return self::SUCCESS;
    }
}
