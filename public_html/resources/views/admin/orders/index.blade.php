@extends('admin.layouts.app')
@section('title', 'الطلبات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="fas fa-shopping-bag text-pink"></i> الطلبات</h3>
        <p class="text-muted mb-0">إدارة ومتابعة طلبات المتجر</p>
    </div>
    <a href="{{ route('admin.reports.sales.export') }}" class="btn btn-outline-pink btn-sm rounded-pill">
        <i class="fas fa-download"></i> تصدير Excel
    </a>
</div>

{{-- Stats Row --}}
@php
$st = [
    ['icon'=>'fa-shopping-bag','val'=>\App\Models\Order::count(),'label'=>'إجمالي الطلبات','gradient'=>'linear-gradient(135deg, #EC4899, #BE185D)'],
    ['icon'=>'fa-clock','val'=>\App\Models\Order::where('status','pending')->count(),'label'=>'قيد الانتظار','gradient'=>'linear-gradient(135deg, #F59E0B, #B45309)'],
    ['icon'=>'fa-truck','val'=>\App\Models\Order::whereIn('status',['shipped','delivered'])->count(),'label'=>'تم الشحن والتوصيل','gradient'=>'linear-gradient(135deg, #10B981, #047857)'],
    ['icon'=>'fa-check-circle','val'=>\App\Models\Order::where('status','completed')->count(),'label'=>'مكتمل','gradient'=>'linear-gradient(135deg, #3B82F6, #1D4ED8)'],
    ['icon'=>'fa-times-circle','val'=>\App\Models\Order::where('status','cancelled')->count(),'label'=>'ملغي','gradient'=>'linear-gradient(135deg, #EF4444, #991B1B)'],
    ['icon'=>'fa-dollar-sign','val'=>number_format(\App\Models\Order::whereIn('status',['completed','delivered'])->sum('total_amount'),0).' ₪','label'=>'الإيرادات','gradient'=>'linear-gradient(135deg, #8B5CF6, #5B21B6)'],
];
@endphp
<div class="row g-3 mb-4">
    @foreach($st as $s)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="stat-card-new text-white" style="background:{{ $s['gradient'] }}">
            <i class="fas {{ $s['icon'] }}" style="font-size:1.5rem;opacity:.3;position:absolute;top:12px;left:12px;"></i>
            <div style="font-size:1.5rem;font-weight:800;">{{ $s['val'] }}</div>
            <div style="font-size:.78rem;opacity:.9;margin-top:4px;">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Search + Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-1">بحث</label>
                <input type="text" name="search" class="form-control" placeholder="رقم الطلب أو اسم العميل..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-1">الحالة</label>
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>قيد الانتظار</option>
                    <option value="confirmed" {{ request('status')==='confirmed'?'selected':'' }}>مؤكد</option>
                    <option value="processing" {{ request('status')==='processing'?'selected':'' }}>قيد المعالجة</option>
                    <option value="shipped" {{ request('status')==='shipped'?'selected':'' }}>تم الشحن</option>
                    <option value="completed" {{ request('status')==='completed'?'selected':'' }}>مكتمل</option>
                    <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>ملغي</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-1">طريقة الدفع</label>
                <select name="payment" class="form-select">
                    <option value="">الكل</option>
                    <option value="cod" {{ request('payment')==='cod'?'selected':'' }}>الدفع عند الاستلام</option>
                    <option value="bank_transfer" {{ request('payment')==='bank_transfer'?'selected':'' }}>تحويل بنكي</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-pink w-100"><i class="fas fa-search"></i></button>
                @if(request()->anyFilled(['search','status','payment']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Orders Table Card --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="fas fa-list text-pink"></i> قائمة الطلبات</h5>
        <span class="badge bg-light text-dark rounded-pill">{{ $orders->total() }} طلب</span>
    </div>
    <div class="card-body px-0">
        @if($orders->isEmpty())
        <div class="text-center py-5">
            <div style="width:72px;height:72px;border-radius:18px;background:#FDF2F8;display:inline-flex;align-items:center;justify-content:center;">
                <i class="fas fa-inbox" style="font-size:2rem;color:#EC4899;opacity:.4;"></i>
            </div>
            <h5 class="fw-bold mt-3 mb-1">لا توجد طلبات</h5>
            <p class="text-muted small">لا توجد طلبات تطابق معايير البحث</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">رقم الطلب</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>الدفع</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th class="pe-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    @php
                    $sc = ['pending'=>['bg'=>'#FEF3C7','t'=>'#92400E'],'confirmed'=>['bg'=>'#E0F2FE','t'=>'#0284C7'],'processing'=>['bg'=>'#E0E7FF','t'=>'#4338CA'],'shipped'=>['bg'=>'#DBEAFE','t'=>'#1E40AF'],'delivered'=>['bg'=>'#DCFCE7','t'=>'#16A34A'],'completed'=>['bg'=>'#DCFCE7','t'=>'#16A34A'],'cancelled'=>['bg'=>'#FEE2E2','t'=>'#991B1B']];
                    $st = $sc[$order->status] ?? ['bg'=>'#F1F5F9','t'=>'#475569'];
                    $sl = ['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','processing'=>'قيد المعالجة','shipped'=>'تم الشحن','delivered'=>'تم التوصيل','completed'=>'مكتمل','cancelled'=>'ملغي'];
                    $pl = ['cod'=>'استلام','bank_transfer'=>'تحويل','jawwal_pay'=>'جوال باي','reflect'=>'Reflect'];
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $order->order_number ?? $order->id }}</td>
                        <td>{{ $order->customer_name ?? ($order->user->name ?? 'زائر') }}</td>
                        <td>
                            @if($order->order_type==='b2b')
                            <span style="background:#1E293B;color:#fff;padding:2px 10px;border-radius:8px;font-size:.7rem;font-weight:700;">B2B</span>
                            @else
                            <span class="text-muted small">B2C</span>
                            @endif
                        </td>
                        <td class="fw-bold" style="color:#DB2777;">{{ number_format($order->total_amount, 2) }} ₪</td>
                        <td><span class="badge bg-light text-dark rounded-pill small">{{ $pl[$order->payment_method] ?? $order->payment_method }}</span></td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:9999px;font-size:.72rem;font-weight:700;background:{{ $st['bg'] }};color:{{ $st['t'] }};">{{ $sl[$order->status] ?? $order->status }}</span>
                        </td>
                        <td class="text-muted small">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td class="pe-4">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-pink rounded-pill" style="padding:3px 12px;font-size:.75rem;">
                                    <i class="fas fa-eye"></i> عرض
                                </a>
                                @if(!in_array($order->status,['completed','cancelled']))
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary border-0 rounded-pill" data-bs-toggle="dropdown" style="padding:3px 8px;">
                                        <i class="fas fa-ellipsis-v" style="font-size:.7rem;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-start shadow-sm rounded-3 border-0 p-2">
                                        @if($order->status==='pending')
                                        <li><a class="dropdown-item rounded-3 small py-2" href="#" onclick="updateStatus({{ $order->id }},'confirmed')"><i class="fas fa-check text-success" style="width:20px;"></i> تأكيد الطلب</a></li>
                                        @endif
                                        @if(in_array($order->status,['confirmed','processing']))
                                        <li><a class="dropdown-item rounded-3 small py-2" href="#" onclick="updateStatus({{ $order->id }},'shipped')"><i class="fas fa-truck text-primary" style="width:20px;"></i> تم الشحن</a></li>
                                        @endif
                                        <li><a class="dropdown-item rounded-3 small py-2 text-danger" href="#" onclick="if(confirm('إلغاء هذا الطلب؟'))updateStatus({{ $order->id }},'cancelled')"><i class="fas fa-times-circle" style="width:20px;"></i> إلغاء الطلب</a></li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>

{{-- Hidden forms for status updates --}}
@foreach($orders as $order)
<form id="statusForm-{{ $order->id }}" action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-none">
    @csrf @method('PATCH')
    <input type="hidden" name="status" id="statusInput-{{ $order->id }}">
</form>
@endforeach

<script>
function updateStatus(orderId, status) {
    document.getElementById('statusInput-' + orderId).value = status;
    document.getElementById('statusForm-' + orderId).submit();
}
</script>
@endsection
