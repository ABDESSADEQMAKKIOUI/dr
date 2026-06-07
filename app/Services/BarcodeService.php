<?php

namespace App\Services;

use App\Models\Product;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    /**
     * Generate a unique EAN-13 barcode string for a product.
     */
    public function generateUnique(): string
    {
        do {
            $barcode = '200' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Generate an SVG barcode image for the given value.
     *
     * @param  string  $value
     * @return string  SVG markup
     */
    public function generateSvg(string $value, int $widthFactor = 2, int $height = 50): string
    {
        $generator = new BarcodeGeneratorSVG();

        return $generator->getBarcode(
            $value,
            $generator::TYPE_CODE_128,
            $widthFactor,
            $height
        );
    }

    /**
     * Generate a PNG barcode and return it as a base64 data URI.
     *
     * @param  string  $value
     * @return string  data:image/png;base64,...
     */
    public function generatePngDataUri(string $value, int $widthFactor = 2, int $height = 50): string
    {
        $generator = new BarcodeGeneratorPNG();
        $png       = $generator->getBarcode($value, $generator::TYPE_CODE_128, $widthFactor, $height);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Build the label data array for a list of product IDs and optional quantities.
     *
     * @param  array  $ids      Product IDs
     * @param  array  $qty      Optional per-index quantity overrides
     * @param  bool   $showPrice
     * @return array
     */
    public function buildLabels(array $ids, array $qty = [], bool $showPrice = true): array
    {
        $products = Product::whereIn('id', $ids)->get()->keyBy('id');
        $labels   = [];

        foreach ($ids as $index => $id) {
            $product  = $products[$id] ?? null;
            if (!$product) {
                continue;
            }

            $quantity = isset($qty[$index]) ? max(1, min((int) $qty[$index], 100)) : 1;
            $value    = $product->barcode ?: $product->sku;

            try {
                $svg = $this->generateSvg($value);
            } catch (\Throwable) {
                $svg = null;
            }

            for ($i = 0; $i < $quantity; $i++) {
                $labels[] = [
                    'name'        => $product->name,
                    'sku'         => $product->sku,
                    'barcode'     => $value,
                    'price'       => $showPrice ? number_format($product->sale_price, 2) : null,
                    'barcode_svg' => $svg,
                ];
            }
        }

        return $labels;
    }
}
