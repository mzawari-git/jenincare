@extends('admin.layouts.app')
@section('title', 'التسويق عبر ميتا')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h4 mb-1"><i class="fas fa-rocket" style="color:var(--pink-600);margin-left:8px;"></i> التسويق عبر ميتا</h1><p class="text-muted small mb-0">Meta Marketing Automation — الميزة قيد التطوير</p></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#1877F2;color:#fff"><i class="fas fa-shopping-bag"></i></div><div class="stat-value-new">{{ number_format($realStats['total_orders'] ?? 0) }}</div><div class="stat-label-new">إجمالي الطلبات</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#10B981;color:#fff"><i class="fas fa-dollar-sign"></i></div><div class="stat-value-new">{{ number_format($realStats['total_revenue'] ?? 0, 0) }} ILS</div><div class="stat-label-new">الإيرادات</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#8B5CF6;color:#fff"><i class="fas fa-users"></i></div><div class="stat-value-new">{{ number_format($realStats['total_users'] ?? 0) }}</div><div class="stat-label-new">المستخدمين</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#f59e0b;color:#fff"><i class="fas fa-bullhorn"></i></div><div class="stat-value-new">{{ $realStats['conversion_rate'] ?? 0 }}%</div><div class="stat-label-new">معدل التحويل</div></div></div>
</div>

<div class="card"><div class="card-header fw-bold small"><i class="fas fa-info-circle" style="color:var(--pink-600);margin-left:6px;"></i> آخر الطلبات</div>
<div class="card-body p-0"><table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th>رقم الطلب</th><th>العميل</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
@forelse($recentOrders ?? [] as $o)
<tr><td><b>#{{ $o['order_number'] ?? $o['id'] }}</b></td><td>{{ $o['customer'] ?? '-' }}</td><td>{{ number_format($o['total'] ?? 0, 0) }} ILS</td><td>{{ $o['status'] ?? '-' }}</td><td><small>{{ $o['created_at'] ?? '' }}</small></td></tr>
@empty
<tr><td colspan="5" class="text-center text-muted small py-3">لا توجد طلبات بعد</td></tr>
@endforelse
</tbody></table></div></div>
@endsection
