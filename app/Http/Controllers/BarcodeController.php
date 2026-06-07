<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    /**
     * Return a new unique barcode string (EAN-13 format).
     */
    public function generate(): JsonResponse
    {
        $barcode = $this->productService->generateBarcode();
        return response()->json(['barcode' => $barcode]);
    }

    /**
     * Find a product by its barcode or SKU.
     * Used by the POS camera/hardware scanner AJAX lookup.
     */
    public function findByBarcode(Request $request): JsonResponse
    {
        $value = $request->query('barcode', '');

        $product = Product::where('barcode', $value)
            ->orWhere('sku', $value)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['product' => null], 404);
        }

        return response()->json([
            'product' => [
                'id'            => $product->id,
                'name'          => $product->name,
                'sku'           => $product->sku,
                'barcode'       => $product->barcode,
                'sale_price'    => $product->sale_price,
                'cost_price'    => $product->cost_price,
                'stock_quantity'=> $product->stock_quantity,
                'image'         => $product->image ? asset('storage/' . $product->image) : null,
            ]
        ]);
    }

    /**
     * Print barcode labels for selected products.
     * GET /barcodes/print?ids[]=1&ids[]=2&size=a4
     */
    public function print(Request $request): View
    {
        $ids  = $request->query('ids', []);
        $size = $request->query('size', 'a4'); // a4 | 58mm | 80mm
        $qty  = $request->query('qty', []);    // optional per-product qty override

        if (empty($ids)) {
            abort(400, 'No product IDs provided.');
        }

        $products = Product::whereIn('id', $ids)->get();

        // Build label list: repeat product entry by quantity
        $labels = [];
        foreach ($products as $index => $product) {
            $quantity = isset($qty[$index]) ? (int)$qty[$index] : 1;
            $quantity = max(1, min($quantity, 50)); // cap at 50 per product

            // Generate SVG barcode
            $barcodeValue = $product->barcode ?: $product->sku;
            $barcodeType  = strlen($barcodeValue) === 13 ? 'EAN13' : 'C128';

            try {
                $generator = new BarcodeGeneratorSVG();
                $barcodeSvg = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128, 2, 50);
            } catch (\Throwable $e) {
                $barcodeSvg = null;
            }

            for ($i = 0; $i < $quantity; $i++) {
                $labels[] = [
                    'name'        => $product->name,
                    'sku'         => $product->sku,
                    'barcode'     => $barcodeValue,
                    'price'       => number_format($product->sale_price, 2),
                    'barcode_svg' => $barcodeSvg,
                ];
            }
        }

        return view('barcodes.print', compact('labels', 'size'));
    }
}
