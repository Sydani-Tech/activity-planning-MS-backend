<?php

namespace App\Imports;

use App\Models\Activity;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class ActivitiesImport implements ToModel, WithHeadingRow
{
    protected $userId;
    protected $rowCount = 0;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['activity']) && empty($row['title'])) {
            return null;
        }

        // Try to find or create department from responsible person/team
        $deptName = $row['responsible_personteam'] ?? $row['responsible_person'] ?? $row['team'] ?? 'General';
        $department = Department::firstOrCreate(['name' => trim($deptName)]);

        // Normalize status
        $status = 'pending';
        $rawStatus = strtolower(trim($row['status'] ?? ''));
        if (in_array($rawStatus, ['done', 'completed', 'complete'])) {
            $status = 'completed';
        } elseif (in_array($rawStatus, ['in progress', 'ongoing', 'in-progress'])) {
            $status = 'ongoing';
        } elseif (in_array($rawStatus, ['delayed', 'overdue', 'late'])) {
            $status = 'delayed';
        }

        // Parse dates
        $startDate = null;
        $endDate = null;
        try {
            if (!empty($row['date'])) {
                $startDate = Carbon::parse($row['date']);
                $endDate = $startDate->copy();
            }
            if (!empty($row['start_date'])) {
                $startDate = Carbon::parse($row['start_date']);
            }
            if (!empty($row['end_date'])) {
                $endDate = Carbon::parse($row['end_date']);
            }
        } catch (\Exception $e) {
            $startDate = now();
            $endDate = now();
        }

        if (!$startDate)
            $startDate = now();
        if (!$endDate)
            $endDate = $startDate;

        $this->rowCount++;

        return new Activity([
            'title' => $row['activity'] ?? $row['title'] ?? 'Untitled',
            'description' => $row['description'] ?? null,
            'week' => $row['week'] ?? $row['wk'] ?? null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'department_id' => $department->id,
            'created_by' => $this->userId,
            'responsible_person' => $row['responsible_personteam'] ?? $row['responsible_person'] ?? null,
            'means_of_verification' => $row['means_of_verification'] ?? null,
            'submission_requirement' => $row['submission_requirement'] ?? null,
            'status' => $status,
            'approval_status' => 'approved', // Bulk imports by Admins are automatically approved
            'remarks' => $row['remarks'] ?? null,
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
