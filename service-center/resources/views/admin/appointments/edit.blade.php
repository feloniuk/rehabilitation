{{-- resources/views/admin/appointments/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Редагування запису')
@section('page-title', 'Редагування запису')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('tenant.admin.appointments.update', ['tenant' => app('currentTenant')->slug, 'appointment' => $appointment->id]) }}" id="appointment-form">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Інформація про клієнта (тільки для перегляду) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-blue-500 mr-1"></i>
                        Клієнт
                    </label>
                    <div class="bg-gray-50 rounded-md p-3">
                        <div class="font-semibold text-gray-900">{{ $appointment->client->name }}</div>
                        <div class="text-sm text-gray-600">{{ $appointment->client->phone }}</div>
                        @if($appointment->client->email)
                            <div class="text-sm text-gray-600">{{ $appointment->client->email }}</div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-purple-500 mr-1"></i>
                        Дата створення
                    </label>
                    <div class="bg-gray-50 rounded-md p-3">
                        <div class="font-semibold text-gray-900">{{ $appointment->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Майстер та послуга --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="master_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-md text-blue-500 mr-1"></i>
                        Майстер *
                    </label>
                    <select id="master_id" name="master_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Оберіть майстра</option>
                        @foreach($masters as $master)
                            <option value="{{ $master->id }}" {{ old('master_id', $appointment->master_id) == $master->id ? 'selected' : '' }}>
                                {{ $master->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-spa text-green-500 mr-1"></i>
                        Послуга *
                    </label>
                    <select id="service_id" name="service_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Оберіть послугу</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id', $appointment->service_id) == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} ({{ $service->duration }} хв)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Дата, час, тривалість --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar text-purple-500 mr-1"></i>
                        Дата *
                    </label>
                    <input type="date" id="appointment_date" name="appointment_date" required
                           value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock text-orange-500 mr-1"></i>
                        Час *
                    </label>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <!-- <label for="appointment_hour" class="block text-xs text-gray-600 mb-1">Години</label> -->
                            <input type="number" id="appointment_hour" name="appointment_hour" required
                                   min="0" max="23" step="1"
                                   value="{{ old('appointment_hour', substr($appointment->appointment_time, 0, 2)) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                   placeholder="00">
                        </div>
                        <div class="text-lg font-semibold text-gray-500 pb-2">:</div>
                        <div class="flex-1">
                            <!-- <label for="appointment_minute" class="block text-xs text-gray-600 mb-1">Хвилини</label> -->
                            <input type="number" id="appointment_minute" name="appointment_minute" required
                                   min="0" max="59" step="1"
                                   value="{{ old('appointment_minute', substr($appointment->appointment_time, 3, 2)) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                   placeholder="00">
                        </div>
                    </div>
                    <input type="hidden" id="appointment_time" name="appointment_time" required
                           value="{{ old('appointment_time', substr($appointment->appointment_time, 0, 5)) }}">
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-rose-500 mr-1"></i>
                        Тривалість (хв) *
                    </label>
                    <input type="number" id="duration" name="duration" required min="1"
                           value="{{ old('duration', $appointment->duration) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Ціна та статус --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                        Ціна (грн) *
                    </label>
                    <input type="number" id="price" name="price" required min="0" step="0.01"
                           value="{{ old('price', $appointment->price) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-check-circle text-blue-600 mr-1"></i>
                        Статус *
                    </label>
                    <select id="status" name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>
                            Заплановано
                        </option>
                        <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>
                            Завершено
                        </option>
                        <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>
                            Скасовано
                        </option>
                    </select>
                </div>
            </div>

            {{-- Примітки --}}
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-yellow-500 mr-1"></i>
                    Примітки
                </label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Додаткова інформація...">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            {{-- Дозвіл на нахлест --}}
            @if(auth()->user()->isAdmin())
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <label class="flex items-center">
                    <input type="checkbox" name="allow_overlap" value="1"
                           {{ old('allow_overlap') ? 'checked' : '' }}
                           class="mr-3 w-4 h-4">
                    <span class="text-sm">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                        <strong>Дозволити редагування навіть якщо час зайнятий</strong>
                    </span>
                </label>
            </div>
            @endif

            {{-- Кнопки --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('tenant.admin.appointments.index', ['tenant' => app('currentTenant')->slug]) }}"
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Скасувати
                </a>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Оновити запис
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Форматування та валідація часу
    const hourInput = document.getElementById('appointment_hour');
    const minuteInput = document.getElementById('appointment_minute');
    const timeHiddenInput = document.getElementById('appointment_time');

    function formatTimeInputs() {
        let hour = parseInt(hourInput.value) || 0;
        let minute = parseInt(minuteInput.value) || 0;

        // Валідація
        hour = Math.max(0, Math.min(23, hour));
        minute = Math.max(0, Math.min(59, minute));

        // Форматування
        hourInput.value = String(hour).padStart(2, '0');
        minuteInput.value = String(minute).padStart(2, '0');

        // Оновлення скритого поля
        timeHiddenInput.value = `${hourInput.value}:${minuteInput.value}`;
    }

    hourInput.addEventListener('blur', formatTimeInputs);
    minuteInput.addEventListener('blur', formatTimeInputs);
    hourInput.addEventListener('change', formatTimeInputs);
    minuteInput.addEventListener('change', formatTimeInputs);

    // Ініціалізація при завантаженні
    formatTimeInputs();

    // Автозаповнення ціни та тривалості при виборі послуги
    document.getElementById('service_id').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const priceInput = document.getElementById('price');
        const durationInput = document.getElementById('duration');

        if (option.value) {
            // Завантажуємо дані послуги майстра
            const masterId = document.getElementById('master_id').value;
            if (masterId) {
                fetch(`{{ route('tenant.admin.appointments.get-master-services', ['tenant' => app('currentTenant')->slug]) }}?master_id=${masterId}`)
                    .then(response => response.json())
                    .then(services => {
                        const service = services.find(s => s.id == option.value);
                        if (service) {
                            priceInput.value = service.price;
                            durationInput.value = service.duration;
                        }
                    });
            }
        }
    });

    // Оновлення послуг при зміні майстра
    document.getElementById('master_id').addEventListener('change', function() {
        const masterId = this.value;
        const serviceSelect = document.getElementById('service_id');

        if (!masterId) {
            serviceSelect.innerHTML = '<option value="">Оберіть послугу</option>';
            return;
        }

        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">Завантаження послуг...</option>';

        fetch(`{{ route('tenant.admin.appointments.get-master-services', ['tenant' => app('currentTenant')->slug]) }}?master_id=${masterId}`)
            .then(response => response.json())
            .then(services => {
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="">Оберіть послугу</option>';

                services.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} (${service.duration} хв)`;
                    option.dataset.price = service.price;
                    option.dataset.duration = service.duration;
                    serviceSelect.appendChild(option);
                });

                // Виберемо поточну послугу
                serviceSelect.value = '{{ $appointment->service_id }}';
            })
            .catch(error => {
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="">Помилка завантаження</option>';
                console.error('Error:', error);
            });
    });

    // Валідація форми
    document.getElementById('appointment-form').addEventListener('submit', function(e) {
        // Оновлюємо час перед відправкою
        formatTimeInputs();
    });
});
</script>
@endpush
@endsection
