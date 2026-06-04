@extends('admin.layouts.app')

@section('title', 'الإشعارات')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-bell" style="color:var(--pink-600);margin-left:8px;"></i> الإشعارات</span>
        <div class="d-flex gap-2">
            <button onclick="markAllAsRead()" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-check-double"></i> تحديد الكل كمقروء
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="notificationsList">
            @forelse($notifications as $notification)
            <div class="notification-item d-flex align-items-center gap-3 p-3 border-bottom {{ $notification->read_at ? 'bg-light opacity-75' : 'bg-white' }}" 
                 data-id="{{ $notification->id }}"
                 style="transition: all .2s;">
                <div class="notification-icon flex-shrink-0" 
                     style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
                     background: {{ $notification->type === 'order' ? 'linear-gradient(135deg, #dcfce7, #bbf7d0)' : 
                                   ($notification->type === 'inventory' ? 'linear-gradient(135deg, #fef3c7, #fde68a)' : 
                                   'linear-gradient(135deg, #e0f2fe, #bae6fd)') }};">
                    <i class="fas {{ $notification->type === 'order' ? 'fa-shopping-bag' : 
                                   ($notification->type === 'inventory' ? 'fa-box' : 
                                   'fa-bell') }}" 
                       style="color: {{ $notification->type === 'order' ? '#16a34a' : 
                                        ($notification->type === 'inventory' ? '#d97706' : 
                                        '#0284c7') }};"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="mb-1 fw-bold {{ $notification->read_at ? 'text-muted' : '' }}">
                            {{ $notification->title }}
                            @if(!$notification->read_at)
                            <span class="badge bg-danger rounded-circle" style="width: 8px; height: 8px; padding: 0; display: inline-block;"></span>
                            @endif
                        </h6>
                        <small class="text-muted" style="font-size: .75rem; white-space: nowrap;">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <p class="mb-0 text-muted" style="font-size: .875rem;">{{ $notification->body }}</p>
                </div>
                <div class="flex-shrink-0 d-flex flex-column gap-1">
                    @if($notification->data['url'] ?? false)
                    <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-pink" style="padding: 4px 12px;">
                        <i class="fas fa-eye"></i>
                    </a>
                    @endif
                    @if(!$notification->read_at)
                    <button onclick="markAsRead({{ $notification->id }})" class="btn btn-sm btn-outline-secondary" style="padding: 4px 12px;">
                        <i class="fas fa-check"></i>
                    </button>
                    @endif
                    <button onclick="deleteNotification({{ $notification->id }})" class="btn btn-sm btn-outline-danger" style="padding: 4px 12px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-bell-slash mb-3" style="font-size: 3rem; opacity: .3;"></i>
                <p>لا توجد إشعارات</p>
            </div>
            @endforelse
        </div>
    </div>
    @if($notifications->hasPages())
    <div class="card-footer">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function markAsRead(id) {
    fetch(`{{ route("admin.notifications.index") }}/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('{{ route('admin.notifications.read-all') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteNotification(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الإشعار؟')) return;
    
    fetch(`{{ route("admin.notifications.index") }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-id="${id}"]`).remove();
        }
    });
}

// Real-time notifications polling (every 30 seconds)
setInterval(() => {
    fetch('{{ route('admin.notifications.unread') }}')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'block' : 'none';
            }
        });
}, 30000);
</script>
@endpush
