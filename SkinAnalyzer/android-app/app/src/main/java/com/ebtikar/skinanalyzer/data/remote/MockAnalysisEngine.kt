package com.ebtikar.skinanalyzer.data.remote

import com.ebtikar.skinanalyzer.core.provider.AnalysisResult
import com.ebtikar.skinanalyzer.data.knowledge.SkinKnowledgeRepository
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.MetricTrend
import com.ebtikar.skinanalyzer.model.ProductRecommendation
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinProfile
import kotlinx.coroutines.delay
import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class MockAnalysisEngine @Inject constructor(
    private val knowledgeRepository: SkinKnowledgeRepository
) {

    suspend fun generateMockResult(providerName: String): AnalysisResult {
        delay(1500)

        val metrics = mutableMapOf<SkinMetric.Type, SkinMetric>()
        val knowledge = knowledgeRepository.getKnowledge()

        val spectrumMetrics = mapOf(
            SkinMetric.Type.MOISTURE to (60..90).random().toFloat(),
            SkinMetric.Type.PORES to (55..85).random().toFloat(),
            SkinMetric.Type.SEBUM to (50..80).random().toFloat(),
            SkinMetric.Type.WRINKLES to (55..88).random().toFloat(),
            SkinMetric.Type.TEXTURE to (65..90).random().toFloat(),
            SkinMetric.Type.UV_SPOTS to (60..88).random().toFloat(),
            SkinMetric.Type.VASCULAR to (62..90).random().toFloat(),
            SkinMetric.Type.PIGMENTATION to (58..87).random().toFloat(),
            SkinMetric.Type.DARK_CIRCLES to (48..80).random().toFloat(),
            SkinMetric.Type.BLACKHEADS to (45..78).random().toFloat(),
            SkinMetric.Type.ACNE to (50..82).random().toFloat(),
            SkinMetric.Type.SKIN_TONE to (70..92).random().toFloat(),
            SkinMetric.Type.SENSITIVITY to (55..85).random().toFloat(),
            SkinMetric.Type.ROSACEA to (50..85).random().toFloat(),
            SkinMetric.Type.MELASMA to (48..84).random().toFloat()
        )

        for ((type, score) in spectrumMetrics) {
            val severity = classifyMockScore(score)
            val previousScore = (score + (-15..15).random()).coerceIn(0f, 100f)
            val trend = when {
                score > previousScore + 3 -> MetricTrend.IMPROVING
                score < previousScore - 3 -> MetricTrend.DECLINING
                else -> MetricTrend.STABLE
            }
            metrics[type] = SkinMetric(
                type = type,
                score = score,
                severity = severity,
                details = getDetailsForMetric(type, score, severity, knowledge),
                recommendations = getRecommendationsForMetric(type, severity, knowledge),
                trend = trend,
                previousScore = previousScore,
                confidence = (75..95).random() / 100f
            )
        }

        Timber.i("Mock analysis generated: ${metrics.size} metrics")

        return AnalysisResult(
            providerName = providerName,
            executionTimeMs = 1500,
            metrics = metrics,
            confidence = 0.85f
        )
    }

    fun generateSkinProfile(metrics: Map<SkinMetric.Type, SkinMetric>): SkinProfile {
        val moisture = metrics[SkinMetric.Type.MOISTURE]?.score ?: 50f
        val sebum = metrics[SkinMetric.Type.SEBUM]?.score ?: 50f
        val sensitivity = metrics[SkinMetric.Type.SENSITIVITY]?.score ?: 50f

        val skinType = when {
            moisture < 40 && sebum > 60 -> "dehydrated_oily"
            moisture < 40 && sebum < 40 -> "dry"
            moisture > 60 && sebum > 60 -> "oily"
            moisture > 60 && sebum < 40 -> "normal"
            else -> "mixed"
        }

        val skinTypeAr = when (skinType) {
            "dehydrated_oily" -> "دهنية جافة"
            "dry" -> "جافة"
            "oily" -> "دهنية"
            "normal" -> "عادية"
            else -> "مختلطة"
        }

        val concerns = mutableListOf<String>()
        val concernsAr = mutableListOf<String>()

        metrics.entries
            .filter { it.value.score < 65f }
            .sortedBy { it.value.score }
            .take(3)
            .forEach { (type, _) ->
                concerns.add(type.name.lowercase().replace("_", " "))
                concernsAr.add(type.arabicName())
            }

        return SkinProfile(
            skinType = skinType,
            skinTypeAr = skinTypeAr,
            hydrationLevel = when {
                moisture >= 70f -> "high"
                moisture >= 50f -> "moderate"
                else -> "low"
            },
            sensitivityLevel = when {
                sensitivity >= 70f -> "low"
                sensitivity >= 50f -> "moderate"
                else -> "high"
            },
            primaryConcerns = concerns,
            primaryConcernsAr = concernsAr
        )
    }

    fun generateExpertTips(metrics: Map<SkinMetric.Type, SkinMetric>): List<String> {
        val tips = mutableListOf<String>()
        val knowledge = runCatching { knowledgeRepository.getCachedKnowledge() }.getOrNull()
        val sorted = metrics.entries.sortedBy { it.value.score }

        sorted.take(3).forEach { (type, _) ->
            val metricKey = type.name
            val metricTips = knowledge?.metrics?.get(metricKey)?.tipsAr
            if (!metricTips.isNullOrEmpty()) {
                tips.add(metricTips.random())
            } else {
                tips.add(fallbackTip(type))
            }
        }

        tips.add("حافظي على روتين يومي ثابت: تنظيف → تونر → سيروم → مرطب → واقي شمس")
        return tips
    }

    fun generateProductRecommendations(metrics: Map<SkinMetric.Type, SkinMetric>): List<ProductRecommendation> {
        val products = mutableListOf<ProductRecommendation>()
        val knowledge = runCatching { knowledgeRepository.getCachedKnowledge() }.getOrNull()
        val sorted = metrics.entries.sortedBy { it.value.score }

        sorted.take(4).forEach { (type, _) ->
            val matched = knowledge?.products
                ?.filter { type.name in it.bestForMetrics }
                ?.maxByOrNull { it.matchScore }
            if (matched != null) {
                products.add(ProductRecommendation(
                    id = matched.id,
                    name = matched.name,
                    nameAr = matched.nameAr,
                    brand = matched.brand,
                    category = matched.category,
                    price = matched.price,
                    currency = matched.currency,
                    matchScore = matched.matchScore,
                    reason = matched.reason,
                    reasonAr = matched.reasonAr
                ))
            } else {
                products.add(fallbackProduct(type))
            }
        }

        return products.sortedByDescending { it.matchScore }
    }

    private fun getDetailsForMetric(type: SkinMetric.Type, score: Float, severity: MetricSeverity, knowledge: com.ebtikar.skinanalyzer.data.knowledge.SkinKnowledgeBase?): String {
        val metricKey = type.name
        val description = knowledge?.metrics?.get(metricKey)?.descriptions?.get(severity.name)
        if (!description.isNullOrBlank()) return description

        return when (type) {
            SkinMetric.Type.MOISTURE -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "مستوى الرطوبة مثالي — البشرة مرطبة بشكل كافٍ"
                MetricSeverity.FAIR -> "رطوبة متوسطة — تحتاجين لترطيب إضافي"
                else -> "جفاف واضح — البشرة تحتاج لترطيب فوري ومكثف"
            }
            SkinMetric.Type.PORES -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "المسام ضيقة ومنتظمة — مظهر ناعم"
                MetricSeverity.FAIR -> "بعض المسام الواسعة في منطقة T"
                else -> "مسام واسعة وملوحة — تحتاج لعناية مركزة"
            }
            SkinMetric.Type.SEBUM -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "إفراز دهني متوازن"
                MetricSeverity.FAIR -> "زيادة طفيفة في الإفرازات الدهنية"
                else -> "إفراز دهني زائد — بشرة لامعة عرضة لحب الشباب"
            }
            SkinMetric.Type.WRINKLES -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "خطوط دقيقة قليلة جداً — بشرة شابة"
                MetricSeverity.FAIR -> "خطوط تعبير واضحة حول العينين والجبهة"
                else -> "تجاعيد عميقة — تحتاج روتين مضاد للشيخوخة"
            }
            SkinMetric.Type.TEXTURE -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "ملمس ناعم ومتجانس"
                MetricSeverity.FAIR -> "خشونة خفيفة في بعض المناطق"
                else -> "ملمس خشن وغير متساوٍ — يحتاج تقشير منتظم"
            }
            SkinMetric.Type.UV_SPOTS -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد أضرار شمس واضحة"
                MetricSeverity.FAIR -> "بقع شمس خفيفة — استخدمي واقي شمس"
                else -> "أضرار شمس متقدمة — تحتاج علاج تصبغي"
            }
            SkinMetric.Type.VASCULAR -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "دورة دموية صحية — لا احمرار"
                MetricSeverity.FAIR -> "احمرار خفيف في الخدود"
                else -> "احمرار واضح وأوعية دموية بارزة"
            }
            SkinMetric.Type.PIGMENTATION -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لون بشرة موحد ومتجانس"
                MetricSeverity.FAIR -> "تصبغات خفيفة متفرقة"
                else -> "تصبغات غامقة واسعة الانتشار"
            }
            SkinMetric.Type.DARK_CIRCLES -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "منطقة العين مشرقة"
                MetricSeverity.FAIR -> "هالات خفيفة تحت العين"
                else -> "هالات داكنة واضحة تحت العين"
            }
            SkinMetric.Type.BLACKHEADS -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "مسام نظيفة من الرؤوس السوداء"
                MetricSeverity.FAIR -> "رؤوس سوداء خفيفة في الأنف والذقن"
                else -> "انتشار واسع للرؤوس السوداء"
            }
            SkinMetric.Type.ACNE -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد بثور نشطة"
                MetricSeverity.FAIR -> "بثور خفيفة متفرقة"
                else -> "حب شباب نشط والتهابات واضحة"
            }
            SkinMetric.Type.SKIN_TONE -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لون بشرة متجانس ومشرق"
                MetricSeverity.FAIR -> "اختلافات طفيفة في اللون"
                else -> "عدم تجانس واضح في لون البشرة"
            }
            SkinMetric.Type.SENSITIVITY -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "بشرة غير حساسة — تتحمل المنتجات"
                MetricSeverity.FAIR -> "حساسية خفيفة لبعض المكونات"
                else -> "بشرة شديدة الحساسية — استخدمي منتجات لطيفة"
            }
            SkinMetric.Type.ROSACEA -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد علامات وردية — بشرة صافية"
                MetricSeverity.FAIR -> "احمرار خفيف قد يكون بداية وردية"
                else -> "علامات وردية واضحة — التهاب واحمرار مزمن"
            }
            SkinMetric.Type.MELASMA -> when (severity) {
                MetricSeverity.EXCELLENT, MetricSeverity.GOOD -> "لا توجد علامات كلف — تصبغ موحد"
                MetricSeverity.FAIR -> "تصبغ خفيف في بعض المناطق"
                else -> "كلف عميق واسع الانتشار — يحتاج علاج مكثف"
            }
        }
    }

    private fun getRecommendationsForMetric(type: SkinMetric.Type, severity: MetricSeverity, knowledge: com.ebtikar.skinanalyzer.data.knowledge.SkinKnowledgeBase?): List<String> {
        if (severity == MetricSeverity.EXCELLENT || severity == MetricSeverity.GOOD) {
            return listOf("حافظي على روتينك الحالي — النتائج ممتازة")
        }

        val metricKey = type.name
        val tips = knowledge?.metrics?.get(metricKey)?.tipsAr
        if (!tips.isNullOrEmpty()) {
            return tips.take(3).ifEmpty {
                listOf("حافظي على روتينك الحالي — النتائج ممتازة")
            }
        }

        return when (type) {
            SkinMetric.Type.MOISTURE -> listOf(
                "استخدمي سيروم حمض الهيالورونيك صباحاً ومساءً",
                "اشربي 8 أكواب ماء يومياً على الأقل",
                "تجنبي الغسول الذي يحتوي على كحول"
            )
            SkinMetric.Type.PORES -> listOf(
                "استخدمي غسول BHA مرتين أسبوعياً",
                "طبقي ماسك الطين أسبوعياً",
                "استخدمي برايمر مسام قبل المكياج"
            )
            SkinMetric.Type.SEBUM -> listOf(
                "استخدمي سيروم النياسيناميد 10%",
                "تجنبي الكريمات الثقيلة واستبدليها بجل خفيف",
                "اغسلي وجهك مرتين يومياً بغسول لطيف"
            )
            SkinMetric.Type.WRINKLES -> listOf(
                "ابدئي باستخدام الريتينول تدريجياً ليلاً",
                "واظبي على واقي الشمس SPF 50+ يومياً",
                "استخدمي كريم عين غني بالببتيدات"
            )
            SkinMetric.Type.TEXTURE -> listOf(
                "قشري بحمض الجليكوليك مرتين أسبوعياً",
                "استخدمي سيروم AHA + BHA",
                "رطبي بشرتك بعد التقشير مباشرة"
            )
            SkinMetric.Type.UV_SPOTS -> listOf(
                "واقي شمس SPF 50+ كل يوم بدون استثناء",
                "سيروم فيتامين سي صباحاً لتفتيح البقع",
                "راجعي طبيب جلدية للبقع الداكنة العميقة"
            )
            SkinMetric.Type.VASCULAR -> listOf(
                "تجنبي الماء الساخن على الوجه",
                "استخدمي منتجات Centella Asiatica المهدئة",
                "تجنبي التقشير القوي والمنتجات المهيجة"
            )
            SkinMetric.Type.PIGMENTATION -> listOf(
                "سيروم الأربوتين + فيتامين سي مساءً",
                "واقي شمس ضروري جداً لمنع التفاقم",
                "فكري في علاج ليزر مع طبيب جلدية"
            )
            SkinMetric.Type.DARK_CIRCLES -> listOf(
                "كريم عين بالكافيين صباحاً ومساءً",
                "نامي 7-8 ساعات يومياً",
                "استخدمي كمادات باردة للعين صباحاً"
            )
            SkinMetric.Type.BLACKHEADS -> listOf(
                "نظفي بشرتك بغسول يحتوي على BHA",
                "استخدمي شرائح الأنف أسبوعياً",
                "طبقي زيت شجرة الشاي على المناطق المصابة"
            )
            SkinMetric.Type.ACNE -> listOf(
                "استخدمي بنزويل بيروكسايد 2.5% على البثور",
                "تجنبي لمس الوجه وتغيير أغطية الوسائد أسبوعياً",
                "راجعي طبيب جلدية إذا استمر الانتشار"
            )
            SkinMetric.Type.SKIN_TONE -> listOf(
                "سيروم فيتامين سي 15-20% صباحاً",
                "تقشير خفيف بحمض اللاكتيك أسبوعياً",
                "واقي شمس ملون لتوحيد اللون فوراً"
            )
            SkinMetric.Type.SENSITIVITY -> listOf(
                "استخدمي منتجات خالية من العطور والكحول",
                "اختبري أي منتج جديد على منطقة صغيرة أولاً",
                "استخدمي كريم حاجز يحتوي على سيراميد"
            )
            SkinMetric.Type.ROSACEA -> listOf(
                "تجنبي الأطعمة الحارة والمشروبات الساخنة التي تزيد الوردية",
                "استخدمي واقي شمس معدني SPF 50+ يومياً",
                "طبقي سيروم النياسيناميد لتهدئة الاحمرار"
            )
            SkinMetric.Type.MELASMA -> listOf(
                "واقي شمس صارم SPF 50+ كل ساعتين — الكلف يتفاقم مع الشمس",
                "استخدمي كريم تفتيح يحتوي على الأربوتين أو حمض الكوجيك",
                "فكري في علاجات التقشير الكيميائي أو الليزر مع طبيب جلدية"
            )
        }
    }

    private fun fallbackTip(type: SkinMetric.Type): String = when (type) {
        SkinMetric.Type.MOISTURE -> "اشربي كمية كافية من الماء يومياً (8 أكواب) واستخدمي مرطباً يحتوي على حمض الهيالورونيك"
        SkinMetric.Type.PORES -> "استخدمي غسولاً يحتوي على حمض الساليسيليك لتنظيف المسام بعمق مرتين أسبوعياً"
        SkinMetric.Type.SEBUM -> "تجنبي المنتجات الدهنية واستخدمي سيروم النياسيناميد للتحكم في الإفرازات الدهنية"
        SkinMetric.Type.WRINKLES -> "واظبي على استخدام كريم مضاد للتجاعيد يحتوي على الريتينول ليلاً وواقي شمس نهاراً"
        SkinMetric.Type.TEXTURE -> "قشري بشرتك بلطف مرة إلى مرتين أسبوعياً باستخدام مقشر كيميائي يحتوي على AHA"
        SkinMetric.Type.UV_SPOTS -> "استخدمي واقي شمس SPF 50+ يومياً حتى في الأيام الغائمة وأعيدي وضعه كل ساعتين"
        SkinMetric.Type.VASCULAR -> "تجنبي الماء الساخن على الوجه واستخدمي منتجات مهدئة تحتوي على Centella Asiatica"
        SkinMetric.Type.PIGMENTATION -> "استخدمي سيروم فيتامين سي صباحاً وواقي شمس باستمرار لتوحيد لون البشرة"
        SkinMetric.Type.DARK_CIRCLES -> "احصلي على نوم كافٍ (7-8 ساعات) واستخدمي كريم عين يحتوي على الكافيين"
        SkinMetric.Type.BLACKHEADS -> "استخدمي ماسك الطين الأسبوعي ومنتجات BHA لتنظيف المسام من الرؤوس السوداء"
        SkinMetric.Type.ACNE -> "تجنبي لمس الوجه واستخدمي منتجات خالية من الزيوت وعلاج موضعي بحمض الأزيلايك"
        SkinMetric.Type.SKIN_TONE -> "واظبي على روتين توحيد اللون مع سيروم الأربوتين وفيتامين سي صباحاً"
        SkinMetric.Type.SENSITIVITY -> "استخدمي منتجات خالية من العطور والكحول واختبري أي منتج جديد على منطقة صغيرة أولاً"
        SkinMetric.Type.ROSACEA -> "الوردية تحتاج عناية خاصة — تجنبي المهيجات الحرارية واستخدمي منتجات مهدئة تحتوي على النياسيناميد والأزولين"
        SkinMetric.Type.MELASMA -> "الكلف العميق يحتاج إلى واقي شمس صارم SPF 50+ يومياً وعلاج موضعي بمثبطات التيروزيناز مثل الأربوتين"
    }

    private fun fallbackProduct(type: SkinMetric.Type): ProductRecommendation = when (type) {
        SkinMetric.Type.MOISTURE -> ProductRecommendation(
            id = "krym-trtyb", name = "Moisturizing Cream", nameAr = "كريم ترطيب",
            brand = "JeniCare", category = "moisturizer", price = 90f, currency = "ILS",
            matchScore = 0.95f, reason = "Deep hydration", reasonAr = "ترطيب عميق للبشرة الجافة",
            shopUrl = "https://jenincare.shop/public/product/krym-trtyb"
        )
        SkinMetric.Type.PORES -> ProductRecommendation(
            id = "syrom-llmsamat-aark-sos", name = "Pore Serum Licorice", nameAr = "سيروم للمسامات عرق سوس",
            brand = "JeniCare", category = "serum", price = 20f, currency = "ILS",
            matchScore = 0.92f, reason = "Pore cleansing", reasonAr = "تنظيف عميق وتقليص المسام",
            shopUrl = "https://jenincare.shop/public/product/syrom-llmsamat-aark-sos"
        )
        SkinMetric.Type.WRINKLES -> ProductRecommendation(
            id = "syrom-kolagyn", name = "Collagen Serum", nameAr = "سيروم كولاجين",
            brand = "JeniCare", category = "serum", price = 35f, currency = "ILS",
            matchScore = 0.90f, reason = "Anti-aging", reasonAr = "مكافحة التجاعيد وشد البشرة",
            shopUrl = "https://jenincare.shop/public/product/syrom-kolagyn"
        )
        SkinMetric.Type.UV_SPOTS -> ProductRecommendation(
            id = "oaky-shms", name = "Sunscreen SPF 50+", nameAr = "واقي شمس",
            brand = "JeniCare", category = "sunscreen", price = 35f, currency = "ILS",
            matchScore = 0.93f, reason = "UV protection", reasonAr = "حماية فائقة من أشعة الشمس",
            shopUrl = "https://jenincare.shop/public/product/oaky-shms"
        )
        SkinMetric.Type.PIGMENTATION -> ProductRecommendation(
            id = "krym-tftyh", name = "Whitening Cream", nameAr = "كريم تفتيح",
            brand = "JeniCare", category = "cream", price = 40f, currency = "ILS",
            matchScore = 0.91f, reason = "Brightening", reasonAr = "تفتيح وتوحيد لون البشرة",
            shopUrl = "https://jenincare.shop/public/product/krym-tftyh"
        )
        SkinMetric.Type.ACNE -> ProductRecommendation(
            id = "knaaa-asod", name = "Black Mask", nameAr = "قناع اسود",
            brand = "JeniCare", category = "mask", price = 10f, currency = "ILS",
            matchScore = 0.88f, reason = "Acne treatment", reasonAr = "علاج حب الشباب والالتهابات",
            shopUrl = "https://jenincare.shop/public/product/knaaa-asod"
        )
        SkinMetric.Type.DARK_CIRCLES -> ProductRecommendation(
            id = "krym-fytamyn-c-llaayon", name = "Vitamin C Eye Cream", nameAr = "كريم فيتامين سي للعيون",
            brand = "JeniCare", category = "eye_care", price = 20f, currency = "ILS",
            matchScore = 0.89f, reason = "Dark circle reduction", reasonAr = "تقليل الهالات الداكنة حول العين",
            shopUrl = "https://jenincare.shop/public/product/krym-fytamyn-c-llaayon"
        )
        SkinMetric.Type.ROSACEA -> ProductRecommendation(
            id = "larosh-syrom", name = "La Roche Serum", nameAr = "لاروش سيروم",
            brand = "La Roche-Posay", category = "serum", price = 50f, currency = "ILS",
            matchScore = 0.89f, reason = "Soothing redness", reasonAr = "تهدئة الاحمرار وعلاج الوردية",
            shopUrl = "https://jenincare.shop/public/product/larosh-syrom"
        )
        SkinMetric.Type.MELASMA -> ProductRecommendation(
            id = "syrom-aark-sos", name = "Licorice Serum", nameAr = "سيروم عرق سوس",
            brand = "JeniCare", category = "serum", price = 20f, currency = "ILS",
            matchScore = 0.88f, reason = "Melasma treatment", reasonAr = "علاج الكلف والتصبغات العميقة",
            shopUrl = "https://jenincare.shop/public/product/syrom-aark-sos"
        )
        SkinMetric.Type.TEXTURE -> ProductRecommendation(
            id = "mkshr-ogh", name = "Face Scrub", nameAr = "مقشر وجه",
            brand = "JeniCare", category = "exfoliant", price = 18f, currency = "ILS",
            matchScore = 0.87f, reason = "Skin smoothing", reasonAr = "تنعيم الملمس وإزالة الخلايا الميتة",
            shopUrl = "https://jenincare.shop/public/product/mkshr-ogh"
        )
        SkinMetric.Type.SEBUM -> ProductRecommendation(
            id = "ghsol-alogh-almrtb-mn-cerave", name = "CeraVe Face Wash", nameAr = "غسول الوجه المرطب من CeraVe",
            brand = "CeraVe", category = "cleanser", price = 18f, currency = "ILS",
            matchScore = 0.90f, reason = "Oil control", reasonAr = "تنظيف لطيف والتحكم في الدهون",
            shopUrl = "https://jenincare.shop/public/product/ghsol-alogh-almrtb-mn-cerave"
        )
        SkinMetric.Type.BLACKHEADS -> ProductRecommendation(
            id = "maknh-zoan", name = "Blackhead Remover", nameAr = "ماكنه زوان",
            brand = "JeniCare", category = "tool", price = 25f, currency = "ILS",
            matchScore = 0.86f, reason = "Blackhead removal", reasonAr = "إزالة الرؤوس السوداء",
            shopUrl = "https://jenincare.shop/public/product/maknh-zoan"
        )
        SkinMetric.Type.VASCULAR -> ProductRecommendation(
            id = "larosh-syrom", name = "La Roche Serum", nameAr = "لاروش سيروم",
            brand = "La Roche-Posay", category = "serum", price = 50f, currency = "ILS",
            matchScore = 0.89f, reason = "Vascular soothing", reasonAr = "تهدئة الأوعية الدموية والاحمرار",
            shopUrl = "https://jenincare.shop/public/product/larosh-syrom"
        )
        SkinMetric.Type.SKIN_TONE -> ProductRecommendation(
            id = "mrtb-fytamyn-sy", name = "Vitamin C Moisturizer", nameAr = "مرطب فيتامين سي",
            brand = "JeniCare", category = "moisturizer", price = 25f, currency = "ILS",
            matchScore = 0.88f, reason = "Skin tone evening", reasonAr = "توحيد لون البشرة وإشراقتها",
            shopUrl = "https://jenincare.shop/public/product/mrtb-fytamyn-sy"
        )
        SkinMetric.Type.SENSITIVITY -> ProductRecommendation(
            id = "ghsol-alogh-almrtb-mn-cerave", name = "CeraVe Face Wash", nameAr = "غسول الوجه المرطب من CeraVe",
            brand = "CeraVe", category = "cleanser", price = 18f, currency = "ILS",
            matchScore = 0.90f, reason = "Gentle cleansing", reasonAr = "تنظيف لطيف للبشرة الحساسة",
            shopUrl = "https://jenincare.shop/public/product/ghsol-alogh-almrtb-mn-cerave"
        )
    }

    private fun classifyMockScore(score: Float): MetricSeverity {
        return when {
            score >= 72f -> MetricSeverity.EXCELLENT
            score >= 55f -> MetricSeverity.GOOD
            score >= 35f -> MetricSeverity.FAIR
            score >= 20f -> MetricSeverity.POOR
            else -> MetricSeverity.CRITICAL
        }
    }

    private fun SkinMetric.Type.arabicName(): String = when (this) {
        SkinMetric.Type.MOISTURE -> "الرطوبة"
        SkinMetric.Type.PORES -> "المسام"
        SkinMetric.Type.SEBUM -> "الدهنية"
        SkinMetric.Type.WRINKLES -> "التجاعيد"
        SkinMetric.Type.TEXTURE -> "الملمس"
        SkinMetric.Type.UV_SPOTS -> "البقع الضوئية"
        SkinMetric.Type.VASCULAR -> "الأوعية الدموية"
        SkinMetric.Type.PIGMENTATION -> "التصبغ"
        SkinMetric.Type.DARK_CIRCLES -> "الهالات الداكنة"
        SkinMetric.Type.BLACKHEADS -> "الرؤوس السوداء"
        SkinMetric.Type.ACNE -> "حب الشباب"
        SkinMetric.Type.SKIN_TONE -> "لون البشرة"
        SkinMetric.Type.SENSITIVITY -> "الحساسية"
        SkinMetric.Type.ROSACEA -> "الوردية"
        SkinMetric.Type.MELASMA -> "الكلف"
    }
}
