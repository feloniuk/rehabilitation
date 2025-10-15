@extends('layouts.admin')

@section('title', 'Історія розсилок')
@section('page-title', 'Історія відправлених повідомлень')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.notifications.index') }}" 
       class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i>
        Повернутись до розсилок
    </a>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Історія відправлень</h3>
        <p class="text-sm text-gray-600">Всі відправлені повідомлення через Telegram</p>
    </div>

    <div class="overflow-x-auto">
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
                                {{ $log->appointment->client->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->template ? $log->template->name : 'Без шаблону' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->appointment->appointment_date->format('d.m.Y') }}
                            {{ substr($log->appointment->appointment_time, 0, 5) }}<br>
                            <span class="text-xs">{{ $log->appointment->service->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->status === 'sent')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Відправлено
                                </span>
                                @if($log->sent_at)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $log->sent_at->format('d.m.Y H:i') }}
                                    </div>
                                @endif
                            @elseif($log->status === 'failed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>
                                    Помилка
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Очікує
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="showMessage({{ $log->id }})" 
                                    class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i> Переглянути
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

    <div class="px-6 py-4 border-t">
        {{ $logs->links() }}
    </div>
</div>

{{-- Модальне вікно з повідомленням --}}
<div id="message-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold">Текст повідомлення</h3>
            <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div id="message-content" class="bg-gray-50 border border-gray-200 rounded p-4 whitespace-pre-line text-sm">
            </div>
            
            <div id="error-content" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded text-sm text-red-800">
            </div>
        </div>
        
        <div class="flex justify-end p-6 border-t bg-gray-50">
            <button onclick="closeMessageModal()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Створюємо об'єкт з даними логів
const logsData = {};
@foreach($logs as $log)
logsData[{{ $log->id }}] = {
    id: {{ $log->id }},
    message: {!! json_encode($log->message) !!},
    error: {!! json_encode($log->error_message) !!},
    status: "{{ $log->status }}"
};
@endforeach

function showMessage(logId) {
    const log = logsData[logId];
    
    if (!log) return;
    
    document.getElementById('message-content').textContent = log.message;
    
    const errorContent = document.getElementById('error-content');
    if (log.status === 'failed' && log.error) {
        errorContent.innerHTML = '<strong>Помилка:</strong> ' + log.error;
        errorContent.classList.remove('hidden');
    } else {
        errorContent.classList.add('hidden');
    }
    
    document.getElementById('message-modal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('message-modal').classList.add('hidden');
}

// Закриття по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});
</script>
@endpush
@endsection