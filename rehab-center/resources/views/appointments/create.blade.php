@extends('layouts.app')

@section('title', 'Запис на прийом')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Головна</a>
            <span class="mx-2">/</span>
            <a href="{{ route('masters.show', $master->id) }}" class="hover:text-blue-600">{{ $master->name }}</a>
            <span class="mx-2">/</span>
            <span>Запис на прийом</span>
        </nav>

        <h1 class="text-3xl font-bold">Запис на прийом</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Booking Details -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">Деталі запису</h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Спеціаліст</p>
                        <p class="font-semibold">{{ $master->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Послуга</p>
                        <p class="font-semibold">{{ $service->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Тривалість</p>
                        <p class="font-semibold">{{ $masterService->getDuration() }} хвилин</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Вартість</p>
                        <p class="font-semibold text-green-600">{{ number_format($masterService->price, 0) }} грн</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <form method="POST" action="{{ route('appointment.store') }}" id="booking-form">
                    @csrf
                    <input type="hidden" name="master_id" value="{{ $master->id }}">
                    <input type="hidden" name="service_id" value="{{ $service->id }}">

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
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="{{ old('email') }}">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">Дата *</label>
                            <input type="date" id="appointment_date" name="appointment_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   min="{{ date('Y-m-d') }}" value="{{ old('appointment_date') }}">
                            @error('appointment_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">Час *</label>
                            <select id="appointment_time" name="appointment_time" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Оберіть час</option>
                            </select>
                            @error('appointment_time')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Додаткові побажання</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Опишіть ваші побажання або особливості...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="text-right">
                        <button type="submit"
                                class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                            Підтвердити запис
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const masterId = {{ $master->id }};
    const serviceId = {{ $service->id }};

    dateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        if (!selectedDate) return;

        // Clear time options
        timeSelect.innerHTML = '<option value="">Завантаження...</option>';

        // Fetch available slots
        fetch(`/masters/${masterId}/available-slots/${selectedDate}/${serviceId}`)
            .then(response => response.json())
            .then(slots => {
                timeSelect.innerHTML = '<option value="">Оберіть час</option>';

                if (slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">Немає доступних слотів</option>';
                    return;
                }

                slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = slot;
                    timeSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                timeSelect.innerHTML = '<option value="">Помилка завантаження</option>';
            });
    });
});
</script>
@endpush
@endsection