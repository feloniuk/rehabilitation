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

                <div class="col-span-full flex gap-2 justify-between items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors flex items-center gap-2">
                            <i class="fas fa-filter"></i>Фільтрувати
                        </button>
                        <a href="{{ route('admin.appointment-audit.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 text-sm font-medium transition-colors flex items-center gap-2">
                            <i class="fas fa-redo"></i>Скинути
                        </a>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Записей на сторінці</label>
                        <select name="per_page" class="w-auto px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
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
                                    'appointment_id' => $log->appointment_id,
                                    'action' => $log->action,
                                    'old_values' => $log->old_values,
                                    'new_values' => $log->new_values,
                                    'created_at' => $log->created_at->toIso8601String()
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

        {{-- Інформація про пагінацію та посилання --}}
        <div class="px-6 py-4 border-t bg-gray-50">
            <div class="flex justify-between items-center mb-4">
                <div class="text-sm text-gray-600">
                    Показано <span class="font-semibold">{{ $logs->count() }}</span> з <span class="font-semibold">{{ $logs->total() }}</span> записів
                    @if($logs->hasPages())
                        (сторінка {{ $logs->currentPage() }} з {{ $logs->lastPage() }})
                    @endif
                </div>
            </div>
            @if($logs->hasPages())
                {{ $logs->links('vendor.pagination.tailwind') }}
            @endif
        </div>
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

    // Parse JSON values if they are strings
    if (typeof log.old_values === 'string') {
        try {
            log.old_values = JSON.parse(log.old_values);
        } catch (e) {
            log.old_values = {};
        }
    }
    if (typeof log.new_values === 'string') {
        try {
            log.new_values = JSON.parse(log.new_values);
        } catch (e) {
            log.new_values = {};
        }
    }

    let html = '<div class="space-y-4">';

    // Заголовок з дією
    const actionColors = {
        'created': 'bg-green-100 text-green-800',
        'updated': 'bg-yellow-100 text-yellow-800',
        'deleted': 'bg-red-100 text-red-800',
        'restored': 'bg-blue-100 text-blue-800'
    };

    html += `
        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
            <div>
                <div class="text-xs text-gray-600 uppercase font-semibold">Запис №</div>
                <div class="text-3xl font-bold text-gray-900">${log.appointment_id}</div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-2 rounded-full ${actionColors[log.action] || 'bg-gray-100'} font-semibold text-sm">
                    ${formatAction(log.action)}
                </span>
                <div class="text-xs text-gray-600 mt-2">
                    ${new Date(log.created_at).toLocaleString('uk-UA')}
                </div>
            </div>
        </div>
    `;

    // Користувач
    if (user) {
        html += `
            <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                <h4 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-user-circle text-blue-600"></i>Виконав дію
                </h4>
                <div class="text-sm">
                    <div class="font-semibold text-gray-900">${user.name}</div>
                    <div class="text-gray-600 text-xs">${user.email}</div>
                </div>
            </div>
        `;
    }

    // Деталі запису (для created, deleted, restored)
    if (log.action === 'deleted' && log.old_values) {
        html += formatAppointmentDetails('Дані видаленої записи', log.old_values, 'red');
    } else if (log.action === 'created' && log.new_values) {
        html += formatAppointmentDetails('Дані створеної записи', log.new_values, 'green');
    } else if (log.action === 'restored' && log.new_values) {
        html += formatAppointmentDetails('Дані відновленої записи', log.new_values, 'blue');
    }

    // Зміни (для updated)
    if (log.action === 'updated' && log.old_values && log.new_values) {
        html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        html += formatAppointmentDetails('Було', log.old_values, 'red');
        html += formatAppointmentDetails('Стало', log.new_values, 'green');
        html += '</div>';
    }

    html += '</div>';
    content.innerHTML = html;
}

