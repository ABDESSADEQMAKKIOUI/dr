@extends('layouts.app')
@section('title', 'Pay Invoice ' . $invoice->reference)
@php
$pageTitle = 'Pay Invoice: ' . $invoice->reference;
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => 'Invoices', 'url' => route('invoices.index')],
    ['label' => $invoice->reference, 'url' => route('invoices.show', $invoice->id)],
    ['label' => 'Pay', 'url' => ''],
];
@endphp

@section('content')
<div class="max-w-2xl">
    <!-- Invoice summary -->
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-800">Invoice Summary</h3>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm mt-2">
            <div><span class="text-gray-500">Invoice #</span><p class="font-semibold">{{ $invoice->reference }}</p></div>
            <div><span class="text-gray-500">Customer</span><p class="font-semibold">{{ $invoice->customer?->name ?? '—' }}</p></div>
            <div><span class="text-gray-500">Total</span><p class="font-semibold">{{ number_format($invoice->total_amount, 2) }} DH</p></div>
            <div><span class="text-gray-500">Paid</span><p class="font-semibold text-green-600">{{ number_format($invoice->paid_amount, 2) }} DH</p></div>
            <div class="col-span-2 border-t pt-3">
                <span class="text-gray-500">Amount Due</span>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }} DH</p>
            </div>
        </div>
    </div>

    @php $due = $invoice->total_amount - $invoice->paid_amount; @endphp

    @if($due <= 0)
    <div class="card text-center py-8">
        <div class="text-green-600 text-5xl mb-3">✓</div>
        <h3 class="text-xl font-bold text-green-700">Invoice Fully Paid</h3>
        <p class="text-gray-500 mt-2">This invoice has no outstanding balance.</p>
        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-outline mt-4">Back to Invoice</a>
    </div>
    @else

    <!-- Saved cards (if any) -->
    @if(count($savedCards) > 0)
    <div class="card mb-4">
        <h4 class="font-semibold text-gray-800 mb-3">Pay with Saved Card</h4>
        <div class="space-y-2" id="saved-cards">
            @foreach($savedCards as $card)
            <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 hover:border-blue-400 cursor-pointer card-option"
                data-pm-id="{{ $card['id'] }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-7 bg-gray-800 rounded flex items-center justify-center text-white text-xs font-bold uppercase">
                        {{ substr($card['brand'], 0, 4) }}
                    </div>
                    <div>
                        <p class="font-medium text-sm">•••• •••• •••• {{ $card['last4'] }}</p>
                        <p class="text-xs text-gray-500">Expires {{ $card['exp'] }}</p>
                    </div>
                </div>
                <button onclick="chargeSavedCard('{{ $card['id'] }}')"
                    class="btn btn-primary btn-sm">Charge {{ number_format($due, 2) }} DH</button>
            </div>
            @endforeach
        </div>
        <div class="border-t mt-4 pt-4">
            <button onclick="document.getElementById('new-card-section').classList.toggle('hidden')"
                class="text-blue-600 text-sm hover:underline">+ Pay with a new card</button>
        </div>
    </div>
    @endif

    <!-- New card payment -->
    <div class="card {{ count($savedCards) > 0 ? 'hidden' : '' }}" id="new-card-section">
        <h4 class="font-semibold text-gray-800 mb-4">
            {{ count($savedCards) > 0 ? 'New Card' : 'Pay with Card' }}
        </h4>

        @if(!config('services.stripe.key'))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
            <strong>Stripe not configured.</strong> Add <code>STRIPE_KEY</code> and <code>STRIPE_SECRET</code> to your <code>.env</code> file.
        </div>
        @else
        <div id="stripe-error" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 hidden mb-4"></div>
        <div id="stripe-success" class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700 hidden mb-4"></div>

        <form id="payment-form">
            <div class="mb-4">
                <label class="form-label">Card Details</label>
                <div id="card-element" class="form-control py-3"></div>
            </div>
            <div class="flex items-center gap-2 mb-4">
                <input type="checkbox" id="save-card" class="rounded">
                <label for="save-card" class="text-sm text-gray-600">Save card for future payments</label>
            </div>
            <button type="submit" id="pay-btn" class="w-full btn btn-primary">
                Pay {{ number_format($due, 2) }} DH
            </button>
        </form>
        @endif
    </div>

    @endif
</div>
@endsection

@if(config('services.stripe.key'))
@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe     = Stripe('{{ $stripeKey }}');
const elements   = stripe.elements();
const cardEl     = elements.create('card', {
    style: { base: { fontSize: '15px', color: '#374151', '::placeholder': { color: '#9ca3af' } } }
});
cardEl.mount('#card-element');

const invoiceId  = {{ $invoice->id }};
const csrfToken  = '{{ csrf_token() }}';

async function getPaymentIntent() {
    const res = await fetch('/api/payments/stripe/intent', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ invoice_id: invoiceId })
    });
    return await res.json();
}

document.getElementById('payment-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.textContent = 'Processing…';
    showError('');

    try {
        const { client_secret, payment_intent_id, error: intentError } = await getPaymentIntent();
        if (intentError) { showError(intentError); btn.disabled = false; btn.textContent = 'Pay'; return; }

        const { paymentIntent, error } = await stripe.confirmCardPayment(client_secret, {
            payment_method: { card: cardEl }
        });

        if (error) { showError(error.message); btn.disabled = false; btn.textContent = 'Pay'; return; }

        // Confirm on server
        const res = await fetch('/api/payments/stripe/confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ payment_intent_id: paymentIntent.id, invoice_id: invoiceId })
        });
        const result = await res.json();

        if (result.success) {
            showSuccess('Payment successful! Redirecting…');
            setTimeout(() => window.location.href = '/invoices/{{ $invoice->id }}', 1500);
        } else {
            showError(result.error || 'Payment recorded but confirmation failed.');
        }
    } catch (err) {
        showError('Unexpected error: ' + err.message);
        btn.disabled = false; btn.textContent = 'Pay';
    }
});

async function chargeSavedCard(pmId) {
    if (!confirm('Charge {{ number_format($due, 2) }} DH to this card?')) return;
    showError('');

    try {
        const res = await fetch('/api/payments/stripe/charge-saved', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ invoice_id: invoiceId, payment_method_id: pmId })
        });
        const result = await res.json();
        if (result.success) {
            showSuccess('Payment successful! Redirecting…');
            setTimeout(() => window.location.href = '/invoices/{{ $invoice->id }}', 1500);
        } else {
            showError(result.error || 'Charge failed.');
        }
    } catch (err) {
        showError('Unexpected error: ' + err.message);
    }
}

function showError(msg) {
    const el = document.getElementById('stripe-error');
    if (msg) { el.textContent = msg; el.classList.remove('hidden'); }
    else { el.classList.add('hidden'); }
}
function showSuccess(msg) {
    const el = document.getElementById('stripe-success');
    el.textContent = msg; el.classList.remove('hidden');
}
</script>
@endpush
@endif
