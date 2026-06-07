@extends('layouts.app')
@section('title', __('app.supplier_due_report'))
@php
$pageTitle = __('app.supplier_due_report');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.suppliers'), 'url' => route('suppliers.index')],
    ['label' => __('app.due_report'), 'url' => ''],
];
@endphp
@section('content')

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.suppliers_with_outstanding_balance') }}</h3>
        <div class="flex gap-3">
            <span class="text-sm text-gray-500">{{ $suppliers->count() }} {{ __('app.supplier_s') }}</span>
            <span class="font-semibold text-red-600">{{ __('app.total_due') }}: {{ number_format($suppliers->sum('due_amount'), 2) }} DH</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('app.supplier') }}</th>
                    <th>{{ __('app.phone') }}</th>
                    <th>{{ __('app.total_purchases') }}</th>
                    <th>{{ __('app.total_paid') }}</th>
                    <th>{{ __('app.outstanding_due') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-blue-600 hover:underline">{{ $supplier->name }}</a>
                        @if($supplier->email)<div class="text-xs text-gray-500">{{ $supplier->email }}</div>@endif
                    </td>
                    <td>{{ $supplier->phone ?: '—' }}</td>
                    <td>{{ number_format($supplier->total_purchase_amount ?? 0, 2) }} DH</td>
                    <td class="text-green-600">{{ number_format($supplier->total_paid_amount ?? 0, 2) }} DH</td>
                    <td>
                        <span class="font-bold text-red-600 text-base">{{ number_format($supplier->due_amount, 2) }} DH</span>
                    </td>
                    <td>
                        <button onclick="openPayModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', {{ $supplier->due_amount }})"
                            class="btn btn-primary btn-sm">{{ __('app.pay_due') }}</button>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline btn-sm">{{ __('app.view') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">{{ __('app.no_suppliers_with_outstanding_balance') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pay Due Modal --}}
<div id="pay-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('app.record_payment_to_supplier') }}</h2>
        <form id="pay-form" method="POST">
            @csrf
            <p class="text-sm text-gray-600 mb-4">{{ __('app.recording_payment_for') }} <strong id="modal-supplier-name"></strong></p>
            <div class="form-group">
                <label class="form-label">{{ __('app.amount_dh') }}</label>
                <input type="number" name="amount" id="modal-amount" step="0.01" min="0.01" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.payment_method') }}</label>
                <select name="payment_method" class="form-control" required>
                    <option value="cash">{{ __('app.cash') }}</option>
                    <option value="bank_transfer">{{ __('app.bank_transfer') }}</option>
                    <option value="cheque">{{ __('app.cheque') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('app.notes') }}</label>
                <input type="text" name="notes" class="form-control" placeholder="{{ __('app.optional_reference') }}">
            </div>
            <div class="flex gap-3 justify-end mt-4">
                <button type="button" onclick="closePayModal()" class="btn btn-outline">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('app.confirm_payment') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPayModal(supplierId, name, dueAmount) {
    document.getElementById('modal-supplier-name').textContent = name;
    document.getElementById('modal-amount').value = parseFloat(dueAmount).toFixed(2);
    document.getElementById('pay-form').action = '/suppliers/' + supplierId + '/pay-due';
    const modal = document.getElementById('pay-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closePayModal() {
    const modal = document.getElementById('pay-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
@endsection
