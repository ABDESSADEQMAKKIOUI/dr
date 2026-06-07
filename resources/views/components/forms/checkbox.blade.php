@props([
    'disabled' => false,
    'label' => '',
    'name' => '',
    'checked' => false,
    'value' => '1',
    'required' => false,
    'error' => null
])

<div class="{{ $attributes->get('class', 'mb-4') }}">
    <label class="flex items-center cursor-pointer">
        <input 
            type="checkbox" 
            name="{{ $name }}" 
            id="{{ $name }}"
            value="{{ $value }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ old($name, $checked) ? 'checked' : '' }}
            {{ $required ? 'required' : '' }}
            {!! $attributes->except(['class', 'disabled', 'label', 'name', 'checked', 'value', 'required', 'error']) !!}
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-200
            {{ $error || $errors->has($name) ? 'border-red-500' : 'border-gray-300' }}
            {{ $disabled ? 'cursor-not-allowed opacity-60' : '' }}"
        >
        @if($label)
            <span class="ml-2 text-sm text-gray-700 {{ $disabled ? 'text-gray-500' : '' }}">
                {{ $label }}
                @if($required) <span class="text-red-500">*</span> @endif
            </span>
        @endif
    </label>

    @if($error)
        <p class="text-red-500 text-sm mt-1 ml-6">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-sm mt-1 ml-6">{{ $errors->first($name) }}</p>
    @endif
</div>
