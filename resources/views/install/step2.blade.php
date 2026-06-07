@extends('install.layout')
@section('title', 'Step 2 — Database')
@section('content')
<h2 class="text-xl font-bold text-gray-800 mb-1">Database Configuration</h2>
<p class="text-sm text-gray-500 mb-6">Enter your database connection details.</p>

<form action="{{ route('install.process', ['step' => 2]) }}" method="POST" class="space-y-4">
    @csrf
    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
            <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
            <input type="text" name="db_port" value="{{ old('db_port', '3306') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
        <input type="text" name="db_database" value="{{ old('db_database') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="erp_db">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-gray-400">(optional)</span></label>
        <input type="password" name="db_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
    </div>
    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
        Test &amp; Continue →
    </button>
</form>
@endsection
