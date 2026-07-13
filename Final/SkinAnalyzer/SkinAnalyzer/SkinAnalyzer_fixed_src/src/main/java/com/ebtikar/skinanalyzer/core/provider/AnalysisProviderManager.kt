package com.ebtikar.skinanalyzer.core.provider

import timber.log.Timber
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AnalysisProviderManager @Inject constructor() {

    private val providers = mutableMapOf<String, AnalysisProvider>()

    fun registerProvider(provider: AnalysisProvider) {
        val name = provider.getName()
        if (providers.containsKey(name)) {
            Timber.w("Provider '$name' already registered. Replacing.")
            providers[name]?.shutdown()
        }
        providers[name] = provider
        Timber.i("Registered analysis provider: $name (priority: ${provider.getPriority()})")
    }

    fun unregisterProvider(name: String) {
        providers.remove(name)?.let { provider ->
            provider.shutdown()
            Timber.i("Unregistered analysis provider: $name")
        }
    }

    fun resolveActiveProvider(): AnalysisProvider? {
        return providers.values
            .filter { it.isAvailable() }
            .maxByOrNull { it.getPriority() }
            .also { provider ->
                if (provider != null) {
                    Timber.i("Active provider resolved: ${provider.getName()}")
                } else {
                    Timber.w("No available analysis provider found")
                }
            }
    }

    fun getRegisteredProviders(): List<AnalysisProvider> = providers.values.toList()

    fun initializeAll(): List<Pair<String, Result<Unit>>> {
        return providers.map { (name, provider) ->
            name to provider.initialize()
        }
    }

    fun shutdownAll() {
        providers.values.forEach { it.shutdown() }
        providers.clear()
        Timber.i("All providers shut down")
    }
}
