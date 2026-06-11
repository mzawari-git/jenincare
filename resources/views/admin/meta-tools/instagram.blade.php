@extends('admin.layouts.app')
@section('title', 'Instagram Management')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold"><i class="fab fa-instagram text-danger me-2"></i>Instagram Management</h4><p class="text-muted mb-0 small">إدارة وتحليل حساب Instagram Business</p></div>
    </div>
    @if(!empty($dashboard['profile']))
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center">
            <img src="{{ $dashboard['profile']['profile_picture_url'] ?? '' }}" class="rounded-circle mb-2" style="width:64px;height:64px;" onerror="this.style.display='none'">
            <h6 class="fw-bold">{{ $dashboard['profile']['username'] ?? '' }}</h6>
            <div class="text-muted small">{{ $dashboard['profile']['name'] ?? '' }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary">{{ number_format($dashboard['followers'] ?? 0) }}</div><div class="text-muted small">المتابعون</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold text-success">{{ $dashboard['media_count'] ?? 0 }}</div><div class="text-muted small">المنشورات</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold text-warning">{{ $dashboard['engagement_rate'] ?? 0 }}%</div><div class="text-muted small">معدل التفاعل</div></div></div></div>
    </div>
    @if(!empty($dashboard['top_posts']))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold">Top Posts by Engagement</h6></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0"><thead class="bg-light"><tr><th>المنشور</th><th>النوع</th><th>إعجابات</th><th>تعليقات</th><th>التفاعل</th></tr></thead><tbody>
            @foreach($dashboard['top_posts'] as $post)
            <tr><td><small>{{ $post['caption'] }}</small></td><td><span class="badge bg-light">{{ $post['media_type'] }}</span></td><td>{{ $post['likes'] }}</td><td>{{ $post['comments'] }}</td><td><strong>{{ $post['engagement'] }}</strong></td></tr>
            @endforeach
            </tbody></table>
        </div>
    </div>
    @endif
    @else
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fab fa-instagram fa-4x text-muted mb-3 opacity-25"></i><h5 class="text-muted">Instagram غير مكون</h5><p class="text-muted">قم بتفعيل Instagram Business ID في إعدادات الحساب</p></div></div>
    @endif
</div>
@endsection