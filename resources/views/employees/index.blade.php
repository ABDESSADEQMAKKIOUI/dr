@extends('layouts.app')
@section('title', __('app.employees'))
@php
$pageTitle = __('app.employees');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.employees'), 'url' => route('employees.index')]];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_employees') }}</h3><a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ __('app.add_employee') }}</a></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>#</th><th>{{ __('app.name') }}</th><th>{{ __('app.email') }}</th><th>{{ __('app.phone') }}</th><th>{{ __('app.department') }}</th><th>{{ __('app.position') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
    <tbody>@forelse($employees ?? [] as $emp)<tr><td>{{ $loop->iteration }}</td><td class="font-medium">{{ $emp->name }}</td><td>{{ $emp->email }}</td><td>{{ $emp->phone }}</td><td>{{ $emp->department->name ?? 'N/A' }}</td><td>{{ $emp->designation->name ?? 'N/A' }}</td><td><span class="badge {{ $emp->status == 'active' ? 'badge-success' : 'badge-secondary' }}">{{ $emp->status == 'active' ? __('app.active') : __('app.inactive') }}</span></td><td><div class="flex space-x-2"><a href="{{ route('employees.show', $emp->id) }}" class="text-blue-600" title="{{ __('app.view') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></a><a href="{{ route('employees.edit', $emp->id) }}" class="text-green-600" title="{{ __('app.edit') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a></div></td></tr>@empty<tr><td colspan="8" class="text-center text-gray-500 py-8">{{ __('app.no_results') }}</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
