@extends($layoutPath)

@section('title', 'إتمام الشراء - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<section class="pt-32 pb-8 relative border-b border-white/5">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_0%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('cart') }}" class="text-white-dim hover:text-brand-500 transition-colors flex items-center gap-1 text-sm">
                <i class="ph ph-arrow-right"></i> العودة للسلة
            </a>
        </div>
        <h1 class="text-3xl font-extrabold text-white">إتمام الشراء</h1>
        <p class="text-white-dim mt-1">أدخلي معلومات الشحن وأكدي طلبك</p>
    </div>
</section>

{{-- Progress Steps --}}
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="flex items-center justify-center gap-0">
        {{-- Step 1: Cart (completed) --}}
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm text-white bg-green-500/30 border border-green-500/50">
                <i class="ph ph-check text-white"></i>
            </span>
            <span class="font-bold text-sm text-green-400">السلة</span>
        </div>
        <div class="w-10 h-0.5 bg-green-500/60 mx-3 rounded-full"></div>
        {{-- Step 2: Payment (current) --}}
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-sm text-white" style="background: var(--gradient-primary);">2</span>
            <span class="font-bold text-sm text-brand-500">الدفع</span>
        </div>
        <div class="w-10 h-0.5 bg-white/10 mx-3 rounded-full"></div>
        {{-- Step 3: Confirm --}}
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full bg-white/5 text-white-dim flex items-center justify-center font-extrabold text-sm">3</span>
            <span class="font-semibold text-sm text-white-dim">تأكيد</span>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm" autocomplete="on">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
            {{-- Left Column: Shipping Form --}}
            <div class="lg:col-span-4 space-y-5">
                {{-- Free Shipping Incentive --}}
                @php
                    $freeShippingThreshold = floatval($settings['free_shipping_min'] ?? $settings['free_shipping_threshold'] ?? 200);
                    $percentToFree = $subtotal >= $freeShippingThreshold ? 100 : min(100, ($subtotal / $freeShippingThreshold) * 100);
                @endphp
                @if($subtotal < $freeShippingThreshold)
                <div class="glass-panel rounded-2xl px-5 py-4 flex items-center gap-4 border-amber-500/20 animate-pulse">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                        <i class="ph ph-truck text-xl text-amber-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <strong class="text-amber-400 text-sm">تبقى {{ number_format($freeShippingThreshold - $subtotal, 2) }} ₪ للشحن المجاني!</strong>
                        <div class="h-1.5 bg-white/5 rounded-full mt-1.5 overflow-hidden">
                            <div class="h-full bg-gradient-to-l from-amber-500 to-amber-400 rounded-full transition-all duration-700" style="width: {{ $percentToFree }}%"></div>
                        </div>
                    </div>
                </div>
                @else
                <div class="glass-panel rounded-2xl px-5 py-4 flex items-center gap-4 border-green-500/20">
                    <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center flex-shrink-0">
                        <i class="ph-fill ph-check-circle text-xl text-green-400"></i>
                    </div>
                    <strong class="text-green-400 text-sm">مؤهل للشحن المجاني! 🎉</strong>
                </div>
                @endif

                {{-- Shipping Info --}}
                <div class="glass-panel rounded-2xl p-6 border-white/5">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-11 h-11 rounded-xl bg-brand-500/10 flex items-center justify-center text-brand-500 text-lg">
                            <i class="ph ph-truck"></i>
                        </span>
                        <h3 class="text-lg font-bold text-white">معلومات الشحن</h3>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1.5 font-semibold text-sm text-white">الاسم الكامل <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-user"></i></span>
                            <input type="text" name="customer_name" value="{{ old('customer_name', Auth::user()->name ?? '') }}" required
                                class="w-full pr-10 pl-4 py-3 border {{ $errors->has('customer_name') ? 'border-red-400 bg-red-500/5' : 'border-white/10 bg-white/5' }} text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-white-dim"
                                placeholder="أدخل اسمك الكامل">
                        </div>
                        @error('customer_name')<p class="text-red-400 text-xs mt-1.5 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-1.5 font-semibold text-sm text-white">البريد الإلكتروني <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-envelope"></i></span>
                                <input type="email" name="customer_email" value="{{ old('customer_email', Auth::user()->email ?? '') }}" required
                                    class="w-full pr-10 pl-4 py-3 border {{ $errors->has('customer_email') ? 'border-red-400 bg-red-500/5' : 'border-white/10 bg-white/5' }} text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-white-dim"
                                    placeholder="example@email.com">
                            </div>
                            @error('customer_email')<p class="text-red-400 text-xs mt-1.5 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block mb-1.5 font-semibold text-sm text-white">رقم الهاتف <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-phone"></i></span>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone', Auth::user()->phone ?? '') }}" required dir="ltr"
                                    class="w-full pr-10 pl-4 py-3 border {{ $errors->has('customer_phone') ? 'border-red-400 bg-red-500/5' : 'border-white/10 bg-white/5' }} text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-white-dim"
                                    placeholder="05XX XXXXXX">
                            </div>
                            @error('customer_phone')<p class="text-red-400 text-xs mt-1.5 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1.5 font-semibold text-sm text-white">العنوان <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute top-3 right-0 flex items-start pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-map-pin"></i></span>
                            <textarea name="shipping_address" required rows="2"
                                class="w-full pr-10 pl-4 py-3 border {{ $errors->has('shipping_address') ? 'border-red-400 bg-red-500/5' : 'border-white/10 bg-white/5' }} text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all resize-y placeholder:text-white-dim"
                                placeholder="العنوان بالكامل (الشارع، الحي، المبنى)">{{ old('shipping_address') }}</textarea>
                        </div>
                        @error('shipping_address')<p class="text-red-400 text-xs mt-1.5 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-1.5 font-semibold text-sm text-white">المدينة <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-buildings"></i></span>
                                <select name="shipping_city" required
                                     class="w-full pr-10 pl-4 py-3 border {{ $errors->has('shipping_city') ? 'border-red-400 bg-red-500/5' : 'border-white/10 bg-white/5' }} text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all cursor-pointer appearance-none [&>option]:bg-gray-900 [&>option]:text-white">
                                    <option value="" disabled {{ old('shipping_city') ? '' : 'selected' }}>اختر المدينة</option>
                                    @foreach(['رام الله','نابلس','الخليل','بيت لحم','جنين','طولكرم','قلقيلية','طوباس','سلفيت','القدس','أريحا','غزة'] as $city)
                                    <option value="{{ $city }}" {{ old('shipping_city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-white-dim"><i class="ph ph-caret-down text-xs"></i></span>
                            </div>
                            @error('shipping_city')<p class="text-red-400 text-xs mt-1.5 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block mb-1.5 font-semibold text-sm text-white">المنطقة <span class="text-white-dim font-normal text-xs">(اختياري)</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-compass"></i></span>
                                <input type="text" name="shipping_region" value="{{ old('shipping_region') }}"
                                    class="w-full pr-10 pl-4 py-3 border border-white/10 bg-white/5 text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-white-dim"
                                    placeholder="مثال: حي الجنان">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1.5 font-semibold text-sm text-white">ملاحظات الطلب</label>
                        <div class="relative">
                            <span class="absolute top-3 right-0 flex items-start pr-3.5 pointer-events-none text-white-dim"><i class="ph ph-note"></i></span>
                            <textarea name="shipping_notes" rows="2"
                                class="w-full pr-10 pl-4 py-3 border border-white/10 bg-white/5 text-white rounded-xl text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all resize-y placeholder:text-white-dim"
                                placeholder="أي ملاحظات إضافية للتوصيل">{{ old('shipping_notes') }}</textarea>
                        </div>
                    </div>

                    @auth
                    {{-- <label class="flex items-center gap-2 mt-4 cursor-pointer">
                        <input type="checkbox" name="save_address" class="w-4 h-4 rounded accent-brand-500">
                        <span class="text-xs text-white-dim">حفظ عنوان الشحن للطلبات المستقبلية</span>
                    </label> --}}
                    @endauth
                </div>
            </div>

            {{-- Right Column: Order Summary + Payment --}}
            <div class="lg:col-span-3 space-y-5 lg:sticky lg:top-28 self-start">
                {{-- Order Review --}}
                <div class="glass-panel rounded-2xl border-white/5 overflow-hidden">
                    <div class="px-5 py-4 border-b border-white/5 flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-brand-500/10 flex items-center justify-center text-brand-500 text-lg">
                            <i class="ph ph-shopping-bag"></i>
                        </span>
                        <span class="font-bold text-white">مراجعة الطلب</span>
                    </div>
                    <div class="px-5 py-3 space-y-1">
                        @foreach($cart->items as $item)
                        <div class="flex items-center gap-3 py-2.5 border-b border-white/5 last:border-b-0">
                            @if($item->product->main_image_url)
                            <img src="{{ $item->product->optimizedImageUrl(100, 100) }}" alt="{{ $item->product->name_ar }}" width="100" height="100" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-white/10">
                            @else
                            <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center flex-shrink-0"><i class="ph ph-image text-white-dim"></i></div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-white truncate">{{ $item->product->name_ar }}</p>
                                <p class="text-xs text-white-dim">الكمية: {{ $item->quantity }}</p>
                            </div>
                            <span class="font-bold text-sm text-white whitespace-nowrap">{{ number_format($item->total, 2) }} ₪</span>
                        </div>
                        @endforeach

                        {{-- Coupon Code --}}
                        <div class="py-2.5 border-b border-white/5">
                            <div class="flex gap-2">
                                <input type="text" id="couponCodeInput" name="coupon_code" placeholder="كود الخصم (اختياري)"
                                    class="flex-1 bg-white/5 border border-white/10 text-white rounded-xl px-3.5 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-white-dim"
                                    value="{{ old('coupon_code') }}">
                                <button type="button" onclick="applyCheckoutCoupon()" class="px-4 py-2 bg-white/10 text-white text-sm font-bold rounded-xl hover:bg-brand-500/20 hover:text-brand-500 hover:border-brand-500/30 border border-transparent transition-all whitespace-nowrap">
                                    تطبيق
                                </button>
                            </div>
                            <div id="couponMsg" class="mt-1.5 text-xs"></div>
                        </div>

                        <div class="flex justify-between py-2.5 text-sm text-white-dim">
                            <span>المجموع الفرعي</span>
                            <span id="checkout-subtotal" class="font-semibold text-white">{{ number_format($subtotal, 2) }} ₪</span>
                        </div>
                        <div class="flex justify-between py-2.5 text-sm">
                            <span class="text-white-dim">الشحن</span>
                            @if($shippingCost > 0)
                            <span id="checkout-shipping" class="font-semibold text-white">{{ number_format($shippingCost, 2) }} ₪</span>
                            @else
                            <span id="checkout-shipping" class="font-semibold text-green-400">مجاني</span>
                            @endif
                        </div>
                        <div id="checkoutDiscountRow" class="hidden justify-between py-2.5 text-sm text-green-400">
                            <span>الخصم</span>
                            <span id="checkoutDiscountAmount" class="font-semibold">-0.00 ₪</span>
                        </div>
                        <div class="flex justify-between py-3 border-t-2 border-white/5 text-base font-extrabold text-white">
                            <span>الإجمالي</span>
                            <span id="checkout-total" class="text-brand-500">{{ number_format($totalAmount, 2) }} ₪</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="glass-panel rounded-2xl border-white/5 overflow-hidden">
                    <div class="px-5 py-4 border-b border-white/5 flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-brand-500/10 flex items-center justify-center text-brand-500 text-lg">
                            <i class="ph ph-credit-card"></i>
                        </span>
                        <span class="font-bold text-white">طريقة الدفع</span>
                    </div>
                    <div class="p-5 space-y-3">
                        @php $firstMethod = array_key_first($paymentMethods); @endphp
                        @foreach($paymentMethods as $method)
                        @php $isFirst = $loop->first; @endphp
                        <label class="payment-option flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 {{ $isFirst ? 'border-brand-500 bg-brand-500/10' : 'border-white/10 bg-white/5 hover:border-brand-500/30' }}" data-method="{{ $method['id'] }}">
                            <input type="radio" name="payment_method" value="{{ $method['id'] }}" {{ $isFirst ? 'checked' : '' }} class="w-5 h-5 accent-brand-500 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <strong class="block text-sm text-white mb-0.5">{{ $method['name'] }}</strong>
                                <small class="text-white-dim text-xs">{{ $method['description'] }}</small>
                            </div>
                            <i class="ph ph-{{ $method['id'] === 'cod' ? 'money' : ($method['id'] === 'bank_transfer' ? 'bank' : 'device-mobile') }} text-xl {{ $isFirst ? 'text-brand-500' : 'text-white-dim' }} flex-shrink-0"></i>
                        </label>
                        @endforeach

                        @if(isset($paymentMethods['bank_transfer']))
                        <div id="bankDetails" class="{{ $firstMethod === 'bank_transfer' ? '' : 'hidden' }} mt-2 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl text-sm">
                            <p class="font-bold text-blue-400 mb-2"><i class="ph ph-info"></i> معلومات التحويل البنكي:</p>
                            @if($settings['payment_bank_name'] ?? false)<p class="text-blue-400 mb-1"><strong>البنك:</strong> {{ $settings['payment_bank_name'] }}</p>@endif
                            @if($settings['payment_bank_holder'] ?? false)<p class="text-blue-400 mb-1"><strong>اسم المستفيد:</strong> {{ $settings['payment_bank_holder'] }}</p>@endif
                            @if($settings['payment_bank_account'] ?? false)<p class="text-blue-400 mb-1" dir="ltr"><strong>رقم الحساب:</strong> {{ $settings['payment_bank_account'] }}</p>@endif
                            @if($settings['payment_bank_iban'] ?? false)<p class="text-blue-400 mb-1" dir="ltr"><strong>IBAN:</strong> {{ $settings['payment_bank_iban'] }}</p>@endif
                            <p class="text-blue-400/60 text-xs mt-2">بعد التحويل، يرجى إرسال إيصال الدفع عبر واتساب لتأكيد الطلب.</p>
                        </div>
                        @endif
                        @if(isset($paymentMethods['jawwal_pay']))
                        <div id="jawwalDetails" class="{{ $firstMethod === 'jawwal_pay' ? '' : 'hidden' }} mt-2 p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl text-sm">
                            <p class="font-bold text-amber-400 mb-2"><i class="ph ph-info"></i> معلومات الدفع عبر جوال باي:</p>
                            @if($settings['payment_jawwal_holder'] ?? false)<p class="text-amber-400 mb-1"><strong>اسم المستفيد:</strong> {{ $settings['payment_jawwal_holder'] }}</p>@endif
                            @if($settings['payment_jawwal_phone'] ?? false)<p class="text-amber-400 mb-1" dir="ltr"><strong>رقم جوال باي:</strong> {{ $settings['payment_jawwal_phone'] }}</p>@endif
                            <p class="text-amber-400/60 text-xs mt-2">بعد إرسال المبلغ، يرجى إرسال تأكيد الدفع عبر واتساب.</p>
                        </div>
                        @endif
                        @if(isset($paymentMethods['reflect']))
                        <div id="reflectDetails" class="{{ $firstMethod === 'reflect' ? '' : 'hidden' }} mt-2 p-4 bg-cyan-500/10 border border-cyan-500/20 rounded-xl text-sm">
                            <p class="font-bold text-cyan-400 mb-2"><i class="ph ph-info"></i> معلومات الدفع عبر Reflect:</p>
                            @if($settings['payment_reflect_holder'] ?? false)<p class="text-cyan-400 mb-1"><strong>اسم المستفيد:</strong> {{ $settings['payment_reflect_holder'] }}</p>@endif
                            @if($settings['payment_reflect_phone'] ?? false)<p class="text-cyan-400 mb-1" dir="ltr"><strong>رقم هاتف Reflect:</strong> {{ $settings['payment_reflect_phone'] }}</p>@endif
                            <p class="text-cyan-400/60 text-xs mt-2">بعد إرسال المبلغ عبر تطبيق Reflect، يرجى إرسال تأكيد الدفع عبر واتساب.</p>
                        </div>
                        @endif

                        {{-- Trust Badges --}}
                        <div class="flex justify-center gap-6 pt-1 pb-2 text-xs text-white-dim">
                            <span class="flex items-center gap-1.5"><i class="ph-fill ph-lock text-green-400"></i> دفع آمن</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-truck text-brand-500"></i> توصيل سريع</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-arrow-counter-clockwise text-amber-400"></i> استرجاع سهل</span>
                        </div>

                        <button type="submit" id="checkoutBtn"
                            class="flex items-center justify-center gap-2 w-full py-4 text-white rounded-full font-bold text-base hover:shadow-neon hover:scale-[1.02] transition-all duration-300 mt-1 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100" style="background: var(--gradient-primary);">
                            <i class="ph ph-check-circle text-lg"></i>
                            <span id="checkoutBtnText">تأكيد الطلب</span>
                            <span id="checkoutBtnSpinner" class="hidden">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>

                        <p class="text-center text-[11px] text-white-dim">
                            بالضغط على "تأكيد الطلب"، أنت توافقين على
                            <a href="{{ route('terms') }}" class="text-brand-500 hover:underline">الشروط والأحكام</a>
                            و
                            <a href="{{ route('privacy') }}" class="text-brand-500 hover:underline">سياسة الخصوصية</a>
                        </p>
                    </div>
                </div>

                {{-- WhatsApp alternative --}}
                <div class="text-center p-4 glass-panel rounded-2xl border-white/5">
                    <p class="text-xs text-white-dim mb-2">تفضلين الطلب عبر واتساب؟</p>
                    <a href="https://wa.me/{{ $settings['site_whatsapp'] ?? '970591234567' }}?text={{ urlencode('السلام عليكم، أريد تأكيد طلبي من شركة جنين للتجميل') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-green-600/15 border border-green-500/20 text-green-400 rounded-full font-bold text-xs hover:bg-green-500/20 hover:border-green-500/40 transition-all duration-300">
                        <i class="ph ph-whatsapp-logo text-sm"></i> اطلب عبر واتساب
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const detailsMap = {
        bank_transfer: document.getElementById('bankDetails'),
        jawwal_pay: document.getElementById('jawwalDetails'),
        reflect: document.getElementById('reflectDetails')
    };

    function selectPayment(label) {
        const radio = label.querySelector('input[type="radio"]');
        radio.checked = true;
        paymentOptions.forEach(o => {
            o.classList.remove('border-brand-500','bg-brand-500/10');
            o.classList.add('border-white/10','bg-white/5');
            const icon = o.querySelector('i:last-child');
            if (icon) { icon.classList.remove('text-brand-500'); icon.classList.add('text-white-dim'); }
        });
        label.classList.remove('border-white/10','bg-white/5');
        label.classList.add('border-brand-500','bg-brand-500/10');
        const icon = label.querySelector('i:last-child');
        if (icon) { icon.classList.remove('text-white-dim'); icon.classList.add('text-brand-500'); }
        Object.values(detailsMap).forEach(d => { if(d) d.classList.add('hidden'); });
        const method = label.dataset.method;
        if (detailsMap[method]) detailsMap[method].classList.remove('hidden');
    }

    paymentOptions.forEach(opt => { opt.addEventListener('click', () => selectPayment(opt)); });

    const form = document.getElementById('checkoutForm');
    const btn = document.getElementById('checkoutBtn');
    const btnText = document.getElementById('checkoutBtnText');
    const btnSpinner = document.getElementById('checkoutBtnSpinner');

    form.addEventListener('submit', function(e) {
        const required = form.querySelectorAll('[required]');
        let valid = true;
        required.forEach(el => {
            if (!el.value.trim()) {
                el.classList.add('border-red-400', 'bg-red-500/5');
                valid = false;
            }
        });
        if (!valid) {
            e.preventDefault();
            const firstInvalid = form.querySelector('.border-red-400');
            if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        btn.disabled = true;
        btnText.textContent = 'جاري تأكيد الطلب...';
        btnSpinner.classList.remove('hidden');
        btn.querySelector('i.ph-check-circle').classList.add('hidden');
    });

    // Clear red border on input
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', function() {
            this.classList.remove('border-red-400', 'bg-red-500/5');
        });
    });
});

