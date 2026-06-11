@extends('admin.layouts.app')

@section('title', 'إدارة إعلانات Google')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fab fa-google text-primary me-2"></i>إدارة إعلانات Google Ads
            </h4>
            <p class="text-muted mb-0 small">إدارة الحملات والمجموعات الإعلانية والكلمات المفتاحية</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="testConnection()">
                <i class="fas fa-plug me-1"></i>اختبار الاتصال
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                <i class="fas fa-plus me-1"></i>حملة جديدة
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Connection Status -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 {{ $connectionStatus ? 'bg-success' : 'bg-warning' }}" style="width:48px;height:48px;">
                        <i class="fab fa-google text-white fs-5"></i>
                    </div>
                    <h6 class="mb-1 fw-bold">Google Ads</h6>
                    <span class="badge {{ $connectionStatus ? 'bg-success' : 'bg-warning' }}">
                        {{ $connectionStatus ? 'متصل' : 'غير متصل' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 bg-primary" style="width:48px;height:48px;">
                        <i class="fas fa-bullhorn text-white fs-5"></i>
                    </div>
                    <h6 class="mb-1 fw-bold">الحملات</h6>
                    <span class="fs-4 fw-bold text-primary">{{ count($campaigns) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 bg-success" style="width:48px;height:48px;">
                        <i class="fas fa-play-circle text-white fs-5"></i>
                    </div>
                    <h6 class="mb-1 fw-bold">نشطة</h6>
                    <span class="fs-4 fw-bold text-success">{{ collect($campaigns)->where('status', 'ENABLED')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 bg-secondary" style="width:48px;height:48px;">
                        <i class="fas fa-pause-circle text-white fs-5"></i>
                    </div>
                    <h6 class="mb-1 fw-bold">متوقفة</h6>
                    <span class="fs-4 fw-bold text-secondary">{{ collect($campaigns)->where('status', 'PAUSED')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>الحملات الإعلانية</h6>
        </div>
        <div class="card-body p-0">
            @if(empty($campaigns))
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد حملات إعلانية</h5>
                    <p class="text-muted">ابدأ بإنشاء حملة إعلانية جديدة</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                        <i class="fas fa-plus me-1"></i>إنشاء حملة
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-3">الحملة</th>
                                <th class="border-0">النوع</th>
                                <th class="border-0">الميزانية</th>
                                <th class="border-0">الاستراتيجية</th>
                                <th class="border-0">الحالة</th>
                                <th class="border-0">التاريخ</th>
                                <th class="border-0 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-bold">{{ $campaign['name'] }}</div>
                                    <small class="text-muted">{{ $campaign['campaign_id'] }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $campaign['channel_type'] }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format($campaign['budget_amount'], 2) }}</span>
                                    <small class="text-muted">{{ $campaign['budget_currency'] }}/{{ $campaign['budget_period'] === 'DAILY' ? 'يومي' : 'أسبوعي' }}</small>
                                </td>
                                <td>
                                    <small>{{ $campaign['bidding_strategy'] }}</small>
                                </td>
                                <td>
                                    @if($campaign['status'] === 'ENABLED')
                                        <span class="badge bg-success"><i class="fas fa-circle me-1" style="font-size:6px"></i>نشط</span>
                                    @elseif($campaign['status'] === 'PAUSED')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-circle me-1" style="font-size:6px"></i>متوقف</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-circle me-1" style="font-size:6px"></i>{{ $campaign['status'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $campaign['start_date'] ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="toggleCampaign('{{ $campaign['campaign_id'] }}')" title="تشغيل/إيقاف">
                                            <i class="fas fa-{{ $campaign['status'] === 'ENABLED' ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewInsights('{{ $campaign['campaign_id'] }}')" title="الأداء">
                                            <i class="fas fa-chart-line"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="viewAdGroups('{{ $campaign['campaign_id'] }}')" title="المجموعات الإعلانية">
                                            <i class="fas fa-layer-group"></i>
                                        </button>
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

    <!-- Ad Groups Panel (Hidden by default) -->
    <div class="card border-0 shadow-sm mt-4 d-none" id="adGroupsPanel">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2"></i>المجموعات الإعلانية</h6>
            <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('adGroupsPanel').classList.add('d-none')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body" id="adGroupsContent">
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">جاري التحميل...</p>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.google-ads.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fab fa-google text-primary me-2"></i>إنشاء حملة جديدة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الحملة</label>
                        <input type="text" class="form-control" name="name" required placeholder="مثال: حملة العناية بالبشرة - يونيو 2026">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الميزانية اليومية</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="budget_amount" required min="1" value="50" step="0.01">
                                <span class="input-group-text">ILS</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">استراتيجية المزايدة</label>
                            <select class="form-select" name="bidding_strategy" required>
                                <option value="MAXIMIZE_CONVERSIONS">تعظيم التحويلات</option>
                                <option value="TARGET_CPA">CPA المستهدف</option>
                                <option value="MANUAL_CPC">CPC يدوي</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ البداية</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ النهاية</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>إنشاء الحملة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Insights Modal -->
<div class="modal fade" id="insightsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-chart-line me-2"></i>أداء الحملة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="insightsContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    fetch('{{ route("admin.google-ads.test-connection") }}')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(err => alert('خطأ: ' + err.message));
}

function toggleCampaign(campaignId) {
    if (!confirm('هل أنت متأكد من تغيير حالة الحملة؟')) return;

    fetch(`/admin/google-ads/${encodeURIComponent(campaignId)}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => alert('خطأ: ' + err.message));
}

function viewInsights(campaignId) {
    const modal = new bootstrap.Modal(document.getElementById('insightsModal'));
    modal.show();

    document.getElementById('insightsContent').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';

    fetch(`/admin/google-ads/${encodeURIComponent(campaignId)}/insights`)
        .then(r => r.json())
        .then(data => {
            if (!data || Object.keys(data).length === 0) {
                document.getElementById('insightsContent').innerHTML = '<div class="text-center py-3 text-muted">لا توجد بيانات أداء</div>';
                return;
            }

            document.getElementById('insightsContent').innerHTML = `
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">المشاهدات</div>
                                <div class="fs-4 fw-bold">${(data.impressions || 0).toLocaleString()}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">النقرات</div>
                                <div class="fs-4 fw-bold text-primary">${(data.clicks || 0).toLocaleString()}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">التكلفة</div>
                                <div class="fs-4 fw-bold text-danger">${(data.cost || 0).toFixed(2)} ₪</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">التحويلات</div>
                                <div class="fs-4 fw-bold text-success">${(data.conversions || 0).toFixed(1)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">ROAS</div>
                                <div class="fs-4 fw-bold ${(data.roas || 0) >= 3 ? 'text-success' : 'text-warning'}">${(data.roas || 0).toFixed(1)}x</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">CTR</div>
                                <div class="fs-4 fw-bold text-info">${((data.ctr || 0) * 100).toFixed(2)}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            document.getElementById('insightsContent').innerHTML = '<div class="text-center py-3 text-danger">خطأ في تحميل البيانات</div>';
        });
}

function viewAdGroups(campaignId) {
    const panel = document.getElementById('adGroupsPanel');
    panel.classList.remove('d-none');

    document.getElementById('adGroupsContent').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';

    fetch(`/admin/google-ads/${encodeURIComponent(campaignId)}/ad-groups`)
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) {
                document.getElementById('adGroupsContent').innerHTML = '<div class="text-center py-3 text-muted">لا توجد مجموعات إعلانية</div>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>المجموعة الإعلانية</th><th>النوع</th><th>CPC</th><th>الحالة</th></tr></thead><tbody>';

            data.forEach(ag => {
                const statusBadge = ag.status === 'ENABLED' ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">متوقف</span>';
                html += `<tr>
                    <td><strong>${ag.name}</strong><br><small class="text-muted">${ag.ad_group_id}</small></td>
                    <td><small>${ag.type}</small></td>
                    <td>${ag.cpc_bid > 0 ? ag.cpc_bid.toFixed(2) + ' ₪' : '-'}</td>
                    <td>${statusBadge}</td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            document.getElementById('adGroupsContent').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('adGroupsContent').innerHTML = '<div class="text-center py-3 text-danger">خطأ في تحميل البيانات</div>';
        });
}
</script>
@endsection
