<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $order->order_number }}</title>
    <style>
        @page { margin: 12px 10px; }
        body { font-family: DejaVu Sans, Tajawal, sans-serif; color: #1c1917; font-size: 8px; line-height: 1.4; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; border-bottom: 1.5px solid #d97a8c; padding-bottom: 6px; }
        .header-left h1 { color: #d97a8c; margin: 0; font-size: 14px; }
        .header-left .company { font-size: 7px; color: #737373; }
        .header-right { text-align: left; }
        .header-right h2 { color: #d97a8c; margin: 0; font-size: 16px; }
        .header-right .number { font-size: 11px; font-weight: bold; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; gap: 8px; }
        .info-box { flex: 1; }
        .info-box h3 { color: #d97a8c; font-size: 9px; margin: 0 0 3px 0; border-bottom: 1px solid #f0f0f0; padding-bottom: 2px; }
        .info-box p { margin: 1px 0; font-size: 7px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        thead th { background: #d97a8c; color: #fff; padding: 4px 3px; font-size: 7px; text-align: center; }
        thead th:first-child, thead th:nth-child(2) { text-align: right; }
        tbody td { padding: 3px 3px; border-bottom: 1px solid #f0f0f0; font-size: 7px; text-align: center; }
        tbody td:first-child, tbody td:nth-child(2) { text-align: right; }
        tbody tr:nth-child(even) { background: #fdf8f9; }
        .summary { display: flex; justify-content: flex-end; }
        .summary-box { width: 55%; }
        .summary-box table { width: 100%; }
        .summary-box td { padding: 2px 5px; font-size: 8px; }
        .summary-box .total td { font-size: 11px; font-weight: bold; color: #d97a8c; border-top: 1px solid #d97a8c; }
        .footer { text-align: center; margin-top: 10px; padding-top: 5px; border-top: 1px solid #f0f0f0; font-size: 7px; color: #a3a3a3; }
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
            <div style="font-size:7px;color:#737373;">{{ $order->created_at->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="info-row">
        <div class="info-box">
            <h3>العميل</h3>
            <p><strong>{{ $order->customer_name ?? $order->user?->name ?? 'زائر' }}</strong></p>
            @if($order->customer_phone || $order->user?->phone)<p>{{ $order->customer_phone ?? $order->user?->phone ?? '' }}</p>@endif
            @if($order->shipping_address)<p style="color:#737373;">{{ $order->shipping_address }}, {{ $order->shipping_city ?? '' }}</p>@endif
        </div>
        <div class="info-box">
            <h3>الطلب</h3>
            <p>الحالة: <strong>{{ $order->status }}</strong></p>
            <p>الدفع: <strong>{{ $order->payment_status }}</strong></p>
            @if($order->payment_method)<p>طريقة: {{ $order->payment_method }}</p>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="40%">المنتج</th>
                <th width="10%">الكمية</th>
                <th width="15%">سعر الوحدة</th>
                <th width="10%">خصم</th>
                <th width="20%">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }} ₪</td>
                <td>{{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '-' }}</td>
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
                <tr class="total"><td>الإجمالي</td><td style="text-align:left;">{{ number_format($order->total_amount, 2) }} ₪</td></tr>
            </table>
        </div>
    </div>

    <div class="footer">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }} - شكراً لتعاملكم معنا</div>
</body>
</html>
