@extends('layouts.admin')

@section('title', 'Сторінки')
@section('page-title', 'Управління контентом сайту')

@section('content')
<div class="bg-white rounded-lg shadow mb-6">
    {{-- Головна сторінка секція --}}
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center bg-gradient-to-r from-pink-50 to-rose-50">
        <div class="flex-grow">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-home text-pink-600 mr-2"></i>
                Головна сторінка
            </h3>
            <p class="text-sm text-gray-600">Текстові блоки та контент головної сторінки</p>
        </div>
        <a href="{{ route('admin.pages.edit-home') }}" 
           class="mt-2 md:mt-0 bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-700 transition-colors w-full md:w-auto text-center">
            <i class="fas fa-edit mr-2"></i>Редагувати головну
        </a>
    </div>
    
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Статистика головної сторінки --}}
            <div class="bg-white border-2 border-pink-200 rounded-lg p-4">
                <div class="text-pink-600 text-3xl mb-2">
                    <i class="fas fa-align-left"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">{{ $homePageBlocks->count() }}</h4>
                <p class="text-sm text-gray-600">Текстових блоків</p>
            </div>
            
            <div class="bg-white border-2 border-blue-200 rounded-lg p-4">
                <div class="text-blue-600 text-3xl mb-2">
                    <i class="fas fa-language"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Українська</h4>
                <p class="text-sm text-gray-600">Мова контенту</p>
            </div>
            
            <div class="bg-white border-2 border-purple-200 rounded-lg p-4">
                <div class="text-purple-600 text-3xl mb-2">
                    <i class="fas fa-clock"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Онлайн</h4>
                <p class="text-sm text-gray-600">Статус публікації</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    {{-- Додаткові сторінки --}}
    <div class="px-4 py-4 border-b flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <h3 class="text-lg font-semibold flex-grow flex items-center">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                Додаткові сторінки
            </h3>
            <a href="{{ route('admin.pages.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors w-full md:w-auto text-center flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Додати сторінку
            </a>
        </div>
    </div>

    {{-- Desktop View --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Заголовок</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pages as $page)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $page->title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $page->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($page->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Активна
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Неактивна
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank"
                                   class="text-green-600 hover:text-green-900 transition-colors" 
                                   title="Переглянути на сайті">
                                    <i class="fas fa-external-link-alt text-lg"></i>
                                </a>
                                <a href="{{ route('admin.pages.edit', $page->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                   title="Редагувати">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.pages.destroy', $page->id) }}" 
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
                            <p>Додаткових сторінок ще немає</p>
                            <a href="{{ route('admin.pages.create') }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i>Додати сторінку
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile View --}}
    <div class="md:hidden p-4 space-y-4">
        @forelse($pages as $page)
            <div class="bg-white rounded-lg shadow-md border p-4">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $page->title }}</h3>
                        <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded">
                            {{ $page->slug }}
                        </code>
                    </div>
                    
                    <div class="flex space-x-2">
                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank"
                           class="text-green-600"
                           title="Переглянути на сайті">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="{{ route('admin.pages.edit', $page->id) }}" 
                           class="text-indigo-600"
                           title="Редагувати">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.pages.destroy', $page->id) }}" 
                              class="inline" onsubmit="return confirm('Ви впевнені?')">
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

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Статус:</span>
                        @if($page->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                Активна
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                Неактивна
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Додаткових сторінок ще немає</p>
                <a href="{{ route('admin.pages.create') }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                    <i class="fas fa-plus mr-1"></i>Додати сторінку
                </a>
            </div>
        @endforelse
    </div>

    @if($pages->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $pages->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('form[onsubmit]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const confirmed = confirm(this.getAttribute('onsubmit').replace('return ', ''));
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
@endsection