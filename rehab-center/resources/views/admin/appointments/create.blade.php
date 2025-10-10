@extends('layouts.admin')

@section('title', 'Створити запис')
@section('page-title', 'Ручне створення запису')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.appointments.manual.store') }}" id="appointment-form">
            @csrf

            {{-- Вибір майстра та послуги --}}
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
                            <option value="{{ $master->id }}" {{ old('master_id') == $master->id ? 'selected' : '' }}>
                                {{ $master->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('master_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
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
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} ({{ $service->duration }} хв)
                            </option>
                        @endforeach
                    </select>
                    @error('service_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
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
                           value="{{ old('appointment_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('appointment_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock text-orange-500 mr-1"></i>
                        Час * (вручну)
                    </label>
                    <input type="time" id="appointment_time" name="appointment_time" required 
                           value="{{ old('appointment_time', '09:00') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('appointment_time')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-teal-500 mr-1"></i>
                        Тривалість (хв) *
                    </label>
                    <input type="number" id="duration" name="duration" required min="15" step="15"
                           value="{{ old('duration', 60) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('duration')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Ціна --}}
            <div class="mb-6">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                    Ціна (грн) *
                </label>
                <input type="number" id="price" name="price" required min="0" step="0.01"
                       value="{{ old('price') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Оберіть спочатку майстра та послугу">
                @error('price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Вибір клієнта --}}
            <div class="mb-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    Клієнт
                </h3>

                <div class="mb-4">
                    <label class="inline-flex items-center mr-6">
                        <input type="radio" name="client_type" value="existing" 
                               {{ old('client_type', 'existing') == 'existing' ? 'checked' : '' }}
                               class="mr-2">
                        <span class="text-sm">Існуючий клієнт</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="client_type" value="new" 
                               {{ old('client_type') == 'new' ? 'checked' : '' }}
                               class="mr-2">
                        <span class="text-sm">Новий клієнт</span>
                    </label>
                </div>

                {{-- Існуючий клієнт --}}
                <div id="existing-client-block" class="{{ old('client_type', 'existing') == 'existing' ? '' : 'hidden' }}">
                    <label for="existing_client" class="block text-sm font-medium text-gray-700 mb-2">
                        Оберіть клієнта
                    </label>
                    <select id="existing_client" name="existing_client"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Оберіть клієнта</option>
                        @foreach($recentClients as $client)
                            <option value="{{ $client->id }}" {{ old('existing_client') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} - {{ $client->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('existing_client')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Новий клієнт --}}
                <div id="new-client-block" class="{{ old('client_type') == 'new' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_client_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Ім'я *
                            </label>
                            <input type="text" id="new_client_name" name="new_client_name"
                                   value="{{ old('new_client_name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('new_client_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_client_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Телефон *
                            </label>
                            <input type="tel" id="new_client_phone" name="new_client_phone"
                                   value="{{ old('new_client_phone') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="+380 XX XXX XX XX">
                            @error('new_client_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="new_client_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email (опціонально)
                            </label>
                            <input type="email" id="new_client_email" name="new_client_email"
                                   value="{{ old('new_client_email') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('new_client_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
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
                          placeholder="Додаткова інформація про запис...">{{ old('notes') }}</textarea>
            </div>

            {{-- Дозвіл на нахлест --}}
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <label class="flex items-center">
                    <input type="checkbox" name="allow_overlap" value="1" 
                           {{ old('allow_overlap') ? 'checked' : '' }}
                           class="mr-3 w-4 h-4">
                    <span class="text-sm">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                        <strong>Дозволити створення запису навіть якщо час зайнятий</strong>
                        <br>
                        <span class="text-gray-600 text-xs">
                            За замовчуванням система не дозволить створити запис, якщо на цей час вже є інший запис у майстра. 
                            Увімкніть цю опцію, якщо потрібно створити запис у будь-якому випадку (наприклад, для екстреного випадку).
                        </span>
                    </span>
                </label>
            </div>

            {{-- Кнопки --}}
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.appointments.index') }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Скасувати
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Створити запис
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const masterSelect = document.getElementById('master_id');
    const serviceSelect = document.getElementById('service_id');
    const priceInput = document.getElementById('price');
    const durationInput = document.getElementById('duration');

    // Перемикання типу клієнта
    const clientTypeRadios = document.querySelectorAll('input[name="client_type"]');
    const existingClientBlock = document.getElementById('existing-client-block');
    const newClientBlock = document.getElementById('new-client-block');

    clientTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                existingClientBlock.classList.remove('hidden');
                newClientBlock.classList.add('hidden');
                document.getElementById('existing_client').required = true;
                document.getElementById('new_client_name').required = false;
                document.getElementById('new_client_phone').required = false;
            } else {
                existingClientBlock.classList.add('hidden');
                newClientBlock.classList.remove('hidden');
                document.getElementById('existing_client').required = false;
                document.getElementById('new_client_name').required = true;
                document.getElementById('new_client_phone').required = true;
            }
        });
    });

    // Автозаповнення ціни та тривалості
    function updatePriceAndDuration() {
        const masterId = masterSelect.value;
        const serviceId = serviceSelect.value;

        if (!masterId || !serviceId) return;

        fetch(`/admin/appointments/get-service-price?master_id=${masterId}&service_id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.price) {
                    priceInput.value = data.price;
                }
                if (data.duration) {
                    durationInput.value = data.duration;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    masterSelect.addEventListener('change', updatePriceAndDuration);
    serviceSelect.addEventListener('change', updatePriceAndDuration);

    // Форматування телефону
    const phoneInput = document.getElementById('new_client_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('380')) {
                value = value.substring(3);
            }
            if (value.length > 0) {
                value = '+380 ' + value.replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
            }
            e.target.value = value.trim();
        });
    }
});
</script>
@endpush
@endsection