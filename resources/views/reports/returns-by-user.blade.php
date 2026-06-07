@extends('layouts.app')
@section('title', 'Returns by User')
@php
$pageTitle = 'Returns by User';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Returns by User','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg></div>
  <div><p class="pg-hero-title">Returns by User</p><p class="pg-hero-sub">Sale and purchase returns processed by each staff member.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'returns-by-user','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.user') }}</label><select name="user_id" class="fi" style="width:200px"><option value="">All Users</option>@foreach($users as $u)<option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach</select></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.user') }}</th><th>Sale Returns</th><th>Purchase Returns</th><th>Total Returns Value</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium">{{ $row->user }}</td>
        <td><span class="badge-warn">{{ $row->sale_returns }}</span></td>
        <td><span class="badge-warn">{{ $row->purchase_returns }}</span></td>
        <td class="font-bold text-orange-500">{{ number_format($row->total_value,2) }} DH</td>
      </tr>
      @empty
      <tr><td colspan="4" class="text-center py-10 text-gray-400">No returns for selected period.</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $rows->sum('sale_returns') }}</td>
          <td class="font-bold">{{ $rows->sum('purchase_returns') }}</td>
          <td class="font-bold text-orange-500">{{ number_format($rows->sum('total_value'),2) }} DH</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
