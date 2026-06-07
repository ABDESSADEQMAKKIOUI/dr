<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $sale->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Thermal receipt — 80mm width by default, overridable by paper size setting */
        :root {
            --receipt-width: {{ ($settings['pos_receipt_size'] ?? '80mm') === '58mm' ? '56mm' : '78mm' }};
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            background: #f5f5f5;
            padding: 12px;
        }

        .print-btn {
            background: #1e40af; color: white; padding: 6px 14px;
            border: none; border-radius: 4px; cursor: pointer; margin-bottom: 10px;
            font-size: 12px; display: block;
        }

        .receipt {
            width: var(--receipt-width);
            background: white;
            padding: 5mm 4mm;
            border: 1px solid #ddd;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .company-name { font-size: 12pt; font-weight: bold; margin-bottom: 2px; }
        .company-info { font-size: 8pt; color: #555; margin-bottom: 1px; }

        .divider  { border-top: 1px dashed #999; margin: 4px 0; }
        .divider2 { border-top: 2px solid #333; margin: 4px 0; }

        .meta { font-size: 8pt; margin: 2px 0; }
        .meta span:first-child { color: #555; }

        table { width: 100%; font-size: 8pt; margin: 4px 0; }
        th { border-bottom: 1px solid #999; padding-bottom: 2px; text-align: left; }
        th:last-child { text-align: right; }
        td { padding: 2px 0; vertical-align: top; }
        td:last-child { text-align: right; white-space: nowrap; }
        td.qty { text-align: center; width: 24px; }

        .totals { font-size: 8pt; }
        .totals .row { display: flex; justify-content: space-between; padding: 1px 0; }
        .totals .row.grand { font-size: 11pt; font-weight: bold; border-top: 2px solid #333; padding-top: 3px; margin-top: 2px; }

        .footer { text-align: center; font-size: 8pt; color: #666; margin-top: 6px; }

        @media print {
            body { background: white; padding: 0; }
            .print-btn { display: none; }
            .receipt { border: none; width: 100%; padding: 2mm; }
            @page {
                margin: 2mm;
                size: {{ ($settings['pos_receipt_size'] ?? '80mm') === '58mm' ? '58mm' : '80mm' }} auto;
            }
        }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨 Print</button>

<div class="receipt">
    <!-- Header -->
    <div class="center">
        @if(($settings['pos_show_logo'] ?? 'true') !== 'false' && !empty($settings['company_logo']))
        <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo" style="max-height:30mm; margin-bottom:3px;">
        @endif
        <div class="company-name">{{ $settings['company_name'] ?? config('app.name') }}</div>
        @if(!empty($settings['company_address']))
        <div class="company-info">{{ $settings['company_address'] }}</div>
        @endif
        @if(!empty($settings['company_phone']))
        <div class="company-info">Tel: {{ $settings['company_phone'] }}</div>
        @endif
    </div>

    <div class="divider2"></div>

    <!-- Meta -->
    <div class="meta"><span>Ref: </span><span class="bold">{{ $sale->reference }}</span></div>
    <div class="meta"><span>Date: </span><span>{{ $sale->date instanceof \Carbon\Carbon ? $sale->date->format('d/m/Y H:i') : $sale->date }}</span></div>
    <div class="meta"><span>Customer: </span><span>{{ $sale->customer?->name ?? 'Walk-in' }}</span></div>
    @if(($settings['pos_show_warehouse'] ?? 'true') !== 'false')
    <div class="meta"><span>Warehouse: </span><span>{{ $sale->warehouse?->name ?? '—' }}</span></div>
    @endif

    <div class="divider"></div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="qty">Qty</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? 'Item' }}</td>
                <td class="qty">{{ $item->quantity }}</td>
                <td>{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="color:#777; font-size:7pt;">
                    @{{ number_format($item->price, 2) }} DH each
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Totals -->
    <div class="totals">
        <div class="row"><span>Subtotal</span><span>{{ number_format($sale->items->sum('subtotal'), 2) }} DH</span></div>
        @if($sale->tax_amount > 0)
        <div class="row"><span>Tax</span><span>{{ number_format($sale->tax_amount, 2) }} DH</span></div>
        @endif
        @if($sale->discount_amount > 0)
        <div class="row"><span>Discount</span><span>-{{ number_format($sale->discount_amount, 2) }} DH</span></div>
        @endif
        <div class="row grand"><span>TOTAL</span><span>{{ number_format($sale->total_amount, 2) }} DH</span></div>
        @if($sale->paid_amount > 0)
        <div class="row"><span>Paid</span><span>{{ number_format($sale->paid_amount, 2) }} DH</span></div>
        @endif
        @if($sale->due_amount > 0)
        <div class="row" style="font-weight:bold"><span>Due</span><span>{{ number_format($sale->due_amount, 2) }} DH</span></div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Footer -->
    <div class="footer">
        {{ $settings['invoice_footer'] ?? 'Thank you!' }}
    </div>
</div>
</body>
</html>
