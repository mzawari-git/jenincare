@extends($layoutPath)

@section('title', 'سياسة الخصوصية - ' . ($siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل'))

@section('meta_description', 'سياسة الخصوصية الخاصة بشركة شركة جنين للتجميل. تعرف على كيفية جمع وحماية واستخدام بياناتك الشخصية.')

@section('content')

<style>
.privacy-hero {
    background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 50%, #A7F3D0 100%);
    padding: 140px 0 60px;
    text-align: center;
    margin-bottom: 40px;
}
.privacy-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #065F46;
    margin-bottom: 15px;
}
.privacy-hero p {
    font-size: 1.1rem;
    color: #047857;
    max-width: 700px;
    margin: 0 auto;
}
.privacy-container {
    max-width: 900px;
    margin: 0 auto;
}
.privacy-section {
    background: #fff;
    border-radius: 16px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #D1FAE5;
}
.privacy-section h2 {
    color: #059669;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid #A7F3D0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.privacy-section h2 i {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #34D399, #059669);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.privacy-section h3 {
    color: #047857;
    font-size: 1.15rem;
    font-weight: 600;
    margin: 25px 0 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.privacy-section p {
    color: #4B5563;
    line-height: 1.9;
    margin-bottom: 15px;
    text-align: justify;
}
.privacy-section ul {
    list-style: none;
    padding: 0;
    margin: 15px 0;
}
.privacy-section ul li {
    padding: 12px 18px;
    margin-bottom: 10px;
    background: #ECFDF5;
    border-radius: 10px;
    border-right: 4px solid #34D399;
    color: #4B5563;
    line-height: 1.7;
}
.privacy-section ul li strong {
    color: #065F46;
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
.info-box h4 i {
    font-size: 1.2rem;
}
.privacy-box {
    background: linear-gradient(135deg, #FCE7F3, #FBCFE8);
    border: 2px solid #EC4899;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
}
.privacy-box h4 {
    color: #BE185D;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.privacy-box h4 i {
    font-size: 1.2rem;
}
.security-box {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    border: 2px solid #F59E0B;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
}
.security-box h4 {
    color: #B45309;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.security-box h4 i {
    font-size: 1.2rem;
}
.highlight-text {
    background: #D1FAE5;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    color: #065F46;
}
.privacy-nav {
    position: sticky;
    top: 100px;
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
}
.privacy-nav h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #065F46;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #A7F3D0;
}
.privacy-nav ul {
    list-style: none;
    padding: 0;
}
.privacy-nav ul li {
    margin-bottom: 8px;
}
.privacy-nav ul li a {
    color: #6B7280;
    text-decoration: none;
    font-size: 0.9rem;
    display: block;
    padding: 10px 12px;
    border-radius: 8px;
    transition: all 0.2s;
}
.privacy-nav ul li a:hover {
    background: #ECFDF5;
    color: #059669;
}
.contact-cta {
    background: linear-gradient(135deg, #059669, #10B981);
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
    color: #059669;
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
    background: #D1FAE5;
    border-radius: 10px;
    margin-bottom: 30px;
    color: #065F46;
    font-weight: 600;
}
@media (max-width: 768px) {
    .privacy-hero h1 {
        font-size: 1.8rem;
    }
    .privacy-section {
        padding: 25px;
    }
    .privacy-nav {
        display: none;
    }
}
</style>

{{-- Hero Section --}}
<div class="privacy-hero">
    <div class="container">
        <h1><i class="fas fa-lock me-3"></i>سياسة الخصوصية</h1>
        <p>نلتزم التزاماً صارماً بحماية خصوصية زوار ومستخدمي موقعنا الإلكتروني</p>
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
            <div class="privacy-nav">
                <h4><i class="fas fa-list me-2"></i>محتوى السياسة</h4>
                <ul>
                    <li><a href="#section1">البيانات التي نجمعها</a></li>
                    <li><a href="#section2">التسويق الرقمي</a></li>
                    <li><a href="#section3">أمن وحماية البيانات</a></li>
                    <li><a href="#section4">مشاركة البيانات</a></li>
                    <li><a href="#section5">حقوق المستخدم</a></li>
                    <li><a href="#contact">تواصل معنا</a></li>
                </ul>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9">
            
            {{-- Introduction --}}
            <div class="privacy-section">
                <p class="lead" style="color: #4B5563; font-size: 1.1rem; text-align: center; font-weight: 600;">
                    تلتزم "{{ $siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل' }}" التزاماً صارماً ومطلقاً بحماية خصوصية زوار ومستخدمي موقعها الإلكتروني ({{ request()->getHost() }}). نحن ندرك أهمية سرية بياناتكم، ولذلك صممنا سياساتنا بما يتوافق تماماً مع القوانين والتشريعات الفلسطينية الناظمة لحماية البيانات الشخصية والسرية التجارية.
                </p>
            </div>

            {{-- Section 1: Data Collection --}}
            <div class="privacy-section" id="section1">
                <h2><i class="fas fa-database"></i> 1. البيانات التي نقوم بجمعها</h2>
                
                <p>انطلاقاً من مبدأ الشفافية، نود إعلامكم بأننا نقوم بجمع البيانات الضرورية فقط لضمان تقديم خدمة احترافية ومخصصة، وتنقسم هذه البيانات إلى:</p>
                
                <div class="privacy-box">
                    <h4><i class="fas fa-user-shield"></i> المعلومات الشخصية الأساسية</h4>
                    <p class="mb-0">عند التسجيل أو إتمام عملية الشراء، نطلب معلومات محددة تشمل <span class="highlight-text">(الاسم الكامل، أرقام الهواتف الفعالة، البريد الإلكتروني، والعناوين البريدية أو الجغرافية الدقيقة)</span> في مختلف المدن والقرى والمخيمات الفلسطينية. الغاية الحصرية من هذه البيانات هي تأكيد الطلبات، وإتمام عمليات الشحن والتوصيل بدقة.</p>
                </div>
                
                <h3><i class="fas fa-chart-line"></i> بيانات التصفح والاستخدام</h3>
                <p>لضمان تحسين مستمر لتجربة التسوق، نقوم بجمع بيانات تقنية غير محددة للهوية الشخصية (مثل نوع المتصفح، نظام التشغيل، والصفحات الأكثر زيارة). تساعدنا هذه البيانات في فهم كيفية تفاعل الزوار مع المنصة وتطويرها بما يلبي تطلعاتكم.</p>
            </div>

            {{-- Section 2: Digital Marketing --}}
            <div class="privacy-section" id="section2">
                <h2><i class="fas fa-bullhorn"></i> 2. التسويق الرقمي وأنظمة التتبع المتقدمة</h2>
                
                <p>نسعى دائماً لتقديم عروض تجميلية مخصصة تناسب اهتمامات وتفضيلات عملائنا. لتحقيق ذلك دون المساس بسرعة أو أمان الموقع، تعتمد منصتنا على تقنيات تسويق وتتبع متطورة تعمل في الخلفية بكفاءة عالية عبر نظام <span class="highlight-text">(Queue Jobs)</span>:</p>
                
                <div class="info-box">
                    <h4><i class="fab fa-facebook"></i> بكسل فيسبوك (Facebook Pixel) وتقنية (CAPI)</h4>
                    <p class="mb-0">لا نعتمد فقط على أدوات التتبع التقليدية، بل نستخدم إعدادات التتبع من جهة الخادم (Server-Side Tracking) عبر واجهة (Conversions API). تضمن هذه التقنية دقة قياس أداء حملاتنا الإعلانية بشكل آمن ومستقل تماماً عن قيود متصفحات الويب، مما يعزز خصوصية نقل البيانات.</p>
                </div>
                
                <h3><i class="fab fa-tiktok"></i> بكسل تيك توك (TikTok Pixel) و (Events API)</h3>
                <p>تُستخدم هذه الأدوات المتقدمة لتحليل التفاعلات مع المحتوى المرئي والإعلاني بفعالية عالية، مما يساعدنا في تخصيص العروض التي تظهر لكم.</p>
                
                <div class="security-box">
                    <h4><i class="fas fa-cogs"></i> الشفافية والتحكم الإداري</h4>
                    <p class="mb-0">تمتلك إدارة المنصة صلاحيات كاملة للتحكم في أدوات التتبع واختبار الاتصال (Ping/Test) بشكل فوري وشفاف من خلال صفحة "التسويق والتتبع" المخصصة في لوحة التحكم المركزية، لضمان عملها وفقاً لأعلى معايير الخصوصية.</p>
                </div>
            </div>

            {{-- Section 3: Security --}}
            <div class="privacy-section" id="section3">
                <h2><i class="fas fa-shield-alt"></i> 3. أمن وحماية البيانات</h2>
                
                <p>أمان بياناتكم هو صميم بنيتنا التقنية. نحن نطبق أحدث المعايير وأكثرها صرامة لحماية قواعد البيانات من أي وصول غير مصرح به أو تسريب:</p>
                
                <div class="info-box">
                    <h4><i class="fas fa-lock"></i> سياسة أمن المحتوى (CSP)</h4>
                    <p class="mb-0">يتم تطبيق بروتوكول سياسة أمن المحتوى (Content Security Policy) بصرامة على كامل المنصة. تعمل هذه التقنية كدرع واقٍ يمنع تنفيذ أي نصوص برمجية (Scripts) خارجية غير مصرح بها أو ضارة على متصفح المستخدم، مما يحمي البيانات المدخلة (مثل كلمات المرور أو معلومات الاتصال) من محاولات الاختراق أو السرقة.</p>
                </div>
                
                <h3><i class="fas fa-sitemap"></i> العزل الهيكلي للبيانات</h3>
                <p>تعتمد منصتنا على معمارية برمجية متقدمة تفصل بين قواعد البيانات؛ حيث يتم عزل بيانات المستخدمين الحساسة والإعدادات الأساسية <span class="highlight-text">(الموجودة ضمن وحدة Core)</span> بشكل كامل عن بيانات المنتجات وإدارة المتجر <span class="highlight-text">(وحدة Commerce)</span>. يضيف هذا الفصل طبقة أمان برمجية استثنائية تحصّن معلوماتكم الشخصية.</p>
            </div>

            {{-- Section 4: Data Sharing --}}
            <div class="privacy-section" id="section4">
                <h2><i class="fas fa-share-alt"></i> 4. مشاركة البيانات مع أطراف ثالثة</h2>
                
                <p>خصوصيتكم أمانة لدينا، وعليه فإننا نؤكد على الآتي:</p>
                
                <div class="privacy-box">
                    <h4><i class="fas fa-handshake"></i> السرية التامة</h4>
                    <p class="mb-0">لا نقوم مطلقاً، وتحت أي ظرف، ببيع، أو تأجير، أو تداول بياناتك الشخصية لأي جهات خارجية أو وكالات تسويقية.</p>
                </div>
                
                <h3><i class="fas fa-shipping-fast"></i> شركاء الخدمات اللوجستية</h3>
                <p>يقتصر حصر مشاركة المعلومات الأساسية جداً (الاسم، العنوان المفصل، ورقم الهاتف) مع شركات الشحن والتوصيل المعتمدة والموثوقة في الأراضي الفلسطينية، وذلك لغاية وحيدة تتمثل في ضمان إيصال طلباتكم إلى باب منزلكم بأسرع وقت ممكن.</p>
            </div>

            {{-- Section 5: User Rights --}}
            <div class="privacy-section" id="section5">
                <h2><i class="fas fa-user-check"></i> 5. حقوق المستخدم وتعديلات السياسة</h2>
                
                <p>نحن نمنحك السيطرة الكاملة على بياناتك الشخصية:</p>
                
                <ul>
                    <li><strong>حقوق الوصول والتعديل:</strong> يحق لك كعميل في أي وقت طلب الوصول إلى بياناتك الشخصية المخزنة في أنظمتنا، أو تعديلها وتحديثها. كما يحق لك المطالبة بحذف حسابك وبياناتك نهائياً من خلال تقديم طلب رسمي لفريق خدمة العملاء.</li>
                    <li><strong>تحديثات السياسة:</strong> نظراً للتطورات التقنية المستمرة، تحتفظ الشركة بالحق في تعديل أو تحديث "سياسة الخصوصية" هذه بما يتوافق مع أي تحديثات في النظام الأساسي للمنصة أو التعديلات الطارئة في القانون الفلسطيني.</li>
                    <li><strong>نفاذ التعديلات:</strong> تُعتبر أي تعديلات على هذه السياسة سارية المفعول ونافذة فور نشرها وتحديثها على هذه الصفحة، ونشجعكم على مراجعتها بشكل دوري.</li>
                </ul>
            </div>

            {{-- Contact CTA --}}
            <div class="contact-cta" id="contact">
                <h3><i class="fas fa-envelope me-2"></i>هل لديك استفسار حول الخصوصية؟</h3>
                <p>فريق خصوصية البيانات جاهز لمساعدتك في أي استفسار</p>
                <a href="{{ route('contact') }}" class="btn">
                    <i class="fas fa-headset me-2"></i>تواصل معنا
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
