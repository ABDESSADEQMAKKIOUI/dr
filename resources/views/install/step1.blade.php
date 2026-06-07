@extends('install.layout')
@section('title', 'Step 1 — Requirements')
@section('content')
<h2 class="text-xl font-bold text-gray-800 mb-1">Server Requirements</h2>
<p class="text-sm text-gray-500 mb-6">Make sure your server meets all the requirements before continuing.</p>

<div class="space-y-2 mb-8">
    @foreach($requirements as $req)
    <div class="flex items-center justify-between p-3 rounded-lg {{ $req['pass'] ? 'bg-green-50' : 'bg-red-50' }}">
        <span class="text-sm font-medium {{ $req['pass'] ? 'text-green-800' : 'text-red-800' }}">{{ $req['name'] }}</span>
        @if($req['pass'])
            <span class="text-green-600 font-bold">✓ OK</span>
        @else
            <span class="text-red-600 font-bold">✗ Missing</span>
        @endif
    </div>
    @endforeach
</div>

<form action="{{ route('install.process', ['step' => 1]) }}" method="POST">
    @csrf
    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
        Continue →
    </button>
</form>
@endsection
