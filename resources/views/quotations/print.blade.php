<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quotation->reference }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; line-height: 1.4; color: #333; margin: 0; padding: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; align-items: flex-start; }
        .logo-section { display: flex; align-items: center; }
        .logo-section img { height: 60px; width: auto; margin-right: 15px; }
        .logo { font-size: 24px; font-weight: bold; color: #333; }
        .invoice-details { text-align: right; }
        .invoice-details h2 { margin: 0 0 5px; color: #555; }
        .invoice-details p { margin: 0; color: #777; }
        .parties { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .from, .to { width: 48%; }
        .section-title { font-weight: bold; text-transform: uppercase; color: #555; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .table th { background-color: #f8f8f8; font-weight: bold; color: #555; }
        .table td.text-right, .table th.text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals-row.grand-total { border-top: 2px solid #333; font-weight: bold; font-size: 16px; margin-top: 10px; padding-top: 10px; }
        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; }
        @media print {
            body { padding: 0; }
            .invoice-container { border: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    @php
        $companyLogo = \App\Models\Setting::where('key', 'company_logo')->first();
        $companyName = \App\Models\Setting::where('key', 'company_name')->first();
        $companyEmail = \App\Models\Setting::where('key', 'company_email')->first();
        $companyPhone = \App\Models\Setting::where('key', 'company_phone')->first();
        $companyAddress = \App\Models\Setting::where('key', 'company_address')->first();
    @endphp
    
    <div class="invoice-container">
        <div class="header">
            <div class="logo-section">
                @if($companyLogo && $companyLogo->value && file_exists(public_path('storage/' . $companyLogo->value)))
                    <img src="{{ public_path('storage/' . $companyLogo->value) }}" alt="Logo">
                @endif
                <div class="logo">{{ $companyName->value ?? config('app.name') }}</div>
            </div>
            <div class="invoice-details">
                <h2>QUOTATION</h2>
                <p>Ref: {{ $quotation->reference }}</p>
                <p>Date: {{ optional($quotation->date)->format('M d, Y') }}</p>
                <p>Valid Until: {{ optional($quotation->valid_until)->format('M d, Y') }}</p>
                <p>Status: {{ strtoupper($quotation->status) }}</p>
            </div>
        </div>

        <div class="parties">
            <div class="from">
                <div class="section-title">From</div>
                <p><strong>{{ $companyName->value ?? config('app.name') }}</strong><br>
                {{ $companyAddress->value ?? '' }}<br>
                @if($companyPhone && $companyPhone->value)
                    Phone: {{ $companyPhone->value }}<br>
                @endif
                @if($companyEmail && $companyEmail->value)
                    Email: {{ $companyEmail->value }}
                @endif
                </p>
            </div>
            <div class="to">
                <div class="section-title">To</div>
                <p><strong>{{ $quotation->customer->name }}</strong><br>
                {{ $quotation->customer->address ?? '' }}<br>
                {{ $quotation->customer->city ?? '' }} {{ $quotation->customer->country ?? '' }}<br>
                {{ $quotation->customer->phone ?? '' }}</p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->product->name }}
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }} DH</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->price, 2) }} DH</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            @if($quotation->tax_amount > 0)
            <div class="totals-row">
                <span>Subtotal</span>
                <span>{{ number_format($quotation->items->sum('subtotal'), 2) }} DH</span>
            </div>
            <div class="totals-row">
                <span>Tax</span>
                <span>{{ number_format($quotation->tax_amount, 2) }} DH</span>
            </div>
            @endif
            @if($quotation->discount_amount > 0)
            <div class="totals-row">
                <span>Discount</span>
                <span>-{{ number_format($quotation->discount_amount, 2) }} DH</span>
            </div>
            @endif
            @if($quotation->shipping_cost > 0)
            <div class="totals-row">
                <span>Shipping</span>
                <span>{{ number_format($quotation->shipping_cost, 2) }} DH</span>
            </div>
            @endif
            <div class="totals-row grand-total">
                <span>Total</span>
                <span>{{ number_format($quotation->total_amount, 2) }} DH</span>
            </div>
        </div>

        <div class="footer">
            @if($quotation->notes)
            <p><strong>Notes:</strong> {{ $quotation->notes }}</p>
            @endif
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
