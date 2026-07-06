package com.ebtikar.skinanalyzer.data.knowledge

import kotlinx.serialization.Serializable
import kotlinx.serialization.json.Json

@Serializable
data class SkinKnowledgeBase(
    val version: Int = 1,
    val lastUpdated: String = "",
    val url: String = "",
    val metrics: Map<String, MetricKnowledge> = emptyMap(),
    val skinTypes: Map<String, SkinTypeInfo> = emptyMap(),
    val fitzpatrick: Map<String, FitzpatrickInfo> = emptyMap(),
    val skinConditions: Map<String, SkinConditionInfo> = emptyMap(),
    val ingredients: Map<String, IngredientInfo> = emptyMap(),
    val routines: Map<String, RoutineInfo> = emptyMap(),
    val products: List<ProductInfo> = emptyList(),
    val generalTips: List<String> = emptyList()
)

@Serializable
data class MetricKnowledge(
    val displayNameAr: String = "",
    val descriptionAr: String = "",
    val descriptions: Map<String, String> = emptyMap(),
    val causesAr: List<String> = emptyList(),
    val tipsAr: List<String> = emptyList(),
    val ingredientsAr: List<String> = emptyList(),
    val relatedConditions: List<String> = emptyList(),
    val zone: String = "FULL_FACE"
)

@Serializable
data class SkinTypeInfo(
    val nameAr: String = "",
    val characteristicsAr: String = "",
    val routineAr: RoutineSteps = RoutineSteps(),
    val avoidAr: List<String> = emptyList()
)

@Serializable
data class RoutineSteps(
    val morning: List<String> = emptyList(),
    val evening: List<String> = emptyList(),
    val weekly: List<String> = emptyList()
)

@Serializable
data class FitzpatrickInfo(
    val nameAr: String = "",
    val characteristicsAr: String = "",
    val recommendationsAr: String = ""
)

@Serializable
data class SkinConditionInfo(
    val nameAr: String = "",
    val descriptionAr: String = "",
    val types: List<ConditionType> = emptyList(),
    val severityDescriptionAr: Map<String, String> = emptyMap(),
    val treatmentsAr: List<String> = emptyList(),
    val whenToSeeDoctorAr: String = ""
)

@Serializable
data class ConditionType(
    val nameAr: String = "",
    val descriptionAr: String = ""
)

@Serializable
data class IngredientInfo(
    val nameAr: String = "",
    val descriptionAr: String = "",
    val benefitsAr: List<String> = emptyList(),
    val bestForMetrics: List<String> = emptyList(),
    val usageAr: String = "",
    val warningsAr: String = ""
)

@Serializable
data class RoutineInfo(
    val nameAr: String = "",
    val stepsAr: List<RoutineStep> = emptyList()
)

@Serializable
data class RoutineStep(
    val order: Int = 0,
    val nameAr: String = "",
    val descriptionAr: String = ""
)

@Serializable
data class ProductInfo(
    val id: String = "",
    val name: String = "",
    val nameAr: String = "",
    val brand: String = "",
    val category: String = "",
    val price: Float = 0f,
    val currency: String = "ILS",
    val matchScore: Float = 0f,
    val reason: String = "",
    val reasonAr: String = "",
    val bestForMetrics: List<String> = emptyList()
)

object SkinKnowledgeJson {
    val json = Json {
        ignoreUnknownKeys = true
        encodeDefaults = true
    }

    fun parse(raw: String): SkinKnowledgeBase {
        return json.decodeFromString<SkinKnowledgeBase>(raw)
    }
}
