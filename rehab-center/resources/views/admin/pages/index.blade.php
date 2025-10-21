@extends('layouts.admin')

@section('title', 'Сторінки')
@section('page-title', 'Управління контентом сайту')

@section('content')
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b flex justify-between items-center bg-gradient-to-r from-emerald-50 to-teal-50">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-home text-emerald-600 mr-2"></i>
                Головна сторінка
            </h3>
            <p class="text-sm text-gray-600">Текстові блоки та контент головної сторінки</p>
        </div>
        <a href="{{ route('admin.pages.edit-home') }}" 
           class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Редагувати головну
        </a>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border-2 border-emerald-200 rounded-lg p-4">
                <div class="text-emerald-600 text-3xl mb-2">
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
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                Додаткові сторінки
            </h3>
            <p class="text-sm text-gray-600">Статичні сторінки сайту (Про нас, Контакти тощо)</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Додати сторінку
        </a>
    </div>

    <div class="overflow-x-auto">
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
                                    <i class="fas fa-check-circle mr-1" style="
                                    display: flex;
                                    align-items: center;
                                "></i>
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
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pages->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $pages->links() }}
        </div>
    @endif
</div>
@endsection