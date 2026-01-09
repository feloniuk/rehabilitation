@extends('layouts.admin')

@section('title', 'Налаштування')
@section('page-title', 'Налаштування сайту')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('tenant.admin.settings.update', ['tenant' => app('currentTenant')->slug]) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="center_name" class="block text-sm font-medium text-gray-700 mb-2">Назва центру *</label>
                    <input type="text" id="center_name" name="center_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('center_name', $settings['center_name']) }}">
                </div>

                <div>
                    <label for="center_phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
                    <input type="tel" id="center_phone" name="center_phone" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('center_phone', $settings['center_phone']) }}">
                </div>
            </div>

            <div class="mb-6">
                <label for="center_email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" id="center_email" name="center_email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('center_email', $settings['center_email']) }}">
            </div>

            <div class="mb-6">
                <label for="center_address" class="block text-sm font-medium text-gray-700 mb-2">Адреса *</label>
                <textarea id="center_address" name="center_address" rows="2" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('center_address', $settings['center_address']) }}</textarea>
            </div>

            <div class="mb-6">
                <label for="center_coordinates" class="block text-sm font-medium text-gray-700 mb-2">Координати (широта,довгота) *</label>
                <input type="text" id="center_coordinates" name="center_coordinates" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="50.4501,30.5234"
                       value="{{ old('center_coordinates', $settings['center_coordinates']) }}">
                <p class="text-sm text-gray-500 mt-1">Знайдіть координати на Google Maps</p>
            </div>

            <div class="mb-6">
                <label for="working_hours" class="block text-sm font-medium text-gray-700 mb-2">Режим роботи *</label>
                <input type="text" id="working_hours" name="working_hours" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('working_hours', $settings['working_hours']) }}">
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Зберегти налаштування
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
