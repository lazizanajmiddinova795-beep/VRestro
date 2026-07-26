<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * GET /api/activity-logs
     * Admin only — returns paginated activity logs with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by action_type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $perPage = min((int) $request->get('per_page', 50), 200);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * DELETE /api/activity-logs/clear
     * Admin only — clears all logs (optional utility).
     */
    public function clear(): JsonResponse
    {
        ActivityLog::truncate();
        return response()->json(['message' => 'Jurnal tozalandi.']);
    }
}
