@extends('layouts.app')
@section('title', 'Warranty Details')
@php $pageTitle = 'Warranty Details'; $breadcrumbs = [['label'=>'Dashboard','url'=>route('dashboard')],['label'=>'Warranties','url'=>route('warranties.index')],['label'=>'#'.$warranty->id]]; @endphp
@section('content')
<div class="card max-w-2xl mx-auto">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold">Warranty #{{ $warranty->id }}</h3>
        <div class="flex gap-2">
            <a href="{{ route('warranties.edit', $warranty) }}" class="btn btn-secondary btn-sm">Edit</a>
        </div>
    </div>
    <div class="p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><span class="text-gray-500 text-sm">Product</span><p class="font-medium">{{ $warranty->product->name ?? '—' }}</p></div>
            <div><span class="text-gray-500 text-sm">Customer</span><p class="font-medium">{{ $warranty->customer->name ?? '—' }}</p></div>
            <div><span class="text-gray-500 text-sm">Serial Number</span><p class="font-mono">{{ $warranty->serial_number ?? '—' }}</p></div>
            <div><span class="text-gray-500 text-sm">Status</span><p><span class="badge badge-{{ ['active'=>'success','expired'=>'warning','claimed'=>'info','voided'=>'danger'][$warranty->status] }}">{{ ucfirst($warranty->status) }}</span></p></div>
            <div><span class="text-gray-500 text-sm">Start Date</span><p>{{ $warranty->start_date->format('M d, Y') }}</p></div>
            <div><span class="text-gray-500 text-sm">End Date</span><p>{{ $warranty->end_date->format('M d, Y') }}</p></div>
        </div>
        @if($warranty->notes)
        <div><span class="text-gray-500 text-sm">Notes</span><p class="mt-1">{{ $warranty->notes }}</p></div>
        @endif
    </div>
</div>
@endsection
