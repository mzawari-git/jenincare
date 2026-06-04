<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title_ar' => 'جهاز إزالة الشعر بالليزر ALMA Soprano – التقنية الأحدث والأكثر أماناً',
                'category' => 'articles',
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'جهاز إزالة الشعر ALMA Soprano | دليل شامل للتقنية والنتائج',
                'meta_description' => 'دليل متكامل عن جهاز ALMA Soprano لإزالة الشعر بالليزر: تقنية Trio Clustered Diode، نظام SHR، تقنية ICE Plus، والمقارنة بين الأطوال الموجية 755 و 810 و 1064 نانومتر.',
                'excerpt_ar' => 'دليل متكامل عن جهاز ALMA Soprano لإزالة الشعر بالليزر بتقنية Trio Clustered Diode الثلاثية، ونظام Super Hair Removal الأحدث عالمياً.',
                'content_ar' => $this->post1Content(),
            ],
            [
                'title_ar' => 'جهاز الليزر الكربوني الجزئي Fractional CO2 – الحل الأمثل لتجديد البشرة',
                'category' => 'articles',
                'is_featured' => true,
                'sort_order' => 2,
                'meta_title' => 'جهاز الليزر الكربوني Fractional CO2 | التجديد العميق للبشرة',
                'meta_description' => 'كل ما تحتاج معرفته عن جهاز الليزر الكربوني الجزئي Fractional CO2: آلية العمل، المناطق العلاجية، تقنية المسح 360 درجة، والمزايا مقارنة بالتقنيات الأخرى.',
                'excerpt_ar' => 'تعرف على جهاز الليزر الكربوني الجزئي Fractional CO2 وكيف يمنح بشرتك تجديداً عميقاً من خلال تحفيز الكولاجين الطبيعي.',
                'content_ar' => $this->post2Content(),
            ],
            [
                'title_ar' => 'جهاز ليزر Q-switched Nd:YAG – العلاج المتقدم لإزالة التاتو والتصبغات',
                'category' => 'articles',
                'is_featured' => false,
                'sort_order' => 3,
                'meta_title' => 'جهاز Q-switched Nd:YAG | إزالة التاتو والتصبغات الجلدية',
                'meta_description' => 'دليل شامل عن جهاز ليزر Q-switched Nd:YAG لإزالة التاتو والتصبغات: مبدأ النبضات فائقة القصر، الأطوال الموجية 1064nm و 532nm، وتقنية هوليوود بيل.',
                'excerpt_ar' => 'دليل شامل عن جهاز Q-switched Nd:YAG لإزالة التاتو والتصبغات الجلدية بتقنية النبضات فائقة القصر.',
                'content_ar' => $this->post3Content(),
            ],
            [
                'title_ar' => 'جهاز هيدروجين أوكسجين 18 في 1 – ثورة العناية الشاملة بالوجه',
                'category' => 'articles',
                'is_featured' => true,
                'sort_order' => 4,
                'meta_title' => 'جهاز هيدروجين أوكسجين 18 في 1 | العناية الشاملة بالبشرة',
                'meta_description' => 'اكتشفي جهاز هيدروجين أوكسجين 18 في 1 متعدد الوظائف للعناية بالوجه: 18 تقنية متكاملة تشمل التنظيف، التقشير، التغذية، الرفع، ومكافحة الشيخوخة.',
                'excerpt_ar' => 'جهاز هيدروجين أوكسجين 18 في 1 يقدم 18 وظيفة متكاملة للعناية بالوجه من التنظيف العميق إلى شد البشرة وتجديدها.',
                'content_ar' => $this->post4Content(),
            ],
            [
                'title_ar' => 'جهاز هايدرو 8 في 1 – دليلك الشامل للتقشير المائي وتنقية البشرة',
                'category' => 'articles',
                'is_featured' => false,
                'sort_order' => 5,
                'meta_title' => 'جهاز هايدرو 8 في 1 | التقشير المائي المتطور للبشرة',
                'meta_description' => 'دليل متكامل عن جهاز هايدرو 8 في 1 للتقشير المائي: Hydro-dermabrasion، قناع LED الضوئي، الترددات الراديوية، الموجات فوق الصوتية، وتقنية الأوكسجين.',
                'excerpt_ar' => 'دليل متكامل عن جهاز هايدرو 8 في 1 للتقشير المائي والعناية بالبشرة بتقنيات متطورة وبسعر اقتصادي.',
                'content_ar' => $this->post5Content(),
            ],
            [
                'title_ar' => 'مقارنة شاملة بين أجهزة العناية بالبشرة الاحترافية – أيها يناسبك؟',
                'category' => 'guides',
                'is_featured' => false,
                'sort_order' => 6,
                'meta_title' => 'مقارنة أجهزة العناية بالبشرة | دليل اختيار الجهاز المناسب',
                'meta_description' => 'مقارنة شاملة بين أجهزة الليزر والعناية بالبشرة الاحترافية: Soprano، CO2 Fractional، Nd:YAG، هيدروجين أوكسجين، وهايدرو فيشل. مقارنة الأسعار، الاستخدامات، والفعالية.',
                'excerpt_ar' => 'مقارنة شاملة بين أجهزة العناية بالبشرة الاحترافية لمساعدتك في اختيار الجهاز الأنسب لصالونك أو عيادتك.',
                'content_ar' => $this->post6Content(),
            ],
        ];

        foreach ($posts as $post) {
            $post['slug'] = $this->arabicSlug($post['title_ar']);
            $post['is_published'] = true;
            $post['created_at'] = now();
            $post['updated_at'] = now();

            if (!BlogPost::where('slug', $post['slug'])->exists()) {
                BlogPost::create($post);
            }
        }
    }

    private function arabicSlug(string $title): string
    {
        $slug = Str::slug($title, '-');
        return $slug ?: 'article-' . uniqid();
    }

    // ─── Article 1: ALMA Soprano ────────────────────────────────────
    private function post1Content(): string
    {
        return <<<'HTML'
<div class="blog-section">
    <h2><i class="fas fa-star"></i> مقدمة – لماذا يُعد Soprano الخيار الأول؟</h2>
    <p>يُعد جهاز <strong>ALMA Soprano</strong> لإزالة الشعر بالليزر أحد أكثر الأجهزة ثورية في عالم التجميل الطبي. تم تطويره بواسطة شركة Alma Lasers الإسرائيلية الرائدة، ويعتمد على تقنيات متطورة تجمع بين الفعالية العالية والأمان التام، مما يجعله مناسباً لجميع أنواع البشرة – بما في ذلك البشرة الداكنة والبشرة الحساسة – دون التسبب بأي ألم يُذكر.</p>
    <p>على عكس أجهزة الليزر التقليدية التي كانت تعاني من قيود كبيرة فيما يتعلق بلون البشرة وكثافة الشعر، يأتي Soprano بحل شامل يعتمد على مزيج فريد من ثلاثة أطوال موجية في آنٍ واحد.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-lightbulb"></i> معلومة أساسية</h4>
        <p class="mb-0">آلية عمل الليزر لإزالة الشعر تعتمد على مبدأ <span class="blog-highlight">التحلل الحراري الانتقائي (Selective Photothermolysis)</span>، حيث يستهدف الليزر صبغة الميلانين في بصيلة الشعر بدقة، مما يؤدي إلى تدميرها حرارياً دون التأثير على الأنسجة المحيطة.</p>
    </div>

    <h2><i class="fas fa-microchip"></i> تقنية Trio Clustered Diode – القوة الثلاثية</h2>
    <p>يمتاز جهاز Soprano Ice Platinum/Titanium بتقنية <strong>"العنقود الثلاثي للديود" (Trio Clustered Diode Technology)</strong>، وهي تقنية حصرية تجمع بين ثلاثة أطوال موجية في قبضة علاجية واحدة، مما يضمن استهداف جميع أنواع الشعر والبشرة:</p>

    <ul>
        <li><strong>الطول الموجي 755 نانومتر (Alexandrite):</strong> مثالي للبشرة الفاتحة والشعر الناعم والخفيف. يتميز بامتصاص عالٍ من صبغة الميلانين في "الانتفاخ" (Bulge) لجذع الشعرة، مما يحقق نتائج سريعة للشعر الزغبي.</li>
        <li><strong>الطول الموجي 810 نانومتر (Speed Diode):</strong> الطول الموجي المتوسط الذي يجمع بين الاختراق العميق والسلامة العالية. يستهدف منطقة "الجريب" (Follicle) بكفاءة متوسطة وهو مناسب لأغلب أنواع البشرة حتى الدرجة الرابعة حسب مقياس فيتزباتريك.</li>
        <li><strong>الطول الموجي 1064 نانومتر (Nd:YAG):</strong> الأكثر اختراقاً وأماناً للبشرة الداكنة جداً. يصل إلى أعماق أكبر متجاوزاً طبقة الميلانين السطحية ليصل مباشرة إلى منطقة "الحليمة" (Papilla) المغذية للشعر.</li>
    </ul>

    <div class="blog-warning-box">
        <h4><i class="fas fa-exclamation-triangle"></i> تنبيه مهم</h4>
        <p class="mb-0">على الرغم من أن الجهاز يُصنف ضمن أجهزة "الاستخدام المنزلي المتقدم"، إلا أنه يجب استخدامه تحت إشراف مختصين مدربين في مراكز التجميل المعتمدة لضمان أفضل النتائج وتجنب أي آثار جانبية غير مرغوبة.</p>
    </div>

    <h2><i class="fas fa-snowflake"></i> نظام SHR وتقنية ICE Plus – إزالة شعر بلا ألم</h2>
    <p>يُعد نظام <strong>إزالة الشعر فائق السرعة (Super Hair Removal - SHR)</strong> نقلة نوعية في عالم الليزر. على عكس الليزر التقليدي الذي يُصدر نبضة واحدة عالية الطاقة، يقوم SHR بإصدار نبضات متتالية منخفضة الطاقة بمعدل يصل إلى 10 نبضات في الثانية الواحدة، مع زيادة تدريجية في الطاقة وصولاً إلى المستوى العلاجي المطلوب.</p>
    <p>هذه الآلية الفريدة تُعطي الجلد وقتاً كافياً لتبديد الحرارة بين النبضات، مما يمنع الإحساس بالألم أو الحروق. كما أن <strong>تقنية التبريد ICE Plus</strong> المدمجة في رأس الجهاز تقوم بتبريد سطح الجلد إلى درجة حرارة تصل إلى -3 درجات مئوية أثناء الجلسة، مما يضاعف الشعور بالراحة ويحمي البشرة تماماً.</p>

    <h2><i class="fas fa-chart-bar"></i> المواصفات الفنية والمميزات الرئيسية</h2>
    <ul>
        <li><strong>عدد النبضات:</strong> 65,000 نبضة مضمونة (قابلة للتجديد)</li>
        <li><strong>سرعة العلاج:</strong> تغطية كامل الجسم في أقل من 40 دقيقة</li>
        <li><strong>ملاءمة البشرة:</strong> جميع أنواع البشرة من الدرجة 1 إلى 6</li>
        <li><strong>نظام التبريد:</strong> ICE Plus بدرجة حرارة -3 مئوية</li>
        <li><strong>شاشة تحكم:</strong> LCD تعمل باللمس بواجهة استخدام سهلة</li>
        <li><strong>الأطوال الموجية:</strong> 755nm / 810nm / 1064nm في قبضة واحدة</li>
    </ul>

    <div class="blog-info-box">
        <h4><i class="fas fa-check-circle"></i> مميزات إضافية</h4>
        <p class="mb-0">الجهاز مزود بنظام أمان متكامل يشمل مستشعر تلامس الجلد (Skin Contact Sensor) الذي يضمن عدم إطلاق النبضات إلا عند التلامس الكامل مع سطح البشرة، ونظام حماية من الحرارة الزائدة (Overheat Protection).</p>
    </div>
</div>
HTML;
    }

    // ─── Article 2: Fractional CO2 ──────────────────────────────────
    private function post2Content(): string
    {
        return <<<'HTML'
<div class="blog-section">
    <h2><i class="fas fa-magic"></i> ما هو الليزر الكربوني الجزئي Fractional CO2؟</h2>
    <p>يُعتبر جهاز <strong>الليزر الكربوني الجزئي (Fractional CO2 Laser)</strong> المعيار الذهبي في مجال تجديد البشرة وعلاج الندبات العميقة. يعمل هذا الجهاز المتطور بطول موجي يبلغ <strong>10,600 نانومتر</strong> ضمن طيف الأشعة تحت الحمراء البعيدة، ويتميز بدقة استثنائية في استهداف طبقات الجلد.</p>
    <p>تقوم آلية العمل على إرسال حزمة ليزرية دقيقة تُحدث آلاف "المناطق الحرارية الدقيقة" (Micro-Thermal Zones - MTZs) في الجلد، مما يحفز عملية تجديد طبيعية تشمل تنشيط الخلايا الليفية (Fibroblasts) لإنتاج الكولاجين والإيلاستين الجديدين.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-flask"></i> آلية العمل العلمية</h4>
        <p class="mb-0">يعتمد مبدأ Fractional CO2 على <span class="blog-highlight">تقسيم شعاع الليزر إلى حزم مجهرية</span> تخترق الجلد تاركةً جسوراً من الأنسجة السليمة بينها. هذه الجسور تُسرّع عملية الالتئام وتقلل فترة النقاهة بشكل كبير مقارنة بالليزر الكربوني التقليدي (الكامل).</p>
    </div>

    <h2><i class="fas fa-bullseye"></i> المناطق والاستخدامات العلاجية</h2>
    <p>يُعد Fractional CO2 من أكثر الأجهزة تنوعاً في الاستخدامات التجميلية والعلاجية:</p>
    <ul>
        <li><strong>علاج التجاعيد العميقة:</strong> يُستخدم لعلاج الخطوط التعبيرية حول الفم والعينين (خطوط العبوس والتجاعيد الجبهية)، حيث يُحفز إنتاج الكولاجين مما يعيد الامتلاء والمرونة للبشرة.</li>
        <li><strong>إزالة الندبات وآثار حب الشباب:</strong> يُعد العلاج الأكثر فعالية للندبات العميقة الضامرة (Atrophic Scars) وندبات حب الشباب الشديدة.</li>
        <li><strong>شد البشرة المترهلة:</strong> يساهم في شد الجلد المترهل في منطقة الوجه والرقبة من خلال تحفيز انكماش ألياف الكولاجين فورياً (Immediate Collagen Contraction).</li>
        <li><strong>توحيد لون البشرة:</strong> يُستخدم لإزالة التصبغات السطحية والعميقة وتجديد سطح البشرة بالكامل.</li>
    </ul>

    <div class="blog-warning-box">
        <h4><i class="fas fa-clock"></i> فترة النقاهة والتوقعات</h4>
        <p class="mb-0">تتراوح فترة النقاهة بعد الجلسة بين 5 إلى 7 أيام، يظهر خلالها احمرار وتقشر طبيعي للجلد. يجب الالتزام بتعليمات ما بعد الجلسة بدقة، وأهمها: تجنب التعرض المباشر لأشعة الشمس لمدة لا تقل عن أسبوعين، واستخدام واقي شمسي طبي بعامل حماية لا يقل عن SPF 50+.</p>
    </div>

    <h2><i class="fas fa-sync-alt"></i> تقنية المسح 360 درجة والتقشير المهبلي</h2>
    <p>تتميز الأجهزة الحديثة من Fractional CO2 بخاصية <strong>المسح الدائري 360 درجة</strong> التي تتيح تغطية كاملة للمنطقة المعالجة بحركة دورانية دقيقة. كما تتوفر ملحقات متخصصة مثل:</p>
    <ul>
        <li><strong>الماسح المهبلي (VVA Scanner):</strong> قبضة متخصصة لإجراء عمليات تضييق وتجديد المهبل (Vaginal Rejuvenation) بطريقة غير جراحية، وهو من أحدث تطبيقات الليزر في مجال الصحة النسائية التجميلية.</li>
        <li><strong>العدسات الجزئية المتعددة:</strong> تتيح تغيير نمط وكثافة النقاط الدقيقة حسب عمق المشكلة ونوع البشرة.</li>
    </ul>

    <h2><i class="fas fa-tachometer-alt"></i> المواصفات الفنية</h2>
    <ul>
        <li><strong>عدد النبضات:</strong> 55,000 نبضة مضمونة</li>
        <li><strong>الطول الموجي:</strong> 10,600 نانومتر (CO2)</li>
        <li><strong>قوة الخرج:</strong> قابلة للتعديل حتى 60 واط</li>
        <li><strong>وضع المسح:</strong> دائري 360 درجة مع إمكانية تعديل الكثافة والمساحة</li>
        <li><strong>شاشة العرض:</strong> LCD ملونة مع واجهة تحكم احترافية</li>
    </ul>

    <div class="blog-info-box">
        <h4><i class="fas fa-star"></i> الميزة التنافسية</h4>
        <p class="mb-0">بالمقارنة مع أجهزة التقشير الكيميائي والسنفرة الجلدية (Dermabrasion)، يوفر Fractional CO2 نتائج أعمق وأكثر استدامة مع فترة نقاهة أقصر نسبياً، كما أنه يُعد الخيار الأمثل للمشاكل الجلدية التي لا تستجيب للعلاجات السطحية.</p>
    </div>
</div>
HTML;
    }

    // ─── Article 3: Q-switched Nd:YAG ───────────────────────────────
    private function post3Content(): string
    {
        return <<<'HTML'
<div class="blog-section">
    <h2><i class="fas fa-bolt"></i> ما هو ليزر Q-switched Nd:YAG؟</h2>
    <p>عندما يتعلق الأمر بإزالة <strong>التاتو والوشم والتصبغات الجلدية العميقة</strong>، لا يوجد جهاز يضاهي فعالية ليزر Q-switched Nd:YAG. يعتمد هذا الجهاز على بلورة من <strong>الإيتريوم ألمنيوم غارنيت المشوبة بالنيوديميوم (Nd:YAG)</strong> كمصدر ليزري، مع تقنية "تبديل عامل الجودة" (Q-switching) التي تُنتج نبضات فائقة القصر والطاقة.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-atom"></i> المبدأ العلمي – النبضات فائقة القصر</h4>
        <p class="mb-0">تقوم تقنية <strong>Q-switching</strong> بتخزين الطاقة في وسط الليزر ثم إطلاقها دفعة واحدة في زمن متناهي القصر يُقاس بالنانوثانية (واحد من مليار من الثانية). هذه النبضة فائقة الطاقة تُسبب <span class="blog-highlight">تفتيتاً ميكانيكياً ضوئياً (Photoacoustic Effect)</span> لجزيئات الحبر والصبغة دون حرق الأنسجة المحيطة.</p>
    </div>

    <h2><i class="fas fa-wave-square"></i> الأطوال الموجية المزدوجة</h2>
    <p>يتميز الجهاز بقدرته على إصدار طولين موجيين أساسيين، لكل منهما استخداماته المتخصصة:</p>
    <ul>
        <li><strong>الطول الموجي 1064 نانومتر:</strong> يُستخدم لعلاج <span class="blog-highlight">التاتو الداكن (الأسود والأزرق الداكن)</span> والتصبغات العميقة مثل الكلف والوحمات الداكنة. يتميز باختراق عميق يصل إلى الأدمة دون التأثير على البشرة السطحية.</li>
        <li><strong>الطول الموجي 532 نانومتر:</strong> يُستخدم لعلاج <span class="blog-highlight">التاتو الملون (الأحمر، البرتقالي، الأصفر)</span> والتصبغات السطحية مثل النمش والبقع الشمسية. يتميز بامتصاص عالٍ من صبغة الميلانين السطحية.</li>
    </ul>

    <h2><i class="fas fa-spray-can"></i> تقنية Hollywood Peel – التقشير الكربوني الفاخر</h2>
    <p>أحد أكثر التطبيقات رواجاً لجهاز Q-switched Nd:YAG هو ما يُعرف بـ <strong>"هوليوود بيل" (Hollywood Peel)</strong> أو التقشير الكربوني. تعتمد هذه الجلسة على ثلاث خطوات متكاملة:</p>
    <ol>
        <li><strong>الخطوة الأولى:</strong> وضع طبقة رقيقة من الكربون السائل النانوي على الوجه بالكامل، حيث يتغلغل الكربون داخل المسامات ويلتصق بالخلايا الميتة والزيوت الزائدة.</li>
        <li><strong>الخطوة الثانية:</strong> تمرير نبضات الليزر 1064nm فوق طبقة الكربون، مما يؤدي إلى <span class="blog-highlight">تسخين وتبخير الكربون فورياً</span> مع إزالة الطبقة السطحية من الجلد الميت والشوائب.</li>
        <li><strong>الخطوة الثالثة:</strong> تطبيق سيروم مغذٍّ ومنعم للبشرة مع تدليك بارد لتلطيف الجلد وتحفيز امتصاص المواد الفعالة.</li>
    </ol>

    <div class="blog-info-box">
        <h4><i class="fas fa-gem"></i> نتائج فورية – بشرة نضرة من أول جلسة</h4>
        <p class="mb-0">يتميز Hollywood Peel بكونه آمناً تماماً ومناسباً لجميع أنواع البشرة. النتائج تظهر فوراً بعد الجلسة: بشرة مشرقة، مسامات أقل وضوحاً، توحيد فوري للون البشرة، وتقليل ملحوظ لإفراز الدهون. يُنصح بتكرار الجلسة كل 3 إلى 4 أسابيع للحصول على أفضل النتائج.</p>
    </div>

    <h2><i class="fas fa-tachometer-alt"></i> المواصفات الفنية</h2>
    <ul>
        <li><strong>عدد النبضات:</strong> 15,000 نبضة مضمونة</li>
        <li><strong>الأطوال الموجية:</strong> 1064nm + 532nm مدمجة</li>
        <li><strong>مدة النبضة:</strong> 5-10 نانوثانية (قصيرة جداً)</li>
        <li><strong>طاقة النبضة:</strong> قابلة للتعديل حسب نوع التصبغ والمنطقة</li>
        <li><strong>حجم بقعة الليزر:</strong> متغير من 1.5mm إلى 8mm</li>
    </ul>

    <div class="blog-warning-box">
        <h4><i class="fas fa-shield-alt"></i> احتياطات السلامة</h4>
        <p class="mb-0">يجب ارتداء نظارات واقية متخصصة للطول الموجي المستخدم أثناء التشغيل. لا يُستخدم الجهاز على البشرة المسفوعة حديثاً أو التي تعرضت لتقشير كيميائي خلال الأسبوعين الماضيين. يُمنع استخدامه للحوامل والمرضعات.</p>
    </div>
</div>
HTML;
    }

    // ─── Article 4: Hydrogen Oxygen 18-in-1 ─────────────────────────
    private function post4Content(): string
    {
        return <<<'HTML'
<div class="blog-section">
    <h2><i class="fas fa-wind"></i> ما هو جهاز هيدروجين أوكسجين 18 في 1؟</h2>
    <p>يُعد جهاز <strong>هيدروجين أوكسجين 18 في 1 (Hydrogen Oxygen 18-in-1)</strong> محطة متكاملة للعناية الفائقة بالوجه، حيث يجمع بين 18 تقنية تجميلية متطورة في جهاز واحد. صُمم هذا الجهاز خصيصاً لمراكز التجميل والصالونات المتطورة التي تسعى لتقديم خدمات Facial احترافية تنافس أعلى المعايير العالمية.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-crown"></i> لماذا 18 وظيفة في جهاز واحد؟</h4>
        <p class="mb-0">بدلاً من الاستثمار في 6 أو 7 أجهزة منفصلة، يوفر جهاز هيدروجين أوكسجين منصة شاملة تُغطي كافة احتياجات العناية بالوجه: من التنظيف العميق إلى الترطيب المكثف، ومن التقشير إلى شد البشرة، مروراً بالتغذية بالأكسجين والهيدروجين النشط.</p>
    </div>

    <h2><i class="fas fa-list-ol"></i> التقنيات الـ 18 المتكاملة</h2>

    <h3><i class="fas fa-tint"></i> 1. التنظيف العميق وتقشير الجلد المائي (Hydro-dermabrasion)</h3>
    <p>تعمل هذه التقنية على تنظيف المسامات بعمق وإزالة الخلايا الميتة باستخدام محاليل مائية خاصة (مثل حمض الساليسيليك وحمض الجليكوليك) مع ضغط سلبي مُتحكم به، مما يمنح البشرة نعومة ونقاء فوريين.</p>

    <h3><i class="fas fa-atom"></i> 2. الترددات الراديوية (RF)</h3>
    <p>تقوم موجات الترددات الراديوية بتسخين طبقة الأدمة العميقة بشكل آمن ومُتحكم به، مما يحفز إنتاج الكولاجين الجديد ويؤدي إلى شد البشرة وتقليل التجاعيد والخطوط الدقيقة.</p>

    <h3><i class="fas fa-broadcast-tower"></i> 3. الموجات فوق الصوتية (Ultrasound)</h3>
    <p>تستخدم الموجات فوق الصوتية (بتردد يتراوح بين 1-3 ميجاهرتز) لتحفيز الدورة الدموية الدقيقة وتعزيز نفاذية المواد الفعالة إلى الطبقات العميقة من الجلد.</p>

    <h3><i class="fas fa-lightbulb"></i> 4. قناع الضوء LED</h3>
    <p>يحتوي الجهاز على قناع LED متكامل بأطوال موجية متعددة: الضوء <span style="color:#3B82F6;">الأزرق (415nm)</span> لعلاج حب الشباب والبكتيريا، <span style="color:#EF4444;">الأحمر (633nm)</span> لتحفيز الكولاجين ومكافحة الشيخوخة، و<span style="color:#F59E0B;">الأصفر (590nm)</span> لتفتيح البشرة وتوحيد لونها.</p>

    <h3><i class="fas fa-flask"></i> 5. الأكسجين النشط (Oxygeneo)</h3>
    <p>تُعد هذه من أبرز تقنيات الجهاز، حيث تعتمد على <strong>"تأثير بور" (Bohr Effect)</strong> الكيميائي الحيوي لإيصال الأكسجين إلى خلايا البشرة. تُنتج كبسولة خاصة تفاعلاً طبيعياً على سطح الجلد:</p>
    <div style="text-align:center;padding:15px;background:#FFFBEB;border-radius:12px;margin:15px 0;font-size:1.1rem;direction:ltr;">
        HbO<sub>2</sub> + H<sup>+</sup> + CO<sub>2</sub> ⇌ Hb-CO<sub>2</sub> + H<sup>+</sup> + O<sub>2</sub>
    </div>
    <p>يؤدي هذا التفاعل إلى إطلاق فقاعات أكسجين دقيقة على سطح الجلد تتغلغل إلى الطبقات العميقة، مما يُحفز تجديد الخلايا ويمنح البشرة إشراقة استثنائية.</p>

    <h3><i class="fas fa-water"></i> 6. الرش المائي H2O2 والترطيب العميق</h3>
    <p>تقوم فوهة خاصة برش رذاذ مائي نانوي مخلوط ببيروكسيد الهيدروجين المخفف (بتركيز آمن)، مما يوفر ترطيباً عميقاً وتعقيماً لطيفاً للمسامات دون التسبب بأي جفاف أو تهيج.</p>

    <h3><i class="fas fa-syringe"></i> 7. الإبر الدقيقة والميزوثيرابي بدون إبر</h3>
    <p>تقنية متطورة لنفاذ المواد الفعالة (مثل الفيتامينات، حمض الهيالورونيك، والببتيدات) إلى أعماق الجلد دون الحاجة إلى وخز بالإبر، مما يوفر نتائج تغذية عميقة دون ألم أو فترة نقاهة.</p>

    <h3><i class="fas fa-icicles"></i> 8. التبريد والشد البارد (Cryotherapy)</h3>
    <p>رأس تبريد خاص بدرجة حرارة منخفضة (تصل إلى 5 درجات مئوية) يُستخدم لتلطيف البشرة بعد الجلسات العميقة، تقليل الانتفاخ والاحمرار، وإغلاق المسامات بعد التنظيف.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-heart"></i> وظائف إضافية شاملة</h4>
        <p class="mb-0">بالإضافة إلى التقنيات المذكورة، يحتوي الجهاز على: التنظيف بالفرشاة الدوارة، جهاز البخار (Vaporizer)، الشفط الكهربائي (Vacuum Pen) لتنظيف المسامات العميقة، خاصية التدليك الاهتزازي، وأداة التسخين الحراري لفتح المسامات قبل العلاج.</p>
    </div>

    <h2><i class="fas fa-tachometer-alt"></i> المواصفات الفنية</h2>
    <ul>
        <li><strong>عدد التقنيات:</strong> 18 وظيفة متكاملة في جهاز واحد</li>
        <li><strong>عدد النبضات:</strong> 12,000 نبضة مضمونة</li>
        <li><strong>شاشة التحكم:</strong> LCD كبيرة بالألوان الكاملة مع واجهة استخدام باللمس</li>
        <li><strong>تصميم القبضات:</strong> 8 قبضات مختلفة قابلة للتبديل السريع</li>
        <li><strong>النظام الكهربائي:</strong> 220 فولت – 50/60 هرتز</li>
    </ul>
</div>
HTML;
    }

    // ─── Article 5: Hydro 8-in-1 ────────────────────────────────────
    private function post5Content(): string
    {
        return <<<'HTML'
<div class="blog-section">
    <h2><i class="fas fa-water"></i> جهاز هايدرو 8 في 1 – التقشير المائي المتكامل</h2>
    <p>في عالم العناية بالبشرة، يُعد التقشير المائي (Hydro-dermabrasion) أحد أكثر الإجراءات طلباً في الصالونات والعيادات. جهاز <strong>هايدرو 8 في 1 (Hydro 8-in-1)</strong> يقدم هذه التقنية وغيرها من الخدمات المتطورة في جهاز واحد وبسعر اقتصادي يناسب جميع الفئات.</p>
    <p>بسعر يبدأ من <strong>2,500 شيكل فقط</strong>، يُعد هذا الجهاز استثماراً ذكياً لأصحاب الصالونات الناشئة والمتوسطة الذين يرغبون في تقديم خدمات Facial احترافية دون تكاليف باهظة.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-tag"></i> أفضل قيمة مقابل السعر</h4>
        <p class="mb-0">يجمع جهاز هايدرو 8 في 1 بين التقنيات الأساسية الأكثر طلباً في سوق العناية بالبشرة، مما يجعله <span class="blog-highlight">الخيار الاقتصادي الأمثل</span> للصالونات التي تبدأ بتوسيع خدماتها التجميلية دون الحاجة لاستثمارات ضخمة في البداية.</p>
    </div>

    <h2><i class="fas fa-cogs"></i> التقنيات الثمانية المتكاملة</h2>

    <h3><i class="fas fa-shower"></i> 1. التقشير المائي (Hydro-dermabrasion)</h3>
    <p>التقنية الأساسية في الجهاز. تعمل قبضة التقشير المائي على إزالة الطبقة السطحية من الخلايا الميتة والشوائب باستخدام تيار مائي مضغوط مخلوط بمحاليل مغذية (سيروم). الضغط السلبي (الشفط) يقوم بسحب الشوائب فورياً، مما يترك البشرة نظيفة وناعمة ومتألقة.</p>

    <h3><i class="fas fa-syringe"></i> 2. النفاذ اللابرري (Electroporation)</h3>
    <p>تقنية النفاذ الكهربائي تسمح للمواد الفعالة (الفيتامينات، السيروم، حمض الهيالورونيك) باختراق حاجز البشرة دون استخدام الإبر. تعمل النبضات الكهربائية الدقيقة على فتح قنوات مؤقتة في غشاء الخلية لفترة قصيرة، مما يُضاعف فعالية المواد المغذية.</p>

    <h3><i class="fas fa-broadcast-tower"></i> 3. الموجات فوق الصوتية (Ultrasound 1MHz)</h3>
    <p>تقوم ذبذبات الموجات فوق الصوتية بتردد 1 ميجاهرتز بتحفيز الدورة الدموية الدقيقة في طبقات الجلد العميقة، مما يُحسن تغذية الخلايا ويساعد على امتصاص المواد الفعالة بشكل أفضل.</p>

    <h3><i class="fas fa-lightbulb"></i> 4. القناع الضوئي LED Therapy</h3>
    <p>قناع LED متكامل يحتوي على مصابيح متعددة الأطياف: الضوء الأحمر لتحفيز الكولاجين، الأزرق لمكافحة البكتيريا المسببة لحب الشباب، والأصفر لتفتيح البشرة وتوحيد لونها. يُستخدم القناع في نهاية الجلسة لمدة 15-20 دقيقة.</p>

    <h3><i class="fas fa-atom"></i> 5. الترددات الراديوية (RF)</h3>
    <p>تقوم أقطاب RF بتسخين طبقة الأدمة بطريقة آمنة، مما يُحفز إنتاج الكولاجين والإيلاستين. النتيجة: بشرة مشدودة، تقليل التجاعيد، وتحسين ملمس الجلد بشكل ملحوظ.</p>

    <h3><i class="fas fa-wind"></i> 6. رش الأكسجين (Oxygen Spray)</h3>
    <p>فوهة خاصة تقوم برش الأكسجين النقي على البشرة مما يُنعش الخلايا ويساعد على تهدئة البشرة بعد جلسات التقشير والعلاجات العميقة.</p>

    <h3><i class="fas fa-icicles"></i> 7. المطرقة الباردة (Cold Hammer)</h3>
    <p>رأس تبريد يُستخدم لإغلاق المسامات بعد التنظيف العميق، تهدئة البشرة بعد التقشير، وتقليل الانتفاخ والاحمرار. درجة الحرارة المنخفضة (5-10 مئوية) تُعطي إحساساً منعشاً وفورياً.</p>

    <h3><i class="fas fa-hand-sparkles"></i> 8. التدليك والشد اليدوي</h3>
    <p>ملحقات خاصة للتدليك بتقنية الاهتزازات الدقيقة التي تساعد على تنشيط الدورة الدموية وتحسين امتصاص الكريمات المغذية في نهاية الجلسة.</p>

    <div class="blog-warning-box">
        <h4><i class="fas fa-clipboard-check"></i> توصيات الاستخدام</h4>
        <p class="mb-0">يُنصح باستخدام الجهاز بواسطة مختص/ة مدرب/ة على تقنيات العناية بالبشرة. يجب تنظيف القبضات والفوهات بعد كل استخدام. يُمنع استخدام الجهاز على البشرة المجروحة أو المصابة بالتهابات جلدية نشطة، أو خلال فترة الحمل دون استشارة طبية.</p>
    </div>

    <h2><i class="fas fa-balance-scale"></i> مقارنة سريعة: هايدرو 8 في 1 مقابل هيدروجين أوكسجين 18 في 1</h2>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:.9rem;">
        <tr style="background:#FEF3C7;">
            <th style="padding:12px;border:1px solid #FDE68A;text-align:right;">الميزة</th>
            <th style="padding:12px;border:1px solid #FDE68A;text-align:center;">هايدرو 8 في 1</th>
            <th style="padding:12px;border:1px solid #FDE68A;text-align:center;">هيدروجين أوكسجين 18 في 1</th>
        </tr>
        <tr>
            <td style="padding:10px;border:1px solid #FEF3C7;">عدد الوظائف</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">8</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">18</td>
        </tr>
        <tr style="background:#FFFBEB;">
            <td style="padding:10px;border:1px solid #FEF3C7;">السعر التقريبي</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">2,500 شيكل</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">12,000 شيكل</td>
        </tr>
        <tr>
            <td style="padding:10px;border:1px solid #FEF3C7;">الجمهور المستهدف</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">صالونات ناشئة ومتوسطة</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">مراكز تجميل متقدمة</td>
        </tr>
        <tr style="background:#FFFBEB;">
            <td style="padding:10px;border:1px solid #FEF3C7;">تقنية الأكسجين النشط</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">رش أوكسجين بسيط</td>
            <td style="padding:10px;border:1px solid #FEF3C7;text-align:center;">Oxygeneo بتأثير Bohr</td>
        </tr>
    </table>

    <div class="blog-info-box">
        <h4><i class="fas fa-lightbulb"></i> التوصية النهائية</h4>
        <p class="mb-0">إذا كنتِ تبدأين مشواركِ في مجال العناية بالبشرة وتبحثين عن جهاز موثوق يغطي الخدمات الأساسية بسعر اقتصادي، فإن <strong>هايدرو 8 في 1</strong> هو خيارك الأمثل. أما إذا كنتِ تديرين مركز تجميل متكاملاً وتبحثين عن أعلى مستوى من الخدمات، فإن <strong>هيدروجين أوكسجين 18 في 1</strong> هو المناسب.</p>
    </div>
</div>
HTML;
    }

    // ─── Article 6: Complete Device Comparison ──────────────────────
    private function post6Content(): string
    {
        $url = config('app.url');
        return <<<HTML
<div class="blog-section">
    <h2><i class="fas fa-balance-scale"></i> لماذا تحتاج إلى دليل مقارنة شامل؟</h2>
    <p>مع تعدد أجهزة العناية بالبشرة والليزر في السوق، قد يكون من الصعب على أصحاب الصالونات والعيادات تحديد الجهاز الأنسب لاحتياجاتهم. في هذا الدليل الشامل، نقدم مقارنة موضوعية بين <strong>أفضل 5 أجهزة</strong> متوفرة لدى شركة جنين للتجميل، لمساعدتك في اتخاذ القرار الصحيح.</p>

    <div class="blog-info-box">
        <h4><i class="fas fa-clipboard-list"></i> معايير المقارنة</h4>
        <p class="mb-0">قمنا بمقارنة الأجهزة بناءً على 5 معايير أساسية: <span class="blog-highlight">السعر، عدد النبضات، تنوع الاستخدامات، سرعة العلاج، وملاءمة أنواع البشرة المختلفة</span>.</p>
    </div>

    <h2><i class="fas fa-table"></i> جدول المقارنة الشامل</h2>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:.85rem;">
            <tr style="background:linear-gradient(135deg,#F59E0B,#D97706);color:white;">
                <th style="padding:12px;border:1px solid #D97706;text-align:right;">المعيار</th>
                <th style="padding:12px;border:1px solid #D97706;text-align:center;">ALMA Soprano</th>
                <th style="padding:12px;border:1px solid #D97706;text-align:center;">CO2 Fractional</th>
                <th style="padding:12px;border:1px solid #D97706;text-align:center;">Nd:YAG Q-Switched</th>
                <th style="padding:12px;border:1px solid #D97706;text-align:center;">هيدروجين 18-1</th>
                <th style="padding:12px;border:1px solid #D97706;text-align:center;">هايدرو 8-1</th>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>السعر التقريبي</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">65,000 شيكل</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">55,000 شيكل</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">15,000 شيكل</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">12,000 شيكل</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">2,500 شيكل</td>
            </tr>
            <tr style="background:#FFFBEB;">
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>عدد النبضات المضمونة</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">65,000</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">55,000</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">15,000</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">12,000</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">-</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>الاستخدام الأساسي</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">إزالة الشعر</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">تجديد البشرة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">إزالة التاتو والتصبغات</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">عناية شاملة بالوجه</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">تقشير وعناية اقتصادية</td>
            </tr>
            <tr style="background:#FFFBEB;">
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>سرعة الجلسة</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">جسم كامل: 40 دقيقة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">وجه كامل: 30-45 دقيقة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">حسب المساحة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">وجه: 60 دقيقة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">وجه: 45 دقيقة</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>ملاءمة البشرة</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">جميع أنواع البشرة 1-6</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">البشرة الفاتحة والمتوسطة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">جميع أنواع البشرة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">جميع أنواع البشرة</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">جميع أنواع البشرة</td>
            </tr>
            <tr style="background:#FFFBEB;">
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>فترة النقاهة</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">لا توجد</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">5-7 أيام</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">1-3 أيام</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">لا توجد</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">لا توجد</td>
            </tr>
            <tr>
                <td style="padding:10px;border:1px solid #FDE68A;"><strong>مستوى الخبرة المطلوب</strong></td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">متقدم</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">متقدم جداً</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">متوسط</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">متوسط</td>
                <td style="padding:10px;border:1px solid #FDE68A;text-align:center;">مبتدئ – متوسط</td>
            </tr>
        </table>
    </div>

    <h2><i class="fas fa-clipboard-check"></i> توصيات حسب نوع النشاط</h2>

    <div class="blog-info-box">
        <h4><i class="fas fa-building"></i> لمراكز التجميل الكبرى والعيادات المتخصصة</h4>
        <p class="mb-0">نوصي بالاستثمار في <strong>ALMA Soprano</strong> لإزالة الشعر كخدمة أساسية ذات عائد مرتفع، و<strong>Fractional CO2</strong> لعلاج الندبات والتجاعيد، مع <strong>Q-switched Nd:YAG</strong> لتغطية خدمات إزالة التاتو والتصبغات. هذا المزيج يمنحك تغطية شاملة لجميع احتياجات العملاء.</p>
    </div>

    <div class="blog-info-box">
        <h4><i class="fas fa-store-alt"></i> للصالونات المتوسطة والنامية</h4>
        <p class="mb-0">ابدئي بـ <strong>هيدروجين أوكسجين 18 في 1</strong> كجهاز Facial متكامل، ثم أضيفي <strong>Q-switched Nd:YAG</strong> لتوسيع خدماتك لتشمل إزالة التاتو و Hollywood Peel. هذا المزيج يمنحك تغطية ممتازة باستثمار معقول.</p>
    </div>

    <div class="blog-info-box">
        <h4><i class="fas fa-home"></i> للصالونات الناشئة والمشاريع الصغيرة</h4>
        <p class="mb-0">جهاز <strong>هايدرو 8 في 1</strong> هو نقطة البداية المثالية. بسعر اقتصادي ومجموعة متكاملة من الوظائف الأساسية، يمكنكِ البدء بتقديم خدمات Facial احترافية فوراً، ثم التوسع تدريجياً بإضافة أجهزة أكثر تقدماً مع نمو عملك.</p>
    </div>

    <h2><i class="fas fa-star"></i> الخلاصة النهائية</h2>
    <p>لا يوجد جهاز واحد "أفضل" للجميع – القرار يعتمد على احتياجاتك الخاصة، ميزانيتك، ونوع العملاء الذين تستهدفينهم. في <strong>شركة جنين للتجميل</strong>، نقدم لك جميع هذه الأجهزة مع ضمان شامل ودعم فني مستمر، لضمان نجاح استثمارك في عالم التجميل.</p>

    <div style="background:linear-gradient(135deg,#D97706,#F59E0B);color:#fff;padding:30px;border-radius:16px;text-align:center;margin-top:30px;">
        <h3 style="font-size:1.4rem;font-weight:700;margin-bottom:10px;color:#fff;">هل تحتاج إلى استشارة شخصية؟</h3>
        <p style="margin-bottom:20px;">فريقنا المتخصص جاهز لمساعدتك في اختيار الجهاز الأنسب لصالونك أو عيادتك</p>
        <a href="{$url}/contact" style="background:#fff;color:#D97706;padding:12px 35px;border-radius:30px;font-weight:600;text-decoration:none;display:inline-block;">تواصل معنا الآن</a>
    </div>
</div>
HTML;
    }
}
