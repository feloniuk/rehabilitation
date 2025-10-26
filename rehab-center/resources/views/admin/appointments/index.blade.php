@extends('layouts.admin')

@section('title', 'Записи')
@section('page-title', 'Управління записами')

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Фильтры -->
    <div class="px-6 py-4 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.appointments.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Поиск по клиенту -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Клієнт</label>
                <input type="text" 
                       name="client_name" 
                       value="{{ request('client_name') }}"
                       placeholder="Ім'я, телефон, email"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Мастер -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Майстер</label>
                <select name="master_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Всі майстри</option>
                    @foreach($masters as $master)
                        <option value="{{ $master->id }}" {{ request('master_id') == $master->id ? 'selected' : '' }}>
                            {{ $master->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Услуга -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Послуга</label>
                <select name="service_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Всі послуги</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Статус -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Всі статуси</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Дата от -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Дата від</label>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Дата до -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Дата до</label>
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Кнопки -->
            <div class="md:col-span-6 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                    <i class="fas fa-search mr-1"></i>
                    Фільтрувати
                </button>
                <a href="{{ route('admin.appointments.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
                    <i class="fas fa-times mr-1"></i>
                    Очистити
                </a>
            </div>
        </form>
    </div>

    <!-- Заголовок с общей информацией -->
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Список записів</h3>
            <p class="text-sm text-gray-600">Знайдено записів: {{ $appointments->total() }}</p>
        </div>
    </div>

    <!-- Таблица -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клієнт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Майстер</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Послуга</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата/Час</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ціна</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($appointments as $appointment)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="showAppointmentDetails({{ $appointment->id }})">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $appointment->client->name }}</div>
                            <div class="text-sm text-gray-500">{{ $appointment->client->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $appointment->master->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $appointment->service->name }}</div>
                            <div class="text-xs text-gray-500">{{ $appointment->duration }} хв</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $appointment->appointment_date->format('d.m.Y') }}</div>
                            <div class="text-xs text-gray-500">{{ substr($appointment->appointment_time, 0, 5) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($appointment->price, 0) }} грн
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($appointment->status === 'scheduled')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Заплановано
                                </span>
                            @elseif($appointment->status === 'completed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Завершено
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Скасовано
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3" onclick="event.stopPropagation()">
                                <button onclick="showAppointmentDetails({{ $appointment->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                        title="Переглянути деталі">
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені що хочете видалити цей запис?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="Видалити запис">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-calendar-times text-3xl mb-2"></i>
                            <p>Записів не знайдено</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    @if($appointments->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $appointments->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Модальное окно с деталями записи -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="appointmentContent" class="p-6">
            <!-- Содержимое будет загружено через AJAX -->
        </div>
        
        <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
            <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAppointmentDetails(appointmentId) {
    // Показать модальное окно
    document.getElementById('appointmentModal').classList.remove('hidden');
    document.getElementById('appointmentModal').classList.add('flex');
    
    // Показать загрузку
    document.getElementById('appointmentContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
            <p class="text-gray-500 mt-2">Завантаження...</p>
        </div>
    `;
    
    // Загрузить данные
    fetch(`/admin/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('appointmentContent').innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Клієнт</h4>
                        <p class="text-lg font-medium">${data.client.name}</p>
                        <p class="text-sm text-gray-600">${data.client.phone}</p>
                        <p class="text-sm text-gray-600">${data.client.email}</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Майстер</h4>
                        <p class="text-lg font-medium">${data.master.name}</p>
                        <p class="text-sm text-gray-600">${data.master.phone}</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Послуга</h4>
                        <p class="text-lg font-medium">${data.service.name}</p>
                        <p class="text-sm text-gray-600">Тривалість: ${data.service.duration} хв</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Дата</h4>
                            <p class="text-lg font-medium">${data.appointment_date}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Час</h4>
                            <p class="text-lg font-medium">${data.appointment_time}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ціна</h4>
                            <p class="text-lg font-medium text-green-600">${data.price} грн</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Статус</h4>
                            <select id="statusSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" onchange="updateStatus(${data.id})">
                                <option value="scheduled" ${data.status === 'scheduled' ? 'selected' : ''}>Заплановано</option>
                                <option value="completed" ${data.status === 'completed' ? 'selected' : ''}>Завершено</option>
                                <option value="cancelled" ${data.status === 'cancelled' ? 'selected' : ''}>Скасовано</option>
                            </select>
                        </div>
                    </div>
                    
                    ${data.notes ? `
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Примітки</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">${data.notes}</p>
                    </div>
                    ` : ''}
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Створено</h4>
                        <p class="text-sm text-gray-600">${data.created_at}</p>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('appointmentContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-400"></i>
                    <p class="text-red-500 mt-2">Помилка завантаження даних</p>
                </div>
            `;
        });
}

function closeModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
    document.getElementById('appointmentModal').classList.remove('flex');
}

function updateStatus(appointmentId) {
    const status = document.getElementById('statusSelect').value;
    
    fetch(`/admin/appointments/${appointmentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновить статус в таблице
            location.reload();
        } else {
            alert('Помилка оновлення статусу');
        }
    })
    .catch(error => {
        alert('Помилка оновлення статусу');
    });
}

// Закрытие модального окна по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endpush
@endsection
