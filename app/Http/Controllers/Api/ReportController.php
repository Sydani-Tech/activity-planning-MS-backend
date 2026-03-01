<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $query = Activity::with(['department', 'creator']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('person')) {
            $query->where('responsible_person', 'like', '%' . $request->person . '%');
        }

        $activities = $query->get();

        if ($request->get('format') === 'csv') {
            return $this->exportCsv($activities);
        }

        // Return JSON by default with some generic multi-dimensional summary
        $summary = [
            'total' => $activities->count(),
            'by_status' => $activities->groupBy('status')->map->count(),
            'by_department' => $activities->groupBy('department.name')->map->count(),
            'completion_rate' => $activities->count() > 0
                ? round(($activities->where('status', 'completed')->count() / $activities->count()) * 100, 2)
                : 0
        ];

        return response()->json([
            'summary' => $summary,
            'data' => $activities
        ]);
    }

    private function exportCsv($activities)
    {
        $filename = "activities_report_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'ID',
            'Title',
            'Description',
            'Week',
            'Start Date',
            'End Date',
            'Department',
            'Responsible Person',
            'Status',
            'Approval Status',
            'Means of Verification'
        ];

        $callback = function () use ($activities, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($activities as $activity) {
                $row = [
                    $activity->id,
                    $activity->title,
                    $activity->description,
                    $activity->week,
                    $activity->start_date ? $activity->start_date->format('Y-m-d') : '',
                    $activity->end_date ? $activity->end_date->format('Y-m-d') : '',
                    $activity->department ? $activity->department->name : '',
                    $activity->responsible_person,
                    $activity->status,
                    $activity->approval_status,
                    $activity->means_of_verification
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
