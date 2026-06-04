@extends('admin.layouts.app')
@section('title', 'إدارة الإعلانات')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3"><i class="fas fa-check-circle"></i> {{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Show config status --}}
@php
    $anyConfigured = false;
    foreach($platforms ?? [] as $p) { if($p['configured']) { $anyConfigured = true; break; } }
@endphp
@if(!$anyConfigured && count($platforms ?? []) > 0)
<div class="alert alert-info mb-3">
    <i class="fas fa-key"></i> <b>لا توجد منصات مهيأة بعد.</b> لإضافة اتصال OAuth بضغطة زر، افتح ملف <code>.env</code> وأزل التعليق عن المفاتيح المطلوبة. مفاتيح Facebook موجودة بالنهاية كقالب جاهز.
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-bullhorn" style="color:var(--pink-600);margin-left:8px;"></i> إدارة الإعلانات</h1>
        <p class="text-muted small mb-0">ربط وإدارة حسابات الإعلانات عبر جميع المنصات الاجتماعية</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#manualTokenModal"><i class="fas fa-key"></i> ربط يدوي</button>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#connectPlatformModal"><i class="fas fa-link"></i> ربط OAuth</button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#1877F2;color:#fff"><i class="fas fa-plug"></i></div><div class="stat-value-new">{{ $connectedCount ?? 0 }}</div><div class="stat-label-new">حسابات متصلة</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#8B5CF6;color:#fff"><i class="fas fa-bullhorn"></i></div><div class="stat-value-new">{{ $totalCampaigns ?? 0 }}</div><div class="stat-label-new">الحملات</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#10B981;color:#fff"><i class="fas fa-play"></i></div><div class="stat-value-new">{{ $activeCount ?? 0 }}</div><div class="stat-label-new">نشطة</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#f59e0b;color:#fff"><i class="fas fa-pause"></i></div><div class="stat-value-new">{{ $pausedCount ?? 0 }}</div><div class="stat-label-new">متوقفة</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#6366f1;color:#fff"><i class="fas fa-image"></i></div><div class="stat-value-new">{{ $creatives->count() ?? 0 }}</div><div class="stat-label-new">تصميمات</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#EC4899;color:#fff"><i class="fas fa-sync-alt"></i></div><div class="stat-value-new"><small>تلقائي</small></div><div class="stat-label-new">مزامنة كل ساعة</div></div></div>
</div>

