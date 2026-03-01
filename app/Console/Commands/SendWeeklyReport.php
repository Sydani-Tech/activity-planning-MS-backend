<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReport;
use App\Models\Activity;
use App\Models\ActivityUpdate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendWeeklyReport extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Send automated weekly activity report to executives and admins';

    public function handle()
    {
        $this->info('Generating weekly report...');

        $weekNumber = Carbon::now()->weekOfYear;

        // Build report data
        $total = Activity::count();
        $completed = Activity::where('status', 'completed')->count();
        $ongoing = Activity::where('status', 'ongoing')->count();
        $pending = Activity::where('status', 'pending')->count();
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        // Department breakdown
        $departments = Activity::select('department_id', 'status', DB::raw('count(*) as count'))
            ->groupBy('department_id', 'status')
            ->with('department:id,name')
            ->get()
            ->groupBy('department_id')
            ->map(function ($items) {
                $dept = $items->first()->department;
                return [
                    'department' => $dept ? $dept->name : 'Unassigned',
                    'completed' => $items->where('status', 'completed')->sum('count'),
                    'ongoing' => $items->where('status', 'ongoing')->sum('count'),
                    'pending' => $items->where('status', 'pending')->sum('count'),
                    'total' => $items->sum('count'),
                ];
            })
            ->values()
            ->toArray();

        // Recent updates this week
        $recentUpdates = ActivityUpdate::with(['activity', 'updater'])
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($update) {
                return [
                    'activity' => $update->activity->title ?? 'N/A',
                    'status' => $update->status,
                    'updated_by' => $update->updater->name ?? 'N/A',
                    'date' => $update->created_at->format('M d, H:i'),
                ];
            })
            ->toArray();

        $reportData = [
            'total' => $total,
            'completed' => $completed,
            'ongoing' => $ongoing,
            'pending' => $pending,
            'completion_rate' => $completionRate,
            'departments' => $departments,
            'recent_updates' => $recentUpdates,
        ];

        // Send to all executives and admins
        $recipients = User::whereIn('role', ['super_admin', 'admin', 'executive'])
            ->where('is_active', true)
            ->pluck('email')
            ->toArray();

        if (empty($recipients)) {
            $this->warn('No recipients found.');
            return;
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new WeeklyReport($reportData, $weekNumber));
                $this->info("Sent to: {$email}");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$email}: {$e->getMessage()}");
            }
        }

        $this->info("Weekly report sent to " . count($recipients) . " recipients.");
    }
}
