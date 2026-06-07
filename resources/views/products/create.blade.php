@extends('layouts.app')

@section('title', __('app.add_product'))

@php
$pageTitle = __('app.add_product');
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.products'),  'url' => route('products.index')],
    ['label' => __('app.add_product'), 'url' => ''],
];
@endphp

@section('content')
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="product-form">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: main fields ──────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Basic information --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.basic_information') ?? 'Basic Information' }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.product_identity') ?? 'Name, SKU, barcode and classification' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Product Name --}}
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('app.name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="{{ __('app.enter_product_name') ?? 'e.g. iPhone 15 Pro 256GB' }}">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- SKU --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.sku') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="sku" value="{{ old('sku') }}" id="sku-input" required
                               class="form-control @error('sku') is-invalid @enderror"
                               placeholder="{{ __('app.enter_sku') ?? 'e.g. PRD-001' }}">
                        <span id="sku-hint" class="form-hint"></span>
                        @error('sku')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Barcode --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.barcode') }}</label>
                        <div class="flex gap-2">
                            <input type="text" name="barcode" id="barcode-field"
                                   value="{{ old('barcode') }}"
                                   class="form-control flex-1"
                                   placeholder="{{ __('app.barcode_placeholder') ?? '1234567890128' }}">
                            <button type="button" onclick="generateBarcode()"
                                    id="gen-barcode-btn"
                                    class="btn btn-outline btn-sm flex-shrink-0 px-3"
                                    title="{{ __('app.auto_generate') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                                <span class="hidden sm:inline text-sm">{{ __('app.auto_generate') ?? 'Generate' }}</span>
                            </button>
                        </div>
                        <span id="barcode-hint" class="form-hint"></span>
                        @error('barcode')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Category --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.category') }} <span class="text-rose-500">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">{{ __('app.select_category') }}</option>
                            @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Brand --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.brand') }}</label>
                        <select name="brand_id" class="form-control">
                            <option value="">{{ __('app.select_brand') ?? '— No brand —' }}</option>
                            @foreach($brands ?? [] as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unit --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.unit') }} <span class="text-rose-500">*</span></label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">{{ __('app.select_unit') }}</option>
                            @foreach($units ?? [] as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('app.description') }}</label>
                        <textarea name="description" rows="3" class="form-control resize-none"
                                  placeholder="{{ __('app.enter_description') ?? 'Optional product description…' }}">{{ old('description') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.pricing') ?? 'Pricing' }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.cost_and_selling_price') ?? 'Cost price, sale price and tax rate' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                    {{-- Cost Price --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.cost_price') }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" name="cost_price"
                                   value="{{ old('cost_price') }}" required
                                   class="form-control pr-12 @error('cost_price') is-invalid @enderror"
                                   placeholder="0.00" id="cost-price-input">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">DH</span>
                        </div>
                        @error('cost_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Sale Price --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.sale_price') }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" name="sale_price"
                                   value="{{ old('sale_price') }}" required
                                   class="form-control pr-12 @error('sale_price') is-invalid @enderror"
                                   placeholder="0.00" id="sale-price-input">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">DH</span>
                        </div>
                        @error('sale_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Tax Rate --}}
                    <div class="form-group">
                        <label class="form-label">{{ __('app.tax') }} (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="tax_rate"
                                   value="{{ old('tax_rate', 20) }}"
                                   class="form-control pr-8"
                                   placeholder="20">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">%</span>
                        </div>
                    </div>

                </div>

                {{-- Live margin indicator --}}
                <div id="margin-indicator" class="hidden mt-1 flex items-center gap-3 px-4 py-2.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="w-2 h-2 rounded-full bg-emerald-500" id="margin-dot"></div>
                    <span class="text-sm text-slate-600">{{ __('app.margin') ?? 'Margin' }}: <strong id="margin-value" class="text-slate-800">—</strong></span>
                    <span class="text-xs text-slate-400 ml-auto" id="margin-profit"></span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: image + stock ────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Product Image --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.image') }}</h3>
            </div>
            <div class="card-body">
                <div id="drop-zone"
                     class="relative border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center
                            hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-200 cursor-pointer"
                     onclick="document.getElementById('image-input').click()">
                    <div id="image-preview-wrap">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-slate-700">{{ __('app.click_to_upload') ?? 'Click to upload' }}</p>
                        <p class="text-xs text-slate-400 mt-1">PNG, JPG, WebP — max 2 MB</p>
                    </div>
                    <img id="image-preview" src="" alt="Preview"
                         class="hidden w-full h-48 object-contain rounded-xl">
                </div>
                <input type="file" name="image" id="image-input"
                       accept="image/*" class="hidden" onchange="previewImage(event)">
                <p class="form-hint mt-2 text-center">{{ __('app.image_help') ?? 'Recommended: 800×800 px' }}</p>
            </div>
        </div>

        {{-- Stock & Warehouse --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title">{{ __('app.stock') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('app.initial_stock_settings') ?? 'Initial stock and threshold' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-body space-y-4">

                <div class="form-group">
                    <label class="form-label">{{ __('app.stock_quantity') }}</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                           class="form-control" placeholder="0" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.stock_alert') }}</label>
                    <input type="number" name="stock_alert" value="{{ old('stock_alert', 10) }}"
                           class="form-control" placeholder="10" min="0">
                    <p class="form-hint">{{ __('app.stock_alert_help') ?? 'Alert when stock falls below this.' }}</p>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('app.warehouse') }}</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">{{ __('app.global_stock') ?? '— Global stock —' }}</option>
                        @foreach($warehouses ?? [] as $w)
                        <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">{{ __('app.warehouse_stock_help') ?? 'Leave empty for global inventory.' }}</p>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ── Sticky bottom action bar ──────────────────────────────────── --}}
<div class="sticky bottom-4 mt-6 z-10">
    <div class="bg-white/95 backdrop-blur border border-slate-200 rounded-2xl shadow-xl px-6 py-4
                flex items-center justify-between gap-4">
        <p class="text-sm text-slate-400 hidden sm:block">
            {{ __('app.fields_marked_required') ?? 'Fields marked * are required' }}
        </p>
        <div class="flex items-center gap-3 ml-auto">
            <a href="{{ route('products.index') }}" class="btn btn-outline btn-md">
                {{ __('app.cancel') }}
            </a>
            <button type="submit" class="btn btn-primary btn-md" id="submit-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('app.add_product') }}
            </button>
        </div>
    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
/* ── Image preview & drag-drop ─────────────────────────────────── */
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img  = document.getElementById('image-preview');
        const wrap = document.getElementById('image-preview-wrap');
        img.src = e.target.result;
        img.classList.remove('hidden');
        wrap.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

const dropZone = document.getElementById('drop-zone');
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('image-input').files = dt.files;
        previewImage({ target: { files: [file] } });
    }
});

/* ── Barcode generation ─────────────────────────────────────────── */
async function generateBarcode() {
    const btn  = document.getElementById('gen-barcode-btn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>';
    try {
        const res  = await fetch('{{ route("barcodes.generate") }}');
        const data = await res.json();
        document.getElementById('barcode-field').value = data.barcode;
    } finally {
        btn.disabled  = false;
        btn.innerHTML = orig;
    }
}

/* ── Live margin indicator ──────────────────────────────────────── */
function updateMargin() {
    const cost = parseFloat(document.getElementById('cost-price-input').value) || 0;
    const sale = parseFloat(document.getElementById('sale-price-input').value) || 0;
    const wrap = document.getElementById('margin-indicator');

    if (cost > 0 && sale > 0) {
        wrap.classList.remove('hidden');
        const marginPct = ((sale - cost) / sale * 100).toFixed(1);
        const profit    = (sale - cost).toFixed(2);
        const good      = parseFloat(marginPct) >= 20;
        document.getElementById('margin-dot').className =
            'w-2 h-2 rounded-full ' + (good ? 'bg-emerald-500' : 'bg-amber-500');
        const mv = document.getElementById('margin-value');
        mv.textContent = marginPct + '%';
        mv.className   = 'font-semibold ' + (good ? 'text-emerald-600' : 'text-amber-600');
        document.getElementById('margin-profit').textContent = '+' + profit + ' DH';
    } else {
        wrap.classList.add('hidden');
    }
}

document.getElementById('cost-price-input').addEventListener('input', updateMargin);
document.getElementById('sale-price-input').addEventListener('input', updateMargin);

/* ── Real-time duplicate check ──────────────────────────────────── */
const DUP_URL = '{{ route("check-duplicate") }}';
let dupTimers = {};

async function checkDuplicate(inputEl, type, hintEl) {
    clearTimeout(dupTimers[type]);
    dupTimers[type] = setTimeout(async () => {
        const value = inputEl.value.trim();
        if (!value) { hintEl.textContent = ''; hintEl.className = 'form-hint'; return; }
        try {
            const res  = await fetch(`${DUP_URL}?type=${type}&value=${encodeURIComponent(value)}`);
            const data = await res.json();
            hintEl.textContent = data.exists
                ? '{{ __("app.already_exists") ?? "Already exists" }}'
                : '';
            hintEl.className   = data.exists ? 'form-hint text-rose-600' : 'form-hint text-emerald-600';
        } catch {}
    }, 450);
}

document.addEventListener('DOMContentLoaded', () => {
    const skuInput = document.getElementById('sku-input');
    const barInput = document.getElementById('barcode-field');
    if (skuInput) skuInput.addEventListener('input', () => checkDuplicate(skuInput, 'sku', document.getElementById('sku-hint')));
    if (barInput) barInput.addEventListener('input', () => checkDuplicate(barInput, 'barcode', document.getElementById('barcode-hint')));
});

/* ── Submit loading state ───────────────────────────────────────── */
document.getElementById('product-form').addEventListener('submit', function () {
    const btn      = document.getElementById('submit-btn');
    btn.disabled   = true;
    btn.innerHTML  = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> {{ __("app.saving") ?? "Saving…" }}';
});
</script>
@endpush