{{-- Platform Connection Status --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold"><i class="fas fa-share-alt" style="color:var(--pink-600);margin-left:6px;"></i> حالة ربط المنصات</div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($platforms ?? [] as $key => $p)
            <div class="col-md-4 col-lg-3">
                <div class="border rounded-3 p-3 text-center h-100">
                    <i class="{{ $p['icon'] }} fa-2x mb-2" style="color:{{ $p['color'] }};"></i>
                    <div class="fw-bold small">{{ $p['name'] }}</div>
                    @if($p['connected'])
                        <span class="badge bg-success"><i class="fas fa-check"></i> متصل</span>
                        <div class="text-muted small mt-1">{{ \Carbon\Carbon::parse($p['connected_at'])->diffForHumans() ?? '' }}</div>
                        <form method="POST" action="{{ route('admin.oauth.disconnect', $key) }}" class="mt-2" onsubmit="return confirm('قطع الاتصال مع {{ $p['name'] }}؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="fas fa-unlink"></i> قطع</button>
                        </form>
                    @elseif($p['configured'] && $p['has_oauth'])
                        <a href="{{ route('admin.oauth.redirect', $key) }}" class="btn btn-sm mt-2" style="background:{{ $p['color'] }};color:#fff;">
                            <i class="{{ $p['icon'] }}"></i> ربط
                        </a>
                    @elseif($p['install_mode'])
                        <span class="badge bg-info">تثبيت المتجر</span>
                    @else
                        <span class="badge bg-secondary">مفاتيح مفقودة</span>
                        <div class="text-muted small mt-1" style="font-size:10px;line-height:1.3;">
                            أضف في .env:<br>
                            @if($key === 'meta')META_APP_ID / META_APP_SECRET
                            @elseif($key === 'tiktok')TIKTOK_APP_ID / TIKTOK_APP_SECRET
                            @elseif($key === 'google')GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET
                            @elseif($key === 'snapchat')SNAPCHAT_CLIENT_ID / SNAPCHAT_CLIENT_SECRET
                            @elseif($key === 'pinterest')PINTEREST_APP_ID / PINTEREST_APP_SECRET
                            @elseif($key === 'twitter')TWITTER_CLIENT_ID / TWITTER_CLIENT_SECRET
                            @elseif($key === 'linkedin')LINKEDIN_CLIENT_ID / LINKEDIN_CLIENT_SECRET
                            @elseif($key === 'shopify')SHOPIFY_API_KEY / SHOPIFY_API_SECRET
                            @else{{ strtoupper($key) }}_CLIENT_ID / {{ strtoupper($key) }}_CLIENT_SECRET
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Connected Meta Accounts --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span><i class="fab fa-facebook" style="color:#1877F2;margin-left:6px;"></i> حسابات فيسبوك المتصلة</span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" onclick="syncAllCampaigns()"><i class="fas fa-sync-alt"></i> مزامنة الكل</button>
            <a href="{{ route('admin.oauth.redirect', 'meta') }}" class="btn btn-sm btn-primary" style="background:#1877F2;border-color:#1877F2;"><i class="fab fa-facebook"></i> ربط OAuth</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="accounts-container">
            @forelse($accounts ?? [] as $acc)
            <div class="border-bottom p-3 d-flex justify-content-between align-items-center" id="account-{{ $acc->id }}">
                <div>
                    <b>{{ $acc->name ?? 'Unnamed' }}</b><br>
                    <span class="text-muted small">ID: {{ $acc->ad_account_id }}</span>
                    <span class="badge bg-{{ $acc->account_status === 'active' ? 'success' : 'secondary' }} ms-2">{{ $acc->account_status }}</span>
                    @if($acc->last_synced_at)<br><span class="text-muted small"><i class="fas fa-clock"></i> آخر مزامنة: {{ $acc->last_synced_at->diffForHumans() }}</span>@endif
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="syncAccount({{ $acc->id }})"><i class="fas fa-sync-alt"></i></button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteAccount({{ $acc->id }})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            @empty
            <p class="text-muted text-center py-4 mb-0">لا توجد حسابات إعلانية متصلة. استخدم زر "ربط OAuth" أو "ربط يدوي".</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Campaigns --}}
<div class="card">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list" style="color:var(--pink-600);margin-left:6px;"></i> الحملات ({{ $totalCampaigns ?? 0 }})</span>
        <span class="text-muted small">آخر 50 حملة</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>اسم الحملة</th>
                    <th>الحساب</th>
                    <th>الهدف</th>
                    <th>الميزانية</th>
                    <th>الحالة</th>
                    <th>آخر مزامنة</th>
                    <th style="width:100px">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns ?? [] as $c)
                <tr>
                    <td><b>{{ $c->name }}</b></td>
                    <td><span class="text-muted small">{{ $c->adAccount->name ?? '-' }}</span></td>
                    <td><span class="badge bg-light text-dark">{{ $c->objective ?: '-' }}</span></td>
                    <td>{{ $c->daily_budget ? number_format($c->daily_budget, 2) . ' ' . ($c->adAccount->currency ?? 'ILS') : '-' }}</td>
                    <td><span class="badge bg-{{ $c->status === 'ACTIVE' ? 'success' : ($c->status === 'PAUSED' ? 'warning text-dark' : 'secondary') }}">{{ $c->status }}</span></td>
                    <td class="text-muted small">{{ $c->last_synced_at ? $c->last_synced_at->diffForHumans() : '-' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleCampaign('{{ $c->id }}')" title="تبديل"><i class="fas fa-{{ $c->status === 'ACTIVE' ? 'pause' : 'play' }}"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCampaign('{{ $c->id }}')" title="حذف"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد حملات بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Manual Token Modal --}}
<div class="modal fade" id="manualTokenModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-key"></i> ربط يدوي بـ Access Token</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="alert alert-info small mb-3">
                <i class="fas fa-info-circle"></i> للفيسبوك: احصل على الرمز من <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Graph API Explorer</a> مع صلاحيات ads_management, ads_read
            </div>
            <div class="alert alert-secondary small" id="manual-result" style="display:none"></div>
            <div class="mb-3"><label class="fw-bold">Access Token</label><input class="form-control font-monospace" id="manual-token" placeholder="EAAB..."></div>
            <button class="btn btn-primary w-100" id="btn-manual-connect" onclick="connectManual()">
                <span id="btn-manual-text"><i class="fas fa-link"></i> ربط</span>
                <span id="btn-manual-spin" class="d-none"><span class="spinner-border spinner-border-sm"></span> جاري...</span>
            </button>
        </div>
    </div></div>
