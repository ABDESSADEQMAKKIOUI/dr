@extends('layouts.app')
@section('title', __('app.edit_deposit'))
@php $pageTitle = __('app.edit_deposit'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.deposits'),'url'=>route('deposits.index')],['label'=>__('app.edit')]]; @endphp
@section('content')
<div class="card max-w-4xl mx-auto shadow-xl border-t-4 border-green-500">
    <div class="card-header bg-white">
        <h3 class="text-xl font-bold text-gray-800">{{ __('app.edit_deposit') }}: {{ $deposit->reference ?? '#'.$deposit->id }}</h3>
    </div>
    <form method="POST" action="{{ route('deposits.update', $deposit) }}" class="p-8 space-y-6">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.amount') }} <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">DH</span>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $deposit->amount) }}" class="form-input pl-10 text-lg font-bold" required>
                </div>
            </div>
            
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', $deposit->date->format('Y-m-d')) }}" class="form-input" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.category') }}</label>
                <select name="category_id" class="form-select">
                    <option value="">-- {{ __('app.select_category') }} --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $deposit->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.deposit_account') }}</label>
                <select name="account_id" class="form-select">
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id', $deposit->account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.payment_method') }}</label>
                <select name="payment_method" class="form-select">
                    @foreach(['Cash', 'Bank Transfer', 'Check', 'Card'] as $method)
                        <option value="{{ $method }}" {{ old('payment_method', $deposit->payment_method) == $method ? 'selected' : '' }}>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.reference') }}</label>
            <input type="text" name="reference" value="{{ old('reference', $deposit->reference) }}" class="form-input">
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.notes') }}</label>
            <textarea name="notes" rows="3" class="form-input">{{ old('notes', $deposit->notes) }}</textarea>
        </div>

        <div class="flex gap-4 pt-6 border-t">
            <button type="submit" class="btn btn-primary px-8">{{ __('app.update_deposit') }}</button>
            <a href="{{ route('deposits.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
