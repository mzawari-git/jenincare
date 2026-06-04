@extends('admin.layouts.app')
@section('title', 'حملات الرسائل الجماعية')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h4 mb-1"><i class="fas fa-history" style="color:var(--pink-600);margin-left:8px;"></i> حملات الرسائل الجماعية</h1><p class="text-muted small mb-0">سجل حملات الإرسال الجماعي عبر ماسنجر</p></div>
    <a href="{{ route('admin.leads-hub.index') }}" class="btn btn-pink btn-sm"><i class="fas fa-arrow-right"></i> العودة للمركز</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light small"><tr><th>الحملة</th><th>الحالة</th><th>المستلمين</th><th>تم الإرسال</th><th>فشل</th><th>التقدم</th><th>التاريخ</th><th></th></tr></thead>
            <tbody>
                @forelse($campaigns as $c)
                <tr>
                    <td class="fw-bold small">{{ $c->name }}</td>
                    <td>
                        @if($c->status == 'completed')<span class="badge bg-success">مكتمل</span>
                        @elseif($c->status == 'sending')<span class="badge bg-warning">قيد الإرسال</span>
                        @elseif($c->status == 'draft')<span class="badge bg-secondary">مسودة</span>
                        @else<span class="badge bg-danger">فشل</span>@endif
                    </td>
                    <td class="small">{{ $c->total_recipients }}</td>
                    <td class="small text-success">{{ $c->sent_count }}</td>
                    <td class="small text-danger">{{ $c->failed_count }}</td>
                    <td>
                        <div class="progress-thin" style="width:100px;">
                            <div class="progress-bar" style="width:{{ $c->progress_percent }}%;background:var(--pink-600);"></div>
                        </div>
                        <small>{{ $c->progress_percent }}%</small>
                    </td>
                    <td class="small text-muted">{{ $c->created_at->format('Y-m-d H:i') }}</td>
                    <td><a href="{{ route('admin.leads-hub.bulk-campaigns.show', $c) }}" class="btn btn-sm btn-outline-pink">تفاصيل</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">لا توجد حملات سابقة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $campaigns->links() }}</div>
</div>
@endsection
