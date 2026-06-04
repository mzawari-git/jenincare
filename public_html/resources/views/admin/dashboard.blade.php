@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@push('styles')
<style>
.analytics-card { background: linear-gradient(135deg, #fff 0%, #f8fafc 100%); border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; }
.analytics-value { font-size: 2rem; font-weight: 800; color: var(--gray-800); }
.analytics-label { font-size: .875rem; color: var(--gray-500); font-weight: 500; }
.analytics-change { font-size: .75rem; padding: 4px 8px; border-radius: 20px; font-weight: 600; }
.analytics-change.up { background: #dcfce7; color: #16a34a; }
.analytics-change.down { background: #fee2e2; color: #dc2626; }
.chart-container { position: relative; height: 300px; }
.chart-container-sm { position: relative; height: 200px; }
.order-status-btn { border-radius: 12px; padding: 12px 16px; border: 1px solid #e2e8f0; background: #fff; transition: all .25s; }
.order-status-btn:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); transform: translateY(-1px); }
.order-status-btn .count { font-size: 1.35rem; font-weight: 800; color: var(--gray-800); }
.order-status-btn .label { font-size: .75rem; color: var(--gray-500); }
.activity-item { padding: .75rem 1rem; border-bottom: 1px solid var(--gray-50); display: flex; gap: .75rem; align-items: flex-start; }
.activity-item:last-child { border-bottom: none; }
.activity-item .activity-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
.activity-item .activity-text { font-size: .8rem; color: var(--gray-700); }
.activity-item .activity-time { font-size: .7rem; color: var(--gray-400); white-space: nowrap; }
</style>
@endpush

@section('content')
{{-- Quick Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-icon-new" style="background: linear-gradient(135deg, #fdf2f8, #fce7f3); color: #db2777;">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <span class="analytics-change {{ $analytics['revenueGrowth'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $analytics['revenueGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($analytics['revenueGrowth']) }}%
                </span>
            </div>
            <div class="stat-value-new">{{ number_format($totalOrders) }}</div>
            <div class="stat-label-new">إجمالي الطلبات</div>
            <div class="mt-2 text-muted small">+{{ $todayOrders }} اليوم</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-icon-new" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a;">
                    <i class="fas fa-shekel-sign"></i>
                </div>
                <span class="analytics-change up">
                    <i class="fas fa-arrow-up"></i> {{ number_format($analytics['avgOrderValue']) }}
                </span>
            </div>
            <div class="stat-value-new">{{ number_format($totalRevenue, 0) }} <small style="font-size:1rem;color:var(--gray-400)">₪</small></div>
            <div class="stat-label-new">إجمالي الإيرادات</div>
            <div class="mt-2 text-muted small">{{ number_format($todayRevenue) }} ₪ اليوم</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-icon-new" style="background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0284c7;">
                    <i class="fas fa-users"></i>
                </div>
                <span class="analytics-change up">
                    <i class="fas fa-arrow-up"></i> {{ $analytics['newCustomers'] }}
                </span>
            </div>
            <div class="stat-value-new">{{ number_format($totalCustomers) }}</div>
            <div class="stat-label-new">العملاء المسجلين</div>
            <div class="mt-2 text-muted small">{{ $analytics['returningCustomers'] }} عميل متكرر</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-icon-new" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706;">
                    <i class="fas fa-box"></i>
                </div>
                <span class="badge bg-{{ $lowStockProducts > 0 ? 'warning' : 'success' }} rounded-pill">
                    {{ $lowStockProducts > 0 ? $lowStockProducts . ' منخفض' : 'ممتاز' }}
                </span>
            </div>
            <div class="stat-value-new">{{ number_format($totalProducts) }}</div>
            <div class="stat-label-new">المنتجات النشطة</div>
            <div class="mt-2 text-muted small">{{ $outOfStockProducts }} نفد من المخزون</div>
        </div>
    </div>
</div>

{{-- Order Status Quick Actions --}}
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="order-status-btn d-flex align-items-center gap-3 text-decoration-none">
            <div style="width:4px;height:32px;border-radius:2px;background:#f59e0b;flex-shrink:0;"></div>
            <div>
                <div class="count">{{ $chartData['status']['pending'] }}</div>
                <div class="label">قيد الانتظار</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="order-status-btn d-flex align-items-center gap-3 text-decoration-none">
            <div style="width:4px;height:32px;border-radius:2px;background:#3b82f6;flex-shrink:0;"></div>
            <div>
                <div class="count">{{ $chartData['status']['processing'] }}</div>
                <div class="label">قيد المعالجة</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="order-status-btn d-flex align-items-center gap-3 text-decoration-none">
            <div style="width:4px;height:32px;border-radius:2px;background:#8b5cf6;flex-shrink:0;"></div>
            <div>
                <div class="count">{{ $chartData['status']['shipped'] }}</div>
                <div class="label">تم الشحن</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="order-status-btn d-flex align-items-center gap-3 text-decoration-none">
            <div style="width:4px;height:32px;border-radius:2px;background:#10b981;flex-shrink:0;"></div>
            <div>
                <div class="count">{{ $chartData['status']['completed'] }}</div>
                <div class="label">مكتمل</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="order-status-btn d-flex align-items-center gap-3 text-decoration-none">
            <div style="width:4px;height:32px;border-radius:2px;background:#ef4444;flex-shrink:0;"></div>
            <div>
                <div class="count">{{ $chartData['status']['cancelled'] }}</div>
                <div class="label">ملغي</div>
            </div>
        </a>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-line" style="color:var(--pink-600);margin-left:6px;"></i> أداء المبيعات (30 يوم)</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="toggleChart('revenue')">الإيرادات</button>
                    <button class="btn btn-outline-secondary" onclick="toggleChart('orders')">الطلبات</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-chart-pie" style="color:var(--pink-600);margin-left:6px;"></i> توزيع حالة الطلبات</div>
            <div class="card-body">
                <div class="chart-container-sm">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Analytics & B2B Row --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-analytics" style="color:var(--pink-600);margin-left:6px;"></i> تحليلات الأداء</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="analytics-card">
                            <div class="analytics-value" style="font-size:1.5rem;">{{ number_format($analytics['avgOrderValue']) }} ₪</div>
                            <div class="analytics-label">متوسط قيمة الطلب</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="analytics-card">
                            <div class="analytics-value" style="font-size:1.5rem;">{{ $analytics['conversionRate'] }}%</div>
                            <div class="analytics-label">معدل التحويل</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="analytics-card">
                            <div class="analytics-value" style="font-size:1.5rem;">{{ number_format($analytics['customerLifetimeValue']) }} ₪</div>
                            <div class="analytics-label">قيمة العميل مدى الحياة</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="analytics-card">
                            <div class="analytics-value" style="font-size:1.5rem;">{{ $analytics['cartAbandonment'] }}</div>
                            <div class="analytics-label">سلات مهجورة +24س</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-building" style="color:var(--pink-600);margin-left:6px;"></i> إحصائيات B2B</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-4 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--gray-800);">{{ $b2bStats['totalCompanies'] }}</div>
                        <div style="font-size:.8rem;color:var(--gray-500);">الشركات</div>
                    </div>
                    <div class="col-4 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--pink-600);">{{ number_format($b2bStats['b2bRevenue'], 0) }} ₪</div>
                        <div style="font-size:.8rem;color:var(--gray-500);">مبيعات B2B</div>
                    </div>
                    <div class="col-4 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--gray-800);">{{ $b2bStats['b2bOrders'] }}</div>
                        <div style="font-size:.8rem;color:var(--gray-500);">طلبات B2B</div>
                    </div>
                </div>
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">الائتمان المستخدم</span>
                        <span class="small fw-bold">{{ number_format($b2bStats['usedCredit']) }} / {{ number_format($b2bStats['totalCredit']) }} ₪</span>
                    </div>
                    <div class="progress-thin">
                        @php
                            $creditPercent = $b2bStats['totalCredit'] > 0 ? ($b2bStats['usedCredit'] / $b2bStats['totalCredit']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-{{ $creditPercent > 80 ? 'danger' : ($creditPercent > 60 ? 'warning' : 'success') }}" style="width: {{ $creditPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Category Sales & Top Cities --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-tags" style="color:var(--pink-600);margin-left:6px;"></i> مبيعات حسب الفئة</div>
            <div class="card-body">
                <div class="chart-container-sm">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-map-marker-alt" style="color:var(--pink-600);margin-left:6px;"></i> أكثر المدن طلباً</div>
            <div class="card-body">
                @forelse($analytics['topCities'] as $city => $count)
                @php
                    $maxCount = $analytics['topCities']->max();
                    $percent = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold">{{ $city }}</span>
                        <span class="text-muted small">{{ $count }} طلب</span>
                    </div>
                    <div class="progress-thin">
                        <div class="progress-bar" style="width: {{ $percent }}%; background: linear-gradient(90deg, var(--pink-500), var(--pink-600));"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">لا توجد بيانات كافية</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Delivery Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-truck" style="color:var(--pink-600);margin-left:6px;"></i> إحصائيات التوصيل</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.delivery') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="fas fa-chart-bar"></i> تقرير التوصيل
                    </a>
                    <a href="{{ route('admin.deliveries.index') }}" class="btn btn-sm btn-outline-pink rounded-pill">
                        إدارة التوصيل <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--gray-800);">{{ $deliveryStats['totalDeliveries'] }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500);">إجمالي التوصيلات</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:#f59e0b;">{{ $deliveryStats['pendingDeliveries'] }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500);">قيد الانتظار</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:#3b82f6;">{{ $deliveryStats['activeDeliveries'] }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500);">قيد التوصيل</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:#10b981;">{{ $deliveryStats['completedDeliveries'] }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500);">تم التوصيل</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:#ef4444;">{{ $deliveryStats['failedDeliveries'] }}</div>
                        <div style="font-size:.75rem;color:var(--gray-500);">فشل / مرتجع</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--pink-600);">{{ $deliveryStats['successRate'] }}%</div>
                        <div style="font-size:.75rem;color:var(--gray-500);">نسبة النجاح</div>
                    </div>
                </div>
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">معدل نجاح التوصيل</span>
                        <span class="small fw-bold">{{ $deliveryStats['successRate'] }}% ({{ $deliveryStats['completedDeliveries'] }} من {{ $deliveryStats['totalDeliveries'] }})</span>
                    </div>
                    <div class="progress-thin">
                        <div class="progress-bar bg-success" style="width: {{ $deliveryStats['successRate'] }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">{{ $deliveryStats['todayDeliveries'] }} توصيلة اليوم</small>
                        <small class="text-success">{{ $deliveryStats['todayCompleted'] }} تم توصيلها اليوم</small>
                    </div>
                </div>
                @if($deliveryStats['drivers']->isNotEmpty())
                <div class="border-top pt-3 mt-3">
                    <div class="row g-2">
                        @foreach($deliveryStats['drivers'] as $driver => $count)
                        <div class="col-md text-center">
                            <div class="p-2 bg-light rounded-3">
                                <div class="fw-bold small">{{ $driver }}</div>
                                <div class="badge rounded-pill" style="background:#d97a8c;color:#fff;font-size:.7rem;">{{ $count }} توصيلة</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Recent Orders & Right Sidebar --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock" style="color:var(--pink-600);margin-left:6px;"></i> آخر الطلبات</span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-pink">عرض الكل <i class="fas fa-arrow-left"></i></a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 table-hover">
                    <thead>
                        <tr>
                            <th>الطلب</th>
                            <th>العميل</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="fw-bold">#{{ $order->order_number ?? $order->id }}</td>
                            <td>{{ $order->customer_name ?? ($order->user->name ?? 'زائر') }}</td>
                            <td class="fw-bold" style="color:var(--pink-600);">{{ number_format($order->total_amount, 2) }} ₪</td>
                            <td>
                                @php
                                    $sMap = ['pending'=>['bg'=>'#FEF3C7','c'=>'#92400E','l'=>'قيد الانتظار'],'confirmed'=>['bg'=>'#E0F2FE','c'=>'#0284C7','l'=>'مؤكد'],'processing'=>['bg'=>'#DBEAFE','c'=>'#1E40AF','l'=>'قيد المعالجة'],'shipped'=>['bg'=>'#DBEAFE','c'=>'#1E40AF','l'=>'تم الشحن'],'delivered'=>['bg'=>'#DCFCE7','c'=>'#16A34A','l'=>'تم التوصيل'],'completed'=>['bg'=>'#DCFCE7','c'=>'#16A34A','l'=>'مكتمل'],'cancelled'=>['bg'=>'#FEE2E2','c'=>'#991B1B','l'=>'ملغي']];
                                    $s = $sMap[$order->status] ?? ['bg'=>'#F1F5F9','c'=>'#475569','l'=>$order->status];
                                @endphp
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:{{ $s['bg'] }};color:{{ $s['c'] }};">{{ $s['l'] }}</span>
                            </td>
                            <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm" style="color:var(--pink-600);padding:4px 8px;"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-inbox mb-2" style="font-size:2rem;display:block;opacity:.3;"></i> لا توجد طلبات بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-star" style="color:#F59E0B;margin-left:6px;"></i> أكثر المنتجات مبيعاً</div>
            <div class="card-body p-0">
                @forelse($topProducts as $product)
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                    @if($product->main_image)
                    <img src="{{ $product->main_image_url }}" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                    @else
                    <div style="width:36px;height:36px;border-radius:8px;background:var(--pink-50);display:flex;align-items:center;justify-content:center;color:var(--pink-600);"><i class="fas fa-box"></i></div>
                    @endif
                    <div style="flex:1;min-width:0;">
                        <div class="fw-bold small text-truncate">{{ $product->name_ar }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $product->sales_count }} مباع · {{ number_format($product->b2c_price, 2) }} ₪</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted small">لا توجد مبيعات بعد</div>
                @endforelse
            </div>
        </div>

        @if($lowStockProducts > 0 || $outOfStockProducts > 0)
        <div class="card mb-4 border-warning">
            <div class="card-header" style="background:#FEF3C7;color:#92400E;"><i class="fas fa-exclamation-triangle"></i> تنبيهات المخزون</div>
            <div class="card-body py-3">
                @if($lowStockProducts > 0)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark rounded-pill">{{ $lowStockProducts }}</span>
                    <span class="small">منتجات منخفضة المخزون</span>
                    <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="ms-auto small" style="color:var(--pink-600);">عرض</a>
                </div>
                @endif
                @if($outOfStockProducts > 0)
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger rounded-pill">{{ $outOfStockProducts }}</span>
                    <span class="small">منتجات نفدت من المخزون</span>
                    <a href="{{ route('admin.products.index', ['stock' => 'out']) }}" class="ms-auto small" style="color:var(--pink-600);">عرض</a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><i class="fas fa-link" style="color:var(--pink-600);margin-left:6px;"></i> روابط سريعة</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-pink btn-sm"><i class="fas fa-plus"></i> إضافة منتج جديد</a>
                    <a href="{{ route('admin.blog.create') }}" class="btn btn-pink btn-sm"><i class="fas fa-newspaper"></i> إضافة مقال جديد</a>
                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-outline-pink btn-sm"><i class="fas fa-ticket"></i> إضافة كوبون خصم</a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-pink btn-sm"><i class="fas fa-box"></i> إدارة المنتجات</a>
                    <a href="{{ route('admin.blog.index') }}" class="btn btn-outline-pink btn-sm"><i class="fas fa-newspaper"></i> إدارة المقالات</a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-pink btn-sm"><i class="fas fa-shopping-bag"></i> إدارة الطلبات</a>
                    <a href="{{ route('admin.b2b.companies') }}" class="btn btn-outline-pink btn-sm"><i class="fas fa-building"></i> شركات B2B</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Tajawal', sans-serif";
Chart.defaults.color = '#64748b';

const ctx = document.getElementById('mainChart').getContext('2d');
const mainChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['labels']) !!},
        datasets: [{
            label: 'الإيرادات (₪)',
            data: {!! json_encode($chartData['revenue']) !!},
            borderColor: '#db2777',
            backgroundColor: 'rgba(219, 39, 119, 0.1)',
            fill: true,
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'الطلبات',
            data: {!! json_encode($chartData['orders']) !!},
            borderColor: '#0284c7',
            backgroundColor: 'rgba(2, 132, 199, 0.1)',
            fill: true,
            tension: 0.4,
            yAxisID: 'y1',
            hidden: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { position: 'top', rtl: true },
            tooltip: {
                rtl: true,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.dataset.yAxisID === 'y') {
                            label += new Intl.NumberFormat('ar-SA').format(context.raw) + ' ₪';
                        } else {
                            label += context.raw;
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                grid: { color: '#f1f5f9' },
                ticks: { callback: function(value) { return value + ' ₪'; } }
            },
            y1: {
                type: 'linear',
                display: false,
                position: 'right',
                grid: { display: false }
            }
        }
    }
});

function toggleChart(type) {
    if (type === 'revenue') {
        mainChart.data.datasets[0].hidden = false;
        mainChart.data.datasets[1].hidden = true;
    } else {
        mainChart.data.datasets[0].hidden = true;
        mainChart.data.datasets[1].hidden = false;
    }
    mainChart.update();
    event.target.parentElement.querySelectorAll('button').forEach(function(btn) { btn.classList.remove('active'); });
    event.target.classList.add('active');
}

const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['قيد الانتظار', 'قيد المعالجة', 'تم الشحن', 'مكتمل', 'ملغي'],
        datasets: [{
            data: [
                {{ $chartData['status']['pending'] }},
                {{ $chartData['status']['processing'] }},
                {{ $chartData['status']['shipped'] }},
                {{ $chartData['status']['completed'] }},
                {{ $chartData['status']['cancelled'] }}
            ],
            backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', rtl: true, labels: { padding: 20, usePointStyle: true } }
        },
        cutout: '65%'
    }
});

const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData['categories']->keys()) !!},
        datasets: [{
            label: 'المبيعات',
            data: {!! json_encode($chartData['categories']->values()) !!},
            backgroundColor: 'rgba(219, 39, 119, 0.8)',
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: '#f1f5f9' } }
        }
    }
});
</script>
@endpush
