<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendActivityReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email and push notifications for upcoming and overdue activities.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        // Find upcoming activities (starting tomorrow)
        $upcomingActivities = \App\Models\Activity::where('status', 'pending')
            ->where('start_date', $tomorrow)
            ->get();

        foreach ($upcomingActivities as $activity) {
            $user = \App\Models\User::where('name', $activity->responsible_person)->first();
            if ($user) {
                $user->notify(new \App\Notifications\ActivityReminderNotification($activity, 'upcoming'));
            }
        }

        // Find overdue activities (end date passed, not completed)
        $overdueActivities = \App\Models\Activity::whereIn('status', ['pending', 'ongoing', 'delayed'])
            ->where('end_date', '<', $today)
            ->get();

        foreach ($overdueActivities as $activity) {
            $user = \App\Models\User::where('name', $activity->responsible_person)->first();
            if ($user) {
                $user->notify(new \App\Notifications\ActivityReminderNotification($activity, 'overdue'));
            }
        }

        $this->info('Activity reminders sent successfully.');
    }
}
