@extends('layouts.admin')

@section('title', 'Текстові блоки')
@section('page-title', 'Управління текстовими блоками')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">Текстові блоки головної сторінки</h3>
            <p class="text-sm text-gray-600">Редагуйте контент що відображається на сайті</p>
        </div>
        <a href="{{ route('admin.text-blocks.create') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
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
                            {{ $block->order }}
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
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.text-blocks.edit', $block->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i> Редагувати
                                </a>
                                <form method="POST" action="{{ route('admin.text-blocks.destroy', $block->id) }}" 
                                      class="inline" onsubmit="return confirm('Ви впевнені?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Видалити
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Текстових блоків не знайдено
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $blocks->links() }}
    </div>
</div>

<div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
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
@endsection