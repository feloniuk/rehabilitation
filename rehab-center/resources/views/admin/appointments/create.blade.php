{{-- resources/views/admin/appointments/create.blade.php --}}

@extends('layouts.admin')

@section('title', 'Створити запис')
@section('page-title', 'Ручне створення запису')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.appointments.manual.store') }}" id="appointment-form">
            @csrf

            <input type="hidden" name="client_type" id="client_type_hidden" value="{{ old('client_type', 'existing') }}">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                            <option value="{{ $master->id }}" {{ old('master_id') == $master->id ? 'selected' : '' }}>
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
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
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
                           value="{{ old('appointment_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock text-orange-500 mr-1"></i>
                        Час *
                    </label>
                    <input type="time" id="appointment_time" name="appointment_time" required 
                           value="{{ old('appointment_time', '09:00') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-teal-500 mr-1"></i>
                        Тривалість (хв) *
                    </label>
                    <input type="number" id="duration" name="duration" required min="1"
                           value="{{ old('duration', 60) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                       placeholder="Оберіть майстра та послугу">
            </div>

            {{-- Вибір клієнта --}}
            <div class="mb-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    Клієнт
                </h3>

                <div class="mb-4">
                    <label class="inline-flex items-center mr-6">
                        <input type="radio" name="client_type" value="existing" 
                               {{ old('client_type', 'existing') == 'existing' ? 'checked' : '' }} 
                               class="mr-2">
                        <span class="text-sm font-medium">Існуючий клієнт</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="client_type" value="new" 
                               {{ old('client_type') == 'new' ? 'checked' : '' }} 
                               class="mr-2">
                        <span class="text-sm font-medium">Новий клієнт</span>
                    </label>
                </div>

                {{-- Існуючий клієнт - пошук з радіо-кнопками --}}
                <div id="existing-client-block" style="display: {{ old('client_type', 'existing') == 'existing' ? 'block' : 'none' }};">
                    <div class="mb-3">
                        <label for="client_search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-1"></i>
                            Пошук клієнта
                        </label>
                        <input type="text" 
                               id="client_search" 
                               placeholder="Введіть ім'я або телефон (мін. 2 символи)..." 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- Список знайдених клієнтів --}}
                    <div id="client_results" class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto bg-white">
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-search text-3xl mb-2 text-gray-400"></i>
                            <p>Почніть вводити для пошуку клієнтів</p>
                        </div>
                    </div>

                    {{-- Прихований input для відправки на сервер --}}
                    <input type="hidden" name="existing_client" id="existing_client" value="{{ old('existing_client') }}">
                </div>

                {{-- Новий клієнт --}}
                <div id="new-client-block" style="display: {{ old('client_type') == 'new' ? 'block' : 'none' }};">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_client_name" class="block text-sm font-medium text-gray-700 mb-2">Ім'я *</label>
                            <input type="text" id="new_client_name" name="new_client_name"
                                   value="{{ old('new_client_name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="new_client_phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
                            <input type="tel" id="new_client_phone" name="new_client_phone"
                                   value="{{ old('new_client_phone') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="+380 XX XXX XX XX">
                        </div>

                        <div class="md:col-span-2">
                            <label for="new_client_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="new_client_email" name="new_client_email"
                                   value="{{ old('new_client_email') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                          placeholder="Додаткова інформація...">{{ old('notes') }}</textarea>
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
    let searchTimeout;
    const searchInput = document.getElementById('client_search');
    const resultsContainer = document.getElementById('client_results');
    const hiddenInput = document.getElementById('existing_client');

    // Перемикання типу клієнта
    document.querySelectorAll('input[name="client_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                document.getElementById('existing-client-block').style.display = 'block';
                document.getElementById('new-client-block').style.display = 'none';
                hiddenInput.required = true;
                document.getElementById('new_client_name').required = false;
                document.getElementById('new_client_phone').required = false;
            } else {
                document.getElementById('existing-client-block').style.display = 'none';
                document.getElementById('new-client-block').style.display = 'block';
                hiddenInput.required = false;
                document.getElementById('new_client_name').required = true;
                document.getElementById('new_client_phone').required = true;
            }
        });
    });

    // Пошук клієнтів
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.innerHTML = `
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-search text-3xl mb-2 text-gray-400"></i>
                    <p>Введіть мінімум 2 символи</p>
                </div>
            `;
            return;
        }

        resultsContainer.innerHTML = `
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-blue-500"></i>
                <p>Завантаження...</p>
            </div>
        `;

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.appointments.search-clients') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.results.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-user-slash text-3xl mb-2 text-gray-400"></i>
                                <p>Нічого не знайдено</p>
                            </div>
                        `;
                        return;
                    }

                    resultsContainer.innerHTML = data.results.map(client => `
                        <label class="flex items-center p-4 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors">
                            <input type="radio" 
                                   name="client_radio" 
                                   value="${client.id}" 
                                   data-name="${client.name}"
                                   data-phone="${client.phone}"
                                   class="mr-4 w-5 h-5 text-blue-600 focus:ring-2 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">${client.name}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-phone text-gray-400 mr-1"></i>
                                    ${client.phone}
                                    ${client.email ? `<span class="ml-3"><i class="fas fa-envelope text-gray-400 mr-1"></i>${client.email}</span>` : ''}
                                </div>
                            </div>
                            <i class="fas fa-check text-blue-600 opacity-0 transition-opacity"></i>
                        </label>
                    `).join('');

                    // Обробка вибору клієнта
                    document.querySelectorAll('input[name="client_radio"]').forEach(radio => {
                        radio.addEventListener('change', function() {
                            hiddenInput.value = this.value;
                            document.getElementById('client_type_hidden').value = this.value;
                            
                            // Візуальна індикація вибору
                            document.querySelectorAll('input[name="client_radio"]').forEach(r => {
                                const label = r.closest('label');
                                const checkIcon = label.querySelector('.fa-check');
                                if (r.checked) {
                                    label.classList.add('bg-blue-50', 'border-blue-200');
                                    checkIcon.classList.remove('opacity-0');
                                    checkIcon.classList.add('opacity-100');
                                } else {
                                    label.classList.remove('bg-blue-50', 'border-blue-200');
                                    checkIcon.classList.add('opacity-0');
                                    checkIcon.classList.remove('opacity-100');
                                }
                            });
                        });
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsContainer.innerHTML = `
                        <div class="p-8 text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                            <p>Помилка завантаження</p>
                        </div>
                    `;
                });
        }, 500);
    });

    // Автозаповнення ціни
    let priceTimeout;
    ['master_id', 'service_id'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            clearTimeout(priceTimeout);
            
            const masterId = document.getElementById('master_id').value;
            const serviceId = document.getElementById('service_id').value;
            const priceInput = document.getElementById('price');

            if (masterId && serviceId) {
                priceInput.disabled = true;
                priceInput.placeholder = 'Завантаження...';
                
                priceTimeout = setTimeout(() => {
                    fetch(`{{ route('admin.appointments.get-service-price') }}?master_id=${masterId}&service_id=${serviceId}`)
                        .then(response => response.json())
                        .then(data => {
                            priceInput.disabled = false;
                            priceInput.value = data.price;
                            document.getElementById('duration').value = data.duration;
                        })
                        .catch(() => {
                            priceInput.disabled = false;
                            priceInput.placeholder = 'Помилка завантаження';
                        });
                }, 300);
            }
        });
    });

    // Форматування телефону
    document.getElementById('new_client_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('380')) value = value.substring(3);
        if (value.length > 0) {
            value = '+380 ' + value.replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
        }
        e.target.value = value.trim();
    });

    // Валідація форми
    document.getElementById('appointment-form').addEventListener('submit', function(e) {
        const clientType = document.querySelector('input[name="client_type"]:checked').value;
        
        if (clientType === 'existing' && !hiddenInput.value) {
            e.preventDefault();
            alert('Оберіть клієнта зі списку');
            searchInput.focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection