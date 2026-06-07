@extends('layouts.app')
@section('title', __('app.email_templates'))
@php
$pageTitle  = __('app.email_templates');
$breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.settings'),'url'=>'#'],['label'=>__('app.email_templates'),'url'=>'']];
$templates = $templates ?? [];
$accent = \App\Models\Setting::where('key','invoice_color')->value('value') ?? '#4F46E5';
@endphp
@section('content')
<div style="--accent:{{ $accent }}">

<div class="pg-hero">
  <div class="pg-hero-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg></div>
  <div><p class="pg-hero-title">{{ __('app.email_templates') }}</p><p class="pg-hero-sub">Customise the content sent to customers for invoices, payments, and more.</p></div>
</div>

@if(session('success'))<div class="flash-ok"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('settings.email-templates.update') }}">
@csrf
<input type="hidden" name="template_type" id="template_type" value="invoice">

<div class="mc">
  <div class="mc-head">
    <div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></div>
    <div><p class="mc-head-title">Select Template</p></div>
  </div>
  <div class="mc-body-lg">
    <select id="template-select" class="fi" style="max-width:320px">
      <option value="invoice">{{ __('app.invoice_email') }}</option>
      <option value="payment_received">{{ __('app.payment_received') }}</option>
      <option value="order_confirmation">{{ __('app.order_confirmation') }}</option>
      <option value="shipping_notification">{{ __('app.shipping_notification') }}</option>
      <option value="welcome">{{ __('app.welcome_email') }}</option>
      <option value="password_reset">{{ __('app.password_reset') }}</option>
    </select>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  {{-- Editor --}}
  <div class="mc">
    <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg></div><div><p class="mc-head-title">{{ __('app.email_content') }}</p></div></div>
    <div class="mc-body-lg space-y-4">
      <div class="fi-group">
        <label class="fi-label">{{ __('app.subject') }}</label>
        <input type="text" name="subject" id="subject" value="{{ old('subject', $templates['invoice']['subject'] ?? 'New Invoice from {company_name}') }}" class="fi">
      </div>
      <div class="fi-group">
        <label class="fi-label">{{ __('app.body') }}</label>
        <textarea name="body" id="body" rows="14" class="fi font-mono text-xs">{{ old('body', $templates['invoice']['body'] ?? '') }}</textarea>
      </div>
      <div class="rounded-xl p-4" style="background:{{ $accent }}0d;border:1px solid {{ $accent }}33">
        <p class="text-xs font-bold mb-2" style="color:{{ $accent }}">{{ __('app.available_variables') }}</p>
        <div class="grid grid-cols-2 gap-1.5 text-xs">
          @foreach(['{company_name}','{customer_name}','{invoice_number}','{amount}','{due_date}','{payment_link}'] as $v)
          <code class="bg-white px-2 py-1 rounded border border-gray-200 text-gray-700">{{ $v }}</code>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Preview --}}
  <div class="mc">
    <div class="mc-head"><div class="mc-icon" style="background:{{ $accent }}1a"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="{{ $accent }}"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></div><div><p class="mc-head-title">{{ __('app.preview') }}</p></div></div>
    <div class="mc-body-lg">
      <div class="rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 text-sm"><strong class="text-gray-500">Subject:</strong> <span id="preview-subject" class="text-gray-800">New Invoice from {company_name}</span></div>
        <div class="p-4 text-sm text-gray-600 leading-relaxed" id="preview-body" style="white-space:pre-wrap;min-height:200px">{{ $templates['invoice']['body'] ?? 'Email body will appear here...' }}</div>
      </div>
    </div>
  </div>
</div>

<div class="flex justify-end gap-3 mt-2">
  <button type="button" onclick="resetTemplate()" class="btn-out">{{ __('app.reset_to_default') }}</button>
  <button type="submit" class="btn-ac">{{ __('app.save_template') }}</button>
</div>
</form>
</div>

@push('scripts')
<script>
const templates=@json($templates);
document.getElementById('template-select').addEventListener('change',function(){
  const type=this.value;
  document.getElementById('template_type').value=type;
  const t=templates[type]||{};
  document.getElementById('subject').value=t.subject||'';
  document.getElementById('body').value=t.body||'';
  updatePreview();
});
document.getElementById('subject').addEventListener('input',updatePreview);
document.getElementById('body').addEventListener('input',updatePreview);
function updatePreview(){
  document.getElementById('preview-subject').textContent=document.getElementById('subject').value;
  document.getElementById('preview-body').textContent=document.getElementById('body').value;
}
function resetTemplate(){if(confirm('{{ __('app.confirm_reset') }}')){location.reload();}}
</script>
@endpush
@endsection
