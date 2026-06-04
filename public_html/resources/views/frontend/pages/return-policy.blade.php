@extends($layoutPath)

@section('title', 'سياسة الإرجاع والاستبدال - ' . ($siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل'))

@section('meta_description', 'سياسة الإرجاع والاستبدال الخاصة بشركة شركة جنين للتجميل. تعرف على الشروط والأحكام والإطار الزمني لإرجاع واستبدال منتجات العناية بالبشرة والتجميل.')

@section('content')

<style>
.policy-hero {
    background: linear-gradient(135deg, #FDF2F8 0%, #FCE7F3 50%, #FBCFE8 100%);
    padding: 140px 0 60px;
    text-align: center;
    margin-bottom: 40px;
}
.policy-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #831843;
    margin-bottom: 15px;
}
.policy-hero p {
    font-size: 1.1rem;
    color: #9D174D;
    max-width: 600px;
    margin: 0 auto;
}
.policy-section {
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #fce7f3;
}
.policy-section h2 {
    color: #DB2777;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.policy-section h2 i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #F472B6, #EC4899);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.policy-section h3 {
    color: #BE185D;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 25px 0 15px;
}
.policy-section ul {
    list-style: none;
    padding: 0;
}
.policy-section ul li {
    padding: 12px 15px;
    margin-bottom: 10px;
    background: #FDF2F8;
    border-radius: 10px;
    border-right: 4px solid #EC4899;
    position: relative;
}
.policy-section ul li::before {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: #10B981;
    margin-left: 10px;
}
.highlight-box {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    border: 2px solid #F59E0B;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
}
.highlight-box.danger {
    background: linear-gradient(135deg, #FEE2E2, #FECACA);
    border-color: #EF4444;
}
.highlight-box.success {
    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
    border-color: #10B981;
}
.highlight-box h4 {
    color: #92400E;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.highlight-box.danger h4 {
    color: #DC2626;
}
.highlight-box.success h4 {
    color: #059669;
}
.contact-cta {
    background: linear-gradient(135deg, #DB2777, #EC4899);
    color: white;
    padding: 40px;
    border-radius: 16px;
    text-align: center;
    margin-top: 40px;
}
.contact-cta h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}
.contact-cta p {
    margin-bottom: 20px;
    opacity: 0.95;
}
.contact-cta .btn {
    background: white;
    color: #DB2777;
    padding: 12px 30px;
    border-radius: 30px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}
.contact-cta .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.policy-nav {
    position: sticky;
    top: 100px;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
}
.policy-nav h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #831843;
    margin-bottom: 15px;
}
.policy-nav ul {
    list-style: none;
    padding: 0;
}
.policy-nav ul li {
    margin-bottom: 8px;
}
.policy-nav ul li a {
    color: #6B7280;
    text-decoration: none;
    font-size: 0.9rem;
    display: block;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.2s;
}
.policy-nav ul li a:hover {
    background: #FDF2F8;
    color: #DB2777;
}
@media (max-width: 768px) {
    .policy-hero h1 {
        font-size: 1.8rem;
    }
    .policy-nav {
        display: none;
    }
}
</style>

{{-- Hero Section --}}
<div class="policy-hero">
    <div class="container">
        <h1><i class="fas fa-undo-alt me-3"></i>سياسة الإرجاع والاستبدال</h1>
        <p>نضع رضا عملائنا في مقدمة أولوياتنا، ونلتزم بأعلى معايير الصحة والسلامة العامة</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3 d-none d-lg-block">
            <div class="policy-nav">
                <h4><i class="fas fa-list me-2"></i>محتوى الصفحة</h4>
                <ul>
                    <li><a href="#section1">الشروط العامة</a></li>
                    <li><a href="#section2">الإطار الزمني</a></li>
                    <li><a href="#section3">الحالات المستثناة</a></li>
                    <li><a href="#section4">المنتجات التالفة</a></li>
                    <li><a href="#section5">تكاليف الشحن</a></li>
                    <li><a href="#section6">استرداد الأموال</a></li>
                    <li><a href="#section7">مبيعات الجملة</a></li>
                    <li><a href="#contact">تواصل معنا</a></li>
                </ul>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9">
            
            {{-- Introduction --}}
            <div class="policy-section">
                <p class="lead" style="color: #4B5563; font-size: 1.1rem;">
                    نحن في <strong>{{ $siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل' }}</strong> نضع رضا عملائنا في مقدمة أولوياتنا. ولأننا نتعامل مع مستحضرات التجميل والعناية بالبشرة، فإننا نلتزم بأعلى معايير الصحة والسلامة العامة، وبما يتوافق تماماً مع قانون حماية المستهلك الفلسطيني.
                </p>
                <p style="color: #6B7280;">
                    حرصاً منا على تقديم تجربة تسوق شفافة ومريحة، قمنا بصياغة هذه السياسة لتوضيح كافة الشروط والأحكام المتعلقة بالإرجاع والاستبدال:
                </p>
            </div>

            {{-- Section 1: General Conditions --}}
            <div class="policy-section" id="section1">
                <h2><i class="fas fa-clipboard-check"></i> 1. الشروط العامة للإرجاع والاستبدال</h2>
                <p>نظراً للحساسية العالية لمنتجات التجميل والعناية الشخصية، تُقبل طلبات الإرجاع أو الاستبدال وفقاً للشروط الصارمة التالية:</p>
                
                <ul>
                    <li><strong>حالة المنتج:</strong> يجب أن يكون المنتج في حالته الأصلية تماماً عند الشراء، غير مفتوح، وغير مستخدم نهائياً.</li>
                    <li><strong>الغلاف الأصلي:</strong> يجب أن يكون المنتج داخل عبوته الأصلية، مع عدم نزع أو إتلاف أي من الأختام الأمنية (اللاصق الشفاف أو ختم المصنع) أو الملصقات أو الباركود.</li>
                    <li><strong>إثبات الشراء:</strong> يُشترط إرفاق فاتورة الشراء الأصلية أو رقم الطلب عند تقديم الطلب.</li>
                </ul>
            </div>

            {{-- Section 2: Time Frame --}}
            <div class="policy-section" id="section2">
                <h2><i class="fas fa-clock"></i> 2. الإطار الزمني المسموح</h2>
                <div class="highlight-box">
                    <h4><i class="fas fa-calendar-alt"></i> فترة الإرجاع والاستبدال</h4>
                    <p class="mb-0">يحق للعميل طلب إرجاع أو استبدال المنتجات (التي تطابق شروط الحالة المذكورة أعلاه) خلال <strong>3 أيام عمل كحد أقصى</strong> من تاريخ استلام الطلب. لن يتم النظر في أي طلبات تتجاوز هذه المدة.</p>
                </div>
            </div>

            {{-- Section 3: Excluded Cases --}}
            <div class="policy-section" id="section3">
                <h2><i class="fas fa-ban"></i> 3. الحالات المستثناة من الإرجاع والاستبدال</h2>
                <p>حفاظاً على الصحة العامة، يُمنع إرجاع أو استبدال المنتجات في الحالات التالية:</p>
                
                <div class="highlight-box danger">
                    <h4><i class="fas fa-exclamation-triangle"></i> منتجات لا تُرجع</h4>
                    <ul class="mb-0">
                        <li>المنتجات التي تم فتحها، أو إزالة ختم الأمان عنها، أو استخدامها بأي شكل من الأشكال (حتى ولو لتجربة بسيطة).</li>
                        <li>المنتجات التالفة نتيجة سوء التخزين أو سوء الاستخدام من قِبل العميل.</li>
                        <li>المنتجات التي تم شراؤها ضمن العروض الترويجية أو التصفية النهائية (إلا في حال وجود عيب مصنعي).</li>
                        <li>أدوات التجميل المباشرة (مثل الفراشي، الإسفنجات، أدوات العناية الشخصية) لأسباب صحية قطعية.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 4: Defective Products --}}
            <div class="policy-section" id="section4">
                <h2><i class="fas fa-shield-alt"></i> 4. المنتجات التالفة أو المعيبة مصنعياً</h2>
                <p>نضمن لك جودة منتجاتنا، ولكن في حال حدوث خطأ أو استلام منتج متضرر:</p>
                
                <div class="highlight-box success">
                    <h4><i class="fas fa-check-circle"></i> ضمان الجودة</h4>
                    <ul class="mb-0">
                        <li>إذا استلمت منتجاً تالفاً أثناء الشحن، أو يحتوي على عيب مصنعي، أو استلمت منتجاً مختلفاً عما قمت بطلبه، نرجو منك التواصل معنا خلال <strong>24 ساعة</strong> من استلام الطلب.</li>
                        <li><strong>التكاليف:</strong> في هذه الحالة، تتحمل الشركة كافة تكاليف الشحن والإرجاع.</li>
                        <li><strong>التعويض:</strong> يحق للعميل الاختيار بين استبدال المنتج بآخر سليم مجاناً، أو استرداد كامل المبلغ المدفوع.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 5: Shipping Costs --}}
            <div class="policy-section" id="section5">
                <h2><i class="fas fa-shipping-fast"></i> 5. تكاليف الشحن ورسوم الإرجاع</h2>
                <p>في حال رغب العميل بإرجاع أو استبدال منتج سليم (لمجرد تغيير الرأي)، وكان المنتج مطابقاً لشروط الإرجاع:</p>
                <ul>
                    <li>يتحمل العميل تكاليف الشحن الأصلية وتكاليف شحن الإرجاع.</li>
                    <li>سيتم خصم هذه الرسوم من المبلغ المسترد.</li>
                    <li>لا توجد رسوم إضافية على المنتجات المعيبة مصنعياً (نتحمل جميع التكاليف).</li>
                </ul>
            </div>

            {{-- Section 6: Refund --}}
            <div class="policy-section" id="section6">
                <h2><i class="fas fa-money-bill-wave"></i> 6. آلية استرداد الأموال</h2>
                <ul>
                    <li>بمجرد وصول المنتج المرتجع إلى مستودعاتنا واجتيازه لفحص الجودة (للتأكد من عدم فتحه أو استخدامه)، سيتم البدء بإجراءات استرداد الأموال.</li>
                    <li>تتم عملية استرداد الأموال بنفس طريقة الدفع الأصلية (أو عبر المحافظ الإلكترونية المعتمدة في فلسطين أو حوالة بنكية).</li>
                    <li>المدة الزمنية: خلال مدة تتراوح بين <strong>3 إلى 7 أيام عمل</strong> من تاريخ استلام المنتج المرتجع.</li>
                </ul>
            </div>

            {{-- Section 7: B2B --}}
            <div class="policy-section" id="section7">
                <h2><i class="fas fa-building"></i> 7. قسم مبيعات الجملة والشركات (B2B)</h2>
                <p>نظراً لطبيعة وحجم الطلبات التجارية، فإن طلبات الجملة لا تخضع لسياسة إرجاع الأفراد المذكورة أعلاه:</p>
                <ul>
                    <li><strong>عقود التوريد:</strong> تخضع طلبات الجملة والشركات لسياسات إرجاع واستبدال مخصصة يتم النص عليها بوضوح في "عقود التوريد" المبرمة بين الشركة والعميل التجاري.</li>
                    <li><strong>إدارة المخزون:</strong> تخضع هذه الطلبات لسياسات إدارة المخزون المتقدمة، وقد يترتب على إرجاع الكميات الكبيرة (لغير الأسباب المصنعية) رسوم إعادة تخزين (Restocking Fees).</li>
                </ul>
            </div>

            {{-- Contact CTA --}}
            <div class="contact-cta" id="contact">
                <h3><i class="fas fa-headset me-2"></i>هل لديك استفسار حول الإرجاع؟</h3>
                <p>فريق خدمة العملاء جاهز لمساعدتك في أي استفسار متعلق بسياسة الإرجاع والاستبدال</p>
                <a href="{{ route('contact') }}" class="btn">
                    <i class="fas fa-envelope me-2"></i>تواصل معنا
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
