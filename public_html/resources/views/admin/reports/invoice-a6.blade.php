<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $order->order_number }}</title>
    <style>
        @page { margin: 6px 5px; }
        body { font-family: DejaVu Sans, Tajawal, sans-serif; color: #1c1917; font-size: 6.5px; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 5px; border-bottom: 1.5px solid #d97a8c; padding-bottom: 4px; }
        .header h1 { color: #d97a8c; margin: 0; font-size: 11px; }
        .header .number { font-size: 9px; font-weight: bold; margin-top: 1px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .info-box { flex: 1; padding: 0 3px; }
        .info-box h3 { color: #d97a8c; font-size: 7px; margin: 0 0 1px 0; }
        .info-box p { margin: 1px 0; font-size: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        thead th { background: #d97a8c; color: #fff; padding: 2px 2px; font-size: 6px; text-align: center; }
        tbody td { padding: 2px 2px; border-bottom: 1px solid #f0f0f0; font-size: 6px; text-align: center; }
        tbody td:first-child, tbody td:nth-child(2) { text-align: right; }
        .summary { border-top: 1px solid #d97a8c; margin-top: 4px; padding-top: 4px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 6px; margin-bottom: 1px; }
        .summary-row.total { font-size: 8px; font-weight: bold; color: #d97a8c; }
        .footer { text-align: center; margin-top: 5px; font-size: 6px; color: #a3a3a3; border-top: 1px solid #f0f0f0; padding-top: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</h1>
        <div class="number">فاتورة {{ $order->order_number }}</div>
        <div style="font-size:6px;color:#737373;">{{ $order->created_at->format('Y-m-d H:i') }}</div>
    </div>

    <div class="info-row">
        <div class="info-box">
            <h3>العميل</h3>
            <p><strong>{{ $order->customer_name ?? $order->user?->name ?? 'زائر' }}</strong></p>
            <p>{{ $order->customer_phone ?? $order->user?->phone ?? '' }}</p>
        </div>
        <div class="info-box">
            <h3>الطلب</h3>
            <p>حالة: <strong>{{ $order->status }}</strong> | دفع: <strong>{{ $order->payment_status }}</strong></p>
            <p>طريقة: {{ $order->payment_method ?? 'الدفع عند الاستلام' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="35%">المنتج</th>
                <th width="12%">كمية</th>
                <th width="18%">سعر</th>
                <th width="15%">خصم</th>
                <th width="20%">إجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
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
        <div class="summary-row"><span>المجموع الفرعي</span><span>{{ number_format($order->subtotal, 2) }} ₪</span></div>
        @if($order->discount_amount > 0)
        <div class="summary-row"><span>الخصم</span><span style="color:#DC2626;">- {{ number_format($order->discount_amount, 2) }} ₪</span></div>
        @endif
        <div class="summary-row"><span>الشحن</span><span>{{ number_format($order->shipping_cost ?? 0, 2) }} ₪</span></div>
        <div class="summary-row total"><span>الإجمالي</span><span>{{ number_format($order->total_amount, 2) }} ₪</span></div>
    </div>

    <div class="footer">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }} - شكراً لتعاملكم معنا</div>
</body>
</html>
