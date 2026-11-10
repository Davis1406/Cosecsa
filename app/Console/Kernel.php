<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Checks daily whether today is N days before the open report
        // period's due date, and if so emails everyone who hasn't yet
        // submitted their section. Guarded by reminder_sent_at so it only
        // fires once per period even if the server restarts mid-day.
        $schedule->command('progress-reports:send-reminders')->dailyAt('08:00');

        // While the CEO has not opened her private report link (emailed by
        // "Share with CEO"), remind her every couple of days (up to a limit);
        // it stops as soon as she opens the link.
        $schedule->command('progress-reports:remind-ceo-link')->dailyAt('08:05');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
