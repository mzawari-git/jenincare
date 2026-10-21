@extends('frontend.layouts.editorial.app')

@section('title', 'برنامج التسويق بالعمولة | اربحي 10% مع كل توصية | شركة جنين للتجميل')
@section('meta_description', 'انضمي إلى برنامج التسويق بالعمولة من جنين كير واربحِ 10% على كل طلبية. بدون سقف أرباح، سحب سهل، تتبع فوري.')

@section('content')
<section style="background:#ffffff;min-height:100vh;">

    {{-- ═══════════ HERO ═══════════ --}}
    <div style="position:relative;padding:7rem 1rem 5rem;overflow:hidden;">
        <div style="position:absolute;top:5rem;right:2rem;width:18rem;height:18rem;border-radius:50%;filter:blur(120px);opacity:0.08;background:#ec4899;"></div>
        <div style="position:absolute;bottom:2rem;left:2rem;width:22rem;height:22rem;border-radius:50%;filter:blur(140px);opacity:0.06;background:#d4af37;"></div>
        <div style="position:relative;z-index:1;max-width:1100px;margin:0 auto;text-align:center;">
            <div style="display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1.25rem;border-radius:9999px;border:1px solid rgba(236,72,153,0.3);background:rgba(236,72,153,0.08);margin-bottom:2rem;">
                <span style="width:8px;height:8px;border-radius:50%;background:#ec4899;animation:pulse 2s infinite;"></span>
                <span style="font-size:.75rem;font-weight:700;color:#be185d;letter-spacing:.15em;">برنامج التسويق بالعمولة</span>
            </div>
            <h1 style="font-size:clamp(2rem,6vw,4.5rem);font-weight:900;color:#0f172a;line-height:1.15;margin-bottom:1.5rem;">
                <span style="display:block;">حولي معرفتك إلى</span>
                <span style="display:block;background:linear-gradient(to left,#ec4899,#be185d,#d4af37);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">دخل ثابت ومستمر</span>
            </h1>
            <p style="font-size:1.15rem;color:#475569;max-width:650px;margin:0 auto 2.5rem;line-height:1.8;">
                انضمي إلى أكبر برنامج تسويق بالعمولة في فلسطين لمستحضرات التجميل وأجهزة الصالونات.
                <span style="display:block;margin-top:.5rem;font-weight:700;color:#be185d;">كل ما عليكِ هو المشاركة.. ونحن نتولى الباقي.</span>
            </p>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;max-width:800px;margin:0 auto 2.5rem;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem .75rem;">
                    <div style="font-size:1.75rem;font-weight:900;color:#ec4899;">10%</div>
                    <div style="color:#64748b;font-size:.7rem;margin-top:.25rem;">عمولة ثابتة</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem .75rem;">
                    <div style="font-size:1.75rem;font-weight:900;color:#b45309;">∞</div>
                    <div style="color:#64748b;font-size:.7rem;margin-top:.25rem;">بدون سقف أرباح</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem .75rem;">
                    <div style="font-size:1.75rem;font-weight:900;color:#0891b2;">30</div>
                    <div style="color:#64748b;font-size:.7rem;margin-top:.25rem;">يوم تتبع</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem .75rem;">
                    <div style="font-size:1.75rem;font-weight:900;color:#16a34a;">24h</div>
                    <div style="color:#64748b;font-size:.7rem;margin-top:.25rem;">سحب سريع</div>
                </div>
            </div>

            @auth
                @if($affiliate && $affiliate->status === 'active')
                    <a href="{{ route('affiliate.dashboard') }}" style="display:inline-flex;align-items:center;gap:.75rem;padding:.9rem 3rem;border-radius:9999px;font-weight:700;font-size:1.1rem;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;text-decoration:none;transition:all .2s;">
                        الذهاب للوحة التحكم <i class="ph ph-arrow-left"></i>
                    </a>
                @else
                    <form action="{{ route('affiliate.register') }}" method="POST" style="max-width:400px;margin:0 auto;">
                        @csrf
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1rem;display:flex;align-items:center;gap:.75rem;">
                            <input type="tel" name="phone" required style="flex:1;background:#fff;border:1px solid #cbd5e1;border-radius:.75rem;padding:.75rem 1rem;color:#0f172a;font-size:.875rem;outline:none;" placeholder="رقم هاتفك">
                            <button type="submit" style="padding:.75rem 1.5rem;border-radius:.75rem;font-weight:700;font-size:.875rem;white-space:nowrap;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;border:none;cursor:pointer;">
                                انضمي الآن
                            </button>
                        </div>
                    </form>
                @endif
            @else
                <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;">
                    <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2.5rem;border-radius:9999px;font-weight:700;font-size:1rem;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;text-decoration:none;transition:all .2s;">
                        <i class="ph ph-user-plus"></i> إنشاء حساب جديد
                    </a>
                    <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2.5rem;border-radius:9999px;font-weight:700;font-size:1rem;border:2px solid #cbd5e1;color:#334155;text-decoration:none;transition:all .2s;">
                        <i class="ph ph-sign-in"></i> تسجيل الدخول
                    </a>
                </div>
            @endauth
        </div>
    </div>

    {{-- ═══════════ HOW IT WORKS ═══════════ --}}
    <div style="padding:4rem 1rem;background:#f8fafc;">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="text-align:center;margin-bottom:3.5rem;">
                <span style="display:inline-block;font-size:.75rem;font-weight:700;color:#be185d;letter-spacing:.15em;background:rgba(236,72,153,0.08);padding:.35rem 1rem;border-radius:9999px;margin-bottom:1rem;">آلية العمل</span>
                <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;color:#0f172a;margin-bottom:.75rem;">كيف يعمل البرنامج؟</h2>
                <p style="color:#475569;max-width:500px;margin:0 auto;">ثلاث خطوات بسيطة تفصل بينكِ وبين دخل إضافي مستمر</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;">
                @foreach([
                    ['1','ph-user-plus','#ec4899','سجّلي وانضمي','أنشئي حسابك على جنين كير، ثم انضمي للبرنامج تلقائياً. يتم تفعيل حسابك فوراً وتحصلين على رابطك التسويقي الخاص.','تفعيل فوري بدون انتظار'],
                    ['2','ph-share-network','#d4af37','شاركي رابطك','انسخي رابطك الخاص وشاركيه في واتساب، إنستغرام، تيك توك، فيسبوك، أو أي قناة. كل نقرة على رابطك تُسجّل تلقائياً.','التتبع يستمر 30 يوماً من آخر نقرة'],
                    ['3','ph-money-wavy','#0891b2','اربحِ واسحبي','في كل مرة يشتري أحدهم عبر رابطك، تربحين 10% من قيمة الطلبية. أرباحك تظهر فوراً في لوحة تحكمك.','سحب ابتداءً من 50 شيكل'],
                ] as $step)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:2rem 1.5rem;text-align:center;position:relative;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="position:absolute;top:-1.25rem;left:50%;transform:translateX(-50%);width:2.5rem;height:2.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:900;color:#fff;background:linear-gradient(135deg,#ec4899,#be185d);">{{ $step[0] }}</div>
                    <div style="width:4.5rem;height:4.5rem;border-radius:1rem;background:{{$step[2]}}12;display:flex;align-items:center;justify-content:center;margin:1.5rem auto 1rem;">
                        <i class="ph {{ $step[1] }}" style="font-size:2rem;color:{{ $step[2] }};"></i>
                    </div>
                    <h3 style="font-size:1.15rem;font-weight:900;color:#0f172a;margin-bottom:.75rem;">{{ $step[3] }}</h3>
                    <p style="color:#475569;font-size:.85rem;line-height:1.7;margin-bottom:1rem;">{{ $step[4] }}</p>
                    <div style="background:#f1f5f9;border-radius:.75rem;padding:.75rem;font-size:.75rem;color:#64748b;">
                        <i class="ph ph-check-circle" style="color:#16a34a;"></i> {{ $step[5] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════ WHO CAN JOIN ═══════════ --}}
    <div style="padding:4rem 1rem;background:#ffffff;">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="text-align:center;margin-bottom:3rem;">
                <span style="display:inline-block;font-size:.75rem;font-weight:700;color:#0891b2;letter-spacing:.15em;background:rgba(8,145,178,0.08);padding:.35rem 1rem;border-radius:9999px;margin-bottom:1rem;">من يمكنه الانضمام؟</span>
                <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;color:#0f172a;margin-bottom:.75rem;">البرنامج مفتوح للجميع</h2>
                <p style="color:#475569;max-width:500px;margin:0 auto;">سواء كنتِ صاحبة صالون، مؤثرة، أو فقط تحبين مشاركة المنتجات - هذا البرنامج لكِ</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
                @foreach([
                    ['ph-scissors','#ec4899','صالونات التجميل','اربطي زبوناتك بمنتجات أصلية واربحِ من كل طلبية'],
                    ['ph-tiktok-logo','#000000','صانعات المحتوى','أنشئي محتوى عن المنتجات واربحِ من مشاهداتك'],
                    ['ph-users-three','#0891b2','مُحبات التجميل','شاركي حبك للمنتجات مع صديقاتك واربحِ بسهولة'],
                    ['ph-storefront','#16a34a','موزعين وشركات','برنامج خاص للشركات والموزعين بعمولات مميزة'],
                ] as $who)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.5rem;text-align:center;transition:all .2s;">
                    <i class="ph {{ $who[0] }}" style="font-size:2rem;color:{{ $who[1] }};margin-bottom:.75rem;"></i>
                    <h4 style="font-weight:900;color:#0f172a;margin-bottom:.5rem;">{{ $who[2] }}</h4>
                    <p style="color:#64748b;font-size:.8rem;line-height:1.6;">{{ $who[3] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════ COMMISSION DETAILS ═══════════ --}}
    <div style="padding:4rem 1rem;background:#f8fafc;">
        <div style="max-width:1000px;margin:0 auto;">
            <div style="text-align:center;margin-bottom:3rem;">
                <span style="display:inline-block;font-size:.75rem;font-weight:700;color:#b45309;letter-spacing:.15em;background:rgba(180,83,9,0.08);padding:.35rem 1rem;border-radius:9999px;margin-bottom:1rem;">تفاصيل العمولة</span>
                <h2 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;color:#0f172a;margin-bottom:.75rem;">نظام عمولات شفاف وواضح</h2>
                <p style="color:#475569;">كل شيء واضح من البداية.. لا مفاجآت ولا رسوم مخفية</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:1.25rem;">
                @foreach([
                    ['ph-percent','#ec4899','نسبة العمولة','10% ثابتة على جميع المنتجات والأجهزة بلا استثناء.','مستحضرات التجميل|أجهزة التجميل|تجهيز الصالونات'],
                    ['ph-timer','#0891b2','مدة التتبع','30 يوماً من آخر نقرة على رابطك. إذا عاد العميل خلال 30 يوم وأكمل الشراء، العمولة لكِ.','الكوكيز والجلسات تضمن عدم ضياع أي عملية'],
                    ['ph-wallet','#16a34a','طريقة السحب','الحد الأدنى 50 ₪ للسحب. أرباحك تظهر لحظياً وتستطيعين سحبها بأي وقت.','تحويل بنكي|PayPal|محفظة إلكترونية'],
                    ['ph-chart-bar','#d4af37','تتبع وتحليلات','لوحة تحكم كاملة تظهر عدد النقرات، التحويلات، الأرباح، ونسبة التحويل.','تقارير يومية وشهرية في لوحة التحكم'],
                ] as $detail)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="display:flex;align-items:flex-start;gap:1rem;">
                        <div style="width:3rem;height:3rem;border-radius:.75rem;background:{{$detail[1]}}12;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="ph {{ $detail[0] }}" style="font-size:1.5rem;color:{{ $detail[1] }};"></i>
                        </div>
                        <div>
                            <h4 style="font-weight:900;color:#0f172a;margin-bottom:.5rem;">{{ $detail[2] }}</h4>
                            <p style="color:#475569;font-size:.85rem;line-height:1.7;margin-bottom:.75rem;">{{ $detail[3] }}</p>
                            <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
                                @foreach(explode('|',$detail[4]) as $tag)
                                <span style="background:#f1f5f9;padding:.25rem .75rem;border-radius:9999px;font-size:.7rem;color:#64748b;">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════ INSTRUCTIONS ═══════════ --}}
    <div style="padding:4rem 1rem;background:#ffffff;">
        <div style="max-width:900px;margin:0 auto;">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1.5rem;padding:2.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div style="text-align:center;margin-bottom:2.5rem;">
                    <span style="display:inline-block;font-size:.75rem;font-weight:700;color:#be185d;letter-spacing:.15em;background:rgba(236,72,153,0.08);padding:.35rem 1rem;border-radius:9999px;margin-bottom:1rem;">دليل المسوّق</span>
                    <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:900;color:#0f172a;margin-bottom:.5rem;">كل ما تحتاجين معرفته</h2>
                </div>

                <div style="display:flex;flex-direction:column;gap:1.25rem;">
                    @foreach([
                        ['01','كيف أحصل على رابطي؟','بعد تسجيل الدخول، يتم إنشاء حسابك التسويقي تلقائياً ويظهر رابطك الخاص فوراً في لوحة التحكم. الرابط يكون بصيغة: <code style="background:#e2e8f0;padding:.15rem .5rem;border-radius:.35rem;color:#be185d;font-size:.8rem;" dir="ltr">jenincare.shop/?ref=اسمك123</code>'],
                        ['02','أين أنشر الرابط؟','في كل مكان! مجموعات واتساب، ستوري إنستغرام، منشورات فيسبوك، فيديوهات تيك توك، قنوات تلغرام، رسائل خاصة، وحتى في صالونك مع الزبونات.'],
                        ['03','متى تظهر العمولة في حسابي؟','فور إتمام الطلبية عبر رابطك، تظهر العمولة في لوحة التحكم بحالة "معلقة". بعد فترة 14 يوماً (لضمان عدم إرجاع الطلب)، تنتقل إلى "موافق عليها" وتصبح متاحة للسحب.'],
                        ['04','كيف أتأكد أن التتبع يعمل؟','كل نقرة على رابطك تُسجل فوراً في لوحة التحكم. تستطيعين رؤية عدد النقرات، مصدر الزيارات، وعدد التحويلات لحظة بلحظة. جربي بنفسك - افتحي الرابط من جهاز آخر وسترين النقرة.'],
                        ['05','هل يوجد سقف للأرباح؟','<span style="color:#be185d;font-weight:700;">لا يوجد أي سقف.</span> تستطيعين ربح آلاف الشواقل شهرياً. كلما زادت مبيعاتك عبر رابطك زادت أرباحك. بعض مسوّقينا يحققون أكثر من 5000 ₪ شهرياً.'],
                        ['06','ماذا لو لدي صالون تجميل؟','أنتِ المثال الأمثل! شاركي الرابط مع زبوناتك ليطلبن المنتجات والأجهزة التي تستخدمينها. اربحي عمولة على كل طلبية بالإضافة لأرباح صالونك. مكسب مزدوج بدون مجهود إضافي.'],
                    ] as $faq)
                    <div style="border-bottom:1px solid #e2e8f0;padding-bottom:1.25rem;">
                        <h4 style="font-weight:900;color:#0f172a;font-size:1rem;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;">
                            <span style="color:#ec4899;">{{ $faq[0] }}</span> {{ $faq[1] }}
                        </h4>
                        <p style="color:#475569;font-size:.85rem;line-height:1.8;">{!! $faq[2] !!}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ CTA ═══════════ --}}
    <div style="padding:4rem 1rem;background:#f8fafc;">
        <div style="max-width:800px;margin:0 auto;">
            <div style="background:linear-gradient(135deg,#ec4899,#be185d);border-radius:1.5rem;padding:3rem 2rem;text-align:center;position:relative;overflow:hidden;color:#fff;">
                <div style="position:absolute;top:-3rem;right:-3rem;width:10rem;height:10rem;border-radius:50%;background:rgba(255,255,255,0.1);"></div>
                <div style="position:absolute;bottom:-3rem;left:-3rem;width:10rem;height:10rem;border-radius:50%;background:rgba(255,255,255,0.08);"></div>
                <div style="position:relative;z-index:1;">
                    <span style="font-size:3rem;display:block;margin-bottom:1rem;">💎</span>
                    <h2 style="font-size:clamp(1.5rem,4vw,2.5rem);font-weight:900;color:#fff;margin-bottom:1rem;">جاهزة لبدء رحلة الأرباح؟</h2>
                    <p style="color:rgba(255,255,255,0.85);font-size:1rem;margin-bottom:2rem;max-width:500px;margin-left:auto;margin-right:auto;">انضمي الآن واحصلي على رابطك التسويقي فوراً. بدون رسوم، بدون تعقيد، بدون حد أقصى للأرباح.</p>

                    @auth
                        @if($affiliate && $affiliate->status === 'active')
                            <a href="{{ route('affiliate.dashboard') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2.5rem;border-radius:9999px;font-weight:700;font-size:1rem;background:#fff;color:#be185d;text-decoration:none;transition:all .2s;">
                                الذهاب للوحة التحكم <i class="ph ph-arrow-left"></i>
                            </a>
                        @else
                            <form action="{{ route('affiliate.register') }}" method="POST" style="max-width:400px;margin:0 auto;">
                                @csrf
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <input type="tel" name="phone" required style="flex:1;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:.75rem;padding:.75rem 1rem;color:#fff;font-size:.875rem;outline:none;" placeholder="رقم هاتفك" class="cta-input">
                                    <button type="submit" style="padding:.75rem 2rem;border-radius:.75rem;font-weight:700;font-size:.875rem;white-space:nowrap;background:#fff;color:#be185d;border:none;cursor:pointer;">
                                        ابدأي الآن
                                    </button>
                                </div>
                            </form>
                        @endif
                    @else
                        <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;">
                            <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2.5rem;border-radius:9999px;font-weight:700;font-size:1rem;background:#fff;color:#be185d;text-decoration:none;transition:all .2s;">
                                إنشاء حساب جديد
                            </a>
                            <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2.5rem;border-radius:9999px;font-weight:700;font-size:1rem;border:2px solid rgba(255,255,255,0.5);color:#fff;text-decoration:none;transition:all .2s;">
                                تسجيل الدخول
                            </a>
                        </div>
                    @endauth

                    <p style="color:rgba(255,255,255,0.6);font-size:.75rem;margin-top:1.25rem;">بتسجيلك فإنك توافقين على <a href="{{ route('terms') }}" style="color:#fff;text-decoration:underline;">شروط وأحكام برنامج التسويق</a></p>
                </div>
            </div>
        </div>
    </div>

</section>

<style>
@keyframes pulse {0%,100%{opacity:1}50%{opacity:0.4}}
.cta-input::placeholder{color:rgba(255,255,255,0.6);}
</style>
@endsection
