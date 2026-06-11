@extends('admin.layouts.app')
@section('title', 'Audience Data Upload')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold"><i class="fas fa-upload text-success me-2"></i>Audience Data Upload</h4><p class="text-muted mb-0 small">رفع بيانات العملاء إلى Meta Custom Audiences</p></div>
        <a href="{{ route('admin.meta-tools.audience-template') }}" class="btn btn-outline-success btn-sm"><i class="fas fa-download me-1"></i>تحميل قالب CSV</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold"><i class="fas fa-file-csv me-2"></i>رفع ملف CSV</h6></div>
                <div class="card-body">
                    <form id="csvUploadForm">
                        @csrf
                        <div class="mb-3"><label class="form-label fw-bold">الجمهور المستهدف</label>
                            <select class="form-select" name="audience_id" required>
                                <option value="">اختر جمهور...</option>
                                @foreach($audiences as $aud)
                                    <option value="{{ $aud->id }}">{{ $aud->name }} ({{ $aud->platform_audience_id ?? 'draft' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label fw-bold">ملف CSV</label><input type="file" class="form-control" name="csv_file" accept=".csv,.txt" required></div>
                        <div class="text-muted small mb-3">الصيغة: email,phone,first_name,last_name,city,country,external_id</div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-upload me-1"></i>رفع البيانات</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold"><i class="fas fa-phone me-2"></i>رفع أرقام هواتف</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-bold">الجمهور</label><select class="form-select" id="phoneAudience"><option value="">اختر...</option>@foreach($audiences as $aud)<option value="{{ $aud->id }}">{{ $aud->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label fw-bold">الأرقام (سطر لكل رقم)</label><textarea class="form-control" id="phoneNumbers" rows="4" placeholder="0591234567&#10;0599876543"></textarea></div>
                    <button class="btn btn-primary w-100" onclick="uploadPhones()"><i class="fas fa-upload me-1"></i>رفع الأرقام</button>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold"><i class="fas fa-envelope me-2"></i>رفع بريد إلكتروني</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-bold">الجمهور</label><select class="form-select" id="emailAudience"><option value="">اختر...</option>@foreach($audiences as $aud)<option value="{{ $aud->id }}">{{ $aud->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label fw-bold">البريد الإلكتروني</label><textarea class="form-control" id="emailAddresses" rows="4" placeholder="user@example.com"></textarea></div>
                    <button class="btn btn-info w-100" onclick="uploadEmails()"><i class="fas fa-upload me-1"></i>رفع البريد</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('csvUploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('{{ route("admin.meta-tools.audience-upload-csv") }}', { method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:formData })
    .then(r=>r.json()).then(d=>{ alert(d.success ? '✅ '+d.message : '❌ '+d.message); });
});
function uploadPhones() {
    const audienceId = document.getElementById('phoneAudience').value;
    const phones = document.getElementById('phoneNumbers').value.split('\n').map(p=>p.trim()).filter(p=>p);
    if (!audienceId || !phones.length) return alert('اختر جمهور وأدخل أرقام');
    fetch('{{ route("admin.meta-tools.audience-upload-phones") }}', { method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({audience_id:audienceId, phones}) })
    .then(r=>r.json()).then(d=>{ alert(d.success ? '✅ تم رفع '+d.uploaded+' رقم' : '❌ '+d.message); });
}
function uploadEmails() {
    const audienceId = document.getElementById('emailAudience').value;
    const emails = document.getElementById('emailAddresses').value.split('\n').map(e=>e.trim()).filter(e=>e);
    if (!audienceId || !emails.length) return alert('اختر جمهور وأدخل بريد');
    fetch('{{ route("admin.meta-tools.audience-upload-emails") }}', { method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify({audience_id:audienceId, emails}) })
    .then(r=>r.json()).then(d=>{ alert(d.success ? '✅ تم رفع '+d.uploaded+' بريد' : '❌ '+d.message); });
}
</script>
@endsection