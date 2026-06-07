@props([
    'title' => '',
    'footer' => null
])

<div class="{{ $attributes->get('class', 'bg-white rounded-lg shadow-md overflow-hidden') }}">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            {{ $actions ?? '' }}
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            {{ $footer }}
        </div>
    @endif
</div>
