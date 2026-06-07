@extends('layouts.app')
@section('title', __('app.top_customers'))
@php
$pageTitle  = __('app.top_customers');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>__('app.top_customers'),'url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.top_customers') }}</p><p class="pg-hero-sub">Rank customers by total spend and outstanding balance.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">Filter</span><a href="{{ route('reports.export-csv',['type'=>'top-customers','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.show_top') }}</label><select name="limit" class="fi" style="width:auto">@foreach([10,20,50] as $l)<option value="{{ $l }}" {{ $limit==$l?'selected':'' }}>Top {{ $l }}</option>@endforeach</select></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>#</th><th>{{ __('app.customer') }}</th><th>{{ __('app.phone') }}</th><th>{{ __('app.orders') }}</th><th>{{ __('app.total_spent') }}</th><th>{{ __('app.total_paid') }}</th><th>{{ __('app.due') }}</th></tr></thead>
      <tbody>
      @forelse($rows as $i => $row)
      <tr>
        <td class="text-gray-400 font-semibold">{{ $i+1 }}</td>
        <td class="font-medium">{{ $row->name }}</td>
        <td class="text-sm text-gray-500">{{ $row->phone?:'—' }}</td>
        <td><span class="badge-info">{{ $row->orders }}</span></td>
        <td class="font-bold" style="color:{{ $accent }}">{{ number_format($row->spent,2) }} DH</td>
        <td class="font-semibold text-green-600">{{ number_format($row->paid,2) }} DH</td>
        <td class="font-semibold {{ ($row->spent-$row->paid)>0 ? 'text-red-500' : 'text-gray-400' }}">{{ number_format($row->spent-$row->paid,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="7" class="text-center py-10 text-gray-400">{{ __('app.no_results') }}</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td></td>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td></td>
          <td class="font-bold">{{ $rows->sum('orders') }}</td>
          <td class="font-bold" style="color:{{ $accent }}">{{ number_format($rows->sum('spent'),2) }} DH</td>
          <td class="font-bold text-green-600">{{ number_format($rows->sum('paid'),2) }} DH</td>
          <td class="font-bold text-red-500">{{ number_format($rows->sum('spent')-$rows->sum('paid'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
