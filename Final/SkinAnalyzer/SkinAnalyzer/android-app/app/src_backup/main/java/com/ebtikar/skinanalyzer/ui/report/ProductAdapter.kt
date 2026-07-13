package com.ebtikar.skinanalyzer.ui.report

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.ebtikar.skinanalyzer.databinding.ItemProductCardBinding
import com.ebtikar.skinanalyzer.model.ProductRecommendation

class ProductAdapter : ListAdapter<ProductRecommendation, ProductAdapter.ProductViewHolder>(ProductDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ProductViewHolder {
        val binding = ItemProductCardBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ProductViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ProductViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class ProductViewHolder(
        private val binding: ItemProductCardBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(product: ProductRecommendation) {
            binding.tvProductName.text = product.nameAr.ifEmpty { product.name }
            binding.tvProductBrand.text = product.brand
            binding.tvProductReason.text = product.reasonAr.ifEmpty { product.reason }
            binding.tvProductMatch.text = "${(product.matchScore * 100).toInt()}%"
            binding.tvProductPrice.text = "${product.price.toInt()} ${getCurrencySymbol(product.currency)}"
            binding.tvProductCategory.text = getCategoryAr(product.category)
        }

        private fun getCurrencySymbol(currency: String): String = when (currency) {
            "SAR" -> "ر.س"
            "USD" -> "$"
            "EUR" -> "€"
            "AED" -> "د.إ"
            else -> currency
        }

        private fun getCategoryAr(category: String): String = when (category) {
            "serum" -> "سيروم"
            "cleanser" -> "غسول"
            "cream" -> "كريم"
            "sunscreen" -> "واقي شمس"
            "moisturizer" -> "مرطب"
            "treatment" -> "علاج"
            "eye_care" -> "عناية عين"
            "mask" -> "ماسك"
            "toner" -> "تونر"
            else -> category
        }
    }

    class ProductDiffCallback : DiffUtil.ItemCallback<ProductRecommendation>() {
        override fun areItemsTheSame(oldItem: ProductRecommendation, newItem: ProductRecommendation) =
            oldItem.id == newItem.id

        override fun areContentsTheSame(oldItem: ProductRecommendation, newItem: ProductRecommendation) =
            oldItem == newItem
    }
}
