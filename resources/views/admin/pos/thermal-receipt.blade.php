<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة حرارية — {{ $siteSettings['site_name'] ?? 'جنين للتجميل' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            width: 80mm;
            margin: 0 auto;
            background: #fff;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.35;
            color: #1a1a1a;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 2mm;
            }
            body {
                width: 80mm;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        .print-controls {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, sans-serif;
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
        }

        .print-controls button:hover { background: #0b5ed7; }

        .receipt {
            width: 100%;
            padding: 2mm 3mm;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 4mm;
            margin-bottom: 3mm;
        }

        .receipt-logo {
            max-width: 50px;
            max-height: 50px;
            margin-bottom: 2px;
        }

        .receipt-store-name {
            font-size: 14px;
            font-weight: bold;
            color: #db2777;
            margin-bottom: 1px;
        }

        .receipt-tax-number {
            font-size: 8px;
            color: #666;
        }

        .receipt-title {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }

        .receipt-meta {
            font-size: 8px;
            color: #555;
            margin-top: 1px;
        }

        .receipt-customer {
            font-size: 9px;
            margin-bottom: 2mm;
            padding: 1mm 0;
            border-bottom: 1px dotted #ddd;
        }

        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
        }

        .receipt-items thead th {
            font-size: 8px;
            color: #555;
            border-bottom: 1px solid #333;
            padding: 1mm 0;
            text-align: center;
        }

        .receipt-items thead th:first-child {
            text-align: right;
        }

        .receipt-items tbody td {
            padding: 1mm 0;
            border-bottom: 1px dotted #eee;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
        }

        .receipt-items tbody td:first-child {
            text-align: right;
        }

        .product-inline-thumb {
            width: 18px;
            height: 18px;
            border-radius: 2px;
            object-fit: cover;
            vertical-align: middle;
            margin-left: 2px;
        }

        .receipt-totals {
            border-top: 1px solid #333;
            padding-top: 2mm;
            margin-bottom: 2mm;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            padding: 0.5mm 0;
        }

        .total-row.discount {
            color: #dc2626;
        }

        .total-row.tax {
            color: #555;
        }

        .total-row.grand-total {
            font-size: 13px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 1mm;
            margin-top: 1mm;
        }

        .receipt-qr {
            text-align: center;
            margin: 2mm 0;
        }

        .receipt-qr canvas, .receipt-qr img {
            width: 50px !important;
            height: 50px !important;
        }

        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #333;
            padding-top: 2mm;
            margin-top: 2mm;
            font-size: 9px;
            color: #888;
        }

        .receipt-barcode {
            text-align: center;
            margin: 2mm 0;
        }

        .receipt-barcode img {
            max-width: 80%;
            height: auto;
        }

        .divider-dash {
            border-top: 1px dashed #ccc;
            margin: 2mm 0;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <div>
            <strong>معاينة الفاتورة الحرارية</strong>
            <span class="text-muted">— {{ $sale->pos_sale_id ?? '' }}</span>
        </div>
        <button onclick="window.print()">
            <i class="fas fa-print"></i> طباعة
        </button>
    </div>

    @php
        $settings = [
            'showLogo' => true,
            'showQR' => true,
            'showTaxNumber' => true,
            'showProductImages' => true,
            'showCustomerInfo' => true,
            'showContactInfo' => true,
        ];
        $subtotal = (float) ($sale->subtotal ?? 0);
        $discount = (float) ($sale->discount_amount ?? 0);
        $taxAmount = (float) ($sale->tax_amount ?? 0);
        $taxRate = (float) ($sale->tax_rate ?? 0);
        $items = is_array($sale->items) ? $sale->items : (is_string($sale->items) ? json_decode($sale->items, true) : []);
        $totalItemDiscount = collect($items)->sum(fn($i) => (float) ($i['item_discount'] ?? 0));
        $netTotal = $subtotal - $discount - $totalItemDiscount;
        $grandTotal = $netTotal + $taxAmount;
        $siteName = $siteSettings['site_name'] ?? 'جنين للتجميل';
        $taxNumber = $siteSettings['tax_number'] ?? '';
        $sitePhone = $siteSettings['contact_phone'] ?? $siteSettings['site_phone'] ?? '';
        $siteAddress = $siteSettings['address'] ?? $siteSettings['site_address'] ?? '';
        $currencySymbol = ($sale->currency ?? 'ILS') === 'ILS' ? '₪' : (($sale->currency ?? 'ILS') === 'USD' ? '$' : ($sale->currency ?? '₪'));
    @endphp

    <div class="receipt">
        {{-- Header --}}
        <div class="receipt-header">
            @if($settings['showLogo'])
                @php $logoUrl = \App\Helpers\SettingsHelper::siteLogo(); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="receipt-logo">
                @endif
            @endif
            <div class="receipt-store-name">{{ $siteName }}</div>
            @if($settings['showContactInfo'] ?? true)
                @if(!empty($sitePhone))
                    <div class="receipt-tax-number">📞 {{ $sitePhone }}</div>
                @endif
                @if(!empty($siteAddress))
                    <div class="receipt-tax-number">📍 {{ $siteAddress }}</div>
                @endif
            @endif
            @if($settings['showTaxNumber'] && $taxNumber)
                <div class="receipt-tax-number">الرقم الضريبي: {{ $taxNumber }}</div>
            @endif
            <div class="receipt-title">فاتورة مبيعات</div>
            <div class="receipt-meta">
                رقم: {{ $sale->pos_sale_id }}<br>
                التاريخ: {{ $sale->created_at instanceof \Carbon\Carbon ? $sale->created_at->format('Y-m-d H:i') : (is_string($sale->created_at) ? $sale->created_at : now()->format('Y-m-d H:i')) }}
                @if($sale->payment_method)
                    <br>دفع: {{ $sale->payment_method === 'cash' ? 'نقداً' : ($sale->payment_method === 'card' ? 'بطاقة' : ($sale->payment_method === 'transfer' ? 'تحويل' : $sale->payment_method)) }}
                @endif
            </div>
        </div>

        {{-- Customer --}}
        @if($settings['showCustomerInfo'] && ($sale->customer_name || $sale->customer_phone))
            <div class="receipt-customer">
                <strong>العميل:</strong> {{ $sale->customer_name ?? '-' }}
                @if($sale->customer_phone) | {{ $sale->customer_phone }} @endif
            </div>
        @endif

        {{-- Items Table --}}
        <table class="receipt-items">
            <thead>
                <tr>
                    <th width="40%">المنتج</th>
                    <th width="15%">الكمية</th>
                    <th width="20%">السعر</th>
                    <th width="25%">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        @if($settings['showProductImages'] && !empty($item['image']))
                            <img src="{{ $item['image'] }}" class="product-inline-thumb" alt="">
                        @endif
                        {{ $item['name'] ?? $item['product_name'] ?? '' }}
                    </td>
                    <td>{{ $item['quantity'] ?? 1 }}</td>
                    <td>{{ number_format((float)($item['price'] ?? 0), 2) }}</td>
                    <td><strong>{{ number_format((float)($item['total'] ?? ((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1))), 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="receipt-totals">
            <div class="total-row">
                <span>المجموع الفرعي</span>
                <span>{{ number_format($subtotal, 2) }} {{ $currencySymbol }}</span>
            </div>
            @if($discount > 0)
            <div class="total-row discount">
                <span>الخصم</span>
                <span>- {{ number_format($discount, 2) }} {{ $currencySymbol }}</span>
            </div>
            @endif
            @if($totalItemDiscount > 0)
            <div class="total-row discount">
                <span>خصم المنتجات</span>
                <span>- {{ number_format($totalItemDiscount, 2) }} {{ $currencySymbol }}</span>
            </div>
            @endif
            @if($taxAmount > 0)
            <div class="total-row tax">
                <span>ضريبة {{ $taxRate * 100 }}%</span>
                <span>{{ number_format($taxAmount, 2) }} {{ $currencySymbol }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>الإجمالي</span>
                <span>{{ number_format($grandTotal, 2) }} {{ $currencySymbol }}</span>
            </div>
        </div>

        {{-- QR Code --}}
        @if($settings['showQR'])
            <div class="receipt-qr">
                <div id="receiptQR"></div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="receipt-footer">
            <div>شكراً لتعاملكم معنا</div>
            <div style="font-size:8px;margin-top:1px;">{{ $siteName }}</div>
            <div style="font-size:7px;margin-top:1px;letter-spacing:1px;">{{ $sale->pos_sale_id }}</div>
        </div>
    </div>

    <script>
        @if($settings['showQR'])
        document.addEventListener('DOMContentLoaded', function() {
            var qrDiv = document.getElementById('receiptQR');
            if (typeof QRCode !== 'undefined') {
                new QRCode(qrDiv, {
                    text: '{{ $sale->pos_sale_id }}',
                    width: 50,
                    height: 50
                });
            }
        });
        @endif
    </script>
</body>
</html>
