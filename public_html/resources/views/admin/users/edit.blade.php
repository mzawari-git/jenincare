@extends('admin.layouts.app')

@section('title', 'تعديل مستخدم: ' . $user->name)

@push('styles')
<style>
.user-header { background: linear-gradient(135deg, var(--pink-600), var(--pink-500)); color: #fff; padding: 24px; border-radius: 16px; margin-bottom: 24px; }
.user-avatar-lg { width: 80px; height: 80px; border-radius: 50%; background: #fff; color: var(--pink-600); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; }
.password-toggle { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray-400); cursor: pointer; }
.password-toggle:hover { color: var(--pink-600); }
.form-section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
.form-section-title { font-weight: 700; color: var(--gray-800); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.form-section-title i { color: var(--pink-600); }
</style>
@endpush

@section('content')

{{-- User Header --}}
<div class="user-header d-flex align-items-center gap-4">
    <div class="user-avatar-lg">{{ substr($user->name, 0, 1) }}</div>
    <div>
        <h2 class="mb-1" style="font-weight:800;">{{ $user->name }}</h2>
        <p class="mb-0 opacity-75">{{ $user->email }} · {{ $user->phone ?? 'لا يوجد هاتف' }}</p>
        <span class="badge bg-light text-dark mt-2">{{ $user->role === 'admin' ? 'مدير' : ($user->role === 'b2b' ? 'B2B' : 'زبون') }}</span>
    </div>
</div>

<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf @method('PUT')
    
    <div class="row g-4">
        {{-- Basic Info --}}
        <div class="col-lg-8">
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-user"></i> المعلومات الأساسية
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الاسم الكامل</label>
                        <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">رقم الهاتف</label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $user->phone) }}" placeholder="059xxxxxxx">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الدور</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror">
                            <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>🛍️ زبون</option>
                            <option value="b2b" {{ old('role', $user->role) === 'b2b' ? 'selected' : '' }}>🏢 B2B</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>👑 مدير</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            
            {{-- Password Section --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-lock"></i> تغيير كلمة المرور
                </div>
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="fas fa-info-circle"></i>
                    <small>اترك الحقول فارغة إذا لم ترغب في تغيير كلمة المرور</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">كلمة مرور جديدة</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                        <div class="position-relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                   class="form-control" placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> معلومات الحساب
                </div>
                <div class="mb-3">
                    <small class="text-muted">تاريخ الإنشاء</small>
                    <div class="fw-bold">{{ $user->created_at->format('Y/m/d H:i') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">آخر تحديث</small>
                    <div class="fw-bold">{{ $user->updated_at->format('Y/m/d H:i') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">الحالة</small>
                    <div>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'نشط' : 'معطل' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-pink btn-lg">
                    <i class="fas fa-save"></i> حفظ التغييرات
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right"></i> رجوع
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush
