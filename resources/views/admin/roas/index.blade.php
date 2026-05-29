@php
$settings = app(\App\Models\Setting::class);
@endphp
@extends('admin.layouts.app')
@section('title', 'True ROAS')
@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">📊 True ROAS</h1>
        <div class="d-flex gap-2">
            <select id="daysFilter" class="form-select" style="width:auto;">
                <option value="7">7 أيام</option>
                <option value="30" selected>30 يوم</option>
                <option value="60">60 يوم</option>
                <option value="90">90 يوم</option>
            </select>
            <button id="refreshBtn" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt"></i> تحديث
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">الطلبات</div>
                    <div class="h4 mb-0" id="totalOrders">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">الإيرادات</div>
                    <div class="h4 mb-0" id="totalRevenue">0 ر.س</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">معدل الإسناد</div>
                    <div class="h4 mb-0" id="attributionRate">0%</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">AOV</div>
                    <div class="h4 mb-0" id="aov">0 ر.س</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">ROAS</div>
                    <div class="h4 mb-0" id="roas">0x</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small">الإيرادات المسندة</div>
                    <div class="h4 mb-0" id="attributedRevenue">0 ر.س</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">توزيع المصادر</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="sourceTable">
                            <thead>
                                <tr>
                                    <th>المصدر</th>
                                    <th>الطلبات</th>
                                    <th>الإيرادات</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">أفضل المصادر</h5>
                </div>
                <div class="card-body">
                    <div id="topSourcesList"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">أداء الحملات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="campaignTable">
                            <thead>
                                <tr>
                                    <th>المصدر</th>
                                    <th>الوسيط</th>
                                    <th>الحملة</th>
                                    <th>الزوار</th>
                                    <th>الأحداث</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadRoasData() {
    var days = document.getElementById('daysFilter').value;
    fetch('{{ route("admin.roas.data") }}?days=' + days)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var s = d.summary;
            document.getElementById('totalOrders').textContent = s.total_orders;
            document.getElementById('totalRevenue').textContent = s.total_revenue.toLocaleString() + ' ر.س';
            document.getElementById('attributionRate').textContent = s.attribution_rate + '%';
            document.getElementById('aov').textContent = s.aov.toLocaleString() + ' ر.س';
            document.getElementById('roas').textContent = s.roas + 'x';
            document.getElementById('attributedRevenue').textContent = s.attributed_revenue.toLocaleString() + ' ر.س';

            var sourceTbody = document.querySelector('#sourceTable tbody');
            sourceTbody.innerHTML = '';
            var totalOrders = s.total_orders || 1;
            d.source_breakdown.forEach(function(item) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + (item.source || 'مباشر') + '</td>' +
                    '<td>' + item.orders + '</td>' +
                    '<td>' + item.revenue.toLocaleString() + ' ر.س</td>' +
                    '<td>' + ((item.orders / totalOrders) * 100).toFixed(1) + '%</td>';
                sourceTbody.appendChild(tr);
            });

            var topList = document.getElementById('topSourcesList');
            topList.innerHTML = '';
            d.top_sources.forEach(function(item) {
                var div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center py-2 border-bottom';
                div.innerHTML = '<strong>' + (item.utm_source || 'مباشر') + '</strong>' +
                    '<span class="text-muted">' + item.unique_visitors + ' زائر</span>';
                topList.appendChild(div);
            });

            var campaignTbody = document.querySelector('#campaignTable tbody');
            campaignTbody.innerHTML = '';
            d.campaign_performance.forEach(function(item) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + (item.utm_source || '-') + '</td>' +
                    '<td>' + (item.utm_medium || '-') + '</td>' +
                    '<td>' + (item.utm_campaign || '-') + '</td>' +
                    '<td>' + item.unique_visitors + '</td>' +
                    '<td>' + item.total_events + '</td>';
                campaignTbody.appendChild(tr);
            });
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadRoasData();
    document.getElementById('daysFilter').addEventListener('change', loadRoasData);
    document.getElementById('refreshBtn').addEventListener('click', loadRoasData);
    setInterval(loadRoasData, 30000);
});
</script>
@endpush
