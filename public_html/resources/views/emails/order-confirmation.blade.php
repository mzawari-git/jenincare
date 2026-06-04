<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الطلب</title>
    <style>
        body { font-family: 'Tajawal', Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 24px; margin-bottom: 24px; }
        .header h1 { font-size: 24px; color: #1c1917; margin: 0 0 4px; }
        .header p { color: #737373; font-size: 14px; margin: 0; }
        .status-badge { display: inline-block; background: #DCFCE7; color: #16A34A; padding: 4px 16px; border-radius: 50px; font-size: 13px; font-weight: 700; }
        .details { margin-bottom: 24px; }
        .details h3 { font-size: 16px; color: #1c1917; margin: 0 0 12px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #525252; border-bottom: 1px solid #f5f5f5; }
        .total { display: flex; justify-content: space-between; padding: 12px 0; font-size: 18px; font-weight: 800; color: #1c1917; border-top: 2px solid #e5e5e5; margin-top: 8px; }
        .footer { text-align: center; padding-top: 24px; color: #a3a3a3; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #faf9f8; padding: 10px 12px; font-size: 12px; color: #737373; text-align: right; }
        td { padding: 10px 12px; font-size: 13px; color: #525252; border-bottom: 1px solid #f5f5f5; }
        a { color: #d97a8c; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>شكراً لطلبك من شركة جنين للتجميل!</h1>
                <p>تم تأكيد طلبك بنجاح</p>
                <div style="margin-top: 12px;">
                    <span class="status-badge">قيد الانتظار</span>
                </div>
                <p style="margin-top: 12px; font-size: 20px; font-weight: 800; color: #d97a8c;">#{{ $order->order_number }}</p>
            </div>

            <div class="details">
                <h3>معلومات الطلب</h3>
                <div class="row"><span>الاسم</span><span style="font-weight:600;">{{ $order->customer_name }}</span></div>
                <div class="row"><span>رقم الهاتف</span><span dir="ltr">{{ $order->customer_phone }}</span></div>
                <div class="row"><span>البريد الإلكتروني</span><span>{{ $order->customer_email }}</span></div>
                <div class="row"><span>العنوان</span><span>{{ $order->shipping_address }}، {{ $order->shipping_city }}</span></div>
                @if($order->shipping_notes)
                <div class="row"><span>ملاحظات</span><span>{{ $order->shipping_notes }}</span></div>
                @endif
            </div>

            <h3>المنتجات</h3>
            <table>
                <thead>
                    <tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} ₪</td>
                        <td>{{ number_format($item->total, 2) }} ₪</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">
                <span>الإجمالي</span>
                <span style="color:#d97a8c;">{{ number_format($order->total_amount, 2) }} ₪</span>
            </div>

            @if(in_array($order->payment_method, ['bank_transfer', 'jawwal_pay', 'reflect']))
            <div style="background:#F0F9FF;border:1px solid #BFDBFE;border-radius:12px;padding:16px;margin-top:16px;font-size:13px;color:#1E40AF;">
                <strong>ملاحظة:</strong> سيتم تأكيد طلبك بعد استلام الدفع. يرجى إرسال إيصال الدفع عبر واتساب.
            </div>
            @endif

            <div class="footer">
                <p>شركة جنين للتجميل - <?php echo date('Y'); ?></p>
                <p style="margin-top:4px;">{{ url('/') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
