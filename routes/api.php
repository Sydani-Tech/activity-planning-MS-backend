<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\SubTaskController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Departments (read for all, write for admin)
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{department}', [DepartmentController::class, 'show']);

    // Activities (read for all authenticated, create for all, edit/delete restricted by role or status)
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{activity}', [ActivityController::class, 'show']);
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::put('/activities/{activity}', [ActivityController::class, 'update']);
    Route::put('/activities/{activity}/status', [ActivityController::class, 'updateStatus']);

    // Comments
    Route::get('/activities/{activity}/comments', [CommentController::class, 'index']);
    Route::post('/activities/{activity}/comments', [CommentController::class, 'store']);

    // SubTasks (Checklists)
    Route::get('/activities/{activity}/sub-tasks', [SubTaskController::class, 'index']);
    Route::post('/activities/{activity}/sub-tasks', [SubTaskController::class, 'store']);
    Route::put('/activities/{activity}/sub-tasks/{subTask}', [SubTaskController::class, 'update']);
    Route::delete('/activities/{activity}/sub-tasks/{subTask}', [SubTaskController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/departments', [DashboardController::class, 'departmentBreakdown']);
    Route::get('/dashboard/weekly', [DashboardController::class, 'weeklyProgress']);

    // Reports
    Route::get('/reports', [ReportController::class, 'generate']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Admin-only routes
    Route::middleware('role:super_admin,admin')->group(function () {
        // Users
        Route::apiResource('users', UserController::class);

        // Departments write
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);

        // Activities edit/delete and approval (structural changes)
        Route::delete('/activities/{activity}', [ActivityController::class, 'destroy']);

        // Excel import
        Route::post('/import/activities', [ImportController::class, 'import']);

        // Audit logs
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });

    // Activities approval (super_admin, admin, program_manager)
    Route::middleware('role:super_admin,admin,program_manager')->group(function () {
        Route::put('/activities/{activity}/approval', [ActivityController::class, 'approve']);
    });
});
