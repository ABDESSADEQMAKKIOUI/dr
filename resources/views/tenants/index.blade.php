@extends('layouts.app')
@section('title', __('app.tenants'))
@php
$pageTitle = __('app.tenants');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.tenants'), 'url' => route('tenants.index')]];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_tenants') }}</h3><a href="{{ route('tenants.create') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ __('app.add_tenant') }}</a></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>#</th><th>{{ __('app.name') }}</th><th>{{ __('app.domain') }}</th><th>{{ __('app.plan') }}</th><th>{{ __('app.users') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.created_at') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
    <tbody>@forelse($tenants ?? [] as $tenant)<tr><td>{{ $loop->iteration }}</td><td class="font-medium">{{ $tenant->name }}</td><td>{{ $tenant->domain }}</td><td><span class="badge badge-primary">{{ ucfirst($tenant->plan) }}</span></td><td>{{ $tenant->users_count }}</td><td><span class="badge {{ $tenant->status == 'active' ? 'badge-success' : 'badge-secondary' }}">{{ __('app.' . $tenant->status) }}</span></td><td>{{ $tenant->created_at->format('M d, Y') }}</td><td><div class="flex space-x-2"><a href="{{ route('tenants.edit', $tenant->id) }}" class="text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a></div></td></tr>@empty<tr><td colspan="8" class="text-center text-gray-500 py-8">{{ __('app.no_tenants_found') }}</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
