@extends('admin.layouts.app')

@section('title', 'تحليلات متقدمة')

@push('styles')
<style>
.analytics-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 24px; border-radius: 16px; margin-bottom: 24px; }
.metric-card { background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; text-align: center; transition: all .3s; }
.metric-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,.1); }
.metric-value { font-size: 2.5rem; font-weight: 800; color: var(--gray-800); }
.metric-label { color: var(--gray-500); font-size: .9rem; font-weight: 500; }
.metric-change { font-size: .85rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; margin-top: 8px; display: inline-block; }
.metric-change.positive { background: #dcfce7; color: #16a34a; }
.metric-change.negative { background: #fee2e2; color: #dc2626; }
.chart-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
.chart-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
.table-orders th { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-500); font-weight: 600; }
.product-rank { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
.product-rank.top { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
.segment-badge { padding: 6px 14px; border-radius: 20px; font-size: .8rem; font-weight: 600; }
.segment-badge.vip { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
.segment-badge.returning { background: #dbeafe; color: #1e40af; }
.segment-badge.new { background: #dcfce7; color: #16a34a; }
.filter-btn { padding: 8px 20px; border-radius: 10px; font-size: .875rem; font-weight: 500; border: 1px solid #e2e8f0; background: #fff; color: var(--gray-600); transition: all .2s; }
.filter-btn:hover, .filter-btn.active { background: var(--pink-600); color: #fff; border-color: var(--pink-600); }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="analytics-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-1" style="font-weight:800;"><i class="fas fa-chart-line me-2"></i> تحليلات المتجر</h2>
            <p class="mb-0 opacity-75">تحليل شامل لأداء المتجر وسلوك العملاء</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.analytics.export', ['period' => $period]) }}" class="btn btn-light">
                <i class="fas fa-download me-1"></i> تصدير
            </a>
        </div>
    </div>
    
    {{-- Period Filter --}}
    <div class="mt-4 d-flex gap-2 flex-wrap">
        @php $periods = [7 => '7 أيام', 30 => '30 يوم', 90 => '3 أشهر', 365 => 'سنة']; @endphp
        @foreach($periods as $days => $label)
        <a href="{{ route('admin.analytics.index', ['period' => $days]) }}" 
           class="filter-btn {{ $period == $days ? 'active' : '' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- Overview Metrics --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="metric-card">
            <div class="metric-value" style="color: #db2777;">{{ number_format($overview['revenue'], 0) }} ₪</div>
            <div class="metric-label">إجمالي الإيرادات</div>
            <span class="metric-change {{ $overview['revenueGrowth'] >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $overview['revenueGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($overview['revenueGrowth']) }}%
            </span>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="metric-card">
            <div class="metric-value" style="color: #0284c7;">{{ number_format($overview['orders']) }}</div>
            <div class="metric-label">عدد الطلبات</div>
            <span class="metric-change {{ $overview['ordersGrowth'] >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $overview['ordersGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($overview['ordersGrowth']) }}%
            </span>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="metric-card">
            <div class="metric-value" style="color: #16a34a;">{{ number_format($overview['avgOrderValue']) }} ₪</div>
            <div class="metric-label">متوسط قيمة الطلب</div>
            <span class="metric-change {{ $overview['avgOrderValueGrowth'] >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $overview['avgOrderValueGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($overview['avgOrderValueGrowth']) }}%
            </span>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="metric-card">
            <div class="metric-value" style="color: #8b5cf6;">{{ $overview['conversionRate'] }}%</div>
            <div class="metric-label">معدل التحويل</div>
            <span class="metric-change positive">
                <i class="fas fa-users"></i> {{ $overview['customers'] }} جديد
            </span>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-area me-2" style="color:var(--pink-600);"></i> الأداء عبر الوقت</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="toggleMainChart('revenue')">الإيرادات</button>
                    <button class="btn btn-outline-secondary" onclick="toggleMainChart('orders')">الطلبات</button>
                </div>
            </div>
            <div class="p-4">
                <div style="height: 320px;">
                    <canvas id="mainTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-header"><i class="fas fa-clock me-2" style="color:var(--pink-600);"></i> توزيع الطلبات بالساعة</div>
            <div class="p-4">
                <div style="height: 320px;">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Customer & Product Analytics --}}
<div class="row g-3 mb-4">
    {{-- Customer Segments --}}
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-header"><i class="fas fa-users me-2" style="color:var(--pink-600);"></i> شرائح العملاء</div>
            <div class="p-4">
                <div style="height: 250px;">
                    <canvas id="segmentsChart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="d-flex align-items-center gap-2">
                            <span class="segment-badge vip">VIP</span>
                            <small>أكثر من 1000₪</small>
                        </span>
                        <strong>{{ $customerAnalytics['segments']['vip'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="d-flex align-items-center gap-2">
                            <span class="segment-badge returning">متكرر</span>
                            <small>أكثر من طلب واحد</small>
                        </span>
                        <strong>{{ $customerAnalytics['segments']['returning'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center gap-2">
                            <span class="segment-badge new">جديد</span>
                            <small>عملاء جدد</small>
                        </span>
                        <strong>{{ $customerAnalytics['segments']['new'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Top Customers --}}
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="chart-header d-flex justify-content-between">
                <span><i class="fas fa-crown me-2" style="color:var(--pink-600);"></i> أفضل العملاء</span>
                <span class="badge bg-warning text-dark">{{ $customerAnalytics['activeCustomers'] }} عميل نشط</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-orders">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الطلبات</th>
                            <th>إجمالي المشتريات</th>
                            <th>آخر طلب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerAnalytics['topCustomers'] as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--pink-500),var(--pink-600));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $customer->name }}</div>
                                        <small class="text-muted">{{ $customer->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $customer->order_count }}</span></td>
                            <td class="fw-bold" style="color:var(--pink-600);">{{ number_format($customer->total_spent) }} ₪</td>
                            <td class="text-muted small">{{ $customer->last_order ? Carbon\Carbon::parse($customer->last_order)->diffForHumans() : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">لا توجد بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Products & Categories --}}
<div class="row g-3 mb-4">
    {{-- Top Products --}}
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-header d-flex justify-content-between">
                <span><i class="fas fa-box-open me-2" style="color:var(--pink-600);"></i> أكثر المنتجات مبيعاً</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>المنتج</th>
                            <th>المبيعات</th>
                            <th>الإيرادات</th>
                            <th>الطلبات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productAnalytics['topProducts'] as $index => $product)
                        <tr>
                            <td>
                                <div class="product-rank {{ $index < 3 ? 'top' : 'bg-light text-muted' }}">
                                    {{ $index + 1 }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($product->main_image)
                                    <img src="{{ url('files/products/' . $product->main_image) }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                                    @else
                                    <div style="width:40px;height:40px;border-radius:8px;background:var(--pink-50);display:flex;align-items:center;justify-content:center;"><i class="fas fa-box text-muted"></i></div>
                                    @endif
                                    <span class="fw-bold">{{ $product->name_ar }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">{{ $product->total_sold }} وحدة</span></td>
                            <td class="fw-bold">{{ number_format($product->total_revenue) }} ₪</td>
                            <td>{{ $product->order_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد بيانات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Category Sales --}}
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-header"><i class="fas fa-tags me-2" style="color:var(--pink-600);"></i> مبيعات الفئات</div>
            <div class="p-4">
                <div style="height: 300px;">
                    <canvas id="categoriesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Geographic & Traffic --}}
<div class="row g-3">
    {{-- Cities --}}
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="chart-header"><i class="fas fa-map-marker-alt me-2" style="color:var(--pink-600);"></i> توزيع جغرافي</div>
            <div class="p-4">
                @forelse($customerAnalytics['cityDistribution'] as $city)
                @php
                    $maxOrders = $customerAnalytics['cityDistribution']->max('order_count');
                    $percent = $maxOrders > 0 ? ($city->order_count / $maxOrders) * 100 : 0;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold">{{ $city->city }}</span>
                        <div class="text-muted small">
                            <span class="me-2">{{ $city->order_count }} طلب</span>
                            <span>{{ number_format($city->total_revenue) }} ₪</span>
                        </div>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;">
                        <div class="progress-bar" style="width: {{ $percent }}%;background:linear-gradient(90deg,var(--pink-500),var(--pink-600));border-radius:4px;"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">لا توجد بيانات</div>
                @endforelse
            </div>
        </div>
    </div>
    
    {{-- Traffic Sources --}}
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="chart-header"><i class="fas fa-globe me-2" style="color:var(--pink-600);"></i> مصادر الزيارات</div>
            <div class="p-4">
                <div style="height: 250px;">
                    <canvas id="sourcesChart"></canvas>
                </div>
                <div class="mt-3">
                    @forelse($trafficSources['sources'] as $source)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-capitalize">{{ $source->source }}</span>
                        <div>
                            <span class="badge bg-light text-dark me-2">{{ $source->count }} طلب</span>
                            <span class="text-muted small">{{ number_format($source->revenue) }} ₪</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">لا توجد بيانات</div>
                    @endforelse
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

// Main Trend Chart
const trendCtx = document.getElementById('mainTrendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($salesData['timeline']->pluck('date')) !!},
        datasets: [{
            label: 'الإيرادات',
            data: {!! json_encode($salesData['timeline']->pluck('revenue')) !!},
            borderColor: '#db2777',
            backgroundColor: 'rgba(219, 39, 119, 0.1)',
            fill: true,
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'الطلبات',
            data: {!! json_encode($salesData['timeline']->pluck('orders')) !!},
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
        plugins: { legend: { position: 'top', rtl: true } },
        scales: {
            x: { grid: { display: false } },
            y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { callback: v => v + ' ₪' } },
            y1: { position: 'right', display: false }
        }
    }
});

function toggleMainChart(type) {
    trendChart.data.datasets[0].hidden = type !== 'revenue';
    trendChart.data.datasets[1].hidden = type !== 'orders';
    trendChart.update();
    event.target.parentElement.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

// Hourly Distribution
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
const hours = Array.from({length: 24}, (_, i) => i + ':00');
const hourlyData = {!! json_encode($salesData['hourly']) !!};
new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: hours,
        datasets: [{
            label: 'الطلبات',
            data: hours.map((_, i) => hourlyData[i] || 0),
            backgroundColor: 'rgba(219, 39, 119, 0.7)',
            borderRadius: 4
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

// Customer Segments
const segmentsCtx = document.getElementById('segmentsChart').getContext('2d');
new Chart(segmentsCtx, {
    type: 'doughnut',
    data: {
        labels: ['VIP', 'متكرر', 'جديد'],
        datasets: [{
            data: [
                {{ $customerAnalytics['segments']['vip'] }},
                {{ $customerAnalytics['segments']['returning'] }},
                {{ $customerAnalytics['segments']['new'] }}
            ],
            backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', rtl: true, labels: { usePointStyle: true, padding: 15 } }
        },
        cutout: '60%'
    }
});

// Categories Chart
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($productAnalytics['categorySales']->pluck('category')) !!},
        datasets: [{
            label: 'المبيعات',
            data: {!! json_encode($productAnalytics['categorySales']->pluck('total_sold')) !!},
            backgroundColor: 'rgba(219, 39, 119, 0.8)',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#f1f5f9' } },
            y: { grid: { display: false } }
        }
    }
});

// Traffic Sources
const sourcesCtx = document.getElementById('sourcesChart').getContext('2d');
new Chart(sourcesCtx, {
    type: 'pie',
    data: {
        labels: {!! json_encode($trafficSources['sources']->pluck('source')) !!},
        datasets: [{
            data: {!! json_encode($trafficSources['sources']->pluck('count')) !!},
            backgroundColor: ['#db2777', '#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#64748b'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right', rtl: true, labels: { boxWidth: 12, padding: 10 } }
        }
    }
});
</script>
@endpush
