package com.ebtikar.skinanalyzer.ui.report

import android.animation.ValueAnimator
import android.content.res.ColorStateList
import android.graphics.Color
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.DecelerateInterpolator
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ItemMetricCardBinding
import com.ebtikar.skinanalyzer.databinding.ItemZoneHeaderBinding
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.MetricTrend
import com.ebtikar.skinanalyzer.model.SkinMetric
import com.ebtikar.skinanalyzer.model.SkinZone
import com.ebtikar.skinanalyzer.model.arabicName
import com.ebtikar.skinanalyzer.model.iconRes

sealed class ReportListItem {
    data class ZoneHeader(val zone: SkinZone, val zoneNameAr: String, val emoji: String) : ReportListItem()
    data class MetricItem(val metric: SkinMetric) : ReportListItem()
}

class ReportMetricAdapter : ListAdapter<ReportListItem, RecyclerView.ViewHolder>(ReportItemDiffCallback()) {

    companion object {
        private const val TYPE_ZONE_HEADER = 0
        private const val TYPE_METRIC = 1
    }

    override fun getItemViewType(position: Int): Int = when (getItem(position)) {
        is ReportListItem.ZoneHeader -> TYPE_ZONE_HEADER
        is ReportListItem.MetricItem -> TYPE_METRIC
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {
        return when (viewType) {
            TYPE_ZONE_HEADER -> {
                val binding = ItemZoneHeaderBinding.inflate(
                    LayoutInflater.from(parent.context), parent, false
                )
                ZoneHeaderViewHolder(binding)
            }
            else -> {
                val binding = ItemMetricCardBinding.inflate(
                    LayoutInflater.from(parent.context), parent, false
                )
                MetricViewHolder(binding)
            }
        }
    }

    override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {
        when (val item = getItem(position)) {
            is ReportListItem.ZoneHeader -> (holder as ZoneHeaderViewHolder).bind(item)
            is ReportListItem.MetricItem -> (holder as MetricViewHolder).bind(item.metric, position)
        }
    }

    fun submitMetrics(metrics: List<SkinMetric>) {
        val items = mutableListOf<ReportListItem>()
        val grouped = metrics.groupBy { it.zone }
        val zoneOrder = listOf(
            SkinZone.T_ZONE to "منطقة T — الجبهة والأنف",
            SkinZone.U_ZONE to "الخدود والوجنتين",
            SkinZone.EYE_AREA to "منطقة حول العين",
            SkinZone.O_ZONE to "المنطقة الخارجية",
            SkinZone.FULL_FACE to "الوجه بالكامل"
        )
        val zoneEmoji = mapOf(
            SkinZone.T_ZONE to "🔹",
            SkinZone.U_ZONE to "🔸",
            SkinZone.EYE_AREA to "👁",
            SkinZone.O_ZONE to "◾",
            SkinZone.FULL_FACE to "🔹"
        )

        for ((zone, name) in zoneOrder) {
            val zoneMetrics = grouped[zone]?.sortedBy { it.score } ?: continue
            if (zoneMetrics.isEmpty()) continue
            items.add(ReportListItem.ZoneHeader(zone, name, zoneEmoji[zone] ?: ""))
            for (m in zoneMetrics) {
                items.add(ReportListItem.MetricItem(m))
            }
        }

        submitList(items)
    }

    class ZoneHeaderViewHolder(
        private val binding: ItemZoneHeaderBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(header: ReportListItem.ZoneHeader) {
            binding.tvZoneName.text = "${header.emoji} ${header.zoneNameAr}"
        }
    }

    class MetricViewHolder(
        private val binding: ItemMetricCardBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(metric: SkinMetric, position: Int) {
            binding.tvMetricName.text = metric.type.arabicName()

            binding.ivMetricIcon.setImageResource(metric.type.iconRes())

            binding.tvMetricValue.text = "%.0f".format(metric.score)

            binding.tvMetricDetail.text = metric.details
            binding.tvMetricDetail.visibility = if (metric.details.isNotEmpty()) View.VISIBLE else View.GONE

            binding.tvConfidence.text = "${(metric.confidence * 100).toInt()}%"

            val delta = metric.trendDelta
            if (delta != null) {
                val sign = if (delta >= 0) "+" else ""
                binding.tvTrend.text = "${sign}${"%.0f".format(delta)}"
                binding.tvTrend.visibility = View.VISIBLE
                val trendColor = when (metric.trend) {
                    MetricTrend.IMPROVING -> "#FF10B981"
                    MetricTrend.DECLINING -> "#FFF43F5E"
                    MetricTrend.STABLE -> "#FF94A3B8"
                }
                binding.tvTrend.setTextColor(Color.parseColor(trendColor))
            } else {
                binding.tvTrend.visibility = View.GONE
            }

            binding.progressMetric.max = 100
            binding.progressMetric.progress = 0

            val scoreColor = metric.severity.colorHex()
            val colorInt = Color.parseColor(scoreColor)
            binding.progressMetric.setIndicatorColor(colorInt)

            ValueAnimator.ofInt(0, metric.score.toInt()).apply {
                duration = 800L
                startDelay = position * 60L
                interpolator = DecelerateInterpolator(1.5f)
                addUpdateListener { anim ->
                    binding.progressMetric.progress = anim.animatedValue as Int
                }
                start()
            }

            val iconBgColor = Color.parseColor(metric.type.iconBgHex())
            binding.cardMetricIcon.setCardBackgroundColor(iconBgColor)
            binding.ivMetricIcon.imageTintList = ColorStateList.valueOf(
                Color.parseColor(metric.type.iconTintHex())
            )

            val (statusText, statusBg, statusText2) = metric.severity.statusInfo()
            binding.tvMetricStatus.text = statusText
            binding.tvMetricStatus.setTextColor(Color.parseColor(statusText2))
            binding.tvMetricStatus.setBackgroundResource(statusBg)

            if (metric.recommendations.isNotEmpty()) {
                binding.containerRecommendations.visibility = View.VISIBLE
                binding.tvRecommendation1.text = "• ${metric.recommendations[0]}"
                if (metric.recommendations.size > 1) {
                    binding.tvRecommendation2.text = "• ${metric.recommendations[1]}"
                    binding.tvRecommendation2.visibility = View.VISIBLE
                } else {
                    binding.tvRecommendation2.visibility = View.GONE
                }
            } else {
                binding.containerRecommendations.visibility = View.GONE
            }
        }

        private fun SkinMetric.Type.iconBgHex(): String = when (this) {
            SkinMetric.Type.MOISTURE     -> "#1A06B6D4"
            SkinMetric.Type.PORES        -> "#1A8B5CF6"
            SkinMetric.Type.SEBUM        -> "#1A84CC16"
            SkinMetric.Type.WRINKLES     -> "#1AF59E0B"
            SkinMetric.Type.TEXTURE      -> "#1A6366F1"
            SkinMetric.Type.UV_SPOTS     -> "#1AEC4899"
            SkinMetric.Type.VASCULAR     -> "#1AEF4444"
            SkinMetric.Type.PIGMENTATION -> "#1AF97316"
            SkinMetric.Type.DARK_CIRCLES -> "#1A475569"
            SkinMetric.Type.BLACKHEADS   -> "#1A64748B"
            SkinMetric.Type.ACNE         -> "#1AF43F5E"
            SkinMetric.Type.SKIN_TONE    -> "#1AFBBF24"
            SkinMetric.Type.SENSITIVITY  -> "#1AEAB308"
            SkinMetric.Type.ROSACEA      -> "#1AE11D48"
            SkinMetric.Type.MELASMA      -> "#1AD35400"
        }

        private fun SkinMetric.Type.iconTintHex(): String = when (this) {
            SkinMetric.Type.MOISTURE     -> "#FF06B6D4"
            SkinMetric.Type.PORES        -> "#FF8B5CF6"
            SkinMetric.Type.SEBUM        -> "#FF84CC16"
            SkinMetric.Type.WRINKLES     -> "#FFF59E0B"
            SkinMetric.Type.TEXTURE      -> "#FF6366F1"
            SkinMetric.Type.UV_SPOTS     -> "#FFEC4899"
            SkinMetric.Type.VASCULAR     -> "#FFEF4444"
            SkinMetric.Type.PIGMENTATION -> "#FFF97316"
            SkinMetric.Type.DARK_CIRCLES -> "#FF94A3B8"
            SkinMetric.Type.BLACKHEADS   -> "#FF94A3B8"
            SkinMetric.Type.ACNE         -> "#FFF43F5E"
            SkinMetric.Type.SKIN_TONE    -> "#FFFBBF24"
            SkinMetric.Type.SENSITIVITY  -> "#FFEAB308"
            SkinMetric.Type.ROSACEA      -> "#FFE11D48"
            SkinMetric.Type.MELASMA      -> "#FFD35400"
        }

        private fun MetricSeverity.colorHex(): String = when (this) {
            MetricSeverity.EXCELLENT -> "#FF10B981"
            MetricSeverity.GOOD      -> "#FF34D399"
            MetricSeverity.FAIR      -> "#FFF59E0B"
            MetricSeverity.POOR      -> "#FFF97316"
            MetricSeverity.CRITICAL  -> "#FFF43F5E"
        }

        private fun MetricSeverity.statusInfo(): Triple<String, Int, String> = when (this) {
            MetricSeverity.EXCELLENT -> Triple("ممتاز",   R.drawable.bg_chip_green,  "#FF10B981")
            MetricSeverity.GOOD      -> Triple("جيد",     R.drawable.bg_chip_green,  "#FF34D399")
            MetricSeverity.FAIR      -> Triple("متوسط",   R.drawable.bg_chip_gold,   "#FFF59E0B")
            MetricSeverity.POOR      -> Triple("ضعيف",    R.drawable.bg_chip_gold,   "#FFF97316")
            MetricSeverity.CRITICAL  -> Triple("خطير",    R.drawable.bg_chip_muted,  "#FFF43F5E")
        }
    }

    class ReportItemDiffCallback : DiffUtil.ItemCallback<ReportListItem>() {
        override fun areItemsTheSame(oldItem: ReportListItem, newItem: ReportListItem): Boolean {
            return when {
                oldItem is ReportListItem.ZoneHeader && newItem is ReportListItem.ZoneHeader ->
                    oldItem.zone == newItem.zone
                oldItem is ReportListItem.MetricItem && newItem is ReportListItem.MetricItem ->
                    oldItem.metric.type == newItem.metric.type
                else -> false
            }
        }

        override fun areContentsTheSame(oldItem: ReportListItem, newItem: ReportListItem): Boolean {
            return oldItem == newItem
        }
    }
}
