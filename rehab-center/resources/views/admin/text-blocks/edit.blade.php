@extends('layouts.admin')

@section('title', 'Редагувати текстовий блок')
@section('page-title', 'Редагувати: ' . $block->title)

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.text-blocks.update', $block->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Назва блоку *</label>
                <input type="text" id="title" name="title" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('title', $block->title) }}">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Опис для адміністратора</p>
            </div>

            <div class="mb-6">
                <label for="key" class="block text-sm font-medium text-gray-700 mb-2">Ключ *</label>
                <input type="text" id="key" name="key" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                       value="{{ old('key', $block->key) }}" readonly>
                @error('key')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Унікальний ідентифікатор (не можна змінювати)</p>
            </div>

            <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Тип поля *</label>
                <select id="type" name="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="text" {{ old('type', $block->type) === 'text' ? 'selected' : '' }}>Текст (один рядок)</option>
                    <option value="textarea" {{ old('type', $block->type) === 'textarea' ? 'selected' : '' }}>Багаторядковий текст</option>
                    <option value="html" {{ old('type', $block->type) === 'html' ? 'selected' : '' }}>HTML</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror>
            </div>

            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Контент *</label>
                @if($block->type === 'text')
                    <input type="text" id="content" name="content" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('content', $block->content) }}">
                @else
                    <textarea id="content" name="content" rows="{{ $block->type === 'html' ? 15 : 5 }}" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('content', $block->content) }}</textarea>
                @endif
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($block->type === 'html')
                    <p class="text-sm text-gray-500 mt-1">Можна використовувати HTML теги</p>
                @endif
            </div>

            <div class="mb-6">
                <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Порядок сортування</label>
                <input type="number" id="order" name="order"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('order', $block->order) }}">
                @error('order')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Чим менше число, тим вище в списку</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.text-blocks.index') }}"
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
// Динамічна зміна типу поля
document.getElementById('type').addEventListener('change', function() {
    const content = document.getElementById('content').value;
    const container = document.getElementById('content').parentElement;
    
    const label = container.querySelector('label');
    const error = container.querySelector('.text-red-500');
    const hint = container.querySelector('.text-gray-500');
    
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
</script>
@endpush
@endsection