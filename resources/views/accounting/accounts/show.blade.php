@extends('layouts.app')
@section('title', $account->name)
@php
$pageTitle = $account->name;
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.accounting'), 'url' => '#'],
    ['label' => __('app.accounts'),   'url' => route('accounting.accounts.index')],
    ['label' => $account->code . ' — ' . $account->name, 'url' => ''],
];

$pcgColors = [
    1 => ['bg' => 'bg-indigo-100',  'text' => 'text-indigo-700',  'border' => 'border-indigo-200',  'icon' => 'bg-indigo-100',  'itext' => 'text-indigo-600',  'label' => 'Capitaux'],
    2 => ['bg' => 'bg-violet-100',  'text' => 'text-violet-700',  'border' => 'border-violet-200',  'icon' => 'bg-violet-100',  'itext' => 'text-violet-600',  'label' => 'Immobilisations'],
    3 => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'icon' => 'bg-amber-100',   'itext' => 'text-amber-600',   'label' => 'Stocks'],
    4 => ['bg' => 'bg-sky-100',     'text' => 'text-sky-700',     'border' => 'border-sky-200',     'icon' => 'bg-sky-100',     'itext' => 'text-sky-600',     'label' => 'Tiers'],
    5 => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'bg-emerald-100', 'itext' => 'text-emerald-600', 'label' => 'Financiers'],
    6 => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'border' => 'border-rose-200',    'icon' => 'bg-rose-100',    'itext' => 'text-rose-600',    'label' => 'Charges'],
    7 => ['bg' => 'bg-teal-100',    'text' => 'text-teal-700',    'border' => 'border-teal-200',    'icon' => 'bg-teal-100',    'itext' => 'text-teal-600',    'label' => 'Produits'],
];
$cls   = $account->pcg_class ? intval($account->pcg_class) : null;
$color = $cls ? ($pcgColors[$cls] ?? $pcgColors[1]) : ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'icon' => 'bg-slate-100', 'itext' => 'text-slate-600', 'label' => '—'];
@endphp

@section('content')

