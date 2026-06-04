@extends($layoutPath)

@section('title', 'إنشاء حساب - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 pt-32 pb-16">
    <div class="w-full max-w-lg bg-white rounded-3xl border border-white/5 shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-brand-500/10 flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-user-plus text-3xl text-brand-500"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">إنشاء حساب جديد</h1>
            <p class="text-white-dim text-sm mt-1">انضمي إلينا واستمتعي بتجربة تسوق فريدة</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-bold text-white mb-1.5">الاسم الكامل <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('name') border-red-400 @enderror" placeholder="اسمك الكامل">
                @error('name')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">البريد الإلكتروني <span class="text-red-400">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('email') border-red-400 @enderror" placeholder="example@email.com">
                @error('email')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">رقم الهاتف <span class="text-red-400">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('phone') border-red-400 @enderror" placeholder="05XX XXXXXX">
                @error('phone')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white mb-1.5">كلمة المرور <span class="text-red-400">*</span></label>
                    <input type="password" name="password" required class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('password') border-red-400 @enderror" placeholder="********">
                    @error('password')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-white mb-1.5">تأكيد كلمة المرور <span class="text-red-400">*</span></label>
                    <input type="password" name="password_confirmation" required class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all" placeholder="********">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">سؤال الأمان <span class="text-red-400">*</span></label>
                <select name="security_question" required
                    class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('security_question') border-red-400 @enderror">
                    <option value="" disabled selected>اختر سؤال أمان لاستعادة كلمة المرور...</option>
                    <option value="ما هو اسم مدرستك الابتدائية؟" {{ old('security_question') == 'ما هو اسم مدرستك الابتدائية؟' ? 'selected' : '' }}>ما هو اسم مدرستك الابتدائية؟</option>
                    <option value="ما هو اسم أفضل صديق لك في الطفولة؟" {{ old('security_question') == 'ما هو اسم أفضل صديق لك في الطفولة؟' ? 'selected' : '' }}>ما هو اسم أفضل صديق لك في الطفولة؟</option>
                    <option value="ما هو اسم حيوانك الأليف الأول؟" {{ old('security_question') == 'ما هو اسم حيوانك الأليف الأول؟' ? 'selected' : '' }}>ما هو اسم حيوانك الأليف الأول؟</option>
                    <option value="في أي مدينة ولدت؟" {{ old('security_question') == 'في أي مدينة ولدت؟' ? 'selected' : '' }}>في أي مدينة ولدت؟</option>
                    <option value="ما هو اسم جدك (والد أمك)؟" {{ old('security_question') == 'ما هو اسم جدك (والد أمك)؟' ? 'selected' : '' }}>ما هو اسم جدك (والد أمك)؟</option>
                    <option value="ما هي ماركة أول سيارة امتلكتها؟" {{ old('security_question') == 'ما هي ماركة أول سيارة امتلكتها؟' ? 'selected' : '' }}>ما هي ماركة أول سيارة امتلكتها؟</option>
                    <option value="ما هو طعامك المفضل؟" {{ old('security_question') == 'ما هو طعامك المفضل؟' ? 'selected' : '' }}>ما هو طعامك المفضل؟</option>
                    <option value="ما هو اسم معلمك المفضل؟" {{ old('security_question') == 'ما هو اسم معلمك المفضل؟' ? 'selected' : '' }}>ما هو اسم معلمك المفضل؟</option>
                </select>
                @error('security_question')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-1.5">إجابة سؤال الأمان <span class="text-red-400">*</span></label>
                <input type="text" name="security_answer" value="{{ old('security_answer') }}" required
                    class="w-full bg-surface border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all @error('security_answer') border-red-400 @enderror"
                    placeholder="أدخل إجابتك (ستستخدم لاستعادة كلمة المرور)">
                @error('security_answer')<p class="text-red-500 text-xs mt-1 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full py-3.5 bg-white style="color:#0f172a;" rounded-full font-bold hover:bg-brand-500 transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="ph ph-user-plus"></i> إنشاء حساب
            </button>
        </form>

        <p class="text-center text-sm text-white-dim mt-6">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="text-brand-500 font-bold hover:underline">تسجيل الدخول</a>
        </p>
    </div>
</div>
@endsection
