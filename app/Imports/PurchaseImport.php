<?php

namespace App\Imports;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class PurchaseImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected int $imported = 0;
    protected int $skipped  = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            // Skip duplicates by reference
            if (Purchase::where('reference', $row['reference'])->exists()) {
                $this->skipped++;
                continue;
            }

            $supplier  = Supplier::where('name', $row['supplier_name'])->first();
            $warehouse = Warehouse::where('name', $row['warehouse_name'] ?? '')->first();
            $product   = Product::where('sku', $row['product_sku'])->first();

            if (!$supplier || !$product) {
                $this->skipped++;
                continue;
            }

            DB::transaction(function () use ($row, $supplier, $warehouse, $product) {
                $quantity  = max(1, (int) ($row['quantity'] ?? 1));
                $unitCost  = (float) ($row['unit_cost'] ?? 0);
                $total     = $quantity * $unitCost;

                $purchase = Purchase::create([
                    'reference'      => $row['reference'],
                    'supplier_id'    => $supplier->id,
                    'warehouse_id'   => $warehouse?->id,
                    'date'           => $row['date'] ?? now()->format('Y-m-d'),
                    'total_amount'   => $total,
                    'paid_amount'    => (float) ($row['paid_amount'] ?? 0),
                    'payment_status' => 'unpaid',
                    'status'         => 'received',
                    'notes'          => $row['notes'] ?? null,
                ]);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $product->id,
                    'quantity'    => $quantity,
                    'price'       => $unitCost,
                    'subtotal'    => $total,
                ]);

                // Update product stock
                $product->increment('stock_quantity', $quantity);
            });

            $this->imported++;
        }
    }

    public function rules(): array
    {
        return [
            'reference'    => 'required|string|max:100',
            'supplier_name'=> 'required|string',
            'product_sku'  => 'required|string',
            'quantity'     => 'required|numeric|min:1',
            'unit_cost'    => 'required|numeric|min:0',
            'date'         => 'nullable|date',
        ];
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int  { return $this->skipped;  }
}
