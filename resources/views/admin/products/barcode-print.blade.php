<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الباركود — {{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
        }

        @media print {
            @page {
                size: @php echo match($layout) { 'a5_12', 'a5_6', 'a5_4' => 'A5', 'a6_8', 'a6_4', 'a6_2' => 'A6', 'thermal_a5' => 'A5', 'thermal_a6' => 'A6', 'custom', 'thermal_custom' => $width . 'mm ' . $height . 'mm', default => 'A4' }; @endphp;
                margin: {{ in_array($layout, ['custom', 'thermal_custom']) ? '0' : (in_array($layout, ['a5_12','a5_6','a5_4','a6_8','a6_4','a6_2']) ? '5mm' : '8mm') }};
            }
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print { display: none !important; }
            .sheet, .label-24, .label-12, .label-6, .label-a5-12, .label-a5-6, .label-a5-4, .label-custom {
                display: block !important;
                overflow: visible !important;
                position: static !important;
                page-break-inside: avoid;
            }
            .barcode-section {
                display: block !important;
                overflow: visible !important;
            }
            .barcode-section svg {
                max-width: 100% !important;
                height: auto !important;
            }
        }

        .print-controls {
            background: #1a1a2e;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .print-controls .title {
            color: #fff;
            font-size: 15px;
            font-weight: 600;
        }
        .print-controls .title span {
            color: #94a3b8;
            font-weight: 400;
        }
        .print-controls button {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background .15s;
        }
        .print-controls button:hover { background: #0b5ed7; }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 8mm 6mm;
            background: white;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 0;
        }
        .sheet.sheet-a5 {
            width: 148mm;
            min-height: 210mm;
            padding: 5mm 4mm;
        }
        .sheet.sheet-a6 {
            width: 105mm;
            min-height: 148mm;
            padding: 4mm 3mm;
        }
        .sheet.custom-layout {
            width: {{ $width }}mm;
            min-height: {{ $height }}mm;
            padding: 1mm;
        }

        @php
            $isCustom = $layout === 'custom';
            $sheetClass = match(true) {
                str_starts_with($layout, 'a5_') => 'sheet-a5',
                str_starts_with($layout, 'a6_') => 'sheet-a6',
                default => '',
            };
            $labelClass = match($layout) {
                'a4_12' => 'label-12',
                'a4_6' => 'label-6',
                'a5_12', 'a6_8' => 'label-a5-12',
                'a5_6', 'a6_4' => 'label-a5-6',
                'a5_4', 'a6_2' => 'label-a5-4',
                'custom' => 'label-custom',
                default => 'label-24',
            };
        @endphp

        .label-24 {
            width: 48mm;
            height: 34mm;
            border: 0.5px solid #d0d0d0;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
            position: relative;
        }
        .label-12 {
            width: 65mm;
            height: 47mm;
            border: 0.5px solid #d0d0d0;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-6 {
            width: 98mm;
            height: 68mm;
            border: 0.5px solid #d0d0d0;
            padding: 4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-a5-12 {
            width: 35mm;
            height: 25mm;
            border: 0.5px solid #d0d0d0;
            padding: 1.5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-a5-6 {
            width: 47mm;
            height: 34mm;
            border: 0.5px solid #d0d0d0;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-a5-4 {
            width: 72mm;
            height: 50mm;
            border: 0.5px solid #d0d0d0;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-custom {
            width: {{ $width }}mm;
            height: {{ $height }}mm;
            border: 0.5px solid #d0d0d0;
            padding: 1.5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            page-break-inside: avoid;
        }

        .brand-line {
            font-size: 7px;
            color: #0d6efd;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .name-line {
            font-size: 9px;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .price-line {
            font-size: 10px;
            font-weight: 800;
            color: #dc2626;
            margin-top: 1px;
        }

        .label-12 .brand-line { font-size: 8px; }
        .label-12 .name-line { font-size: 11px; }
        .label-12 .price-line { font-size: 12px; }

        .label-6 .brand-line { font-size: 10px; }
        .label-6 .name-line { font-size: 14px; }
        .label-6 .price-line { font-size: 15px; }

        .label-a5-12 .brand-line { font-size: 6px; }
        .label-a5-12 .name-line { font-size: 7px; }
        .label-a5-12 .price-line { font-size: 8px; }

        .label-a5-6 .brand-line { font-size: 7px; }
        .label-a5-6 .name-line { font-size: 8px; }
        .label-a5-6 .price-line { font-size: 9px; }

        .label-a5-4 .brand-line { font-size: 9px; }
        .label-a5-4 .name-line { font-size: 12px; }
        .label-a5-4 .price-line { font-size: 13px; }

        .label-custom .brand-line { font-size: 6px; }
        .label-custom .name-line { font-size: 7px; }
        .label-custom .price-line { font-size: 8px; }

        .barcode-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .barcode-section svg {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .info-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <div class="title">
            طباعة الباركود
            <span>— {{ $totalLabels }} ملصق ({{ count($products) }} منتج) | {{ $layout }}</span>
        </div>
        <button onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 5V1h8v4" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/><path d="M12 9h-1M12 13H4v-3h8v3z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="2" y="5" width="12" height="5" rx="1" stroke="#fff" stroke-width="1.5"/></svg>
            طباعة الآن
        </button>
    </div>

    <div class="sheet {{ $sheetClass }} {{ $isCustom ? 'custom-layout' : '' }}">
        @foreach($expanded as $product)
            <div class="{{ $labelClass }}">

                @if($showBrand)
                    <div class="brand-line">{{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</div>
                @endif

                @if($barcodePosition === 'top')
                    @if($product->barcode && $product->barcode_svg)
                        <div class="barcode-section">
                            {!! $product->barcode_svg !!}
                        </div>
                    @else
                        <div style="font-size:9px;color:#dc2626;padding:4px 0;">لا يوجد باركود</div>
                    @endif

                    @if($showName)
                        <div class="info-section" style="margin-top:1px;">
                            <div class="name-line" title="{{ $product->name_ar }}">{{ Str::limit($product->name_ar, 35) }}</div>
                        </div>
                    @endif

                    @if($showPrice)
                        <div class="price-line">{{ number_format($product->b2c_price, 0) }} ₪</div>
                    @endif

                @else
                    @if($showName)
                        <div class="name-line" title="{{ $product->name_ar }}">{{ Str::limit($product->name_ar, 35) }}</div>
                    @endif

                    @if($product->barcode && $product->barcode_svg)
                        <div class="barcode-section" style="margin-top:1px;">
                            {!! $product->barcode_svg !!}
                        </div>
                    @else
                        <div style="font-size:9px;color:#dc2626;padding:4px 0;">لا يوجد باركود</div>
                    @endif

                    @if($showPrice)
                        <div class="price-line" style="margin-top:0;">{{ number_format($product->b2c_price, 0) }} ₪</div>
                    @endif
                @endif

            </div>
        @endforeach
    </div>
</body>
</html>
