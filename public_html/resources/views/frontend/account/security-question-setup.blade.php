@extends($layoutPath)

@section('title', 'تحديث سؤال الأمان - حسابي - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 pt-32 pb-16 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-brand-100 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-pink-100 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="w-full max-w-lg glass-panel rounded-3xl border border-white/5 shadow-xl p-8 relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-brand-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-brand-200">
                <i class="ph ph-shield-check text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">تحديث سؤال الأمان</h1>
            <p class="text-white-dim text-sm mt-1">سؤال الأمان يساعدك في استعادة كلمة المرور إذا نسيتها</p>
        </div>

        @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="ph ph-check-circle text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="ph ph-warning-circle text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form action="{{ route('account.security-question.update') }}" method="POST" class="space-y-5" novalidate>
            @csrf

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">اختر سؤال الأمان <span class="text-red-400">*</span></label>
                <select name="security_question" required
                    class="w-full bg-white border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('security_question') border-red-400 @enderror">
                    <option value="" disabled selected>اختر سؤال أمان...</option>
                    <option value="ما هو اسم مدرستك الابتدائية؟" {{ old('security_question', $user->security_question) == 'ما هو اسم مدرستك الابتدائية؟' ? 'selected' : '' }}>ما هو اسم مدرستك الابتدائية؟</option>
                    <option value="ما هو اسم أفضل صديق لك في الطفولة؟" {{ old('security_question', $user->security_question) == 'ما هو اسم أفضل صديق لك في الطفولة؟' ? 'selected' : '' }}>ما هو اسم أفضل صديق لك في الطفولة؟</option>
                    <option value="ما هو اسم حيوانك الأليف الأول؟" {{ old('security_question', $user->security_question) == 'ما هو اسم حيوانك الأليف الأول؟' ? 'selected' : '' }}>ما هو اسم حيوانك الأليف الأول؟</option>
                    <option value="في أي مدينة ولدت؟" {{ old('security_question', $user->security_question) == 'في أي مدينة ولدت؟' ? 'selected' : '' }}>في أي مدينة ولدت؟</option>
                    <option value="ما هو اسم جدك (والد أمك)؟" {{ old('security_question', $user->security_question) == 'ما هو اسم جدك (والد أمك)؟' ? 'selected' : '' }}>ما هو اسم جدك (والد أمك)؟</option>
                    <option value="ما هي ماركة أول سيارة امتلكتها؟" {{ old('security_question', $user->security_question) == 'ما هي ماركة أول سيارة امتلكتها؟' ? 'selected' : '' }}>ما هي ماركة أول سيارة امتلكتها؟</option>
                    <option value="ما هو طعامك المفضل؟" {{ old('security_question', $user->security_question) == 'ما هو طعامك المفضل؟' ? 'selected' : '' }}>ما هو طعامك المفضل؟</option>
                    <option value="ما هو اسم معلمك المفضل؟" {{ old('security_question', $user->security_question) == 'ما هو اسم معلمك المفضل؟' ? 'selected' : '' }}>ما هو اسم معلمك المفضل؟</option>
                </select>
                @error('security_question')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">إجابتك <span class="text-red-400">*</span></label>
                <input type="text" name="security_answer" required
                    class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('security_answer') border-red-400 @enderror"
                    placeholder="أدخل إجابتك">
                @error('security_answer')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">كلمة المرور الحالية <span class="text-red-400">*</span></label>
                <div class="relative" x-data="{ show: false }">
                    <i class="ph ph-lock absolute right-4 top-1/2 -translate-y-1/2 text-white-dim text-lg"></i>
                    <input :type="show ? 'text' : 'password'" name="current_password" required
                        class="w-full bg-white border border-white/10 rounded-xl pr-12 pl-12 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('current_password') border-red-400 @enderror"
                        placeholder="********">
                    <button type="button" @click="show = !show" class="absolute left-4 top-1/2 -translate-y-1/2 text-white-dim hover:text-white-dim transition-colors">
                        <i :class="show ? 'ph ph-eye-slash' : 'ph ph-eye'" class="text-lg"></i>
                    </button>
                </div>
                @error('current_password')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="w-full py-3.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white rounded-full font-bold hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-200 flex items-center justify-center gap-2">
                <i class="ph ph-check-circle"></i> حفظ سؤال الأمان
            </button>
        </form>

        <p class="text-center text-sm text-white-dim mt-6">
            <a href="{{ route('account') }}" class="text-brand-500 font-bold hover:underline">العودة لحسابي</a>
        </p>
    </div>
</div>
@endsection
