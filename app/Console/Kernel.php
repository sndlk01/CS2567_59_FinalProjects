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
        // $schedule->command('inspire')->hourly();
        
        // Run every day at 1 AM
        $schedule->command('tdbm:sync')->dailyAt('01:00');

        // Alternative schedules:
        // Every hour
        // $schedule->command('tdbm:sync')->hourly();

        // Every 6 hours
        // $schedule->command('tdbm:sync')->cron('0 */6 * * *');

        // Every Sunday at 1 AM
        // $schedule->command('tdbm:sync')->weekly()->sundays()->at('01:00');
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
