@props([
    'title' => '',
    'value' => '',
    'icon' => 'fa-chart-bar',
    'color' => 'blue',
    'percentage' => null,
    'trend' => null // 'up', 'down', or null
])

<div class="{{ $attributes->get('class', 'bg-white rounded-lg shadow-md p-6') }}">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">{{ $title }}</p>
            <h3 class="text-2xl font-bold text-gray-800 mt-2">{{ $value }}</h3>
            
            @if($percentage !== null)
                <div class="flex items-center mt-2 text-sm">
                    @if($trend === 'up')
                        <span class="text-green-500 flex items-center font-bold">
                            <i class="fas fa-arrow-up mr-1 text-xs"></i> {{ $percentage }}%
                        </span>
                    @elseif($trend === 'down')
                        <span class="text-red-500 flex items-center font-bold">
                            <i class="fas fa-arrow-down mr-1 text-xs"></i> {{ $percentage }}%
                        </span>
                    @else
                        <span class="text-gray-500 flex items-center font-bold">
                            {{ $percentage }}%
                        </span>
                    @endif
                    <span class="text-gray-400 ml-2">from last month</span>
                </div>
            @endif
        </div>
        
        <div class="bg-{{ $color }}-100 p-4 rounded-full shadow-sm">
            <i class="fas {{ $icon }} text-{{ $color }}-600 text-xl w-6 h-6 flex items-center justify-center"></i>
        </div>
    </div>
</div>
