@extends($layoutPath)

@section('title', 'استعادة كلمة المرور - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 pt-32 pb-16 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-brand-100 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-pink-100 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="w-full max-w-md glass-panel rounded-3xl border border-white/5 shadow-xl p-8 relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-brand-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-brand-200">
                <i class="ph ph-lock-key-open text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">استعادة كلمة المرور</h1>
            <p class="text-white-dim text-sm mt-1">أدخل بريدك الإلكتروني لاستعادة كلمة المرور</p>
        </div>

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-5" novalidate>
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

            <button type="submit"
                class="w-full py-3.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white rounded-full font-bold hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-200 flex items-center justify-center gap-2">
                <i class="ph ph-arrow-right"></i> متابعة
            </button>
        </form>

        <p class="text-center text-sm text-white-dim mt-6">
            <a href="{{ route('login') }}" class="text-brand-500 font-bold hover:underline">العودة لتسجيل الدخول</a>
        </p>
    </div>
</div>
@endsection
