<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('description', 'like', "%{$escaped}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$escaped}%"));
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'actionLabels' => ActivityLogger::actionLabels(),
        ]);
    }
}
