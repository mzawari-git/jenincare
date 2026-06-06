package com.jenincare.skinanalyzer.data.repository

import com.jenincare.skinanalyzer.data.model.Product
import com.jenincare.skinanalyzer.data.remote.ApiService
import com.jenincare.skinanalyzer.util.NetworkMonitor
import com.jenincare.skinanalyzer.util.Result
import kotlinx.coroutines.flow.first
import java.io.IOException
import javax.inject.Inject
import javax.inject.Singleton

interface ProductRepository {

    suspend fun getRecommendedProducts(scanId: String): Result<List<Product>>
}

@Singleton
class ProductRepositoryImpl @Inject constructor(
    private val apiService: ApiService,
    private val networkMonitor: NetworkMonitor
) : ProductRepository {

    override suspend fun getRecommendedProducts(scanId: String): Result<List<Product>> {
        if (!networkMonitor.isConnected.first()) {
            return Result.Error(IOException("No internet connection"))
        }

        return try {
            val response = apiService.getRecommendedProducts(scanId)

            if (response.isSuccessful) {
                val productsResponse = response.body()
                val products = productsResponse?.products?.map { dto ->
                    Product(
                        id = dto.id,
                        name = dto.name,
                        nameAr = dto.nameAr,
                        price = dto.price,
                        imageUrl = dto.imageUrl,
                        description = dto.description,
                        matchingReason = dto.matchingReason
                    )
                } ?: emptyList()
                Result.Success(products)
            } else {
                Result.Error(IOException("Failed to fetch products: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.Error(e)
        }
    }
}
