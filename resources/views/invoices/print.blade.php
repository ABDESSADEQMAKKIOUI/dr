<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->reference }}</title>
@php
use App\Models\Setting;
$S = Setting::all()->pluck('value', 'key');

$accentColor   = $S['invoice_color']         ?? '#4F46E5';
$accentLight   = $accentColor . '18';        // ~10% opacity hex approximation
$showLogo      = ($S['show_company_logo']     ?? '1') === '1';
$showTaxNum    = ($S['show_tax_number']       ?? '1') === '1';
$showBank      = ($S['show_bank_details']     ?? '0') === '1';
$showSig       = ($S['show_signature_block']  ?? '0') === '1';
$footer        = $S['invoice_footer']         ?? 'Thank you for your business!';
$terms         = $S['invoice_terms']          ?? '';
$payInstr      = $S['payment_instructions']   ?? '';

$companyName   = $S['company_name']           ?? config('app.name');
$companyEmail  = $S['company_email']          ?? '';
$companyPhone  = $S['company_phone']          ?? '';
$companyAddr   = $S['company_address']        ?? '';
$taxNumber     = $S['tax_number']             ?? '';
$companyLogo   = $S['company_logo']           ?? '';

$subtotal = $invoice->items->sum(fn($i) => ($i->quantity ?? 0) * ($i->price ?? 0));
$balance  = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
$isPaid   = $balance <= 0;
$isPartial= !$isPaid && ($invoice->paid_amount ?? 0) > 0;
@endphp
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #1e293b;
            background: #f1f5f9;
        }
        .page {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
        }

        /* ── Header ── */
        .inv-header {
            background-color: {{ $accentColor }};
            padding: 32px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .inv-header .company-name { font-size: 22px; font-weight: 800; color: #fff; }
        .inv-header .company-sub  { font-size: 11px; color: rgba(255,255,255,.75); margin-top: 4px; }
        .inv-header .logo         { height: 54px; width: auto; margin-bottom: 8px; filter: brightness(0) invert(1); }
        .inv-title  { text-align: right; }
        .inv-title h1 { font-size: 32px; font-weight: 900; color: #fff; letter-spacing: 3px; }
        .inv-title .inv-num { font-size: 13px; color: rgba(255,255,255,.85); margin-top: 4px; font-family: monospace; }

        /* ── Status badge ── */
        .status-row { display: flex; justify-content: flex-end; padding: 10px 40px; background-color: {{ $accentLight }}; border-bottom: 1px solid #e2e8f0; }
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
        .badge-paid    { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef9c3; color: #854d0e; }
        .badge-unpaid  { background: #fee2e2; color: #991b1b; }

        /* ── Parties ── */
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; padding: 32px 40px 24px; border-bottom: 1px solid #e2e8f0; }
        .party-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: {{ $accentColor }}; margin-bottom: 8px; }
        .party-name  { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .party-detail { color: #64748b; font-size: 12px; line-height: 1.8; }

        /* ── Meta row ── */
        .meta-row { display: grid; grid-template-columns: repeat(3,1fr); padding: 16px 40px; background-color: {{ $accentLight }}; border-bottom: 1px solid #e2e8f0; gap: 16px; }
        .meta-item .meta-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: #94a3b8; }
        .meta-item .meta-value { font-size: 13px; font-weight: 700; color: #1e293b; margin-top: 2px; }

        /* ── Items table ── */
        .items-section { padding: 24px 40px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead tr { background-color: {{ $accentColor }}; }
        .items-table thead th { padding: 10px 12px; color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; text-align: left; }
        .items-table thead th.right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .items-table tbody tr:last-child { border-bottom: none; }
        .items-table tbody td { padding: 11px 12px; vertical-align: top; }
        .items-table tfoot tr { background-color: {{ $accentLight }}; }
        .items-table tfoot td { padding: 10px 12px; font-weight: 700; font-size: 14px; }
        .product-name { font-weight: 600; color: #0f172a; }
        .product-sku  { font-size: 11px; color: #94a3b8; margin-top: 2px; font-family: monospace; }
        .text-right { text-align: right; }
        .accent { color: {{ $accentColor }}; }

        /* ── Totals ── */
        .totals-section { display: flex; justify-content: flex-end; padding: 0 40px 24px; }
        .totals-box { width: 280px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .totals-row:last-child { border-bottom: none; }
        .totals-row.grand { padding: 10px 0; font-size: 16px; font-weight: 800; }
        .totals-row.grand .accent { font-size: 18px; }
        .totals-label { color: #64748b; }
        .totals-paid   { color: #16a34a; font-weight: 700; }
        .totals-balance-paid { color: #16a34a; font-weight: 800; }
        .totals-balance-due  { color: #dc2626; font-weight: 800; }

        /* ── Payment instructions ── */
        .payment-section { margin: 0 40px 24px; padding: 16px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; }
        .payment-section .section-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: {{ $accentColor }}; margin-bottom: 6px; }
        .payment-section p { font-size: 12px; color: #475569; line-height: 1.7; white-space: pre-wrap; }

        /* ── Terms ── */
        .terms-section { margin: 0 40px 24px; padding: 16px 20px; border-left: 3px solid {{ $accentColor }}; background: #fafafa; }
        .terms-section .section-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: {{ $accentColor }}; margin-bottom: 6px; }
        .terms-section p { font-size: 11px; color: #64748b; line-height: 1.7; white-space: pre-wrap; }

        /* ── Signature ── */
        .sig-section { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin: 0 40px 32px; padding-top: 16px; }
        .sig-box { border-top: 1px solid #cbd5e1; padding-top: 8px; }
        .sig-box .sig-label { font-size: 11px; color: #94a3b8; }

        /* ── Footer ── */
        .inv-footer { background-color: {{ $accentColor }}; padding: 16px 40px; text-align: center; }
        .inv-footer p { color: rgba(255,255,255,.9); font-size: 12px; }

        /* ── Print ── */
        @media print {
            body { background: #fff; }
            .page { margin: 0; border-radius: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Print / close bar (hidden on print) --}}
<div class="no-print" style="max-width:800px;margin:16px auto;display:flex;gap:8px;justify-content:flex-end">
    <button onclick="window.print()"
            style="background:{{ $accentColor }};color:#fff;border:none;padding:8px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px">
        🖨 Print
    </button>
    <button onclick="window.close()"
            style="background:#e2e8f0;color:#475569;border:none;padding:8px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px">
        ✕ Close
    </button>
</div>

<div class="page">

    {{-- Header --}}
    <div class="inv-header">
        <div>
            @if($showLogo && $companyLogo && file_exists(public_path('storage/' . $companyLogo)))
                <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo" class="logo">
            @endif
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-sub">
                @if($companyAddr){{ $companyAddr }}@endif
                @if($companyPhone) · {{ $companyPhone }}@endif
                @if($companyEmail) · {{ $companyEmail }}@endif
                @if($showTaxNum && $taxNumber) · ICE: {{ $taxNumber }}@endif
            </div>
        </div>
        <div class="inv-title">
            <h1>INVOICE</h1>
            <div class="inv-num">{{ $invoice->reference }}</div>
        </div>
    </div>

    {{-- Status badge --}}
    <div class="status-row">
        @if($isPaid)
            <span class="status-badge badge-paid">✓ Paid</span>
        @elseif($isPartial)
            <span class="status-badge badge-partial">◑ Partial Payment</span>
        @else
            <span class="status-badge badge-unpaid">⚠ Unpaid</span>
        @endif
    </div>

    {{-- Parties --}}
    <div class="parties">
        <div>
            <div class="party-label">From</div>
            <div class="party-name">{{ $companyName }}</div>
            <div class="party-detail">
                {{ $companyAddr }}@if($companyPhone)<br>{{ $companyPhone }}@endif
                @if($companyEmail)<br>{{ $companyEmail }}@endif
                @if($showTaxNum && $taxNumber)<br>ICE: {{ $taxNumber }}@endif
            </div>
        </div>
        <div>
            <div class="party-label">Bill To</div>
            <div class="party-name">{{ $invoice->customer->name ?? '—' }}</div>
            <div class="party-detail">
                {{ $invoice->customer->address ?? '' }}
                @if($invoice->customer->city ?? false)<br>{{ $invoice->customer->city }}@endif
                @if($invoice->customer->phone ?? false)<br>{{ $invoice->customer->phone }}@endif
                @if($invoice->customer->email ?? false)<br>{{ $invoice->customer->email }}@endif
                @if($invoice->customer->tax_number ?? false)<br>ICE: {{ $invoice->customer->tax_number }}@endif
            </div>
        </div>
    </div>

    {{-- Meta row --}}
    <div class="meta-row">
        <div class="meta-item">
            <div class="meta-label">Invoice #</div>
            <div class="meta-value" style="font-family:monospace">{{ $invoice->reference }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Invoice Date</div>
            <div class="meta-value">{{ $invoice->date ? $invoice->date->format('d M Y') : '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Due Date</div>
            <div class="meta-value" style="{{ isset($invoice->due_date) && $invoice->due_date->isPast() && !$isPaid ? 'color:#dc2626' : '' }}">
                {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="items-section">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Description</th>
                    <th style="width:60px" class="right">Qty</th>
                    <th style="width:110px" class="right">Unit Price</th>
                    <th style="width:120px" class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td style="color:#94a3b8;font-size:11px">{{ $loop->iteration }}</td>
                    <td>
                        <div class="product-name">{{ $item->product->name ?? $item->description ?? '—' }}</div>
                        @if($item->product?->sku)
                        <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="text-right" style="color:#475569">{{ $item->quantity }}</td>
                    <td class="text-right" style="color:#475569">{{ number_format($item->price ?? 0, 2) }} DH</td>
                    <td class="text-right" style="font-weight:600;color:#0f172a">{{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 2) }} DH</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right accent" style="font-size:15px">TOTAL</td>
                    <td class="text-right accent" style="font-size:16px">{{ number_format($invoice->total_amount ?? 0, 2) }} DH</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Totals breakdown --}}
    <div class="totals-section">
        <div class="totals-box">
            @if(($invoice->tax_amount ?? 0) > 0)
            <div class="totals-row">
                <span class="totals-label">Subtotal</span>
                <span>{{ number_format($subtotal, 2) }} DH</span>
            </div>
            <div class="totals-row">
                <span class="totals-label">Tax</span>
                <span>{{ number_format($invoice->tax_amount, 2) }} DH</span>
            </div>
            @endif
            @if(($invoice->discount_amount ?? 0) > 0)
            <div class="totals-row">
                <span class="totals-label">Discount</span>
                <span style="color:#dc2626">−{{ number_format($invoice->discount_amount, 2) }} DH</span>
            </div>
            @endif
            <div class="totals-row grand">
                <span>Total</span>
                <span class="accent">{{ number_format($invoice->total_amount ?? 0, 2) }} DH</span>
            </div>
            @if(($invoice->paid_amount ?? 0) > 0)
            <div class="totals-row">
                <span class="totals-label">Paid</span>
                <span class="totals-paid">{{ number_format($invoice->paid_amount, 2) }} DH</span>
            </div>
            <div class="totals-row">
                <span>Balance Due</span>
                <span class="{{ $balance <= 0 ? 'totals-balance-paid' : 'totals-balance-due' }}">
                    {{ number_format(abs($balance), 2) }} DH
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment instructions --}}
    @if($payInstr)
    <div class="payment-section">
        <div class="section-label">Payment Instructions</div>
        <p>{{ $payInstr }}</p>
    </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="payment-section">
        <div class="section-label">Notes</div>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Terms & Conditions --}}
    @if($terms)
    <div class="terms-section">
        <div class="section-label">Terms & Conditions</div>
        <p>{{ $terms }}</p>
    </div>
    @endif

    {{-- Signature block --}}
    @if($showSig)
    <div class="sig-section">
        <div class="sig-box">
            <div class="sig-label">Authorised Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Client Signature</div>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="inv-footer">
        <p>{{ $footer }}</p>
        @if($companyEmail)
        <p style="margin-top:4px;opacity:.75">{{ $companyEmail }}</p>
        @endif
    </div>

</div>
</body>
</html>
