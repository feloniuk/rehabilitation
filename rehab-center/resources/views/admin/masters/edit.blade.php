@extends('layouts.admin')

@section('title', 'Редагувати майстра')
@section('page-title', 'Редагувати майстра: ' . $master->name)

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.masters.update', $master->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ім'я *</label>
                    <input type="text" id="name" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('name', $master->name) }}">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('email', $master->email) }}">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Новий пароль</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Залишіть пустим, щоб не змінювати пароль</p>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                    <input type="tel" id="phone" name="phone" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('phone', $master->phone) }}">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Фото</label>
                @if($master->photo)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $master->photo) }}" alt="{{ $master->name }}" class="w-24 h-24 object-cover rounded">
                    </div>
                @endif
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
                          placeholder="Розкажіть про досвід та кваліфікацію майстра...">{{ old('description', $master->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-2">Спеціалізація</label>
                <input type="text" id="specialty" name="specialty" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Наприклад, Спеціаліст з реабілітації"
                       value="{{ old('specialty', $master->specialty) }}">
                @error('specialty')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Короткий опис спеціалізації майстра</p>
            </div>

            <!-- Статистика майстра -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">
                    <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                    Статистика та досягнення
                </h3>
                <p class="text-sm text-gray-600 mb-4">Ця інформація відображається на сторінці майстра</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-graduate mr-1"></i>
                            Років досвіду
                        </label>
                        <input type="number" id="experience_years" name="experience_years" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="{{ old('experience_years', $master->experience_years ?? 5) }}"
                            placeholder="5">
                        @error('experience_years')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Скільки років працює майстром</p>
                    </div>
                    
                    <div>
                        <label for="clients_count" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-users mr-1"></i>
                            Кількість клієнтів
                        </label>
                        <input type="number" id="clients_count" name="clients_count" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="{{ old('clients_count', $master->clients_count ?? 200) }}"
                            placeholder="200">
                        @error('clients_count')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Загальна кількість клієнтів</p>
                    </div>
                    
                    <div>
                        <label for="certificates_count" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-certificate mr-1"></i>
                            Кількість сертифікатів
                        </label>
                        <input type="number" id="certificates_count" name="certificates_count" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="{{ old('certificates_count', $master->certificates_count ?? 3) }}"
                            placeholder="3">
                        @error('certificates_count')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Професійних сертифікатів</p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $master->is_active) ? 'checked' : '' }}
                           class="mr-2">
                    <span class="text-sm font-medium text-gray-700">Активний майстер</span>
                </label>
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
                                       {{ (old("work_schedule.$dayKey.is_working") ?? $master->work_schedule[$dayKey]['is_working'] ?? false) ? 'checked' : '' }}
                                       class="mr-2">
                                <span class="text-sm font-medium">{{ $dayName }}</span>
                            </label>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="time" name="work_schedule[{{ $dayKey }}][start]" 
                                   value="{{ old("work_schedule.$dayKey.start") ?? $master->work_schedule[$dayKey]['start'] ?? '09:00' }}"
                                   class="px-2 py-1 border border-gray-300 rounded text-sm">
                            <span class="text-gray-500">-</span>
                            <input type="time" name="work_schedule[{{ $dayKey }}][end]" 
                                   value="{{ old("work_schedule.$dayKey.end") ?? $master->work_schedule[$dayKey]['end'] ?? '17:00' }}"
                                   class="px-2 py-1 border border-gray-300 rounded text-sm">
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Services and Prices -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Послуги та ціни</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($services as $service)
                        @php
                            $masterService = $master->masterServices->where('service_id', $service->id)->first();
                        @endphp
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" id="service_{{ $service->id }}" 
                                       {{ $masterService ? 'checked' : '' }}
                                       onchange="toggleService({{ $service->id }})" class="mr-2">
                                <label for="service_{{ $service->id }}" class="font-medium">
                                    {{ $service->name }}
                                </label>
                            </div>
                            
                            <div id="service_fields_{{ $service->id }}" class="space-y-2" style="display: {{ $masterService ? 'block' : 'none' }};">
                                <div>
                                    <label class="block text-sm text-gray-600">Ціна (грн) *</label>
                                    <input type="number" name="services[{{ $service->id }}][price]" step="0.01" min="0"
                                           class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                           value="{{ old("services.$service->id.price", $masterService->price ?? '') }}">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600">Тривалість (хв)</label>
                                    <input type="number" name="services[{{ $service->id }}][duration]" min="15"
                                           class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                           placeholder="{{ $service->duration }}" 
                                           value="{{ old("services.$service->id.duration", $masterService->duration ?? '') }}">
                                    <p class="text-xs text-gray-500">Залишіть пустим для використання стандартної тривалості ({{ $service->duration }} хв)</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.masters.index') }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Оновити майстра
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleService(serviceId) {
    const checkbox = document.getElementById(`service_${serviceId}`);
    const fields = document.getElementById(`service_fields_${serviceId}`);
    
    if (checkbox.checked) {
        fields.style.display = 'block';
        fields.querySelector('input[name$="[price]"]').required = true;
    } else {
        fields.style.display = 'none';
        fields.querySelector('input[name$="[price]"]').required = false;
    }
}
</script>
@endpush
@endsection
