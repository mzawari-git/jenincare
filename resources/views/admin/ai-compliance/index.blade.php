@php
$settings = app(\App\Models\Setting::class);
@endphp
@extends('admin.layouts.app')
@section('title', 'الامتثال AI')
@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">🤖 لوحة الامتثال AI</h1>
        <div>
            <a href="{{ route('admin.trigger-words.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> إدارة الكلمات الممنوعة
            </a>
            <button id="refreshHealthBtn" class="btn btn-outline-success">
                <i class="fas fa-sync-alt"></i> تحديث الصحة
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($healthScores as $platform => $health)
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small text-uppercase">{{ $platform }}</div>
                    <div class="h2 mb-0 health-score" data-platform="{{ $platform }}"
                         style="color: {{ $health['score'] >= 80 ? '#28a745' : ($health['score'] >= 50 ? '#ffc107' : '#dc3545') }}">
                        {{ $health['score'] }}
                    </div>
                    <small class="text-muted">{{ $health['status'] }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">مزودي AI</h5>
                </div>
                <div class="card-body">
                    @foreach($aiProviders as $name => $provider)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>{{ $provider['name'] }}</span>
                        <span class="badge {{ $provider['available'] ? 'bg-success' : 'bg-secondary' }}">
                            {{ $provider['available'] ? 'متاح' : 'غير متاح' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">إجمالي الكلمات الممنوعة</div>
                    <div class="h2 mb-0">{{ $triggerWordCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">الكلمات النشطة</div>
                    <div class="h2 mb-0">{{ $activeTriggerWordCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">اختبار التنقية</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">النص</label>
                        <textarea id="testText" class="form-control" rows="3" placeholder="أدخل نصاً لاختبار التنقية..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المنصة</label>
                        <select id="testPlatform" class="form-select">
                            <option value="facebook">Facebook</option>
                            <option value="tiktok">TikTok</option>
                            <option value="google">Google</option>
                        </select>
                    </div>
                    <button id="runTestBtn" class="btn btn-primary">تشغيل الاختبار</button>
                    <div id="testResult" class="mt-3" style="display:none;">
                        <hr>
                        <h6>النتيجة:</h6>
                        <div id="resultContent" class="p-3 rounded bg-light"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">سجل التنقية (آخر 20)</h5>
                </div>
                <div class="table-responsive" style="max-height:300px;">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>المنصة</th>
                                <th>الحدث</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sanitizationLogs as $log)
                            <tr>
                                <td>{{ $log->platform }}</td>
                                <td>{{ $log->event_name }}</td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($recentBlocks->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-danger">⚠️ الأحداث المحظورة مؤخراً</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>المنصة</th>
                        <th>الحدث</th>
                        <th>السبب</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBlocks as $block)
                    <tr>
                        <td>{{ $block->platform }}</td>
                        <td>{{ $block->event_name }}</td>
                        <td class="text-danger">{{ $block->error_message }}</td>
                        <td>{{ $block->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('refreshHealthBtn').addEventListener('click', function() {
    fetch('{{ route("admin.ai-compliance.refresh-health") }}')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            Object.keys(d.scores).forEach(function(platform) {
                var score = d.scores[platform];
                var el = document.querySelector('.health-score[data-platform="' + platform + '"]');
                if (el) {
                    el.textContent = score.score;
                    el.style.color = score.score >= 80 ? '#28a745' : (score.score >= 50 ? '#ffc107' : '#dc3545');
                }
            });
        });
});

document.getElementById('runTestBtn').addEventListener('click', function() {
    var text = document.getElementById('testText').value;
    var platform = document.getElementById('testPlatform').value;

    fetch('{{ route("admin.ai-compliance.test") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ text: text, platform: platform }),
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        var resultDiv = document.getElementById('testResult');
        var content = document.getElementById('resultContent');
        resultDiv.style.display = 'block';

        var html = '<div class="mb-2"><strong>الأصلي:</strong><br>' + d.original + '</div>';
        html += '<div class="mb-2"><strong>بعد التنقية:</strong><br>' + d.sanitized + '</div>';
        if (d.blocked) {
            html += '<div class="text-danger"><strong>محظور:</strong> ' + d.reason + '</div>';
        }
        content.innerHTML = html;
    });
});
</script>
@endpush
