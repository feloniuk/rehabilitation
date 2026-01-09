@extends('layouts.admin')

@section('title', 'Історія розсилок')
@section('page-title', 'Історія відправлених повідомлень')

@section('content')
<div class="mb-6">
    <a href="{{ route('tenant.admin.notifications.index', ['tenant' => app('currentTenant')->slug]) }}" 
       class="text-blue-600 hover:text-blue-800 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Повернутись до розсилок
    </a>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">
                <i class="fas fa-history text-blue-600 mr-2"></i>
                Історія відправлень
            </h3>
            <p class="text-sm text-gray-600">Всі відправлені повідомлення через Telegram</p>
        </div>

        {{-- Фільтри для мобільної та десктоп версії --}}
        <div class="mt-4 md:mt-0 w-full md:w-auto flex space-x-2">
            <select class="w-full md:w-auto px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option>За останній місяць</option>
                <option>За тиждень</option>
                <option>За рік</option>
            </select>
            <select class="w-full md:w-auto px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option>Всі статуси</option>
                <option>Відправлені</option>
                <option>Помилка</option>
            </select>
        </div>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клієнт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Телефон</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Шаблон</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Запис</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">
                                {{ $log->appointment?->client?->name ?? 'Без клієнта' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->template ? $log->template->name : 'Без шаблону' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($log->appointment)
                                {{ $log->appointment->appointment_date->format('d.m.Y') }}
                                {{ substr($log->appointment->appointment_time, 0, 5) }}<br>
                                <span class="text-xs">{{ $log->appointment->service->name }}</span>
                            @else
                                <span class="text-gray-400"><i class="fas fa-trash-alt mr-1"></i>Запись удалена</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($log->status)
                                @case('sent')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Відправлено
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>
                                        Помилка
                                    </span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Очікує
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="showMessage({{ $log->id }})" 
                                    class="text-blue-600 hover:text-blue-900 transition-colors"
                                    title="Переглянути повідомлення">
                                <i class="fas fa-eye text-lg"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Історія розсилок порожня</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($logs as $log)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            {{ $log->appointment?->client?->name ?? 'Без клієнта' }}
                        </h3>
                        <p class="text-sm text-gray-500">{{ $log->phone }}</p>
                    </div>
                    <button onclick="showMessage({{ $log->id }})"
                            class="text-blue-600"
                            title="Переглянути повідомлення">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Шаблон:</span>
                        <span>{{ $log->template ? $log->template->name : 'Без шаблону' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Запис:</span>
                        <span>
                            @if($log->appointment)
                                {{ $log->appointment->appointment_date->format('d.m.Y') }}
                                {{ substr($log->appointment->appointment_time, 0, 5) }}
                            @else
                                <span class="text-gray-400"><i class="fas fa-trash-alt mr-1"></i>Удалена</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Статус:</span>
                        @switch($log->status)
                            @case('sent')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    Відправлено
                                </span>
                                @break
                            @case('failed')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                    Помилка
                                </span>
                                @break
                            @default
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                                    Очікує
                                </span>
                        @endswitch
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Дата:</span>
                        <span>{{ $log->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Історія розсилок порожня</p>
            </div>
        @endforelse
    </div>

    @if($logs->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $logs->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

{{-- Модальне вікно з повідомленням --}}
<div id="message-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Текст повідомлення</h3>
            <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-4">
            <div id="message-content" class="bg-gray-50 border border-gray-200 rounded p-4 whitespace-pre-line text-sm"></div>
            
            <div id="error-content" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded text-sm text-red-800">
            </div>
        </div>
        
        <div class="flex justify-end p-4 border-t bg-gray-50">
            <button onclick="closeMessageModal()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 w-full md:w-auto">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const logsData = {};
@foreach($logs as $log)
logsData[{{ $log->id }}] = {
    id: {{ $log->id }},
    message: `{!! addslashes($log->message) !!}`,
    error: `{!! addslashes($log->error_message ?? '') !!}`,
    status: "{{ $log->status }}"
};
@endforeach

function showMessage(logId) {
    const log = logsData[logId];
    
    if (!log) return;
    
    const modal = document.getElementById('message-modal');
    const messageContent = document.getElementById('message-content');
    const errorContent = document.getElementById('error-content');
    
    messageContent.textContent = log.message;
    
    if (log.status === 'failed' && log.error) {
        errorContent.innerHTML = '<strong>Помилка:</strong> ' + log.error;
        errorContent.classList.remove('hidden');
    } else {
        errorContent.classList.add('hidden');
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeMessageModal() {
    const modal = document.getElementById('message-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});
</script>
@endpush
@endsection