@extends('layouts.app')
@section('title', __('app.sales_by_user'))
@php
$pageTitle = __('app.sales_by_user');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.sales_by_user'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.sales_by_user') }} / {{ __('app.cashier') }}</p><p class="pg-hero-sub">Orders and revenue attributed to each staff member or cashier.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'sales-by-user','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.user') }}</label><select name="user_id" class="fi" style="width:200px"><option value="">{{ __('app.all_users') }}</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ $userId==$user->id?'selected':'' }}>{{ $user->name }}</option>@endforeach</select></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.user') }}</th><th>{{ __('app.orders') }}</th><th>{{ __('app.revenue') }}</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium">{{ $row->user }}</td>
        <td><span class="badge-info">{{ $row->orders }}</span></td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->revenue,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="3" class="text-center py-10 text-gray-400">{{ __('app.no_results') }}</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $rows->sum('orders') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('revenue'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
