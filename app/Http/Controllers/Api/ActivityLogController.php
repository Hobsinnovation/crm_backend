<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user:id,name,role')
            ->when($request->user_id, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($request->action, fn ($q, $action) => $q->where('action', $action))
            ->when($request->model, fn ($q, $model) => $q->where('model', $model))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('action', 'like', "%{$search}%")
                          ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }

    /**
     * Filter dropdowns ke liye — users aur distinct actions
     */
    public function filters()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'users'   => User::select('id', 'name')->orderBy('name')->get(),
                'actions' => ActivityLog::distinct()->pluck('action'),
                'models'  => ActivityLog::whereNotNull('model')->distinct()->pluck('model'),
            ],
        ]);
    }
}