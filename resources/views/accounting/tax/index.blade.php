@extends('layouts.app')
@section('title', __('app.tax_management'))
@php
$pageTitle = __('app.tax_management');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.accounting'), 'url' => '#'], ['label' => __('app.tax'), 'url' => '']];
$stats = $stats ?? ['collected' => 0, 'paid' => 0];
@endphp
@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="card bg-blue-50 border-blue-200"><div class="flex items-center justify-between"><div><p class="text-blue-600 text-sm mb-1">{{ __('app.tax_collected') }}</p><h3 class="text-3xl font-bold text-blue-700">{{ number_format($stats['collected'] ?? 0, 2) }} DH</h3></div><svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div></div>
    <div class="card bg-green-50 border-green-200"><div class="flex items-center justify-between"><div><p class="text-green-600 text-sm mb-1">{{ __('app.tax_paid') }}</p><h3 class="text-3xl font-bold text-green-700">{{ number_format($stats['paid'] ?? 0, 2) }} DH</h3></div><svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div></div>
    <div class="card bg-purple-50 border-purple-200"><div class="flex items-center justify-between"><div><p class="text-purple-600 text-sm mb-1">{{ __('app.net_tax') }}</p><h3 class="text-3xl font-bold text-purple-700">{{ number_format(($stats['collected'] ?? 0) - ($stats['paid'] ?? 0), 2) }} DH</h3></div><svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg></div></div>
</div>

<div class="card mb-6">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.tax_summary') }}</h3><a href="{{ route('accounting.tax.declaration') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>{{ __('app.tax_declaration') }}</a></div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div><label class="form-label">{{ __('app.from_date') }}</label><input type="date" name="from_date" value="{{ request('from_date', date('Y-m-01')) }}" class="form-control"></div>
        <div><label class="form-label">{{ __('app.to_date') }}</label><input type="date" name="to_date" value="{{ request('to_date', date('Y-m-d')) }}" class="form-control"></div>
        <div class="flex items-end"><button type="submit" class="btn btn-primary w-full">{{ __('app.generate') }}</button></div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card"><h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('app.tax_collected_sales') }}</h3><div class="overflow-x-auto"><table class="table"><thead><tr><th>{{ __('app.description') }}</th><th>{{ __('app.amount') }}</th></tr></thead><tbody>@forelse($taxCollected ?? [] as $item)<tr><td>{{ $item['description'] }}</td><td class="font-semibold text-green-600">{{ number_format($item['amount'], 2) }} DH</td></tr>@empty<tr><td colspan="2" class="text-center text-gray-500 py-4">{{ __('app.no_data') }}</td></tr>@endforelse<tr class="bg-gray-50 font-semibold"><td>{{ __('app.total_collected') }}</td><td class="text-green-600">{{ number_format($stats['collected'] ?? 0, 2) }} DH</td></tr></tbody></table></div></div>
    <div class="card"><h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('app.tax_paid_purchases') }}</h3><div class="overflow-x-auto"><table class="table"><thead><tr><th>{{ __('app.description') }}</th><th>{{ __('app.amount') }}</th></tr></thead><tbody>@forelse($taxPaid ?? [] as $item)<tr><td>{{ $item['description'] }}</td><td class="font-semibold text-red-600">{{ number_format($item['amount'], 2) }} DH</td></tr>@empty<tr><td colspan="2" class="text-center text-gray-500 py-4">{{ __('app.no_data') }}</td></tr>@endforelse<tr class="bg-gray-50 font-semibold"><td>{{ __('app.total_paid') }}</td><td class="text-red-600">{{ number_format($stats['paid'] ?? 0, 2) }} DH</td></tr></tbody></table></div></div>
</div>
@endsection
