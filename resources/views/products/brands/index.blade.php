@extends('layouts.app')
@section('title', __('app.brands'))
@php
$pageTitle = __('app.brands');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.products'), 'url' => route('products.index')], ['label' => __('app.brands'), 'url' => '']];
@endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.all') }} {{ __('app.brands') }}</h3><a href="{{ route('products.brands.create') }}" class="btn btn-primary btn-sm"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ __('app.add_brand') }}</a></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>#</th><th>{{ __('app.name') }}</th><th>{{ __('app.description') }}</th><th>{{ __('app.products') }}</th><th>{{ __('app.created_at') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
    <tbody>@forelse($brands ?? [] as $brand)<tr><td>{{ $loop->iteration }}</td><td class="font-medium">{{ $brand->name }}</td><td>{{ $brand->description ?? '-' }}</td><td><span class="badge badge-info">{{ $brand->products_count ?? 0 }} {{ __('app.products') }}</span></td><td>{{ $brand->created_at->format('M d, Y') }}</td><td><div class="flex space-x-2"><a href="{{ route('products.brands.edit', $brand->id) }}" class="text-green-600" title="{{ __('app.edit') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a><form method="POST" action="{{ route('products.brands.destroy', $brand->id) }}" class="inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">@csrf @method('DELETE')<button type="submit" class="text-red-600" title="{{ __('app.delete') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form></div></td></tr>@empty<tr><td colspan="6" class="text-center text-gray-500 py-8">{{ __('app.no_results') }}</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
