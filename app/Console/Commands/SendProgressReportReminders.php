<?php

namespace App\Console\Commands;

use App\Mail\ProgressReportReminderMail;
use App\Models\ProgressReportPeriod;
use App\Models\ProgressReportSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProgressReportReminders extends Command
{
    protected $signature = 'progress-reports:send-reminders';
    protected $description = 'Email a reminder to any staff member who has not yet submitted their section, N days before the report is due.';

    public function handle(): int
    {
        $settings = ProgressReportSetting::current();
        if (! $settings->reminder_enabled) {
            $this->info('Reminders are disabled in settings — nothing to do.');
            return self::SUCCESS;
        }

        $today = now()->startOfDay();

        $period = ProgressReportPeriod::where('status', 'open')
            ->whereNull('reminder_sent_at')
            ->orderByDesc('period_month')
            ->first();

        if (! $period) {
            $this->info('No open, un-reminded period found.');
            return self::SUCCESS;
        }

        $reminderDate = $period->due_date->copy()->subDays($settings->reminder_days_before)->startOfDay();
        if (! $today->equalTo($reminderDate)) {
            $this->info("Not yet the reminder date for {$period->period_month->format('F Y')} (reminder date: {$reminderDate->toDateString()}).");
            return self::SUCCESS;
        }

        $adminOfficerSection = collect(config('progress_report_sections'))->firstWhere('label', 'ADMINISTRATIVE OFFICER (DIANA KAIZA)');
        $sender = $adminOfficerSection ? User::find($adminOfficerSection['user_id']) : null;

        $pending = $period->participants()->where('status', 'pending')->with('user')->get();

        $sent = 0;
        foreach ($pending as $participant) {
            if (! $participant->user || ! $participant->user->email) {
                continue;
            }

            Mail::to($participant->user->email)
                ->send(new ProgressReportReminderMail($period->due_date->format('d M Y'), $sender));
            $sent++;
        }

        $period->update(['reminder_sent_at' => now()]);

        $this->info("Sent {$sent} reminder(s) for {$period->period_month->format('F Y')}.");
        return self::SUCCESS;
    }
}
