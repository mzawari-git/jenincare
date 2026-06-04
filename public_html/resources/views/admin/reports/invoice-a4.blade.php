<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $order->order_number }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: DejaVu Sans, Tajawal, sans-serif; color: #1c1917; font-size: 12px; line-height: 1.6; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #d97a8c; padding-bottom: 15px; }
        .header-left h1 { color: #d97a8c; margin: 0; font-size: 22px; }
        .header-left .company { font-size: 11px; color: #737373; }
        .header-right { text-align: left; }
        .header-right h2 { color: #d97a8c; margin: 0; font-size: 24px; }
        .header-right .number { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-box { width: 48%; }
        .info-box h3 { color: #d97a8c; font-size: 13px; margin: 0 0 8px 0; border-bottom: 1px solid #f0f0f0; padding-bottom: 4px; }
        .info-box p { margin: 2px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #d97a8c; color: #fff; padding: 8px 6px; font-size: 11px; text-align: center; }
        thead th:first-child, thead th:nth-child(2) { text-align: right; }
        tbody td { padding: 7px 6px; border-bottom: 1px solid #f0f0f0; font-size: 11px; text-align: center; }
        tbody td:first-child, tbody td:nth-child(2) { text-align: right; }
        tbody tr:nth-child(even) { background: #fdf8f9; }
        .summary { display: flex; justify-content: flex-end; }
        .summary-box { width: 50%; }
        .summary-box table { width: 100%; }
        .summary-box td { padding: 5px 10px; font-size: 12px; }
        .summary-box .total td { font-size: 16px; font-weight: bold; color: #d97a8c; border-top: 1px solid #d97a8c; }
        .footer { text-align: center; margin-top: 25px; padding-top: 10px; border-top: 1px solid #f0f0f0; font-size: 10px; color: #a3a3a3; }
        .notes { font-size: 10px; color: #737373; margin-top: 15px; padding: 10px; background: #faf9f8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</h1>
            <div class="company">{{ $siteSettings['site_description'] ?? '' }}</div>
        </div>
        <div class="header-right">
            <h2>فاتورة</h2>
            <div class="number">{{ $order->order_number }}</div>
            <div style="font-size:10px;color:#737373;">التاريخ: {{ $order->created_at->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="info-row">
        <div class="info-box">
            <h3>معلومات العميل</h3>
            <p><strong>{{ $order->customer_name ?? $order->user?->name ?? 'زائر' }}</strong></p>
            @if($order->customer_email || $order->user?->email)<p>{{ $order->customer_email ?? $order->user?->email ?? '' }}</p>@endif
            @if($order->customer_phone || $order->user?->phone)<p>{{ $order->customer_phone ?? $order->user?->phone ?? '' }}</p>@endif
            @if($order->shipping_address)<p style="color:#737373;">{{ $order->shipping_address }}, {{ $order->shipping_city ?? '' }}</p>@endif
        </div>
        <div class="info-box">
            <h3>معلومات الطلب</h3>
            <p>الحالة: <strong>{{ $order->status }}</strong></p>
            <p>حالة الدفع: <strong>{{ $order->payment_status }}</strong></p>
            @if($order->payment_method)<p>طريقة الدفع: {{ $order->payment_method }}</p>@endif
            @if($order->tracking_number)<p>رقم التتبع: {{ $order->tracking_number }}</p>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="38%">المنتج</th>
                <th width="12%">الرمز</th>
                <th width="10%">الكمية</th>
                <th width="12%">سعر الوحدة</th>
                <th width="10%">الخصم</th>
                <th width="13%">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product_name }}</td>
                <td style="font-family:monospace;font-size:10px;">{{ $item->product_sku }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }} ₪</td>
                <td>{{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) . ' ₪' : '-' }}</td>
                <td><strong>{{ number_format($item->total, 2) }} ₪</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-box">
            <table>
                <tr><td style="color:#737373;">المجموع الفرعي</td><td style="text-align:left;">{{ number_format($order->subtotal, 2) }} ₪</td></tr>
                @if($order->discount_amount > 0)
                <tr><td style="color:#737373;">الخصم</td><td style="text-align:left;color:#DC2626;">- {{ number_format($order->discount_amount, 2) }} ₪</td></tr>
                @endif
                <tr><td style="color:#737373;">الشحن</td><td style="text-align:left;">{{ number_format($order->shipping_cost ?? 0, 2) }} ₪</td></tr>
                @if($order->tax_amount > 0)
                <tr><td style="color:#737373;">الضريبة</td><td style="text-align:left;">{{ number_format($order->tax_amount, 2) }} ₪</td></tr>
                @endif
                <tr class="total"><td>الإجمالي</td><td style="text-align:left;">{{ number_format($order->total_amount, 2) }} ₪</td></tr>
            </table>
        </div>
    </div>

    @if($order->shipping_notes)
    <div class="notes"><strong>ملاحظات الشحن:</strong> {{ $order->shipping_notes }}</div>
    @endif

    <div class="footer">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }} - شكراً لتعاملكم معنا | {{ $order->created_at->format('Y-m-d H:i') }}</div>
</body>
</html>
