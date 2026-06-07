@extends('layouts.app')
@section('title', __('app.leads'))
@php
$pageTitle = __('app.leads');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.crm'), 'url' => '#'], ['label' => __('app.leads'), 'url' => '']];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_leads') }}</h3><a href="{{ route('crm.leads.create') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ __('app.add_lead') }}</a></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>#</th><th>{{ __('app.name') }}</th><th>{{ __('app.company') }}</th><th>{{ __('app.email') }}</th><th>{{ __('app.phone') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
    <tbody>@forelse($leads ?? [] as $lead)<tr><td>{{ $loop->iteration }}</td><td class="font-medium">{{ $lead->name }}</td><td>{{ $lead->company }}</td><td>{{ $lead->email }}</td><td>{{ $lead->phone }}</td><td><span class="badge badge-info">{{ __('app.' . ($lead->status ?? 'new')) }}</span></td><td><div class="flex space-x-2"><a href="{{ route('crm.leads.edit', $lead->id) }}" class="text-green-600" title="{{ __('app.edit') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a><form method="POST" action="{{ route('crm.leads.convert', $lead->id) }}" class="inline">@csrf<button type="submit" class="text-purple-600" title="{{ __('app.convert_to_customer') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></button></form></div></td></tr>@empty<tr><td colspan="7" class="text-center text-gray-500 py-8">{{ __('app.no_results') }}</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
