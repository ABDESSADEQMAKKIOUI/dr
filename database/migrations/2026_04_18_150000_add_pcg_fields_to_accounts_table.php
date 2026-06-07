<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->tinyInteger('pcg_class')->nullable()->after('code');
            $table->string('normal_balance', 10)->nullable()->after('pcg_class'); // debit | credit
            $table->decimal('opening_balance', 15, 2)->default(0)->after('normal_balance');
            $table->string('currency', 3)->default('MAD')->after('opening_balance');
            // Class 2 – Immobilisations
            $table->date('acquisition_date')->nullable()->after('currency');
            $table->decimal('amortization_rate', 5, 2)->nullable()->after('acquisition_date');
            $table->unsignedSmallInteger('useful_life_years')->nullable()->after('amortization_rate');
            // Class 3 – Stocks
            $table->string('valuation_method', 10)->nullable()->after('useful_life_years'); // fifo|lifo|cmup
            // Class 4 & 7 – Tiers / Produits
            $table->decimal('vat_rate', 5, 2)->nullable()->after('valuation_method');
            $table->unsignedSmallInteger('payment_terms_days')->nullable()->after('vat_rate');
            // Class 5 – Financiers
            $table->string('bank_name')->nullable()->after('payment_terms_days');
            $table->string('iban', 40)->nullable()->after('bank_name');
            // Class 6 – Charges
            $table->decimal('budget_amount', 15, 2)->nullable()->after('iban');
            $table->boolean('vat_deductible')->nullable()->after('budget_amount');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'pcg_class', 'normal_balance', 'opening_balance', 'currency',
                'acquisition_date', 'amortization_rate', 'useful_life_years',
                'valuation_method', 'vat_rate', 'payment_terms_days',
                'bank_name', 'iban', 'budget_amount', 'vat_deductible',
            ]);
        });
    }
};
