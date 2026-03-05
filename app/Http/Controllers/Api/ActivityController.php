<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ActivityStatusChanged;
use App\Models\Activity;
use App\Models\ActivityUpdate;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['department', 'creator']);

        // Department-level filtering for focal persons
        if ($request->user()->role === 'focal_person') {
            $query->where('department_id', $request->user()->department_id);
        }

        // Executives can only see approved activities
        if ($request->user()->role === 'executive') {
            $query->where('approval_status', 'approved');
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if (!in_array($request->user()->role, ['focal_person']) && $request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('week')) {
            $query->where('week', $request->week);
        }
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            });
        }

        if ($request->has('month')) {
            $query->whereMonth('start_date', $request->month);
        }

        if ($request->has('quarter')) {
            $quarter = $request->quarter;
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('start_date', '>=', $startMonth)
                ->whereMonth('start_date', '<=', $endMonth);
        }

        $sortBy = $request->get('sort_by', 'week');
        $sortDir = $request->get('sort_dir', 'asc');

        return response()->json($query->orderBy($sortBy, $sortDir)->paginate(25));
    }

    public function store(Request $request)
    {
        if ($request->user()->role === 'program_manager') {
            return response()->json(['message' => 'Program managers are not authorized to create activities.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'week' => 'nullable|integer|min:1|max:52',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'required|exists:departments,id',
            'responsible_person' => 'nullable|string|max:255',
            'means_of_verification' => 'nullable|string|max:255',
            'submission_requirement' => 'nullable|string|max:255',
            'status' => 'sometimes|in:pending,ongoing,completed,delayed',
            'remarks' => 'nullable|string',
        ]);

        $activity = Activity::create(array_merge(
            $request->only([
                'title',
                'description',
                'week',
                'start_date',
                'end_date',
                'department_id',
                'responsible_person',
                'means_of_verification',
                'submission_requirement',
                'status',
                'remarks',
            ]),
            [
                'created_by' => $request->user()->id,
                'approval_status' => in_array($request->user()->role, ['admin', 'super_admin', 'program_manager']) ? 'approved' : 'pending'
            ]
        ));

        // Audit log
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'created',
            'model_type' => 'Activity',
            'model_id' => $activity->id,
            'new_values' => $activity->toArray(),
            'ip_address' => $request->ip(),
        ]);

        // Send Notification if this was proposed by a Focal Person (so Admins know to review)
        if ($activity->approval_status === 'pending') {
            $managers = User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($managers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'activity_id' => $activity->id,
                    'type' => 'submitted',
                    'message' => "New activity pending approval: {$activity->title}",
                ]);
            }
        } else if ($activity->approval_status === 'approved') {
            // Notify focal persons in the assigned department when an admin creates an activity
            $focalPersons = User::where('department_id', $activity->department_id)
                ->where('role', 'focal_person')
                ->where('id', '!=', $request->user()->id)
                ->get();

            foreach ($focalPersons as $person) {
                $message = "A new activity '{$activity->title}' has been assigned to your department.";
                if ($activity->responsible_person === $person->name) {
                    $message = "You have been assigned a new activity: '{$activity->title}'.";
                }

                Notification::create([
                    'user_id' => $person->id,
                    'activity_id' => $activity->id,
                    'type' => 'assigned',
                    'message' => $message,
                ]);
            }
        }

        return response()->json($activity->load(['department', 'creator']), 201);
    }

    public function show(Activity $activity)
    {
        return response()->json($activity->load(['department', 'creator', 'updates.updater']));
    }

    public function update(Request $request, Activity $activity)
    {
        if ($request->user()->role === 'program_manager') {
            return response()->json(['message' => 'Program managers are not authorized to edit activity structure.'], 403);
        }

        // Governance block (Test Case 3) - Only admins/managers can edit an approved activity's structure
        if ($activity->approval_status === 'approved' && !in_array($request->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Activity structure cannot be modified after approval.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'week' => 'sometimes|nullable|integer|min:1|max:52',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'department_id' => 'sometimes|exists:departments,id',
            'responsible_person' => 'sometimes|nullable|string|max:255',
            'means_of_verification' => 'sometimes|nullable|string|max:255',
            'submission_requirement' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|in:pending,ongoing,completed,delayed',
            'remarks' => 'sometimes|nullable|string',
        ]);

        $oldValues = $activity->toArray();

        $activity->update($request->only([
            'title',
            'description',
            'week',
            'start_date',
            'end_date',
            'department_id',
            'responsible_person',
            'means_of_verification',
            'submission_requirement',
            'status',
            'remarks',
        ]));

        // Audit log
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'updated',
            'model_type' => 'Activity',
            'model_id' => $activity->id,
            'old_values' => $oldValues,
            'new_values' => $activity->fresh()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json($activity->fresh()->load(['department', 'creator']));
    }

    public function approve(Request $request, Activity $activity)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        $oldValues = $activity->toArray();
        $activity->approval_status = $request->status;
        $activity->save();

        if ($request->remarks) {
            $activity->updates()->create([
                'updated_by' => $request->user()->id,
                'status' => $activity->status, // keep existing progress status
                'remarks' => $request->remarks . ' (Approval Update)',
            ]);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'approval_updated',
            'model_type' => 'Activity',
            'model_id' => $activity->id,
            'old_values' => $oldValues,
            'new_values' => $activity->fresh()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        // Notify the original creator of the decision
        Notification::create([
            'user_id' => $activity->created_by,
            'activity_id' => $activity->id,
            'type' => $request->status,
            'message' => "Your activity '{$activity->title}' has been {$request->status}.",
        ]);

        return response()->json($activity->fresh()->load(['department', 'creator', 'updates.updater']));
    }

    public function verify(Request $request, Activity $activity)
    {
        if ($activity->status !== 'ongoing') {
            return response()->json(['message' => 'Only ongoing activities can be verified.'], 403);
        }

        $request->validate([
            'remarks' => 'nullable|string'
        ]);

        $oldValues = $activity->toArray();
        $activity->is_verified = true;
        $activity->save();

        if ($request->remarks) {
            $activity->updates()->create([
                'updated_by' => $request->user()->id,
                'status' => $activity->status,
                'remarks' => $request->remarks . ' (Activity Verified)',
            ]);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'verified',
            'model_type' => 'Activity',
            'model_id' => $activity->id,
            'old_values' => $oldValues,
            'new_values' => $activity->fresh()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        Notification::create([
            'user_id' => $activity->created_by,
            'activity_id' => $activity->id,
            'type' => 'verified',
            'message' => "Your activity '{$activity->title}' has been verified and is ready for completion.",
        ]);

        return response()->json($activity->fresh()->load(['department', 'creator', 'updates.updater']));
    }

    public function destroy(Request $request, Activity $activity)
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'deleted',
            'model_type' => 'Activity',
            'model_id' => $activity->id,
            'old_values' => $activity->toArray(),
            'ip_address' => $request->ip(),
        ]);

        $activity->delete();
        return response()->json(['message' => 'Activity deleted successfully.']);
    }

    /**
     * Update activity status (for focal persons).
     */
    public function updateStatus(Request $request, Activity $activity)
    {
        $request->validate([
            'status' => 'required|in:pending,ongoing,completed,delayed',
            'remarks' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($request->status === 'completed' && !$activity->is_verified) {
            return response()->json(['message' => 'This activity must be verified by a Manager or Admin before it can be marked as completed.'], 403);
        }

        $oldStatus = $activity->status;

        // Update the activity status
        $activity->update(['status' => $request->status]);

        // Create activity update record
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        ActivityUpdate::create([
            'activity_id' => $activity->id,
            'updated_by' => $request->user()->id,
            'remarks' => $request->remarks,
            'attachment' => $attachmentPath,
            'status' => $request->status,
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'status_updated',
            'model_type' => 'Activity',
            'model_id' => $activity->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status],
            'ip_address' => $request->ip(),
        ]);

        // Send email notifications to admins and executives
        if ($oldStatus !== $request->status) {
            $activity->load('department');
            $recipients = User::whereIn('role', ['super_admin', 'admin', 'executive'])
                ->where('is_active', true)
                ->pluck('email');

            foreach ($recipients as $email) {
                try {
                    Mail::to($email)->send(new ActivityStatusChanged(
                        $activity,
                        $oldStatus,
                        $request->user()->name
                    ));
                } catch (\Exception $e) {
                    // Log but don't fail the request
                    \Log::warning("Failed to send status notification to {$email}: " . $e->getMessage());
                }

                // Create In-App Notification
                $user = User::where('email', $email)->first();
                if ($user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'activity_id' => $activity->id,
                        'type' => 'status_updated',
                        'message' => "Activity '{$activity->title}' status changed to {$request->status}",
                    ]);
                }
            }
        }

        return response()->json($activity->fresh()->load(['department', 'creator', 'updates.updater']));
    }
}
