<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الباركود — جنين للتجميل</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            padding: 0;
            margin: 0;
        }

        @media print {
            @page { size: A4; margin: 10mm; }
            body { padding: 0; }
            .no-print { display: none !important; }
        }

        .print-controls {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-controls button {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
        }

        .print-controls button:hover { background: #0b5ed7; }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 0;
        }

        /* Layout: 24 labels (5cm x 3cm) — 4 columns × 6 rows */
        .label-24 {
            width: 48mm;
            height: 32mm;
            border: 1px dashed #ccc;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }

        /* Layout: 12 labels (7cm x 4cm) — 3 columns × 4 rows */
        .label-12 {
            width: 65mm;
            height: 45mm;
            border: 1px dashed #ccc;
            padding: 4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }

        /* Layout: 6 labels (10cm x 5cm) — 2 columns × 3 rows */
        .label-6 {
            width: 98mm;
            height: 65mm;
            border: 1px dashed #ccc;
            padding: 5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }

        .barcode-img {
            max-width: 100%;
            height: auto;
            image-rendering: crisp-edges;
        }

        .product-name {
            font-size: 9px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 2px;
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-sku {
            font-size: 8px;
            color: #6c757d;
            margin-bottom: 3px;
            font-family: monospace;
        }

        .product-price {
            font-size: 10px;
            font-weight: 700;
            color: #dc3545;
            margin-top: 2px;
        }

        .barcode-number {
            font-size: 9px;
            font-family: monospace;
            color: #495057;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .brand-text {
            font-size: 7px;
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .label-12 .product-name { font-size: 11px; }
        .label-12 .product-sku { font-size: 9px; }
        .label-12 .product-price { font-size: 12px; }
        .label-12 .barcode-number { font-size: 10px; }

        .label-6 .product-name { font-size: 13px; }
        .label-6 .product-sku { font-size: 11px; }
        .label-6 .product-price { font-size: 14px; }
        .label-6 .barcode-number { font-size: 12px; }
        .label-6 .brand-text { font-size: 9px; }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <div>
            <strong>طباعة الباركود</strong>
            <span class="text-muted">— {{ count($products) }} منتج | تخطيط: {{ $layout }}</span>
        </div>
        <button onclick="window.print()">
            <i class="fas fa-print"></i> طباعة الآن
        </button>
    </div>

    @php
        $labelClass = match($layout) {
            'a4_12' => 'label-12',
            'a4_6' => 'label-6',
            default => 'label-24',
        };
    @endphp

    <div class="sheet">
        @foreach($products as $product)
            @for($i = 0; $i < ($layout === 'a4_6' ? 1 : ($layout === 'a4_12' ? 1 : 1)); $i++)
                <div class="{{ $labelClass }}">
                    <div class="brand-text">{{ $siteSettings['site_name'] ?? 'جنين للتجميل' }}</div>
                    <div class="product-name" title="{{ $product->name_ar }}">{{ Str::limit($product->name_ar, 35) }}</div>
                    <div class="product-sku">{{ $product->sku }}</div>

                    @if($product->barcode)
                        {{-- Using barcode.tec-it.com API for free barcode generation --}}
                        <img src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($product->barcode) }}&code=EAN13&dpi=96&dataseparator=&translate-esc=true"
                             alt="{{ $product->barcode }}"
                             class="barcode-img"
                             style="height: {{ $layout === 'a4_6' ? '35mm' : ($layout === 'a4_12' ? '22mm' : '15mm') }};">
                        <div class="barcode-number">{{ $product->barcode }}</div>
                    @else
                        <div style="font-size: 10px; color: #dc3545; padding: 5px;">لا يوجد باركود</div>
                    @endif

                    <div class="product-price">{{ number_format($product->b2c_price, 0) }} ₪</div>
                </div>
            @endfor
        @endforeach
    </div>
</body>
</html>
