@extends($layoutPath)

@section('title', 'سياسة الشحن والتوصيل - ' . ($siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل'))

@section('meta_description', 'سياسة الشحن والتوصيل الخاصة بشركة شركة جنين للتجميل. تعرف على مناطق التغطية، أوقات التوصيل، والتكاليف في فلسطين.')

@section('content')

<style>
.policy-hero {
    background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 50%, #BAE6FD 100%);
    padding: 140px 0 60px;
    text-align: center;
    margin-bottom: 40px;
}
.policy-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #075985;
    margin-bottom: 15px;
}
.policy-hero p {
    font-size: 1.1rem;
    color: #0369A1;
    max-width: 700px;
    margin: 0 auto;
}
.policy-section {
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #E0F2FE;
}
.policy-section h2 {
    color: #0284C7;
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
    background: linear-gradient(135deg, #38BDF8, #0EA5E9);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
.policy-section h3 {
    color: #0369A1;
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
    background: #F0F9FF;
    border-radius: 10px;
    border-right: 4px solid #38BDF8;
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
    background: linear-gradient(135deg, #DBEAFE, #BFDBFE);
    border: 2px solid #3B82F6;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
}
.highlight-box.warning {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    border-color: #F59E0B;
}
.highlight-box.success {
    background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
    border-color: #10B981;
}
.highlight-box h4 {
    color: #1E40AF;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.highlight-box.warning h4 {
    color: #B45309;
}
.highlight-box.success h4 {
    color: #047857;
}
.delivery-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.delivery-table th {
    background: linear-gradient(135deg, #38BDF8, #0EA5E9);
    color: white;
    padding: 15px;
    text-align: right;
    font-weight: 600;
}
.delivery-table td {
    padding: 15px;
    border-bottom: 1px solid #E0F2FE;
    background: #F8FAFC;
}
.delivery-table tr:last-child td {
    border-bottom: none;
}
.delivery-table tr:hover td {
    background: #F0F9FF;
}
.contact-cta {
    background: linear-gradient(135deg, #0284C7, #0EA5E9);
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
    color: #0284C7;
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
    color: #075985;
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
    background: #F0F9FF;
    color: #0284C7;
}
@media (max-width: 768px) {
    .policy-hero h1 {
        font-size: 1.8rem;
    }
    .policy-nav {
        display: none;
    }
    .delivery-table {
        font-size: 0.9rem;
    }
    .delivery-table th,
    .delivery-table td {
        padding: 10px;
    }
}
</style>

{{-- Hero Section --}}
<div class="policy-hero">
    <div class="container">
        <h1><i class="fas fa-shipping-fast me-3"></i>سياسة الشحن والتوصيل</h1>
        <p>شبكة لوجستية متطورة تضمن وصول طلباتكِ بسرعة، وأمان، وبأعلى معايير الجودة الممكنة</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3 d-none d-lg-block">
            <div class="policy-nav">
                <h4><i class="fas fa-list me-2"></i>محتوى الصفحة</h4>
                <ul>
                    <li><a href="#section1">مناطق التغطية</a></li>
                    <li><a href="#section2">سرعة التجهيز</a></li>
                    <li><a href="#section3">الجداول الزمنية</a></li>
                    <li><a href="#section4">رسوم الشحن</a></li>
                    <li><a href="#section5">تتبع الطلبات</a></li>
                    <li><a href="#section6">التزامات العميل</a></li>
                    <li><a href="#contact">تواصل معنا</a></li>
                </ul>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9">
            
            {{-- Introduction --}}
            <div class="policy-section">
                <p class="lead" style="color: #4B5563; font-size: 1.1rem;">
                    نحن في <strong>{{ $siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل' }}</strong> ندرك أنكِ تترقبين وصول منتجات العناية والتجميل الخاصة بكِ بشغف. لذلك، قمنا ببناء شبكة لوجستية متطورة تضمن وصول طلباتكِ بسرعة، وأمان، وبأعلى معايير الجودة الممكنة.
                </p>
            </div>

            {{-- Section 1: Coverage Areas --}}
            <div class="policy-section" id="section1">
                <h2><i class="fas fa-map-marked-alt"></i> 1. مناطق التغطية</h2>
                
                <div class="highlight-box success">
                    <h4><i class="fas fa-check-circle"></i> تغطية شاملة</h4>
                    <p class="mb-0">نفخر بتقديم خدمات التوصيل إلى كافة أنحاء فلسطين. أينما كنتِ، نحرص على إيصال مستحضرات التجميل المفضلة لديكِ مباشرة إلى باب منزلكِ أو مكان عملكِ.</p>
                </div>
                
                <h3><i class="fas fa-mountain me-2"></i>الوصول للمناطق النائية</h3>
                <p>نتعاون مع أفضل شركات الشحن المحلية لضمان تغطية القرى والمناطق البعيدة، مع التزامنا التام بالحفاظ على سلامة المنتجات أثناء النقل.</p>
            </div>

            {{-- Section 2: Processing Speed --}}
            <div class="policy-section" id="section2">
                <h2><i class="fas fa-box-open"></i> 2. سرعة التجهيز وإدارة الطلبات</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li><strong>تقنية متقدمة:</strong> بفضل نظام إدارة المخزون المتقدم والمؤتمت في منصتنا، يتم استقبال طلبكِ ومعالجته بدقة وسرعة عالية.</li>
                            <li><strong>وقت التجهيز:</strong> يتم تجهيز وتغليف الطلبات المكتملة خلال <strong>24 إلى 48 ساعة عمل</strong> من لحظة تأكيد الطلب.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="highlight-box">
                            <h4><i class="fas fa-shield-alt"></i> التغليف الآمن</h4>
                            <p class="mb-0">نظراً لطبيعة منتجات التجميل، نستخدم مواد تغليف مخصصة وآمنة لحماية العبوات الزجاجية والمواد الحساسة للحرارة أو الكسر أثناء عملية النقل.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Delivery Timeframes --}}
            <div class="policy-section" id="section3">
                <h2><i class="fas fa-clock"></i> 3. الجداول الزمنية للتوصيل</h2>
                
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>المنطقة</th>
                            <th>مدة التوصيل</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-city me-2" style="color:#0EA5E9;"></i>المدن الرئيسية</td>
                            <td><strong>2 - 4 أيام عمل</strong></td>
                            <td>رام الله، نابلس، الخليل، بيت لحم، القدس، جنين</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-home me-2" style="color:#10B981;"></i>القرى والمناطق الأخرى</td>
                            <td><strong>3 - 6 أيام عمل</strong></td>
                            <td>بناءً على خطوط سير شركات الشحن المعتمدة</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="highlight-box warning">
                    <h4><i class="fas fa-calendar-week"></i> أوقات العمل</h4>
                    <p class="mb-0">أيام العمل الرسمية لعمليات التجهيز والشحن هي من <strong>السبت إلى الخميس</strong>، وتُستثنى أيام الجمعة والعطل الرسمية من حساب مدة التوصيل.</p>
                </div>
            </div>

            {{-- Section 4: Shipping Costs --}}
            <div class="policy-section" id="section4">
                <h2><i class="fas fa-coins"></i> 4. رسوم وتكاليف الشحن</h2>
                
                <ul>
                    <li><strong>شفافية تامة:</strong> يتم احتساب رسوم التوصيل بشكل آلي بناءً على المنطقة الجغرافية (المدينة أو المحافظة) التي يتم التوصيل إليها.</li>
                    <li><strong>وضوح قبل الدفع:</strong> تظهر كافة تكاليف الشحن بشكل واضح ومفصل في صفحة "سلة المشتريات" قبل إتمام عملية الدفع النهائي، لتكوني على اطلاع تام بإجمالي التكلفة.</li>
                </ul>
                
                <div class="highlight-box success">
                    <h4><i class="fas fa-gift"></i> عروض الشحن المجاني</h4>
                    <p class="mb-0">قد نوفر خدمة الشحن المجاني للطلبات التي تتجاوز قيمة معينة (يتم الإعلان عنها في الموقع أو عبر العروض الترويجية). تابعونا للاستفادة من هذه العروض!</p>
                </div>
            </div>

            {{-- Section 5: Order Tracking --}}
            <div class="policy-section" id="section5">
                <h2><i class="fas fa-search-location"></i> 5. تتبع الطلبات</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <h3><i class="fas fa-sms me-2"></i>إشعارات التتبع</h3>
                        <p>بمجرد تسليم طلبكِ لشركة الشحن، ستتلقين رسالة نصية (SMS) أو رسالة عبر البريد الإلكتروني تحتوي على معلومات الطلب ورقم التتبع (إن وُجد).</p>
                    </div>
                    <div class="col-md-6">
                        <h3><i class="fas fa-phone me-2"></i>تواصل المندوب</h3>
                        <p>يقوم مندوب التوصيل بالتواصل معكِ هاتفياً قبل التوجه إلى عنوانكِ للتأكد من تواجدكِ وتحديد الوقت المناسب للاستلام.</p>
                    </div>
                </div>
            </div>

            {{-- Section 6: Customer Obligations --}}
            <div class="policy-section" id="section6">
                <h2><i class="fas fa-user-check"></i> 6. التزامات العميل عند الاستلام</h2>
                
                <div class="highlight-box warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> صحة البيانات</h4>
                    <p class="mb-0">لضمان وصول الطلب دون تأخير، يرجى التأكد من إدخال العنوان بشكل دقيق (المدينة، الحي، الشارع، أقرب مَعلم) ورقم هاتف فعّال.</p>
                </div>
                
                <ul>
                    <li><strong>الاستجابة للمندوب:</strong> في حال عدم رد العميل على اتصالات مندوب التوصيل لعدة مرات متتالية، قد يتم إعادة الطلب إلى مستودعاتنا، وفي حالة طلب إعادة شحنه مرة أخرى، قد يتحمل العميل رسوم شحن إضافية.</li>
                    <li><strong>فحص الطلب:</strong> يُرجى التأكد من سلامة التغليف الخارجي للطلب عند استلامه من المندوب. في حال وجود أي ضرر ظاهر على الكرتون، يرجى التواصل مع خدمة العملاء فوراً.</li>
                </ul>
            </div>

            {{-- Contact CTA --}}
            <div class="contact-cta" id="contact">
                <h3><i class="fas fa-headset me-2"></i>هل لديك استفسار حول الشحن؟</h3>
                <p>فريق خدمة العملاء جاهز لمساعدتك في أي استفسار متعلق بسياسة الشحن والتوصيل</p>
                <a href="{{ route('contact') }}" class="btn">
                    <i class="fas fa-envelope me-2"></i>تواصل معنا
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
