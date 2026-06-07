@extends('layouts.app')
@section('title', __('app.deposit_details'))
@php $pageTitle = __('app.deposit_details'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.deposits'),'url'=>route('deposits.index')],['label'=>__('app.view')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="card p-8 border-l-4 border-blue-500 shadow-lg">
        <div class="flex justify-between items-start mb-8">
            <div>
                <span class="badge badge-secondary mb-2">{{ $deposit->category->name ?? 'General' }}</span>
                <h3 class="text-3xl font-extrabold text-gray-900">{{ __('app.deposit') }}</h3>
                <p class="text-gray-500 font-mono">{{ $deposit->reference ?? 'NO REFERENCE' }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-gray-400 text-uppercase mb-1">{{ __('app.amount') }}</p>
                <h4 class="text-4xl font-black text-green-600 tracking-tight">{{ number_format($deposit->amount, 2) }} <span class="text-lg">DH</span></h4>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12 border-t pt-8">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold">{{ __('app.date') }}</label>
                    <p class="font-bold text-gray-800">{{ $deposit->date->format('l, d F Y') }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500"><i class="fas fa-university"></i></div>
                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold">{{ __('app.account') }}</label>
                    <p class="font-bold text-gray-800">{{ $deposit->account->name ?? '—' }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-500"><i class="fas fa-credit-card"></i></div>
                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold">{{ __('app.payment_method') }}</label>
                    <p class="font-bold text-gray-800">{{ $deposit->payment_method ?? '—' }}</p>
                </div>
            </div>
        </div>

        @if($deposit->notes)
        <div class="mt-12 p-4 bg-gray-50 border border-gray-100 rounded-lg">
            <label class="text-xs text-gray-400 uppercase font-bold mb-2 block">{{ __('app.notes') }}</label>
            <p class="text-gray-700 italic">{{ $deposit->notes }}</p>
        </div>
        @endif

        <div class="mt-12 flex gap-3">
            <a href="{{ route('deposits.edit', $deposit) }}" class="btn btn-primary px-6">{{ __('app.edit') }}</a>
            <a href="{{ route('deposits.index') }}" class="btn btn-secondary px-6">{{ __('app.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
