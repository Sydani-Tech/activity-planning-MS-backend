<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user:id,name,email');

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(25)
        );
    }
}
