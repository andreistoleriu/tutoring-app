<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReminderService;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send pending reminders to users';

    public function handle(ReminderService $reminderService): int
    {
        $this->info('Checking for pending reminders...');

        $sentCount = $reminderService->sendPendingReminders();

        $this->info("Successfully sent {$sentCount} reminders.");

        return 0;
    }
}
