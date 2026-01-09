<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterNotificationLog;
use Illuminate\Http\Request;

class MasterNotificationLogController extends Controller
{
    public function index($tenant, Request $request)
    {
        $query = MasterNotificationLog::with(['master', 'appointment.service'])
            ->orderBy('created_at', 'desc');

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтр по мастеру
        if ($request->filled('master_id')) {
            $query->where('master_id', $request->master_id);
        }

        // Фильтр по дате
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Статистика
        $totalLogs = MasterNotificationLog::count();
        $sentLogs = MasterNotificationLog::where('status', 'sent')->count();
        $failedLogs = MasterNotificationLog::where('status', 'failed')->count();

        return view('admin.master-notification-logs.index', compact('logs', 'totalLogs', 'sentLogs', 'failedLogs'));
    }

    public function show($tenant, MasterNotificationLog $log)
    {
        $log->load(['master', 'appointment.service', 'appointment.client']);

        return view('admin.master-notification-logs.show', compact('log'));
    }
}
