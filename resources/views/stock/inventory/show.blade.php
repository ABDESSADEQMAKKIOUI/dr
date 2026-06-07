@extends('layouts.app')
@section('title', __('app.stock_count') ?? 'Stock Count')

@php
$pageTitle = ($count->reference);
$breadcrumbs = [
    ['label' => __('app.dashboard'),    'url' => route('dashboard')],
    ['label' => __('app.stock'),         'url' => '#'],
    ['label' => __('app.stock_counts') ?? 'Stock Counts', 'url' => route('stock.inventory.counts')],
    ['label' => $count->reference,       'url' => ''],
];

$statusConfig = match($count->status) {
    'draft'     => ['badge' => 'badge-secondary', 'bg' => 'bg-slate-100',   'icon' => 'text-slate-500',   'label' => __('app.draft')],
    'counting'  => ['badge' => 'badge-warning',   'bg' => 'bg-amber-100',   'icon' => 'text-amber-600',   'label' => __('app.counting') ?? 'Counting'],
    'completed' => ['badge' => 'badge-info',      'bg' => 'bg-sky-100',     'icon' => 'text-sky-600',     'label' => __('app.completed')],
    'adjusted'  => ['badge' => 'badge-success',   'bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600', 'label' => __('app.adjusted') ?? 'Adjusted'],
    default     => ['badge' => 'badge-secondary', 'bg' => 'bg-slate-100',   'icon' => 'text-slate-500',   'label' => ucfirst($count->status)],
};

$totalItems       = $discrepancies->count();
$countedItems     = $discrepancies->filter(fn($d) => $d['counted'] !== null)->count();
$discrepantItems  = $discrepancies->filter(fn($d) => $d['discrepancy'] !== null && $d['discrepancy'] != 0)->count();
$surplus          = $discrepancies->filter(fn($d) => ($d['discrepancy'] ?? 0) > 0)->sum(fn($d) => $d['discrepancy']);
$shortage         = $discrepancies->filter(fn($d) => ($d['discrepancy'] ?? 0) < 0)->sum(fn($d) => $d['discrepancy']);
@endphp

@section('content')

{{-- ── Top action bar ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('stock.inventory.counts') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        @if(in_array($count->status, ['draft', 'counting']))
        <a href="{{ route('stock.inventory.count-entry', $count) }}" class="btn btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.enter_counts') ?? 'Enter Counts' }}
        </a>
        @endif
        @if($count->status === 'counting')
        <form method="POST" action="{{ route('stock.inventory.count-complete', $count) }}">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm"
                    onclick="return confirm('{{ __('app.confirm_complete') ?? 'Mark this count as completed?' }}')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('app.mark_complete') ?? 'Mark Complete' }}
            </button>
        </form>
        @endif
        @if($count->status === 'completed')
        <form method="POST" action="{{ route('stock.inventory.count-approve', $count) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('{{ __('app.confirm_approve') ?? 'Approve and adjust stock? This will modify inventory to match your counts.' }}')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('app.approve_adjust') ?? 'Approve & Adjust Stock' }}
            </button>
        </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: info card ─────────────────────────────────────────── --}}
    <div class="space-y-5">

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $statusConfig['bg'] }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $statusConfig['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.count_info') ?? 'Count Info' }}</h3>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.reference') }}</span>
                    <span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        {{ $count->reference }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                <div class="flex items-start justify-between px-5 py-3 gap-3">
                    <span class="text-sm text-slate-500 flex-shrink-0">{{ __('app.warehouse') }}</span>
                    <span class="text-sm font-semibold text-slate-700 text-right">{{ $count->warehouse->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.date') }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ \Carbon\Carbon::parse($count->date)->format('d M Y') }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.created_by') }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $count->user->full_name ?? '—' }}</span>
                </div>
            </div>
        </div>

        @if($count->notes)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.notes') }}</h3>
            </div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $count->notes }}</p>
            </div>
        </div>
        @endif

        {{-- Summary stats --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.summary') ?? 'Summary' }}</h3>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.total_products') ?? 'Total Products' }}</span>
                    <span class="text-sm font-bold text-slate-800">{{ $totalItems }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.counted') ?? 'Counted' }}</span>
                    <span class="text-sm font-bold text-indigo-600">{{ $countedItems }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.discrepancies') ?? 'Discrepancies' }}</span>
                    <span class="text-sm font-bold {{ $discrepantItems > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $discrepantItems }}</span>
                </div>
                @if($surplus > 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.surplus') ?? 'Surplus' }}</span>
                    <span class="text-sm font-bold text-emerald-600">+{{ $surplus }}</span>
                </div>
                @endif
                @if($shortage < 0)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.shortage') ?? 'Shortage' }}</span>
                    <span class="text-sm font-bold text-rose-600">{{ $shortage }}</span>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── RIGHT: discrepancy table ─────────────────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.count_report') ?? 'Count Report' }}</h3>
                </div>
                @if($totalItems)
                <span class="badge badge-secondary">{{ $totalItems }} {{ __('app.items') }}</span>
                @endif
            </div>

            @if($discrepancies->count())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>{{ __('app.product') }}</th>
                            <th>{{ __('app.sku') }}</th>
                            <th class="text-right w-28">{{ __('app.system_stock') ?? 'System Stock' }}</th>
                            <th class="text-right w-28">{{ __('app.counted') ?? 'Counted' }}</th>
                            <th class="text-right w-28">{{ __('app.difference') ?? 'Difference' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($discrepancies as $d)
                        @php
                        $diff = $d['discrepancy'];
                        $diffClass = $diff === null ? 'text-slate-400'
                            : ($diff > 0 ? 'text-emerald-600' : ($diff < 0 ? 'text-rose-600' : 'text-slate-400'));
                        @endphp
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $loop->iteration }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-xs font-bold">
                                            {{ strtoupper(substr($d['product']->name ?? 'P', 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-800">{{ $d['product']->name ?? '—' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                    {{ $d['product']->sku ?? '—' }}
                                </span>
                            </td>
                            <td class="text-right text-sm font-semibold text-slate-700">{{ $d['expected'] }}</td>
                            <td class="text-right text-sm font-semibold text-slate-700">
                                {{ $d['counted'] ?? '—' }}
                            </td>
                            <td class="text-right text-sm font-bold {{ $diffClass }}">
                                @if($diff === null)
                                    —
                                @elseif($diff > 0)
                                    +{{ $diff }}
                                @else
                                    {{ $diff }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($surplus != 0 || $shortage != 0)
                    <tfoot>
                        @if($surplus > 0)
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-sm font-semibold text-slate-600 text-right border-t border-slate-100">
                                {{ __('app.total_surplus') ?? 'Total Surplus' }}
                            </td>
                            <td class="px-4 py-2 text-right border-t border-slate-100">
                                <span class="text-sm font-bold text-emerald-600">+{{ $surplus }}</span>
                            </td>
                        </tr>
                        @endif
                        @if($shortage < 0)
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-sm font-semibold text-slate-600 text-right border-t border-slate-100">
                                {{ __('app.total_shortage') ?? 'Total Shortage' }}
                            </td>
                            <td class="px-4 py-2 text-right border-t border-slate-100">
                                <span class="text-sm font-bold text-rose-600">{{ $shortage }}</span>
                            </td>
                        </tr>
                        @endif
                    </tfoot>
                    @endif
                </table>
            </div>
            @else
            <div class="card-body">
                <div class="empty-state py-10">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="empty-state-title">{{ __('app.no_counts_entered') ?? 'No counts entered yet' }}</p>
                    @if(in_array($count->status, ['draft','counting']))
                    <a href="{{ route('stock.inventory.count-entry', $count) }}" class="btn btn-primary btn-sm mt-4">
                        {{ __('app.enter_counts') ?? 'Enter Counts' }}
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
