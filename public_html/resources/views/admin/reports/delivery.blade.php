@extends('admin.layouts.app')
@section('title', 'تقرير التوصيل')

@push('styles')
<style>
.stat-card-report {
    color: #fff; border-radius: 16px; padding: 1.2rem 1rem;
    text-align: center; transition: all .3s;
}
.stat-card-report:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.stat-card-report .stat-icon { font-size: 2rem; opacity: .25; margin-bottom: 4px; }
.stat-card-report .stat-value { font-size: 1.5rem; font-weight: 800; }
.stat-card-report .stat-label { font-size: .75rem; opacity: .9; }
.text-pink { color: #d97a8c !important; }
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c7687a; color: #fff; }
.btn-outline-pink { border-color: #d97a8c; color: #d97a8c; }
.btn-outline-pink:hover { background: #d97a8c; color: #fff; }
.info-card { background: #fff; border-radius: 12px; padding: 1rem; border: 1px solid #e2e8f0; }
.info-card .val { font-size: 1.3rem; font-weight: 800; color: var(--gray-800); }
.info-card .lbl { font-size: .75rem; color: var(--gray-500); }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-truck text-pink"></i> تقرير التوصيل</h3>
            <p class="text-muted mb-0">
                ملخص عمليات التوصيل — 
                <a href="{{ route('admin.deliveries.index') }}" class="text-pink">العودة لإدارة التوصيل</a>
            </p>
        </div>
        <a href="{{ route('admin.reports.delivery.export', request()->all()) }}" class="btn btn-pink rounded-pill">
            <i class="fas fa-file-excel"></i> تصدير Excel
        </a>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-report" style="background:linear-gradient(135deg, #6366f1, #4f46e5)">
                <div class="stat-icon"><i class="fas fa-list"></i></div>
                <div class="stat-value">{{ number_format($summary->total ?? 0) }}</div>
                <div class="stat-label">إجمالي التوصيلات</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-report" style="background:linear-gradient(135deg, #f59e0b, #d97706)">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value">{{ number_format($summary->pending ?? 0) }}</div>
                <div class="stat-label">قيد الانتظار</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-report" style="background:linear-gradient(135deg, #3b82f6, #2563eb)">
                <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="stat-value">{{ number_format($summary->in_progress ?? 0) }}</div>
                <div class="stat-label">قيد التوصيل</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-report" style="background:linear-gradient(135deg, #10b981, #047857)">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value">{{ number_format($summary->delivered ?? 0) }}</div>
                <div class="stat-label">تم التوصيل</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-report" style="background:linear-gradient(135deg, #ef4444, #dc2626)">
                <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-value">{{ number_format($summary->failed ?? 0) }}</div>
                <div class="stat-label">فشل / مرتجع</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-report" style="background:linear-gradient(135deg, #8b5cf6, #7c3aed)">
                <div class="stat-icon"><i class="fas fa-shekel-sign"></i></div>
                <div class="stat-value">{{ number_format($summary->total_cost ?? 0, 0) }} ₪</div>
                <div class="stat-label">تكلفة التوصيل</div>
            </div>
        </div>
    </div>

    {{-- Additional Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="info-card text-center">
                <div class="lbl">إجمالي الدفع عند الاستلام</div>
                <div class="val">{{ number_format($summary->total_cod ?? 0, 2) }} ₪</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-card text-center">
                <div class="lbl">متوسط أيام التوصيل</div>
                <div class="val">{{ number_format($summary->avg_days ?? 0, 1) }} يوم</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <div class="lbl mb-2">نسبة نجاح التوصيل</div>
                @php
                    $successRate = ($summary->total ?? 0) > 0 
                        ? round(($summary->delivered / max($summary->total, 1)) * 100, 1) 
                        : 0;
                @endphp
                <div class="d-flex align-items-center gap-2">
                    <div class="flex-grow-1">
                        <div class="progress" style="height:20px; border-radius:10px;">
                            <div class="progress-bar bg-success" style="width:{{ $successRate }}%">{{ $successRate }}%</div>
                        </div>
                    </div>
                    <span class="fw-bold">{{ $successRate }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">الحالة</label>
                    <select name="status" class="form-select rounded-pill">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التوصيل</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشل</option>
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
                    <input type="text" name="city" class="form-control rounded-pill" value="{{ request('city') }}">
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
                    <button type="submit" class="btn btn-pink rounded-pill w-100"><i class="fas fa-filter"></i> تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- By City --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h5 class="fw-bold mb-0"><i class="fas fa-map-marker-alt text-pink"></i> التوصيل حسب المدينة</h5>
                </div>
                <div class="card-body">
                    @forelse($deliveryByCity as $city => $count)
                    <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-light rounded-3">
                        <span class="fw-bold">{{ $city }}</span>
                        <span class="badge bg-pink rounded-pill" style="background:#d97a8c !important">{{ $count }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- By Driver --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h5 class="fw-bold mb-0"><i class="fas fa-user-hard-hat text-pink"></i> التوصيل حسب السائق</h5>
                </div>
                <div class="card-body">
                    @forelse($deliveryByDriver as $driver => $count)
                    <div class="d-flex align-items-center justify-content-between mb-2 p-2 bg-light rounded-3">
                        <span class="fw-bold">{{ $driver }}</span>
                        <span class="badge rounded-pill" style="background:#3b82f6 !important; color:#fff">{{ $count }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">لا توجد بيانات</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Delivery Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-table text-pink"></i> قائمة التوصيلات</h5>
            <span class="badge bg-light text-muted">{{ $deliveries->total() }} توصيلة</span>
        </div>
        <div class="card-body px-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">رقم التوصيل</th>
                            <th>رقم الطلب</th>
                            <th>السائق</th>
                            <th>المدينة</th>
                            <th>الحالة</th>
                            <th>تكلفة التوصيل</th>
                            <th>الدفع عند الاستلام</th>
                            <th>تاريخ التوصيل</th>
                            <th class="pe-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                        <tr>
                            <td class="ps-3 fw-bold text-pink">{{ $delivery->delivery_number }}</td>
                            <td>{{ $delivery->order->order_number ?? '—' }}</td>
                            <td>{{ $delivery->driver_name ?: '—' }}</td>
                            <td>{{ $delivery->delivery_city ?: '—' }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $delivery->status_color }}">
                                    {{ $delivery->status_label }}
                                </span>
                            </td>
                            <td>{{ number_format($delivery->delivery_cost, 2) }} ₪</td>
                            <td>{{ $delivery->cod_amount > 0 ? number_format($delivery->cod_amount, 2) . ' ₪' : '—' }}</td>
                            <td>{{ $delivery->delivered_at?->format('Y-m-d') ?? '—' }}</td>
                            <td class="pe-3">
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-outline-pink rounded-pill">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">لا توجد توصيلات</td></tr>
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
