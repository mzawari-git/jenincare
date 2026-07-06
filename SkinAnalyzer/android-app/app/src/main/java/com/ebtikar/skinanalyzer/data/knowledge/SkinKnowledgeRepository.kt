package com.ebtikar.skinanalyzer.data.knowledge

import android.content.Context
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.OkHttpClient
import okhttp3.Request
import timber.log.Timber
import java.io.File
import java.util.concurrent.TimeUnit
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SkinKnowledgeRepository @Inject constructor(
    @ApplicationContext private val context: Context
) {
    private var knowledge: SkinKnowledgeBase? = null
    private val client = OkHttpClient.Builder()
        .connectTimeout(10, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .build()

    private val cacheFile: File
        get() = File(context.filesDir, "knowledge/skin_knowledge.json")

    suspend fun getKnowledge(): SkinKnowledgeBase {
        if (knowledge == null) {
            knowledge = loadFromCache() ?: loadFromAssets() ?: SkinKnowledgeBase()
        }
        return knowledge!!
    }

    suspend fun refreshFromRemote(): Boolean {
        return withContext(Dispatchers.IO) {
            try {
                val url = getKnowledge().url
                if (url.isBlank()) return@withContext false

                val request = Request.Builder().url(url).build()
                val response = client.newCall(request).execute()

                if (!response.isSuccessful) {
                    Timber.w("Knowledge remote fetch failed: ${response.code}")
                    return@withContext false
                }

                val raw = response.body?.string() ?: return@withContext false
                val remote = SkinKnowledgeJson.parse(raw)

                val local = knowledge ?: loadFromAssets()
                if (local != null && remote.version <= local.version) {
                    Timber.i("Knowledge up to date (local v${local.version}, remote v${remote.version})")
                    return@withContext false
                }

                cacheFile.parentFile?.mkdirs()
                cacheFile.writeText(raw)
                knowledge = remote
                Timber.i("Knowledge updated to v${remote.version}")
                true
            } catch (e: Exception) {
                Timber.w(e, "Failed to refresh knowledge from remote")
                false
            }
        }
    }

    fun getCachedKnowledge(): SkinKnowledgeBase {
        return knowledge ?: SkinKnowledgeBase()
    }

    fun getVersion(): Int {
        return knowledge?.version ?: 0
    }

    fun clearCache() {
        if (cacheFile.exists()) cacheFile.delete()
        knowledge = null
    }

    private fun loadFromCache(): SkinKnowledgeBase? {
        return try {
            if (!cacheFile.exists()) return null
            val raw = cacheFile.readText()
            SkinKnowledgeJson.parse(raw).also {
                Timber.i("Knowledge loaded from cache (v${it.version})")
            }
        } catch (e: Exception) {
            Timber.w(e, "Failed to load knowledge from cache")
            null
        }
    }

    private fun loadFromAssets(): SkinKnowledgeBase? {
        return try {
            context.assets.open("knowledge/skin_knowledge.json")
                .bufferedReader().use { it.readText() }
                .let { SkinKnowledgeJson.parse(it) }
                .also {
                    Timber.i("Knowledge loaded from assets (v${it.version})")
                }
        } catch (e: Exception) {
            Timber.e(e, "Failed to load knowledge from assets")
            null
        }
    }
}
