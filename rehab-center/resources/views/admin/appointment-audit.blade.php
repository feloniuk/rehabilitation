@extends('layouts.admin')

@section('title', 'Аудит записів')
@section('page-title', 'Аудит дій з записами')

@section('content')
<div class="max-w-7xl">
    <div class="bg-white rounded-lg shadow">
        {{-- Фільтри --}}
        <div class="px-6 py-4 border-b bg-gray-50">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID запису</label>
                    <input type="number" name="appointment_id"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ request('appointment_id') }}" placeholder="ID">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дія</label>
                    <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Всі</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Створено</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Оновлено</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Видалено</option>
                        <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Відновлено</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Тип користувача</label>
                    <select name="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Всі</option>
                        <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Адмін</option>
                        <option value="master" {{ request('user_type') == 'master' ? 'selected' : '' }}>Майстер</option>
                        <option value="client" {{ request('user_type') == 'client' ? 'selected' : '' }}>Клієнт</option>
                        <option value="system" {{ request('user_type') == 'system' ? 'selected' : '' }}>Система</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дата від</label>
                    <input type="date" name="date_from"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ request('date_from') }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дата до</label>
                    <input type="date" name="date_to"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ request('date_to') }}">
                </div>

                <div class="col-span-full flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-filter"></i>Фільтрувати
                    </button>
                    <a href="{{ route('admin.appointment-audit.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-redo"></i>Скинути
                    </a>
                </div>
            </form>
        </div>

        {{-- Статистика --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 border-b bg-gray-50">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ $logs->total() }}</div>
                <div class="text-sm text-gray-600">Всього записів</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ $createdCount ?? 0 }}</div>
                <div class="text-sm text-gray-600">Створено</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-yellow-600">{{ $updatedCount ?? 0 }}</div>
                <div class="text-sm text-gray-600">Оновлено</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-red-600">{{ $deletedCount ?? 0 }}</div>
                <div class="text-sm text-gray-600">Видалено</div>
            </div>
        </div>

        {{-- Таблиця --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Дата/час</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Запис ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Дія</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Користувач</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">IP Адреса</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Дія</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $log->created_at->format('d.m.Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    #{{ $log->appointment_id }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @switch($log->action)
                                    @case('created')
                                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                            <i class="fas fa-plus mr-1"></i>Створено
                                        </span>
                                        @break
                                    @case('updated')
                                        <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                            <i class="fas fa-edit mr-1"></i>Оновлено
                                        </span>
                                        @break
                                    @case('deleted')
                                        <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                            <i class="fas fa-trash mr-1"></i>Видалено
                                        </span>
                                        @break
                                    @case('restored')
                                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                            <i class="fas fa-redo mr-1"></i>Відновлено
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    @if($log->user)
                                        {{ $log->user->name }}
                                    @else
                                        <span class="text-gray-400">Видалено</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($log->user_type === 'system')
                                        <span class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded">Система</span>
                                    @elseif($log->user_type === 'admin')
                                        <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded">Адмін</span>
                                    @elseif($log->user_type === 'master')
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded">Майстер</span>
                                    @else
                                        <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded">Клієнт</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="showAuditDetails({{ json_encode(['log' => [
                                    'id' => $log->id,
                                    'action' => $log->action,
                                    'old_values' => $log->old_values,
                                    'new_values' => $log->new_values,
                                    'appointment_date' => $log->appointment_date ?? null,
                                    'appointment_time' => $log->appointment_time ?? null
                                ], 'user' => $log->user?->only(['id', 'name', 'email']) ?? null]) }}, this)"
                                        class="text-blue-600 hover:text-blue-800 transition-colors font-medium">
                                    <i class="fas fa-eye mr-1"></i>Деталі
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p>Записів не знайдено</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагінація --}}
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $logs->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>
</div>

{{-- Модалка з деталями аудиту --}}
<div id="auditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Деталі аудиту</h3>
            <button onclick="closeAuditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="auditContent" class="p-6"></div>
        <div class="p-6 border-t flex gap-2 justify-end">
            <button onclick="closeAuditModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAuditDetails(data, element) {
    const modal = document.getElementById('auditModal');
    const content = document.getElementById('auditContent');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const { log, user } = data;
    let html = '<div class="space-y-4">';

    // Основна інформація
    html += `
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-3">Основна інформація</h4>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-600">ID Запису:</span>
                    <div class="font-semibold text-gray-900">${log.id}</div>
                </div>
                <div>
                    <span class="text-gray-600">Дія:</span>
                    <div class="font-semibold text-gray-900">${log.action}</div>
                </div>
            </div>
        </div>
    `;

    // Користувач
    if (user) {
        html += `
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Користувач</h4>
                <div class="text-sm">
                    <div><strong>${user.name}</strong></div>
                    <div class="text-gray-600">${user.email}</div>
                </div>
            </div>
        `;
    }

    // Старі значення
    if (log.old_values && Object.keys(log.old_values).length > 0) {
        html += '<div class="bg-red-50 rounded-lg p-4">';
        html += '<h4 class="font-semibold text-gray-900 mb-3">Попередні значення</h4>';
        html += '<div class="space-y-2 text-sm">';

        if (log.old_values.client) {
            html += `<div><strong>Клієнт:</strong> ${log.old_values.client.name} (${log.old_values.client.phone || 'N/A'})</div>`;
        }
        if (log.old_values.master) {
            html += `<div><strong>Майстер:</strong> ${log.old_values.master.name}</div>`;
        }
        if (log.old_values.service) {
            html += `<div><strong>Послуга:</strong> ${log.old_values.service.name}</div>`;
        }
        if (log.old_values.appointment_date) {
            html += `<div><strong>Дата:</strong> ${new Date(log.old_values.appointment_date).toLocaleDateString('uk-UA')} @ ${log.old_values.appointment_time || 'N/A'}</div>`;
        }
        if (log.old_values.status) {
            html += `<div><strong>Статус:</strong> ${log.old_values.status}</div>`;
        }

        html += '</div></div>';
    }

    // Нові значення
    if (log.new_values && Object.keys(log.new_values).length > 0) {
        html += '<div class="bg-green-50 rounded-lg p-4">';
        html += '<h4 class="font-semibold text-gray-900 mb-3">Нові значення</h4>';
        html += '<div class="space-y-2 text-sm">';

        Object.entries(log.new_values).forEach(([key, value]) => {
            if (key !== 'updated_at') {
                html += `<div><strong>${key}:</strong> ${value}</div>`;
            }
        });

        html += '</div></div>';
    }

    html += '</div>';
    content.innerHTML = html;
}

function closeAuditModal() {
    const modal = document.getElementById('auditModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAuditModal();
    }
});
</script>
@endpush

@endsection
