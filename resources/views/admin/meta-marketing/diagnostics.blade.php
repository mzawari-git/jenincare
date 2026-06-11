@extends('admin.layouts.app')
@section('title', 'تشخيص CAPI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-stethoscope" style="color:var(--pink-600);margin-left:8px;"></i> تشخيص أداء CAPI</h1>
        <p class="text-muted small mb-0">Conversions API Diagnostics — مراقبة وتحليل أداء أحداث التحويل</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill d-none" id="alertCountBadge">
            <i class="fas fa-exclamation-circle me-1"></i> <span id="alertCount">0</span> تنبيه
        </span>
        <button onclick="exportCsv()" class="btn btn-sm btn-outline-secondary" title="تصدير CSV">
            <i class="fas fa-download"></i> تصدير
        </button>
        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill" id="lastRefresh">
            <i class="fas fa-sync-alt me-1"></i> <span id="refreshTime">الآن</span>
        </span>
    </div>
</div>

{{-- Alerts Section --}}
<div class="mb-4" id="alertsContainer">
    @foreach($stats['alerts'] as $alert)
    <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-2" role="alert">
        <i class="fas {{ $alert['icon'] }}"></i>
        <span>{{ $alert['message'] }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:.75rem;"></button>
    </div>
    @endforeach
</div>

{{-- Row 1: KPI Cards --}}
<div class="row g-3 mb-4" id="kpiRow">
    <div class="col-md-2 col-6">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;" id="statTotal">{{ number_format($stats['total']) }}</div>
            <div class="stat-label-new">إجمالي الأحداث</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#10B981;" id="statSuccessRate">{{ $stats['success_rate'] }}%</div>
            <div class="stat-label-new">نسبة النجاح</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#EF4444;" id="statFailed">{{ number_format($stats['failed_count']) }}</div>
            <div class="stat-label-new">فاشل</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;" id="statAvgDuration">{{ $stats['avg_duration_ms'] }}</div>
            <div class="stat-label-new">متوسط المدة (مللي)</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;" id="statToday">{{ number_format($stats['today_count']) }}</div>
            <div class="stat-label-new">اليوم</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;" id="statUniqueTypes">{{ $stats['unique_event_types'] }}</div>
            <div class="stat-label-new">أنواع الأحداث</div>
        </div>
    </div>
</div>

{{-- Row 2: Gauge + Donut --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-heartbeat" style="color:var(--pink-600);margin-left:6px;"></i> معدل النجاح الإجمالي</div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                <div id="successGaugeChart" style="width:100%;max-width:280px;"></div>
                <div class="row w-100 mt-3 text-center g-2">
                    <div class="col-4">
                        <div class="small text-muted">ناجح</div>
                        <div class="fw-bold text-success" id="gaugeSuccess">{{ number_format($stats['success_count']) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">فاشل</div>
                        <div class="fw-bold text-danger" id="gaugeFailed">{{ number_format($stats['failed_count']) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">معلق</div>
                        <div class="fw-bold text-secondary" id="gaugePending">{{ number_format($stats['pending_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie" style="color:var(--pink-600);margin-left:6px;"></i> توزيع الأحداث حسب النوع</div>
            <div class="card-body">
                <div id="eventsByTypeChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Row 3: Daily Trends + Duration --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-line" style="color:var(--pink-600);margin-left:6px;"></i> الاتجاه اليومي (آخر 30 يوم)</span>
                <span class="small text-muted" id="trendSummary"></span>
            </div>
            <div class="card-body">
                <div id="dailyTrendChart"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-clock" style="color:var(--pink-600);margin-left:6px;"></i> متوسط المدة حسب النوع</div>
            <div class="card-body">
                <div id="durationChart"></div>
            </div>
        </div>
    </div>
</div>

{{-- Row 4: Platform Status --}}
<div class="row g-3 mb-4">
    @php
        $platforms = [
            'facebook' => ['label' => 'فيسبوك', 'icon' => 'fab fa-facebook', 'color' => '#1877F2'],
            'tiktok' => ['label' => 'تيك توك', 'icon' => 'fab fa-tiktok', 'color' => '#000000'],
            'google' => ['label' => 'جوجل', 'icon' => 'fab fa-google', 'color' => '#EA4335'],
            'snapchat' => ['label' => 'سناب شات', 'icon' => 'fab fa-snapchat', 'color' => '#FFFC00'],
            'pinterest' => ['label' => 'بنترست', 'icon' => 'fab fa-pinterest', 'color' => '#E60023'],
            'twitter' => ['label' => 'تويتر', 'icon' => 'fab fa-twitter', 'color' => '#1DA1F2'],
            'linkedin' => ['label' => 'لينكد إن', 'icon' => 'fab fa-linkedin', 'color' => '#0A66C2'],
        ];
    @endphp
    @foreach($platforms as $key => $p)
        @php
            $pCount = $stats['platform_counts'][$key] ?? 0;
            $pRate = $stats['platform_success_rates'][$key] ?? null;
            $pLast = $stats['last_event_per_platform'][$key] ?? null;
            $pSettings = $settings[$key] ?? [];
            $pixelEnabled = $pSettings['enabled'] ?? false;
            $capiEnabled = $pSettings['capi_enabled'] ?? false;
        @endphp
        <div class="col-md-3 col-6">
            <div class="stat-card-new">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="{{ $p['icon'] }}" style="color:{{ $p['color'] }};font-size:1.2rem;width:28px;"></i>
                        <span class="fw-semibold">{{ $p['label'] }}</span>
                    </div>
                    @if($pCount > 0)
                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill small">{{ number_format($pCount) }}</span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill small">—</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="text-muted">المعدل:</span>
                    <span class="fw-semibold">{{ $pRate !== null ? $pRate . '%' : '—' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center small mt-1">
                    <span class="text-muted">آخر حدث:</span>
                    <span class="small text-muted">{{ $pLast ? \Carbon\Carbon::parse($pLast)->diffForHumans() : '—' }}</span>
                </div>
                <div class="d-flex gap-2 mt-2">
                    @if($pixelEnabled)
                        <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill" style="font-size:.65rem;">Pixel</span>
                    @endif
                    @if($capiEnabled)
                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill" style="font-size:.65rem;">CAPI</span>
                    @endif
                    @if(!$pixelEnabled && !$capiEnabled)
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill" style="font-size:.65rem;">متوقف</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Row 5: Recent Events Table --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-list" style="color:var(--pink-600);margin-left:6px;"></i> آخر الأحداث</span>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <select id="filterPlatform" class="form-select form-select-sm" style="width:auto;">
                <option value="">كل المنصات</option>
                <option value="facebook">فيسبوك</option>
                <option value="tiktok">تيك توك</option>
                <option value="google">جوجل</option>
                <option value="snapchat">سناب شات</option>
                <option value="pinterest">بنترست</option>
                <option value="twitter">تويتر</option>
                <option value="linkedin">لينكد إن</option>
                <option value="custom">مخصص</option>
            </select>
            <select id="filterStatus" class="form-select form-select-sm" style="width:auto;">
                <option value="">كل الحالات</option>
                <option value="success">ناجح</option>
                <option value="failed">فاشل</option>
                <option value="pending">معلق</option>
            </select>
            <select id="filterEventName" class="form-select form-select-sm" style="width:auto;">
                <option value="">كل الأحداث</option>
                @foreach(array_keys($stats['events_by_type'] ?? []) as $ename)
                    <option value="{{ $ename }}">{{ $ename }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-pink" onclick="refreshEventsTable()"><i class="fas fa-search"></i></button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
            <table class="table table-hover mb-0" id="eventsTable">
                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th>المنصة</th>
                        <th>الحدث</th>
                        <th>الحالة</th>
                        <th>المدة</th>
                        <th>Event ID</th>
                        <th>الرسالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody id="eventsBody">
                    @forelse($stats['recent_events'] as $ev)
                    <tr class="event-row" data-platform="{{ $ev['platform'] }}" data-success="{{ is_null($ev['success']) ? 'pending' : ($ev['success'] ? 'success' : 'failed') }}" data-event="{{ $ev['event_name'] }}">
                        <td>
                            @php $pLabel = $platforms[$ev['platform']]['label'] ?? $ev['platform']; @endphp
                            @php $pIcon = $platforms[$ev['platform']]['icon'] ?? 'fas fa-cloud'; @endphp
                            @php $pColor = $platforms[$ev['platform']]['color'] ?? '#64748B'; @endphp
                            <i class="{{ $pIcon }}" style="color:{{ $pColor }};width:18px;"></i>
                            {{ $pLabel }}
                        </td>
                        <td><span class="badge bg-light text-dark rounded-pill px-3 py-1">{{ $ev['event_name'] }}</span></td>
                        <td>
                            @if(is_null($ev['success']))
                                <span class="badge bg-secondary">معلق</span>
                            @elseif($ev['success'])
                                <span class="badge bg-success">ناجح</span>
                            @else
                                <span class="badge bg-danger">فاشل</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $ev['duration_ms'] ? $ev['duration_ms'] . 'ms' : '—' }}</small></td>
                        <td><small class="text-muted" style="font-family:monospace;font-size:.75rem;">{{ \Illuminate\Support\Str::limit($ev['event_id'] ?? '—', 16) }}</small></td>
                        <td><small class="text-muted" style="max-width:180px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $ev['error_message'] ?? '' }}">{{ $ev['error_message'] ?? '—' }}</small></td>
                        <td><small class="text-muted">{{ \Carbon\Carbon::parse($ev['created_at'])->diffForHumans() }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted small py-4">لا توجد أحداث بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Row 6: Error Analysis --}}
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-exclamation-triangle" style="color:var(--pink-600);margin-left:6px;"></i> تحليل الأخطاء</div>
    <div class="card-body p-0">
        @if(count($stats['errors']) > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>رسالة الخطأ</th><th>عدد مرات التكرار</th><th>آخر ظهور</th><th>نسبة من الفشل</th></tr>
                </thead>
                <tbody>
                    @foreach($stats['errors'] as $i => $err)
                    @php $errPct = $stats['failed_count'] > 0 ? round(($err['count'] / $stats['failed_count']) * 100, 1) : 0; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><small style="font-family:monospace;">{{ $err['error_message'] ?? '—' }}</small></td>
                        <td><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1">{{ number_format($err['count']) }}</span></td>
                        <td><small class="text-muted">{{ \Carbon\Carbon::parse($err['last_occurrence'])->diffForHumans() }}</small></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-thin flex-grow-1" style="max-width:120px;">
                                    <div class="progress-bar bg-danger" style="width:{{ $errPct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $errPct }}%</small>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted small py-4"><i class="fas fa-check-circle text-success mb-2" style="font-size:1.5rem;display:block;"></i> لا توجد أخطاء مسجلة — جميع الأحداث تعمل بنجاح</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function() {
    var stats = @json($stats);
    var refreshInterval = 30000;
    var charts = {};

    function getGaugeColor(rate) {
        if (rate >= 90) return '#10B981';
        if (rate >= 70) return '#F59E0B';
        return '#EF4444';
    }

    function renderGauge(rate) {
        var el = document.getElementById('successGaugeChart');
        if (!el) return;
        if (charts.gauge) charts.gauge.destroy();
        var color = getGaugeColor(rate);
        charts.gauge = new ApexCharts(el, {
            chart: { type: 'radialBar', height: 250, fontFamily: 'Tajawal, sans-serif', sparkline: { enabled: true } },
            series: [rate],
            colors: [color],
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: { margin: 0, size: '65%' },
                    track: { background: '#E2E8F0', strokeWidth: '97%' },
                    dataLabels: {
                        show: true,
                        name: { show: true, fontSize: '13px', fontFamily: 'Tajawal', color: '#94A3B8', offsetY: 28 },
                        value: { show: true, fontSize: '28px', fontFamily: 'Tajawal', fontWeight: 700, color: '#1E293B', offsetY: -10, formatter: function(v) { return v + '%'; } }
                    }
                }
            },
            labels: ['نسبة النجاح']
        });
        charts.gauge.render();

        var badge = document.getElementById('gaugeBadge');
        if (!badge) {
            badge = document.createElement('span');
            badge.id = 'gaugeBadge';
            badge.className = 'badge rounded-pill px-3 py-2 mt-2';
            el.parentNode.appendChild(badge);
        }
        badge.className = 'badge rounded-pill px-3 py-2 mt-2';
        if (rate >= 90) badge.classList.add('bg-success');
        else if (rate >= 70) badge.classList.add('bg-warning', 'text-dark');
        else badge.classList.add('bg-danger');
        badge.textContent = rate + '% نجاح';
    }

    function renderDonut(data) {
        var el = document.getElementById('eventsByTypeChart');
        if (!el) return;
        if (charts.eventsByType) charts.eventsByType.destroy();
        var labels = Object.keys(data);
        var values = Object.values(data);
        if (labels.length === 0) { labels = ['لا توجد بيانات']; values = [1]; }
        var colors = ['#EC4899','#8B5CF6','#0EA5E9','#10B981','#F59E0B','#EF4444','#6366F1','#14B8A6','#F97316','#84CC16','#06B6D4','#D946EF'];
        charts.eventsByType = new ApexCharts(el, {
            chart: { type: 'donut', height: 280, fontFamily: 'Tajawal, sans-serif' },
            series: values,
            labels: labels,
            colors: colors.slice(0, labels.length),
            stroke: { show: false },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '55%' } } },
            legend: { position: 'left', labels: { fontFamily: 'Tajawal' }, itemMargin: { horizontal: 5, vertical: 3 } },
            responsive: [{ breakpoint: 768, options: { legend: { position: 'bottom' } } }]
        });
        charts.eventsByType.render();
    }

    function renderTrends(data) {
        var el = document.getElementById('dailyTrendChart');
        if (!el) return;
        if (charts.dailyTrend) charts.dailyTrend.destroy();
        var labels = data.map(function(d) { return d.date; });
        var success = data.map(function(d) { return parseInt(d.success); });
        var failed = data.map(function(d) { return parseInt(d.failed); });
        if (labels.length === 0) { labels = ['لا توجد بيانات']; success = [0]; failed = [0]; }

        var total = success.reduce(function(a,b) { return a + b; }, 0) + failed.reduce(function(a,b) { return a + b; }, 0);
        document.getElementById('trendSummary').textContent = total + ' حدث في 30 يوم';

        charts.dailyTrend = new ApexCharts(el, {
            chart: { type: 'line', height: 280, toolbar: { show: false }, fontFamily: 'Tajawal, sans-serif' },
            series: [
                { name: 'ناجح', data: success },
                { name: 'فاشل', data: failed }
            ],
            colors: ['#10B981', '#EF4444'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0.05 } },
            xaxis: { categories: labels, labels: { rotate: -45, style: { fontSize: '10px', fontFamily: 'Tajawal' } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
            grid: { borderColor: '#f1f1f1' },
            tooltip: { shared: true, intersect: false },
            legend: { position: 'top', labels: { fontFamily: 'Tajawal' } }
        });
        charts.dailyTrend.render();
    }

    function renderDuration(data) {
        var el = document.getElementById('durationChart');
        if (!el) return;
        if (charts.duration) charts.duration.destroy();
        var labels = Object.keys(data);
        var values = Object.values(data).map(function(v) { return Math.round(v); });
        if (labels.length === 0) { labels = ['لا توجد بيانات']; values = [0]; }
        charts.duration = new ApexCharts(el, {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Tajawal, sans-serif' },
            series: [{ name: 'متوسط المدة (ms)', data: values }],
            colors: ['#8B5CF6'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '70%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: labels, labels: { style: { fontSize: '9px', fontFamily: 'Tajawal' } } },
            yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
            grid: { borderColor: '#f1f1f1' },
            tooltip: { y: { formatter: function(v) { return v + ' ms'; } } }
        });
        charts.duration.render();
    }

    function refreshEventsTable() {
        var platform = document.getElementById('filterPlatform').value;
        var status = document.getElementById('filterStatus').value;
        var eventName = document.getElementById('filterEventName').value;
        document.querySelectorAll('.event-row').forEach(function(row) {
            var show = true;
            if (platform && row.dataset.platform !== platform) show = false;
            if (status && row.dataset.success !== status) show = false;
            if (eventName && row.dataset.event !== eventName) show = false;
            row.style.display = show ? '' : 'none';
        });
    }

    document.getElementById('filterPlatform').addEventListener('change', refreshEventsTable);
    document.getElementById('filterStatus').addEventListener('change', refreshEventsTable);
    document.getElementById('filterEventName').addEventListener('change', refreshEventsTable);

    function renderAlerts(alerts) {
        var container = document.getElementById('alertsContainer');
        if (!container) return;
        var badge = document.getElementById('alertCountBadge');
        var countEl = document.getElementById('alertCount');
        if (!alerts || alerts.length === 0) {
            container.innerHTML = '';
            if (badge) badge.classList.add('d-none');
            return;
        }
        var html = '';
        var dangerCount = 0;
        alerts.forEach(function(a) {
            html += '<div class="alert alert-' + a.type + ' alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-2" role="alert">'
                + '<i class="fas ' + (a.icon || 'fa-info-circle') + '"></i>'
                + '<span>' + a.message + '</span>'
                + '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" style="font-size:.75rem;"></button>'
                + '</div>';
            if (a.type === 'danger') dangerCount++;
        });
        container.innerHTML = html;
        if (badge && countEl) {
            badge.classList.remove('d-none');
            if (dangerCount > 0) {
                badge.className = 'badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill';
            } else {
                badge.className = 'badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill';
            }
            countEl.textContent = alerts.length;
        }
    }

    function updateStats(newStats) {
        document.getElementById('statTotal').textContent = newStats.total.toLocaleString();
        document.getElementById('statSuccessRate').textContent = newStats.success_rate + '%';
        document.getElementById('statFailed').textContent = newStats.failed_count.toLocaleString();
        document.getElementById('statAvgDuration').textContent = newStats.avg_duration_ms;
        document.getElementById('statToday').textContent = newStats.today_count.toLocaleString();
        document.getElementById('statUniqueTypes').textContent = newStats.unique_event_types;
        document.getElementById('gaugeSuccess').textContent = newStats.success_count.toLocaleString();
        document.getElementById('gaugeFailed').textContent = newStats.failed_count.toLocaleString();
        document.getElementById('gaugePending').textContent = newStats.pending_count.toLocaleString();
        renderGauge(newStats.success_rate);
        renderDonut(newStats.events_by_type);
        renderTrends(newStats.daily_data);
        renderDuration(newStats.duration_by_type);
        renderAlerts(newStats.alerts);
    }

    function fetchRefresh() {
        fetch('{{ route("admin.diagnostics.data") }}')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d && typeof d.success_rate !== 'undefined') {
                    updateStats(d);
                    document.getElementById('refreshTime').textContent = 'منذ لحظات';
                }
            })
            .catch(function() {});
    }

    function exportCsv() {
        var platform = document.getElementById('filterPlatform').value;
        var status = document.getElementById('filterStatus').value;
        var eventName = document.getElementById('filterEventName').value;
        var rows = document.querySelectorAll('.event-row');
        var visible = [];
        rows.forEach(function(row) {
            if (row.style.display !== 'none') {
                visible.push(row);
            }
        });
        if (visible.length === 0) { alert('لا توجد أحداث للتصدير'); return; }
        var csv = '\uFEFF';
        csv += 'المنصة,الحدث,الحالة,المدة (ms),Event ID,الرسالة,التاريخ\n';
        visible.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            var vals = [];
            cells.forEach(function(cell) { vals.push(cell.textContent.trim()); });
            csv += vals.map(function(v) { return '"' + v.replace(/"/g, '""') + '"'; }).join(',') + '\n';
        });
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'capi-events-' + new Date().toISOString().slice(0,10) + '.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    }

    renderGauge({{ $stats['success_rate'] }});
    renderDonut(@json($stats['events_by_type']));
    renderTrends(@json($stats['daily_data']));
    renderDuration(@json($stats['duration_by_type']));
    refreshEventsTable();

    setInterval(fetchRefresh, refreshInterval);
    setInterval(function() {
        var el = document.getElementById('refreshTime');
        if (el.textContent === 'الآن') el.textContent = 'منذ لحظات';
    }, 1000);
})();
</script>
@endpush
