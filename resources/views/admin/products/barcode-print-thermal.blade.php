<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الباركود — {{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</title>
    @php
        $isThermal = $layout === 'thermal';
        $isA5 = $layout === 'thermal_a5';
        $isA6 = $layout === 'thermal_a6';
        $isCustom = $layout === 'thermal_custom';
        $pageWidth = $isCustom ? ($width ?: 50) . 'mm' : ($isA5 ? '148mm' : ($isA6 ? '105mm' : '80mm'));
        $pageHeight = $isCustom ? ($height ?: 30) . 'mm' : ($isA5 ? '210mm' : ($isA6 ? '148mm' : 'auto'));
        $bodyWidth = $pageWidth;
        $labelPadding = $isA5 ? '4mm 6mm' : ($isA6 ? '3mm 5mm' : '2mm 0');
        $titleText = $isA5 ? 'طباعة A5' : ($isA6 ? 'طباعة A6' : ($isCustom ? 'طباعة مخصص' : 'طباعة ZD410 50×30mm'));
        $paperWidth = $pageWidth;
        $labelInnerWidth = $isThermal ? '50mm' : '100%';
        $labelMinHeight = $isThermal ? '30mm' : 'auto';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: {{ $isThermal ? '#fff' : '#e8e8e8' }};
            width: {{ $bodyWidth }};
            margin: 0;
            padding: 0;
        }
        .paper {
            background: #fff;
            width: {{ $paperWidth }};
            margin: 0;
            padding: 0;
            min-height: {{ $isThermal ? 'auto' : '100vh' }};
            box-shadow: {{ $isThermal ? 'none' : '0 0 0 1px #ccc' }};
        }

        @media print {
            html { margin: 0; }
            @page {
                size: {{ $pageWidth }} @if($isThermal)auto @else {{ $pageHeight }} @endif;
                margin: 0;
            }
            body {
                background: #fff;
                width: {{ $bodyWidth }};
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .paper {
                background: #fff;
                width: {{ $paperWidth }};
                margin: 0;
                padding: 0;
                min-height: auto;
                box-shadow: none;
            }
            .no-print { display: none !important; }
        }

        .thermal-label {
            width: 100%;
            margin: 0;
            padding: {{ $labelPadding }};
            text-align: center;
            page-break-inside: avoid;
            page-break-after: always;
        }
        .thermal-label:last-child {
            page-break-after: avoid;
        }
        .thermal-label-inner {
            width: {{ $labelInnerWidth }};
            min-height: {{ $labelMinHeight }};
            margin: 0 auto;
            padding: 2mm 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-line {
            font-size: 8px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 0;
            line-height: 1.3;
        }
        .name-line {
            font-size: 9px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 0;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.3;
        }
        .price-line {
            font-size: 11px;
            font-weight: bold;
            color: #dc2626;
            margin-top: 0;
            line-height: 1.3;
        }
        .barcode-section {
            margin: 1px auto;
            text-align: center;
        }
        .barcode-section svg {
            max-width: 100%;
            display: block;
            margin: 0 auto;
            height: auto;
        }
        .divider {
            border-top: 1px dashed #d0d0d0;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print" style="background:#1a1a2e;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;font-family:'Segoe UI',sans-serif;position:sticky;top:0;z-index:100;">
        <div style="color:#fff;font-size:14px;font-weight:600;">
            {{ $titleText }}
            <span style="color:#94a3b8;font-weight:400;">— {{ $totalLabels }} ملصق ({{ count($products) }} منتج)</span>
        </div>
        <button onclick="window.print()" style="background:#0d6efd;color:white;border:none;padding:8px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:600;">طباعة</button>
    </div>
    @if($isThermal)
    <div class="no-print" style="background:#fff3cd;padding:10px 14px;font-size:12px;font-family:'Segoe UI',sans-serif;color:#856404;border-bottom:1px solid #ffc107;text-align:right;">
        <strong>إعدادات الطابعة الحرارية في الطباعة (Ctrl+P):</strong><br>
        • <u>Margins: None</u> (أزل الهوامش) &nbsp; • ألغِ <u>Headers and Footers</u><br>
        • <u>Scale: 100</u> (حجم فعلي) &nbsp; • <u>Paper Size: 80mm</u> (حسب تعريف الطابعة)
    </div>
    @endif
    <div class="paper">
    @foreach($expanded as $product)
        <div class="thermal-label">
            <div class="thermal-label-inner">

            @if($showBrand)
                <div class="brand-line">{{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</div>
            @endif

            @if($barcodePosition === 'top')

                @if($product->barcode && $product->barcode_svg)
                    <div class="barcode-section">
                        {!! $product->barcode_svg !!}
                    </div>
                @else
                    <div style="font-size:8px;color:#dc2626;padding:2px 0;">لا يوجد باركود</div>
                @endif

                @if($showName)
                    <div class="name-line">{{ $product->name_ar }}</div>
                @endif

                @if($showPrice)
                    <div class="price-line">{{ number_format($product->b2c_price, 0) }} ₪</div>
                @endif

            @else

                @if($showName)
                    <div class="name-line">{{ $product->name_ar }}</div>
                @endif

                @if($product->barcode && $product->barcode_svg)
                    <div class="barcode-section">
                        {!! $product->barcode_svg !!}
                    </div>
                @else
                    <div style="font-size:9px;color:#dc2626;padding:3px 0;">لا يوجد باركود</div>
                @endif

                @if($showPrice)
                    <div class="price-line">{{ number_format($product->b2c_price, 0) }} ₪</div>
                @endif

            @endif

            </div>
        </div>
        @if(!$loop->last)
            <div class="divider"></div>
        @endif
    @endforeach
    </div>
</body>
</html>
