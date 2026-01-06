@extends('layouts.admin')

@section('title', 'Додати текстовий блок')
@section('page-title', 'Додати новий текстовий блок')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.text-blocks.store') }}">
            @csrf

            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Назва блоку *</label>
                <input type="text" id="title" name="title" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('title') }}" placeholder="Наприклад: Заголовок Hero секції">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Опис для адміністратора (не відображається на сайті)</p>
            </div>

            <div class="mb-6">
                <label for="key" class="block text-sm font-medium text-gray-700 mb-2">Ключ *</label>
                <input type="text" id="key" name="key" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('key') }}" placeholder="hero_title">
                @error('key')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Унікальний ідентифікатор (латиниця, цифри, підкреслення). Наприклад: hero_title, feature_1_text</p>
            </div>

            <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Тип поля *</label>
                <select id="type" name="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Текст (один рядок)</option>
                    <option value="textarea" {{ old('type') === 'textarea' ? 'selected' : '' }}>Багаторядковий текст</option>
                    <option value="html" {{ old('type') === 'html' ? 'selected' : '' }}>HTML</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Контент *</label>
                <textarea id="content" name="content" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Введіть текст...">{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Порядок сортування</label>
                <input type="number" id="order" name="order"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('order', 0) }}">
                @error('order')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Чим менше число, тим вище в списку</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.pages') }}"
                   class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </a>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Створити блок
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Динамічна зміна типу поля
document.getElementById('type').addEventListener('change', function() {
    const content = document.getElementById('content').value;
    const container = document.getElementById('content').parentElement;
    
    const label = container.querySelector('label');
    const error = container.querySelector('.text-red-500');
    
    if (this.value === 'text') {
        container.innerHTML = '';
        container.appendChild(label);
        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'content';
        input.name = 'content';
        input.required = true;
        input.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.value = content;
        input.placeholder = 'Введіть текст...';
        container.appendChild(input);
    } else {
        const rows = this.value === 'html' ? 15 : 5;
        container.innerHTML = '';
        container.appendChild(label);
        const textarea = document.createElement('textarea');
        textarea.id = 'content';
        textarea.name = 'content';
        textarea.rows = rows;
        textarea.required = true;
        textarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        textarea.value = content;
        textarea.placeholder = this.value === 'html' ? 'Введіть HTML код...' : 'Введіть текст...';
        container.appendChild(textarea);
        
        if (this.value === 'html') {
            const p = document.createElement('p');
            p.className = 'text-sm text-gray-500 mt-1';
            p.textContent = 'Можна використовувати HTML теги';
            container.appendChild(p);
        }
    }
    
    if (error) container.appendChild(error);
});

// Автоматична генерація ключа з назви
document.getElementById('title').addEventListener('input', function() {
    const keyInput = document.getElementById('key');
    if (!keyInput.value) {
        const key = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 50);
        keyInput.value = key;
    }
});
</script>
@endpush
@endsection