{{-- Top action bar --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('accounting.accounts.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('accounting.accounts.edit', $account->id) }}" class="btn btn-outline btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('app.edit') }}
        </a>
        <form method="POST" action="{{ route('accounting.accounts.destroy', $account->id) }}"
              onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('app.delete') }}
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- LEFT column --}}
    <div class="space-y-5">

        {{-- Account identity card --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $color['icon'] }} flex items-center justify-center font-black text-lg {{ $color['itext'] }}">
                        {{ $cls ?? '?' }}
                    </div>
                    <div>
                        <h3 class="card-title">{{ $account->name }}</h3>
                        @if($cls)
                        <span class="text-xs {{ $color['text'] }} {{ $color['bg'] }} px-2 py-0.5 rounded-full font-medium">
                            Classe {{ $cls }} — {{ $color['label'] }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.product_code') ?? 'Code' }}</span>
                    <span class="mono text-sm font-bold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-lg">{{ $account->code }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.normal_balance') ?? 'Sens normal' }}</span>
                    @if($account->normal_balance === 'debit')
                        <span class="badge badge-info">Débit</span>
                    @elseif($account->normal_balance === 'credit')
                        <span class="badge badge-warning">Crédit</span>
                    @else
                        <span class="text-slate-400 text-xs">—</span>
                    @endif
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.type') }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ ucfirst($account->accountType->name ?? '—') }}</span>
                </div>
                @if($account->parent)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.parent_account') ?? 'Compte parent' }}</span>
                    <a href="{{ route('accounting.accounts.show', $account->parent->id) }}"
                       class="mono text-sm font-semibold text-indigo-600 hover:underline">
                        {{ $account->parent->code }} — {{ $account->parent->name }}
                    </a>
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.status') }}</span>
                    <span class="badge {{ $account->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $account->is_active ? __('app.active') : __('app.inactive') }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.currency') ?? 'Devise' }}</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $account->currency ?? 'MAD' }}</span>
                </div>
            </div>
        </div>

        {{-- Balance summary --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.balance') ?? 'Solde' }}</h3></div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">{{ __('app.opening_balance') ?? 'Solde d\'ouverture' }}</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ number_format($account->opening_balance ?? 0, 2) }} {{ $account->currency ?? 'MAD' }}
                    </span>
                </div>
                @php
                    $debit  = $account->transactions->where('type','debit')->sum('amount');
                    $credit = $account->transactions->where('type','credit')->sum('amount');
                    $balance = ($account->normal_balance === 'credit')
                        ? ($account->opening_balance + $credit - $debit)
                        : ($account->opening_balance + $debit - $credit);
                @endphp
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">Mouvements débit</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($debit, 2) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-slate-500">Mouvements crédit</span>
                    <span class="text-sm font-semibold text-slate-700">{{ number_format($credit, 2) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.balance') ?? 'Solde actuel' }}</span>
                    <span class="text-base font-bold {{ $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ number_format($balance, 2) }} {{ $account->currency ?? 'MAD' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($account->description)
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('app.description') }}</h3></div>
            <div class="card-body">
                <p class="text-sm text-slate-600 leading-relaxed">{{ $account->description }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- RIGHT column --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Contextual PCG details --}}
        @if($cls)
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $color['icon'] }} flex items-center justify-center">
                        <span class="font-black text-sm {{ $color['itext'] }}">{{ $cls }}</span>
                    </div>
                    <h3 class="card-title">Détails PCG — Classe {{ $cls }} ({{ $color['label'] }})</h3>
                </div>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Class 2: Immobilisations --}}
                @if($cls === 2)
                <div class="detail-item">
                    <span class="detail-label">Date d'acquisition</span>
                    <span class="detail-value">
                        {{ $account->acquisition_date ? \Carbon\Carbon::parse($account->acquisition_date)->format('d M Y') : '—' }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Taux d'amortissement</span>
                    <span class="detail-value">{{ $account->amortization_rate ? $account->amortization_rate . ' %' : '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Durée de vie utile</span>
                    <span class="detail-value">{{ $account->useful_life_years ? $account->useful_life_years . ' ans' : '—' }}</span>
                </div>
                @endif

                {{-- Class 3: Stocks --}}
                @if($cls === 3)
                <div class="detail-item">
                    <span class="detail-label">Méthode de valorisation</span>
                    <span class="detail-value">
                        @switch($account->valuation_method)
                            @case('fifo') FIFO — Premier entré, premier sorti @break
                            @case('lifo') LIFO — Dernier entré, premier sorti @break
                            @case('cmup') CMUP — Coût moyen unitaire pondéré @break
                            @default —
                        @endswitch
                    </span>
                </div>
                @endif

                {{-- Class 4 & 7: TVA + délai paiement --}}
                @if(in_array($cls, [4, 7]))
                <div class="detail-item">
                    <span class="detail-label">Taux TVA</span>
                    <span class="detail-value">{{ $account->vat_rate !== null ? $account->vat_rate . ' %' : '—' }}</span>
                </div>
                @if($cls === 4)
                <div class="detail-item">
                    <span class="detail-label">Délai de paiement</span>
                    <span class="detail-value">{{ $account->payment_terms_days !== null ? $account->payment_terms_days . ' jours' : '—' }}</span>
                </div>
                @endif
                @endif

                {{-- Class 5: Banque --}}
                @if($cls === 5)
                <div class="detail-item">
                    <span class="detail-label">Banque</span>
                    <span class="detail-value">{{ $account->bank_name ?? '—' }}</span>
                </div>
                <div class="detail-item sm:col-span-2">
                    <span class="detail-label">RIB / IBAN</span>
                    <span class="detail-value mono text-xs tracking-widest">{{ $account->iban ?? '—' }}</span>
                </div>
                @endif

                {{-- Class 6: Charges --}}
                @if($cls === 6)
                <div class="detail-item">
                    <span class="detail-label">Budget annuel</span>
                    <span class="detail-value">
                        {{ $account->budget_amount !== null ? number_format($account->budget_amount, 2) . ' ' . ($account->currency ?? 'MAD') : '—' }}
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">TVA déductible</span>
                    <span class="detail-value">
                        @if($account->vat_deductible === null) —
                        @elseif($account->vat_deductible) <span class="badge badge-success">Oui</span>
                        @else <span class="badge badge-secondary">Non</span>
                        @endif
                    </span>
                </div>
                @endif

                {{-- All classes: opening balance + currency already shown in left card --}}

            </div>
        </div>
        @endif

        {{-- Sub-accounts --}}
        @if($account->children->count())
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                        </svg>
                    </div>
                    <h3 class="card-title">Sous-comptes</h3>
                </div>
                <span class="badge badge-secondary">{{ $account->children->count() }}</span>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Sens</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="w-16 text-center">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account->children as $child)
                        <tr>
                            <td><span class="mono text-sm font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">{{ $child->code }}</span></td>
                            <td class="text-sm font-medium text-slate-700">{{ $child->name }}</td>
                            <td>
                                @if($child->normal_balance === 'debit')
                                    <span class="badge badge-info">Débit</span>
                                @elseif($child->normal_balance === 'credit')
                                    <span class="badge badge-warning">Crédit</span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $child->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $child->is_active ? __('app.active') : __('app.inactive') }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('accounting.accounts.show', $child->id) }}"
                                   class="action-btn action-btn-view" title="{{ __('app.view') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Recent transactions --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="card-title">{{ __('app.recent_transactions') ?? 'Dernières écritures' }}</h3>
                </div>
                @if($account->transactions->count())
                <span class="badge badge-secondary">{{ $account->transactions->count() }}</span>
                @endif
            </div>

            @if($account->transactions->count())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('app.description') }}</th>
                            <th class="text-right">Débit</th>
                            <th class="text-right">Crédit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account->transactions as $tx)
                        <tr>
                            <td class="text-sm text-slate-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($tx->date ?? $tx->created_at)->format('d M Y') }}
                            </td>
                            <td class="text-sm text-slate-700">{{ $tx->description ?? $tx->reference ?? '—' }}</td>
                            <td class="text-right text-sm font-semibold {{ $tx->type === 'debit' ? 'text-slate-800' : 'text-slate-300' }}">
                                {{ $tx->type === 'debit' ? number_format($tx->amount, 2) : '' }}
                            </td>
                            <td class="text-right text-sm font-semibold {{ $tx->type === 'credit' ? 'text-slate-800' : 'text-slate-300' }}">
                                {{ $tx->type === 'credit' ? number_format($tx->amount, 2) : '' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body">
                <div class="empty-state py-8">
                    <div class="empty-state-icon">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="empty-state-title">Aucune écriture</p>
                    <p class="empty-state-desc">Ce compte n'a pas encore de mouvements.</p>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
.detail-item { display: flex; flex-direction: column; gap: 2px; }
.detail-label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
.detail-value { font-size: 0.875rem; font-weight: 600; color: #334155; }
</style>
@endpush
