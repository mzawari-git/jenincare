@php
$settings = app(\App\Models\Setting::class);
@endphp
@extends('admin.layouts.app')
@section('title', 'التوقع والتحليلات')
@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">🔮 لوحة التوقع والتحليلات</h1>
        <div class="d-flex gap-2">
            <select id="daysFilter" class="form-select" style="width:auto;">
                <option value="7">7 أيام</option>
                <option value="30" selected>30 يوم</option>
                <option value="90">90 يوم</option>
            </select>
            <button id="refreshBtn" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt"></i> تحديث
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">إجمالي LTV المتوقع (365d)</div>
                    <div class="h4 mb-0" id="totalLtv">0 ر.س</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">متوسط LTV للعميل</div>
                    <div class="h4 mb-0" id="avgLtv">0 ر.س</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">مبيعات POS</div>
                    <div class="h4 mb-0" id="posSales">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">POS مطابق أونلاين</div>
                    <div class="h4 mb-0" id="posMatched">0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">توزيع الشرائح</h5>
                </div>
                <div class="card-body">
                    <div id="segmentChart">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>🏢 B2B (قيمة عالية)</span>
                            <span class="badge bg-primary" id="b2bCount">0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>👤 B2C (متوسط)</span>
                            <span class="badge bg-info" id="b2cCount">0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span>💫 One-Time (منخفض)</span>
                            <span class="badge bg-secondary" id="oneTimeCount">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">المضاعفات حسب المنصة</h5>
                </div>
                <div class="card-body">
                    <div id="multipliersTable"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تفاصيل العملاء</h5>
            <span class="text-muted small" id="orderCount">0 طلب</span>
        </div>
        <div class="table-responsive" style="max-height:400px;">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>البريد</th>
                        <th>قيمة الطلب</th>
                        <th>LTV 30 يوم</th>
                        <th>LTV 365 يوم</th>
                        <th>الشريحة</th>
                    </tr>
                </thead>
                <tbody id="ordersTable"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadData() {
    var days = document.getElementById('daysFilter').value;
    fetch('{{ route("admin.predictive.data") }}?days=' + days)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            document.getElementById('totalLtv').textContent = d.total_ltv_365d.toLocaleString() + ' ر.س';
            document.getElementById('avgLtv').textContent = d.average_ltv.toLocaleString() + ' ر.س';
            document.getElementById('posSales').textContent = d.pos_stats.total_sales;
            document.getElementById('posMatched').textContent = d.pos_stats.matched_sales;
            document.getElementById('b2bCount').textContent = d.segments.b2b;
            document.getElementById('b2cCount').textContent = d.segments.b2c;
            document.getElementById('oneTimeCount').textContent = d.segments.one_time;
            document.getElementById('orderCount').textContent = d.total_orders + ' طلب';

            var tbody = document.getElementById('ordersTable');
            tbody.innerHTML = '';
            d.orders.forEach(function(o) {
                var tr = document.createElement('tr');
                var segmentBadge = o.segment === 'b2b' ? 'bg-primary' : (o.segment === 'b2c' ? 'bg-info' : 'bg-secondary');
                tr.innerHTML = '<td>' + o.id + '</td>' +
                    '<td>' + (o.email || '-') + '</td>' +
                    '<td>' + o.aov.toLocaleString() + ' ر.س</td>' +
                    '<td>' + o.ltv_30d.toLocaleString() + ' ر.س</td>' +
                    '<td>' + o.ltv_365d.toLocaleString() + ' ر.س</td>' +
                    '<td><span class="badge ' + segmentBadge + '">' + o.segment + '</span></td>';
                tbody.appendChild(tr);
            });

            var mDiv = document.getElementById('multipliersTable');
            var mHtml = '<table class="table table-sm mb-0"><thead><tr><th>الشريحة</th><th>افتراضي</th><th>Facebook</th><th>TikTok</th><th>Google</th></tr></thead><tbody>';
            var segments = ['b2b', 'b2c', 'one_time'];
            segments.forEach(function(s) {
                mHtml += '<tr><td>' + s + '</td><td>' + (d.multipliers.default[s] || 1) + 'x</td>' +
                    '<td>' + (d.multipliers.facebook[s] || '-') + '</td>' +
                    '<td>' + (d.multipliers.tiktok[s] || '-') + '</td>' +
                    '<td>' + (d.multipliers.google[s] || '-') + '</td></tr>';
            });
            mHtml += '</tbody></table>';
            mDiv.innerHTML = mHtml;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadData();
    document.getElementById('daysFilter').addEventListener('change', loadData);
    document.getElementById('refreshBtn').addEventListener('click', loadData);
});
</script>
@endpush
