@props([
    'disabled' => false,
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'error' => null
])

<div class="{{ $attributes->get('class', 'mb-4') }}">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    
    <input 
        {{ $disabled ? 'disabled' : '' }}
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {!! $attributes->except(['class', 'disabled', 'label', 'name', 'type', 'value', 'placeholder', 'required', 'error']) !!}
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 
        {{ $error || $errors->has($name) ? 'border-red-500 focus:ring-red-500' : 'border-gray-300' }}
        {{ $disabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white' }}"
    >

    @if($error)
        <p class="text-red-500 text-sm mt-1">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-sm mt-1">{{ $errors->first($name) }}</p>
    @endif
</div>
