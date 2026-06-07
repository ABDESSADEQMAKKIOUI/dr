<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Journal;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Créer une écriture comptable en double entrée
     */
    public function createJournalEntry(array $data): Transaction
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'journal_id' => $data['journal_id'],
                'date' => $data['date'] ?? now(),
                'reference' => $data['reference'] ?? $this->generateReference(),
                'description' => $data['description'],
                'user_id' => auth()->id(),
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            // Créer les lignes d'écriture (débit et crédit)
            foreach ($data['entries'] as $entry) {
                $transaction->entries()->create([
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'] ?? 0,
                    'credit' => $entry['credit'] ?? 0,
                    'description' => $entry['description'] ?? null,
                ]);

                $totalDebit += $entry['debit'] ?? 0;
                $totalCredit += $entry['credit'] ?? 0;
            }

            // Vérifier l'équilibre débit/crédit
            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \Exception("L'écriture n'est pas équilibrée. Débit: $totalDebit, Crédit: $totalCredit");
            }

            DB::commit();
            return $transaction->fresh('entries.account', 'journal');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Génération automatique d'écritures depuis ventes
     */
    public function generateFromSale(\App\Models\Sale $sale): Transaction
    {
        $salesJournal = Journal::where('code', 'VTE')->first();

        return $this->createJournalEntry([
            'journal_id' => $salesJournal->id,
            'date' => $sale->date,
            'reference' => $sale->reference,
            'description' => "Vente {$sale->reference} - Client: {$sale->customer->name}",
            'entries' => [
                // Débit: Créances clients
                [
                    'account_id' => Account::where('code', '411')->first()->id,
                    'debit' => $sale->total_amount,
                    'credit' => 0,
                ],
                // Crédit: Ventes
                [
                    'account_id' => Account::where('code', '707')->first()->id,
                    'debit' => 0,
                    'credit' => $sale->total_amount - $sale->tax_amount,
                ],
                // Crédit: TVA collectée
                [
                    'account_id' => Account::where('code', '4457')->first()->id,
                    'debit' => 0,
                    'credit' => $sale->tax_amount,
                ],
            ],
        ]);
    }

    /**
     * Génération automatique d'écritures depuis achats
     */
    public function generateFromPurchase(\App\Models\Purchase $purchase): Transaction
    {
        $purchaseJournal = Journal::where('code', 'ACH')->first();

        return $this->createJournalEntry([
            'journal_id' => $purchaseJournal->id,
            'date' => $purchase->date,
            'reference' => $purchase->reference,
            'description' => "Achat {$purchase->reference} - Fournisseur: {$purchase->supplier->name}",
            'entries' => [
                // Débit: Achats
                [
                    'account_id' => Account::where('code', '607')->first()->id,
                    'debit' => $purchase->total_amount - $purchase->tax_amount,
                    'credit' => 0,
                ],
                // Débit: TVA déductible
                [
                    'account_id' => Account::where('code', '4456')->first()->id,
                    'debit' => $purchase->tax_amount,
                    'credit' => 0,
                ],
                // Crédit: Dettes fournisseurs
                [
                    'account_id' => Account::where('code', '401')->first()->id,
                    'debit' => 0,
                    'credit' => $purchase->total_amount,
                ],
            ],
        ]);
    }

    /**
     * Grand livre (General Ledger)
     */
    public function getGeneralLedger(array $filters = []): array
    {
        $query = DB::table('transaction_entries')
            ->join('accounts', 'transaction_entries.account_id', '=', 'accounts.id')
            ->join('transactions', 'transaction_entries.transaction_id', '=', 'transactions.id')
            ->select(
                'accounts.code',
                'accounts.name',
                DB::raw('SUM(transaction_entries.debit) as total_debit'),
                DB::raw('SUM(transaction_entries.credit) as total_credit'),
                DB::raw('SUM(transaction_entries.debit - transaction_entries.credit) as balance')
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name');

        if (isset($filters['date_from'])) {
            $query->where('transactions.date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('transactions.date', '<=', $filters['date_to']);
        }

        return $query->get()->toArray();
    }

    /**
     * Balance générale
     */
    public function getTrialBalance(array $filters = []): array
    {
        return $this->getGeneralLedger($filters);
    }

    /**
     * Génération référence automatique
     */
    protected function generateReference(): string
    {
        return 'TXN-' . date('Ymd') . '-' . str_pad(Transaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculer TVA
     */
    public function calculateVAT(array $filters = []): array
    {
        $vatCollected = DB::table('transaction_entries')
            ->join('accounts', 'transaction_entries.account_id', '=', 'accounts.id')
            ->join('transactions', 'transaction_entries.transaction_id', '=', 'transactions.id')
            ->where('accounts.code', '4457') // TVA collectée
            ->sum('transaction_entries.credit');

        $vatDeductible = DB::table('transaction_entries')
            ->join('accounts', 'transaction_entries.account_id', '=', 'accounts.id')
            ->join('transactions', 'transaction_entries.transaction_id', '=', 'transactions.id')
            ->where('accounts.code', '4456') // TVA déductible
            ->sum('transaction_entries.debit');

        return [
            'vat_collected' => $vatCollected,
            'vat_deductible' => $vatDeductible,
            'vat_to_pay' => $vatCollected - $vatDeductible,
        ];
    }
}
