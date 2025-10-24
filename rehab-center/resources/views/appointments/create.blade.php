@extends('layouts.app')

@section('title', 'Запис на прийом')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">Головна</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="{{ route('masters.show', $master->id) }}" class="hover:text-emerald-600 transition-colors">{{ $master->name }}</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-gray-700">Запис на прийом</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
            <i class="fas fa-calendar-plus text-2xl text-emerald-600"></i>
        </div>
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Запис на прийом</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Заповніть форму нижче, щоб записатися на консультацію до нашого спеціаліста
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
        <!-- Booking Details Sidebar -->
        <div class="lg:col-span-2">
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl p-6 sticky top-24">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-info-circle text-emerald-600 mr-2"></i>
                    Деталі запису
                </h3>

                <!-- Master Info -->
                <div class="mb-6 pb-6 border-b border-emerald-200">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-emerald-200">
                            @if($master->photo)
                                <img src="{{ asset('storage/' . $master->photo) }}"
                                     alt="{{ $master->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-emerald-400 flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">{{ $master->name }}</h4>
                            <p class="text-sm text-gray-600">Спеціаліст з реабілітації</p>
                            <!-- Rating -->
                            <div class="flex items-center mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-yellow-400 text-xs"></i>
                                @endfor
                                <span class="text-xs text-gray-500 ml-1">(4.9)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Info -->
                <div class="space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-600">Послуга</p>
                            <p class="font-semibold text-gray-800">{{ $service->name }}</p>
                        </div>
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-spa text-emerald-600"></i>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Тривалість</p>
                            <p class="font-semibold text-gray-800">{{ $masterService->getDuration() }} хвилин</p>
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $masterService->getDuration() }} хв
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Вартість</p>
                            <p class="font-bold text-2xl text-emerald-600">
                                {{ number_format($masterService->price, 0) }} 
                                <span class="text-lg text-gray-500">грн</span>
                            </p>
                        </div>
                        <div class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-medium">
                            Найкраща ціна
                        </div>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="mt-6 pt-6 border-t border-emerald-200">
                    <h4 class="font-semibold text-gray-800 mb-3">Що входить:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-500 mr-2"></i>
                            Первинна консультація
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-500 mr-2"></i>
                            Професійний сеанс
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-500 mr-2"></i>
                            Рекомендації спеціаліста
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-emerald-500 mr-2"></i>
                            Індивідуальний підхід
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <form method="POST" action="{{ route('appointment.store') }}" id="booking-form">
                    @csrf
                    <input type="hidden" name="master_id" value="{{ $master->id }}">
                    <input type="hidden" name="service_id" value="{{ $service->id }}">

                    <!-- Personal Information -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-user text-emerald-600 mr-2"></i>
                            Особисті дані
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Повне ім'я *
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Введіть ваше ім'я"
                                       value="{{ old('name') }}">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Телефон *
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                       placeholder="+380 XX XXX XX XX"
                                       value="{{ old('phone') }}">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email (опціонально)
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                   placeholder="your@email.com"
                                   value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Date & Time Selection -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-calendar-check text-emerald-600 mr-2"></i>
                            Дата та час
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Оберіть дату *
                                </label>
                                <input type="date" 
                                       id="appointment_date" 
                                       name="appointment_date" 
                                       required
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                       value="{{ old('appointment_date', request('date')) }}">
                                @error('appointment_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Оберіть час *
                                </label>
                                <select id="appointment_time" 
                                        name="appointment_time" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                    <option value="">Спочатку оберіть дату</option>
                                </select>
                                @error('appointment_time')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Available Slots Preview -->
                        <div id="slots-preview" class="mt-6 hidden">
                            <p class="text-sm font-medium text-gray-700 mb-3">Доступні часи:</p>
                            <div id="slots-grid" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3"></div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-clipboard text-emerald-600 mr-2"></i>
                            Додаткова інформація
                        </h3>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Примітки (опціонально)
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 resize-none"
                                      placeholder="Опишіть ваші побажання або особливості, які важливо знати спеціалісту...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('masters.show', $master->id) }}" 
                           class="text-gray-600 hover:text-gray-800 font-medium flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Назад
                        </a>
                        
                        <button type="submit" 
                                id="submit-btn"
                                class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-8 py-4 rounded-xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-2">
                            <span id="btn-text">
                                <i class="fas fa-check-circle mr-2"></i>
                                Підтвердити запис
                            </span>
                            <span id="btn-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Обробка...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Contact Section -->
    <div class="mt-16 bg-gray-50 rounded-2xl p-8">
        <h3 class="text-2xl font-bold text-gray-800 text-center mb-8">
            Маєте питання? Зв'яжіться з нами
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center group">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-200 transition-colors">
                    <i class="fas fa-phone text-emerald-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Телефонуйте</h4>
                <a href="tel:{{ \App\Models\Setting::get('center_phone') }}" 
                   class="text-emerald-600 hover:text-emerald-700 font-medium">
                    {{ \App\Models\Setting::get('center_phone') }}
                </a>
            </div>
            
            <div class="text-center group">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
                    <i class="fas fa-envelope text-blue-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Пишіть</h4>
                <a href="mailto:{{ \App\Models\Setting::get('center_email') }}" 
                   class="text-blue-600 hover:text-blue-700 font-medium">
                    {{ \App\Models\Setting::get('center_email') }}
                </a>
            </div>
            
            <div class="text-center group">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-200 transition-colors">
                    <i class="fas fa-comments text-purple-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Чат</h4>
                <button class="text-purple-600 hover:text-purple-700 font-medium">
                    Онлайн консультант
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const slotsPreview = document.getElementById('slots-preview');
    const slotsGrid = document.getElementById('slots-grid');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('booking-form');
    
    const masterId = {{ $master->id }};
    const serviceId = {{ $service->id }};

    // Функция загрузки доступных слотов
    function loadAvailableSlots(selectedDate) {
        if (!selectedDate) return;

        // Show loading state
        timeSelect.innerHTML = '<option value="">Завантаження...</option>';
        timeSelect.disabled = true;
        slotsPreview.classList.add('hidden');

        // Fetch available slots
        fetch(`/masters/${masterId}/available-slots/${selectedDate}/${serviceId}`)
            .then(response => response.json())
            .then(slots => {
                timeSelect.innerHTML = '<option value="">Оберіть час</option>';
                timeSelect.disabled = false;

                if (slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">Немає доступних слотів</option>';
                    slotsGrid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-4">На цю дату всі часи зайняті</p>';
                } else {
                    // Populate time select
                    slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = slot;
                        timeSelect.appendChild(option);
                    });

                    // Show slots grid
                    slotsGrid.innerHTML = '';
                    slots.forEach(slot => {
                        const slotBtn = document.createElement('button');
                        slotBtn.type = 'button';
                        slotBtn.className = 'px-3 py-2 text-sm border border-gray-300 rounded-lg hover:border-emerald-500 hover:bg-emerald-50 transition-colors';
                        slotBtn.textContent = slot;
                        slotBtn.onclick = () => selectTimeSlot(slot);
                        slotsGrid.appendChild(slotBtn);
                    });
                }
                
                slotsPreview.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                timeSelect.innerHTML = '<option value="">Помилка завантаження</option>';
                timeSelect.disabled = false;
            });
    }

    // Date selection handler
    dateInput.addEventListener('change', function() {
        loadAvailableSlots(this.value);
    });

    // ИСПРАВЛЕНИЕ: Проверка предустановленной даты при загрузке страницы
    if (dateInput.value) {
        loadAvailableSlots(dateInput.value);
    }

    // Time slot selection
    function selectTimeSlot(time) {
        timeSelect.value = time;
        // Update visual selection
        slotsGrid.querySelectorAll('button').forEach(btn => {
            btn.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-700');
            btn.classList.add('border-gray-300');
            if (btn.textContent === time) {
                btn.classList.add('border-emerald-500', 'bg-emerald-100', 'text-emerald-700');
                btn.classList.remove('border-gray-300');
            }
        });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        document.getElementById('btn-text').classList.add('hidden');
        document.getElementById('btn-loading').classList.remove('hidden');
        
        // Submit after delay (for better UX)
        setTimeout(() => {
            this.submit();
        }, 1000);
    });

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('380')) {
            value = value.substring(3);
        }
        if (value.length > 0) {
            value = '+380 ' + value.replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
        }
        e.target.value = value;
    });

    // Auto-resize textarea
    const notesTextarea = document.getElementById('notes');
    notesTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});
</script>
@endpush

@push('styles')
<style>
    .sticky {
        position: sticky;
    }
    
    @media (max-width: 1024px) {
        .sticky {
            position: relative;
        }
    }
    
    /* Custom scrollbar for textarea */
    textarea::-webkit-scrollbar {
        width: 8px;
    }
    
    textarea::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    textarea::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    textarea::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush
@endsection