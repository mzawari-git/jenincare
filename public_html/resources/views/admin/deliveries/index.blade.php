@extends('admin.layouts.app')
@section('title', 'إدارة التوصيل')

@push('styles')
<style>
.delivery-stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.2rem 1rem;
    border: 1px solid #e2e8f0;
    text-align: center;
    transition: all .3s;
    cursor: pointer;
    text-decoration: none;
    display: block;
    color: inherit;
}
.delivery-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
    color: inherit;
}
.delivery-stat-card .icon-circle {
    width: 48px; height: 48px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 8px;
    font-size: 1.3rem;
}
.delivery-stat-card .stat-num {
    font-size: 1.6rem; font-weight: 800; color: var(--gray-800);
}
.delivery-stat-card .stat-label {
    font-size: .75rem; color: var(--gray-500); font-weight: 500;
}
.filter-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.status-badge .status-dot {
    width: 6px; height: 6px; border-radius: 50%; display: inline-block;
}
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c7687a; color: #fff; }
.btn-outline-pink { border-color: #d97a8c; color: #d97a8c; }
.btn-outline-pink:hover { background: #d97a8c; color: #fff; }
.text-pink { color: #d97a8c !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-truck text-pink"></i> إدارة التوصيل</h3>
            <p class="text-muted mb-0">متابعة وإدارة عمليات التوصيل والسائقين</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.delivery') }}" class="btn btn-outline-pink rounded-pill">
                <i class="fas fa-chart-bar"></i> تقرير التوصيل
            </a>
            <a href="{{ route('admin.deliveries.create') }}" class="btn btn-pink rounded-pill">
                <i class="fas fa-plus"></i> توصيل جديد
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('admin.deliveries.index') }}" class="delivery-stat-card">
                <div class="icon-circle" style="background:#e0f2fe; color:#0284c7;"><i class="fas fa-list"></i></div>
                <div class="stat-num">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">إجمالي التوصيلات</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('admin.deliveries.index', ['status' => 'pending']) }}" class="delivery-stat-card">
                <div class="icon-circle" style="background:#fef3c7; color:#d97706;"><i class="fas fa-clock"></i></div>
                <div class="stat-num">{{ number_format($stats['pending']) }}</div>
                <div class="stat-label">قيد الانتظار</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('admin.deliveries.index', ['status' => 'in_transit']) }}" class="delivery-stat-card">
                <div class="icon-circle" style="background:#dbeafe; color:#2563eb;"><i class="fas fa-shipping-fast"></i></div>
                <div class="stat-num">{{ number_format($stats['inProgress']) }}</div>
                <div class="stat-label">قيد التوصيل</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('admin.deliveries.index', ['status' => 'delivered']) }}" class="delivery-stat-card">
                <div class="icon-circle" style="background:#dcfce7; color:#16a34a;"><i class="fas fa-check-circle"></i></div>
                <div class="stat-num">{{ number_format($stats['delivered']) }}</div>
                <div class="stat-label">تم التوصيل</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('admin.deliveries.index', ['status' => 'failed']) }}" class="delivery-stat-card">
                <div class="icon-circle" style="background:#fee2e2; color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-num">{{ number_format($stats['failed']) }}</div>
                <div class="stat-label">فشل / مرتجع</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="{{ route('admin.orders.index') }}" class="delivery-stat-card">
                <div class="icon-circle" style="background:#f3e8ff; color:#9333ea;"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-num"><i class="fas fa-arrow-left"></i></div>
                <div class="stat-label">الطلبات</div>
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">بحث</label>
                    <input type="text" name="search" class="form-control rounded-pill" placeholder="رقم التوصيل / التتبع / المستلم..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">الحالة</label>
                    <select name="status" class="form-select rounded-pill">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>تم التعيين</option>
                        <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>تم الاستلام</option>
                        <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>قيد النقل</option>
                        <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>قيد التوصيل</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التوصيل</option>
                        <option value="attempted" {{ request('status') == 'attempted' ? 'selected' : '' }}>محاولة توصيل</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشل التوصيل</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>مرتجع</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">السائق</label>
                    <select name="driver" class="form-select rounded-pill">
                        <option value="">الكل</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d }}" {{ request('driver') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">المدينة</label>
                    <input type="text" name="city" class="form-control rounded-pill" placeholder="المدينة..." value="{{ request('city') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control rounded-pill" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control rounded-pill" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-pink rounded-pill w-100"><i class="fas fa-search"></i> تصفية</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary rounded-pill w-100"><i class="fas fa-times"></i> مسح</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">رقم التوصيل</th>
                            <th>الطلب</th>
                            <th>العميل / المستلم</th>
                            <th>السائق</th>
                            <th>المدينة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th class="pe-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="fw-bold text-pink text-decoration-none">
                                    {{ $delivery->delivery_number }}
                                </a>
                                @if($delivery->tracking_number)
                                    <br><small class="text-muted">{{ $delivery->tracking_number }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">{{ $delivery->order->order_number ?? '—' }}</span>
                                <br><small class="text-muted">{{ number_format($delivery->order->total_amount ?? 0, 2) }} ₪</small>
                            </td>
                            <td>
                                <div>{{ $delivery->recipient_name ?: $delivery->order->customer_name ?? '—' }}</div>
                                @if($delivery->delivery_address)
                                    <small class="text-muted text-truncate d-inline-block" style="max-width:140px">{{ $delivery->delivery_address }}</small>
                                @endif
                            </td>
                            <td>
                                @if($delivery->driver_name)
                                    <div>{{ $delivery->driver_name }}</div>
                                    <small class="text-muted">{{ $delivery->driver_phone }}</small>
                                @else
                                    <span class="badge bg-light text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $delivery->delivery_city ?: $delivery->order->shipping_city ?? '—' }}</td>
                            <td>
                                <span class="status-badge bg-{{ $delivery->status_color }}">
                                    <span class="status-dot" style="background:currentColor"></span>
                                    {{ $delivery->status_label }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $delivery->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $delivery->created_at->format('H:i') }}</small>
                            </td>
                            <td class="pe-3">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-outline-pink rounded-pill" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.deliveries.edit', $delivery) }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-truck fa-3x mb-3 d-block opacity-25"></i>
                                لا توجد عمليات توصيل
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
@endsection