// Checkout coupon handling
let checkoutCouponDiscount = 0;
async function applyCheckoutCoupon() {
    const code = document.getElementById('couponCodeInput').value.trim();
    const msgEl = document.getElementById('couponMsg');
    if (!code) { msgEl.innerHTML = '<span class="text-red-400"><i class="ph ph-x-circle"></i> أدخل كود الخصم</span>'; return; }

    const basePath = window.basePath || '';
    try {
        const r = await fetch(basePath + '/cart/coupon', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ code: code })
        });
        const d = await r.json();
        if (d.success) {
            checkoutCouponDiscount = parseFloat(d.discount);
            document.getElementById('checkoutDiscountRow').style.display = 'flex';
            document.getElementById('checkoutDiscountAmount').textContent = '-' + checkoutCouponDiscount.toFixed(2) + ' ₪';
            updateCheckoutTotal();
            msgEl.innerHTML = '<span class="text-green-400"><i class="ph ph-check-circle"></i> ' + d.message + '</span>';
            if (typeof showNotification === 'function') showNotification('success', d.message);
        } else {
            checkoutCouponDiscount = 0;
            document.getElementById('checkoutDiscountRow').style.display = 'none';
            updateCheckoutTotal();
            msgEl.innerHTML = '<span class="text-red-400"><i class="ph ph-x-circle"></i> ' + d.message + '</span>';
        }
    } catch(e) {
        msgEl.innerHTML = '<span class="text-red-400"><i class="ph ph-x-circle"></i> حدث خطأ، حاول مرة أخرى</span>';
    }
}

function updateCheckoutTotal() {
    const subtotalEl = document.getElementById('checkout-subtotal');
    const shippingEl = document.getElementById('checkout-shipping');
    const totalEl = document.getElementById('checkout-total');

    let subtotal = parseFloat(subtotalEl.textContent.replace(/[^0-9.]/g, ''));
    let shipping = 0;
    if (shippingEl.textContent.includes('مجاني')) {
        shipping = 0;
    } else {
        shipping = parseFloat(shippingEl.textContent.replace(/[^0-9.]/g, ''));
    }
    const total = Math.max(0, subtotal + shipping - checkoutCouponDiscount);
    totalEl.textContent = total.toFixed(2) + ' ₪';
}
</script>
@endsection
