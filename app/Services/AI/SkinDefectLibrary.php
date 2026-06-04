<?php

namespace App\Services\AI;

class SkinDefectLibrary
{
    private const CONDITIONS = [
        // ===================== ACNE & BLEMISH =====================
        'acne_comedonal' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب الدهني (رؤوس بيضاء/سوداء)',
            'name_en' => 'Comedonal Acne (Whiteheads/Blackheads)',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['salicylic_acid', 'niacinamide', 'retinol'],
            'product_categories' => ['cleanser', 'toner', 'exfoliator'],
            'tip_ar' => 'استخدم غسول يحتوي على حمض الساليسيليك مرتين يومياً',
            'tip_en' => 'Use salicylic acid cleanser twice daily',
        ],
        'acne_inflammatory' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب الالتهابي (حطاطات/بثور)',
            'name_en' => 'Inflammatory Acne (Papules/Pustules)',
            'severity_range' => [3, 8], 'requires_medical' => true,
            'ingredients' => ['benzoyl_peroxide', 'salicylic_acid', 'azelaic_acid'],
            'product_categories' => ['treatment', 'spot_treatment', 'cleanser'],
            'tip_ar' => 'لا تعصر البثور لتجنب الالتهابات والندوب',
            'tip_en' => 'Do not pop pimples to avoid scarring',
        ],
        'acne_nodulocystic' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب العقدي والكيسي',
            'name_en' => 'Nodulocystic Acne',
            'severity_range' => [7, 10], 'requires_medical' => true,
            'ingredients' => ['isotretinoin', 'antibiotics'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'هذا النوع يحتاج علاجاً طبياً فورياً',
            'tip_en' => 'This type requires immediate medical treatment',
        ],
        'acne_conglobata' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب المتكتل',
            'name_en' => 'Acne Conglobata',
            'severity_range' => [8, 10], 'requires_medical' => true,
            'ingredients' => ['isotretinoin', 'systemic_corticosteroids'],
            'product_categories' => ['prescription', 'treatment'],
            'tip_ar' => 'حالة شديدة تتطلب تدخلاً طبياً فورياً',
            'tip_en' => 'Severe condition requiring immediate medical intervention',
        ],
        'acne_mechanica' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب الميكانيكي',
            'name_en' => 'Acne Mechanica',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['salicylic_acid', 'niacinamide'],
            'product_categories' => ['cleanser', 'treatment'],
            'tip_ar' => 'ناتج عن الاحتكاك أو الضغط على الجلد',
            'tip_en' => 'Caused by friction or pressure on the skin',
        ],
        'acne_cosmetica' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب التجميلي',
            'name_en' => 'Acne Cosmetica',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => ['niacinamide', 'salicylic_acid'],
            'product_categories' => ['cleanser', 'toner'],
            'tip_ar' => 'استخدم منتجات غير مسددة للمسام',
            'tip_en' => 'Use non-comedogenic products',
        ],
        'acne_fungal' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne',
            'name_ar' => 'حب الشباب الفطري (التهاب الجريبات الملاسيزي)',
            'name_en' => 'Fungal Acne (Malassezia Folliculitis)',
            'severity_range' => [2, 6], 'requires_medical' => true,
            'ingredients' => ['ketoconazole', 'selenium_sulfide', 'zinc_pyrithione'],
            'product_categories' => ['antifungal', 'treatment'],
            'tip_ar' => 'يحتاج علاجاً مضاداً للفطريات وليس مضادات حيوية',
            'tip_en' => 'Requires antifungal treatment, not antibiotics',
        ],
        'acne_scar_icepick' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne_scarring',
            'name_ar' => 'ندبات حب الشباب (نوع المخرز)',
            'name_en' => 'Ice Pick Acne Scars',
            'severity_range' => [3, 8], 'requires_medical' => true,
            'ingredients' => ['retinoids', 'vitamin_c'],
            'product_categories' => ['treatment', 'serum'],
            'tip_ar' => 'هذه الندبات العميقة تحتاج علاجاً احترافياً مثل التقشير الكيميائي العميق أو الليزر',
            'tip_en' => 'Deep scars requiring professional treatment like deep chemical peels or laser',
        ],
        'acne_scar_boxcar' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne_scarring',
            'name_ar' => 'ندبات حب الشباب (نوع الصندوق)',
            'name_en' => 'Boxcar Acne Scars',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['tretinoin', 'vitamin_c', 'silicone'],
            'product_categories' => ['treatment', 'serum'],
            'tip_ar' => 'ندبات عريضة ذات حواف حادة تحتاج علاجاً بالليزر أو التقشير الكيميائي',
            'tip_en' => 'Broad scars with sharp edges need laser or chemical peel treatment',
        ],
        'acne_scar_rolling' => [
            'category' => 'acne_blemish', 'subcategory' => 'acne_scarring',
            'name_ar' => 'ندبات حب الشباب (نوع التموج)',
            'name_en' => 'Rolling Acne Scars',
            'severity_range' => [2, 6], 'requires_medical' => true,
            'ingredients' => ['retinoids', 'vitamin_c', 'peptides'],
            'product_categories' => ['treatment', 'serum'],
            'tip_ar' => 'ندبات متموجة تنتج عن تليفات تحت الجلد',
            'tip_en' => 'Undulating scars caused by subdermal fibrosis',
        ],
        'post_inflammatory_hyperpigmentation' => [
            'category' => 'acne_blemish', 'subcategory' => 'post_acne',
            'name_ar' => 'فرط التصبغ التالي للالتهاب',
            'name_en' => 'Post-Inflammatory Hyperpigmentation (PIH)',
            'severity_range' => [1, 6], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'niacinamide', 'kojic_acid', 'azelaic_acid'],
            'product_categories' => ['serum', 'treatment', 'sunscreen'],
            'tip_ar' => 'استخدم سيروم فيتامين سي يومياً مع واقي شمس قوي',
            'tip_en' => 'Use vitamin C serum daily with strong sunscreen',
        ],
        'post_inflammatory_erythema' => [
            'category' => 'acne_blemish', 'subcategory' => 'post_acne',
            'name_ar' => 'الاحمرار التالي للالتهاب',
            'name_en' => 'Post-Inflammatory Erythema (PIE)',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['niacinamide', 'azelaic_acid', 'centella_asiatica'],
            'product_categories' => ['treatment', 'moisturizer'],
            'tip_ar' => 'بقع حمراء ناتجة عن التهاب سابق، استخدم منتجات مهدئة',
            'tip_en' => 'Red marks from prior inflammation, use soothing products',
        ],
        'keratosis_pilaris' => [
            'category' => 'acne_blemish', 'subcategory' => 'texture',
            'name_ar' => 'التقرن الشعري',
            'name_en' => 'Keratosis Pilaris',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['lactic_acid', 'salicylic_acid', 'urea', 'ammonium_lactate'],
            'product_categories' => ['exfoliator', 'moisturizer', 'treatment'],
            'tip_ar' => 'استخدم مقشر كيميائي يحتوي على حمض اللاكتيك بانتظام',
            'tip_en' => 'Use chemical exfoliant with lactic acid regularly',
        ],
        'perioral_dermatitis' => [
            'category' => 'acne_blemish', 'subcategory' => 'dermatitis',
            'name_ar' => 'التهاب الجلد حول الفم',
            'name_en' => 'Perioral Dermatitis',
            'severity_range' => [2, 6], 'requires_medical' => true,
            'ingredients' => ['metronidazole', 'azelaic_acid', 'ivermectin'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'توقف عن استخدام منتجات العناية بالفم القوية واستشر طبيباً',
            'tip_en' => 'Stop using potent skincare products around mouth and consult a doctor',
        ],

        // ===================== PIGMENTATION =====================
        'melasma_epidermal' => [
            'category' => 'pigmentation', 'subcategory' => 'facial_pigmentation',
            'name_ar' => 'الكلف السطحي (البشرة)',
            'name_en' => 'Epidermal Melasma',
            'severity_range' => [2, 7], 'requires_medical' => true,
            'ingredients' => ['hydroquinone', 'tretinoin', 'kojic_acid', 'tranexamic_acid'],
            'product_categories' => ['treatment', 'sunscreen', 'serum'],
            'tip_ar' => 'الكلف السطحي يستجيب للعلاجات الموضعية مع الوقاية الصارمة من الشمس',
            'tip_en' => 'Epidermal melasma responds to topical treatments with strict sun protection',
        ],
        'melasma_dermal' => [
            'category' => 'pigmentation', 'subcategory' => 'facial_pigmentation',
            'name_ar' => 'الكلف العميق (الأدمة)',
            'name_en' => 'Dermal Melasma',
            'severity_range' => [5, 9], 'requires_medical' => true,
            'ingredients' => ['tranexamic_acid', 'laser_treatment'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'الكلف العميق أصعب في العلاج وقد يحتاج جلسات ليزر',
            'tip_en' => 'Dermal melasma is harder to treat and may need laser sessions',
        ],
        'melasma_mixed' => [
            'category' => 'pigmentation', 'subcategory' => 'facial_pigmentation',
            'name_ar' => 'الكلف المختلط',
            'name_en' => 'Mixed Melasma',
            'severity_range' => [4, 8], 'requires_medical' => true,
            'ingredients' => ['hydroquinone', 'tretinoin', 'tranexamic_acid'],
            'product_categories' => ['treatment', 'sunscreen', 'serum'],
            'tip_ar' => 'الكلف المختلط يحتاج علاجاً شاملاً يجمع بين الموضعي والليزر',
            'tip_en' => 'Mixed melasma needs comprehensive treatment combining topical and laser',
        ],
        'solar_lentigo' => [
            'category' => 'pigmentation', 'subcategory' => 'age_related',
            'name_ar' => 'النمش الشمسي (بقع الشيخوخة)',
            'name_en' => 'Solar Lentigo (Age Spots)',
            'severity_range' => [2, 5], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'retinoids', 'kojic_acid', 'licorice_extract'],
            'product_categories' => ['serum', 'treatment', 'sunscreen'],
            'tip_ar' => 'بقع بنية ناتجة عن التعرض للشمس، استخدم واقي شمس يومياً',
            'tip_en' => 'Brown spots from sun exposure, use daily sunscreen',
        ],
        'ephelides' => [
            'category' => 'pigmentation', 'subcategory' => 'genetic',
            'name_ar' => 'النمش العادي',
            'name_en' => 'Freckles (Ephelides)',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'sunscreen', 'niacinamide'],
            'product_categories' => ['sunscreen', 'serum'],
            'tip_ar' => 'النمش طبيعي وراثي، استخدم واقي شمس يومياً',
            'tip_en' => 'Freckles are natural and genetic, use daily sunscreen',
        ],
        'vitiligo' => [
            'category' => 'pigmentation', 'subcategory' => 'depigmentation',
            'name_ar' => 'البهاق',
            'name_en' => 'Vitiligo',
            'severity_range' => [1, 8], 'requires_medical' => true,
            'ingredients' => ['tacrolimus', 'corticosteroids', 'excimer_laser'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'البهاق حالة مناعية تحتاج متابعة طبية واستخدام واقي شمس',
            'tip_en' => 'Vitiligo is an autoimmune condition requiring medical follow-up and sunscreen',
        ],
        'chloasma' => [
            'category' => 'pigmentation', 'subcategory' => 'hormonal',
            'name_ar' => 'الكلف الهرموني',
            'name_en' => 'Chloasma',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['hydroquinone', 'tretinoin', 'tranexamic_acid'],
            'product_categories' => ['treatment', 'sunscreen'],
            'tip_ar' => 'مرتبط بالتغيرات الهرمونية، يحتاج علاجاً تحت إشراف طبي',
            'tip_en' => 'Related to hormonal changes, needs medical supervision',
        ],
        'horis_nevus' => [
            'category' => 'pigmentation', 'subcategory' => 'facial_pigmentation',
            'name_ar' => 'وحمة هوري',
            'name_en' => "Hori's Nevus",
            'severity_range' => [3, 6], 'requires_medical' => true,
            'ingredients' => ['laser_treatment', 'q_switched_laser'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'بقع زرقاء-رمادية ثنائية الجانب، تحتاج علاجاً بالليزر',
            'tip_en' => 'Bilateral blue-gray patches needing laser treatment',
        ],
        'nevus_of_ota' => [
            'category' => 'pigmentation', 'subcategory' => 'facial_pigmentation',
            'name_ar' => 'وحمة أوتا',
            'name_en' => "Nevus of Ota",
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['q_switched_laser', 'laser_treatment'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'تصبغ أزرق-رمادي في منطقة العصب مثلث التوائم',
            'tip_en' => 'Blue-gray pigmentation in trigeminal nerve area',
        ],
        'drug_induced_pigmentation' => [
            'category' => 'pigmentation', 'subcategory' => 'induced',
            'name_ar' => 'فرط التصبغ الدوائي',
            'name_en' => 'Drug-Induced Hyperpigmentation',
            'severity_range' => [2, 6], 'requires_medical' => true,
            'ingredients' => ['hydroquinone', 'retinoids'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'قد يكون ناتجاً عن أدوية معينة، استشر طبيبك',
            'tip_en' => 'May be caused by certain medications, consult your doctor',
        ],
        'poikiloderma_of_civatte' => [
            'category' => 'pigmentation', 'subcategory' => 'vascular_pigmentation',
            'name_ar' => 'تجلد ريشي',
            'name_en' => "Poikiloderma of Civatte",
            'severity_range' => [2, 5], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'retinoids', 'niacinamide', 'sunscreen'],
            'product_categories' => ['sunscreen', 'serum', 'treatment'],
            'tip_ar' => 'تغير لوني وعائي في الرقبة وأعلى الصدر بسبب الشمس والعطور',
            'tip_en' => 'Vascular discoloration on neck and chest from sun and perfumes',
        ],
        'riehls_melanosis' => [
            'category' => 'pigmentation', 'subcategory' => 'facial_pigmentation',
            'name_ar' => 'تصبغ ريل',
            'name_en' => "Riehl's Melanosis",
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['hydroquinone', 'tretinoin', 'sunscreen'],
            'product_categories' => ['treatment', 'sunscreen'],
            'tip_ar' => 'تصبغ شبكي غامق في الوجه والرقبة، قد يكون حساسية لمستحضرات التجميل',
            'tip_en' => 'Dark reticulate pigmentation on face and neck, possibly cosmetic allergy',
        ],

        // ===================== AGING & WRINKLES =====================
        'wrinkles_dynamic' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'expression_lines',
            'name_ar' => 'التجاعيد الديناميكية (خطوط التعبير)',
            'name_en' => 'Dynamic Wrinkles (Expression Lines)',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['botulinum_toxin', 'peptides', 'retinoids'],
            'product_categories' => ['treatment', 'serum', 'moisturizer'],
            'tip_ar' => 'تظهر مع حركة العضلات، استخدم الريتينول والببتيدات',
            'tip_en' => 'Appear with muscle movement, use retinol and peptides',
        ],
        'wrinkles_static' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'deep_lines',
            'name_ar' => 'التجاعيد الثابتة',
            'name_en' => 'Static Wrinkles',
            'severity_range' => [3, 8], 'requires_medical' => false,
            'ingredients' => ['retinoids', 'hyaluronic_acid', 'collagen', 'peptides'],
            'product_categories' => ['serum', 'treatment', 'moisturizer'],
            'tip_ar' => 'تجاعيد موجودة حتى في حالة الراحة، تحتاج عناية مكثفة',
            'tip_en' => 'Wrinkles present even at rest, need intensive care',
        ],
        'fine_lines_superficial' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'fine_lines',
            'name_ar' => 'الخطوط الدقيقة السطحية',
            'name_en' => 'Fine Lines (Superficial)',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => ['hyaluronic_acid', 'peptides', 'vitamin_c', 'sunscreen'],
            'product_categories' => ['serum', 'moisturizer', 'sunscreen'],
            'tip_ar' => 'الخطوط الدقيقة مبكرة، استخدم حمض الهيالورونيك والواقي',
            'tip_en' => 'Early fine lines, use hyaluronic acid and sunscreen',
        ],
        'deep_furrows' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'deep_lines',
            'name_ar' => 'الأخاديد العميقة',
            'name_en' => 'Deep Furrows',
            'severity_range' => [6, 10], 'requires_medical' => true,
            'ingredients' => ['fillers', 'botulinum_toxin', 'retinoids', 'collagen'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'أخاديد عميقة قد تحتاج حشوات أو بوتوكس',
            'tip_en' => 'Deep furrows may need fillers or Botox',
        ],
        'crepey_skin' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'texture',
            'name_ar' => 'الجلد المجعد (الورقي)',
            'name_en' => 'Crepey Skin',
            'severity_range' => [2, 6], 'requires_medical' => false,
            'ingredients' => ['hyaluronic_acid', 'retinoids', 'vitamin_c', 'ceramides'],
            'product_categories' => ['moisturizer', 'serum', 'treatment'],
            'tip_ar' => 'جلد رقيق مثل الورق، استخدم مرطبات غنية بالسيراميد',
            'tip_en' => 'Thin paper-like skin, use moisturizers rich in ceramides',
        ],
        'solar_elastosis' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'sun_damage',
            'name_ar' => 'التليف الشمسي (تلف الكولاجين الشمسي)',
            'name_en' => 'Solar Elastosis (Sun Damage)',
            'severity_range' => [3, 7], 'requires_medical' => false,
            'ingredients' => ['retinoids', 'vitamin_c', 'niacinamide', 'sunscreen'],
            'product_categories' => ['sunscreen', 'serum', 'treatment'],
            'tip_ar' => 'تلف الجلد الناتج عن الشمس مع بشرة صفراء سميكة',
            'tip_en' => 'Sun-damaged skin with thick yellowed texture',
        ],
        'crow_feet' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'expression_lines',
            'name_ar' => 'تجاعيد زوايا العين (قدم الغراب)',
            'name_en' => "Crow's Feet",
            'severity_range' => [1, 6], 'requires_medical' => false,
            'ingredients' => ['retinoids', 'peptides', 'hyaluronic_acid', 'vitamin_c'],
            'product_categories' => ['eye_care', 'serum', 'moisturizer'],
            'tip_ar' => 'تجاعيد دقيقة حول العينين، استخدم كريم عيون بالريتينول',
            'tip_en' => 'Fine lines around eyes, use retinol eye cream',
        ],
        'marionette_lines' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'deep_lines',
            'name_ar' => 'خطوط الماريونيت',
            'name_en' => 'Marionette Lines',
            'severity_range' => [3, 8], 'requires_medical' => true,
            'ingredients' => ['fillers', 'retinoids', 'collagen'],
            'product_categories' => ['treatment', 'serum'],
            'tip_ar' => 'خطوط من زوايا الفم للأسفل، قد تحتاج حشوات',
            'tip_en' => 'Lines from mouth corners downward, may need fillers',
        ],
        'nasolabial_folds' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'deep_lines',
            'name_ar' => 'الخطوط الأنفية الشفوية',
            'name_en' => 'Nasolabial Folds',
            'severity_range' => [2, 7], 'requires_medical' => false,
            'ingredients' => ['hyaluronic_acid', 'collagen', 'peptides'],
            'product_categories' => ['serum', 'moisturizer', 'treatment'],
            'tip_ar' => 'خطوط طبيعية من الأنف إلى الفم، استخدم سيروم الكولاجين',
            'tip_en' => 'Natural lines from nose to mouth, use collagen serum',
        ],
        'platysmal_bands' => [
            'category' => 'aging_wrinkles', 'subcategory' => 'neck',
            'name_ar' => 'حبال الرقبة',
            'name_en' => 'Platysmal Bands',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['botulinum_toxin', 'retinoids'],
            'product_categories' => ['neck_care', 'treatment'],
            'tip_ar' => 'حبال عضلية في الرقبة مع التقدم في العمر',
            'tip_en' => 'Muscular bands in neck with aging',
        ],

        // ===================== SKIN TEXTURE =====================
        'rough_texture' => [
            'category' => 'texture', 'subcategory' => 'general',
            'name_ar' => 'ملمس بشرة خشن',
            'name_en' => 'Rough Skin Texture',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['lactic_acid', 'glycolic_acid', 'salicylic_acid', 'retinoids'],
            'product_categories' => ['exfoliator', 'serum', 'moisturizer'],
            'tip_ar' => 'استخدم مقشر كيميائي أسبوعي لتحسين الملمس',
            'tip_en' => 'Use weekly chemical exfoliant to improve texture',
        ],
        'orange_peel_texture' => [
            'category' => 'texture', 'subcategory' => 'pore_related',
            'name_ar' => 'ملمس قشر البرتقال',
            'name_en' => 'Orange Peel Texture',
            'severity_range' => [2, 6], 'requires_medical' => false,
            'ingredients' => ['niacinamide', 'salicylic_acid', 'retinoids', 'zinc'],
            'product_categories' => ['toner', 'serum', 'treatment'],
            'tip_ar' => 'ملمس يشبه قشر البرتقال بسبب المسام الواسعة، استخدم النياسيناميد',
            'tip_en' => 'Orange peel texture from enlarged pores, use niacinamide',
        ],
        'hypertrophic_scars' => [
            'category' => 'texture', 'subcategory' => 'scarring',
            'name_ar' => 'الندبات المتضخمة',
            'name_en' => 'Hypertrophic Scars',
            'severity_range' => [4, 8], 'requires_medical' => true,
            'ingredients' => ['silicone', 'corticosteroids', 'laser_treatment'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'ندبات بارزة تحتاج علاجاً طبياً',
            'tip_en' => 'Raised scars requiring medical treatment',
        ],
        'milia' => [
            'category' => 'texture', 'subcategory' => 'lesions',
            'name_ar' => 'الميليا (الدهون البيضاء)',
            'name_en' => 'Milia (White Oil Bumps)',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => ['retinoids', 'salicylic_acid', 'glycolic_acid'],
            'product_categories' => ['exfoliator', 'treatment'],
            'tip_ar' => 'حبيبات بيضاء صغيرة، استخدم التقشير الكيميائي',
            'tip_en' => 'Small white bumps, use chemical exfoliation',
        ],
        'syringoma' => [
            'category' => 'texture', 'subcategory' => 'lesions',
            'name_ar' => 'الورم الغدي العرقي',
            'name_en' => 'Syringoma',
            'severity_range' => [2, 4], 'requires_medical' => true,
            'ingredients' => ['laser_treatment', 'electrocautery'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'حبيبات بلون الجلد حول العينين، تحتاج إزالة بالليزر',
            'tip_en' => 'Skin-colored bumps around eyes, need laser removal',
        ],

        // ===================== HYDRATION & BARRIER =====================
        'dehydrated_skin' => [
            'category' => 'hydration_barrier', 'subcategory' => 'moisture_balance',
            'name_ar' => 'البشرة المجففة (نقص الماء)',
            'name_en' => 'Dehydrated Skin (Lack of Water)',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['hyaluronic_acid', 'glycerin', 'panthenol', 'ceramides'],
            'product_categories' => ['moisturizer', 'serum', 'toner'],
            'tip_ar' => 'اشرب الماء بكثرة واستخدم مرطب بحمض الهيالورونيك',
            'tip_en' => 'Drink plenty of water and use hyaluronic acid moisturizer',
        ],
        'dry_skin' => [
            'category' => 'hydration_barrier', 'subcategory' => 'oil_moisture',
            'name_ar' => 'البشرة الجافة (نقص الزيوت)',
            'name_en' => 'Dry Skin (Lack of Oil)',
            'severity_range' => [1, 6], 'requires_medical' => false,
            'ingredients' => ['ceramides', 'shea_butter', 'squalane', 'fatty_acids'],
            'product_categories' => ['moisturizer', 'oil', 'cleanser'],
            'tip_ar' => 'استخدم مرطباً غنياً بالزيوت الطبيعية والسيراميد',
            'tip_en' => 'Use rich moisturizer with natural oils and ceramides',
        ],
        'compromised_barrier' => [
            'category' => 'hydration_barrier', 'subcategory' => 'barrier_function',
            'name_ar' => 'حاجز البشرة التالف',
            'name_en' => 'Compromised Skin Barrier',
            'severity_range' => [3, 8], 'requires_medical' => false,
            'ingredients' => ['ceramides', 'niacinamide', 'panthenol', 'centella_asiatica', 'squalane'],
            'product_categories' => ['moisturizer', 'serum', 'treatment'],
            'tip_ar' => 'تجنب المنتجات القوية وركز على ترميم الحاجز الجلدي',
            'tip_en' => 'Avoid harsh products and focus on barrier repair',
        ],
        'tewl' => [
            'category' => 'hydration_barrier', 'subcategory' => 'barrier_function',
            'name_ar' => 'فقدان الماء عبر البشرة (TEWL)',
            'name_en' => 'Transepidermal Water Loss (TEWL)',
            'severity_range' => [2, 7], 'requires_medical' => false,
            'ingredients' => ['ceramides', 'vaseline', 'squalane', 'dimethicone'],
            'product_categories' => ['moisturizer', 'occlusive'],
            'tip_ar' => 'فقدان الرطوبة بسبب ضعف الحاجز الجلدي',
            'tip_en' => 'Moisture loss due to weak skin barrier',
        ],
        'xerosis_cutis' => [
            'category' => 'hydration_barrier', 'subcategory' => 'dryness',
            'name_ar' => 'جفاف الجلد المزمن',
            'name_en' => 'Xerosis Cutis',
            'severity_range' => [2, 6], 'requires_medical' => false,
            'ingredients' => ['urea', 'lactic_acid', 'ceramides', 'petrolatum'],
            'product_categories' => ['moisturizer', 'treatment'],
            'tip_ar' => 'جفاف مزمن مع تقشر، استخدم مرطب باليوريا',
            'tip_en' => 'Chronic dryness with flaking, use urea moisturizer',
        ],
        'ichthyosis_vulgaris' => [
            'category' => 'hydration_barrier', 'subcategory' => 'genetic_dryness',
            'name_ar' => 'السماك الشائع',
            'name_en' => 'Ichthyosis Vulgaris',
            'severity_range' => [2, 5], 'requires_medical' => true,
            'ingredients' => ['lactic_acid', 'urea', 'ceramides', 'tretinoin'],
            'product_categories' => ['moisturizer', 'treatment', 'prescription'],
            'tip_ar' => 'حالة وراثية تسبب جفافاً شديداً مع تقشر',
            'tip_en' => 'Genetic condition causing severe dryness with scaling',
        ],

        // ===================== SEBUM & OIL =====================
        'seborrhea' => [
            'category' => 'sebum_oil', 'subcategory' => 'excess_oil',
            'name_ar' => 'الدهون الزائدة (الزهم)',
            'name_en' => 'Seborrhea (Excessive Oil)',
            'severity_range' => [2, 6], 'requires_medical' => false,
            'ingredients' => ['niacinamide', 'salicylic_acid', 'zinc_pca', 'retinoids'],
            'product_categories' => ['cleanser', 'toner', 'serum', 'moisturizer'],
            'tip_ar' => 'نظف البشرة مرتين يومياً واستخدم تونر قابض للمسام',
            'tip_en' => 'Cleanse twice daily and use pore-tightening toner',
        ],
        'sebaceous_filaments' => [
            'category' => 'sebum_oil', 'subcategory' => 'pore_content',
            'name_ar' => 'الخيوط الدهنية',
            'name_en' => 'Sebaceous Filaments',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => ['salicylic_acid', 'niacinamide', 'retinoids'],
            'product_categories' => ['cleanser', 'toner', 'treatment'],
            'tip_ar' => 'خيوط دهنية طبيعية في الأنف والذقن، استخدم حمض الساليسيليك بانتظام',
            'tip_en' => 'Natural oil filaments on nose and chin, use salicylic acid regularly',
        ],
        'sebaceous_hyperplasia' => [
            'category' => 'sebum_oil', 'subcategory' => 'glandular',
            'name_ar' => 'تضخم الغدد الدهنية',
            'name_en' => 'Sebaceous Hyperplasia',
            'severity_range' => [2, 5], 'requires_medical' => true,
            'ingredients' => ['retinoids', 'laser_treatment', 'electrocautery'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'نتوءات صفراء صغيرة ناتجة عن تضخم الغدد الدهنية',
            'tip_en' => 'Small yellow bumps from enlarged sebaceous glands',
        ],
        'steatocystoma' => [
            'category' => 'sebum_oil', 'subcategory' => 'cysts',
            'name_ar' => 'الكيسة الدهنية',
            'name_en' => 'Steatocystoma (Sebaceous Cyst)',
            'severity_range' => [3, 6], 'requires_medical' => true,
            'ingredients' => ['surgical_excision', 'retinoids'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'كيسة دهنية تحت الجلد تحتاج استئصالاً جراحياً',
            'tip_en' => 'Subcutaneous oil cyst needing surgical removal',
        ],

        // ===================== VASCULAR & REDNESS =====================
        'telangiectasia' => [
            'category' => 'vascular_redness', 'subcategory' => 'vessels',
            'name_ar' => 'توسع الشعيرات الدموية (الأوردة العنكبوتية)',
            'name_en' => 'Telangiectasia (Spider Veins)',
            'severity_range' => [2, 5], 'requires_medical' => true,
            'ingredients' => ['laser_treatment', 'ipl', 'vitamin_k'],
            'product_categories' => ['treatment', 'serum'],
            'tip_ar' => 'أوعية دموية صغيرة مرئية، تحتاج علاجاً بالليزر',
            'tip_en' => 'Visible small blood vessels, need laser treatment',
        ],
        'facial_erythema' => [
            'category' => 'vascular_redness', 'subcategory' => 'redness',
            'name_ar' => 'احمرار الوجه العام',
            'name_en' => 'Facial Erythema (General Redness)',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['azelaic_acid', 'niacinamide', 'centella_asiatica', 'green_tea'],
            'product_categories' => ['moisturizer', 'serum', 'treatment'],
            'tip_ar' => 'استخدم منتجات مهدئة تحتوي على النياسيناميد',
            'tip_en' => 'Use soothing products with niacinamide',
        ],
        'rosacea_type1' => [
            'category' => 'vascular_redness', 'subcategory' => 'rosacea',
            'name_ar' => 'الوردية النوع الأول (احمرار مع أوعية دموية)',
            'name_en' => 'Rosacea Type 1 (Erythematotelangiectatic)',
            'severity_range' => [2, 6], 'requires_medical' => true,
            'ingredients' => ['azelaic_acid', 'metronidazole', 'ivermectin', 'sunscreen'],
            'product_categories' => ['treatment', 'sunscreen', 'moisturizer'],
            'tip_ar' => 'احمرار مزمن مع أوعية دموية ظاهرة، استخدم واقي شمس يومياً',
            'tip_en' => 'Chronic redness with visible vessels, use daily sunscreen',
        ],
        'rosacea_type2' => [
            'category' => 'vascular_redness', 'subcategory' => 'rosacea',
            'name_ar' => 'الوردية النوع الثاني (حطاطي بثري)',
            'name_en' => 'Rosacea Type 2 (Papulopustular)',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['azelaic_acid', 'metronidazole', 'ivermectin', 'doxycycline'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'وردية مع بثور وحطاطات، تحتاج علاجاً طبياً',
            'tip_en' => 'Rosacea with pustules and papules, needs medical treatment',
        ],
        'rosacea_type3' => [
            'category' => 'vascular_redness', 'subcategory' => 'rosacea',
            'name_ar' => 'الوردية النوع الثالث (الأنف الشمي)',
            'name_en' => 'Rosacea Type 3 (Phymatous)',
            'severity_range' => [5, 9], 'requires_medical' => true,
            'ingredients' => ['laser_treatment', 'surgery', 'isotretinoin'],
            'product_categories' => ['prescription', 'treatment'],
            'tip_ar' => 'تضخم في أنسجة الأنف، يحتاج تدخلاً طبياً',
            'tip_en' => 'Nasal tissue enlargement, needs medical intervention',
        ],
        'rosacea_type4' => [
            'category' => 'vascular_redness', 'subcategory' => 'rosacea',
            'name_ar' => 'الوردية النوع الرابع (العينية)',
            'name_en' => 'Rosacea Type 4 (Ocular)',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['antibiotics', 'cyclosporine', 'warm_compresses'],
            'product_categories' => ['prescription'],
            'tip_ar' => 'تصيب العينين بجفاف واحمرار، استشر طبيب عيون',
            'tip_en' => 'Affects eyes with dryness and redness, consult ophthalmologist',
        ],
        'couperose_skin' => [
            'category' => 'vascular_redness', 'subcategory' => 'vessels',
            'name_ar' => 'البشرة الكوبيروزية (الهشاشة الوعائية)',
            'name_en' => 'Couperose Skin',
            'severity_range' => [2, 5], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'vitamin_k', 'niacinamide', 'centella_asiatica'],
            'product_categories' => ['serum', 'moisturizer', 'treatment'],
            'tip_ar' => 'بشرة حساسة مع أوعية دموية هشة، تجنب التغيرات الحرارية المفاجئة',
            'tip_en' => 'Sensitive skin with fragile vessels, avoid sudden temperature changes',
        ],
        'flushing' => [
            'category' => 'vascular_redness', 'subcategory' => 'temporary',
            'name_ar' => 'الاحمرار المفاجئ (الهبات)',
            'name_en' => 'Flushing/Blushing',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['niacinamide', 'azelaic_acid', 'beta_blockers'],
            'product_categories' => ['treatment', 'moisturizer'],
            'tip_ar' => 'احمرار مفاجئ بسبب عوامل مختلفة، استخدم منتجات مهدئة',
            'tip_en' => 'Sudden redness from various triggers, use soothing products',
        ],
        'angioma' => [
            'category' => 'vascular_redness', 'subcategory' => 'lesions',
            'name_ar' => 'الورم الوعائي (الكرزي/العنكبوتي)',
            'name_en' => 'Angioma (Cherry/Spider)',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['laser_treatment', 'electrocautery'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'بقع حمراء وعائية، يمكن إزالتها بالليزر',
            'tip_en' => 'Red vascular spots, can be removed with laser',
        ],

        // ===================== SENSITIVITY & INFLAMMATION =====================
        'allergic_contact_dermatitis' => [
            'category' => 'sensitivity', 'subcategory' => 'contact',
            'name_ar' => 'التهاب الجلد التماسي التحسسي',
            'name_en' => 'Allergic Contact Dermatitis',
            'severity_range' => [3, 8], 'requires_medical' => true,
            'ingredients' => ['corticosteroids', 'antihistamines', 'calamine'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'حساسية من مادة معينة، تجنب المسبب واستشر طبيباً',
            'tip_en' => 'Allergy to a specific substance, avoid trigger and consult doctor',
        ],
        'atopic_dermatitis' => [
            'category' => 'sensitivity', 'subcategory' => 'chronic',
            'name_ar' => 'التهاب الجلد التأتبي (الإكزيما)',
            'name_en' => 'Atopic Dermatitis (Eczema)',
            'severity_range' => [3, 9], 'requires_medical' => true,
            'ingredients' => ['corticosteroids', 'tacrolimus', 'ceramides', 'bleach_baths'],
            'product_categories' => ['moisturizer', 'treatment', 'prescription'],
            'tip_ar' => 'إكزيما مزمنة مع حكة وجفاف، تحتاج ترطيباً مستمراً وعلاجاً',
            'tip_en' => 'Chronic eczema with itching and dryness, needs continuous moisturizing',
        ],
        'seborrheic_dermatitis' => [
            'category' => 'sensitivity', 'subcategory' => 'chronic',
            'name_ar' => 'التهاب الجلد الدهني',
            'name_en' => 'Seborrheic Dermatitis',
            'severity_range' => [2, 6], 'requires_medical' => true,
            'ingredients' => ['ketoconazole', 'selenium_sulfide', 'zinc_pyrithione', 'corticosteroids'],
            'product_categories' => ['treatment', 'cleanser', 'prescription'],
            'tip_ar' => 'التهاب مع قشرة وحكة في المناطق الدهنية من الوجه',
            'tip_en' => 'Inflammation with dandruff and itching in oily facial areas',
        ],
        'reactive_skin' => [
            'category' => 'sensitivity', 'subcategory' => 'general',
            'name_ar' => 'البشرة المتفاعلة (الحساسة)',
            'name_en' => 'Reactive Skin',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['centella_asiatica', 'panthenol', 'allantoin', 'niacinamide'],
            'product_categories' => ['moisturizer', 'serum', 'cleanser'],
            'tip_ar' => 'بشرة تتفاعل بسرعة مع المنتجات، استخدم منتجات لطيفة وخالية من العطور',
            'tip_en' => 'Skin reacts quickly to products, use gentle fragrance-free products',
        ],
        'photosensitivity' => [
            'category' => 'sensitivity', 'subcategory' => 'sun_related',
            'name_ar' => 'الحساسية للضوء',
            'name_en' => 'Photosensitivity',
            'severity_range' => [2, 7], 'requires_medical' => true,
            'ingredients' => ['sunscreen', 'antihistamines', 'corticosteroids'],
            'product_categories' => ['sunscreen', 'treatment', 'prescription'],
            'tip_ar' => 'حساسية شديدة للشمس، استخدم واقي شمس واسع الطيف يومياً',
            'tip_en' => 'Severe sun sensitivity, use broad-spectrum sunscreen daily',
        ],

        // ===================== PORES =====================
        'dilated_pores' => [
            'category' => 'pores', 'subcategory' => 'enlarged',
            'name_ar' => 'المسام الواسعة',
            'name_en' => 'Dilated Pores',
            'severity_range' => [1, 5], 'requires_medical' => false,
            'ingredients' => ['niacinamide', 'retinoids', 'salicylic_acid', 'zinc_pca'],
            'product_categories' => ['toner', 'serum', 'treatment', 'cleanser'],
            'tip_ar' => 'المسام ليس لها عضلات لإغلاقها، استخدم النياسيناميد لتقليل مظهرها',
            'tip_en' => 'Pores can\'t open/close, use niacinamide to minimize appearance',
        ],
        'clogged_pores' => [
            'category' => 'pores', 'subcategory' => 'blocked',
            'name_ar' => 'المسام المسدودة (الرؤوس السوداء)',
            'name_en' => 'Clogged Pores (Blackheads)',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['salicylic_acid', 'retinoids', 'niacinamide'],
            'product_categories' => ['cleanser', 'toner', 'exfoliator', 'treatment'],
            'tip_ar' => 'استخدم حمض الساليسيليك بانتظام لتنظيف المسام',
            'tip_en' => 'Use salicylic acid regularly to clean pores',
        ],
        'strawberry_nose' => [
            'category' => 'pores', 'subcategory' => 'nasal',
            'name_ar' => 'الأنف الفراولي',
            'name_en' => 'Strawberry Nose',
            'severity_range' => [2, 5], 'requires_medical' => false,
            'ingredients' => ['salicylic_acid', 'niacinamide', 'retinoids', 'zinc_pca'],
            'product_categories' => ['cleanser', 'toner', 'treatment'],
            'tip_ar' => 'مسام واسعة في الأنف مع رؤوس سوداء، نظف بانتظام بحمض الساليسيليك',
            'tip_en' => 'Enlarged nose pores with blackheads, clean regularly with salicylic acid',
        ],

        // ===================== UNDER-EYE =====================
        'dark_circles_pigmented' => [
            'category' => 'under_eye', 'subcategory' => 'dark_circles',
            'name_ar' => 'الهالات السوداء الصباغية',
            'name_en' => 'Pigmented Dark Circles',
            'severity_range' => [2, 6], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'kojic_acid', 'retinoids', 'niacinamide'],
            'product_categories' => ['eye_care', 'serum'],
            'tip_ar' => 'بني/رمادي اللون، استخدم سيروم فيتامين سي حول العينين',
            'tip_en' => 'Brown/gray color, use vitamin C serum around eyes',
        ],
        'dark_circles_vascular' => [
            'category' => 'under_eye', 'subcategory' => 'dark_circles',
            'name_ar' => 'الهالات السوداء الوعائية',
            'name_en' => 'Vascular Dark Circles',
            'severity_range' => [2, 5], 'requires_medical' => false,
            'ingredients' => ['vitamin_k', 'caffeine', 'retinoids'],
            'product_categories' => ['eye_care', 'serum'],
            'tip_ar' => 'زرقاء/بنفسجية بسبب الأوعية الدموية، حسّن جودة النوم',
            'tip_en' => 'Blue/purple from blood vessels, improve sleep quality',
        ],
        'dark_circles_structural' => [
            'category' => 'under_eye', 'subcategory' => 'dark_circles',
            'name_ar' => 'الهالات السوداء الهيكلية',
            'name_en' => 'Structural Dark Circles',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['fillers', 'hyaluronic_acid', 'prp'],
            'product_categories' => ['eye_care', 'treatment'],
            'tip_ar' => 'ظل ناتج عن بنية الوجه، قد يحتاج حشوات تجميلية',
            'tip_en' => 'Shadow from facial structure, may need fillers',
        ],
        'tear_trough' => [
            'category' => 'under_eye', 'subcategory' => 'structural',
            'name_ar' => 'تقعر أسفل العين (دمع العين)',
            'name_en' => 'Tear Trough Deformity',
            'severity_range' => [4, 8], 'requires_medical' => true,
            'ingredients' => ['hyaluronic_acid_filler', 'prf', 'fat_grafting'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'تقعر واضح تحت العينين، يحتاج حشوات حمض الهيالورونيك',
            'tip_en' => 'Pronounced hollow under eyes, needs hyaluronic acid fillers',
        ],
        'eye_bags' => [
            'category' => 'under_eye', 'subcategory' => 'edema',
            'name_ar' => 'انتفاخ تحت العين (أكياس العين)',
            'name_en' => 'Eye Bags (Periorbital Edema)',
            'severity_range' => [2, 6], 'requires_medical' => false,
            'ingredients' => ['caffeine', 'vitamin_k', 'cold_compresses', 'drainage_massage'],
            'product_categories' => ['eye_care', 'serum'],
            'tip_ar' => 'انتفاخ تحت العينين، استخدم كريم عيون بالكافيين',
            'tip_en' => 'Swelling under eyes, use caffeine eye cream',
        ],
        'xanthelasma' => [
            'category' => 'under_eye', 'subcategory' => 'deposits',
            'name_ar' => 'اللويحات الصفراء حول العين',
            'name_en' => 'Xanthelasma',
            'severity_range' => [3, 5], 'requires_medical' => true,
            'ingredients' => ['laser_treatment', 'chemical_peel', 'surgical_excision'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'لويحات صفراء تحت الجلد حول العينين، قد ترتبط بارتفاع الكوليسترول',
            'tip_en' => 'Yellow plaques under eye skin, may be linked to high cholesterol',
        ],

        // ===================== MEDICAL / PATHOLOGICAL =====================
        'actinic_keratosis' => [
            'category' => 'medical', 'subcategory' => 'precancerous',
            'name_ar' => 'التقرن السفعي (ما قبل السرطان)',
            'name_en' => 'Actinic Keratosis (Precancerous)',
            'severity_range' => [4, 8], 'requires_medical' => true,
            'ingredients' => ['cryotherapy', '5_fluorouracil', 'imiquimod', 'photodynamic_therapy'],
            'product_categories' => ['prescription', 'treatment'],
            'tip_ar' => 'آفة ما قبل سرطانية، يجب مراجعة طبيب الجلدية فوراً',
            'tip_en' => 'Precancerous lesion, must see a dermatologist immediately',
        ],
        'basal_cell_carcinoma' => [
            'category' => 'medical', 'subcategory' => 'cancerous',
            'name_ar' => 'سرطان الخلايا القاعدية (BCC)',
            'name_en' => 'Basal Cell Carcinoma (BCC)',
            'severity_range' => [6, 10], 'requires_medical' => true,
            'ingredients' => ['surgical_excision', 'mohs_surgery', 'radiotherapy'],
            'product_categories' => ['prescription'],
            'tip_ar' => 'أكثر أنواع سرطان الجلد شيوعاً، يحتاج استئصالاً جراحياً فورياً',
            'tip_en' => 'Most common skin cancer, needs immediate surgical removal',
        ],
        'squamous_cell_carcinoma' => [
            'category' => 'medical', 'subcategory' => 'cancerous',
            'name_ar' => 'سرطان الخلايا الحرشفية (SCC)',
            'name_en' => 'Squamous Cell Carcinoma (SCC)',
            'severity_range' => [7, 10], 'requires_medical' => true,
            'ingredients' => ['surgical_excision', 'mohs_surgery', 'radiotherapy', 'chemotherapy'],
            'product_categories' => ['prescription'],
            'tip_ar' => 'سرطان جلدي خطير يمكن أن ينتشر، يحتاج علاجاً فورياً',
            'tip_en' => 'Dangerous skin cancer that can metastasize, needs immediate treatment',
        ],
        'malignant_melanoma' => [
            'category' => 'medical', 'subcategory' => 'cancerous',
            'name_ar' => 'الورم الميلانيني الخبيث',
            'name_en' => 'Malignant Melanoma',
            'severity_range' => [8, 10], 'requires_medical' => true,
            'ingredients' => ['surgical_excision', 'immunotherapy', 'targeted_therapy', 'chemotherapy'],
            'product_categories' => ['prescription'],
            'tip_ar' => 'أخطر أنواع سرطان الجلد، يتطلب تدخلاً طبياً فورياً جداً',
            'tip_en' => 'Most dangerous skin cancer, requires immediate medical intervention',
        ],
        'melanocytic_nevus' => [
            'category' => 'medical', 'subcategory' => 'benign',
            'name_ar' => 'الوحمة الميلانينية (الشامة الحميدة)',
            'name_en' => 'Melanocytic Nevus (Benign Mole)',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => [],
            'product_categories' => [],
            'tip_ar' => 'شامة حميدة، راقبها لأي تغير في الشكل أو اللون',
            'tip_en' => 'Benign mole, monitor for any change in shape or color',
        ],
        'seborrheic_keratosis' => [
            'category' => 'medical', 'subcategory' => 'benign',
            'name_ar' => 'التقرن الدهني',
            'name_en' => 'Seborrheic Keratosis',
            'severity_range' => [1, 4], 'requires_medical' => false,
            'ingredients' => ['cryotherapy', 'cautery'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'زوائد جلدية بنية حميدة شائعة مع التقدم في العمر',
            'tip_en' => 'Common benign brown growths with aging',
        ],
        'dermatofibroma' => [
            'category' => 'medical', 'subcategory' => 'benign',
            'name_ar' => 'الورم الليفي الجلدي',
            'name_en' => 'Dermatofibroma',
            'severity_range' => [1, 3], 'requires_medical' => false,
            'ingredients' => ['surgical_excision'],
            'product_categories' => ['treatment'],
            'tip_ar' => 'عقيدة حميدة صلبة، يمكن تركها أو استئصالها',
            'tip_en' => 'Firm benign nodule, can be left or excised',
        ],
        'psoriasis_facial' => [
            'category' => 'medical', 'subcategory' => 'autoimmune',
            'name_ar' => 'الصدفية الوجهية',
            'name_en' => 'Facial Psoriasis',
            'severity_range' => [3, 8], 'requires_medical' => true,
            'ingredients' => ['corticosteroids', 'vitamin_d_analogues', 'tacrolimus', 'biologics'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'مرض مناعي مزمن مع لويحات حمراء متقشرة',
            'tip_en' => 'Chronic autoimmune disease with red scaly plaques',
        ],
        'lupus_erythematosus' => [
            'category' => 'medical', 'subcategory' => 'autoimmune',
            'name_ar' => 'الذئبة الحمامية',
            'name_en' => 'Lupus Erythematosus',
            'severity_range' => [5, 9], 'requires_medical' => true,
            'ingredients' => ['corticosteroids', 'hydroxychloroquine', 'immunosuppressants', 'sunscreen'],
            'product_categories' => ['prescription', 'sunscreen'],
            'tip_ar' => 'مرض مناعي يسبب طفحاً على الوجه بشكل فراشة',
            'tip_en' => 'Autoimmune disease causing butterfly-shaped facial rash',
        ],
        'tinea_faciei' => [
            'category' => 'medical', 'subcategory' => 'fungal',
            'name_ar' => 'التينيا الوجهية (فطريات الوجه)',
            'name_en' => 'Tinea Faciei (Fungal Infection)',
            'severity_range' => [2, 5], 'requires_medical' => true,
            'ingredients' => ['terbinafine', 'clotrimazole', 'ketoconazole'],
            'product_categories' => ['antifungal', 'prescription'],
            'tip_ar' => 'عدوى فطرية في الوجه مع بقعة حمراء دائرية متقشرة',
            'tip_en' => 'Fungal infection on face with circular scaly red patch',
        ],
        'impetigo' => [
            'category' => 'medical', 'subcategory' => 'bacterial',
            'name_ar' => 'القوباء (عدوى بكتيرية)',
            'name_en' => 'Impetigo (Bacterial Infection)',
            'severity_range' => [3, 6], 'requires_medical' => true,
            'ingredients' => ['mupirocin', 'fusidic_acid', 'antibiotics'],
            'product_categories' => ['prescription', 'treatment'],
            'tip_ar' => 'عدوى بكتيرية معدية مع قشور صفراء',
            'tip_en' => 'Contagious bacterial infection with yellow crusts',
        ],
        'herpes_simplex' => [
            'category' => 'medical', 'subcategory' => 'viral',
            'name_ar' => 'الهربس البسيط (قرحة البرد)',
            'name_en' => 'Herpes Simplex (Cold Sore)',
            'severity_range' => [3, 6], 'requires_medical' => true,
            'ingredients' => ['acyclovir', 'valacyclovir', 'penciclovir', 'lysine'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'فيروس الهربس يسبب بثوراً حول الفم، استخدم مضاداً فيروسياً عند أول علامة',
            'tip_en' => 'Herpes virus causes blisters around mouth, use antiviral at first sign',
        ],
        'lichen_planus' => [
            'category' => 'medical', 'subcategory' => 'inflammatory',
            'name_ar' => 'الحزاز المسطح',
            'name_en' => 'Lichen Planus',
            'severity_range' => [3, 7], 'requires_medical' => true,
            'ingredients' => ['corticosteroids', 'retinoids', 'antihistamines', 'phototherapy'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'حطاطات أرجوانية حاكة غير معروفة السبب',
            'tip_en' => 'Itchy purple papules of unknown cause',
        ],
        'molluscum_contagiosum' => [
            'category' => 'medical', 'subcategory' => 'viral',
            'name_ar' => 'المليساء المعدية',
            'name_en' => 'Molluscum Contagiosum',
            'severity_range' => [2, 4], 'requires_medical' => true,
            'ingredients' => ['cryotherapy', 'curettage', 'cantharidin', 'retinoids'],
            'product_categories' => ['treatment', 'prescription'],
            'tip_ar' => 'حطاطات صغيرة بلون الجلد مع نقرة في المنتصف',
            'tip_en' => 'Small skin-colored papules with central dimple',
        ],

        // ===================== SPECTRAL & LIGHTING =====================
        'uv_damage_visible' => [
            'category' => 'spectral', 'subcategory' => 'ultraviolet',
            'name_ar' => 'تلف أشعة الشمس المرئي (UV)',
            'name_en' => 'Visible UV Damage',
            'severity_range' => [2, 8], 'requires_medical' => false,
            'ingredients' => ['sunscreen', 'vitamin_c', 'retinoids', 'niacinamide'],
            'product_categories' => ['sunscreen', 'serum', 'treatment'],
            'tip_ar' => 'أضرار الشمس العميقة تحت سطح الجلد غير المرئية بالعين المجردة',
            'tip_en' => 'Deep sun damage beneath skin surface invisible to naked eye',
        ],
        'uv_porphyrins' => [
            'category' => 'spectral', 'subcategory' => 'ultraviolet',
            'name_ar' => 'البورفيرينات (بكتيريا حب الشباب تحت UV)',
            'name_en' => 'Porphyrins (Acne Bacteria Under UV)',
            'severity_range' => [1, 6], 'requires_medical' => false,
            'ingredients' => ['benzoyl_peroxide', 'retinoids', 'antibiotics'],
            'product_categories' => ['treatment', 'cleanser'],
            'tip_ar' => 'بكتيريا حب الشباب تظهر باللون البرتقالي/الأحمر تحت الضوء فوق البنفسجي',
            'tip_en' => 'Acne bacteria appears orange/red under UV light',
        ],
        'cross_polarized_pigmentation' => [
            'category' => 'spectral', 'subcategory' => 'cross_polarized',
            'name_ar' => 'التصبغات العميقة (ضوء مستقطب)',
            'name_en' => 'Deep Pigmentation (Cross-Polarized)',
            'severity_range' => [1, 7], 'requires_medical' => false,
            'ingredients' => ['vitamin_c', 'retinoids', 'hydroquinone', 'tranexamic_acid'],
            'product_categories' => ['serum', 'treatment', 'sunscreen'],
            'tip_ar' => 'تصبغات غير مرئية بالعين العادية تظهر تحت الضوء المستقطب',
            'tip_en' => 'Pigmentation invisible to normal eye visible under cross-polarized light',
        ],
    ];

    private const CATEGORIES = [
        'acne_blemish' => ['name_ar' => 'حب الشباب والبثور', 'name_en' => 'Acne & Blemish', 'order' => 1],
        'pigmentation' => ['name_ar' => 'التصبغات', 'name_en' => 'Pigmentation', 'order' => 2],
        'aging_wrinkles' => ['name_ar' => 'الشيخوخة والتجاعيد', 'name_en' => 'Aging & Wrinkles', 'order' => 3],
        'texture' => ['name_ar' => 'ملمس البشرة', 'name_en' => 'Skin Texture', 'order' => 4],
        'hydration_barrier' => ['name_ar' => 'الترطيب والحاجز الجلدي', 'name_en' => 'Hydration & Barrier', 'order' => 5],
        'sebum_oil' => ['name_ar' => 'الدهون والزهم', 'name_en' => 'Sebum & Oil', 'order' => 6],
        'vascular_redness' => ['name_ar' => 'الأوعية والاحمرار', 'name_en' => 'Vascular & Redness', 'order' => 7],
        'sensitivity' => ['name_ar' => 'الحساسية والالتهاب', 'name_en' => 'Sensitivity & Inflammation', 'order' => 8],
        'pores' => ['name_ar' => 'المسام', 'name_en' => 'Pores', 'order' => 9],
        'under_eye' => ['name_ar' => 'منطقة تحت العين', 'name_en' => 'Under-Eye', 'order' => 10],
        'medical' => ['name_ar' => 'حالات طبية', 'name_en' => 'Medical Conditions', 'order' => 11],
        'spectral' => ['name_ar' => 'تحليل طيفي', 'name_en' => 'Spectral Analysis', 'order' => 12],
    ];

    private const FACIAL_ZONES = [
        'forehead_center' => ['name_ar' => 'منتصف الجبهة', 'name_en' => 'Forehead Center', 'x' => 50, 'y' => 15],
        'forehead_left' => ['name_ar' => 'الجبهة اليسرى', 'name_en' => 'Forehead Left', 'x' => 25, 'y' => 15],
        'forehead_right' => ['name_ar' => 'الجبهة اليمنى', 'name_en' => 'Forehead Right', 'x' => 75, 'y' => 15],
        'glabella' => ['name_ar' => 'ما بين الحاجبين', 'name_en' => 'Glabella', 'x' => 50, 'y' => 30],
        'temple_left' => ['name_ar' => 'الصدغ الأيسر', 'name_en' => 'Left Temple', 'x' => 10, 'y' => 30],
        'temple_right' => ['name_ar' => 'الصدغ الأيمن', 'name_en' => 'Right Temple', 'x' => 90, 'y' => 30],
        'eyebrow_left' => ['name_ar' => 'الحاجب الأيسر', 'name_en' => 'Left Eyebrow', 'x' => 30, 'y' => 25],
        'eyebrow_right' => ['name_ar' => 'الحاجب الأيمن', 'name_en' => 'Right Eyebrow', 'x' => 70, 'y' => 25],
        'periorbital_left_upper' => ['name_ar' => 'فوق العين اليسرى', 'name_en' => 'Left Upper Eyelid', 'x' => 30, 'y' => 35],
        'periorbital_right_upper' => ['name_ar' => 'فوق العين اليمنى', 'name_en' => 'Right Upper Eyelid', 'x' => 70, 'y' => 35],
        'periorbital_left_lower' => ['name_ar' => 'تحت العين اليسرى', 'name_en' => 'Left Under-Eye', 'x' => 30, 'y' => 45],
        'periorbital_right_lower' => ['name_ar' => 'تحت العين اليمنى', 'name_en' => 'Right Under-Eye', 'x' => 70, 'y' => 45],
        'nose_bridge' => ['name_ar' => 'جسر الأنف', 'name_en' => 'Nose Bridge', 'x' => 50, 'y' => 38],
        'nose_tip' => ['name_ar' => 'طرف الأنف', 'name_en' => 'Nose Tip', 'x' => 50, 'y' => 50],
        'nose_left_alar' => ['name_ar' => 'جناح الأنف الأيسر', 'name_en' => 'Left Nose Alar', 'x' => 42, 'y' => 50],
        'nose_right_alar' => ['name_ar' => 'جناح الأنف الأيمن', 'name_en' => 'Right Nose Alar', 'x' => 58, 'y' => 50],
        'nasolabial_left' => ['name_ar' => 'الخط الأنفي الشفوي الأيسر', 'name_en' => 'Left Nasolabial Fold', 'x' => 38, 'y' => 55],
        'nasolabial_right' => ['name_ar' => 'الخط الأنفي الشفوي الأيمن', 'name_en' => 'Right Nasolabial Fold', 'x' => 62, 'y' => 55],
        'cheek_left_upper' => ['name_ar' => 'الخد الأيسر العلوي', 'name_en' => 'Left Upper Cheek', 'x' => 20, 'y' => 45],
        'cheek_right_upper' => ['name_ar' => 'الخد الأيمن العلوي', 'name_en' => 'Right Upper Cheek', 'x' => 80, 'y' => 45],
        'cheek_left_lower' => ['name_ar' => 'الخد الأيسر السفلي', 'name_en' => 'Left Lower Cheek', 'x' => 20, 'y' => 60],
        'cheek_right_lower' => ['name_ar' => 'الخد الأيمن السفلي', 'name_en' => 'Right Lower Cheek', 'x' => 80, 'y' => 60],
        'cheek_left_zygomatic' => ['name_ar' => 'الوجنة اليسرى', 'name_en' => 'Left Zygomatic', 'x' => 25, 'y' => 40],
        'cheek_right_zygomatic' => ['name_ar' => 'الوجنة اليمنى', 'name_en' => 'Right Zygomatic', 'x' => 75, 'y' => 40],
        'upper_lip' => ['name_ar' => 'الشفة العلوية', 'name_en' => 'Upper Lip', 'x' => 50, 'y' => 62],
        'lower_lip' => ['name_ar' => 'الشفة السفلية', 'name_en' => 'Lower Lip', 'x' => 50, 'y' => 68],
        'mouth_corner_left' => ['name_ar' => 'زاوية الفم اليسرى', 'name_en' => 'Left Mouth Corner', 'x' => 38, 'y' => 65],
        'mouth_corner_right' => ['name_ar' => 'زاوية الفم اليمنى', 'name_en' => 'Right Mouth Corner', 'x' => 62, 'y' => 65],
        'chin_center' => ['name_ar' => 'منتصف الذقن', 'name_en' => 'Chin Center', 'x' => 50, 'y' => 78],
        'chin_left' => ['name_ar' => 'الذقن الأيسر', 'name_en' => 'Left Chin', 'x' => 40, 'y' => 80],
        'chin_right' => ['name_ar' => 'الذقن الأيمن', 'name_en' => 'Right Chin', 'x' => 60, 'y' => 80],
        'jawline_left' => ['name_ar' => 'خط الفك الأيسر', 'name_en' => 'Left Jawline', 'x' => 15, 'y' => 75],
        'jawline_right' => ['name_ar' => 'خط الفك الأيمن', 'name_en' => 'Right Jawline', 'x' => 85, 'y' => 75],
        'neck_upper' => ['name_ar' => 'الرقبة العلوية', 'name_en' => 'Upper Neck', 'x' => 50, 'y' => 92],
    ];

    private const SPECTRAL_MODES = [
        'rgb' => ['name' => 'RGB White Light', 'name_ar' => 'الضوء الأبيض RGB', 'detects' => ['surface_pores', 'wrinkles', 'skin_tone']],
        'cross_polarized' => ['name' => 'Cross-Polarized', 'name_ar' => 'الضوء المستقطب المتقاطع', 'detects' => ['deep_pigmentation', 'vascular_redness', 'melasma']],
        'parallel_polarized' => ['name' => 'Parallel-Polarized', 'name_ar' => 'الضوء المستقطب المتوازي', 'detects' => ['surface_texture', 'fine_lines', 'shininess']],
        'uv' => ['name' => 'UV Light', 'name_ar' => 'الضوء فوق البنفسجي', 'detects' => ['sun_damage', 'porphyrins', 'sebum']],
    ];

    public function getAllConditions(): array
    {
        return self::CONDITIONS;
    }

    public function getCondition(string $key): ?array
    {
        return self::CONDITIONS[$key] ?? null;
    }

    public function getCategories(): array
    {
        return self::CATEGORIES;
    }

    public function getFacialZones(): array
    {
        return self::FACIAL_ZONES;
    }

    public function getSpectralModes(): array
    {
        return self::SPECTRAL_MODES;
    }

    public function getConditionsByCategory(string $category): array
    {
        return array_filter(self::CONDITIONS, fn($c) => ($c['category'] ?? '') === $category);
    }

    public function getConditionsRequiringMedical(): array
    {
        return array_filter(self::CONDITIONS, fn($c) => $c['requires_medical'] ?? false);
    }

    public function getAllDefectKeys(): array
    {
        return array_keys(self::CONDITIONS);
    }

    public function detectFromFeatures(array $features): array
    {
        $detected = [];

        if (($features['acne_index'] ?? 0) > 0.4) {
            $detected[] = array_merge(
                self::CONDITIONS['acne_inflammatory'],
                ['key' => 'acne_inflammatory', 'confidence' => $features['acne_index']]
            );
        }

        if (($features['pigmentation_index'] ?? 0) > 0.5) {
            $detected[] = array_merge(
                self::CONDITIONS['solar_lentigo'],
                ['key' => 'solar_lentigo', 'confidence' => $features['pigmentation_index']]
            );
        }

        if (($features['redness_index'] ?? 0) > 0.5) {
            $detected[] = array_merge(
                self::CONDITIONS['facial_erythema'],
                ['key' => 'facial_erythema', 'confidence' => $features['redness_index']]
            );
        }

        if (($features['wrinkle_index'] ?? 0) > 0.4) {
            $detected[] = array_merge(
                self::CONDITIONS['fine_lines_superficial'],
                ['key' => 'fine_lines_superficial', 'confidence' => $features['wrinkle_index']]
            );
        }

        if (($features['pore_index'] ?? 0) > 0.5) {
            $detected[] = array_merge(
                self::CONDITIONS['dilated_pores'],
                ['key' => 'dilated_pores', 'confidence' => $features['pore_index']]
            );
        }

        if (($features['oil_index'] ?? 0) > 0.6) {
            $detected[] = array_merge(
                self::CONDITIONS['seborrhea'],
                ['key' => 'seborrhea', 'confidence' => $features['oil_index']]
            );
        }

        if (($features['dryness_index'] ?? 0) > 0.5) {
            $detected[] = array_merge(
                self::CONDITIONS['dry_skin'],
                ['key' => 'dry_skin', 'confidence' => $features['dryness_index']]
            );
        }

        if (($features['texture_index'] ?? 0) > 0.5) {
            $detected[] = array_merge(
                self::CONDITIONS['rough_texture'],
                ['key' => 'rough_texture', 'confidence' => $features['texture_index']]
            );
        }

        if (($features['elasticity_index'] ?? 0) < 0.4) {
            $detected[] = array_merge(
                self::CONDITIONS['wrinkles_static'],
                ['key' => 'wrinkles_static', 'confidence' => 1 - ($features['elasticity_index'] ?? 0)]
            );
        }

        if (($features['uv_damage_index'] ?? 0) > 0.4) {
            $detected[] = array_merge(
                self::CONDITIONS['uv_damage_visible'],
                ['key' => 'uv_damage_visible', 'confidence' => $features['uv_damage_index']]
            );
        }

        return $detected;
    }

    public function getIngredientsForType(string $type): array
    {
        return $this->getIngredientRecommendations([$type]);
    }

    public function getIngredientRecommendations(array $defectKeys): array
    {
        $ingredients = [];
        foreach ($defectKeys as $key) {
            $condition = self::CONDITIONS[$key] ?? null;
            if ($condition && !empty($condition['ingredients'])) {
                $ingredients = array_merge($ingredients, $condition['ingredients']);
            }
        }
        return array_values(array_unique($ingredients));
    }

    public function getProductCategoryRecommendations(array $defectKeys): array
    {
        $categories = [];
        foreach ($defectKeys as $key) {
            $condition = self::CONDITIONS[$key] ?? null;
            if ($condition && !empty($condition['product_categories'])) {
                $categories = array_merge($categories, $condition['product_categories']);
            }
        }
        return array_values(array_unique($categories));
    }

    public function getCareTips(array $defectKeys, string $lang = 'ar'): array
    {
        $tips = [];
        foreach ($defectKeys as $key) {
            $condition = self::CONDITIONS[$key] ?? null;
            if ($condition) {
                $tips[] = $lang === 'ar' ? $condition['tip_ar'] : $condition['tip_en'];
            }
        }
        return $tips;
    }
}
