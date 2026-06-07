<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::with(['customer', 'paymentMethod'])->paginate(15);
        return view('payments.index', compact('payments'));
    }

    public function create(): View
    {
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('payments.create', compact('paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'date' => 'required|date',
        ]);

        Payment::create($validated);
        return redirect()->route('payments.index')->with('success', __('app.created_success'));
    }

    public function show(Payment $payment): View
    {
        return view('payments.show', compact('payment'));
    }
}
