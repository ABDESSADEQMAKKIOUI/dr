@extends('layouts.app')
@section('title', 'Supplier Summary Report')
@php
$pageTitle = 'Supplier Summary Report';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Suppliers','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg></div>
  <div><p class="pg-hero-title">Supplier Summary</p><p class="pg-hero-sub">Purchase history, payments, and outstanding balances for all suppliers.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'suppliers','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>Supplier</th><th>{{ __('app.phone') }}</th><th>{{ __('app.email') }}</th><th>Orders</th><th>Total Purchased</th><th>Total Paid</th><th>Due</th><th></th></tr></thead>
      <tbody>
      @forelse($suppliers as $row)
      <tr>
        <td class="font-medium">{{ $row->name }}</td>
        <td class="text-sm text-gray-500">{{ $row->phone??'—' }}</td>
        <td class="text-sm text-gray-500">{{ $row->email??'—' }}</td>
        <td><span class="badge-info">{{ $row->orders_count }}</span></td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->total_purchased,2) }} DH</td>
        <td class="font-semibold text-green-600">{{ number_format($row->total_paid,2) }} DH</td>
        <td class="font-semibold {{ $row->due>0?'text-red-500':'text-gray-400' }}">{{ number_format($row->due,2) }} DH</td>
        <td><a href="{{ route('reports.supplier-detail',$row->id) }}" class="badge-info" style="text-decoration:none">View</a></td>
      </tr>
      @empty
      <tr><td colspan="8" class="text-center py-10 text-gray-400">No supplier data for this period.</td></tr>
      @endforelse
      </tbody>
      @if($suppliers->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td colspan="3" class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $suppliers->sum('orders_count') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($suppliers->sum('total_purchased'),2) }} DH</td>
          <td class="font-bold text-green-600">{{ number_format($suppliers->sum('total_paid'),2) }} DH</td>
          <td class="font-bold text-red-500">{{ number_format($suppliers->sum('due'),2) }} DH</td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
