@extends('layouts.admin')

@section('title', 'Записи')
@section('page-title', 'Управління записами')

@section('content')
<div class="bg-white rounded-lg shadow">
    {{-- Фільтри --}}
    <div class="px-4 py-4 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.appointments.index') }}" 
              class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Клієнт --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Клієнт</label>
                <input type="text" 
                       name="client_name" 
                       value="{{ request('client_name') }}"
                       placeholder="Ім'я, телефон, email"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Майстер (тільки для адміна) --}}
            @if(auth()->user()->isAdmin())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Майстер</label>
                    <select name="master_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Всі майстри</option>
                        @foreach($masters as $master)
                            <option value="{{ $master->id }}" 
                                    {{ request('master_id') == $master->id ? 'selected' : '' }}>
                                {{ $master->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Статус --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                <select name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Всі статуси</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" 
                                {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Кнопки --}}
            <div class="col-span-full flex gap-2 mt-2">
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm w-full md:w-auto">
                    <i class="fas fa-search mr-1"></i>Фільтрувати
                </button>
                <a href="{{ route('admin.appointments.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm w-full md:w-auto text-center">
                    <i class="fas fa-times mr-1"></i>Скинути
                </a>
            </div>
        </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клієнт</th>
                    @if(auth()->user()->isAdmin())
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Майстер</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Послуга</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата/Час</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($appointments as $appointment)
                    <tr class="hover:bg-gray-50 cursor-pointer">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900 max-w-[75px] truncate" title="{{ $appointment->client->name }}">
                                {{ $appointment->client->name }}
                            </div>
                            <div class="text-sm text-gray-500 max-w-[75px] truncate" title="{{ $appointment->client->phone }}">
                                {{ $appointment->client->phone }}
                            </div>
                        </td>
                        @if(auth()->user()->isAdmin())
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-[120px] truncate" title="{{ $appointment->master->name }}">
                                {{ $appointment->master->name }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 max-w-[75px] truncate" title="{{ $appointment->service->name }}">
                                {{ $appointment->service->name }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $appointment->duration }} хв</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $appointment->appointment_date->format('d.m.Y') }}</div>
                            <div class="text-xs text-gray-500">{{ substr($appointment->appointment_time, 0, 5) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($appointment->status)
                                @case('scheduled')
                                    <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Заплановано
                                    </span>
                                    @break
                                @case('completed')
                                    <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Завершено
                                    </span>
                                    @break
                                @default
                                    <span style="
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                " class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Скасовано
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <button onclick="showAppointmentDetails({{ $appointment->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                        title="Деталі">
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" 
                                    class="inline" onsubmit="return confirm('Ви впевнені?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="Видалити">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? '6' : '5' }}" 
                            class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-3"></i>
                            <p>Записів не знайдено</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($appointments as $appointment)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $appointment->client->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $appointment->client->phone }}</p>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="showAppointmentDetails({{ $appointment->id }})" 
                                class="text-blue-600"
                                title="Деталі">
                            <i class="fas fa-eye"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" 
                              class="inline" onsubmit="return confirm('Ви впевнені?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600"
                                    title="Видалити">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    @if(auth()->user()->isAdmin())
                        <div class="flex justify-between">
                            <span class="text-gray-600">Майстер:</span>
                            <span>{{ $appointment->master->name }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Послуга:</span>
                        <span>{{ $appointment->service->name }} ({{ $appointment->duration }} хв)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Дата/Час:</span>
                        <span>
                            {{ $appointment->appointment_date->format('d.m.Y') }} 
                            {{ substr($appointment->appointment_time, 0, 5) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Статус:</span>
                        @switch($appointment->status)
                            @case('scheduled')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    Заплановано
                                </span>
                                @break
                            @case('completed')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    Завершено
                                </span>
                                @break
                            @default
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                    Скасовано
                                </span>
                        @endswitch
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-calendar-times text-4xl mb-3"></i>
                <p>Записів не знайдено</p>
            </div>
        @endforelse
    </div>

    @if($appointments->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $appointments->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

{{-- Модалка з деталями запису --}}
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-semibold">Деталі запису</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="appointmentContent" class="p-4"></div>
        <div class="p-4 border-t">
            <button onclick="closeModal()" class="w-full bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                Закрити
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAppointmentDetails(id) {
    const modal = document.getElementById('appointmentModal');
    const content = document.getElementById('appointmentContent');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    content.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
            <p class="text-gray-500 mt-2">Завантаження...</p>
        </div>
    `;
    
    fetch(`/admin/appointments/${id}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <div class="space-y-3">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Клієнт</div>
                        <div class="font-semibold">${data.client.name}</div>
                        <div class="text-sm text-gray-600">${data.client.phone}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Майстер</div>
                        <div class="font-medium">${data.master.name}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Послуга</div>
                        <div class="font-medium">${data.service.name}</div>
                        <div class="text-sm text-gray-600">${data.service.duration} хв</div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Дата</div>
                            <div class="font-medium">${data.appointment_date}</div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Час</div>
                            <div class="font-medium">${data.appointment_time}</div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Ціна</div>
                            <div class="text-lg font-bold text-green-600">${data.price} грн</div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 mb-1">Статус</div>
                            ${getStatusBadge(data.status)}
                        </div>
                    </div>
                    ${data.notes ? `
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Примітки</div>
                        <div class="bg-gray-50 p-3 rounded text-sm">${data.notes}</div>
                    </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                    <p>Помилка завантаження</p>
                </div>
            `;
        });
}

function getStatusBadge(status) {
    switch(status) {
        case 'scheduled':
            return `<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Заплановано</span>`;
        case 'completed':
            return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Завершено</span>`;
        default:
            return `<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Скасовано</span>`;
    }
}

function closeModal() {
    const modal = document.getElementById('appointmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('form[onsubmit]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const confirmed = confirm(this.getAttribute('onsubmit').replace('return ', ''));
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
@endsection