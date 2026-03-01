<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return response()->json(Department::withCount('activities', 'users')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:500',
        ]);

        $department = Department::create($request->only(['name', 'description']));

        return response()->json($department, 201);
    }

    public function show(Department $department)
    {
        return response()->json($department->loadCount('activities', 'users'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'sometimes|nullable|string|max:500',
        ]);

        $department->update($request->only(['name', 'description']));

        return response()->json($department->fresh());
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['message' => 'Department deleted successfully.']);
    }
}
