<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الباركود حراري — {{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            width: 80mm;
            margin: 0 auto;
        }

        @media print {
            @page { size: 80mm auto; margin: 2mm; }
            body { width: 80mm; }
            .no-print { display: none !important; }
        }

        .print-controls {
            background: #1a1a2e;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .print-controls .title {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }
        .print-controls .title span {
            color: #94a3b8;
            font-weight: 400;
        }
        .print-controls button {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
            transition: background .15s;
        }
        .print-controls button:hover { background: #0b5ed7; }

        .thermal-label {
            width: 74mm;
            margin: 2mm auto;
            padding: 3mm 2mm;
            text-align: center;
            page-break-inside: avoid;
            page-break-after: always;
        }
        .thermal-label:last-child {
            page-break-after: avoid;
        }

        .brand-line {
            font-size: 9px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 1px;
        }
        .name-line {
            font-size: 10px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 1px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .price-line {
            font-size: 12px;
            font-weight: bold;
            color: #dc2626;
            margin-top: 1px;
        }
        .barcode-section {
            margin: 2px auto;
            text-align: center;
        }
        .barcode-section canvas,
        .barcode-section img {
            max-width: 92%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .divider {
            border-top: 1px dashed #d0d0d0;
            margin: 1mm 0;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <div class="title">
            طباعة حراري
            <span>— {{ $totalLabels }} ملصق ({{ count($products) }} منتج)</span>
        </div>
        <button onclick="printLabels()">طباعة</button>
    </div>

    @foreach($expanded as $product)
        <div class="thermal-label">

            @if($showBrand)
                <div class="brand-line">{{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</div>
            @endif

            @if($barcodePosition === 'top')

                @if($product->barcode)
                    <div class="barcode-section">
                        <canvas class="bcode" data-code="{{ $product->barcode }}" data-height="80"></canvas>
                    </div>
                @else
                    <div style="font-size:9px;color:#dc2626;padding:3px 0;">لا يوجد باركود</div>
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

                @if($product->barcode)
                    <div class="barcode-section">
                        <canvas class="bcode" data-code="{{ $product->barcode }}" data-height="80"></canvas>
                    </div>
                @else
                    <div style="font-size:9px;color:#dc2626;padding:3px 0;">لا يوجد باركود</div>
                @endif

                @if($showPrice)
                    <div class="price-line">{{ number_format($product->b2c_price, 0) }} ₪</div>
                @endif

            @endif

        </div>
        @if(!$loop->last)
            <div class="divider"></div>
        @endif
    @endforeach

    <script>
    function convertCanvasesToImages() {
        document.querySelectorAll('canvas.bcode').forEach(function(canvas) {
            if (canvas.dataset._converted) return;
            var img = document.createElement('img');
            img.src = canvas.toDataURL();
            img.style.cssText = 'display:block;margin:0 auto;max-width:92%;height:auto;';
            img.className = canvas.className;
            canvas.parentNode.replaceChild(img, canvas);
            img.dataset._converted = '1';
        });
    }

    function printLabels() {
        convertCanvasesToImages();
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('canvas.bcode').forEach(function(canvas) {
            var code = canvas.getAttribute('data-code');
            var h = parseInt(canvas.getAttribute('data-height')) || 55;
            if (!code) return;
            try {
                JsBarcode(canvas, code, {
                    format: 'EAN13',
                    width: 2,
                    height: h,
                    displayValue: false,
                    margin: 2,
                    background: '#ffffff',
                });
            } catch(e) {
                try {
                    JsBarcode(canvas, code, {
                        format: 'CODE128',
                        width: 1.5,
                        height: h,
                        displayValue: false,
                        margin: 2,
                        background: '#ffffff',
                    });
                } catch(e2) {}
            }
        });

        if (window.matchMedia) {
            window.matchMedia('print').addEventListener('change', function(mql) {
                if (mql.matches) convertCanvasesToImages();
            });
        }
    });
    </script>
</body>
</html>
