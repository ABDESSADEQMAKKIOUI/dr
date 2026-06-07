<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Invoice</title>
<style>
  body{margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;font-size:14px;color:#1e293b}
  .wrap{max-width:600px;margin:32px auto}
  .header{border-radius:12px 12px 0 0;padding:32px 40px;text-align:center}
  .header h1{margin:0;font-size:26px;font-weight:900;color:#fff}
  .header p{margin:6px 0 0;color:rgba(255,255,255,.8);font-size:13px}
  .body{background:#fff;padding:32px 40px}
  .greeting{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px}
  .text{color:#475569;line-height:1.7;margin-bottom:20px}
  .summary-row{display:flex;justify-content:space-between;padding:10px 20px;border-bottom:1px solid #f1f5f9}
  .summary-row:last-child{border-bottom:none}
  .summary-label{color:#64748b;font-size:13px}
  .summary-value{font-weight:700;color:#0f172a;font-size:13px}
  .total-row{display:flex;justify-content:space-between;padding:14px 20px}
  .balance-row{display:flex;justify-content:space-between;padding:14px 20px;border-top:2px solid rgba(255,255,255,.3)}
  .divider{border:none;border-top:1px solid #e2e8f0;margin:24px 0}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
  .badge-due{background:#fef3c7;color:#92400e}
  .badge-paid{background:#d1fae5;color:#065f46}
  .footer{background:#f8fafc;border-radius:0 0 12px 12px;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0}
  .footer p{margin:4px 0;color:#94a3b8;font-size:12px}
</style>
</head>
<body>
@php
use App\Models\Setting;
$S           = Setting::all()->pluck('value','key');
$accent      = $S['invoice_color']   ?? '#4F46E5';
$companyName = $S['company_name']    ?? config('app.name');
$companyEmail= $S['company_email']   ?? '';
$footer      = $S['invoice_footer']  ?? 'Thank you for your business!';
$payInstr    = $S['payment_instructions'] ?? '';
$customer    = $invoice->customer ?? null;
$balance     = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
@endphp

<div class="wrap">
  <div class="header" style="background:{{ $accent }}">
    <h1>{{ $companyName }}</h1>
    <p>Invoice {{ $invoice->reference }}</p>
  </div>

  <div class="body">
    <p class="greeting">Hello{{ $customer?->name ? ', ' . $customer->name : '' }},</p>
    <p class="text">
      Please find your invoice <strong>{{ $invoice->reference }}</strong> details below.
      @if($invoice->due_date) This invoice is due on <strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</strong>.@endif
    </p>

    <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:24px">
      <div style="background:{{ $accent }};padding:12px 20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#fff">Invoice Summary</div>

      <div class="summary-row">
        <span class="summary-label">Invoice #</span>
        <span class="summary-value" style="font-family:monospace">{{ $invoice->reference }}</span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Date</span>
        <span class="summary-value">{{ isset($invoice->date) ? \Carbon\Carbon::parse($invoice->date)->format('d M Y') : now()->format('d M Y') }}</span>
      </div>
      @if($invoice->due_date)
      <div class="summary-row">
        <span class="summary-label">Due Date</span>
        <span class="summary-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</span>
      </div>
      @endif

      @foreach(($invoice->items ?? collect()) as $item)
      <div class="summary-row">
        <span class="summary-label">{{ $item->product->name ?? ($item->description ?? '—') }} × {{ $item->quantity }}</span>
        <span class="summary-value">{{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 2) }} DH</span>
      </div>
      @endforeach

      @if(($invoice->tax_amount ?? 0) > 0)
      <div class="summary-row">
        <span class="summary-label">Tax</span>
        <span class="summary-value">+{{ number_format($invoice->tax_amount, 2) }} DH</span>
      </div>
      @endif
      @if(($invoice->discount_amount ?? 0) > 0)
      <div class="summary-row">
        <span class="summary-label">Discount</span>
        <span class="summary-value" style="color:#16a34a">-{{ number_format($invoice->discount_amount, 2) }} DH</span>
      </div>
      @endif

      <div class="total-row" style="background:{{ $accent }}">
        <span style="font-size:15px;font-weight:800;color:#fff">Total</span>
        <span style="font-size:17px;font-weight:900;color:#fff">{{ number_format($invoice->total_amount ?? 0, 2) }} DH</span>
      </div>

      @if(($invoice->paid_amount ?? 0) > 0)
      <div class="balance-row" style="background:{{ $accent }}">
        <span style="font-size:13px;font-weight:600;color:rgba(255,255,255,.85)">Paid</span>
        <span style="font-size:13px;font-weight:700;color:rgba(255,255,255,.85)">{{ number_format($invoice->paid_amount, 2) }} DH</span>
      </div>
      <div class="balance-row" style="background:{{ $accent }}">
        <span style="font-size:14px;font-weight:800;color:#fff">Balance Due</span>
        <span style="font-size:16px;font-weight:900;color:#fff">{{ number_format($balance, 2) }} DH</span>
      </div>
      @endif
    </div>

    @if($payInstr)
    <div style="background:#f8fafc;border-left:4px solid {{ $accent }};padding:12px 16px;border-radius:0 8px 8px 0;margin-bottom:20px">
      <p style="margin:0;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Payment Instructions</p>
      <p style="margin:0;color:#374151;font-size:13px;line-height:1.6">{{ $payInstr }}</p>
    </div>
    @endif

    <hr class="divider">
    <p class="text" style="font-size:13px;color:#64748b">{{ $footer }}</p>
  </div>

  <div class="footer">
    <p style="color:#475569;font-weight:600">{{ $companyName }}</p>
    @if($companyEmail)<p>{{ $companyEmail }}</p>@endif
    <p style="margin-top:12px">This email was sent automatically. Please do not reply directly.</p>
  </div>
</div>
</body>
</html>