function formatAction(action) {
    const actions = {
        'created': '✓ Створено',
        'updated': '✎ Оновлено',
        'deleted': '✗ Видалено',
        'restored': '↻ Відновлено'
    };
    return actions[action] || action;
}

function formatAppointmentDetails(title, data, color = 'blue') {
    const colorMap = {
        'red': 'bg-red-50 border-red-500',
        'green': 'bg-green-50 border-green-500',
        'blue': 'bg-blue-50 border-blue-500',
        'yellow': 'bg-yellow-50 border-yellow-500'
    };

    let html = `<div class="bg-${color}-50 rounded-lg p-4 border-l-4 border-${color}-500">`;
    html += `<h4 class="font-semibold text-gray-900 mb-4">${title}</h4>`;
    html += '<div class="space-y-3 text-sm">';

    // Клієнт
    if (data.client && (data.client.name || data.client.id)) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">👤 Клієнт</div>
                <div class="font-semibold text-gray-900">${data.client.name || '—'}</div>
                <div class="text-gray-600 text-xs">
                    ${data.client.phone ? `📞 ${data.client.phone}` : ''}
                    ${data.client.email ? `<div>✉️ ${data.client.email}</div>` : ''}
                </div>
            </div>
        `;
    }

    // Майстер
    if (data.master && (data.master.name || data.master.id)) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">🧑‍💼 Майстер</div>
                <div class="font-semibold text-gray-900">${data.master.name || '—'}</div>
                <div class="text-gray-600 text-xs">
                    ${data.master.phone ? `📞 ${data.master.phone}` : ''}
                </div>
            </div>
        `;
    }

    // Послуга
    if (data.service && (data.service.name || data.service.id)) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">🛠️ Послуга</div>
                <div class="font-semibold text-gray-900">${data.service.name || '—'}</div>
                <div class="text-gray-600 text-xs">
                    ${data.service.duration ? `⏱️ ${data.service.duration} хв` : ''}
                </div>
            </div>
        `;
    }

    // Дата і час
    if (data.appointment_date || data.appointment_time) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">📅 Дата та час</div>
                <div class="font-semibold text-gray-900">
                    ${data.appointment_date ? new Date(data.appointment_date).toLocaleDateString('uk-UA') : '—'}
                    ${data.appointment_time ? `@ ${data.appointment_time}` : ''}
                </div>
            </div>
        `;
    }

    // Тривалість
    if (data.duration) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">⏱️ Тривалість</div>
                <div class="font-semibold text-gray-900">${data.duration} хв</div>
            </div>
        `;
    }

    // Ціна
    if (data.price) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">💰 Ціна</div>
                <div class="font-bold text-green-600 text-lg">${parseFloat(data.price).toFixed(2)} грн</div>
            </div>
        `;
    }

    // Статус
    if (data.status) {
        const statusMap = {
            'scheduled': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Заплановано</span>',
            'completed': '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Завершено</span>',
            'cancelled': '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Скасовано</span>'
        };
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">📊 Статус</div>
                <div>${statusMap[data.status] || data.status}</div>
            </div>
        `;
    }

    // Підтвердження
    if (data.is_confirmed !== undefined) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">✓ Підтверджено</div>
                <div class="font-semibold text-gray-900">${data.is_confirmed ? '✓ Так' : '✗ Ні'}</div>
            </div>
        `;
    }

    // Примітки
    if (data.notes) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">📝 Примітки</div>
                <div class="text-gray-700 whitespace-pre-wrap">${data.notes}</div>
            </div>
        `;
    }

    // Telegram сповіщення
    if (data.telegram_notification_sent !== undefined) {
        html += `
            <div class="bg-white rounded p-3">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">💬 Telegram сповіщення</div>
                <div class="font-semibold text-gray-900">
                    ${data.telegram_notification_sent ? '✓ Відправлено' : '✗ Не відправлено'}
                </div>
            </div>
        `;
    }

    html += '</div></div>';
    return html;
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
