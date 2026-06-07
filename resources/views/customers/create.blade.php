@extends('layouts.app')
@section('title', __('app.add_customer'))
@php
$pageTitle = __('app.add_customer');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.customers'), 'url' => route('customers.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.add_customer') }}</h3></div>
    <form method="POST" action="{{ route('customers.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.name') }} *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.company') }}</label><input type="text" name="company_name" value="{{ old('company_name') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.email') }} *</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required>@error('email')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.phone') }} *</label><input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>@error('phone')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.tax_number') }}</label><input type="text" name="tax_number" value="{{ old('tax_number') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.credit_limit') }}</label><input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', 0) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.address') }}</label><textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea></div>
            <div class="form-group"><label class="form-label">{{ __('app.city') }}</label><input type="text" name="city" value="{{ old('city') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.country') }}</label><input type="text" name="country" value="{{ old('country') }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.notes') }}</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="flex items-center"><input type="checkbox" name="status" value="1" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-700">{{ __('app.active') }}</span></label></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('customers.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.add_customer') }}</button></div>
    </form>
</div>
@push('scripts')
<script>
const DUP_URL = '{{ route("check-duplicate") }}';
let dupT = {};
function checkDup(el, type, hintId) {
    clearTimeout(dupT[type]);
    dupT[type] = setTimeout(async () => {
        const v = el.value.trim();
        if (!v) { document.getElementById(hintId).textContent = ''; return; }
        const d = await (await fetch(`${DUP_URL}?type=${type}&value=${encodeURIComponent(v)}`)).json();
        const h = document.getElementById(hintId);
        h.textContent = d.exists ? '⚠ Already exists' : '';
        h.className   = 'text-xs text-red-500 mt-0.5';
    }, 450);
}
document.addEventListener('DOMContentLoaded', () => {
    const emailEl = document.querySelector('input[name="email"]');
    const phoneEl = document.querySelector('input[name="phone"]');
    if (emailEl) { emailEl.insertAdjacentHTML('afterend', '<span id="cemail-hint"></span>'); emailEl.addEventListener('input', () => checkDup(emailEl, 'email_c', 'cemail-hint')); }
    if (phoneEl) { phoneEl.insertAdjacentHTML('afterend', '<span id="cphone-hint"></span>'); phoneEl.addEventListener('input', () => checkDup(phoneEl, 'phone_c', 'cphone-hint')); }
});
</script>
@endpush
@endsection
