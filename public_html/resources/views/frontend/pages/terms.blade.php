@extends($layoutPath)

@section('title', 'الشروط والأحكام - ' . ($siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل'))

@section('meta_description', 'الشروط والأحكام القانونية لاستخدام منصة شركة شركة جنين للتجميل. اقرأ الوثيقة القانونية قبل إتمام أي عملية شراء.')

@section('content')

<style>
.terms-hero {
    background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 50%, #FDE68A 100%);
    padding: 140px 0 60px;
    text-align: center;
    margin-bottom: 40px;
}
.terms-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #92400E;
    margin-bottom: 15px;
}
.terms-hero p {
    font-size: 1.1rem;
    color: #B45309;
    max-width: 700px;
    margin: 0 auto;
}
.terms-container {
    max-width: 900px;
    margin: 0 auto;
}
.terms-section {
    background: #fff;
    border-radius: 16px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #FEF3C7;
}
.terms-section h2 {
    color: #D97706;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid #FDE68A;
    display: flex;
    align-items: center;
    gap: 12px;
}
.terms-section h2 i {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #F59E0B, #D97706);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.terms-section h3 {
    color: #B45309;
    font-size: 1.15rem;
    font-weight: 600;
    margin: 25px 0 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.terms-section p {
    color: #4B5563;
    line-height: 1.9;
    margin-bottom: 15px;
    text-align: justify;
}
.terms-section ul {
    list-style: none;
    padding: 0;
    margin: 15px 0;
}
.terms-section ul li {
    padding: 12px 18px;
    margin-bottom: 10px;
    background: #FFFBEB;
    border-radius: 10px;
    border-right: 4px solid #F59E0B;
    color: #4B5563;
    line-height: 1.7;
}
.terms-section ul li strong {
    color: #92400E;
}
.warning-box {
    background: linear-gradient(135deg, #FEE2E2, #FECACA);
    border: 2px solid #EF4444;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
}
.warning-box h4 {
    color: #DC2626;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.warning-box h4 i {
    font-size: 1.2rem;
}
.info-box {
    background: linear-gradient(135deg, #DBEAFE, #BFDBFE);
    border: 2px solid #3B82F6;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
}
.info-box h4 {
    color: #1E40AF;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.highlight-text {
    background: #FEF3C7;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    color: #92400E;
}
.terms-nav {
    position: sticky;
    top: 100px;
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
}
.terms-nav h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #92400E;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #FDE68A;
}
.terms-nav ul {
    list-style: none;
    padding: 0;
}
.terms-nav ul li {
    margin-bottom: 8px;
}
.terms-nav ul li a {
    color: #6B7280;
    text-decoration: none;
    font-size: 0.9rem;
    display: block;
    padding: 10px 12px;
    border-radius: 8px;
    transition: all 0.2s;
}
.terms-nav ul li a:hover {
    background: #FFFBEB;
    color: #D97706;
}
.contact-cta {
    background: linear-gradient(135deg, #D97706, #F59E0B);
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
    color: #D97706;
    padding: 12px 35px;
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
.last-updated {
    text-align: center;
    padding: 20px;
    background: #FEF3C7;
    border-radius: 10px;
    margin-bottom: 30px;
    color: #92400E;
    font-weight: 600;
}
@media (max-width: 768px) {
    .terms-hero h1 {
        font-size: 1.8rem;
    }
    .terms-section {
        padding: 25px;
    }
    .terms-nav {
        display: none;
    }
}
</style>

{{-- Hero Section --}}
<div class="terms-hero">
    <div class="container">
        <h1><i class="fas fa-file-contract me-3"></i>الشروط والأحكام</h1>
        <p>وثيقة قانونية تنظم علاقتكم بمنصتنا، يرجى قراءتها بعناية</p>
    </div>
</div>

<div class="container mb-5">
    {{-- Last Updated --}}
    <div class="last-updated">
        <i class="fas fa-calendar-alt me-2"></i> آخر تحديث: {{ date('Y/m/d') }}
    </div>

    <div class="row">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3 d-none d-lg-block">
            <div class="terms-nav">
                <h4><i class="fas fa-list me-2"></i>محتوى الوثيقة</h4>
                <ul>
                    <li><a href="#section1">نطاق التطبيق</a></li>
                    <li><a href="#section2">أنواع الحسابات</a></li>
                    <li><a href="#section3">سياسة التسعير</a></li>
                    <li><a href="#section4">الاستخدام الآمن</a></li>
                    <li><a href="#section5">حقوق الملكية</a></li>
                    <li><a href="#contact">تواصل معنا</a></li>
                </ul>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9">
            
            {{-- Introduction --}}
            <div class="terms-section">
                <p class="lead" style="color: #4B5563; font-size: 1.1rem; text-align: center; font-weight: 600;">
                    مرحباً بكم في منصة {{ $siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل' }}. تُشكل هذه الصفحة وثيقة قانونية بالغة الأهمية تنظم علاقتكم بمنصتنا. يرجى قراءتها بعناية قبل البدء باستخدام الموقع أو إتمام أي عملية شراء.
                </p>
            </div>

            {{-- Section 1: Legal Scope --}}
            <div class="terms-section" id="section1">
                <h2><i class="fas fa-gavel"></i> 1. نطاق التطبيق والقانون المرجعي</h2>
                
                <h3><i class="fas fa-handshake"></i> الاتفاقية المُلزمة</h3>
                <p>تُعد هذه الشروط والأحكام بمثابة اتفاقية قانونية ملزمة تماماً بين "{{ $siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل' }}" (يُشار إليها لاحقاً بـ "الشركة" أو "المنصة") وبين المستخدم (سواء كان متسوقاً فرداً أو جهة تجارية).</p>
                
                <h3><i class="fas fa-check-double"></i> الموافقة المطلقة</h3>
                <p>بمجرد دخولك إلى منصتنا، أو تصفحك لمنتجاتنا، أو استخدامك لأي من خدماتنا، فإنك تقر صراحةً باطلاعك الكامل على هذه الشروط والموافقة عليها دون أي تحفظ.</p>
                
                <h3><i class="fas fa-sync-alt"></i> تحديث الشروط</h3>
                <p>تحتفظ الشركة بالحق في تعديل أو تحديث هذه الشروط في أي وقت دون إشعار مسبق، ويُعد استمرارك في استخدام المنصة بعد التعديل موافقة ضمنية على الشروط الجديدة.</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-balance-scale"></i> المرجعية القانونية والاختصاص</h4>
                    <p class="mb-0">تخضع هذه الوثيقة وتُفسر حصرياً وفقاً للقوانين والتشريعات المعمول بها في <span class="highlight-text">دولة فلسطين</span>. في حال حدوث أي نزاع (لا سمح الله) يتم حله ودياً أولاً، وإلا فيكون الاختصاص الحصري لمحاكم فلسطين النظامية.</p>
                </div>
            </div>

            {{-- Section 2: Account Types --}}
            <div class="terms-section" id="section2">
                <h2><i class="fas fa-users-cog"></i> 2. أنواع الحسابات والخدمات المقدمة</h2>
                
                <p>توفر منصتنا بنية تحتية برمجية متقدمة لخدمة شرائح متعددة من العملاء، وتُدار جميع الحسابات عبر "وحدة الإدارة الأساسية للمستخدمين" (Core Module):</p>
                
                <ul>
                    <li><strong>حسابات الأفراد (B2C):</strong> مخصصة للمستهلكين النهائيين، تتيح لهم تجربة تسوق سلسة، تتبع الطلبات، وإدارة قوائم المفضلات بطريقة آمنة.</li>
                    <li><strong>حسابات الشركات والصالونات (B2B):</strong> حسابات مخصصة لعملاء الجملة، تخضع لعملية تحقق وموافقة مسبقة من قِبل إدارة الشركة للوصول إلى الميزات التجارية.</li>
                    <li><strong>طلبات عروض الأسعار (RFQ):</strong> تتيح المنصة لأصحاب الحسابات التجارية المعتمدة (B2B) استخدام نظام مخصص لرفع طلبات عروض أسعار للكميات الكبيرة، وتتم معالجتها آلياً وبشرياً لتقديم أفضل عروض التوريد.</li>
                </ul>
                
                <div class="warning-box">
                    <h4><i class="fas fa-exclamation-triangle"></i> دقة البيانات والمسؤولية</h4>
                    <p class="mb-0">يلتزم المستخدم بإدخال بيانات تسجيل دقيقة، صحيحة، ومحدثة (الاسم، العنوان، رقم الهاتف). تُخلي الشركة مسؤوليتها التامة عن أي تأخير في الشحن أو إلغاء للطلبات ناتج عن تقديم معلومات خاطئة أو ناقصة، ويتحمل المستخدم مسؤولية الحفاظ على سرية بيانات الدخول الخاصة به.</p>
                </div>
            </div>

            {{-- Section 3: Pricing Policy --}}
            <div class="terms-section" id="section3">
                <h2><i class="fas fa-tags"></i> 3. سياسة التسعير وإدارة الطلبات</h2>
                
                <div class="info-box">
                    <h4><i class="fas fa-calculator"></i> نظام التسعير الذكي (Pricing Engine)</h4>
                    <p class="mb-0">تعتمد المنصة على خوارزميات تسعير متطورة تضمن الشفافية وتمنح العملاء (خاصة في قطاع B2B) خصومات تلقائية ومدروسة تتناسب طردياً مع حجم ونوع الكمية المضافة إلى سلة المشتريات.</p>
                </div>
                
                <h3><i class="fas fa-boxes"></i> إدارة المخزون والتوافر</h3>
                <p>عرض المنتج على الموقع لا يضمن توافره الدائم. تخضع جميع الطلبات للمراجعة والتحقق الفوري من توافرها ضمن "نظام إدارة المخزون المتقدم" لدينا. في حال نفاذ الكمية بعد إتمام الدفع، يحق للشركة إلغاء الطلب (أو جزء منه)، مع الالتزام التام بإرجاع المبلغ المدفوع للعميل دون أي تأخير.</p>
                
                <h3><i class="fas fa-shekel-sign"></i> العملة المعتمدة والضرائب</h3>
                <p>تُعرض جميع الأسعار على المنصة بعملة الشيكل الإسرائيلي (ILS) (أو العملة المحلية المعتمدة حينها). جميع الأسعار الظاهرة للمستهلك النهائي تشمل ضريبة القيمة المضافة (VAT) بما يتوافق مع متطلبات وزارة المالية الفلسطينية.</p>
                
                <h3><i class="fas fa-edit"></i> تعديل الأسعار</h3>
                <p>تحتفظ المنصة بحق تغيير أسعار المنتجات في أي وقت دون إشعار مسبق، ولا يسري هذا التغيير على الطلبات التي تم تأكيدها ودفع قيمتها بنجاح.</p>
            </div>

            {{-- Section 4: Security Usage --}}
            <div class="terms-section" id="section4">
                <h2><i class="fas fa-shield-alt"></i> 4. الاستخدام الآمن والقيود التقنية</h2>
                
                <p>لضمان تجربة مستخدم مستقرة وعادلة لجميع عملائنا في فلسطين، قمنا بتزويد المنصة بأحدث التقنيات الأمنية. يُحظر تماماً القيام بأي ممارسات قد تضر بالبنية التحتية للموقع:</p>
                
                <ul>
                    <li><strong>نظام تقييد الطلبات (Rate Limiting):</strong> تعمل المنصة بآلية دفاعية تلقائية تحظر العناوين (IPs) التي تقوم بإرسال عدد هائل ومكثف من الطلبات في وقت قصير لمنع إساءة الاستخدام والبرمجيات الآلية (Bots).</li>
                    <li><strong>الحماية من الهجمات الموزعة (DDoS Protection):</strong> تخضع حركة المرور على المنصة لمراقبة حثيثة وتطبيق أنظمة حماية متقدمة لصد أي هجمات حجب خدمة، مما يضمن بقاء الموقع متاحاً للعملاء الحقيقيين.</li>
                </ul>
                
                <div class="warning-box">
                    <h4><i class="fas fa-ban"></i> المساءلة القانونية والحظر</h4>
                    <p>إن أي محاولة مقصودة لاختراق خوادم المنصة، التحايل على خوارزميات التسعير، استغلال الثغرات، أو نشر برمجيات خبيثة، ستؤدي إلى الحظر الفوري والنهائي لحساب المستخدم وعنوان الـ IP الخاص به، مع احتفاظ الشركة بحق الملاحقة القانونية والمطالبة بالتعويض عن الأضرار الناجمة.</p>
                </div>
            </div>

            {{-- Section 5: Intellectual Property --}}
            <div class="terms-section" id="section5">
                <h2><i class="fas fa-copyright"></i> 5. حقوق الملكية الفكرية والامتثال القانوني</h2>
                
                <h3><i class="fas fa-gem"></i> الملكية الحصرية</h3>
                <p>إن جميع المحتويات المتاحة على المنصة، بما في ذلك النصوص، الصور، الفيديوهات، العلامات التجارية، الشعارات، وتصميم واجهة المستخدم باللغة العربية (RTL)، هي ملكية فكرية حصرية لـ "{{ $siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل' }}" ومحمية بموجب قوانين حقوق الطبع والنشر.</p>
                
                <h3><i class="fas fa-copy"></i> حظر النسخ</h3>
                <p>يُمنع منعاً باتاً نسخ، إعادة إنتاج، توزيع، أو استخدام أي جزء من محتوى الموقع لأغراض تجارية أو عامة دون الحصول على إذن كتابي ورسمي ومسبق من إدارة الشركة.</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-robot"></i> الرقابة والامتثال الذكي (AI Compliance)</h4>
                    <p class="mb-0">تخضع جميع العمليات الشرائية وسلوكيات التصفح لرقابة آنية من خلال "وحدة فحص الامتثال المعتمدة على الذكاء الاصطناعي". تعمل هذه الوحدة على رصد أي نشاط مشبوه لضمان توافق جميع المعاملات مع السياسات الداخلية للشركة ومعايير التجارة الإلكترونية الآمنة، مما يوفر بيئة تسوق آمنة وموثوقة للجميع.</p>
                </div>
            </div>

            {{-- Contact CTA --}}
            <div class="contact-cta" id="contact">
                <h3><i class="fas fa-envelope me-2"></i>هل لديك استفسار قانوني؟</h3>
                <p>فريقنا القانوني والقانوني جاهز لمساعدتك في أي استفسار يتعلق بالشروط والأحكام</p>
                <a href="{{ route('contact') }}" class="btn">
                    <i class="fas fa-headset me-2"></i>تواصل معنا
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
