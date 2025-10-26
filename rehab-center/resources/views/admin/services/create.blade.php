@extends('layouts.admin')

@section('title', 'Створити послугу')
@section('page-title', 'Створити послугу')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Назва послуги *
            </label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   value="{{ old('name') }}"
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
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
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
                   value="{{ old('duration', 60) }}"
                   min="1"
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
                         src="https://via.placeholder.com/200x200?text=Без+фото" 
                         alt="Preview" 
                         class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                </div>
                
                <div class="flex-1">
                    <input type="file" 
                           name="photo" 
                           id="photo" 
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-sm text-gray-500 mt-2">
                        JPG, PNG, WEBP. Максимум 2MB.
                    </p>
                </div>
            </div>
            
            @error('photo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('admin.services.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Скасувати
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Створити послугу
            </button>
        </div>
    </form>
</div>

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