@extends($layoutPath)

@section('title', 'الأسئلة الشائعة - ' . ($siteSettings['site_name'] ?? 'شركة شركة جنين للتجميل'))

@section('meta_description', 'الأسئلة الشائعة حول خدمات شركة شركة جنين للتجميل. تعرف على نظام B2B، حماية البيانات، الشحن، والإرجاع.')

@section('content')

<style>
.faq-hero {
    background: linear-gradient(135deg, #F5F3FF 0%, #EDE9FE 50%, #DDD6FE 100%);
    padding: 140px 0 60px;
    text-align: center;
    margin-bottom: 40px;
}
.faq-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #5B21B6;
    margin-bottom: 15px;
}
.faq-hero p {
    font-size: 1.1rem;
    color: #7C3AED;
    max-width: 600px;
    margin: 0 auto;
}
.faq-search {
    max-width: 600px;
    margin: 30px auto 0;
    position: relative;
}
.faq-search input {
    width: 100%;
    padding: 15px 25px 15px 50px;
    border: 2px solid #C4B5FD;
    border-radius: 50px;
    font-size: 1rem;
    background: white;
    transition: all 0.3s;
}
.faq-search input:focus {
    outline: none;
    border-color: #8B5CF6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}
.faq-search i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #8B5CF6;
    font-size: 1.2rem;
}
.faq-container {
    max-width: 900px;
    margin: 0 auto;
}
.faq-category {
    margin-bottom: 40px;
}
.faq-category-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #6D28D9;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #DDD6FE;
    display: flex;
    align-items: center;
    gap: 10px;
}
.faq-category-title i {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #A78BFA, #8B5CF6);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.faq-item {
    background: white;
    border-radius: 12px;
    margin-bottom: 15px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    border: 1px solid #EDE9FE;
}
.faq-question {
    padding: 20px 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s;
    font-weight: 600;
    color: #4C1D95;
}
.faq-question:hover {
    background: #F5F3FF;
}
.faq-question i {
    color: #8B5CF6;
    transition: transform 0.3s;
}
.faq-item.active .faq-question i {
    transform: rotate(180deg);
}
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}
.faq-item.active .faq-answer {
    max-height: 2000px;
}
.faq-answer-content {
    padding: 0 25px 25px;
    color: #6B7280;
    line-height: 1.8;
}
.faq-answer-content p {
    margin-bottom: 15px;
}
.faq-answer-content h4 {
    color: #7C3AED;
    font-size: 1rem;
    font-weight: 600;
    margin: 20px 0 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.faq-answer-content h4 i {
    color: #10B981;
    font-size: 0.9rem;
}
.faq-answer-content ul {
    list-style: none;
    padding: 0;
}
.faq-answer-content ul li {
    padding: 10px 15px;
    margin-bottom: 8px;
    background: #F5F3FF;
    border-radius: 8px;
    border-right: 3px solid #A78BFA;
}
.faq-answer-content ul li::before {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: #10B981;
    margin-left: 8px;
}
.highlight-feature {
    background: linear-gradient(135deg, #FEF3C7, #FDE68A);
    border: 2px solid #F59E0B;
    border-radius: 10px;
    padding: 15px;
    margin: 15px 0;
}
.highlight-feature.security {
    background: linear-gradient(135deg, #DBEAFE, #BFDBFE);
    border-color: #3B82F6;
}
.highlight-feature h5 {
    color: #B45309;
    font-weight: 700;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.highlight-feature.security h5 {
    color: #1E40AF;
}
.highlight-feature h5 i {
    font-size: 1.1rem;
}
.contact-banner {
    background: linear-gradient(135deg, #7C3AED, #8B5CF6);
    color: white;
    padding: 40px;
    border-radius: 16px;
    text-align: center;
    margin-top: 50px;
}
.contact-banner h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}
.contact-banner p {
    margin-bottom: 20px;
    opacity: 0.95;
}
.contact-banner .btn {
    background: white;
    color: #7C3AED;
    padding: 12px 35px;
    border-radius: 30px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}
.contact-banner .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.no-results {
    text-align: center;
    padding: 40px;
    color: #6B7280;
    display: none;
}
.no-results.show {
    display: block;
}
@media (max-width: 768px) {
    .faq-hero h1 {
        font-size: 1.8rem;
    }
    .faq-question {
        padding: 15px 20px;
        font-size: 0.95rem;
    }
    .faq-answer-content {
        padding: 0 20px 20px;
    }
}
</style>

{{-- Hero Section --}}
<div class="faq-hero">
    <div class="container">
        <h1><i class="fas fa-question-circle me-3"></i>الأسئلة الشائعة</h1>
        <p>إجابات على استفساراتكم حول خدماتنا، الأمان، والشحن</p>
        
        {{-- Search Box --}}
        <div class="faq-search">
            <i class="fas fa-search"></i>
            <input type="text" id="faqSearch" placeholder="ابحث عن سؤال..." onkeyup="searchFAQ()">
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="faq-container">
        
        {{-- B2B Category --}}
        <div class="faq-category" data-category="b2b">
            <h2 class="faq-category-title"><i class="fas fa-building"></i> طلبات الجملة والشركات (B2B)</h2>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>تفاصيل الطلب بكميات كبيرة (لصالونات التجميل والشركات)</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>نظام مخصص للشركات (B2B) وطلبات التسعير (RFQ): لا تقتصر منصة {{ $siteSettings['site_name'] ?? 'Jenin Care' }} على البيع المباشر للأفراد (B2C)، بل تم بناء وحدة برمجية متكاملة ومستقلة تماماً مخصصة للشركات والصالونات (B2B).</p>
                        
                        <h4><i class="fas fa-check-circle"></i> نظام طلبات عروض الأسعار (RFQ)</h4>
                        <p>تتيح هذه الوحدة لأصحاب الصالونات إنشاء حسابات تجارية، وتقديم طلبات عروض أسعار رسمية (RFQ) للحصول على أفضل التكلفات للكميات الضخمة.</p>
                        
                        <div class="highlight-feature">
                            <h5><i class="fas fa-calculator"></i> محرك التسعير الذكي (Pricing Engine)</h5>
                            <p class="mb-0">بدلاً من التفاوض اليدوي على الأسعار، تعمل المنصة من خلال "محرك تسعير" متقدم (Pricing Engine). يقوم هذا النظام بقراءة الكميات المدرجة في سلة المشتريات وتطبيق هيكل خصومات تدريجي وآلي؛ فكلما زادت الكمية التي يطلبها الصالون، انخفض سعر القطعة الواحدة بشكل فوري وشفاف.</p>
                        </div>
                        
                        <h4><i class="fas fa-check-circle"></i> إدارة مخزون متقدمة</h4>
                        <p>طلبات الكميات الكبيرة مدعومة بنظام متقدم لإدارة المخزون، مما يضمن:</p>
                        <ul>
                            <li>دقة عالية في عرض المنتجات المتوفرة فعلياً في المستودعات</li>
                            <li>منع بيع منتجات غير متاحة</li>
                            <li>تسليم طلبات الصالونات في الوقت المحدد دون تأخير</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Security Category --}}
        <div class="faq-category" data-category="security">
            <h2 class="faq-category-title"><i class="fas fa-shield-alt"></i> حماية البيانات والأمان</h2>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>تفاصيل حماية البيانات الشخصية والبنكية</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <p>نحن نلتزم بأعلى معايير الأمان لحماية بياناتك الشخصية والمالية. إليك التفاصيل الكاملة لنظام الحماية المتقدم لدينا:</p>
                        
                        <div class="highlight-feature security">
                            <h5><i class="fas fa-shield-virus"></i> الحماية من هجمات حجب الخدمة (DDoS Protection)</h5>
                            <p class="mb-0">المنصة مزودة بجدار حماية متقدم لصد هجمات حجب الخدمة الموزعة (DDoS). هذا يعني أن الموقع محمي ضد محاولات إغراق السيرفر بزيارات وهمية تهدف إلى تعطيله، مما يضمن بقاء المتجر متاحاً وسريعاً لإتمام عمليات الشراء في كل الأوقات.</p>
                        </div>
                        
                        <h4><i class="fas fa-check-circle"></i> تقييد معدل الطلبات (Rate Limiting)</h4>
                        <p>يستخدم النظام تقنية تقييد معدل الطلبات لمنع أي نشاط آلي مكثف أو روبوتات خبيثة (Bots) من استغلال الموقع أو محاولة تخمين كلمات المرور. هذه التقنية تحمي حسابات العملاء من الاختراق وتوفر بيئة تسوق آمنة.</p>
                        
                        <h4><i class="fas fa-check-circle"></i> سياسة أمن المحتوى (Content Security Policy - CSP)</h4>
                        <p>تُطبق المنصة سياسة صارمة لأمن المحتوى (CSP). تعتبر هذه الطبقة الأمنية حاسمة لمنع ثغرات الحقن البرمجي (مثل XSS)، حيث تضمن عدم تنفيذ أي كود خبيث على متصفح العميل أثناء إدخاله لبياناته الشخصية أو بطاقته الائتمانية.</p>
                        
                        <div class="highlight-feature security">
                            <h5><i class="fas fa-robot"></i> فحص الامتثال والأمان (AI Compliance)</h5>
                            <p class="mb-0">لضمان أعلى معايير الأمان، تحتوي المنصة على وحدة برمجية متخصصة في فحص الامتثال (AI Compliance). تعمل هذه الوحدة على مراقبة العمليات للتأكد من توافقها الدائم مع معايير الأمان وسياسات الخصوصية.</p>
                        </div>
                        
                        <h4><i class="fas fa-check-circle"></i> عزل البيانات الهيكلي</h4>
                        <p>تم تصميم بنية المنصة لتعزل بيانات المستخدمين والإعدادات الحساسة (Core) عن وحدات المنتجات والطلبات (Commerce). هذا التقسيم المعماري:</p>
                        <ul>
                            <li>يقلل من المخاطر الأمنية</li>
                            <li>يضمن بقاء بيانات العملاء في بيئة مشفرة ومعزولة</li>
                            <li>يمنع الوصول غير المصرح به إلى المعلومات الحساسة</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- No Results Message --}}
        <div class="no-results" id="noResults">
            <i class="fas fa-search" style="font-size: 3rem; color: #C4B5FD; margin-bottom: 15px;"></i>
            <h4>لم يتم العثور على نتائج</h4>
            <p>جرب البحث بكلمات مختلفة أو تواصل معنا مباشرة</p>
        </div>

        {{-- Contact Banner --}}
        <div class="contact-banner">
            <h3><i class="fas fa-headset me-2"></i>لم تجد إجابة لسؤالك؟</h3>
            <p>فريق دعم العملاء جاهز لمساعدتك في أي استفسار</p>
            <a href="{{ route('contact') }}" class="btn">
                <i class="fas fa-envelope me-2"></i>تواصل معنا
            </a>
        </div>

    </div>
