@extends('layouts.app')
@section('title', __('app.create_account') ?? 'Create Account')
@php
$pageTitle = __('app.create_account') ?? 'Create Account';
$breadcrumbs = [
    ['label' => __('app.dashboard'),  'url' => route('dashboard')],
    ['label' => __('app.accounting'), 'url' => '#'],
    ['label' => __('app.accounts'),   'url' => route('accounting.accounts.index')],
    ['label' => __('app.create'),     'url' => ''],
];

$pcgClasses = [
    '1' => [
        'label'    => __('app.pcg_class_1') ?? 'Classe 1 — Comptes de capitaux',
        'color'    => 'indigo',
        'desc'     => 'Capitaux propres, emprunts, provisions pour risques.',
        'type'     => 'liability',
        'normal'   => 'credit',
        'common'   => ['101 Capital social', '106 Réserves', '110 Report à nouveau', '119 Résultat déficitaire', '150 Provisions pour risques', '164 Emprunts bancaires', '166 Dépôts reçus'],
        'extra'    => ['opening_balance', 'currency'],
    ],
    '2' => [
        'label'    => __('app.pcg_class_2') ?? 'Classe 2 — Immobilisations',
        'color'    => 'violet',
        'desc'     => 'Actifs durables : corporels, incorporels, financiers.',
        'type'     => 'asset',
        'normal'   => 'debit',
        'common'   => ['211 Terrains', '213 Constructions', '215 Matériel industriel', '218 Autres immo. corporelles', '205 Concessions & brevets', '261 Titres de participation', '271 Titres immobilisés'],
        'extra'    => ['opening_balance', 'acquisition_date', 'amortization_rate', 'useful_life_years', 'currency'],
    ],
    '3' => [
        'label'    => __('app.pcg_class_3') ?? 'Classe 3 — Comptes de stocks',
        'color'    => 'amber',
        'desc'     => 'Matières premières, en-cours de production, produits finis.',
        'type'     => 'asset',
        'normal'   => 'debit',
        'common'   => ['310 Matières premières', '320 Autres appro.', '330 En-cours de production', '350 Stocks de produits', '370 Stocks de marchandises'],
        'extra'    => ['opening_balance', 'valuation_method', 'currency'],
    ],
    '4' => [
        'label'    => __('app.pcg_class_4') ?? 'Classe 4 — Comptes de tiers',
        'color'    => 'sky',
        'desc'     => 'Fournisseurs, clients, personnel, État (TVA, IS…).',
        'type'     => 'liability',
        'normal'   => 'credit',
        'common'   => ['401 Fournisseurs', '411 Clients', '421 Personnel — Rémunérations', '431 Sécurité sociale', '441 État — Subventions', '4455 TVA collectée', '4456 TVA déductible', '444 État — IS', '467 Autres comptes débiteurs'],
        'extra'    => ['opening_balance', 'vat_rate', 'payment_terms_days', 'currency'],
    ],
    '5' => [
        'label'    => __('app.pcg_class_5') ?? 'Classe 5 — Comptes financiers',
        'color'    => 'emerald',
        'desc'     => 'Trésorerie : banque, caisse, chèques, valeurs mobilières.',
        'type'     => 'asset',
        'normal'   => 'debit',
        'common'   => ['512 Banque', '514 Chèques postaux', '516 Titres de placement', '530 Caisse', '540 Valeurs mobilières', '580 Virements internes'],
        'extra'    => ['opening_balance', 'bank_name', 'iban', 'currency'],
    ],
    '6' => [
        'label'    => __('app.pcg_class_6') ?? 'Classe 6 — Comptes de charges',
        'color'    => 'rose',
        'desc'     => 'Achats, services extérieurs, salaires, impôts, charges financières.',
        'type'     => 'expense',
        'normal'   => 'debit',
        'common'   => ['601 Achats matières premières', '607 Achats marchandises', '611 Sous-traitance', '613 Locations', '616 Assurances', '621 Personnel ext.', '625 Déplacements', '627 Frais bancaires', '631 Impôts & taxes', '641 Salaires', '645 Charges sociales', '661 Charges d\'intérêts', '671 Charges exceptionnelles'],
        'extra'    => ['opening_balance', 'vat_deductible', 'budget_amount', 'currency'],
    ],
    '7' => [
        'label'    => __('app.pcg_class_7') ?? 'Classe 7 — Comptes de produits',
        'color'    => 'teal',
        'desc'     => 'Ventes, prestations, produits financiers, produits exceptionnels.',
        'type'     => 'revenue',
        'normal'   => 'credit',
        'common'   => ['701 Ventes produits finis', '706 Prestations de services', '707 Ventes marchandises', '708 Produits activités annexes', '709 Remises accordées', '731 Production stockée', '761 Produits financiers', '771 Produits exceptionnels'],
        'extra'    => ['opening_balance', 'vat_rate', 'currency'],
    ],
];
@endphp

