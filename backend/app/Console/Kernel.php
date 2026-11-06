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
        // Mutex 15 min: el comando recorre todos los tenants SQL y un backup RDS ~2 min.
        $schedule->command('partes:tasks-run-due')->everyMinute()->withoutOverlapping(15);
        $schedule->command('tasks:purge-history')->daily();
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
