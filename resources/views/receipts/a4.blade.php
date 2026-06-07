<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; background: #f5f5f5; padding: 20px; }

        .receipt-wrapper { max-width: 700px; margin: 0 auto; }

        .print-btn {
            background: #1e40af; color: white; padding: 8px 20px;
            border: none; border-radius: 6px; cursor: pointer; margin-bottom: 16px;
            font-size: 13px;
        }

        .receipt {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 32px;
        }

        .header { text-align: center; margin-bottom: 24px; }
        .header img { max-height: 60px; margin-bottom: 8px; }
        .header h1 { font-size: 20pt; font-weight: bold; color: #1e40af; }
        .header p { font-size: 9pt; color: #555; }

        .divider { border-top: 1px dashed #ccc; margin: 16px 0; }
        .divider-solid { border-top: 2px solid #1e40af; margin: 16px 0; }

        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-bottom: 16px; font-size: 10pt; }
        .meta-grid .label { color: #777; }
        .meta-grid .value { font-weight: 600; text-align: right; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #1e40af; color: white; padding: 8px 10px; text-align: left; font-size: 10pt; }
        thead th:last-child { text-align: right; }
        tbody tr:nth-child(even) { background: #f8faff; }
        tbody td { padding: 6px 10px; font-size: 10pt; border-bottom: 1px solid #f0f0f0; }
        tbody td:last-child { text-align: right; }

        .totals { max-width: 280px; margin-left: auto; }
        .totals .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 10pt; }
        .totals .row.total { font-size: 13pt; font-weight: bold; color: #1e40af; border-top: 2px solid #1e40af; padding-top: 8px; margin-top: 4px; }

        .footer { text-align: center; margin-top: 24px; font-size: 9pt; color: #888; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 9pt; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #16a34a; }
        .badge-partial { background: #fef9c3; color: #ca8a04; }
        .badge-unpaid { background: #fee2e2; color: #dc2626; }

        @media print {
            body { background: white; padding: 0; }
            .print-btn { display: none; }
            .receipt { border: none; border-radius: 0; padding: 10mm; }
            @page { margin: 8mm; size: A4 portrait; }
        }
    </style>
</head>
<body>
<div class="receipt-wrapper">
    <button class="print-btn" onclick="window.print()">🖨 Print Receipt</button>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            @if(($settings['pos_show_logo'] ?? 'true') !== 'false' && !empty($settings['company_logo']))
            <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo">
            @endif
            <h1>{{ $settings['company_name'] ?? config('app.name') }}</h1>
            @if(!empty($settings['company_address']))
            <p>{{ $settings['company_address'] }}</p>
            @endif
            @if(!empty($settings['company_phone']))
            <p>Tel: {{ $settings['company_phone'] }}</p>
            @endif
            @if(!empty($settings['company_email']))
            <p>{{ $settings['company_email'] }}</p>
            @endif
        </div>

        <div class="divider-solid"></div>

        <!-- Sale Info -->
        <div class="meta-grid">
            <span class="label">Receipt #</span>
            <span class="value">{{ $sale->reference }}</span>

            <span class="label">Date</span>
            <span class="value">{{ $sale->date instanceof \Carbon\Carbon ? $sale->date->format('d/m/Y H:i') : $sale->date }}</span>

            <span class="label">Customer</span>
            <span class="value">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</span>

            @if(($settings['pos_show_warehouse'] ?? 'true') !== 'false')
            <span class="label">Warehouse</span>
            <span class="value">{{ $sale->warehouse?->name ?? '—' }}</span>
            @endif

            <span class="label">Cashier</span>
            <span class="value">{{ auth()->user()?->name ?? '—' }}</span>

            <span class="label">Status</span>
            <span class="value">
                <span class="badge badge-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span>
            </span>
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? 'Product' }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:right">{{ number_format($item->price, 2) }} DH</td>
                    <td>{{ number_format($item->subtotal, 2) }} DH</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="row">
                <span>Subtotal</span>
                <span>{{ number_format($sale->items->sum('subtotal'), 2) }} DH</span>
            </div>
            @if($sale->tax_amount > 0)
            <div class="row">
                <span>Tax</span>
                <span>{{ number_format($sale->tax_amount, 2) }} DH</span>
            </div>
            @endif
            @if($sale->discount_amount > 0)
            <div class="row" style="color:#dc2626">
                <span>Discount</span>
                <span>-{{ number_format($sale->discount_amount, 2) }} DH</span>
            </div>
            @endif
            @if($sale->shipping_cost > 0)
            <div class="row">
                <span>Shipping</span>
                <span>{{ number_format($sale->shipping_cost, 2) }} DH</span>
            </div>
            @endif
            <div class="row total">
                <span>TOTAL</span>
                <span>{{ number_format($sale->total_amount, 2) }} DH</span>
            </div>
            @if($sale->paid_amount > 0)
            <div class="row" style="color:#16a34a">
                <span>Paid</span>
                <span>{{ number_format($sale->paid_amount, 2) }} DH</span>
            </div>
            @endif
            @if($sale->due_amount > 0)
            <div class="row" style="color:#dc2626">
                <span>Balance Due</span>
                <span>{{ number_format($sale->due_amount, 2) }} DH</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            @if(!empty($settings['invoice_footer']))
            <p>{{ $settings['invoice_footer'] }}</p>
            @else
            <p>Thank you for your purchase!</p>
            @endif
        </div>
    </div>
</div>
</body>
</html>
