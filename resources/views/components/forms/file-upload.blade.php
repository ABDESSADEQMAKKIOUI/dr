@props([
    'disabled' => false,
    'label' => '',
    'name' => '',
    'accept' => '*/*',
    'multiple' => false,
    'required' => false,
    'error' => null,
    'preview' => false
])

<div class="{{ $attributes->get('class', 'mb-4') }}" x-data="{ fileName: '' }">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    
    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:bg-gray-50 transition duration-200 {{ $error || $errors->has($name) ? 'border-red-500' : '' }}">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600 justify-center">
                <label for="{{ $name }}" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                    <span>Upload a file</span>
                    <input 
                        id="{{ $name }}" 
                        name="{{ $name }}{{ $multiple ? '[]' : '' }}" 
                        type="file" 
                        class="sr-only"
                        accept="{{ $accept }}"
                        {{ $multiple ? 'multiple' : '' }}
                        {{ $disabled ? 'disabled' : '' }}
                        {{ $required ? 'required' : '' }}
                        @change="fileName = $event.target.files.length > 1 ? $event.target.files.length + ' files selected' : $event.target.files[0].name"
                    >
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500">
                <span x-text="fileName ? fileName : '{{ $accept == 'image/*' ? 'PNG, JPG, GIF up to 10MB' : 'Any file up to 10MB' }}'"></span>
            </p>
        </div>
    </div>

    @if($error)
        <p class="text-red-500 text-sm mt-1">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-sm mt-1">{{ $errors->first($name) }}</p>
    @endif
</div>
