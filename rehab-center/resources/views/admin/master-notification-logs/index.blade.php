{{-- resources/views/admin/master-notification-logs/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Логи отправки уведомлений мастерам')
@section('page-title', 'Логи уведомлений мастерам')

@section('content')
<div class="max-w-7xl">
    <div class="bg-white rounded-lg shadow p-6">
        {{-- Фильтры --}}
        <div class="mb-6 border-b pb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Все</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Ожидание</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Отправлено ✓</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Ошибка ✗</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">С даты</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">По дату</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Фильтр
                    </button>
                    <a href="{{ route('admin.master-notification-logs.index') }}"
                       class="flex-1 bg-gray-500 text-white px-4 py-2 rounded text-center hover:bg-gray-600">
                        <i class="fas fa-redo mr-2"></i>Сброс
                    </a>
                </div>
            </form>
        </div>

        {{-- Статистика --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ $totalLogs }}</div>
                <div class="text-sm text-gray-600">Всего отправок</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ $sentLogs }}</div>
                <div class="text-sm text-gray-600">Успешно</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-red-600">{{ $failedLogs }}</div>
                <div class="text-sm text-gray-600">Ошибок</div>
            </div>
        </div>

        {{-- Таблица логов --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Мастер</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Запись</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Статус</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Источник</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Время</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $log->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $log->master->name }}</div>
                                <div class="text-xs text-gray-500">{{ $log->phone }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-700">{{ $log->appointment->service->name }}</div>
                                <div class="text-xs text-gray-500">{{ $log->appointment->appointment_date->format('d.m.Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($log->status === 'sent')
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        <i class="fas fa-check mr-1"></i>Отправлено
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                        <i class="fas fa-times mr-1"></i>Ошибка
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        <i class="fas fa-clock mr-1"></i>Ожидание
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($log->resolution_source)
                                    <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                                        {{ ucfirst($log->resolution_source) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-sm">
                                {{ $log->created_at->format('d.m.Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.master-notification-logs.show', $log) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    <i class="fas fa-eye mr-1"></i>Просмотр
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <div>Логов не найдено</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
