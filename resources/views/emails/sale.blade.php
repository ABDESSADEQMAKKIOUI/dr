<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
  .header { background: #16A34A; color: white; padding: 24px; text-align: center; }
  .content { padding: 24px; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  th { background: #F3F4F6; text-align: left; padding: 8px 12px; }
  td { padding: 8px 12px; border-bottom: 1px solid #E5E7EB; }
  .total-row td { font-weight: bold; }
  .footer { background: #F9FAFB; padding: 16px 24px; text-align: center; font-size: 12px; color: #6B7280; }
</style>
</head>
<body>
<div class="header">
  <h1>{{ $company }}</h1>
  <p>Order Confirmation #{{ $sale->reference }}</p>
</div>
<div class="content">
  <p>Dear {{ $sale->customer->name ?? 'Customer' }},</p>
  <p>Thank you for your order! Here is a summary:</p>

  <table>
    <thead>
      <tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
    </thead>
    <tbody>
      @foreach($sale->items as $item)
      <tr>
        <td>{{ $item->product->name ?? 'Item' }}</td>
        <td>{{ $item->quantity }}</td>
        <td>{{ number_format($item->price, 2) }} DH</td>
        <td>{{ number_format($item->subtotal, 2) }} DH</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      @if($sale->discount_amount > 0)
      <tr><td colspan="3">Discount</td><td>-{{ number_format($sale->discount_amount, 2) }} DH</td></tr>
      @endif
      @if($sale->tax_amount > 0)
      <tr><td colspan="3">Tax</td><td>{{ number_format($sale->tax_amount, 2) }} DH</td></tr>
      @endif
      <tr class="total-row"><td colspan="3">TOTAL</td><td>{{ number_format($sale->total_amount, 2) }} DH</td></tr>
    </tfoot>
  </table>

  <p style="margin-top:24px;">Date: <strong>{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</strong></p>
  <p>We will process your order shortly. Thank you!</p>
</div>
<div class="footer">{{ $company }} &mdash; This is an automated email, please do not reply.</div>
</body>
</html>
