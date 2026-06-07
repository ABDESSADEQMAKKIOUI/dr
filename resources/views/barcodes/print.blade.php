<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Labels</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 16px;
        }

        /* === Screen controls (hidden on print) === */
        .screen-controls {
            background: #1e40af;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .screen-controls select,
        .screen-controls button {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .screen-controls select { background: #fff; color: #1e40af; }
        .screen-controls button { background: #22c55e; color: white; font-weight: bold; }

        /* === Label sheet === */
        .label-sheet {
            background: white;
            border-radius: 8px;
            padding: 10mm;
        }

        /* === A4 layout: 3 columns === */
        .layout-a4 .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4mm;
        }
        .layout-a4 .label-cell {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 4mm;
            text-align: center;
            page-break-inside: avoid;
        }

        /* === 80mm layout: 2 columns === */
        .layout-80mm .label-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3mm;
        }
        .layout-80mm .label-cell {
            border: 1px dashed #aaa;
            padding: 2mm;
            text-align: center;
            page-break-inside: avoid;
        }

        /* === 58mm layout: 1 column === */
        .layout-58mm .label-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3mm;
            max-width: 58mm;
        }
        .layout-58mm .label-cell {
            border: 1px dashed #aaa;
            padding: 2mm;
            text-align: center;
            page-break-inside: avoid;
        }

        .label-cell .product-name {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2mm;
            line-height: 1.2;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .label-cell .barcode-svg {
            width: 100%;
            max-height: 14mm;
            overflow: hidden;
        }
        .label-cell .barcode-svg svg {
            width: 100%;
            height: auto;
        }

        .label-cell .barcode-value {
            font-size: 7pt;
            color: #555;
            margin-top: 1mm;
            font-family: monospace;
            letter-spacing: 1px;
        }

        .label-cell .product-price {
            font-size: 10pt;
            font-weight: bold;
            color: #1e40af;
            margin-top: 2mm;
        }

        /* === Print styles === */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .screen-controls { display: none !important; }
            .label-sheet { padding: 5mm; border-radius: 0; }

            @page {
                margin: 5mm;
                size: A4 portrait;
            }

            .label-cell {
                page-break-inside: avoid;
            }
        }

        @media print and (max-width: 80mm) {
            @page { size: 80mm auto; margin: 2mm; }
        }
    </style>
</head>
<body>

<!-- Screen controls -->
<div class="screen-controls">
    <div>
        <strong>{{ count($labels) }} {{ __('app.barcode_labels') }}</strong> {{ __('app.ready_to_print') }}
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        <label style="font-size:13px;">{{ __('app.paper_size') }}:</label>
        <select id="size-selector" onchange="changeSize(this.value)">
            <option value="a4" {{ ($size ?? 'a4') === 'a4' ? 'selected' : '' }}>{{ __('app.a4_3_columns') }}</option>
            <option value="80mm" {{ ($size ?? '') === '80mm' ? 'selected' : '' }}>{{ __('app.80mm_2_columns') }}</option>
            <option value="58mm" {{ ($size ?? '') === '58mm' ? 'selected' : '' }}>{{ __('app.58mm_1_column') }}</option>
        </select>
        <button onclick="window.print()">🖨 {{ __('app.print') }}</button>
    </div>
</div>

<!-- Label sheet -->
<div class="label-sheet layout-{{ $size ?? 'a4' }}" id="label-sheet">
    <div class="label-grid">
        @foreach($labels as $label)
        <div class="label-cell">
            <div class="product-name">{{ $label['name'] }}</div>

            @if($label['barcode_svg'])
            <div class="barcode-svg">{!! $label['barcode_svg'] !!}</div>
            @endif

            <div class="barcode-value">{{ $label['barcode'] }}</div>
            <div class="product-price">{{ $label['price'] }} DH</div>
        </div>
        @endforeach
    </div>
</div>

<script>
function changeSize(size) {
    const sheet = document.getElementById('label-sheet');
    sheet.className = 'label-sheet layout-' + size;
}

// Auto-print if requested via URL param
const params = new URLSearchParams(window.location.search);
if (params.get('autoprint') === '1') {
    window.onload = () => window.print();
}
</script>
</body>
</html>
