@extends('layouts.app')
@section('title', __('app.edit_recurring_invoice'))
@php $pageTitle = __('app.edit_recurring_invoice'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.recurring_invoices'),'url'=>route('recurring-invoices.index')],['label'=>__('app.edit')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto shadow-xl rounded-2xl overflow-hidden">
    <div class="bg-indigo-700 p-8 text-white flex justify-between items-center">
        <div>
            <h3 class="text-2xl font-black italic uppercase tracking-tighter">{{ __('app.update_subscription') }}</h3>
            <p class="text-indigo-200 text-sm">{{ $recurringInvoice->customer->name ?? '—' }}</p>
        </div>
        <span class="badge badge-light font-black uppercase text-[10px] tracking-widest px-4 py-2">{{ $recurringInvoice->status }}</span>
    </div>
    
    <form method="POST" action="{{ route('recurring-invoices.update', $recurringInvoice) }}" class="bg-white p-8 space-y-8">
        @csrf @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.status') }} *</label>
                <select name="status" class="form-select border-gray-200 font-bold" required>
                    @foreach(['active', 'paused', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ old('status', $recurringInvoice->status) == $st ? 'selected' : '' }}>{{ __('app.' . $st) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.frequency') }} *</label>
                    <select name="frequency" class="form-select border-gray-200 font-bold" required>
                        @foreach(['daily', 'weekly', 'monthly', 'yearly'] as $fq)
                            <option value="{{ $fq }}" {{ old('frequency', $recurringInvoice->frequency) == $fq ? 'selected' : '' }}>{{ __('app.' . $fq) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.next_run_date') }} *</label>
                    <input type="date" name="next_run_at" class="form-input border-gray-200" value="{{ old('next_run_at', $recurringInvoice->next_run_at ? $recurringInvoice->next_run_at->format('Y-m-d') : '') }}" required>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl">
            <input type="hidden" name="send_email" value="0">
            <input type="checkbox" name="send_email" id="send_email" value="1" class="form-checkbox h-6 w-6 text-indigo-600 rounded-lg" {{ $recurringInvoice->send_email ? 'checked' : '' }}>
            <label for="send_email" class="text-sm font-bold text-indigo-900">{{ __('app.automatically_send_invoice_to_customer_email') }}</label>
        </div>

        <div class="space-y-2">
            <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.notes') }}</label>
            <textarea name="notes" rows="3" class="form-input border-gray-200">{{ old('notes', $recurringInvoice->notes) }}</textarea>
        </div>

        <div class="pt-6 flex gap-4 border-t">
            <button type="submit" class="btn btn-primary px-12 py-4 text-lg font-black uppercase tracking-tighter shadow-lg shadow-indigo-200">{{ __('app.update_subscription') }}</button>
            <a href="{{ route('recurring-invoices.show', $recurringInvoice) }}" class="btn btn-secondary flex items-center px-8">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
