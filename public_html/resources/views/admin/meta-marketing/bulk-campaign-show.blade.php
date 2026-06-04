@extends('admin.layouts.app')
@section('title', 'تفاصيل الحملة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h4 mb-1"><i class="fas fa-paper-plane" style="color:var(--pink-600);margin-left:8px;"></i> {{ $campaign->name }}</h1><p class="text-muted small mb-0">{{ $campaign->created_at->format('Y-m-d H:i') }}</p></div>
    <a href="{{ route('admin.leads-hub.bulk-campaigns') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-value-new">{{ $campaign->total_recipients }}</div><div class="stat-label-new">المستلمين</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-value-new" style="color:#10b981;">{{ $campaign->sent_count }}</div><div class="stat-label-new">تم الإرسال</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-value-new" style="color:#ef4444;">{{ $campaign->failed_count }}</div><div class="stat-label-new">فشل</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-value-new" style="color:#3b82f6;">{{ $campaign->read_count }}</div><div class="stat-label-new">تمت القراءة</div></div></div>
    <div class="col-md-2"><div class="stat-card-new"><div class="stat-value-new" style="color:#8b5cf6;">{{ $campaign->reply_count }}</div><div class="stat-label-new">ردود</div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header fw-bold small">نص الرسالة</div>
    <div class="card-body"><p class="mb-1">{{ $campaign->message_text }}</p>
        @if($campaign->quick_replies)
        <div class="mt-2">@foreach($campaign->quick_replies as $qr)<span class="badge bg-pink me-1">{{ $qr }}</span>@endforeach</div>
        @endif
    </div>
</div>

@if($campaign->recipient_filters)
<div class="card mb-3">
    <div class="card-header fw-bold small">معايير التصفية</div>
    <div class="card-body">
        @foreach($campaign->recipient_filters as $k => $v)
        <span class="badge bg-light text-dark me-1">{{ $k }}: {{ $v }}</span>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header fw-bold small">سجل الإرسال ({{ count($log) }})</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light small"><tr><th>العميل</th><th>PSID</th><th>الحالة</th><th>السبب</th><th>الوقت</th></tr></thead>
            <tbody>
                @foreach($log as $entry)
                <tr>
                    <td class="small">{{ $leads[$entry['lead_id']]->sender_name ?? '-' }}</td>
                    <td class="small text-muted">{{ $entry['psid'] }}</td>
                    <td>@if($entry['status']=='sent')<span class="badge bg-success">تم</span>@else<span class="badge bg-danger">فشل</span>@endif</td>
                    <td class="small text-muted">{{ $entry['reason'] ?? '-' }}</td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($entry['timestamp'])->format('H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
