@extends('admin.layouts.app')
@section('title', 'IP المراجعين')
@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">🛡️ IP المراجعين</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIpModal">
            <i class="fas fa-plus"></i> إضافة IP
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>IP</th>
                        <th>المصدر</th>
                        <th>ISP</th>
                        <th>الحالة</th>
                        <th>تحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ips as $ip)
                    <tr>
                        <td><code>{{ $ip->ip_address }}</code></td>
                        <td>{{ $ip->source ?? '-' }}</td>
                        <td>{{ $ip->isp ?? '-' }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-ip" type="checkbox"
                                    data-id="{{ $ip->id }}" {{ $ip->active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <form action="{{ route('admin.reviewer-ips.destroy', $ip) }}" method="POST"
                                onsubmit="return confirm('حذف {{ $ip->ip_address }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا توجد IPs</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $ips->links() }}</div>
    </div>
</div>

<div class="modal fade" id="addIpModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.reviewer-ips.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5>إضافة IP مراجع</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" required placeholder="192.168.1.1">
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">المصدر</label>
                        <select name="source" class="form-select">
                            <option value="meta">Meta</option>
                            <option value="tiktok">TikTok</option>
                            <option value="google">Google</option>
                            <option value="datacenter">Datacenter</option>
                            <option value="manual">إضافة يدوية</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">ISP</label>
                        <input type="text" name="isp" class="form-control" placeholder="AWS, Google Cloud...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.toggle-ip').forEach(function(cb) {
    cb.addEventListener('change', function() {
        fetch('{{ url("/admin/reviewer-ips/") }}/' + this.dataset.id + '/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    });
});
</script>
@endpush
