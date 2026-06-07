<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Install') — {{ config('app.name', 'SAFM') }}</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="w-full max-w-2xl">

    {{-- Logo & Title --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">{{ config('app.name', 'SAFM') }}</h1>
        <p class="text-gray-500 text-sm mt-1">Installation Wizard</p>
    </div>

    {{-- Step indicator --}}
    @php $currentStep = $step ?? 1; @endphp
    <div class="flex items-center justify-center mb-8">
        @foreach(['Requirements', 'Database', 'Migrations', 'Admin', 'Company', 'Done'] as $i => $label)
            @php $n = $i + 1; @endphp
            <div class="flex items-center {{ $n < 6 ? '' : '' }}">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                    {{ $n < $currentStep ? 'bg-green-500 text-white' : ($n == $currentStep ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                    {{ $n < $currentStep ? '✓' : $n }}
                </div>
                <span class="ml-1 text-xs hidden sm:block {{ $n == $currentStep ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">{{ $label }}</span>
                @if($n < 6)
                    <div class="w-8 h-0.5 mx-1 {{ $n < $currentStep ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-lg p-8">
        @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg">
            @foreach($errors->all() as $e)
                <p class="text-sm text-red-700">{{ $e }}</p>
            @endforeach
        </div>
        @endif

        @yield('content')
    </div>

</div>
<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
