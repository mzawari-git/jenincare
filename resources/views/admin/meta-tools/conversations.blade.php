@extends('admin.layouts.app')
@section('title', 'Facebook Conversations')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold"><i class="fab fa-facebook-messenger text-primary me-2"></i>Facebook Conversations</h4><p class="text-muted mb-0 small">الرد على رسائل العملاء من Facebook Messenger</p></div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger fs-6" id="unreadBadge">{{ $stats['unread'] ?? 0 }} غير مقروءة</span>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary">{{ $stats['total'] ?? 0 }}</div><div class="text-muted small">إجمالي المحادثات</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold text-danger">{{ $stats['unread'] ?? 0 }}</div><div class="text-muted small">غير مقروءة</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><div class="fs-2 fw-bold text-success">{{ $stats['today'] ?? 0 }}</div><div class="text-muted small">اليوم</div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @forelse($conversations['conversations'] ?? [] as $conv)
            <div class="d-flex align-items-center gap-3 p-3 border-bottom {{ $conv['unread_count'] > 0 ? 'bg-light' : '' }}" style="cursor:pointer" onclick="openConversation('{{ $conv['id'] }}')">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                    <i class="fab fa-facebook-messenger text-primary"></i>
                </div>
                <div class="flex-fill min-width-0">
                    <div class="d-flex justify-content-between">
                        <strong class="{{ $conv['unread_count'] > 0 ? '' : 'text-muted' }}">{{ implode(', ', $conv['participants'] ?? ['عميل']) }}</strong>
                        <small class="text-muted">{{ $conv['updated_time'] ? \Carbon\Carbon::parse($conv['updated_time'])->diffForHumans() : '' }}</small>
                    </div>
                    <div class="text-muted small text-truncate">{{ $conv['snippet'] ?? '' }}</div>
                </div>
                @if($conv['unread_count'] > 0)
                    <span class="badge bg-danger rounded-pill">{{ $conv['unread_count'] }}</span>
                @endif
            </div>
            @empty
            <div class="text-center py-5"><i class="fab fa-facebook-messenger fa-3x text-muted mb-3 opacity-25"></i><h5 class="text-muted">لا توجد محادثات</h5></div>
            @endforelse
        </div>
    </div>
</div>
<div class="modal fade" id="conversationModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">المحادثة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="messagesContainer" style="max-height:400px;overflow-y:auto;"><div class="text-center py-3"><div class="spinner-border text-primary"></div></div></div>
    <div class="modal-footer">
        <div class="input-group"><input type="text" class="form-control" id="replyMessage" placeholder="اكتب ردك..."><button class="btn btn-primary" onclick="sendReply()"><i class="fas fa-paper-plane"></i></button></div>
    </div>
</div></div></div>
<script>
let currentConversationId = null;
function openConversation(id) {
    currentConversationId = id;
    new bootstrap.Modal(document.getElementById('conversationModal')).show();
    document.getElementById('messagesContainer').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';
    fetch('{{ url('admin/meta-tools/conversations') }}/' + id + '/messages').then(r=>r.json()).then(d=>{
        if (!d.success || !d.messages.length) { document.getElementById('messagesContainer').innerHTML = '<div class="text-center text-muted py-3">لا توجد رسائل</div>'; return; }
        let html = '';
        d.messages.forEach(m => {
            html += `<div class="d-flex ${m.is_page ? 'justify-content-start' : 'justify-content-end'} mb-2">
                <div class="rounded-3 p-2 px-3" style="max-width:70%;background:${m.is_page ? '#e3f2fd' : '#dcf8c6'};">
                    <div class="small">${m.message}</div>
                    <div class="text-muted" style="font-size:10px">${m.created_time ? new Date(m.created_time).toLocaleTimeString('ar') : ''}</div>
                </div></div>`;
        });
        document.getElementById('messagesContainer').innerHTML = html;
    });
}
function sendReply() {
    const msg = document.getElementById('replyMessage').value.trim();
    if (!msg || !currentConversationId) return;
    fetch('{{ route("admin.meta-tools.conversation-reply") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({recipient_id: currentConversationId, message: msg})
    }).then(r=>r.json()).then(d=>{
        if (d.success) { document.getElementById('replyMessage').value = ''; openConversation(currentConversationId); }
        else alert('❌ '+d.message);
    });
}
</script>
@endsection