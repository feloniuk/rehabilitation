@extends('layouts.admin')

@section('title', 'Редагувати клієнта')
@section('page-title', 'Редагування: ' . $client->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.clients.update', $client->id) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Повне ім'я *
                    </label>
                    <input type="text" id="name" name="name" required
                           value="{{ old('name', $client->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email *
                    </label>
                    <input type="email" id="email" name="email" required
                           value="{{ old('email', $client->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Телефон *
                    </label>
                    <input type="tel" id="phone" name="phone" required
                           value="{{ old('phone', $client->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="+380 XX XXX XX XX">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telegram_username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-telegram text-blue-500 mr-1"></i>
                        Telegram username
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">@</span>
                        <input type="text" id="telegram_username" name="telegram_username"
                               value="{{ old('telegram_username', $client->telegram_username) }}"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="username">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Необов'язково. Для швидкого зв'язку через Telegram.</p>
                    @error('telegram_username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Пароль не редагується, профіль клієнта поки не передбачений --}}

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ $client->is_active ? 'checked' : '' }}
                           class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Активний клієнт</span>
                </label>
            </div>

            <!-- После полей с контактами -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Опис клієнта
                </label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Додаткова інформація про клієнта...">{{ old('description', $client->description) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Необов'язково. До 500 символів.</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.clients.index') }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Зберегти зміни
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush
@endsection