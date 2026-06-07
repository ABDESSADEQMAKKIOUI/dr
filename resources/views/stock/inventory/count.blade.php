@extends('layouts.app')
@section('title', __('app.stock_count') ?? 'Stock Count')

@php
$pageTitle = __('app.physical_stock_count') ?? 'Physical Stock Count';
$breadcrumbs = [
    ['label' => __('app.dashboard'), 'url' => route('dashboard')],
    ['label' => __('app.stock'),      'url' => '#'],
    ['label' => __('app.inventory'),  'url' => route('stock.inventory.index')],
    ['label' => __('app.count') ?? 'Count', 'url' => ''],
];
@endphp

@section('content')
<form method="POST" action="{{ route('stock.inventory.store_count') }}" id="count-form">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: settings ──────────────────────────────────────────── --}}
    <div class="space-y-5">

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.count_settings') ?? 'Count Settings' }}</h3>
                </div>
            </div>
            <div class="card-body space-y-4">

                {{-- Warehouse --}}
                <div class="form-group mb-0">
                    <label class="form-label">{{ __('app.warehouse') }} <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" id="warehouse-select" class="form-control" required>
                        <option value="">{{ __('app.select_warehouse') }}</option>
                        @foreach($warehouses ?? [] as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Count Date --}}
                <div class="form-group mb-0">
                    <label class="form-label">{{ __('app.count_date') ?? 'Count Date' }} <span class="text-rose-500">*</span></label>
                    <input type="date" name="count_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>

                {{-- Notes --}}
                <div class="form-group mb-0">
                    <label class="form-label">{{ __('app.notes') }}</label>
                    <textarea name="notes" rows="3" class="form-control"
                              placeholder="{{ __('app.notes_placeholder') ?? 'Add any notes about this count...' }}"></textarea>
                </div>

            </div>
        </div>

        {{-- Summary card (updated by JS) --}}
        <div class="card" id="summary-card" style="display:none">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.summary') ?? 'Summary' }}</h3>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.total_products') ?? 'Products' }}</span>
                    <span class="text-sm font-bold text-slate-800" id="sum-total">0</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.counted') ?? 'Counted' }}</span>
                    <span class="text-sm font-bold text-indigo-600" id="sum-counted">0</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.discrepancies') ?? 'Discrepancies' }}</span>
                    <span class="text-sm font-bold text-rose-600" id="sum-discrepancies">0</span>
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-col gap-2">
            <button type="submit" id="submit-btn" class="btn btn-primary w-full" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('app.save_stock_count') ?? 'Save Stock Count' }}
            </button>
            <a href="{{ route('stock.inventory.index') }}" class="btn btn-outline w-full">
                {{ __('app.cancel') }}
            </a>
        </div>

    </div>

    {{-- ── RIGHT: products table ───────────────────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="card-title" id="products-title">{{ __('app.products_stock_count') ?? 'Products Stock Count' }}</h3>
                </div>
                <span class="badge badge-secondary" id="products-badge" style="display:none">0 {{ __('app.items') }}</span>
            </div>

            {{-- Loading state --}}
            <div id="loading-state" class="card-body hidden">
                <div class="flex items-center justify-center py-12 gap-3">
                    <svg class="animate-spin w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-sm text-slate-500">{{ __('app.loading') }}</span>
                </div>
            </div>

            {{-- Empty / select warehouse prompt --}}
            <div id="empty-state" class="card-body">
                <div class="empty-state py-12">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="empty-state-title">{{ __('app.select_warehouse_to_start') ?? 'Select a warehouse to start' }}</p>
                    <p class="empty-state-desc">{{ __('app.warehouse_products_will_load') ?? 'Products in this warehouse will load automatically.' }}</p>
                </div>
            </div>

            {{-- No products in warehouse --}}
            <div id="no-products-state" class="card-body hidden">
                <div class="empty-state py-12">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <p class="empty-state-title">{{ __('app.no_products_in_warehouse') ?? 'No products in this warehouse' }}</p>
                    <p class="empty-state-desc">{{ __('app.add_products_first') ?? 'Add stock to this warehouse first.' }}</p>
                </div>
            </div>

            {{-- Products table --}}
            <div id="products-table-wrap" class="table-wrapper hidden">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.product') }}</th>
                            <th>{{ __('app.sku') }}</th>
                            <th class="text-right w-28">{{ __('app.system_stock') ?? 'System Stock' }}</th>
                            <th class="text-right w-36">{{ __('app.physical_count') ?? 'Physical Count' }} *</th>
                            <th class="text-right w-28">{{ __('app.difference') ?? 'Difference' }}</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody"></tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
const apiUrl  = '{{ route('stock.inventory.products_by_warehouse') }}';
const csrfMeta = '{{ csrf_token() }}';

const warehouseSelect  = document.getElementById('warehouse-select');
const loadingState     = document.getElementById('loading-state');
const emptyState       = document.getElementById('empty-state');
const noProductsState  = document.getElementById('no-products-state');
const tableWrap        = document.getElementById('products-table-wrap');
const tbody            = document.getElementById('products-tbody');
const submitBtn        = document.getElementById('submit-btn');
const summaryCard      = document.getElementById('summary-card');
const productsBadge    = document.getElementById('products-badge');

function showState(state) {
    [loadingState, emptyState, noProductsState, tableWrap].forEach(el => el.classList.add('hidden'));
    state.classList.remove('hidden');
}

warehouseSelect.addEventListener('change', async function () {
    const warehouseId = this.value;

    if (!warehouseId) {
        showState(emptyState);
        submitBtn.disabled = true;
        summaryCard.style.display = 'none';
        productsBadge.style.display = 'none';
        return;
    }

    showState(loadingState);

    try {
        const res  = await fetch(`${apiUrl}?warehouse_id=${warehouseId}`);
        const data = await res.json();

        if (!data.length) {
            showState(noProductsState);
            submitBtn.disabled = true;
            summaryCard.style.display = 'none';
            productsBadge.style.display = 'none';
            return;
        }

        tbody.innerHTML = '';
        data.forEach(product => {
            const tr = document.createElement('tr');
            tr.dataset.productId = product.id;
            tr.innerHTML = `
                <td>
                    <div class="font-semibold text-slate-800">${escHtml(product.name)}</div>
                </td>
                <td>
                    <span class="mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">${escHtml(product.sku)}</span>
                </td>
                <td class="text-right">
                    <span class="system-stock font-semibold text-slate-700">${product.system_stock}</span>
                    <span class="text-xs text-slate-400 ml-0.5">${escHtml(product.unit)}</span>
                </td>
                <td class="text-right">
                    <input type="number"
                           name="counts[${product.id}]"
                           class="physical-count form-control text-right w-24 ml-auto"
                           data-system="${product.system_stock}"
                           placeholder="${product.system_stock}"
                           min="0" step="1">
                </td>
                <td class="text-right difference font-semibold text-slate-400">—</td>
            `;
            tbody.appendChild(tr);
        });

        showState(tableWrap);
        submitBtn.disabled = false;
        summaryCard.style.display = '';
        productsBadge.style.display = '';
        productsBadge.textContent = `${data.length} {{ __('app.items') }}`;
        updateSummary();

    } catch (e) {
        showState(emptyState);
        console.error(e);
    }
});

// Live difference + summary update
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('physical-count')) return;
    const row        = e.target.closest('tr');
    const system     = parseInt(e.target.dataset.system) || 0;
    const physical   = e.target.value !== '' ? parseInt(e.target.value) : null;
    const diffCell   = row.querySelector('.difference');

    if (physical === null) {
        diffCell.textContent = '—';
        diffCell.className   = 'text-right difference font-semibold text-slate-400';
    } else {
        const diff = physical - system;
        diffCell.textContent = diff > 0 ? '+' + diff : diff;
        diffCell.className   = 'text-right difference font-semibold ' +
            (diff > 0 ? 'text-emerald-600' : diff < 0 ? 'text-rose-600' : 'text-slate-400');
    }
    updateSummary();
});

function updateSummary() {
    const inputs   = tbody.querySelectorAll('.physical-count');
    const total    = inputs.length;
    const counted  = Array.from(inputs).filter(i => i.value !== '').length;
    const discrepancies = Array.from(inputs).filter(i => {
        if (i.value === '') return false;
        return parseInt(i.value) !== parseInt(i.dataset.system);
    }).length;

    document.getElementById('sum-total').textContent        = total;
    document.getElementById('sum-counted').textContent      = counted;
    document.getElementById('sum-discrepancies').textContent = discrepancies;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
