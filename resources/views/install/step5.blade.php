@extends('install.layout')
@section('title', 'Step 5 — Company Info')
@section('content')
<h2 class="text-xl font-bold text-gray-800 mb-1">Company Information</h2>
<p class="text-sm text-gray-500 mb-6">Configure your company details and regional preferences.</p>

<form action="{{ route('install.process', ['step' => 5]) }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
        <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="My Company LLC">
        @error('company_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
        <input type="text" name="currency" value="{{ old('currency', 'DH') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="DH, USD, EUR…">
        @error('currency')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
        <select name="timezone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            @foreach(timezone_identifiers_list() as $tz)
                <option value="{{ $tz }}" {{ old('timezone', 'Africa/Casablanca') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
            @endforeach
        </select>
        @error('timezone')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
        Finish Installation →
    </button>
</form>
@endsection
