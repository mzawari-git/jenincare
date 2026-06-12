<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الباركود حراري — {{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3/dist/JsBarcode.all.min.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js';document.head.appendChild(s)"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #e8e8e8;
            width: 80mm;
            margin: 0;
            padding: 0;
        }
        .paper {
            background: #fff;
            width: 80mm;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            box-shadow: 0 0 0 1px #ccc;
        }

        @media print {
            html { margin: 0; }
            @page { size: 80mm auto; margin: 0; }
            body {
                background: #fff;
                width: 80mm;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .paper {
                background: #fff;
                width: 80mm;
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
            padding: 2mm 4mm;
            text-align: center;
            page-break-inside: avoid;
            page-break-after: always;
        }
        .thermal-label:last-child {
            page-break-after: avoid;
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
        .barcode-section canvas,
        .barcode-section img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
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
            طباعة حراري
            <span style="color:#94a3b8;font-weight:400;">— {{ $totalLabels }} ملصق ({{ count($products) }} منتج)</span>
        </div>
        <button onclick="printLabels()" style="background:#0d6efd;color:white;border:none;padding:8px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:600;">طباعة</button>
    </div>
    <div class="no-print" style="background:#fff3cd;padding:10px 14px;font-size:12px;font-family:'Segoe UI',sans-serif;color:#856404;border-bottom:1px solid #ffc107;text-align:right;">
        <strong>إعدادات Zebra ZD410 في الطباعة (Ctrl+P):</strong><br>
        • <u>Margins: None</u> (أزل الهوامش) &nbsp; • ألغِ <u>Headers and Footers</u><br>
        • <u>Scale: 100</u> (حجم فعلي) &nbsp; • <u>Paper Size: 80mm</u> (حسب تعريف الطابعة)
    </div>
    <div class="paper">
    @foreach($expanded as $product)
        <div class="thermal-label">

            @if($showBrand)
                <div class="brand-line">{{ $siteSettings['site_name'] ?? \App\Helpers\SettingsHelper::siteName() }}</div>
            @endif

            @if($barcodePosition === 'top')

                @if($product->barcode)
                    <div class="barcode-section">
                        <canvas class="bcode" data-code="{{ $product->barcode }}" data-height="60"></canvas>
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

                @if($product->barcode)
                    <div class="barcode-section">
                        <canvas class="bcode" data-code="{{ $product->barcode }}" data-height="60"></canvas>
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
    </div>

    <script>
    var JSB_LOADED = typeof JsBarcode !== 'undefined';

    function barcodeError(canvas, code) {
        var txt = document.createElement('div');
        txt.textContent = code || 'لا يوجد باركود';
        txt.style.cssText = 'font-size:10px;font-weight:bold;font-family:monospace;letter-spacing:1px;color:#333;padding:2px 0;';
        canvas.parentNode.replaceChild(txt, canvas);
    }

    function convertCanvasesToImages() {
        document.querySelectorAll('canvas.bcode').forEach(function(canvas) {
            if (canvas.dataset._converted) return;
            var img = document.createElement('img');
            img.src = canvas.toDataURL();
            img.style.cssText = 'display:block;margin:0 auto;max-width:100%;height:auto;';
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
        if (!JSB_LOADED) {
            console.error('JsBarcode library not loaded from CDN');
            document.querySelectorAll('canvas.bcode').forEach(function(canvas) {
                barcodeError(canvas, canvas.getAttribute('data-code'));
            });
            return;
        }
        document.querySelectorAll('canvas.bcode').forEach(function(canvas) {
            var code = canvas.getAttribute('data-code');
            var h = parseInt(canvas.getAttribute('data-height')) || 55;
            if (!code) return;
            try {
                JsBarcode(canvas, code, {
                    format: 'EAN13',
                    width: 1.5,
                    height: h,
                    displayValue: false,
                    margin: 1,
                    background: '#ffffff',
                });
            } catch(e) {
                try {
                    JsBarcode(canvas, code, {
                        format: 'CODE128',
                        width: 2,
                        height: h,
                        displayValue: false,
                        margin: 1,
                        background: '#ffffff',
                    });
                } catch(e2) {
                    barcodeError(canvas, code);
                }
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
