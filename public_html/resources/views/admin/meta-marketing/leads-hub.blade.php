@extends('admin.layouts.app')
@section('title', 'عملاء فيسبوك')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h4 mb-1"><i class="fas fa-users" style="color:var(--pink-600);margin-left:8px;"></i> عملاء فيسبوك</h1><p class="text-muted small mb-0">Facebook Leads Hub — الميزة قيد التطوير</p></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#1877F2;color:#fff"><i class="fas fa-users"></i></div><div class="stat-value-new">{{ $totalLeads ?? 0 }}</div><div class="stat-label-new">إجمالي العملاء</div></div></div>
    <div class="col-md-4"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#10B981;color:#fff"><i class="fas fa-calendar-check"></i></div><div class="stat-value-new">{{ $syncedToday ?? 0 }}</div><div class="stat-label-new">اليوم</div></div></div>
    <div class="col-md-4"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#f59e0b;color:#fff"><i class="fas fa-sync"></i></div><div class="stat-value-new">0</div><div class="stat-label-new">بانتظار المزامنة</div></div></div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list" style="color:var(--pink-600);margin-left:6px;"></i> قائمة العملاء</span>
        <small class="text-muted">{{ $totalLeads ?? 0 }} عميل</small>
    </div>
    <div class="card-body p-0">
        @if(($leads->count() ?? 0) > 0)
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>المصدر</th><th>التاريخ</th></tr></thead>
            <tbody>
            @foreach($leads as $lead)
            <tr><td>{{ $lead->name ?? '-' }}</td><td>{{ $lead->email ?? '-' }}</td><td>{{ $lead->phone ?? '-' }}</td><td>{{ $lead->source ?? '-' }}</td><td><small>{{ $lead->created_at ?? '' }}</small></td></tr>
            @endforeach
            </tbody>
        </table>
        @else
        <p class="text-muted text-center py-5 mb-0"><i class="fas fa-inbox d-block mb-2" style="font-size:2rem;opacity:.3;"></i>لا يوجد عملاء بعد</p>
        @endif
    </div>
</div>
@endsection
