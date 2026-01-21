<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentAuditLog;
use Illuminate\Http\Request;

class AppointmentAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AppointmentAuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Фільтр по appointment_id
        if ($request->filled('appointment_id')) {
            $query->where('appointment_id', $request->appointment_id);
        }

        // Фільтр по дії
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Фільтр по типу користувача
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Фільтр по даті
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Отримуємо статистику для карточок
        $createdCount = (clone $query)->where('action', 'created')->count();
        $updatedCount = (clone $query)->where('action', 'updated')->count();
        $deletedCount = (clone $query)->where('action', 'deleted')->count();

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.appointment-audit', compact('logs', 'createdCount', 'updatedCount', 'deletedCount'));
    }
}
