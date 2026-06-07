@extends('layouts.app')
@section('title', __('app.add_deposit'))
@php $pageTitle = __('app.add_deposit'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.deposits'),'url'=>route('deposits.index')],['label'=>__('app.add')]]; @endphp
@section('content')
<div class="card max-w-4xl mx-auto shadow-xl border-t-4 border-blue-500">
    <div class="card-header bg-white"><h3 class="text-xl font-bold text-gray-800">{{ __('app.record_new_deposit') }}</h3></div>
    <form method="POST" action="{{ route('deposits.store') }}" class="p-8 space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.amount') }} <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">DH</span>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-input pl-10 text-lg font-bold" required>
                </div>
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-input" required>
                @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.category') }}</label>
                <select name="category_id" class="form-select">
                    <option value="">-- {{ __('app.select_category') }} --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.deposit_account') }}</label>
                <select name="account_id" class="form-select">
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label font-bold text-gray-700">{{ __('app.payment_method') }}</label>
                <select name="payment_method" class="form-select">
                    <option value="Cash">{{ __('app.cash') }}</option>
                    <option value="Bank Transfer">{{ __('app.bank_transfer') }}</option>
                    <option value="Check">{{ __('app.check') }}</option>
                    <option value="Card">{{ __('app.card') }}</option>
                </select>
            </div>
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.reference') }}</label>
            <input type="text" name="reference" value="{{ old('reference') }}" class="form-input" placeholder="e.g. Receipt #, Transaction ID">
        </div>

        <div>
            <label class="form-label font-bold text-gray-700">{{ __('app.notes') }}</label>
            <textarea name="notes" rows="3" class="form-input" placeholder="{{ __('app.any_additional_details') }}">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-4 pt-6 border-t">
            <button type="submit" class="btn btn-primary px-8">{{ __('app.save_deposit') }}</button>
            <a href="{{ route('deposits.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
