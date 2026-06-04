@extends($layoutPath)

@section('title', 'تسجيل الدخول - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 pt-32 pb-16 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-brand-100 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-pink-100 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="w-full max-w-md glass-panel rounded-3xl border border-white/5 shadow-xl p-8 relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-brand-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-brand-200">
                <i class="ph ph-user text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">تسجيل الدخول</h1>
            <p class="text-white-dim text-sm mt-1">أهلاً بعودتك! أدخل بياناتك للدخول</p>
        </div>

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5" id="loginForm" novalidate>
            @csrf

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">البريد الإلكتروني <span class="text-red-400">*</span></label>
                <div class="relative">
                    <i class="ph ph-envelope-simple absolute right-4 top-1/2 -translate-y-1/2 text-white-dim text-lg"></i>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-white border border-white/10 rounded-xl pr-12 pl-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('email') border-red-400 @enderror"
                        placeholder="example@email.com">
                </div>
                @error('email')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">كلمة المرور <span class="text-red-400">*</span></label>
                <div class="relative" x-data="{ show: false }">
                    <i class="ph ph-lock absolute right-4 top-1/2 -translate-y-1/2 text-white-dim text-lg"></i>
                    <input :type="show ? 'text' : 'password'" name="password" required
                        class="w-full bg-white border border-white/10 rounded-xl pr-12 pl-12 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('password') border-red-400 @enderror"
                        placeholder="********">
                    <button type="button" @click="show = !show" class="absolute left-4 top-1/2 -translate-y-1/2 text-white-dim hover:text-white-dim transition-colors">
                        <i :class="show ? 'ph ph-eye-slash' : 'ph ph-eye'" class="text-lg"></i>
                    </button>
                </div>
                @error('password')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 cursor-pointer">
                    <span class="text-sm text-white-dim group-hover:text-white transition-colors">تذكرني</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-brand-500 font-medium hover:text-brand-700 hover:underline transition-colors">نسيت كلمة المرور؟</a>
            </div>

            <button type="submit" id="loginSubmit"
                class="w-full py-3.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white rounded-full font-bold hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-200 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <span id="btnText"><i class="ph ph-sign-in"></i> تسجيل الدخول</span>
                <span id="btnSpinner" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-white/10"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white text-white-dim">أو</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ url('auth/google/redirect') }}" class="flex items-center justify-center gap-2 py-2.5 border border-white/10 rounded-xl text-sm text-white-dim hover:bg-white/5 hover:border-gray-300 transition-all">
                <i class="ph ph-google-logo text-lg text-red-400"></i>
                <span>Google</span>
            </a>
            <a href="{{ url('auth/facebook/redirect') }}" class="flex items-center justify-center gap-2 py-2.5 border border-white/10 rounded-xl text-sm text-white-dim hover:bg-white/5 hover:border-gray-300 transition-all">
                <i class="ph ph-facebook-logo text-lg text-blue-600"></i>
                <span>Facebook</span>
            </a>
        </div>

        <p class="text-center text-sm text-white-dim mt-6">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="text-brand-500 font-bold hover:underline">إنشاء حساب جديد</a>
        </p>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginSubmit');
        btn.disabled = true;
        document.getElementById('btnText').classList.add('hidden');
        document.getElementById('btnSpinner').classList.remove('hidden');
    });
</script>
@endpush
@endsection
