@extends('layouts.app')
@section('title', __('app.invoice_settings'))
@php
$pageTitle = __('app.invoice_settings');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.settings'), 'url' => '#'],
    ['label' => __('app.invoice_settings'), 'url' => ''],
];
$s = $settings; // shorthand
@endphp

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('settings.invoice.update') }}" id="invoice-settings-form">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: Form ───────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- 1. Numbering --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.invoice_numbering') ?? 'Invoice Numbering' }}</h3>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="form-group">
                    <label class="form-label">{{ __('app.invoice_prefix') ?? 'Prefix' }}</label>
                    <input type="text" name="invoice_prefix"
                           value="{{ old('invoice_prefix', $s['invoice_prefix'] ?? 'INV-') }}"
                           class="form-control font-mono" placeholder="INV-"
                           oninput="updatePreview()">
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.invoice_prefix_hint') ?? 'Added before the invoice number' }}</p>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.next_invoice_number') ?? 'Next Number' }}</label>
                    <input type="number" name="next_invoice_number" min="1"
                           value="{{ old('next_invoice_number', $s['next_invoice_number'] ?? '1') }}"
                           class="form-control font-mono"
                           oninput="updatePreview()">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.invoice_number_format') ?? 'Format' }}</label>
                    <select name="invoice_number_format" class="form-control" onchange="updatePreview()">
                        <option value="sequential" {{ ($s['invoice_number_format'] ?? 'sequential') === 'sequential' ? 'selected' : '' }}>
                            {{ __('app.sequential') ?? 'Sequential' }} — INV-001
                        </option>
                        <option value="padded" {{ ($s['invoice_number_format'] ?? '') === 'padded' ? 'selected' : '' }}>
                            {{ __('app.padded') ?? 'Padded (6 digits)' }} — INV-000001
                        </option>
                        <option value="year_prefix" {{ ($s['invoice_number_format'] ?? '') === 'year_prefix' ? 'selected' : '' }}>
                            {{ __('app.year_prefix') ?? 'Year prefix' }} — INV-2024-001
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.reset_counter') ?? 'Reset Counter' }}</label>
                    <select name="reset_invoice_counter" class="form-control">
                        <option value="never"   {{ ($s['reset_invoice_counter'] ?? 'never') === 'never'   ? 'selected' : '' }}>{{ __('app.never') ?? 'Never' }}</option>
                        <option value="yearly"  {{ ($s['reset_invoice_counter'] ?? '') === 'yearly'  ? 'selected' : '' }}>{{ __('app.yearly') ?? 'Every year' }}</option>
                        <option value="monthly" {{ ($s['reset_invoice_counter'] ?? '') === 'monthly' ? 'selected' : '' }}>{{ __('app.monthly') ?? 'Every month' }}</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 2. Appearance --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.invoice_appearance') ?? 'Appearance' }}</h3>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label">{{ __('app.invoice_color_theme') ?? 'Accent Colour' }}</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="invoice_color" id="invoice-color"
                                   value="{{ $s['invoice_color'] ?? '#4F46E5' }}"
                                   class="h-10 w-16 rounded-lg border border-slate-200 cursor-pointer p-1"
                                   oninput="updatePreview()">
                            <input type="text" id="invoice-color-text"
                                   value="{{ $s['invoice_color'] ?? '#4F46E5' }}"
                                   class="form-control font-mono w-32"
                                   oninput="document.getElementById('invoice-color').value=this.value;updatePreview()">
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ __('app.invoice_color_hint') ?? 'Used for headers and accents on the invoice' }}</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.invoice_template') ?? 'Template' }}</label>
                        <select name="invoice_template" class="form-control" onchange="updatePreview()">
                            <option value="modern"  {{ ($s['invoice_template'] ?? 'modern') === 'modern'  ? 'selected' : '' }}>{{ __('app.modern') ?? 'Modern' }}</option>
                            <option value="classic" {{ ($s['invoice_template'] ?? '') === 'classic' ? 'selected' : '' }}>{{ __('app.classic') ?? 'Classic' }}</option>
                            <option value="minimal" {{ ($s['invoice_template'] ?? '') === 'minimal' ? 'selected' : '' }}>{{ __('app.minimal') ?? 'Minimal' }}</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('app.show_on_invoice') ?? 'Show on invoice' }}</p>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="show_company_logo" value="1"
                               {{ !empty($s['show_company_logo']) && $s['show_company_logo'] !== '0' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ __('app.show_company_logo') ?? 'Company logo' }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="show_tax_number" value="1"
                               {{ !empty($s['show_tax_number']) && $s['show_tax_number'] !== '0' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ __('app.show_tax_number') ?? 'Tax / ICE number' }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="show_bank_details" value="1"
                               {{ !empty($s['show_bank_details']) && $s['show_bank_details'] !== '0' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ __('app.show_bank_details') ?? 'Bank / payment details' }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="show_signature_block" value="1"
                               {{ !empty($s['show_signature_block']) && $s['show_signature_block'] !== '0' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ __('app.show_signature_block') ?? 'Signature block' }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- 3. Payment terms --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.payment_terms') ?? 'Payment Terms' }}</h3>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="form-group">
                    <label class="form-label">{{ __('app.default_payment_terms_days') ?? 'Default due (days)' }}</label>
                    <div class="relative">
                        <input type="number" name="default_payment_terms" min="0"
                               value="{{ old('default_payment_terms', $s['default_payment_terms'] ?? '30') }}"
                               class="form-control pr-14">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-semibold">days</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.payment_terms_hint') ?? 'Added to invoice date to set the due date' }}</p>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.late_fee') ?? 'Late fee (%)' }}</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" name="late_fee_percentage"
                               value="{{ old('late_fee_percentage', $s['late_fee_percentage'] ?? '0') }}"
                               class="form-control pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-semibold">%</span>
                    </div>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="form-label">{{ __('app.payment_instructions') ?? 'Payment instructions' }}</label>
                    <textarea name="payment_instructions" rows="3" class="form-control"
                              placeholder="{{ __('app.payment_instructions_placeholder') ?? 'Bank account, RIB, or payment link…' }}">{{ old('payment_instructions', $s['payment_instructions'] ?? '') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.payment_instructions_hint') ?? 'Printed below the totals on every invoice' }}</p>
                </div>
            </div>
        </div>

        {{-- 4. Footer & terms --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.footer_and_terms') ?? 'Footer & Terms' }}</h3>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="form-group">
                    <label class="form-label">{{ __('app.invoice_footer_text') ?? 'Footer message' }}</label>
                    <textarea name="invoice_footer" rows="2" class="form-control"
                              placeholder="{{ __('app.invoice_footer_placeholder') ?? 'Thank you for your business!' }}"
                              oninput="updatePreview()">{{ old('invoice_footer', $s['invoice_footer'] ?? 'Thank you for your business!') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('app.invoice_terms') ?? 'Terms & Conditions' }}</label>
                    <textarea name="invoice_terms" rows="4" class="form-control"
                              placeholder="{{ __('app.invoice_terms_placeholder') ?? 'Payment is due within the specified period…' }}">{{ old('invoice_terms', $s['invoice_terms'] ?? '') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">{{ __('app.invoice_terms_hint') ?? 'Shown at the bottom of every invoice' }}</p>
                </div>
                <div class="form-group">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_send_invoice" value="1"
                               {{ !empty($s['auto_send_invoice']) && $s['auto_send_invoice'] !== '0' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>
                            <span class="text-sm font-medium text-slate-700">{{ __('app.auto_send_invoice') ?? 'Auto-send by email' }}</span>
                            <span class="block text-xs text-slate-400">{{ __('app.auto_send_hint') ?? 'Automatically email the invoice when created' }}</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: Live Preview ──────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Preview card --}}
        <div class="sticky top-24">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('app.preview') ?? 'Preview' }}</h3>
                    <span class="text-xs text-slate-400">{{ __('app.live_preview') ?? 'Updates as you type' }}</span>
                </div>
                <div class="p-4">
                    <div id="invoice-preview" class="rounded-lg border border-slate-200 overflow-hidden text-xs">
                        {{-- Header band --}}
                        <div id="preview-header" class="px-4 py-3 flex items-start justify-between" style="background-color: {{ $s['invoice_color'] ?? '#4F46E5' }}">
                            <div>
                                <div class="font-bold text-white text-sm">{{ \App\Models\Setting::where('key','company_name')->value('value') ?? config('app.name') }}</div>
                                <div class="text-white opacity-75 text-xs mt-0.5">{{ \App\Models\Setting::where('key','company_address')->value('value') ?? '' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-white text-base tracking-wide">INVOICE</div>
                                <div id="preview-number" class="text-white opacity-90 text-xs mt-0.5">{{ ($s['invoice_prefix'] ?? 'INV-') }}{{ str_pad($s['next_invoice_number'] ?? 1, 3, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                        {{-- Body --}}
                        <div class="p-4 bg-white space-y-3">
                            <div class="flex justify-between text-slate-500">
                                <div>
                                    <div class="font-semibold text-slate-700 text-xs">Bill To</div>
                                    <div class="text-slate-500">Customer Name</div>
                                </div>
                                <div class="text-right text-xs">
                                    <div>Date: {{ date('d M Y') }}</div>
                                    <div>Due: {{ date('d M Y', strtotime('+' . ($s['default_payment_terms'] ?? 30) . ' days')) }}</div>
                                </div>
                            </div>
                            {{-- Items mini table --}}
                            <table class="w-full">
                                <thead>
                                    <tr id="preview-thead" style="background-color: {{ $s['invoice_color'] ?? '#4F46E5' }}11">
                                        <th class="text-left py-1 px-2 text-slate-600">Item</th>
                                        <th class="text-right py-1 px-2 text-slate-600">Qty</th>
                                        <th class="text-right py-1 px-2 text-slate-600">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-slate-100">
                                        <td class="py-1 px-2 text-slate-700">Sample Product</td>
                                        <td class="py-1 px-2 text-right text-slate-600">2</td>
                                        <td class="py-1 px-2 text-right font-semibold text-slate-800">200.00 DH</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="py-1 px-2 text-right font-bold text-slate-700">Total</td>
                                        <td id="preview-total-color" class="py-1 px-2 text-right font-black" style="color: {{ $s['invoice_color'] ?? '#4F46E5' }}">200.00 DH</td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- Footer --}}
                            <div id="preview-footer" class="pt-2 border-t border-slate-100 text-center text-slate-400 italic">
                                {{ $s['invoice_footer'] ?? 'Thank you for your business!' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current number preview --}}
            <div class="card mt-4">
                <div class="card-header"><h3 class="card-title">{{ __('app.next_invoice_will_be') ?? 'Next invoice number' }}</h3></div>
                <div class="p-5 text-center">
                    <div id="preview-number-large" class="text-2xl font-black text-indigo-700 font-mono tracking-widest">
                        {{ ($s['invoice_prefix'] ?? 'INV-') }}{{ str_pad($s['next_invoice_number'] ?? 1, 3, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('app.save_settings') ?? 'Save Settings' }}
                </button>
            </div>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
const colorInput  = document.getElementById('invoice-color');
const colorText   = document.getElementById('invoice-color-text');

colorInput.addEventListener('input', () => {
    colorText.value = colorInput.value;
    updatePreview();
});

function updatePreview() {
    const prefix  = document.querySelector('[name="invoice_prefix"]').value || 'INV-';
    const next    = parseInt(document.querySelector('[name="next_invoice_number"]').value || 1);
    const format  = document.querySelector('[name="invoice_number_format"]').value;
    const footer  = document.querySelector('[name="invoice_footer"]').value;
    const color   = document.getElementById('invoice-color').value || '#4F46E5';

    let num;
    if (format === 'padded')       num = String(next).padStart(6, '0');
    else if (format === 'year_prefix') num = new Date().getFullYear() + '-' + String(next).padStart(3, '0');
    else                           num = String(next).padStart(3, '0');

    const invoiceNum = prefix + num;

    document.getElementById('preview-number').textContent       = invoiceNum;
    document.getElementById('preview-number-large').textContent = invoiceNum;
    document.getElementById('preview-header').style.backgroundColor = color;
    document.getElementById('preview-thead').style.backgroundColor  = color + '22';
    document.getElementById('preview-total-color').style.color      = color;
    document.getElementById('preview-footer').textContent           = footer || 'Thank you for your business!';
}
</script>
@endpush