</div>

<script>
function toggleFAQ(element) {
    const item = element.parentElement;
    const isActive = item.classList.contains('active');
    
    // Close all items
    document.querySelectorAll('.faq-item').forEach(faq => {
        faq.classList.remove('active');
    });
    
    // Open clicked item if it wasn't active
    if (!isActive) {
        item.classList.add('active');
    }
}

function searchFAQ() {
    const searchTerm = document.getElementById('faqSearch').value.toLowerCase();
    const items = document.querySelectorAll('.faq-item');
    const categories = document.querySelectorAll('.faq-category');
    let hasResults = false;
    
    items.forEach(item => {
        const question = item.querySelector('.faq-question span').textContent.toLowerCase();
        const answer = item.querySelector('.faq-answer-content').textContent.toLowerCase();
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
            hasResults = true;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Hide empty categories
    categories.forEach(category => {
        const visibleItems = category.querySelectorAll('.faq-item[style="display: block;"]').length;
        const totalItems = category.querySelectorAll('.faq-item').length;
        
        if (visibleItems === 0 && searchTerm !== '') {
            category.style.display = 'none';
        } else {
            category.style.display = 'block';
        }
    });
    
    // Show no results message
    const noResults = document.getElementById('noResults');
    if (!hasResults && searchTerm !== '') {
        noResults.classList.add('show');
    } else {
        noResults.classList.remove('show');
    }
}
</script>

@endsection
