<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\UserWelcomeEmail;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('department');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        if ($perPage > 100)
            $perPage = 100;

        return response()->json($query->orderBy('name')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:super_admin,admin,focal_person,program_manager,executive',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $plaintextPassword = Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($plaintextPassword),
            'role' => $request->role,
            'department_id' => $request->department_id,
        ]);

        try {
            Mail::to($user->email)->send(new UserWelcomeEmail($user, $plaintextPassword));
        } catch (\Exception $e) {
            \Log::warning("Failed to send welcome email to {$user->email}: " . $e->getMessage());
        }

        return response()->json($user->load('department'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('department'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'role' => 'sometimes|in:super_admin,admin,focal_person,program_manager,executive',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'role', 'department_id', 'is_active']));

        if ($request->has('password') && $request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json($user->fresh()->load('department'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }
}