@section('content')
<div class="max-w-4xl">

    {{-- PCG class picker --}}
    <div class="card mb-6">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ __('app.select_account_class') ?? 'Sélectionner la classe de compte' }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">Plan Comptable Général (PCG) — Classes 1 à 7</p>
            </div>
        </div>
        <div class="p-5 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2" id="class-picker">
            @foreach($pcgClasses as $cls => $cfg)
            <button type="button"
                    onclick="selectClass('{{ $cls }}')"
                    id="cls-btn-{{ $cls }}"
                    class="cls-btn flex flex-col items-center gap-1 p-3 rounded-xl border-2 border-slate-200 bg-white hover:border-indigo-300 transition text-center cursor-pointer">
                <span class="text-xl font-black text-slate-700">{{ $cls }}</span>
                <span class="text-[10px] text-slate-500 leading-tight">
                    @switch($cls)
                        @case('1') Capitaux @break
                        @case('2') Immo. @break
                        @case('3') Stocks @break
                        @case('4') Tiers @break
                        @case('5') Financiers @break
                        @case('6') Charges @break
                        @case('7') Produits @break
                    @endswitch
                </span>
            </button>
            @endforeach
        </div>

        {{-- Class description banner --}}
        <div id="class-desc" class="hidden mx-5 mb-5 px-4 py-3 rounded-lg border text-sm"></div>
    </div>

    {{-- Main form --}}
    <form method="POST" action="{{ route('accounting.accounts.store') }}" data-validate id="account-form">
        @csrf
        <input type="hidden" name="pcg_class" id="pcg_class_input">
        <input type="hidden" name="type"      id="type_input" value="{{ old('type') }}">

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="form-title">{{ __('app.account_details') ?? 'Détails du compte' }}</h3>
            </div>

            <div class="p-6 space-y-5">

                {{-- Code + Name --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('app.account_code') ?? 'Code' }} *</label>
                        <div class="relative">
                            <span id="code-prefix" class="absolute left-3 top-1/2 -translate-y-1/2 text-indigo-600 font-bold text-sm pointer-events-none hidden"></span>
                            <input type="text" name="code" id="code-input"
                                   value="{{ old('code') }}"
                                   placeholder="ex. 5120"
                                   class="form-control font-mono" required>
                        </div>
                        <p class="text-xs text-slate-400 mt-1" id="code-hint">Le code doit suivre le Plan Comptable Général.</p>
                        @error('code')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.account_name') ?? 'Nom du compte' }} *</label>
                        <input type="text" name="name" id="name-input"
                               value="{{ old('name') }}"
                               class="form-control" required>
                        @error('name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Common accounts suggestions --}}
                <div id="common-accounts" class="hidden">
                    <label class="form-label">{{ __('app.common_accounts') ?? 'Comptes courants' }}</label>
                    <div id="common-accounts-list" class="flex flex-wrap gap-2 mt-1"></div>
                    <p class="text-xs text-slate-400 mt-1">Cliquez pour préremplir — vous pouvez modifier ensuite.</p>
                </div>

                {{-- Parent + Normal side --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('app.parent_account') ?? 'Compte parent' }}</label>
                        <select name="parent_id" class="form-control">
                            <option value="">{{ __('app.none_root') ?? 'Aucun (compte racine)' }}</option>
                            @foreach($parentAccounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('parent_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} — {{ $acc->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.normal_balance') ?? 'Sens normal' }}</label>
                        <select name="normal_balance" id="normal-balance" class="form-control">
                            <option value="debit"  {{ old('normal_balance') == 'debit'  ? 'selected' : '' }}>{{ __('app.debit')  ?? 'Débit' }}</option>
                            <option value="credit" {{ old('normal_balance') == 'credit' ? 'selected' : '' }}>{{ __('app.credit') ?? 'Crédit' }}</option>
                        </select>
                    </div>
                </div>

                {{-- Contextual fields — shown/hidden by class --}}

                {{-- Opening balance (all classes) --}}
                <div class="form-group ctx-field" id="field-opening_balance">
                    <label class="form-label">{{ __('app.opening_balance') ?? 'Solde d\'ouverture' }}</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="opening_balance"
                               value="{{ old('opening_balance', 0) }}" class="form-control pr-12">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">DH</span>
                    </div>
                </div>

                {{-- Class 2: Immobilisations --}}
                <div class="ctx-field hidden" id="field-acquisition_date">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.acquisition_date') ?? 'Date d\'acquisition' }}</label>
                            <input type="date" name="acquisition_date" value="{{ old('acquisition_date') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('app.amortization_rate') ?? 'Taux d\'amort. (%)' }}</label>
                            <input type="number" step="0.01" min="0" max="100" name="amortization_rate"
                                   value="{{ old('amortization_rate') }}" placeholder="ex. 20" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('app.useful_life') ?? 'Durée de vie (ans)' }}</label>
                            <input type="number" min="1" name="useful_life_years"
                                   value="{{ old('useful_life_years') }}" placeholder="ex. 5" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Class 3: Stocks --}}
                <div class="ctx-field hidden" id="field-valuation_method">
                    <div class="form-group">
                        <label class="form-label">{{ __('app.valuation_method') ?? 'Méthode de valorisation' }}</label>
                        <select name="valuation_method" class="form-control">
                            <option value="">{{ __('app.select') ?? 'Sélectionner' }}</option>
                            <option value="fifo"    {{ old('valuation_method') == 'fifo'    ? 'selected' : '' }}>FIFO — Premier entré, premier sorti</option>
                            <option value="lifo"    {{ old('valuation_method') == 'lifo'    ? 'selected' : '' }}>LIFO — Dernier entré, premier sorti</option>
                            <option value="cmup" {{ old('valuation_method') == 'cmup' ? 'selected' : '' }}>CMUP — Coût moyen unitaire pondéré</option>
                        </select>
                    </div>
                </div>

                {{-- Class 4 & 7: VAT rate --}}
                <div class="ctx-field hidden" id="field-vat_rate">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.vat_rate') ?? 'Taux TVA (%)' }}</label>
                            <select name="vat_rate" class="form-control">
                                <option value="">{{ __('app.not_applicable') ?? 'Non applicable' }}</option>
                                <option value="0"  {{ old('vat_rate') == '0'  ? 'selected' : '' }}>0%  — Exonéré</option>
                                <option value="7"  {{ old('vat_rate') == '7'  ? 'selected' : '' }}>7%  — Taux réduit</option>
                                <option value="10" {{ old('vat_rate') == '10' ? 'selected' : '' }}>10% — Taux réduit</option>
                                <option value="14" {{ old('vat_rate') == '14' ? 'selected' : '' }}>14% — Taux intermédiaire</option>
                                <option value="20" {{ old('vat_rate') == '20' ? 'selected' : '' }}>20% — Taux normal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('app.payment_terms') ?? 'Délai de paiement (jours)' }}</label>
                            <input type="number" min="0" name="payment_terms_days"
                                   value="{{ old('payment_terms_days', 30) }}" placeholder="30" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Class 5: Bank --}}
                <div class="ctx-field hidden" id="field-bank_name">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.bank_name') ?? 'Nom de la banque' }}</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                   placeholder="ex. Attijariwafa Bank" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('app.iban') ?? 'RIB / IBAN' }}</label>
                            <input type="text" name="iban" value="{{ old('iban') }}"
                                   placeholder="MA xx xxxx xxxx xxxx xxxx" class="form-control font-mono">
                        </div>
                    </div>
                </div>

                {{-- Class 6: Budget --}}
                <div class="ctx-field hidden" id="field-budget_amount">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('app.annual_budget') ?? 'Budget annuel (DH)' }}</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="budget_amount"
                                       value="{{ old('budget_amount') }}" class="form-control pr-12">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">DH</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('app.vat_deductible') ?? 'TVA déductible' }}</label>
                            <select name="vat_deductible" class="form-control">
                                <option value="1" {{ old('vat_deductible', '1') == '1' ? 'selected' : '' }}>{{ __('app.yes') ?? 'Oui' }}</option>
                                <option value="0" {{ old('vat_deductible') == '0' ? 'selected' : '' }}>{{ __('app.no')  ?? 'Non' }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Currency (all classes) --}}
                <div class="form-group ctx-field" id="field-currency">
                    <label class="form-label">{{ __('app.currency') ?? 'Devise' }}</label>
                    <select name="currency" class="form-control">
                        <option value="MAD" {{ old('currency', 'MAD') == 'MAD' ? 'selected' : '' }}>MAD — Dirham marocain</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD — Dollar américain</option>
                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP — Livre sterling</option>
                    </select>
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label class="form-label">{{ __('app.description') }}</label>
                    <textarea name="description" rows="2" class="form-control"
                              placeholder="{{ __('app.account_description_hint') ?? 'Optionnel — notes sur ce compte' }}">{{ old('description') }}</textarea>
                </div>

                {{-- Active --}}
                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', '1') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded border-slate-300">
                        <span class="text-sm text-slate-700 font-medium">{{ __('app.active_account') ?? 'Compte actif' }}</span>
                    </label>
                </div>

            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('app.create_account') ?? 'Créer le compte' }}</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const pcgClasses = @json($pcgClasses);

