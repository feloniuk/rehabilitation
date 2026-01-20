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
                           min="{{ date('Y-m-d') }}"
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
                                   value="{{ old('appointment_hour', substr(old('appointment_time', '09:00'), 0, 2)) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                   placeholder="00">
                        </div>
                        <div class="text-lg font-semibold text-gray-500 pb-2">:</div>
                        <div class="flex-1">
                            <!-- <label for="appointment_minute" class="block text-xs text-gray-600 mb-1">Хвилини</label> -->
                            <input type="number" id="appointment_minute" name="appointment_minute" required
                                   min="0" max="59" step="1"
                                   value="{{ old('appointment_minute', substr(old('appointment_time', '09:00'), 3, 2)) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                   placeholder="00">
                        </div>
                    </div>
                    <input type="hidden" id="appointment_time" name="appointment_time" required
                           value="{{ old('appointment_time', '09:00') }}">
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-hourglass-half text-rose-500 mr-1"></i>
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
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('new_client_phone') border-red-500 @enderror"
                                   placeholder="+380 XX XXX XX XX">
                            @error('new_client_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
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
            
            @if(auth()->user()->isAdmin())
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
            @endif

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

    // Зберігаємо робочий час мастра для валідації
    let currentMasterWorkingHours = null;
    let outsideWorkingHoursConfirmed = false;

    function parseDescriptionWithViberLink(text) {
        if (!text) return '';

        // HTML encode text
        var div = document.createElement('div');
        div.textContent = text;
        var encoded = div.innerHTML;

        // Replace line breaks
        encoded = encoded.replace(/\n/g, '<br>').replace(/\r/g, '');

        // Replace Viber links
        encoded = encoded.replace(/viber:\/\/chat\?number=([^&\s<>"']+)/g,
            '<a href="viber://chat?number=$1" class="text-blue-600 hover:text-blue-800 hover:underline"><i class="fab fa-viber mr-1"></i>Viber</a>');

        return encoded;
    }

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

                    // Рендеринг результатів
                    resultsContainer.innerHTML = data.results.map(client => `
                        <label class="flex items-center p-4 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors">
                            <input type="radio"
                                name="client_radio"
                                value="${client.id}"
                                data-name="${client.name}"
                                data-phone="${client.phone}"
                                data-telegram="${client.telegram_username || ''}"
                                class="mr-4 w-5 h-5 text-blue-600 focus:ring-2 focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">${client.name}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-phone text-gray-400 mr-1"></i>
                                    ${client.phone}
                                    ${client.email ? `<span class="ml-3"><i class="fas fa-envelope text-gray-400 mr-1"></i>${client.email}</span>` : ''}
                                    ${client.telegram_username ? `<a href="https://t.me/${client.telegram_username}" target="_blank" class="ml-3 text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()"><i class="fab fa-telegram mr-1"></i>@${client.telegram_username}</a>` : ''}
                                </div>
                                ${client.description ? `
                                    <div class="text-xs text-gray-500 mt-2 bg-gray-100 p-2 rounded">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        ${client.description}
                                    </div>
                                ` : ''}
                            </div>
                            <i class="fas fa-check text-blue-600 opacity-0 transition-opacity"></i>
                        </label>
                    `).join('');

                    // Вставляємо HTML контент для описань
                    document.querySelectorAll('.client-description-html').forEach(el => {
                        const encoded = el.getAttribute('data-description-html');
                        const text = decodeURIComponent(escape(atob(encoded)));
                        const html = parseDescriptionWithViberLink(text);
                        el.querySelector('.description-content').innerHTML = html;
                        el.removeAttribute('data-description-html');
                    });

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

    // Функція для завантаження першого вільного слота
    function loadFirstAvailableSlot() {
        const masterId = document.getElementById('master_id').value;
        const serviceId = document.getElementById('service_id').value;
        const date = document.getElementById('appointment_date').value;

        if (!masterId || !serviceId || !date) {
            return;
        }

        fetch(`/masters/${masterId}/first-slot/${date}/${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Зберігаємо робочий час для валідації
                    currentMasterWorkingHours = data.working_hours;
                    outsideWorkingHoursConfirmed = false;

                    if (data.is_working_day && data.first_available_slot) {
                        // Встановлюємо перший вільний слот
                        const [hour, minute] = data.first_available_slot.split(':');
                        document.getElementById('appointment_hour').value = hour;
                        document.getElementById('appointment_minute').value = minute;
                        formatTimeInputs();
                    } else if (!data.is_working_day) {
                        // Майстер не працює - показуємо попередження
                        showWorkingHoursWarning('Майстер не працює в цей день. Час буде встановлено на 09:00.');
                        document.getElementById('appointment_hour').value = '09';
                        document.getElementById('appointment_minute').value = '00';
                        formatTimeInputs();
                    } else {
                        // Немає вільних слотів
                        showWorkingHoursWarning('Немає вільних слотів на цей день у робочий час майстра.');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading first slot:', error);
            });
    }

    // Показати попередження
    function showWorkingHoursWarning(message) {
        // Видаляємо попередні попередження
        const oldWarning = document.getElementById('working-hours-warning');
        if (oldWarning) oldWarning.remove();

        const warningDiv = document.createElement('div');
        warningDiv.id = 'working-hours-warning';
        warningDiv.className = 'bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4';
        warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;

        const form = document.getElementById('appointment-form');
        form.insertBefore(warningDiv, form.firstChild.nextSibling);

        setTimeout(() => {
            if (warningDiv.parentNode) {
                warningDiv.remove();
            }
        }, 5000);
    }

    // Загрузка услуг мастера
    document.getElementById('master_id').addEventListener('change', function() {
        const masterId = this.value;
        const serviceSelect = document.getElementById('service_id');
        const priceInput = document.getElementById('price');

        // Скидаємо робочий час
        currentMasterWorkingHours = null;
        outsideWorkingHoursConfirmed = false;

        if (!masterId) {
            serviceSelect.innerHTML = '<option value="">Оберіть послугу</option>';
            priceInput.value = '';
            priceInput.placeholder = 'Спочатку виберіть майстра';
            return;
        }

        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">Завантаження послуг...</option>';

        fetch(`{{ route('admin.appointments.get-master-services') }}?master_id=${masterId}`)
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
            })
            .catch(error => {
                serviceSelect.disabled = false;
                serviceSelect.innerHTML = '<option value="">Помилка завантаження</option>';
                console.error('Error:', error);
            });

        priceInput.value = '';
    });

    // Автозаполнення ціни та тривалості при виборі послуги
    document.getElementById('service_id').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const priceInput = document.getElementById('price');
        const durationInput = document.getElementById('duration');

        if (option.value) {
            priceInput.value = option.dataset.price;
            durationInput.value = option.dataset.duration;
            // Завантажуємо перший вільний слот після вибору послуги
            loadFirstAvailableSlot();
        } else {
            priceInput.value = '';
            durationInput.value = '';
        }
    });

    // Завантаження слоту при зміні дати
    document.getElementById('appointment_date').addEventListener('change', function() {
        outsideWorkingHoursConfirmed = false;
        loadFirstAvailableSlot();
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

    // Обмеження часу для сьогоднішньої дати
    function updateTimeRestriction() {
        const dateInput = document.getElementById('appointment_date');
        const timeInput = document.getElementById('appointment_time');
        const selectedDate = dateInput.value;
        const today = new Date().toISOString().split('T')[0];

        if (selectedDate === today) {
            // Якщо вибрана сьогодняшня дата, встановлюємо мінімальний час
            const now = new Date();
            const currentHour = String(now.getHours()).padStart(2, '0');
            const currentMinute = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${currentHour}:${currentMinute}`;

            timeInput.min = currentTime;

            // Якщо вибраний час менше за поточний, очищаємо його
            if (timeInput.value < currentTime) {
                timeInput.value = currentTime;
            }
        } else {
            // Для майбутніх дат можна вибирати будь-який час
            timeInput.min = '00:00';
        }
    }

    // Обмеження дати та часу
    document.getElementById('appointment_date').addEventListener('change', updateTimeRestriction);
    document.getElementById('appointment_time').addEventListener('change', updateTimeRestriction);

    // Ініціалізація при завантаженні сторінки
    updateTimeRestriction();

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

    // Перевірка чи час у межах робочого часу
    function isTimeInWorkingHours(hour, minute) {
        if (!currentMasterWorkingHours) {
            return true; // Якщо немає даних - не блокуємо
        }

        const timeStr = String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
        const start = currentMasterWorkingHours.start;
        const end = currentMasterWorkingHours.end;

        return timeStr >= start && timeStr < end;
    }

    // Кастомний confirm-діалог
    function showConfirmDialog(options) {
        const title = options.title || 'Підтвердження';
        const message = options.message || '';
        const confirmText = options.confirmText || 'Так';
        const cancelText = options.cancelText || 'Скасувати';
        const type = options.type || 'warning';
        const onConfirm = options.onConfirm || function() {};
        const onCancel = options.onCancel || function() {};

        let iconColor, iconBg, buttonColor;
        if (type === 'danger') {
            iconColor = 'text-red-600';
            iconBg = 'bg-red-100';
            buttonColor = 'bg-red-600 hover:bg-red-700';
        } else if (type === 'info') {
            iconColor = 'text-blue-600';
            iconBg = 'bg-blue-100';
            buttonColor = 'bg-blue-600 hover:bg-blue-700';
        } else {
            iconColor = 'text-yellow-600';
            iconBg = 'bg-yellow-100';
            buttonColor = 'bg-yellow-600 hover:bg-yellow-700';
        }

        const modalHtml = `
            <div id="customConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] p-4">
                <div class="bg-white rounded-lg max-w-md w-full shadow-xl">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full ${iconBg} flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-xl ${iconColor}"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">${title}</h3>
                                <p class="text-gray-600 text-sm">${message}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
                        <button id="confirmDialogCancel" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            ${cancelText}
                        </button>
                        <button id="confirmDialogConfirm" class="px-4 py-2 text-white rounded-lg transition-colors ${buttonColor}">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Видаляємо попередній діалог якщо є
        const existingModal = document.getElementById('customConfirmModal');
        if (existingModal) existingModal.remove();

        const wrapper = document.createElement('div');
        wrapper.innerHTML = modalHtml.trim();
        const modal = wrapper.firstChild;
        document.body.appendChild(modal);

        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.transition = 'opacity 0.2s';
            modal.style.opacity = '1';
        }, 10);

        function closeModal() {
            modal.style.opacity = '0';
            setTimeout(() => {
                if (modal.parentNode) modal.parentNode.removeChild(modal);
            }, 200);
        }

        modal.querySelector('#confirmDialogCancel').onclick = () => { closeModal(); onCancel(); };
        modal.querySelector('#confirmDialogConfirm').onclick = () => { closeModal(); onConfirm(); };

        modal.onclick = (e) => { if (e.target === modal) { closeModal(); onCancel(); } };

        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closeModal();
                onCancel();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    // Валідація форми
    document.getElementById('appointment-form').addEventListener('submit', function(e) {
        const clientType = document.querySelector('input[name="client_type"]:checked').value;

        // Оновлюємо час перед відправкою
        formatTimeInputs();

        if (clientType === 'existing' && !hiddenInput.value) {
            e.preventDefault();
            alert('Оберіть клієнта зі списку');
            searchInput.focus();
            return false;
        }

        // Перевірка робочого часу (якщо ще не підтверджено)
        if (!outsideWorkingHoursConfirmed && currentMasterWorkingHours) {
            const hour = parseInt(document.getElementById('appointment_hour').value);
            const minute = parseInt(document.getElementById('appointment_minute').value);

            if (!isTimeInWorkingHours(hour, minute)) {
                e.preventDefault();

                const timeStr = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
                const workingHoursText = `${currentMasterWorkingHours.start} - ${currentMasterWorkingHours.end}`;
                const form = this;

                showConfirmDialog({
                    title: 'Час поза робочим графіком',
                    message: `Обраний час <strong>${timeStr}</strong> знаходиться поза робочим часом майстра <strong>(${workingHoursText})</strong>.<br><br>Ви впевнені, що хочете створити запис на цей час?`,
                    confirmText: 'Так, створити',
                    cancelText: 'Скасувати',
                    type: 'warning',
                    onConfirm: function() {
                        outsideWorkingHoursConfirmed = true;
                        form.submit();
                    }
                });
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection
