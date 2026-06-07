@extends('layouts.app')
@section('title', __('app.point_of_sale'))
@php $pageTitle = __('app.point_of_sale'); @endphp

@push('styles')
<style>
    /* Make POS fill the viewport below the header */
    .pos-wrap { height: calc(100vh - 64px); overflow: hidden; }
    .pos-products { flex: 1; overflow-y: auto; }
    .pos-cart { width: 360px; min-width: 320px; }
    .cart-items-scroll { flex: 1; overflow-y: auto; }
    /* Category pills active */
    .cat-pill.active { background: #4f46e5; color: #fff; }
    /* Payment buttons */
    .pay-btn { transition: all .15s; }
    .pay-btn.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
    /* Product card hover */
    .prod-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,.15); }
    .prod-card { transition: transform .15s, box-shadow .15s; }
    .product-title { white-space: normal; word-break: break-word; }
    /* Out-of-stock overlay */
    .out-of-stock { opacity: .55; }
</style>
@endpush

@section('content')
<div x-data="posApp()" x-init="init()" class="pos-wrap flex gap-0 -mx-6 -mt-2">

    {{-- ═══════════════════════════════════════════════════════════
         LEFT — Products panel
    ════════════════════════════════════════════════════════════ --}}
    <div class="pos-products flex flex-col bg-slate-50 px-5 py-4 gap-4">

        {{-- Search + tools bar --}}
        <div class="flex items-center gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="product-search"
                       placeholder="{{ __('app.search_by_name_or_barcode') }}"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       autocomplete="off">
            </div>

            {{-- Camera --}}
            <button @click="openCamera()" type="button"
                    class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-500 hover:text-indigo-600 hover:border-indigo-300 shadow-sm transition"
                    title="{{ __('app.camera_scanner') ?? 'Camera' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>

            {{-- Held sales --}}
            <button @click="toggleDraftsPanel()" type="button"
                    class="relative p-2.5 rounded-xl border border-slate-200 bg-white text-slate-500 hover:text-indigo-600 hover:border-indigo-300 shadow-sm transition"
                    title="{{ __('app.held_sales') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span x-show="draftsCount > 0" x-text="draftsCount"
                      class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center"></span>
            </button>
        </div>

        {{-- Category pill filters --}}
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="filterCategory('')"
                    class="cat-pill active text-xs font-semibold px-3 py-1.5 rounded-full border border-indigo-200 bg-indigo-600 text-white transition"
                    data-cat="">
                {{ __('app.all') }}
            </button>
            @foreach($categories ?? [] as $category)
            <button onclick="filterCategory('{{ $category->id }}')"
                    class="cat-pill text-xs font-semibold px-3 py-1.5 rounded-full border border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600 transition"
                    data-cat="{{ $category->id }}">
                {{ $category->name }}
            </button>
            @endforeach
        </div>

        {{-- Products grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3" id="products-grid">
            @forelse($products ?? [] as $product)
            @php
            $productJson = [
                "id"          => $product->id,
                "name"        => $product->name,
                "sku"         => $product->sku,
                "barcode"     => $product->barcode ?? "",
                "price"       => $product->sale_price,
                "cost_price"  => $product->cost_price,
                "stock"       => $product->stock_quantity,
                "category_id" => $product->category_id,
                "image"       => $product->image ? asset("storage/" . $product->image) : null,
            ];
            $outOfStock = $product->stock_quantity <= 0;
            @endphp
            <div class="prod-card {{ $outOfStock ? 'out-of-stock' : '' }} bg-white rounded-2xl border border-slate-100 shadow-sm cursor-pointer overflow-hidden product-item"
                 data-product-id="{{ $product->id }}"
                 data-category-id="{{ $product->category_id }}"
                 data-product='@json($productJson)'>

                {{-- Image / placeholder --}}
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}"
                         alt="{{ $product->name }}"
                         class="w-full h-32 object-cover">
                @else
                    <div class="w-full h-32 bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif

                <div class="p-3">
                    <p class="text-sm font-semibold text-slate-800 product-title leading-tight mb-0.5">{{ $product->name }}</p>
                    <p class="text-[11px] text-slate-400 mb-2">{{ $product->sku }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-indigo-600">{{ number_format($product->sale_price, 2) }} DH</span>
                        <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded-md
                            {{ $outOfStock ? 'bg-rose-50 text-rose-500' : ($product->stock_quantity <= 5 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                            {{ $outOfStock ? __('app.out_of_stock') : $product->stock_quantity }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center text-slate-400 py-16">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                {{ __('app.no_products_available') }}
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         RIGHT — Cart panel
    ════════════════════════════════════════════════════════════ --}}
    <div class="pos-cart flex flex-col bg-white border-l border-slate-100 shadow-xl">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-indigo-600">
            <h2 class="text-white font-bold text-base flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                {{ __('app.cart_items') }}
            </h2>
        </div>

        {{-- Warehouse + Customer --}}
        <div class="px-5 py-3 border-b border-slate-100 space-y-3 bg-slate-50">
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1 block">
                    {{ __('app.warehouse') }} *
                </label>
                <select id="warehouse-select"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach($warehouses ?? [] as $w)
                    <option value="{{ $w->id }}" {{ $w->id == ($defaultWarehouseId ?? '') ? 'selected' : '' }}>
                        {{ $w->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1 block">
                    {{ __('app.customer') }}
                </label>
                <select id="customer-select"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">{{ __('app.walk_in_customer') }}</option>
                    @foreach($customers ?? [] as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Cart items (scrollable) --}}
        <div class="cart-items-scroll px-5 py-3">
            <div id="cart-items">
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="text-sm">{{ __('app.cart_is_empty') }}</p>
                </div>
            </div>
        </div>

        {{-- Totals + Discount + Payment + Actions --}}
        <div class="border-t border-slate-100 px-5 py-4 space-y-4">

            {{-- Totals --}}
            <div class="space-y-2">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>{{ __('app.subtotal') }}</span>
                    <span id="subtotal" class="font-semibold text-slate-700">0.00 DH</span>
                </div>
                <div class="flex justify-between text-sm text-slate-500">
                    <span>{{ __('app.tax') }} ({{ \App\Models\Setting::get('default_tax_rate', 0) }}%)</span>
                    <span id="tax" class="font-semibold text-slate-700">0.00 DH</span>
                </div>
                <div class="flex justify-between items-center text-sm text-slate-500">
                    <span>{{ __('app.discount') }}</span>
                    <input type="number" id="discount" value="0" min="0" step="0.01"
                           class="w-24 px-2 py-1 border border-slate-200 rounded-lg text-right text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                    <span class="font-bold text-slate-800 text-base">{{ __('app.total') }}</span>
                    <span id="total" class="text-xl font-bold text-indigo-600">0.00 DH</span>
                </div>
            </div>

            {{-- Payment Method --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('app.payment_method') }}</p>
                <div class="grid grid-cols-2 gap-2">
                    <button class="pay-btn active text-sm font-semibold py-2 rounded-xl border border-indigo-600 bg-indigo-600 text-white payment-method" data-method="cash">
                        💵 {{ __('app.cash') }}
                    </button>
                    <button class="pay-btn text-sm font-semibold py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-indigo-300 payment-method" data-method="card">
                        💳 {{ __('app.card') }}
                    </button>
                    <button class="pay-btn text-sm font-semibold py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-indigo-300 payment-method" data-method="check">
                        🧾 {{ __('app.cheque') }}
                    </button>
                    <button class="pay-btn text-sm font-semibold py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-indigo-300 payment-method" data-method="credit">
                        📋 {{ __('app.credit') }}
                    </button>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="space-y-2">
                <button id="checkout-btn"
                        class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-indigo-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('app.complete_sale') }}
                </button>
                <div class="grid grid-cols-2 gap-2">
                    <button id="hold-btn"
                            class="py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 flex items-center justify-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/>
                        </svg>
                        {{ __('app.hold') }}
                    </button>
                    <button id="clear-cart-btn"
                            class="py-2.5 rounded-xl border border-rose-100 text-rose-500 text-sm font-semibold hover:bg-rose-50 flex items-center justify-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('app.clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════ DRAFTS SLIDE-IN PANEL ═══════════════ --}}
    <div x-show="showDraftsPanel" x-cloak
         class="fixed inset-0 z-50 flex"
         @click.self="showDraftsPanel = false">
        <div class="ml-auto w-80 bg-white shadow-2xl h-full overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-slate-100 px-5 py-4 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">
                    {{ __('app.held_sales') }} <span class="text-indigo-600" x-text="'(' + draftsCount + ')'"></span>
                </h3>
                <button @click="showDraftsPanel = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="drafts-list" class="p-4 space-y-3">
                <p class="text-slate-400 text-sm text-center py-6">{{ __('app.no_held_sales') }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════ CAMERA MODAL ═══════════════ --}}
    <div x-show="showCameraModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-5 w-80">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-slate-800">{{ __('app.camera_scanner') ?? 'Camera Scanner' }}</h3>
                <button @click="closeCamera()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <video id="camera-video" class="w-full rounded-xl bg-black" autoplay playsinline muted></video>
            <p id="camera-status" class="text-xs text-slate-400 mt-2 text-center">{{ __('app.point_camera_at_barcode') }}</p>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden transition">
        <div id="toast-msg" class="px-5 py-3 rounded-xl shadow-xl text-white text-sm font-semibold"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ==========================================
// Alpine.js component
// ==========================================
function posApp() {
    return {
        showDraftsPanel: false,
        showCameraModal: false,
        draftsCount: {{ $draftsCount ?? 0 }},
        cameraStream: null,
        cameraScanner: null,

        init() { loadDraftsList(); },

        toggleDraftsPanel() {
            this.showDraftsPanel = !this.showDraftsPanel;
            if (this.showDraftsPanel) loadDraftsList();
        },

        openCamera() {
            this.showCameraModal = true;
            this.$nextTick(() => startCameraScanner(this));
        },

        closeCamera() {
            stopCameraScanner(this);
            this.showCameraModal = false;
        }
    };
}

// ==========================================
// Category filter (pill buttons)
// ==========================================
let activeCategoryId = '';
function filterCategory(catId) {
    activeCategoryId = catId;
    document.querySelectorAll('.cat-pill').forEach(btn => {
        const isActive = btn.dataset.cat === catId;
        btn.classList.toggle('active', isActive);
        btn.classList.toggle('bg-indigo-600', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('bg-white', !isActive);
        btn.classList.toggle('text-slate-600', !isActive);
    });
    const term = document.getElementById('product-search').value.toLowerCase();
    filterProducts(term, catId);
}

// ==========================================
// Cart state
// ==========================================
let cart = [];
let selectedPaymentMethod = 'cash';
let barcodeBuffer = '';
let barcodeTimer = null;
const SCAN_TIMEOUT = 80;

function addProductById(productId) {
    const item = Array.from(document.querySelectorAll('.product-item'))
        .find(el => parseInt(el.dataset.productId) === parseInt(productId));
    if (item) { item.click(); return true; }
    return false;
}

function addProductByBarcode(barcode) {
    const domItem = Array.from(document.querySelectorAll('.product-item')).find(el => {
        const p = JSON.parse(el.dataset.product);
        return p.sku === barcode || p.barcode === barcode;
    });
    if (domItem) {
        domItem.click();
        document.getElementById('product-search').value = '';
        return;
    }
    fetch(`/api/products/by-barcode?barcode=${encodeURIComponent(barcode)}`)
        .then(r => r.json())
        .then(data => {
            if (data.product) {
                const p = data.product;
                addToCart({ id: p.id, name: p.name, price: parseFloat(p.sale_price), quantity: 1, stock: p.stock_quantity });
                document.getElementById('product-search').value = '';
                showToast('{{ __('app.product_added') ?? "Product added" }}: ' + p.name, 'green');
            } else {
                showToast('{{ __('app.barcode_not_found') ?? "Not found" }}: ' + barcode, 'red');
            }
        })
        .catch(() => showToast('{{ __('app.barcode_not_found') ?? "Not found" }}: ' + barcode, 'red'));
}

document.querySelectorAll('.product-item').forEach(item => {
    item.addEventListener('click', function () {
        const product = JSON.parse(this.dataset.product);
        addToCart({ id: product.id, name: product.name, price: parseFloat(product.price), quantity: 1, stock: product.stock });
    });
});

function addToCart(product) {
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.quantity < existing.stock) {
            existing.quantity += 1;
        } else {
            showToast('{{ __('app.insufficient_stock') }}', 'red');
            return;
        }
    } else {
        cart.push({ id: product.id, name: product.name, price: product.price, quantity: product.quantity ?? 1, stock: product.stock });
    }
    renderCart();
}

// ==========================================
// Cart rendering
// ==========================================
function renderCart() {
    const cartItems = document.getElementById('cart-items');
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="text-sm">${'{{ __('app.cart_is_empty') }}'}</p>
            </div>`;
    } else {
        cartItems.innerHTML = cart.map((item, index) => `
            <div class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0">
                <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600 font-bold text-xs">
                    ${item.name.substring(0,2).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">${item.name}</p>
                    <p class="text-xs text-slate-400">${item.price.toFixed(2)} DH × ${item.quantity} = <span class="text-indigo-600 font-semibold">${(item.price * item.quantity).toFixed(2)} DH</span></p>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button onclick="decreaseQuantity(${index})"
                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-base leading-none transition">−</button>
                    <span class="w-7 text-center text-sm font-bold text-slate-800">${item.quantity}</span>
                    <button onclick="increaseQuantity(${index})"
                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-base leading-none transition">+</button>
                    <button onclick="removeItem(${index})"
                            class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 flex items-center justify-center text-rose-400 hover:text-rose-600 transition ml-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
    }
    updateTotals();
}

function increaseQuantity(index) {
    if (cart[index].quantity < cart[index].stock) { cart[index].quantity += 1; renderCart(); }
    else showToast('{{ __('app.insufficient_stock') }}', 'red');
}
function decreaseQuantity(index) {
    if (cart[index].quantity > 1) { cart[index].quantity -= 1; renderCart(); }
}
function removeItem(index) { cart.splice(index, 1); renderCart(); }

 function updateTotals() {
     const subtotal = cart.reduce((s, i) => s + i.price * i.quantity, 0);
     const taxRate  = parseFloat('{{ \App\Models\Setting::get("default_tax_rate", 0) }}') || 0;
     const tax      = subtotal * (taxRate / 100);
     const discount = parseFloat(document.getElementById('discount').value) || 0;
     const total    = subtotal + tax - discount;
     document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' DH';
     document.getElementById('tax').textContent      = tax.toFixed(2) + ' DH';
     document.getElementById('total').textContent    = total.toFixed(2) + ' DH';
 }
document.getElementById('discount').addEventListener('input', updateTotals);

// ==========================================
// Payment method
// ==========================================
document.querySelectorAll('.payment-method').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.payment-method').forEach(b => {
            b.classList.remove('active', 'bg-indigo-600', 'text-white', 'border-indigo-600');
            b.classList.add('border-slate-200', 'text-slate-600');
        });
        this.classList.add('active', 'bg-indigo-600', 'text-white', 'border-indigo-600');
        this.classList.remove('border-slate-200', 'text-slate-600');
        selectedPaymentMethod = this.dataset.method;
    });
});

// ==========================================
// Checkout
// ==========================================
document.getElementById('checkout-btn').addEventListener('click', async function () {
    if (cart.length === 0) { showToast('{{ __('app.cart_is_empty') }}', 'red'); return; }
    const warehouseId = document.getElementById('warehouse-select').value;
    if (!warehouseId) { showToast('{{ __('app.please_select_warehouse') }}', 'red'); return; }

    const customerId = document.getElementById('customer-select').value;
    const discount   = parseFloat(document.getElementById('discount').value) || 0;

    const data = {
        warehouse_id:   warehouseId,
        customer_id:    customerId || null,
        items:          cart.map(i => ({ id: i.id, quantity: i.quantity, price: i.price })),
        payment_method: selectedPaymentMethod,
        discount:       discount,
        _token:         '{{ csrf_token() }}'
    };

    this.disabled = true;
    this.innerHTML = '<svg class="w-4 h-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> {{ __('app.processing') }}...';

    try {
        const response = await fetch('{{ route("sales.pos.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            showToast('{{ __('app.sale_completed') }} ✓', 'green');
            cart = [];
            renderCart();
            document.getElementById('discount').value = 0;
            if (result.auto_print) {
                window.open(result.receipt_url, '_blank');
            } else if (confirm('{{ __('app.print_receipt') }}')) {
                window.open(result.receipt_url, '_blank');
            }
        } else {
            showToast('Error: ' + (result.message || 'Unknown error'), 'red');
        }
    } catch (e) {
        showToast('Failed to complete sale.', 'red');
        console.error(e);
    } finally {
        this.disabled = false;
        this.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> {{ __('app.complete_sale') }}`;
    }
});

// ==========================================
// Hold / Clear
// ==========================================
document.getElementById('clear-cart-btn').addEventListener('click', function () {
    if (cart.length === 0 || confirm('{{ __('app.are_you_sure') }}')) {
        cart = []; renderCart();
    }
});

document.getElementById('hold-btn').addEventListener('click', async function () {
    if (cart.length === 0) { showToast('{{ __('app.cart_is_empty') }}', 'red'); return; }
    const warehouseId = document.getElementById('warehouse-select').value;
    const customerId  = document.getElementById('customer-select').value;
    const discount    = parseFloat(document.getElementById('discount').value) || 0;

    try {
        const response = await fetch('{{ route("pos.drafts.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ warehouse_id: warehouseId, customer_id: customerId || null, items: cart, discount })
        });
        const result = await response.json();
        if (result.success) {
            cart = []; renderCart();
            document.getElementById('discount').value = 0;
            const app = document.querySelector('[x-data]').__x.$data;
            app.draftsCount = result.count;
            showToast('{{ __('app.cart_held_success') }}', 'green');
            loadDraftsList();
        }
    } catch (e) { showToast('Failed to hold cart.', 'red'); }
});

// ==========================================
// Drafts
// ==========================================
async function loadDraftsList() {
    const res  = await fetch('{{ route("pos.drafts.index") }}');
    const data = await res.json();
    const list = document.getElementById('drafts-list');

    if (!data.drafts || data.drafts.length === 0) {
        list.innerHTML = `<p class="text-slate-400 text-sm text-center py-6">${'{{ __('app.no_held_sales') }}'}</p>`;
        return;
    }
    list.innerHTML = data.drafts.map(draft => `
        <div class="border border-slate-100 rounded-xl p-4 space-y-3 hover:shadow-sm transition">
            <div class="flex justify-between">
                <span class="font-semibold text-slate-800 text-sm">${draft.customer_name || '{{ __('app.walk_in_customer') }}'}</span>
                <span class="text-xs text-slate-400">${draft.items_count} {{ __('app.items') }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
                <span class="font-bold text-indigo-600">${draft.total} DH</span>
                <span>${draft.created_at}</span>
            </div>
            <div class="flex gap-2">
                <button onclick="resumeDraft(${draft.id})"
                        class="flex-1 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                    {{ __('app.resume') }}
                </button>
                <button onclick="discardDraft(${draft.id})"
                        class="py-1.5 px-3 rounded-lg border border-rose-200 text-rose-500 text-xs font-semibold hover:bg-rose-50 transition">
                    {{ __('app.discard') }}
                </button>
            </div>
        </div>
    `).join('');
}

async function resumeDraft(id) {
    const res   = await fetch('{{ route("pos.drafts.index") }}');
    const data  = await res.json();
    const draft = data.drafts.find(d => d.id === id);
    if (!draft) return;

    cart = draft.items.map(i => ({ id: i.id, name: i.name, price: parseFloat(i.price), quantity: i.quantity, stock: i.stock || 999 }));
    document.getElementById('discount').value = draft.discount || 0;
    if (draft.customer_id) document.getElementById('customer-select').value = draft.customer_id;
    if (draft.warehouse_id) document.getElementById('warehouse-select').value = draft.warehouse_id;

    renderCart();
    await discardDraft(id);
    const app = document.querySelector('[x-data]').__x.$data;
    app.showDraftsPanel = false;
    showToast('{{ __('app.resume') }}!', 'green');
}

async function discardDraft(id) {
    await fetch(`/api/pos/drafts/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    const res  = await fetch('{{ route("pos.drafts.index") }}');
    const data = await res.json();
    const app  = document.querySelector('[x-data]').__x.$data;
    app.draftsCount = data.count;
    loadDraftsList();
}

// ==========================================
// Search & Filter
// ==========================================
document.getElementById('product-search').addEventListener('input', function () {
    filterProducts(this.value.toLowerCase(), activeCategoryId);
});

function filterProducts(term, categoryId) {
    document.querySelectorAll('.product-item').forEach(item => {
        const p = JSON.parse(item.dataset.product);
        const matchSearch = !term || p.name.toLowerCase().includes(term) || p.sku.toLowerCase().includes(term);
        const matchCat    = !categoryId || p.category_id == categoryId;
        item.style.display = (matchSearch && matchCat) ? '' : 'none';
    });
}

// ==========================================
// Hardware barcode scanner
// ==========================================
document.getElementById('product-search').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        const value = this.value.trim();
        if (value) { addProductByBarcode(value); this.value = ''; }
        barcodeBuffer = ''; clearTimeout(barcodeTimer); e.preventDefault(); return;
    }
    if (e.key.length === 1) {
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimer);
        barcodeTimer = setTimeout(() => {
            if (barcodeBuffer.length >= 4) { addProductByBarcode(barcodeBuffer); this.value = ''; }
            barcodeBuffer = '';
        }, SCAN_TIMEOUT);
    }
});

// ==========================================
// Camera scanner
// ==========================================
async function startCameraScanner(alpineCtx) {
    const videoEl  = document.getElementById('camera-video');
    const statusEl = document.getElementById('camera-status');
    if (typeof ZXing === 'undefined') { statusEl.textContent = 'Scanner library not loaded.'; return; }
    try {
        const codeReader = new ZXing.BrowserMultiFormatReader();
        alpineCtx.cameraScanner = codeReader;
        const devices = await ZXing.BrowserCodeReader.listVideoInputDevices();
        if (!devices || !devices.length) { statusEl.textContent = 'No camera found.'; return; }
        await codeReader.decodeFromVideoDevice(devices[0].deviceId, videoEl, (result) => {
            if (result) {
                addProductByBarcode(result.getText());
                stopCameraScanner(alpineCtx);
                alpineCtx.showCameraModal = false;
            }
        });
    } catch (err) { statusEl.textContent = 'Camera error: ' + err.message; }
}

function stopCameraScanner(alpineCtx) {
    if (alpineCtx.cameraScanner) { alpineCtx.cameraScanner.reset(); alpineCtx.cameraScanner = null; }
    const video = document.getElementById('camera-video');
    if (video && video.srcObject) { video.srcObject.getTracks().forEach(t => t.stop()); video.srcObject = null; }
}

// ==========================================
// Toast
// ==========================================
function showToast(message, color = 'green') {
    const toast = document.getElementById('toast');
    const msg   = document.getElementById('toast-msg');
    msg.textContent = message;
    const colors = { green: 'bg-emerald-500', red: 'bg-rose-500', blue: 'bg-indigo-500' };
    msg.className = `px-5 py-3 rounded-xl shadow-xl text-white text-sm font-semibold ${colors[color] || colors.green}`;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}
</script>
@endpush