</div>

{{-- OAuth Platform Selector Modal --}}
<div class="modal fade" id="connectPlatformModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-link"></i> ربط منصة إعلانية</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="text-muted small mb-3">اختر المنصة للمتابعة عبر OAuth — سيتم تحويلك لتسجيل الدخول ومنح الصلاحيات تلقائياً</p>
            <div class="row g-3">
                @foreach($platforms ?? [] as $key => $p)
                @if($p['has_oauth'])
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 text-center h-100">
                        <i class="{{ $p['icon'] }} fa-3x mb-2" style="color:{{ $p['color'] }};"></i>
                        <div class="fw-bold">{{ $p['name'] }}</div>
                        <div class="text-muted small mb-2">{{ $p['configured'] ? 'جاهز للربط' : 'يحتاج إعداد' }}</div>
                        @if($p['connected'])
                            <span class="badge bg-success">متصل بالفعل</span>
                        @elseif($p['configured'])
                            <a href="{{ route('admin.oauth.redirect', $key) }}" class="btn btn-sm mt-1 w-100" style="background:{{ $p['color'] }};color:#fff;">
                                <i class="{{ $p['icon'] }}"></i> ربط OAuth
                            </a>
                        @else
                            <span class="badge bg-warning text-dark">ادخل المفاتيح أولاً</span>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div></div>
</div>

@endsection

@push('scripts')
<script>
const BASE = window.location.origin + window.location.pathname.replace(/\/admin\/ads.*/, '');

function toggleBtn(id, loading) {
    document.getElementById('btn-' + id + '-text').classList[loading ? 'add' : 'remove']('d-none');
    document.getElementById('btn-' + id + '-spin').classList[loading ? 'remove' : 'add']('d-none');
    document.getElementById('btn-' + id).disabled = loading;
}

async function connectManual() {
    const token = document.getElementById('manual-token').value.trim();
    if (!token) return alert('أدخل رمز الوصول');
    const el = document.getElementById('manual-result');
    toggleBtn('manual-connect', true);
    try {
        const r = await fetch(BASE + '/admin/ads/accounts/connect', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ access_token: token })
        });
        const d = await r.json();
        el.style.display = 'block';
        el.className = 'alert alert-' + (d.success ? 'success' : 'danger') + ' small';
        el.textContent = d.message || (d.success ? 'تم الربط' : 'فشل');
        if (d.success) setTimeout(() => location.reload(), 1200);
    } catch (e) {
        el.style.display = 'block'; el.className = 'alert alert-danger small'; el.textContent = 'فشل الاتصال';
    } finally { toggleBtn('manual-connect', false); }
}

async function syncAccount(id) {
    toggleBtn('sync-all', true);
    try {
        const r = await fetch(BASE + '/admin/ads/sync', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        const d = await r.json();
        location.reload();
    } catch (e) { alert('فشل'); toggleBtn('sync-all', false); }
}

async function syncAllCampaigns() { toggleBtn('sync-all', true); await syncAccount(0); }

async function deleteAccount(id) {
    if (!confirm('حذف هذا الحساب وجميع حملاته؟')) return;
    try {
        const r = await fetch(BASE + '/admin/ads/accounts/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        if (r.ok) location.reload(); else { const d = await r.json(); alert(d.message || 'فشل'); }
    } catch (e) { alert('فشل'); }
}

async function toggleCampaign(id) {
    try {
        const r = await fetch(BASE + '/admin/ads/campaigns/' + id + '/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        const d = await r.json();
        if (d.success) location.reload(); else alert(d.message || 'فشل');
    } catch (e) { alert('فشل'); }
}

async function deleteCampaign(id) {
    if (!confirm('حذف هذه الحملة؟')) return;
    try {
        const r = await fetch(BASE + '/admin/ads/campaigns/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        if (r.ok) location.reload(); else { const d = await r.json(); alert(d.message || 'فشل'); }
    } catch (e) { alert('فشل'); }
}

document.getElementById('manualTokenModal').addEventListener('show.bs.modal', function () {
    document.getElementById('manual-result').style.display = 'none';
    document.getElementById('manual-token').value = '';
});

@if($autoSync ?? false)
(function() {
    toggleBtn('sync-all', true);
    fetch(BASE + '/admin/ads/sync', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
        .finally(() => location.reload());
})();
@endif
</script>
@endpush
