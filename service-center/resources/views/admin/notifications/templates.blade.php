@extends('layouts.admin')

@section('title', 'Шаблони розсилок')
@section('page-title', 'Управління шаблонами повідомлень')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <h3 class="text-lg font-semibold flex-grow">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                Шаблони розсилок
            </h3>
            <button onclick="showCreateModal()" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Новий шаблон
            </button>
        </div>
    </div>

    {{-- Desktop View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Повідомлення</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Використано</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($templates as $template)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $template->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-md truncate">
                                {{ Str::limit($template->message, 100) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                {{ $template->logs_count }} разів
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <button onclick="showTemplateDetails({{ $template->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors"
                                        title="Перегляд">
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                                <button onclick="editTemplate({{ $template->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                        title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.notifications.templates.delete', $template->id) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені, що хочете видалити цей шаблон?')">
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
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-file-alt text-4xl mb-3"></i>
                            <p>Шаблонів ще не створено</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($templates as $template)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $template->name }}</h3>
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                            {{ $template->logs_count }} разів
                        </span>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="showTemplateDetails({{ $template->id }})" 
                                class="text-blue-600"
                                title="Перегляд">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editTemplate({{ $template->id }})" 
                                class="text-indigo-600"
                                title="Редагувати">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.notifications.templates.delete', $template->id) }}" 
                              class="inline" onsubmit="return confirm('Ви впевнені, що хочете видалити цей шаблон?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600"
                                    title="Видалити">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-sm text-gray-600 truncate">
                    {{ $template->message }}
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-file-alt text-4xl mb-3"></i>
                <p>Шаблонів ще не створено</p>
            </div>
        @endforelse
    </div>

    @if($templates->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $templates->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

{{-- Модальне вікно перегляду --}}
<div id="template-details-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold" id="template-details-title"></h3>
            <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Назва шаблону</label>
                    <p id="template-details-name" class="text-gray-900"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Повний текст</label>
                    <pre id="template-details-message" class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap"></pre>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Використано</label>
                        <p id="template-details-logs" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Створено</label>
                        <p id="template-details-created" class="text-gray-900"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 border-t flex justify-end">
            <button onclick="closeDetailsModal()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Закрити
            </button>
        </div>
    </div>
</div>

{{-- Модальне вікно створення/редагування --}}
<div id="template-edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 id="template-edit-title" class="text-lg font-semibold">Створити шаблон</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="template-edit-form" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label for="template-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Назва шаблону *
                    </label>
                    <input type="text" id="template-name" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="template-message" class="block text-sm font-medium text-gray-700 mb-2">
                        Текст повідомлення *
                    </label>
                    <textarea id="template-message" name="message" rows="6" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                </div>
            </div>
            
            <div class="p-6 border-t flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const templates = @json($templates);

function showTemplateDetails(id) {
    const template = templates.find(t => t.id === id);
    
    document.getElementById('template-details-title').textContent = 'Деталі шаблону';
    document.getElementById('template-details-name').textContent = template.name;
    document.getElementById('template-details-message').textContent = template.message;
    document.getElementById('template-details-logs').textContent = template.logs_count + ' разів';
    document.getElementById('template-details-created').textContent = template.created_at;
    
    const modal = document.getElementById('template-details-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function editTemplate(id) {
    const template = templates.find(t => t.id === id);
    
    const form = document.getElementById('template-edit-form');
    const title = document.getElementById('template-edit-title');
    const nameInput = document.getElementById('template-name');
    const messageInput = document.getElementById('template-message');
    
    title.textContent = 'Редагувати шаблон';
    nameInput.value = template.name;
    messageInput.value = template.message;
    form.action = `/admin/notifications/templates/${id}`;
    
    const modal = document.getElementById('template-edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function showCreateModal() {
    const form = document.getElementById('template-edit-form');
    const title = document.getElementById('template-edit-title');
    const nameInput = document.getElementById('template-name');
    const messageInput = document.getElementById('template-message');
    
    title.textContent = 'Створити шаблон';
    nameInput.value = '';
    messageInput.value = '';
    form.action = '{{ route("admin.notifications.templates.store") }}';
    
    const modal = document.getElementById('template-edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDetailsModal() {
    const modal = document.getElementById('template-details-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function closeEditModal() {
    const modal = document.getElementById('template-edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailsModal();
        closeEditModal();
    }
});
</script>
@endpush
@endsection