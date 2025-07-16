<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReminderService;

class CleanupOldReminders extends Command
{
    protected $signature = 'reminders:cleanup {--days=30 : Number of days old reminders to delete}';
    protected $description = 'Clean up old sent reminders';

    public function handle(ReminderService $reminderService): int
    {
        $days = $this->option('days');

        $this->info("Cleaning up reminders older than {$days} days...");

        $deletedCount = $reminderService->deleteOldReminders($days);

        $this->info("Successfully deleted {$deletedCount} old reminders.");

        return 0;
    }
}
