<?php

namespace App\Http\Controllers;

use App\Models\CardPayment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Sale;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function __construct(protected StripeService $stripe) {}

    /**
     * Show the card payment page for an invoice.
     * GET /invoices/{invoice}/pay
     */
    public function invoicePayPage(Invoice $invoice): View
    {
        $invoice->load('customer', 'items.product');
        $savedCards = [];

        if ($invoice->customer?->stripe_customer_id) {
            try {
                $savedCards = $this->stripe->listSavedCards($invoice->customer->stripe_customer_id);
            } catch (\Throwable $e) {}
        }

        $stripeKey = config('services.stripe.key');
        return view('payments.stripe-pay', compact('invoice', 'savedCards', 'stripeKey'));
    }

    /**
     * Create a PaymentIntent for an invoice.
     * POST /api/payments/stripe/intent
     */
    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice  = Invoice::with('customer')->findOrFail($validated['invoice_id']);
        $due      = $invoice->total_amount - $invoice->paid_amount;

        if ($due <= 0) {
            return response()->json(['error' => 'Invoice is already paid.'], 400);
        }

        $stripeCustomerId = null;
        if ($invoice->customer) {
            try {
                $stripeCustomerId = $this->stripe->getOrCreateStripeCustomer($invoice->customer);
            } catch (\Throwable $e) {}
        }

        try {
            $result = $this->stripe->createPaymentIntent($due, 'mad', $stripeCustomerId);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirm payment after Stripe.js completes.
     * POST /api/payments/stripe/confirm
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'invoice_id'        => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::with('customer', 'sale')->findOrFail($validated['invoice_id']);

        try {
            $data = $this->stripe->retrievePaymentIntent($validated['payment_intent_id']);

            if ($data['status'] !== 'succeeded') {
                return response()->json(['error' => 'Payment not completed yet.'], 400);
            }

            // Record card payment
            CardPayment::create([
                'payable_type'            => Invoice::class,
                'payable_id'              => $invoice->id,
                'customer_id'             => $invoice->customer_id,
                'stripe_payment_intent_id'=> $validated['payment_intent_id'],
                'stripe_payment_method_id'=> $data['pm_id'],
                'amount'                  => $data['amount'],
                'currency'                => 'mad',
                'status'                  => 'succeeded',
                'card_last4'              => $data['card_last4'],
                'card_brand'              => $data['card_brand'],
            ]);

            // Update invoice paid amount
            $invoice->paid_amount += $data['amount'];
            $invoice->status       = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'sent';
            $invoice->save();

            return response()->json(['success' => true, 'message' => 'Payment recorded successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List saved cards for a customer.
     * GET /api/customers/{customer}/cards
     */
    public function listCards(Customer $customer): JsonResponse
    {
        if (!$customer->stripe_customer_id) {
            return response()->json(['cards' => []]);
        }

        try {
            $cards = $this->stripe->listSavedCards($customer->stripe_customer_id);
            return response()->json(['cards' => $cards]);
        } catch (\Throwable $e) {
            return response()->json(['cards' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a saved card.
     * DELETE /api/customers/{customer}/cards/{pmId}
     */
    public function removeCard(Customer $customer, string $pmId): JsonResponse
    {
        try {
            $this->stripe->detachPaymentMethod($pmId);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Charge a saved card directly (off-session).
     * POST /api/payments/stripe/charge-saved
     */
    public function chargeSavedCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id'        => 'required|exists:invoices,id',
            'payment_method_id' => 'required|string',
        ]);

        $invoice  = Invoice::with('customer')->findOrFail($validated['invoice_id']);
        $due      = $invoice->total_amount - $invoice->paid_amount;

        if ($due <= 0) {
            return response()->json(['error' => 'Invoice is already paid.'], 400);
        }

        try {
            $stripeCustomerId = $this->stripe->getOrCreateStripeCustomer($invoice->customer);
            $data = $this->stripe->chargeWithSavedCard($due, $validated['payment_method_id'], $stripeCustomerId);

            if ($data['status'] !== 'succeeded') {
                return response()->json(['error' => 'Charge failed.'], 400);
            }

            CardPayment::create([
                'payable_type'            => Invoice::class,
                'payable_id'              => $invoice->id,
                'customer_id'             => $invoice->customer_id,
                'stripe_payment_intent_id'=> $data['payment_intent_id'],
                'stripe_payment_method_id'=> $validated['payment_method_id'],
                'amount'                  => $due,
                'currency'                => 'mad',
                'status'                  => 'succeeded',
                'card_last4'              => $data['card_last4'],
                'card_brand'              => $data['card_brand'],
            ]);

            $invoice->paid_amount += $due;
            $invoice->status       = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'sent';
            $invoice->save();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
