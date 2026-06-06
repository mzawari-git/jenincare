package com.jenincare.skinanalyzer.data.model

data class Product(
    val id: String,
    val name: String,
    val nameAr: String?,
    val price: Double,
    val imageUrl: String?,
    val description: String?,
    val matchingReason: String?
)
