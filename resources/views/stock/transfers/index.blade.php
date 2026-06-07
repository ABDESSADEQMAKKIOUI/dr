@extends('layouts.app')
@section('title', __('app.stock_transfers'))
@php
$pageTitle = __('app.stock_transfers');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.stock'), 'url' => '#'], ['label' => __('app.transfers'), 'url' => '']];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.stock_transfers') }}</h3><a href="{{ route('stock.transfers.create') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ __('app.new') }} {{ __('app.transfer') }}</a></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>#</th><th>{{ __('app.reference') }}</th><th>{{ __('app.from') }} {{ __('app.warehouse') }}</th><th>{{ __('app.to') }} {{ __('app.warehouse') }}</th><th>{{ __('app.date') }}</th><th>{{ __('app.items') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
    <tbody>@forelse($transfers ?? [] as $transfer)<tr><td>{{ $loop->iteration }}</td><td class="font-semibold">{{ $transfer->reference }}</td><td>{{ $transfer->fromWarehouse->name ?? '-' }}</td><td>{{ $transfer->toWarehouse->name ?? '-' }}</td><td>{{ $transfer->date?->format('M d, Y') ?? $transfer->created_at->format('M d, Y') }}</td><td>{{ $transfer->items_count ?? 0 }} {{ __('app.items') }}</td><td>@if($transfer->status == 'pending')<span class="badge badge-warning">{{ __('app.pending') }}</span>@elseif($transfer->status == 'in_transit')<span class="badge badge-info">{{ __('app.in_transit') }}</span>@else<span class="badge badge-success">{{ __('app.completed') }}</span>@endif</td><td><a href="{{ route('stock.transfers.show', $transfer->id) }}" class="text-blue-600 hover:text-blue-800" title="{{ __('app.view') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></a></td></tr>@empty<tr><td colspan="8" class="text-center text-gray-500 py-8">{{ __('app.no_results') }}</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
