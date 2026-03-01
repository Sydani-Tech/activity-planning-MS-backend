<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $query = Activity::where('approval_status', 'approved');

        // Focal persons see only their department
        if ($request->user()->role === 'focal_person') {
            $query->where('department_id', $request->user()->department_id);
        }

        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $ongoing = (clone $query)->where('status', 'ongoing')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $delayed = (clone $query)->where('status', 'delayed')->count();

        return response()->json([
            'total' => $total,
            'completed' => $completed,
            'ongoing' => $ongoing,
            'pending' => $pending,
            'delayed' => $delayed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ]);
    }

    public function departmentBreakdown()
    {
        $data = Activity::where('approval_status', 'approved')
            ->select('department_id', 'status', DB::raw('count(*) as count'))
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
                    'delayed' => $items->where('status', 'delayed')->sum('count'),
                    'total' => $items->sum('count'),
                ];
            })
            ->values();

        return response()->json($data);
    }

    public function weeklyProgress()
    {
        $data = Activity::where('approval_status', 'approved')
            ->select('week', 'status', DB::raw('count(*) as count'))
            ->whereNotNull('week')
            ->groupBy('week', 'status')
            ->orderBy('week')
            ->get()
            ->groupBy('week')
            ->map(function ($items, $week) {
                return [
                    'week' => $week,
                    'completed' => $items->where('status', 'completed')->sum('count'),
                    'ongoing' => $items->where('status', 'ongoing')->sum('count'),
                    'pending' => $items->where('status', 'pending')->sum('count'),
                    'delayed' => $items->where('status', 'delayed')->sum('count'),
                    'total' => $items->sum('count'),
                ];
            })
            ->values();

        return response()->json($data);
    }
}
