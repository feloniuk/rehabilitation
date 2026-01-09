@extends('layouts.admin')

@section('title', 'Редагування головної сторінки')
@section('page-title', 'Редагування контенту головної сторінки')

@section('content')

<div class="mb-6">
    <a href="{{ route('tenant.admin.pages.index', ['tenant' => app('currentTenant')->slug]) }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i>
        Повернутись до списку сторінок
    </a>
</div>

<div class="bg-gradient-to-r from-pink-500 to-rose-600 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-2">
                <i class="fas fa-home mr-2"></i>
                Головна сторінка
            </h2>
            <p class="text-pink-100">Редагуйте текстові блоки що відображаються на головній сторінці сайту</p>
        </div>
        <a href="{{ route('tenant.home', ['tenant' => app('currentTenant')->slug]) }}" target="_blank"
           class="bg-white text-pink-600 px-6 py-3 rounded-lg font-semibold hover:bg-pink-50 transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>
            Переглянути сайт
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Текстові блоки</h3>
            <p class="text-sm text-gray-600">Всього блоків: {{ $blocks->total() }}</p>
        </div>
        <a href="{{ route('tenant.admin.pages.blocks.create', ['tenant' => app('currentTenant')->slug]) }}" 
           class="bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Додати блок
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Порядок</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ключ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тип</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Контент</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($blocks as $block)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full font-semibold">
                                {{ $block->order }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $block->title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $block->key }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($block->type === 'text')
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Текст</span>
                            @elseif($block->type === 'textarea')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Багаторядковий</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">HTML</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-md truncate">
                                {{ Str::limit($block->content, 80) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <button onclick="openEditModal({{ $block->id }}, '{{ addslashes($block->title) }}', '{{ addslashes($block->key) }}', '{{ $block->type }}', {{ json_encode($block->content) }}, {{ $block->order }})" 
                                       class="text-indigo-600 hover:text-indigo-900 transition-colors" 
                                       title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <form method="POST" action="{{ route('tenant.admin.pages.blocks.destroy', ['tenant' => app('currentTenant')->slug, 'block' => $block->id]) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 transition-colors" 
                                            title="Видалити">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Текстових блоків не знайдено</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($blocks->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $blocks->links() }}
        </div>
    @endif
</div>

<!-- Модальне вікно редагування -->
<div id="edit-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold">Редагувати блок</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="edit-form" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <div class="mb-4">
                    <label for="edit-title" class="block text-sm font-medium text-gray-700 mb-2">
                        Назва блоку *
                    </label>
                    <input type="text" id="edit-title" name="title" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>

                <div class="mb-4">
                    <label for="edit-key" class="block text-sm font-medium text-gray-700 mb-2">
                        Ключ (не можна змінювати)
                    </label>
                    <input type="text" id="edit-key" name="key" required readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>

                <div class="mb-4">
                    <label for="edit-type" class="block text-sm font-medium text-gray-700 mb-2">
                        Тип поля *
                    </label>
                    <select id="edit-type" name="type" required onchange="updateEditContentField()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="text">Текст (один рядок)</option>
                        <option value="textarea">Багаторядковий текст</option>
                        <option value="html">HTML</option>
                    </select>
                </div>

                <div class="mb-4" id="edit-content-container">
                    <label for="edit-content" class="block text-sm font-medium text-gray-700 mb-2">
                        Контент *
                    </label>
                    <textarea id="edit-content" name="content" rows="5" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"></textarea>
                </div>

                <div class="mb-4">
                    <label for="edit-order" class="block text-sm font-medium text-gray-700 mb-2">
                        Порядок сортування
                    </label>
                    <input type="number" id="edit-order" name="order"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500">
                </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
                <button type="button" onclick="closeEditModal()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </button>
                <button type="submit" 
                        class="bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-700">
                    <i class="fas fa-save mr-2"></i>
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>

<div class="bg-blue-50 border-l-4 border-blue-400 p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                <strong>Підказка:</strong> Текстові блоки використовуються для управління контентом на головній сторінці сайту. 
                Ви можете змінювати тексти без редагування коду.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(id, title, key, type, content, order) {
    const tenantSlug = '{{ app('currentTenant')->slug }}';
    document.getElementById('edit-form').action = `/${tenantSlug}/admin/pages/home/blocks/${id}`;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-key').value = key;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-content').value = content;
    document.getElementById('edit-order').value = order;
    
    updateEditContentField();
    
    document.getElementById('edit-modal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}

function updateEditContentField() {
    const type = document.getElementById('edit-type').value;
    const content = document.getElementById('edit-content').value;
    const container = document.getElementById('edit-content-container');
    
    const label = container.querySelector('label');
    
    container.innerHTML = '';
    container.appendChild(label);
    
    if (type === 'text') {
        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'edit-content';
        input.name = 'content';
        input.required = true;
        input.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500';
        input.value = content;
        container.appendChild(input);
    } else {
        const rows = type === 'html' ? 15 : 5;
        const textarea = document.createElement('textarea');
        textarea.id = 'edit-content';
        textarea.name = 'content';
        textarea.rows = rows;
        textarea.required = true;
        textarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500';
        textarea.value = content;
        container.appendChild(textarea);
        
        if (type === 'html') {
            const hint = document.createElement('p');
            hint.className = 'text-xs text-gray-500 mt-1';
            hint.textContent = 'Можна використовувати HTML теги';
            container.appendChild(hint);
        }
    }
}

// Закриття по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});
</script>
@endpush
@endsection
