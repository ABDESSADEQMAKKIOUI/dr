<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\DB;

class OpeningStockImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected int $imported = 0;
    protected int $skipped = 0;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $product = Product::where('sku', $row['product_sku'])->first();
                $warehouse = Warehouse::find($row['warehouse_id']);

                if (!$product || !$warehouse) {
                    $this->skipped++;
                    continue;
                }

                $quantity  = (int) $row['quantity'];
                $costPrice = (float) ($row['cost_price'] ?? 0);

                // Create stock adjustment record
                $adjustment = StockAdjustment::create([
                    'warehouse_id' => $warehouse->id,
                    'date'         => now(),
                    'reference'    => 'OPENING-' . strtoupper(\Str::random(6)),
                    'type'         => 'addition',
                    'notes'        => 'Opening Stock Import',
                    'user_id'      => auth()->id(),
                ]);

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id'          => $product->id,
                    'quantity'            => $quantity,
                    'type'                => 'addition',
                ]);

                // Update product warehouse stock
                $pw = ProductWarehouse::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                    ['quantity' => 0]
                );
                $pw->increment('quantity', $quantity);

                // Update product cost price if provided
                if ($costPrice > 0) {
                    $product->update(['cost' => $costPrice]);
                }

                $this->imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'product_sku'  => 'required|string',
            'warehouse_id' => 'required|integer',
            'quantity'     => 'required|integer|min:1',
            'cost_price'   => 'nullable|numeric|min:0',
        ];
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int { return $this->skipped; }
}
