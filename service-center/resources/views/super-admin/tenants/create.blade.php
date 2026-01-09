@extends('super-admin.layouts.app')

@section('title', 'Створити організацію')
@section('page-title', 'Створення нової організації')

@section('content')
<div class="mb-6">
    <a href="{{ route('super-admin.tenants.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Повернутися до списку
    </a>
</div>

<div class="bg-white rounded-lg shadow p-8 max-w-2xl">
    <form action="{{ route('super-admin.tenants.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ім'я організації *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
            <input type="text" name="slug" value="{{ old('slug') }}" required placeholder="organization-slug" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
            <p class="text-gray-600 text-sm mt-1">Унікальний ідентифікатор для URL</p>
            @error('slug')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Власник</label>
            <select name="owner_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('owner_id') border-red-500 @enderror">
                <option value="">-- Вибрати власника --</option>
                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                    <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('owner_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Пробний період (дні)</label>
            <input type="number" name="trial_days" value="{{ old('trial_days') }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('trial_days') border-red-500 @enderror">
            <p class="text-gray-600 text-sm mt-1">Залиште пусто для без триалу</p>
            @error('trial_days')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center space-x-3 pt-4">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded">
                <span class="ml-2 text-gray-700">Активна</span>
            </label>
        </div>

        <div class="flex items-center space-x-4 pt-6 border-t border-gray-200">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i>
                Створити
            </button>
            <a href="{{ route('super-admin.tenants.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-times mr-2"></i>
                Скасувати
            </a>
        </div>
    </form>
</div>
@endsection
