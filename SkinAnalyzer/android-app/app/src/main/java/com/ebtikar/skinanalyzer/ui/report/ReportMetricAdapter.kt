package com.ebtikar.skinanalyzer.ui.report

import android.animation.ValueAnimator
import android.content.res.ColorStateList
import android.graphics.Color
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.DecelerateInterpolator
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ItemMetricCardBinding
import com.ebtikar.skinanalyzer.model.MetricSeverity
import com.ebtikar.skinanalyzer.model.MetricTrend
import com.ebtikar.skinanalyzer.model.SkinMetric

class ReportMetricAdapter : ListAdapter<SkinMetric, ReportMetricAdapter.MetricViewHolder>(MetricDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): MetricViewHolder {
        val binding = ItemMetricCardBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MetricViewHolder(binding)
    }

    override fun onBindViewHolder(holder: MetricViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class MetricViewHolder(
        private val binding: ItemMetricCardBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(metric: SkinMetric) {
            binding.tvMetricName.text = metric.type.arabicName()

            val iconRes = metric.type.iconRes()
            binding.ivMetricIcon.setImageResource(iconRes)

            binding.tvMetricValue.text = "%.0f".format(metric.score)

            binding.tvMetricDetail.text = metric.details
            binding.tvMetricDetail.visibility = if (metric.details.isNotEmpty()) View.VISIBLE else View.GONE

            binding.tvConfidence.text = "${(metric.confidence * 100).toInt()}%"

            if (metric.trendDelta != null) {
                val delta = metric.trendDelta!!
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
                startDelay = adapterPosition * 60L
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

        private fun SkinMetric.Type.arabicName(): String = when (this) {
            SkinMetric.Type.MOISTURE      -> "الرطوبة"
            SkinMetric.Type.PORES         -> "المسام"
            SkinMetric.Type.SEBUM         -> "الدهنية"
            SkinMetric.Type.WRINKLES      -> "التجاعيد"
            SkinMetric.Type.TEXTURE       -> "الملمس"
            SkinMetric.Type.UV_SPOTS      -> "البقع الضوئية"
            SkinMetric.Type.VASCULAR      -> "الأوعية الدموية"
            SkinMetric.Type.PIGMENTATION  -> "التصبغ"
            SkinMetric.Type.DARK_CIRCLES  -> "الهالات الداكنة"
            SkinMetric.Type.BLACKHEADS    -> "الرؤوس السوداء"
            SkinMetric.Type.ACNE          -> "حب الشباب"
            SkinMetric.Type.SKIN_TONE     -> "لون البشرة"
            SkinMetric.Type.SENSITIVITY   -> "الحساسية"
            SkinMetric.Type.ROSACEA       -> "الوردية"
            SkinMetric.Type.MELASMA       -> "الكلف"
        }

        private fun SkinMetric.Type.iconRes(): Int = when (this) {
            SkinMetric.Type.MOISTURE      -> R.drawable.ic_metric_moisture
            SkinMetric.Type.PORES         -> R.drawable.ic_metric_pores
            SkinMetric.Type.SEBUM         -> R.drawable.ic_metric_sebum
            SkinMetric.Type.WRINKLES      -> R.drawable.ic_metric_wrinkles
            SkinMetric.Type.TEXTURE       -> R.drawable.ic_metric_texture
            SkinMetric.Type.UV_SPOTS      -> R.drawable.ic_metric_uv
            SkinMetric.Type.VASCULAR      -> R.drawable.ic_metric_vascular
            SkinMetric.Type.PIGMENTATION  -> R.drawable.ic_metric_spots
            SkinMetric.Type.DARK_CIRCLES  -> R.drawable.ic_metric_dark_circles
            SkinMetric.Type.BLACKHEADS    -> R.drawable.ic_metric_pores
            SkinMetric.Type.ACNE          -> R.drawable.ic_metric_sensitivity
            SkinMetric.Type.SKIN_TONE     -> R.drawable.ic_metric_texture
            SkinMetric.Type.SENSITIVITY   -> R.drawable.ic_metric_sensitivity
            SkinMetric.Type.ROSACEA       -> R.drawable.ic_metric_rosacea
            SkinMetric.Type.MELASMA       -> R.drawable.ic_metric_melasma
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

    class MetricDiffCallback : DiffUtil.ItemCallback<SkinMetric>() {
        override fun areItemsTheSame(oldItem: SkinMetric, newItem: SkinMetric) =
            oldItem.type == newItem.type

        override fun areContentsTheSame(oldItem: SkinMetric, newItem: SkinMetric) =
            oldItem == newItem
    }
}
