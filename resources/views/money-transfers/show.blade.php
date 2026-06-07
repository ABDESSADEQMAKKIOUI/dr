@extends('layouts.app')
@section('title', __('app.transfer_details'))
@php $pageTitle = __('app.transfer_details'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.money_transfers'),'url'=>route('money-transfers.index')],['label'=>__('app.view')]]; @endphp
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card overflow-hidden shadow-2xl rounded-2xl border-none">
        <div class="bg-indigo-600 p-8 text-white relative overflow-hidden">
            <div class="relative z-10 flex justify-between items-center">
                <div>
                    <h2 class="text-sm uppercase font-black tracking-widest text-indigo-200">{{ __('app.transfer_reference') }}</h2>
                    <p class="text-2xl font-mono">{{ $moneyTransfer->reference ?? '#TR-'.$moneyTransfer->id }}</p>
                </div>
                <div class="text-right">
                    <h3 class="text-sm uppercase font-black tracking-widest text-indigo-200">{{ __('app.total_amount') }}</h3>
                    <p class="text-4xl font-black">{{ number_format($moneyTransfer->amount, 2) }} <span class="text-lg">DH</span></p>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500 rounded-full opacity-50"></div>
        </div>

        <div class="p-8 bg-white grid grid-cols-1 md:grid-cols-2 gap-12 border-b">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 block">{{ __('app.from') }}</label>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-red-50 text-red-500 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-sign-out-alt"></i></div>
                    <div>
                        <p class="text-xl font-black text-gray-800">{{ $moneyTransfer->fromAccount->name ?? '—' }}</p>
                        <p class="text-sm text-gray-500 uppercase tracking-tighter">{{ __('app.source_account') }}</p>
                    </div>
                </div>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 block">{{ __('app.to') }}</label>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-50 text-green-500 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-sign-in-alt"></i></div>
                    <div>
                        <p class="text-xl font-black text-gray-800">{{ $moneyTransfer->toAccount->name ?? '—' }}</p>
                        <p class="text-sm text-gray-500 uppercase tracking-tighter">{{ __('app.destination_account') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8 bg-gray-50 grid grid-cols-2 md:grid-cols-3 gap-8 text-center border-b">
            <div>
                <label class="text-xs font-black text-gray-400 uppercase block mb-1">{{ __('app.date') }}</label>
                <p class="font-bold text-gray-800">{{ $moneyTransfer->date->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="text-xs font-black text-gray-400 uppercase block mb-1">{{ __('app.fee') }}</label>
                <p class="font-bold text-gray-800">{{ number_format($moneyTransfer->fee, 2) }} DH</p>
            </div>
            <div class="col-span-2 md:col-span-1">
                <label class="text-xs font-black text-gray-400 uppercase block mb-1">{{ __('app.recorded_at') }}</label>
                <p class="font-bold text-gray-800">{{ $moneyTransfer->created_at->format('H:i') }}</p>
            </div>
        </div>

        @if($moneyTransfer->notes)
        <div class="p-8 bg-white">
            <label class="text-xs font-black text-gray-400 uppercase mb-3 block">{{ __('app.notes') }}</label>
            <div class="p-4 bg-indigo-50 border-l-4 border-indigo-400 rounded-r-lg text-indigo-900 leading-relaxed italic">
                {{ $moneyTransfer->notes }}
            </div>
        </div>
        @endif

        <div class="p-8 bg-gray-50 flex justify-between">
            <a href="{{ route('money-transfers.index') }}" class="btn btn-secondary px-8 font-bold">{{ __('app.back_to_list') }}</a>
            <form action="{{ route('money-transfers.destroy', $moneyTransfer) }}" method="POST" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger px-8 font-bold">{{ __('app.delete_record') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
