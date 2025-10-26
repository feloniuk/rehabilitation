@extends('layouts.admin')

@section('title', 'Редагувати послугу')
@section('page-title', 'Редагувати послугу')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('admin.services.update', $service->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Назва послуги *
            </label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   value="{{ old('name', $service->name) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Опис
            </label>
            <textarea name="description" 
                      id="description" 
                      rows="4"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $service->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                Тривалість (хвилин) *
            </label>
            <input type="number" 
                   name="duration" 
                   id="duration" 
                   value="{{ old('duration', $service->duration) }}"
                   min="15"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   required>
            @error('duration')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Фото -->
        <div class="mb-6">
            <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                Фото послуги
            </label>
            
            <div class="mt-2 flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <img id="photo-preview" 
                         src="{{ $service->photo ? asset('storage/' . $service->photo) : 'https://via.placeholder.com/200x200?text=Без+фото' }}" 
                         alt="Preview" 
                         class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                </div>
                
                <div class="flex-1">
                    @if($service->photo)
                        <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Фото завантажено
                                </span>
                                <button type="button" 
                                        onclick="if(confirm('Видалити фото?')) document.getElementById('delete-photo-form').submit();"
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>
                                    Видалити
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    <input type="file" 
                           name="photo" 
                           id="photo" 
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-sm text-gray-500 mt-2">
                        JPG, PNG, WEBP. Максимум 2MB. Залиште порожнім, щоб не змінювати фото.
                    </p>
                </div>
            </div>
            
            @error('photo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       {{ $service->is_active ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Активна послуга</span>
            </label>
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('admin.services.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Скасувати
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Зберегти зміни
            </button>
        </div>
    </form>
</div>

<!-- Форма для видалення фото (прихована) -->
@if($service->photo)
<form id="delete-photo-form" 
      method="POST" 
      action="{{ route('admin.services.update', $service->id) }}" 
      class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="name" value="{{ $service->name }}">
    <input type="hidden" name="description" value="{{ $service->description }}">
    <input type="hidden" name="duration" value="{{ $service->duration }}">
    <input type="hidden" name="is_active" value="{{ $service->is_active ? '1' : '0' }}">
    <input type="hidden" name="delete_photo" value="1">
</form>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endpush
@endsection