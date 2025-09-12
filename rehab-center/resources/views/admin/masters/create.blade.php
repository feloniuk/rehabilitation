@extends('layouts.admin')

@section('title', 'Додати майстра')
@section('page-title', 'Додати нового майстра')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.masters.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Показуємо помилки валідації --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ім'я *</label>
                    <input type="text" id="name" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('name') }}">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Пароль *</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                    <input type="tel" id="phone" name="phone" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('phone') }}">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Фото</label>
                <input type="file" id="photo" name="photo" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('photo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Опис</label>
                <textarea id="description" name="description" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Розкажіть про досвід та кваліфікацію майстра...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Work Schedule -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Графік роботи</h3>
                @php
                    $days = [
                        'monday' => 'Понеділок',
                        'tuesday' => 'Вівторок',
                        'wednesday' => 'Середа', 
                        'thursday' => 'Четвер',
                        'friday' => 'П\'ятниця',
                        'saturday' => 'Субота',
                        'sunday' => 'Неділя'
                    ];
                @endphp
                
                @foreach($days as $dayKey => $dayName)
                    <div class="flex items-center space-x-4 mb-3">
                        <div class="w-24">
                            <label class="flex items-center">
                                <input type="checkbox" name="work_schedule[{{ $dayKey }}][is_working]" value="1"
                                       {{ (old("work_schedule.$dayKey.is_working") || $defaultSchedule[$dayKey]['is_working']) ? 'checked' : '' }}
                                       class="mr-2">
                                <span class="text-sm font-medium">{{ $dayName }}</span>
                            </label>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="time" name="work_schedule[{{ $dayKey }}][start]" 
                                   value="{{ old("work_schedule.$dayKey.start", $defaultSchedule[$dayKey]['start']) }}"
                                   class="px-2 py-1 border border-gray-300 rounded text-sm">
                            <span class="text-gray-500">-</span>
                            <input type="time" name="work_schedule[{{ $dayKey }}][end]" 
                                   value="{{ old("work_schedule.$dayKey.end", $defaultSchedule[$dayKey]['end']) }}"
                                   class="px-2 py-1 border border-gray-300 rounded text-sm">
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Services and Prices -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Послуги та ціни *</h3>
                <p class="text-sm text-gray-600 mb-4">Оберіть принаймні одну послугу та вкажіть ціну</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($services as $service)
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" 
                                       id="service_{{ $service->id }}" 
                                       data-service-id="{{ $service->id }}"
                                       class="service-checkbox mr-2"
                                       {{ old("services.$service->id.price") ? 'checked' : '' }}>
                                <label for="service_{{ $service->id }}" class="font-medium">
                                    {{ $service->name }}
                                </label>
                            </div>
                            
                            <div id="service_fields_{{ $service->id }}" 
                                 class="service-fields space-y-2" 
                                 style="display: {{ old("services.$service->id.price") ? 'block' : 'none' }};">
                                <div>
                                    <label class="block text-sm text-gray-600">Ціна (грн) *</label>
                                    <input type="number" 
                                           name="services[{{ $service->id }}][price]" 
                                           step="0.01" 
                                           min="0"
                                           class="price-input w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                           value="{{ old("services.$service->id.price") }}">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600">Тривалість (хв)</label>
                                    <input type="number" 
                                           name="services[{{ $service->id }}][duration]" 
                                           min="15"
                                           class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                           placeholder="{{ $service->duration }}" 
                                           value="{{ old("services.$service->id.duration") }}">
                                    <p class="text-xs text-gray-500">Залишіть пустим для стандартної тривалості ({{ $service->duration }} хв)</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @error('services')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.masters.index') }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Створити майстра
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Отримуємо всі чекбокси послуг
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    
    serviceCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const serviceId = this.dataset.serviceId;
            const fieldsContainer = document.getElementById(`service_fields_${serviceId}`);
            const priceInput = fieldsContainer.querySelector('.price-input');
            
            if (this.checked) {
                fieldsContainer.style.display = 'block';
                priceInput.required = true;
            } else {
                fieldsContainer.style.display = 'none';
                priceInput.required = false;
                priceInput.value = ''; // Очищаємо значення
            }
        });
    });
    
    // Перевіряємо чи є вибрані послуги при відправці форми
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const checkedServices = document.querySelectorAll('.service-checkbox:checked');
        const hasValidServices = Array.from(checkedServices).some(function(checkbox) {
            const serviceId = checkbox.dataset.serviceId;
            const priceInput = document.querySelector(`input[name="services[${serviceId}][price]"]`);
            return priceInput && priceInput.value && parseFloat(priceInput.value) > 0;
        });
        
        if (!hasValidServices) {
            e.preventDefault();
            alert('Оберіть принаймні одну послугу та вкажіть ціну більше 0');
        }
    });
});
</script>
@endpush
@endsection