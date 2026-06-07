@extends('layouts.app')
@section('title', __('app.backup_restore'))
@php
$pageTitle  = __('app.backup_restore');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>__('app.backup'),'url'=>'']];
$stats = $stats ?? ['total'=>0,'last_backup'=>'Never','total_size'=>'0 MB'];
$backups = $backups ?? [];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.backup_restore') }}</p><p class="pg-hero-sub">Create, schedule, and restore database backups.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash-err"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>{{ session('error') }}</div>@endif

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_backups') }}</p><p class="kpi-value">{{ $stats['total']??0 }}</p></div><div class="kpi-icon" style="background:#dbeafe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.last_backup') }}</p><p class="kpi-value sm">{{ $stats['last_backup']??__('app.never') }}</p></div><div class="kpi-icon" style="background:#d1fae5"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></div></div>
  <div class="kpi"><div><p class="kpi-label">{{ __('app.total_size') }}</p><p class="kpi-value sm">{{ $stats['total_size']??'0 MB' }}</p></div><div class="kpi-icon" style="background:#ede9fe"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 1 1 0-4h14a2 2 0 1 1 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4"/></svg></div></div>
</div>

{{-- Create backup --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/></svg></div><div><p class="mc-head-title">{{ __('app.create_backup') }}</p><p class="mc-head-sub">Create a new backup snapshot right now.</p></div></div>
  <form method="POST" action="{{ route('settings.backup.create') }}" class="mc-body-lg">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="fi-group">
        <label class="fi-label">{{ __('app.backup_type') }}</label>
        <select name="backup_type" class="fi">
          <option value="full">{{ __('app.full_backup') }}</option>
          <option value="database">{{ __('app.database_only') }}</option>
          <option value="files">{{ __('app.files_only') }}</option>
        </select>
      </div>
      <div class="fi-group">
        <label class="fi-label">{{ __('app.backup_name') }}</label>
        <input type="text" name="backup_name" value="{{ date('Y-m-d_H-i-s') }}" class="fi">
      </div>
    </div>
    <div class="flex justify-end mt-2"><button type="submit" class="btn-ac">{{ __('app.create_backup_now') }}</button></div>
  </form>
</div>

{{-- Schedule --}}
<div class="mc">
  <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></div><div><p class="mc-head-title">{{ __('app.automatic_backup_schedule') }}</p><p class="mc-head-sub">Run backups automatically on a schedule.</p></div></div>
  <form method="POST" action="{{ route('settings.backup.schedule') }}" class="mc-body-lg">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div class="fi-group">
        <label class="fi-label">{{ __('app.backup_frequency') }}</label>
        <select name="backup_frequency" class="fi">
          <option value="daily" {{ ($settings['backup_frequency']??'daily')=='daily'?'selected':'' }}>{{ __('app.daily') }}</option>
          <option value="weekly" {{ ($settings['backup_frequency']??'')=='weekly'?'selected':'' }}>{{ __('app.weekly') }}</option>
          <option value="monthly" {{ ($settings['backup_frequency']??'')=='monthly'?'selected':'' }}>{{ __('app.monthly') }}</option>
        </select>
      </div>
      <div class="fi-group">
        <label class="fi-label">{{ __('app.backup_time') }}</label>
        <input type="time" name="backup_time" value="{{ $settings['backup_time']??'02:00' }}" class="fi">
      </div>
      <div class="fi-group">
        <label class="fi-label">{{ __('app.keep_backups_for') }} (days)</label>
        <input type="number" name="backup_retention" value="{{ $settings['backup_retention']??30 }}" class="fi" min="1">
      </div>
    </div>
    <div class="tr" style="padding:.75rem 0">
      <div><p class="tr-title">{{ __('app.enable_auto_backup') }}</p></div>
      <label class="ts"><input type="checkbox" name="enable_auto_backup" value="1" {{ !empty($settings['enable_auto_backup'])?'checked':'' }}><span class="ts-slider"></span></label>
    </div>
    <div class="flex justify-end mt-2"><button type="submit" class="btn-ac">{{ __('app.save_schedule') }}</button></div>
  </form>
</div>

{{-- History --}}
<div class="mt-wrap">
  <div class="mc-head" style="padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9">
    <div><p class="mc-head-title">{{ __('app.backup_history') }}</p><p class="mc-head-sub">All backup files stored in <code style="font-size:.8em;background:#f1f5f9;padding:0 4px;border-radius:4px">storage/app/backups/</code></p></div>
  </div>
  <div class="overflow-x-auto">
    <table class="mt">
      <thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.size') }}</th><th>{{ __('app.date') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
      <tbody>
      @forelse($files as $b)
      <tr>
        <td class="font-medium font-mono text-sm">{{ $b['name'] }}<span class="text-gray-400">.sql.gz</span></td>
        <td class="text-sm text-gray-500">{{ $b['size'] }}</td>
        <td class="text-sm text-gray-500">{{ $b['created_at'] }}</td>
        <td>
          <div class="flex gap-3 items-center">
            <a href="{{ route('settings.backup.download', ['filename' => $b['filename']]) }}"
               class="text-blue-500 hover:text-blue-700" title="Download">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            </a>
            <form method="POST" action="{{ route('settings.backup.delete') }}" class="inline"
                  onsubmit="return confirm('Delete this backup file?')">
              @csrf
              <input type="hidden" name="filename" value="{{ $b['filename'] }}">
              <button type="submit" class="text-red-400 hover:text-red-600" title="Delete">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="4" class="text-center py-10 text-gray-400">{{ __('app.no_backups_found') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
@endsection
