<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\SubTask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubTaskController extends Controller
{
    public function index(Activity $activity)
    {
        return response()->json($activity->subTasks);
    }

    public function store(Request $request, Activity $activity)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subTask = $activity->subTasks()->create([
            'title' => $request->title,
            'is_completed' => false,
        ]);

        return response()->json($subTask, 201);
    }

    public function update(Request $request, Activity $activity, SubTask $subTask)
    {
        // Ensure subTask belongs to the activity
        if ($subTask->activity_id !== $activity->id) {
            abort(404);
        }

        $request->validate([
            'is_completed' => 'required|boolean',
        ]);

        $subTask->update([
            'is_completed' => $request->is_completed,
        ]);

        return response()->json($subTask);
    }

    public function destroy(Activity $activity, SubTask $subTask)
    {
        if ($subTask->activity_id !== $activity->id) {
            abort(404);
        }

        $subTask->delete();

        return response()->json(null, 204);
    }
}
