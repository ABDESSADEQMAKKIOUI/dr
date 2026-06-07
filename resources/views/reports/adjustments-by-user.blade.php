@extends('layouts.app')
@section('title', 'Adjustments by User')
@php
$pageTitle = 'Stock Adjustments by User';
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.reports'),'url'=>'#'],['label'=>'Adjustments by User','url'=>'']];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg></div>
  <div><p class="pg-hero-title">Stock Adjustments by User</p><p class="pg-hero-sub">Inventory additions and removals performed by each staff member.</p></div>
</div>

<div class="rf">
  <div class="rf-head"><span class="rf-title">{{ __('app.filter') }}</span><a href="{{ route('reports.export-csv',['type'=>'adjustments-by-user','from'=>$from,'to'=>$to]) }}" class="btn-out" style="padding:.5rem 1rem;font-size:.8125rem">{{ __('app.export_csv') }}</a></div>
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div><label class="fi-label">{{ __('app.from') }}</label><input type="date" name="from" value="{{ $from }}" class="fi"></div>
    <div><label class="fi-label">{{ __('app.to') }}</label><input type="date" name="to" value="{{ $to }}" class="fi"></div>
    <button type="submit" class="btn-ac">{{ __('app.filter') }}</button>
  </form>
</div>

<div class="mt-wrap">
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.user') }}</th><th>Adjustments</th><th>Added (+)</th><th>Removed (−)</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td class="font-medium">{{ $row->user }}</td>
        <td><span class="badge-info">{{ $row->count }}</span></td>
        <td class="font-semibold text-green-600">+{{ number_format($row->added) }}</td>
        <td class="font-semibold text-red-500">-{{ number_format($row->removed) }}</td>
      </tr>
      @empty
      <tr><td colspan="4" class="text-center py-10 text-gray-400">No adjustments for selected period.</td></tr>
      @endforelse
      </tbody>
      @if($rows->count())
      <tfoot style="background:#f8fafc">
        <tr>
          <td class="font-bold text-gray-700">{{ __('app.total') }}</td>
          <td class="font-bold">{{ $rows->sum('count') }}</td>
          <td class="font-bold text-green-600">+{{ number_format($rows->sum('added')) }}</td>
          <td class="font-bold text-red-500">-{{ number_format($rows->sum('removed')) }}</td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
</div>
@endsection
