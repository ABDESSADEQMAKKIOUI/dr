@props([
    'disabled' => false,
    'label' => '',
    'name' => '',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
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

    <select 
        {{ $disabled ? 'disabled' : '' }}
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {!! $attributes->except(['class', 'disabled', 'label', 'name', 'options', 'selected', 'placeholder', 'required', 'error']) !!}
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200
        {{ $error || $errors->has($name) ? 'border-red-500 focus:ring-red-500' : 'border-gray-300' }}
        {{ $disabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white' }}"
    >
        @if($placeholder !== false)
            <option value="">{{ $placeholder ?? __('app.select_an_option') }}</option>
        @endif

        @foreach($options as $key => $option)
            @if(is_array($option) || is_object($option))
                {{-- Handle collection or array of objects --}}
                <option value="{{ $option->id ?? $option['id'] ?? $key }}" 
                    {{ (old($name, $selected) == ($option->id ?? $option['id'] ?? $key)) ? 'selected' : '' }}>
                    {{ $option->name ?? $option['name'] ?? $option }}
                </option>
            @else
                {{-- Handle simple key-value array --}}
                <option value="{{ $key }}" {{ (old($name, $selected) == $key) ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endif
        @endforeach
        
        {{ $slot }}
    </select>

    @if($error)
        <p class="text-red-500 text-sm mt-1">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="text-red-500 text-sm mt-1">{{ $errors->first($name) }}</p>
    @endif
</div>
