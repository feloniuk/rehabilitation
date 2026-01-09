@extends('super-admin.layouts.app')

@section('title', 'Редагувати ' . $tenant->name)
@section('page-title', 'Редагування організації')

@section('content')
<div class="mb-6">
    <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Повернутися до деталей
    </a>
</div>

<div class="bg-white rounded-lg shadow p-8 max-w-2xl">
    <form action="{{ route('super-admin.tenants.update', $tenant) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ім'я організації *</label>
            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
            <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
            @error('slug')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Trial закінчується</label>
            <input type="datetime-local" name="trial_ends_at" value="{{ old('trial_ends_at', $tenant->trial_ends_at?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('trial_ends_at') border-red-500 @enderror">
            @error('trial_ends_at')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center space-x-3">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }} class="rounded">
                <span class="ml-2 text-gray-700">Активна</span>
            </label>
        </div>

        <div class="flex items-center space-x-4 pt-6 border-t border-gray-200">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i>
                Зберегти
            </button>
            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-times mr-2"></i>
                Скасувати
            </a>
        </div>
    </form>
</div>
@endsection
