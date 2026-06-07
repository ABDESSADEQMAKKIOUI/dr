<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\CustomerGroup;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Warehouse;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 0. Clear previous demo data ────────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'invoice_items', 'invoices',
            'sale_payments', 'sale_items', 'sales',
            'purchase_payments', 'purchase_items', 'purchases',
            'expenses', 'expense_categories',
            'suppliers', 'customers', 'customer_groups',
            'product_warehouses', 'products', 'brands', 'categories',
        ] as $table) {
            DB::statement("TRUNCATE TABLE `{$table}`");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $warehouse   = Warehouse::first();
        $user        = \App\Models\User::first();
        $unitPcs     = Unit::where('short_name', 'pcs')->first() ?? Unit::first();
        $paymentCash = PaymentMethod::where('code', 'cash')->first();
        $paymentCard = PaymentMethod::where('code', 'card')->first();

        // ── 1. Categories ──────────────────────────────────────────────
        $categories = [];
        $catData = [
            ['name' => 'Électronique',   'description' => 'Appareils et gadgets électroniques'],
            ['name' => 'Informatique',   'description' => 'Ordinateurs, accessoires et périphériques'],
            ['name' => 'Téléphonie',     'description' => 'Smartphones et accessoires'],
            ['name' => 'Audio & Vidéo',  'description' => 'Son, image et divertissement'],
            ['name' => 'Fournitures',    'description' => 'Papeterie et consommables de bureau'],
        ];
        foreach ($catData as $cat) {
            $categories[] = Category::create($cat);
        }

        // ── 2. Brands ──────────────────────────────────────────────────
        $brands = [];
        $brandData = [
            ['name' => 'Apple',   'description' => 'Apple Inc.'],
            ['name' => 'Samsung', 'description' => 'Samsung Electronics'],
            ['name' => 'HP',      'description' => 'Hewlett-Packard'],
            ['name' => 'Lenovo',  'description' => 'Lenovo Group'],
            ['name' => 'Sony',    'description' => 'Sony Corporation'],
            ['name' => 'Logitech','description' => 'Logitech International'],
        ];
        foreach ($brandData as $b) {
            $brands[] = Brand::create($b);
        }

        // ── 3. Products ────────────────────────────────────────────────
        $products = [];
        $productData = [
            // Electronics
            ['name' => 'iPhone 15 Pro 256GB',    'sku' => 'APL-IP15P-256',  'barcode' => '3700123410001', 'category' => 2, 'brand' => 0, 'cost' => 8500,  'price' => 11999, 'stock' => 25,  'alert' => 5,  'unit' => $unitPcs->id],
            ['name' => 'iPhone 15 128GB',         'sku' => 'APL-IP15-128',   'barcode' => '3700123410002', 'category' => 2, 'brand' => 0, 'cost' => 6500,  'price' => 8999,  'stock' => 30,  'alert' => 5,  'unit' => $unitPcs->id],
            ['name' => 'Samsung Galaxy S24',      'sku' => 'SAM-GS24-256',   'barcode' => '3700123410003', 'category' => 2, 'brand' => 1, 'cost' => 7000,  'price' => 9499,  'stock' => 20,  'alert' => 5,  'unit' => $unitPcs->id],
            ['name' => 'Samsung Galaxy A54',      'sku' => 'SAM-GA54-128',   'barcode' => '3700123410004', 'category' => 2, 'brand' => 1, 'cost' => 2800,  'price' => 3999,  'stock' => 40,  'alert' => 10, 'unit' => $unitPcs->id],
            // Computers
            ['name' => 'MacBook Pro M3 14"',      'sku' => 'APL-MBP-M3-14',  'barcode' => '3700123410005', 'category' => 1, 'brand' => 0, 'cost' => 14000, 'price' => 19999, 'stock' => 10,  'alert' => 3,  'unit' => $unitPcs->id],
            ['name' => 'HP EliteBook 840 G10',    'sku' => 'HP-EB840-G10',   'barcode' => '3700123410006', 'category' => 1, 'brand' => 2, 'cost' => 9500,  'price' => 12999, 'stock' => 8,   'alert' => 3,  'unit' => $unitPcs->id],
            ['name' => 'Lenovo ThinkPad X1',      'sku' => 'LEN-TP-X1',      'barcode' => '3700123410007', 'category' => 1, 'brand' => 3, 'cost' => 11000, 'price' => 14999, 'stock' => 6,   'alert' => 2,  'unit' => $unitPcs->id],
            // Audio
            ['name' => 'AirPods Pro 2',           'sku' => 'APL-APP2',       'barcode' => '3700123410008', 'category' => 3, 'brand' => 0, 'cost' => 1800,  'price' => 2499,  'stock' => 50,  'alert' => 10, 'unit' => $unitPcs->id],
            ['name' => 'Sony WH-1000XM5',         'sku' => 'SNY-WH1000XM5',  'barcode' => '3700123410009', 'category' => 3, 'brand' => 4, 'cost' => 2200,  'price' => 2999,  'stock' => 15,  'alert' => 5,  'unit' => $unitPcs->id],
            // Accessories
            ['name' => 'Logitech MX Master 3S',   'sku' => 'LGT-MXM3S',      'barcode' => '3700123410010', 'category' => 1, 'brand' => 5, 'cost' => 550,   'price' => 799,   'stock' => 35,  'alert' => 10, 'unit' => $unitPcs->id],
            ['name' => 'Clavier Logitech MX Keys', 'sku' => 'LGT-MXK',       'barcode' => '3700123410011', 'category' => 1, 'brand' => 5, 'cost' => 700,   'price' => 999,   'stock' => 28,  'alert' => 10, 'unit' => $unitPcs->id],
            // Low stock
            ['name' => 'Samsung 4K Monitor 27"',  'sku' => 'SAM-MON27-4K',   'barcode' => '3700123410012', 'category' => 0, 'brand' => 1, 'cost' => 3200,  'price' => 4499,  'stock' => 4,   'alert' => 5,  'unit' => $unitPcs->id],
            // Out of stock
            ['name' => 'iPad Pro M4 11"',         'sku' => 'APL-IPD-M4-11',  'barcode' => '3700123410013', 'category' => 0, 'brand' => 0, 'cost' => 7500,  'price' => 10499, 'stock' => 0,   'alert' => 3,  'unit' => $unitPcs->id],
            // Supplies
            ['name' => 'Câble USB-C 2m',          'sku' => 'ACC-USBC-2M',    'barcode' => '3700123410014', 'category' => 4, 'brand' => 0, 'cost' => 45,    'price' => 89,    'stock' => 200, 'alert' => 50, 'unit' => $unitPcs->id],
            ['name' => 'Chargeur 65W GaN',        'sku' => 'ACC-CHG-65W',    'barcode' => '3700123410015', 'category' => 4, 'brand' => 0, 'cost' => 150,   'price' => 249,   'stock' => 80,  'alert' => 20, 'unit' => $unitPcs->id],
        ];

        foreach ($productData as $pd) {
            $catObj   = $categories[$pd['category']] ?? $categories[0];
            $brandObj = $brands[$pd['brand']] ?? null;

            $product = Product::create([
                'name'          => $pd['name'],
                'sku'           => $pd['sku'],
                'barcode'       => $pd['barcode'],
                'category_id'   => $catObj->id,
                'brand_id'      => $brandObj?->id,
                'unit_id'       => $pd['unit'],
                'sale_unit_id'  => $pd['unit'],
                'purchase_unit_id' => $pd['unit'],
                'type'          => 'simple',
                'cost_price'    => $pd['cost'],
                'sale_price'    => $pd['price'],
                'tax_type'      => 'exclusive',
                'tax_rate'      => 20,
                'stock_alert'   => $pd['alert'],
                'stock_quantity'=> $pd['stock'],
                'is_active'     => true,
                'is_featured'   => in_array($pd['sku'], ['APL-IP15P-256', 'APL-MBP-M3-14', 'SAM-GS24-256']),
                'track_stock'   => true,
                'created_by'    => $user->id,
            ]);

            ProductWarehouse::create([
                'product_id'   => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity'     => $pd['stock'],
            ]);

            $products[] = $product;
        }

        // ── 4. Customer groups ─────────────────────────────────────────
        $vip    = CustomerGroup::create(['name' => 'VIP',       'discount_percentage' => 10]);
        $retail = CustomerGroup::create(['name' => 'Détail',    'discount_percentage' => 0]);
        $wholesale = CustomerGroup::create(['name' => 'Grossiste', 'discount_percentage' => 15]);

        // ── 5. Customers ───────────────────────────────────────────────
        $customers = [];
        $customerData = [
            ['name' => 'Youssef El Mansouri',  'email' => 'youssef@example.ma',  'phone' => '+212 6 11 22 33 44', 'city' => 'Casablanca',  'group' => $vip->id,      'credit' => 50000],
            ['name' => 'Fatima Zahra Bennani', 'email' => 'fatima@example.ma',   'phone' => '+212 6 22 33 44 55', 'city' => 'Rabat',       'group' => $retail->id,   'credit' => 20000],
            ['name' => 'Karim Tazi',           'email' => 'karim@example.ma',    'phone' => '+212 6 33 44 55 66', 'city' => 'Marrakech',   'group' => $wholesale->id,'credit' => 100000],
            ['name' => 'Nadia Alaoui',         'email' => 'nadia@example.ma',    'phone' => '+212 6 44 55 66 77', 'city' => 'Fès',         'group' => $retail->id,   'credit' => 15000],
            ['name' => 'Omar Chraibi',         'email' => 'omar@example.ma',     'phone' => '+212 6 55 66 77 88', 'city' => 'Agadir',      'group' => $vip->id,      'credit' => 75000],
            ['name' => 'Salma Idrissi',        'email' => 'salma@example.ma',    'phone' => '+212 6 66 77 88 99', 'city' => 'Tanger',      'group' => $retail->id,   'credit' => 10000],
            ['name' => 'Tech Solutions SARL',  'email' => 'contact@techsol.ma',  'phone' => '+212 5 22 00 11 22', 'city' => 'Casablanca',  'group' => $wholesale->id,'credit' => 200000],
        ];
        foreach ($customerData as $cd) {
            $customers[] = Customer::create([
                'name'              => $cd['name'],
                'email'             => $cd['email'],
                'phone'             => $cd['phone'],
                'city'              => $cd['city'],
                'country'           => 'Morocco',
                'customer_group_id' => $cd['group'],
                'credit_limit'      => $cd['credit'],
                'is_active'         => true,
                'created_by'        => $user->id,
            ]);
        }

        // ── 6. Suppliers ───────────────────────────────────────────────
        $suppliers = [];
        $supplierData = [
            ['name' => 'Apple Maroc Distribution', 'email' => 'orders@apple-ma.com',     'phone' => '+212 5 22 10 00 01', 'city' => 'Casablanca'],
            ['name' => 'Samsung Electronics MA',   'email' => 'supply@samsung-ma.com',   'phone' => '+212 5 22 10 00 02', 'city' => 'Casablanca'],
            ['name' => 'HP Morocco',               'email' => 'procurement@hp-ma.com',   'phone' => '+212 5 37 10 00 03', 'city' => 'Rabat'],
            ['name' => 'Tech Import SARL',         'email' => 'import@techimport.ma',    'phone' => '+212 5 22 10 00 04', 'city' => 'Casablanca'],
            ['name' => 'Electro Pro Grossiste',    'email' => 'vente@electropro.ma',     'phone' => '+212 5 24 10 00 05', 'city' => 'Marrakech'],
        ];
        foreach ($supplierData as $sd) {
            $suppliers[] = Supplier::create([
                'name'       => $sd['name'],
                'email'      => $sd['email'],
                'phone'      => $sd['phone'],
                'city'       => $sd['city'],
                'country'    => 'Morocco',
                'is_active'  => true,
                'created_by' => $user->id,
            ]);
        }

        // ── 7. Expense categories ──────────────────────────────────────
        $expCats = [];
        foreach (['Loyer', 'Électricité', 'Internet & Télécom', 'Transport', 'Salaires', 'Marketing', 'Maintenance'] as $ec) {
            $expCats[] = ExpenseCategory::create(['name' => $ec]);
        }

        // ── 8. Purchases ───────────────────────────────────────────────
        $purchaseData = [
            // [supplier_idx, date_offset_days, items: [[product_idx, qty, cost]], paid_full]
            [0, -60, [[0, 10, 8500], [1, 15, 6500]], true],    // Apple supplier – iPhones
            [1, -55, [[2, 10, 7000], [3, 20, 2800]], true],    // Samsung
            [2, -45, [[5, 5,  9500], [6, 4, 11000]], true],    // HP
            [3, -40, [[7, 30, 1800], [8, 10, 2200]], true],    // Audio
            [4, -30, [[9, 20, 550],  [10, 15, 700]], true],    // Accessories
            [0, -20, [[4, 5, 14000]], false],                  // MacBook – partially paid
            [1, -15, [[11, 5, 3200], [12, 3, 7500]], true],    // Monitors + iPad
            [3, -10, [[13, 100, 45], [14, 50, 150]], true],    // Cables & chargers
        ];

        $purchases = [];
        foreach ($purchaseData as $idx => [$supIdx, $daysAgo, $items, $fullPaid]) {
            $date       = Carbon::now()->addDays($daysAgo)->format('Y-m-d');
            $totalAmount = 0;
            $taxAmount   = 0;

            foreach ($items as [, $qty, $cost]) {
                $sub = $qty * $cost;
                $tax = round($sub * 0.20, 2);
                $totalAmount += $sub + $tax;
                $taxAmount   += $tax;
            }

            $paidAmount = $fullPaid ? $totalAmount : round($totalAmount * 0.5, 2);
            $payStatus  = $fullPaid ? 'paid' : 'partial';

            $purchase = Purchase::create([
                'reference'      => 'PO-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id'    => $suppliers[$supIdx]->id,
                'warehouse_id'   => $warehouse->id,
                'user_id'        => $user->id,
                'date'           => $date,
                'status'         => 'received',
                'tax_amount'     => $taxAmount,
                'discount_amount'=> 0,
                'shipping_cost'  => 0,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'payment_status' => $payStatus,
            ]);

            foreach ($items as [$prodIdx, $qty, $cost]) {
                $sub = $qty * $cost;
                $tax = round($sub * 0.20, 2);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $products[$prodIdx]->id,
                    'quantity'    => $qty,
                    'price'       => $cost,
                    'tax_type'    => 'exclusive',
                    'tax_rate'    => 20,
                    'tax_amount'  => $tax,
                    'discount_type'  => 'fixed',
                    'discount_value' => 0,
                    'discount_amount'=> 0,
                    'subtotal'    => $sub + $tax,
                ]);
            }

            if ($paidAmount > 0) {
                PurchasePayment::create([
                    'purchase_id'       => $purchase->id,
                    'amount'            => $paidAmount,
                    'payment_method_id' => $paymentCash->id,
                    'date'              => $date,
                    'user_id'           => $user->id,
                ]);
            }

            $purchases[] = $purchase;
        }

        // ── 9. Sales ───────────────────────────────────────────────────
        $salesRaw = [
            // [customer_idx, date_offset, items: [[prod_idx, qty]], paid_full, payment_method]
            [0, -55, [[0, 1], [7, 1]],       true,  'cash'],    // Youssef
            [1, -50, [[3, 1], [9, 1]],       true,  'card'],    // Fatima
            [2, -45, [[1, 2], [2, 1]],       true,  'transfer'],// Karim wholesale
            [4, -40, [[4, 1]],               true,  'card'],    // Omar – MacBook
            [0, -35, [[8, 1], [10, 1]],      true,  'cash'],    // Youssef
            [3, -30, [[3, 1], [14, 2]],      true,  'card'],    // Nadia
            [6, -25, [[0, 3], [1, 5], [2, 2]], true, 'transfer'],// Tech Solutions bulk
            [5, -20, [[7, 1], [13, 3]],      true,  'cash'],    // Salma
            [2, -15, [[5, 1], [6, 1]],       false, 'transfer'],// Karim – unpaid
            [0, -12, [[9, 2], [10, 1]],      true,  'card'],    // Youssef
            [4, -10, [[1, 1], [3, 2]],       true,  'cash'],    // Omar
            [1, -8,  [[14, 5], [13, 10]],    true,  'cash'],    // Fatima – supplies
            [6, -5,  [[4, 2], [5, 1]],       false, 'transfer'],// Tech Solutions bulk – partial
            [3, -3,  [[7, 1]],               true,  'card'],    // Nadia
            [5, -1,  [[0, 1], [8, 1]],       true,  'cash'],    // Salma – recent
        ];

        $sales = [];
        foreach ($salesRaw as $sIdx => [$custIdx, $daysAgo, $items, $fullPaid, $payMethod]) {
            $date = Carbon::now()->addDays($daysAgo)->format('Y-m-d');
            $totalAmount = 0;
            $taxAmount   = 0;

            foreach ($items as [$prodIdx, $qty]) {
                $price = $products[$prodIdx]->sale_price;
                $sub   = $qty * $price;
                $tax   = round($sub * 0.20, 2);
                $totalAmount += $sub + $tax;
                $taxAmount   += $tax;
            }

            $paidAmount = $fullPaid ? $totalAmount : round($totalAmount * 0.5, 2);
            $payStatus  = $fullPaid ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

            $sale = Sale::create([
                'reference'      => 'SO-' . str_pad($sIdx + 1, 4, '0', STR_PAD_LEFT),
                'customer_id'    => $customers[$custIdx]->id,
                'warehouse_id'   => $warehouse->id,
                'user_id'        => $user->id,
                'date'           => $date,
                'status'         => 'delivered',
                'tax_amount'     => $taxAmount,
                'discount_type'  => 'fixed',
                'discount_value' => 0,
                'discount_amount'=> 0,
                'shipping_cost'  => 0,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'payment_status' => $payStatus,
            ]);

            foreach ($items as [$prodIdx, $qty]) {
                $price    = $products[$prodIdx]->sale_price;
                $unitCost = $products[$prodIdx]->cost_price;
                $sub      = $qty * $price;
                $tax      = round($sub * 0.20, 2);
                SaleItem::create([
                    'sale_id'        => $sale->id,
                    'product_id'     => $products[$prodIdx]->id,
                    'quantity'       => $qty,
                    'price'          => $price,
                    'unit_cost'      => $unitCost,
                    'tax_type'       => 'exclusive',
                    'tax_rate'       => 20,
                    'tax_amount'     => $tax,
                    'discount_type'  => 'fixed',
                    'discount_value' => 0,
                    'discount_amount'=> 0,
                    'subtotal'       => $sub + $tax,
                ]);
            }

            $pmId = match($payMethod) {
                'card'     => $paymentCard?->id ?? $paymentCash->id,
                'transfer' => PaymentMethod::where('code', 'transfer')->first()?->id ?? $paymentCash->id,
                default    => $paymentCash->id,
            };

            if ($paidAmount > 0) {
                SalePayment::create([
                    'sale_id'           => $sale->id,
                    'amount'            => $paidAmount,
                    'payment_method_id' => $pmId,
                    'date'              => $date,
                    'user_id'           => $user->id,
                ]);
            }

            $sales[] = $sale;
        }

        // ── 10. Invoices (for completed paid sales) ────────────────────
        foreach ($sales as $iIdx => $sale) {
            if ($sale->payment_status === 'paid') {
                $invoice = Invoice::create([
                    'reference'      => 'INV-' . str_pad($iIdx + 1, 4, '0', STR_PAD_LEFT),
                    'sale_id'        => $sale->id,
                    'customer_id'    => $sale->customer_id,
                    'date'           => $sale->date,
                    'due_date'       => Carbon::parse($sale->date)->addDays(30)->format('Y-m-d'),
                    'status'         => 'paid',
                    'tax_amount'     => $sale->tax_amount,
                    'discount_amount'=> $sale->discount_amount,
                    'total_amount'   => $sale->total_amount,
                    'paid_amount'    => $sale->paid_amount,
                    'terms'          => 'Paiement à 30 jours.',
                ]);

                // Copy items to invoice_items
                foreach ($sale->items ?? SaleItem::where('sale_id', $sale->id)->get() as $si) {
                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'product_id'  => $si->product_id,
                        'description' => Product::find($si->product_id)?->name ?? '',
                        'quantity'    => $si->quantity,
                        'price'       => $si->price,
                        'tax_rate'    => $si->tax_rate,
                        'tax_amount'  => $si->tax_amount,
                        'subtotal'    => $si->subtotal,
                    ]);
                }
            }
        }

        // ── 11. Expenses ───────────────────────────────────────────────
        $expenseData = [
            [0, 'Loyer bureau Casablanca – Janvier',   8500,  -90],
            [0, 'Loyer bureau Casablanca – Février',   8500,  -60],
            [0, 'Loyer bureau Casablanca – Mars',      8500,  -30],
            [1, 'Facture électricité Q1',              1200,  -60],
            [1, 'Facture électricité Q2',              980,   -5],
            [2, 'Abonnement Internet fibre',           599,   -90],
            [2, 'Abonnement Internet fibre',           599,   -60],
            [2, 'Abonnement Internet fibre',           599,   -30],
            [3, 'Carburant véhicule livraison',        650,   -20],
            [3, 'Entretien véhicule',                  1800,  -45],
            [5, 'Google Ads – Campagne Ramadan',       3500,  -55],
            [5, 'Impression flyers promotionnels',     800,   -40],
            [6, 'Maintenance serveur',                 2000,  -15],
            [6, 'Réparation climatisation',            1200,  -25],
        ];

        foreach ($expenseData as $eIdx => [$catIdx, $desc, $amount, $daysAgo]) {
            Expense::create([
                'reference'           => 'EXP-' . str_pad($eIdx + 1, 4, '0', STR_PAD_LEFT),
                'expense_category_id' => $expCats[$catIdx]->id,
                'date'                => Carbon::now()->addDays($daysAgo)->format('Y-m-d'),
                'amount'              => $amount,
                'payment_method_id'   => $paymentCash->id,
                'description'         => $desc,
                'user_id'             => $user->id,
            ]);
        }

        $this->command->info('✓ Demo data seeded: ' . count($products) . ' products, ' . count($customers) . ' customers, ' . count($suppliers) . ' suppliers, ' . count($purchases) . ' purchases, ' . count($sales) . ' sales, ' . count($expenseData) . ' expenses.');
    }
}
