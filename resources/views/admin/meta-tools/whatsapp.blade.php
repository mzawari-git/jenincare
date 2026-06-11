@extends('admin.layouts.app')
@section('title', 'WhatsApp Integration')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Cloud API</h4>
            <p class="text-muted mb-0 small">إدارة رسائل WhatsApp وقوالب الرسائل</p>
        </div>
        <button class="btn btn-outline-success btn-sm" onclick="testWhatsApp()"><i class="fas fa-plug me-1"></i>اختبار الاتصال</button>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fab fa-whatsapp fa-2x text-success mb-2"></i>
                    <h6 class="fw-bold">Phone Number</h6>
                    <div class="text-muted">{{ $phoneInfo['display_phone_number'] ?? 'غير مكون' }}</div>
                    @if(!empty($phoneInfo['quality_rating']))
                        <span class="badge bg-success mt-1">Quality: {{ $phoneInfo['quality_rating'] }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold">Business Account</h6>
                    <div class="text-muted">{{ $businessProfile['name'] ?? 'غير مكون' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-paper-plane fa-2x text-info mb-2"></i>
                    <h6 class="fw-bold">Messaging Limit</h6>
                    <div class="text-muted">{{ $phoneInfo['messaging_limit_tier'] ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2"></i>إرسال رسالة</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">رقم الهاتف</label>
                    <input type="text" class="form-control" id="whatsappPhone" placeholder="0591234567">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">الرسالة</label>
                    <textarea class="form-control" id="whatsappMessage" rows="2" placeholder="اكتب رسالتك..."></textarea>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-success w-100" onclick="sendWhatsApp()"><i class="fab fa-whatsapp me-1"></i>إرسال</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function testWhatsApp() {
    fetch('{{ route("admin.meta-tools.whatsapp-test") }}').then(r=>r.json()).then(d=>{
        alert(d.success ? '✅ '+d.message : '❌ '+d.message);
    });
}
function sendWhatsApp() {
    const phone = document.getElementById('whatsappPhone').value;
    const message = document.getElementById('whatsappMessage').value;
    if (!phone || !message) return alert('أدخل الرقم والرسالة');
    fetch('{{ route("admin.meta-tools.whatsapp-send") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({phone, message})
    }).then(r=>r.json()).then(d=>{
        alert(d.success ? '✅ '+d.message : '❌ '+d.message);
    });
}
</script>
@endsection