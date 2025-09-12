@extends('layouts.admin')

@section('title', 'Редагувати сторінку')
@section('page-title', 'Редагувати сторінку: ' . $page->title)

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.pages.update', $page->id) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Заголовок *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('title', $page->title) }}">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">URL (slug) *</label>
                    <input type="text" id="slug" name="slug" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('slug', $page->slug) }}">
                    <p class="text-sm text-gray-500 mt-1">Використовуйте тільки літери, цифри та дефіси</p>
                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Зміст *</label>
                <textarea id="content" name="content" rows="15" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('content', $page->content) }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Можна використовувати HTML-теги</p>
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $page->is_active) ? 'checked' : '' }}
                           class="mr-2">
                    <span class="text-sm font-medium text-gray-700">Активна сторінка</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.pages.index') }}"
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </a>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Оновити сторінку
                </button>
            </div>
        </form>
    </div>
</div>
@endsection