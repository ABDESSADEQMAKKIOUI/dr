@extends('layouts.app')
@section('title', __('app.expense') . ' ' . __('app.details'))
@php
$pageTitle = __('app.expense') . ' ' . __('app.details');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.expenses'), 'url' => route('expenses.index')], ['label' => __('app.details'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.expense') }} #{{ $expense->id }}</h3>
        <div class="flex space-x-2">
            <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-primary btn-sm">{{ __('app.edit') }}</a>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline btn-sm">{{ __('app.back') }}</a>
        </div>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">{{ __('app.category') }}</label>
                    <p class="font-semibold text-gray-800">{{ $expense->category->name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">{{ __('app.amount') }}</label>
                    <p class="font-semibold text-2xl text-red-600">{{ number_format($expense->amount, 2) }} DH</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">{{ __('app.date') }}</label>
                    <p class="font-semibold text-gray-800">{{ $expense->date ? $expense->date->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">Payment Method</label>
                    <p class="font-semibold text-gray-800">{{ $expense->paymentMethod->name ?? 'N/A' }}</p>
                </div>
            </div>
            <!-- Right Column -->
            <div class="space-y-4">
                @if($expense->reference)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">Reference</label>
                    <p class="font-semibold text-gray-800">{{ $expense->reference }}</p>
                </div>
                @endif
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">{{ __('app.created_by') }}</label>
                    <p class="font-semibold text-gray-800">{{ $expense->user->full_name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">{{ __('app.created_at') }}</label>
                    <p class="font-semibold text-gray-800">{{ $expense->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm text-gray-500 block mb-1">Last Updated</label>
                    <p class="font-semibold text-gray-800">{{ $expense->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
        <!-- Description -->
        @if($expense->description)
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <label class="text-sm text-gray-500 block mb-2">{{ __('app.description') }}</label>
            <p class="text-gray-800 whitespace-pre-line">{{ $expense->description }}</p>
        </div>
        @endif
        <!-- Attachment -->
        @if($expense->attachment)
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
            <label class="text-sm text-blue-700 block mb-2 font-semibold">Attachment</label>
            <div class="flex items-center space-x-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                <div class="flex-1">
                    <a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                        View Attachment
                    </a>
                    <p class="text-xs text-gray-500 mt-1">{{ basename($expense->attachment) }}</p>
                </div>
                <a href="{{ Storage::url($expense->attachment) }}" download class="btn btn-sm btn-outline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
