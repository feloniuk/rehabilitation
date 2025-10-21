@extends('layouts.admin')

@section('title', 'Шаблони розсилок')
@section('page-title', 'Управління шаблонами повідомлень')

@section('content')

<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Доступні плейсхолдери</h3>
            <p class="text-sm text-gray-600">Використовуйте ці змінні в тексті шаблону</p>
        </div>
        <button onclick="togglePlaceholders()" 
                class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-info-circle mr-1"></i>
            Показати/Сховати
        </button>
    </div>
    
    <div id="placeholders-section" class="p-6 hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($placeholders as $placeholder => $description)
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <code class="text-sm text-blue-600 font-mono">{{ $placeholder }}</code>
                    <p class="text-xs text-gray-600 mt-1">{{ $description }}</p>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
                <i class="fas fa-lightbulb mr-1"></i>
                <strong>Приклад:</strong> "Доброго дня, {client_name}! Нагадуємо про ваш запис {date} о {time} до майстра {master_name}."
            </p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h3 class="text-lg font-semibold">Список шаблонів</h3>
        <button onclick="showCreateModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Створити шаблон
        </button>
    </div>

    <div class="overflow-x-auto">
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
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $template->name }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $template->id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-md truncate">
                                {{ Str::limit($template->message, 100) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $template->logs()->count() }} разів
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <button onclick="editTemplate({{ $template->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                        title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.notifications.templates.delete', $template->id) }}" 
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
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Шаблонів ще немає</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $templates->links() }}
    </div>
</div>

{{-- Модальне вікно створення/редагування --}}
<div id="template-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 id="modal-title" class="text-lg font-semibold">Створити шаблон</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="template-form" method="POST" action="">
            @csrf
            <input type="hidden" name="_method" value="POST" id="form-method">
            
            <div class="p-6">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Назва шаблону *
                    </label>
                    <input type="text" id="name" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Наприклад: Нагадування за день">
                </div>

                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        Текст повідомлення *
                    </label>
                    <textarea id="message" name="message" rows="8" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                              placeholder="Доброго дня, {client_name}! Нагадуємо про ваш запис..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        Використовуйте плейсхолдери для підстановки даних (див. вище)
                    </p>
                </div>

                {{-- Швидкі кнопки вставки плейсхолдерів --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Швидка вставка:
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($placeholders as $placeholder => $description)
                            <button type="button" 
                                    onclick="insertPlaceholder('{{ $placeholder }}')"
                                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded border border-gray-300 transition-colors">
                                {{ $placeholder }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
                <button type="button" onclick="closeModal()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Скасувати
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Показати/сховати плейсхолдери
function togglePlaceholders() {
    const section = document.getElementById('placeholders-section');
    section.classList.toggle('hidden');
}

// Показати модальне вікно створення
function showCreateModal() {
    document.getElementById('modal-title').textContent = 'Створити шаблон';
    document.getElementById('template-form').action = '{{ route("admin.notifications.templates.store") }}';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('name').value = '';
    document.getElementById('message').value = '';
    document.getElementById('template-modal').classList.remove('hidden');
}

// Показати модальне вікно редагування
function editTemplate(id) {
    // Отримати дані шаблону через AJAX
    fetch(`/admin/notifications/templates/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modal-title').textContent = 'Редагувати шаблон';
            document.getElementById('template-form').action = `/admin/notifications/templates/${id}`;
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('name').value = data.name;
            document.getElementById('message').value = data.message;
            document.getElementById('template-modal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка завантаження даних шаблону');
        });
}

// Закрити модальне вікно
function closeModal() {
    document.getElementById('template-modal').classList.add('hidden');
}

// Вставити плейсхолдер у текст
function insertPlaceholder(placeholder) {
    const textarea = document.getElementById('message');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
}

// Закриття по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endpush
@endsection