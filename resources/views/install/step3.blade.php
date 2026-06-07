@extends('install.layout')
@section('title', 'Step 3 — Migrations')
@section('content')
<h2 class="text-xl font-bold text-gray-800 mb-1">Database Setup</h2>
<p class="text-sm text-gray-500 mb-6">Click the button below to run database migrations and seed default data.</p>

<div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-6 text-sm text-yellow-800">
    <strong>Warning:</strong> This will create all database tables and insert default data. If the database is not empty, existing data may be affected.
</div>

<form action="{{ route('install.process', ['step' => 3]) }}" method="POST">
    @csrf
    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition">
        Run Migrations &amp; Seeders →
    </button>
</form>
@endsection
