<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
    Commands\SendReminders::class,
    ];


    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send reminders every 15 minutes
            $schedule->command('reminders:send')
             ->everyFifteenMinutes()
             ->withoutOverlapping();

        // Clean up old reminders weekly
            $schedule->command('reminders:cleanup')
                    ->weekly()
                    ->sundays()
                    ->at('02:00');
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
