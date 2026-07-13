package com.ebtikar.skinanalyzer.ui.home

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.databinding.ItemHomeRecentBinding
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class HomeRecentAdapter(
    private val onItemClick: (String) -> Unit
) : ListAdapter<SkinReportEntity, HomeRecentAdapter.RecentViewHolder>(RecentDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecentViewHolder {
        val binding = ItemHomeRecentBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return RecentViewHolder(binding)
    }

    override fun onBindViewHolder(holder: RecentViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    inner class RecentViewHolder(
        private val binding: ItemHomeRecentBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(report: SkinReportEntity) {
            val dateFormat = SimpleDateFormat("d MMM yyyy", Locale("ar"))
            val date = Date(report.timestamp)
            binding.tvRecentDate.text = dateFormat.format(date)
            binding.tvRecentProvider.text = report.providerName.replace("_", " ")
            binding.tvRecentScore.text = "%.0f".format(report.overallScore)

            val scoreLabel = when {
                report.overallScore >= 85f -> "ممتاز"
                report.overallScore >= 70f -> "جيد"
                report.overallScore >= 55f -> "مقبول"
                report.overallScore >= 35f -> "ضعيف"
                else -> "حرج"
            }
            binding.tvRecentScoreLabel.text = scoreLabel
            binding.scoreRingSmall.setScore(report.overallScore)

            binding.root.setOnClickListener { onItemClick(report.id) }
        }
    }

    class RecentDiffCallback : DiffUtil.ItemCallback<SkinReportEntity>() {
        override fun areItemsTheSame(oldItem: SkinReportEntity, newItem: SkinReportEntity) =
            oldItem.id == newItem.id

        override fun areContentsTheSame(oldItem: SkinReportEntity, newItem: SkinReportEntity) =
            oldItem == newItem
    }
}
