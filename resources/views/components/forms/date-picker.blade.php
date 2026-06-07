@props([
    'disabled' => false,
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => 'Select date',
    'required' => false,
    'min' => null,
    'max' => null,
    'error' => null
])

<div class="{{ $attributes->get('class', 'mb-4') }}">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-calendar text-gray-400"></i>
        </div>
        <input 
            type="date"
            {{ $disabled ? 'disabled' : '' }}
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            @if($min) min="{{ $min }}" @endif
            @if($max) max="{{ $max }}" @endif
            {!! $attributes->except(['class', 'disabled', 'label', 'name', 'value', 'placeholder', 'required', 'min', 'max', 'error']) !!}
            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 
            {{ $error || $errors->has($name) ? 'border-red-500 focus:ring-red-500' : 'border-gray-300' }}
            {{ $disabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white' }}"
        >
    </div>

    @if($error)
        <p class="text-red-500 text-sm mt-1">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-sm mt-1">{{ $errors->first($name) }}</p>
    @endif
</div>
