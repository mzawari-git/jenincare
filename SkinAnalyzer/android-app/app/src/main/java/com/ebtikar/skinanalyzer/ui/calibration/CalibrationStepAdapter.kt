package com.ebtikar.skinanalyzer.ui.calibration

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ItemCalibrationStepBinding

data class CalibrationStep(
    val id: String,
    val title: String,
    val description: String,
    val status: StepStatus = StepStatus.PENDING,
    val result: String? = null
)

enum class StepStatus {
    PENDING, RUNNING, PASS, FAIL
}

class CalibrationStepAdapter : ListAdapter<CalibrationStep, CalibrationStepAdapter.StepViewHolder>(StepDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): StepViewHolder {
        val binding = ItemCalibrationStepBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return StepViewHolder(binding)
    }

    override fun onBindViewHolder(holder: StepViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class StepViewHolder(
        private val binding: ItemCalibrationStepBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(step: CalibrationStep) {
            val context = binding.root.context
            binding.tvStepTitle.text = step.title
            binding.tvStepDescription.text = step.description

            when (step.status) {
                StepStatus.PENDING -> {
                    binding.cardStepIcon.setCardBackgroundColor(ContextCompat.getColor(context, R.color.surface_card_2))
                    binding.ivStepIcon.setColorFilter(ContextCompat.getColor(context, R.color.text_muted))
                    binding.progressStep.visibility = View.GONE
                    binding.tvStepStatus.text = "في الانتظار"
                    binding.tvStepStatus.setTextColor(ContextCompat.getColor(context, R.color.text_muted))
                    binding.tvStepStatus.setBackgroundResource(R.drawable.bg_chip_muted)
                    binding.tvStepResult.visibility = View.GONE
                }
                StepStatus.RUNNING -> {
                    binding.cardStepIcon.setCardBackgroundColor(ContextCompat.getColor(context, R.color.primary_light))
                    binding.ivStepIcon.setColorFilter(ContextCompat.getColor(context, R.color.primary))
                    binding.progressStep.visibility = View.VISIBLE
                    binding.tvStepStatus.text = "جاري..."
                    binding.tvStepStatus.setTextColor(ContextCompat.getColor(context, R.color.primary))
                    binding.tvStepStatus.setBackgroundResource(R.drawable.bg_chip_cyan)
                    binding.tvStepResult.visibility = View.GONE
                }
                StepStatus.PASS -> {
                    binding.cardStepIcon.setCardBackgroundColor(ContextCompat.getColor(context, R.color.severity_excellent_bg))
                    binding.ivStepIcon.setColorFilter(ContextCompat.getColor(context, R.color.severity_excellent))
                    binding.ivStepIcon.setImageResource(R.drawable.ic_start_scan)
                    binding.progressStep.visibility = View.GONE
                    binding.tvStepStatus.text = "نجح"
                    binding.tvStepStatus.setTextColor(ContextCompat.getColor(context, R.color.severity_excellent))
                    binding.tvStepStatus.setBackgroundResource(R.drawable.bg_chip_green)
                    if (step.result != null) {
                        binding.tvStepResult.text = step.result
                        binding.tvStepResult.visibility = View.VISIBLE
                    }
                }
                StepStatus.FAIL -> {
                    binding.cardStepIcon.setCardBackgroundColor(ContextCompat.getColor(context, R.color.severity_critical_bg))
                    binding.ivStepIcon.setColorFilter(ContextCompat.getColor(context, R.color.severity_critical))
                    binding.ivStepIcon.setImageResource(R.drawable.ic_delete)
                    binding.progressStep.visibility = View.GONE
                    binding.tvStepStatus.text = "فشل"
                    binding.tvStepStatus.setTextColor(ContextCompat.getColor(context, R.color.severity_critical))
                    binding.tvStepStatus.setBackgroundResource(R.drawable.bg_chip_muted)
                    if (step.result != null) {
                        binding.tvStepResult.text = step.result
                        binding.tvStepResult.visibility = View.VISIBLE
                        binding.tvStepResult.setTextColor(ContextCompat.getColor(context, R.color.severity_critical))
                    }
                }
            }
        }
    }

    class StepDiffCallback : DiffUtil.ItemCallback<CalibrationStep>() {
        override fun areItemsTheSame(oldItem: CalibrationStep, newItem: CalibrationStep) =
            oldItem.id == newItem.id

        override fun areContentsTheSame(oldItem: CalibrationStep, newItem: CalibrationStep) =
            oldItem == newItem
    }
}
