@extends('layouts.admin')

@section('title', 'Розсилки')
@section('page-title', 'Модуль розсилок')

@section('content')
{{-- Статистика --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Відправлено всього</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['total_sent'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Відправлено сьогодні</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['sent_today'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-paper-plane text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Помилки</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['total_failed'] }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

{{-- Швидкі дії --}}
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <h3 class="text-lg font-semibold flex-grow">Швидкі дії</h3>
            <div class="flex space-x-2 w-full md:w-auto">
                <a href="{{ route('admin.notifications.templates') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                    <i class="fas fa-file-alt mr-2"></i>
                    Шаблони
                </a>
                <a href="{{ route('admin.notifications.logs') }}" 
                   class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                    <i class="fas fa-history mr-2"></i>
                    Історія
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Форма розсилки --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-4 border-b">
        <h3 class="text-lg font-semibold flex items-center">
            <i class="fas fa-bullhorn text-purple-600 mr-2"></i>
            Створити розсилку
        </h3>
        <p class="text-sm text-gray-600 mt-1">Оберіть шаблон та записи для відправки нагадувань</p>
    </div>

    <form method="POST" action="{{ route('admin.notifications.send') }}" id="notification-form">
        @csrf

        <div class="p-4 space-y-4">
            {{-- Вибір шаблону --}}
            <div>
                <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Оберіть шаблон повідомлення *
                </label>
                <select id="template_id" name="template_id" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Оберіть шаблон</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('template_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Попередній перегляд --}}
            <div id="preview-section" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Попередній перегляд
                </label>
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div id="preview-content" class="bg-white border border-gray-200 rounded p-3 whitespace-pre-line text-sm"></div>
                </div>
            </div>

            {{-- Таблиця записів --}}
            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-medium text-gray-700">
                        Оберіть записи для розсилки *
                    </label>
                    <div class="flex gap-2">
                        <button type="button" onclick="selectAll()" 
                                class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fas fa-check-square mr-1"></i>
                            Вибрати всі
                        </button>
                        <button type="button" onclick="deselectAll()" 
                                class="text-sm text-red-600 hover:text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>
                            Скасувати вибір
                        </button>
                    </div>
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden md:block overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" id="select-all" 
                                           class="w-4 h-4" onchange="toggleAll(this)">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клієнт</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Майстер</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Послуга</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата/Час</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Телефон</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($upcomingAppointments as $appointment)
                                <tr class="hover:bg-gray-50 appointment-row">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="appointment_ids[]" 
                                               value="{{ $appointment->id }}" 
                                               class="appointment-checkbox w-4 h-4"
                                               data-appointment-id="{{ $appointment->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $appointment->client->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $appointment->master->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $appointment->service->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $appointment->appointment_date->format('d.m.Y') }}<br>
                                        <span class="text-gray-500">{{ substr($appointment->appointment_time, 0, 5) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $appointment->client->phone }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-3xl mb-2"></i>
                                        <p>Немає майбутніх записів</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="md:hidden space-y-4">
                    @forelse($upcomingAppointments as $appointment)
                        <div class="bg-white rounded-lg shadow-md border p-4">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $appointment->client->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $appointment->client->phone }}</p>
                                </div>
                                <input type="checkbox" name="appointment_ids[]" 
                                       value="{{ $appointment->id }}" 
                                       class="appointment-checkbox w-4 h-4"
                                       data-appointment-id="{{ $appointment->id }}">
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Майстер:</span>
                                    <span>{{ $appointment->master->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Послуга:</span>
                                    <span>{{ $appointment->service->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Дата/Час:</span>
                                    <span>
                                        {{ $appointment->appointment_date->format('d.m.Y') }} 
                                        {{ substr($appointment->appointment_time, 0, 5) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Немає майбутніх записів</p>
                        </div>
                    @endforelse
                </div>

                @if($upcomingAppointments->hasPages())
                    <div class="mt-4">
                        {{ $upcomingAppointments->links('vendor.pagination.tailwind') }}
                    </div>
                @endif

                @error('appointment_ids')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Лічильник обраних --}}
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Обрано записів: <strong id="selected-count">0</strong>
                </p>
            </div>
        </div>

        <div class="px-4 py-4 bg-gray-50 border-t flex justify-end">
            <button type="submit" id="send-button" disabled
                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-auto">
                <i class="fas fa-paper-plane mr-2"></i>
                Відправити розсилку
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.appointment-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateSelectedCount();
    updatePreview();
}

function selectAll() {
    document.querySelectorAll('.appointment-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('select-all').checked = true;
    updateSelectedCount();
    updatePreview();
}

function deselectAll() {
    document.querySelectorAll('.appointment-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all').checked = false;
    updateSelectedCount();
    updatePreview();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.appointment-checkbox:checked').length;
    document.getElementById('selected-count').textContent = count;
    
    const sendButton = document.getElementById('send-button');
    const templateSelect = document.getElementById('template_id');
    
    sendButton.disabled = count === 0 || !templateSelect.value;
}

function updatePreview() {
    const templateId = document.getElementById('template_id').value;
    const firstChecked = document.querySelector('.appointment-checkbox:checked');
    const previewSection = document.getElementById('preview-section');
    
    if (!templateId || !firstChecked) {
        previewSection.classList.add('hidden');
        return;
    }
    
    const appointmentId = firstChecked.dataset.appointmentId;
    
    fetch('/admin/notifications/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            template_id: templateId,
            appointment_id: appointmentId
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('preview-content').textContent = data.preview;
        previewSection.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.appointment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updatePreview();
        });
    });
    
    document.getElementById('template_id').addEventListener('change', function() {
        updateSelectedCount();
        updatePreview();
    });
    
    document.getElementById('notification-form').addEventListener('submit', function(e) {
        const count = document.querySelectorAll('.appointment-checkbox:checked').length;
        
        if (!confirm(`Ви впевнені, що хочете відправити ${count} повідомлень?`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection