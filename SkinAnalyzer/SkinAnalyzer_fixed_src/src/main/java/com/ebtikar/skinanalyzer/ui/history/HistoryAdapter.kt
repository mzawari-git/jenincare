package com.ebtikar.skinanalyzer.ui.history

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.ebtikar.skinanalyzer.data.local.SkinReportEntity
import com.ebtikar.skinanalyzer.databinding.ItemHistoryReportBinding
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class HistoryAdapter(
    private val onReportClick: (String) -> Unit,
    private val onReportDelete: (String) -> Unit
) : ListAdapter<SkinReportEntity, HistoryAdapter.HistoryViewHolder>(HistoryDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): HistoryViewHolder {
        val binding = ItemHistoryReportBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return HistoryViewHolder(binding)
    }

    override fun onBindViewHolder(holder: HistoryViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    inner class HistoryViewHolder(
        private val binding: ItemHistoryReportBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(report: SkinReportEntity) {
            val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
            val timeFormat = SimpleDateFormat("HH:mm", Locale.getDefault())
            val date = Date(report.timestamp)
            binding.tvHistoryDate.text = dateFormat.format(date)
            binding.tvHistoryTime.text = timeFormat.format(date)
            binding.tvHistoryScore.text = "%.0f".format(report.overallScore)
            binding.tvHistoryProvider.text = report.providerName.replace("_", " ")

            val scoreLabel = when {
                report.overallScore >= 85f -> "ممتاز"
                report.overallScore >= 70f -> "جيد"
                report.overallScore >= 55f -> "مقبول"
                report.overallScore >= 35f -> "ضعيف"
                else -> "حرج"
            }
            binding.tvHistoryScoreLabel.text = scoreLabel

            binding.scoreRingSmall.setScore(report.overallScore)

            binding.root.setOnClickListener { onReportClick(report.id) }
        }
    }

    class HistoryDiffCallback : DiffUtil.ItemCallback<SkinReportEntity>() {
        override fun areItemsTheSame(oldItem: SkinReportEntity, newItem: SkinReportEntity) =
            oldItem.id == newItem.id

        override fun areContentsTheSame(oldItem: SkinReportEntity, newItem: SkinReportEntity) =
            oldItem == newItem
    }
}
