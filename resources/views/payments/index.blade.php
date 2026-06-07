@extends('layouts.app')
@section('title', __('app.payments'))
@php
$pageTitle = __('app.payments');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.payments'), 'url' => route('payments.index')]];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_payments') }}</h3><a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ __('app.add_payment') }}</a></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>#</th><th>{{ __('app.date') }}</th><th>{{ __('app.reference') }}</th><th>{{ __('app.customer') }}/{{ __('app.supplier') }}</th><th>{{ __('app.amount') }}</th><th>{{ __('app.payment_method') }}</th><th>{{ __('app.type') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
    <tbody>@forelse($payments ?? [] as $payment)<tr><td>{{ $loop->iteration }}</td><td>{{ $payment->created_at->format('M d, Y') }}</td><td class="font-semibold">{{ $payment->reference }}</td><td>{{ $payment->payable->name }}</td><td>{{ number_format($payment->amount, 2) }} DH</td><td><span class="badge badge-info">{{ $payment->payment_method }}</span></td><td><span class="badge {{ $payment->type == 'received' ? 'badge-success' : 'badge-warning' }}">{{ __('app.' . $payment->type) }}</span></td><td><a href="{{ route('payments.show', $payment->id) }}" class="text-blue-600" title="{{ __('app.view') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></a></td></tr>@empty<tr><td colspan="8" class="text-center text-gray-500 py-8">{{ __('app.no_results') }}</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
