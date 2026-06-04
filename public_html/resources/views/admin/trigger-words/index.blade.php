@php
$settings = app(\App\Models\Setting::class);
@endphp
@extends('admin.layouts.app')
@section('title', 'الكلمات الممنوعة')
@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">🚫 الكلمات الممنوعة</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWordModal">
            <i class="fas fa-plus"></i> إضافة كلمة
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">كل التصنيفات</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="severity" class="form-select">
                        <option value="">كل المستويات</option>
                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">كل المنصات</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" {{ request('platform') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-filter"></i> تصفية
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>الكلمة</th>
                        <th>التصنيف</th>
                        <th>المستوى</th>
                        <th>المنصة</th>
                        <th>الإجراء</th>
                        <th>البديل</th>
                        <th>الحالة</th>
                        <th>تحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($words as $word)
                    <tr>
                        <td><code>{{ $word->word }}</code></td>
                        <td><span class="badge bg-info">{{ $word->category }}</span></td>
                        <td>
                            @switch($word->severity)
                                @case('critical') <span class="badge bg-danger">Critical</span> @break
                                @case('high') <span class="badge bg-warning text-dark">High</span> @break
                                @case('medium') <span class="badge bg-secondary">Medium</span> @break
                                @default <span class="badge bg-light text-dark">Low</span>
                            @endswitch
                        </td>
                        <td>{{ $word->platform ?? 'All' }}</td>
                        <td>
                            @switch($word->action)
                                @case('block') <span class="badge bg-dark">حظر</span> @break
                                @case('replace') <span class="badge bg-success">استبدال</span> @break
                                @default <span class="badge bg-danger">إزالة</span>
                            @endswitch
                        </td>
                        <td>{{ $word->replacement ?? '-' }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-word" type="checkbox"
                                    data-id="{{ $word->id }}"
                                    {{ $word->active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary edit-word"
                                    data-id="{{ $word->id }}"
                                    data-word="{{ $word->word }}"
                                    data-category="{{ $word->category }}"
                                    data-severity="{{ $word->severity }}"
                                    data-platform="{{ $word->platform }}"
                                    data-action="{{ $word->action }}"
                                    data-replacement="{{ $word->replacement }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.trigger-words.destroy', $word) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('حذف {{ $word->word }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد كلمات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $words->links() }}
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addWordModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.trigger-words.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">إضافة كلمة ممنوعة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">الكلمة</label>
                    <input type="text" name="word" class="form-control" required>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">التصنيف</label>
                        <select name="category" class="form-select" required>
                            <option value="medical_claims">Medical Claims</option>
                            <option value="weight_loss">Weight Loss</option>
                            <option value="financial">Financial</option>
                            <option value="before_after">Before/After</option>
                            <option value="beauty_claims">Beauty Claims</option>
                            <option value="profanity">Profanity</option>
                            <option value="discrimination">Discrimination</option>
                            <option value="test_email">Test Email</option>
                            <option value="misleading">Misleading</option>
                            <option value="scam">Scam</option>
                            <option value="meta_policy">Meta Policy</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">المستوى</label>
                        <select name="severity" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">الإجراء</label>
                        <select name="action" class="form-select" required>
                            <option value="replace">Replace</option>
                            <option value="remove">Remove</option>
                            <option value="block">Block</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">المنصة (اختياري)</label>
                        <select name="platform" class="form-select">
                            <option value="">All Platforms</option>
                            <option value="facebook">Facebook</option>
                            <option value="tiktok">TikTok</option>
                            <option value="google">Google</option>
                            <option value="snapchat">Snapchat</option>
                            <option value="pinterest">Pinterest</option>
                            <option value="twitter">Twitter</option>
                            <option value="linkedin">LinkedIn</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">البديل (لإجراء Replace)</label>
                    <input type="text" name="replacement" class="form-control" placeholder="**[text]**">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-word').forEach(function(cb) {
    cb.addEventListener('change', function() {
        fetch('{{ url("/admin/trigger-words/") }}/' + this.dataset.id + '/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    });
});
</script>
@endpush
