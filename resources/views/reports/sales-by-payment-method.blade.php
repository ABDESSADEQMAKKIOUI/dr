@extends('layouts.app')
@section('title', __('app.sales_by_payment_method'))
@php
$pageTitle = __('app.sales_by_payment_method');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.sales_by_payment_method'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.sales_by_payment_method') }}</p><p class="pg-hero-sub">Orders and revenue grouped by cash, card, bank transfer, and other methods.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.payment_method') }}</th><th>{{ __('app.orders') }}</th><th>{{ __('app.total') }}</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium capitalize">{{ __('app.'.$row->method) }}</td>
        <td><span class="badge-info">{{ $row->orders }}</span></td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->total,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="3" class="text-center py-10 text-gray-400">{{ __('app.no_data_selected_period') }}</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $rows->sum('orders') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('total'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
