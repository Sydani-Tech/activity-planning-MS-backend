<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\ActivityUpdate;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\Carbon;

class MarkDelayedActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:mark-delayed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically scan and mark overdue activities as delayed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning for overdue activities...');

        $overdueActivities = Activity::where('approval_status', 'approved')
            ->whereIn('status', ['pending', 'ongoing'])
            ->whereDate('end_date', '<', Carbon::today())
            ->get();

        $count = 0;
        foreach ($overdueActivities as $activity) {
            $oldStatus = $activity->status;

            $activity->status = 'delayed';
            $activity->save();

            // Log update history
            ActivityUpdate::create([
                'activity_id' => $activity->id,
                'updated_by' => null, // System automated
                'remarks' => 'Activity has automatically been flagged as Delayed because it has passed its end date.',
                'status' => 'delayed',
            ]);

            // Create Audit log entry for system action
            AuditLog::create([
                'user_id' => null, // System
                'action' => 'status_updated_delayed',
                'model_type' => 'Activity',
                'model_id' => $activity->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'delayed'],
                'ip_address' => '127.0.0.1',
            ]);

            // Notify original creator 
            Notification::create([
                'user_id' => $activity->created_by,
                'activity_id' => $activity->id,
                'type' => 'overdue',
                'message' => "SYSTEM ALERT: Activity '{$activity->title}' is now overdue.",
            ]);

            $count++;
        }

        $this->info("Successfully marked {$count} activities as delayed.");
    }
}