const colorMap = {
    indigo:  { bg: 'bg-indigo-50',  border: 'border-indigo-300', text: 'text-indigo-700',  pill: 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200' },
    violet:  { bg: 'bg-violet-50',  border: 'border-violet-300', text: 'text-violet-700',  pill: 'bg-violet-100 text-violet-700 hover:bg-violet-200' },
    amber:   { bg: 'bg-amber-50',   border: 'border-amber-300',  text: 'text-amber-700',   pill: 'bg-amber-100 text-amber-700 hover:bg-amber-200' },
    sky:     { bg: 'bg-sky-50',     border: 'border-sky-300',    text: 'text-sky-700',     pill: 'bg-sky-100 text-sky-700 hover:bg-sky-200' },
    emerald: { bg: 'bg-emerald-50', border: 'border-emerald-300',text: 'text-emerald-700', pill: 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' },
    rose:    { bg: 'bg-rose-50',    border: 'border-rose-300',   text: 'text-rose-700',    pill: 'bg-rose-100 text-rose-700 hover:bg-rose-200' },
    teal:    { bg: 'bg-teal-50',    border: 'border-teal-300',   text: 'text-teal-700',    pill: 'bg-teal-100 text-teal-700 hover:bg-teal-200' },
};

// ctx-field visibility map: which extra fields to show per class
const fieldMap = {
    '1': ['opening_balance', 'currency'],
    '2': ['opening_balance', 'acquisition_date', 'currency'],
    '3': ['opening_balance', 'valuation_method', 'currency'],
    '4': ['opening_balance', 'vat_rate', 'currency'],
    '5': ['opening_balance', 'bank_name', 'currency'],
    '6': ['opening_balance', 'budget_amount', 'currency'],
    '7': ['opening_balance', 'vat_rate', 'currency'],
};

let selectedClass = null;

function selectClass(cls) {
    selectedClass = cls;
    const cfg = pcgClasses[cls];
    const colors = colorMap[cfg.color];

    // Highlight selected button
    document.querySelectorAll('.cls-btn').forEach(b => {
        b.classList.remove('border-indigo-500', 'border-violet-500', 'border-amber-500',
            'border-sky-500', 'border-emerald-500', 'border-rose-500', 'border-teal-500',
            'bg-indigo-50', 'bg-violet-50', 'bg-amber-50', 'bg-sky-50', 'bg-emerald-50', 'bg-rose-50', 'bg-teal-50');
        b.classList.add('border-slate-200');
    });
    const btn = document.getElementById('cls-btn-' + cls);
    btn.classList.remove('border-slate-200');
    btn.classList.add('border-' + cfg.color + '-500', colors.bg);

    // Show description banner
    const desc = document.getElementById('class-desc');
    desc.className = `mx-5 mb-5 px-4 py-3 rounded-lg border text-sm ${colors.bg} ${colors.border} ${colors.text}`;
    desc.innerHTML = `<strong>Classe ${cls}</strong> — ${cfg.desc} <span class="ml-2 text-xs opacity-70">Sens normal : <strong>${cfg.normal === 'debit' ? 'Débit ↑' : 'Crédit ↑'}</strong></span>`;

    // Set hidden inputs
    document.getElementById('pcg_class_input').value = cls;
    document.getElementById('type_input').value = cfg.type;

    // Set normal balance
    document.getElementById('normal-balance').value = cfg.normal;

    // Set code prefix hint
    const codeInput = document.getElementById('code-input');
    if (!codeInput.value || /^\d$/.test(codeInput.value)) {
        codeInput.value = cls;
    }
    document.getElementById('code-hint').textContent = `Classe ${cls} : codes de ${cls}00 à ${cls}99`;

    // Show common accounts
    const commonDiv = document.getElementById('common-accounts');
    const commonList = document.getElementById('common-accounts-list');
    commonDiv.classList.remove('hidden');
    commonList.innerHTML = '';
    cfg.common.forEach(item => {
        const parts = item.split(' ');
        const code = parts[0];
        const name = parts.slice(1).join(' ');
        const pill = document.createElement('button');
        pill.type = 'button';
        pill.className = `text-xs px-2.5 py-1 rounded-full font-medium transition cursor-pointer ${colorMap[cfg.color].pill}`;
        pill.textContent = item;
        pill.onclick = () => {
            document.getElementById('code-input').value = code;
            document.getElementById('name-input').value = name;
        };
        commonList.appendChild(pill);
    });

    // Show/hide contextual fields
    document.querySelectorAll('.ctx-field').forEach(el => el.classList.add('hidden'));
    (fieldMap[cls] || []).forEach(f => {
        const el = document.getElementById('field-' + f);
        if (el) el.classList.remove('hidden');
    });
}

// Restore on validation error
@if(old('pcg_class'))
    document.addEventListener('DOMContentLoaded', () => selectClass('{{ old('pcg_class') }}'));
@endif
</script>
@endpush
@endsection
