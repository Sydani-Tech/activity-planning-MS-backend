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
        // Send weekly activity report every Monday at 8:00 AM
        $schedule->command('report:weekly')->weeklyOn(1, '8:00');

        // Auto-mark overdue approved activities as "Delayed" every night at midnight 
        $schedule->command('activities:mark-delayed')->daily();

        // Send activity reminders
        $schedule->command('activities:send-reminders')->dailyAt('08:00');

        // Database backups
        $schedule->command('backup:database')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
