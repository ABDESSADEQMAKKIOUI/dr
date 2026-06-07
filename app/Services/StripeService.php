<?php

namespace App\Services;

use App\Models\CardPayment;
use App\Models\Customer;
use Stripe\StripeClient;

class StripeService
{
    private ?StripeClient $stripe = null;

    public function __construct()
    {
        $key = config('services.stripe.secret');
        if ($key) {
            $this->stripe = new StripeClient($key);
        }
    }

    private function client(): StripeClient
    {
        if (!$this->stripe) {
            throw new \RuntimeException('Stripe is not configured. Set STRIPE_SECRET in your .env file.');
        }
        return $this->stripe;
    }

    /**
     * Get or create a Stripe Customer for a local Customer.
     */
    public function getOrCreateStripeCustomer(Customer $customer): string
    {
        if ($customer->stripe_customer_id) {
            return $customer->stripe_customer_id;
        }

        $stripeCustomer = $this->client()->customers->create([
            'name'  => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]);

        $customer->update(['stripe_customer_id' => $stripeCustomer->id]);

        return $stripeCustomer->id;
    }

    /**
     * Create a PaymentIntent for a given amount (in the currency's smallest unit).
     * Amount is in MAD (dirham). Stripe requires amount in centimes.
     */
    public function createPaymentIntent(float $amount, string $currency = 'mad', ?string $stripeCustomerId = null): array
    {
        $params = [
            'amount'   => (int) round($amount * 100),
            'currency' => strtolower($currency),
        ];

        if ($stripeCustomerId) {
            $params['customer'] = $stripeCustomerId;
            $params['setup_future_usage'] = 'off_session'; // allow saving card
        }

        $intent = $this->client()->paymentIntents->create($params);

        return [
            'client_secret'       => $intent->client_secret,
            'payment_intent_id'   => $intent->id,
        ];
    }

    /**
     * Retrieve a PaymentIntent (to confirm its status).
     */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        $intent = $this->client()->paymentIntents->retrieve($paymentIntentId);

        $pm = null;
        if ($intent->payment_method) {
            try {
                $pm = $this->client()->paymentMethods->retrieve($intent->payment_method);
            } catch (\Throwable $e) {}
        }

        return [
            'status'    => $intent->status,
            'amount'    => $intent->amount / 100,
            'card_last4'=> $pm?->card?->last4,
            'card_brand'=> $pm?->card?->brand,
            'pm_id'     => $intent->payment_method,
        ];
    }

    /**
     * List saved payment methods (cards) for a Stripe customer.
     */
    public function listSavedCards(string $stripeCustomerId): array
    {
        $methods = $this->client()->customers->allPaymentMethods($stripeCustomerId, ['type' => 'card']);

        return collect($methods->data)->map(fn($pm) => [
            'id'    => $pm->id,
            'brand' => $pm->card->brand,
            'last4' => $pm->card->last4,
            'exp'   => $pm->card->exp_month . '/' . $pm->card->exp_year,
        ])->all();
    }

    /**
     * Detach (remove) a payment method from a customer.
     */
    public function detachPaymentMethod(string $pmId): void
    {
        $this->client()->paymentMethods->detach($pmId);
    }

    /**
     * Charge an existing saved payment method (off-session).
     */
    public function chargeWithSavedCard(float $amount, string $pmId, string $stripeCustomerId, string $currency = 'mad'): array
    {
        $intent = $this->client()->paymentIntents->create([
            'amount'               => (int) round($amount * 100),
            'currency'             => strtolower($currency),
            'customer'             => $stripeCustomerId,
            'payment_method'       => $pmId,
            'confirm'              => true,
            'off_session'          => true,
        ]);

        $pm = $this->client()->paymentMethods->retrieve($pmId);

        return [
            'status'              => $intent->status,
            'payment_intent_id'   => $intent->id,
            'card_last4'          => $pm->card->last4 ?? null,
            'card_brand'          => $pm->card->brand ?? null,
        ];
    }
}
