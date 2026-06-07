@extends('layouts.app')
@section('title', __('app.money_transfers'))
@php $pageTitle = __('app.money_transfers'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.money_transfers')]]; @endphp
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- New Transfer Form --}}
    <div class="card shadow-md border-t-4 border-indigo-500">
        <div class="card-header bg-white"><h3 class="font-bold text-gray-800">{{ __('app.record_new_transfer') }}</h3></div>
        <form method="POST" action="{{ route('money-transfers.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label text-sm font-bold text-gray-600">{{ __('app.from_account') }} *</label>
                    <select name="from_account_id" class="form-select" required>
                        <option value="">{{ __('app.select_account') }}</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-sm font-bold text-gray-600">{{ __('app.to_account') }} *</label>
                    <select name="to_account_id" class="form-select" required>
                        <option value="">{{ __('app.select_account') }}</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-sm font-bold text-gray-600">{{ __('app.amount') }} *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">DH</span>
                        <input type="number" step="0.01" name="amount" class="form-input pl-10" required>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label text-sm font-bold text-gray-600">{{ __('app.transfer_fee') }}</label>
                    <input type="number" step="0.01" name="fee" class="form-input" value="0">
                </div>
                <div>
                    <label class="form-label text-sm font-bold text-gray-600">{{ __('app.date') }} *</label>
                    <input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="form-label text-sm font-bold text-gray-600">{{ __('app.reference') }}</label>
                    <input type="text" name="reference" class="form-input" placeholder="{{ __('app.optional') }}">
                </div>
            </div>
            <div>
                <label class="form-label text-sm font-bold text-gray-600">{{ __('app.notes') }}</label>
                <textarea name="notes" rows="1" class="form-input" placeholder="{{ __('app.optional') }}"></textarea>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn btn-primary px-10">{{ __('app.execute_transfer') }}</button>
            </div>
        </form>
    </div>

    {{-- Transfers Table --}}
    <div class="card overflow-hidden">
        <div class="card-header bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">{{ __('app.transfer_history') }}</h3>
            <form method="GET" class="flex gap-2">
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs w-32 py-1">
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs w-32 py-1">
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.from') }}</th>
                        <th class="text-center font-bold text-gray-400">→</th>
                        <th>{{ __('app.to') }}</th>
                        <th class="text-right">{{ __('app.amount') }}</th>
                        <th class="text-right">{{ __('app.fee') }}</th>
                        <th class="text-right">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="text-sm">{{ $transfer->date->format('d/m/Y') }}</td>
                        <td><span class="font-bold text-red-600">{{ $transfer->fromAccount->name ?? '—' }}</span></td>
                        <td class="text-center text-gray-300"><i class="fas fa-arrow-right"></i></td>
                        <td><span class="font-bold text-green-600">{{ $transfer->toAccount->name ?? '—' }}</span></td>
                        <td class="text-right font-black">{{ number_format($transfer->amount, 2) }} DH</td>
                        <td class="text-right text-gray-400 text-xs">{{ number_format($transfer->fee, 2) }} DH</td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('money-transfers.show', $transfer) }}" class="text-indigo-600 hover:text-indigo-900"><i class="fas fa-eye"></i></a>
                            <form action="{{ route('money-transfers.destroy', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 ml-1"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-12 italic">{{ __('app.no_transfers_recorded') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50">{{ $transfers->links() }}</div>
    </div>
</div>
@endsection
