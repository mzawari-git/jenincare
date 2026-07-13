import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { providersApi } from '@/api/endpoints'

export const useAIProvidersStore = defineStore('aiProviders', () => {
  const providers = ref([])
  const loading = ref(false)

  const activeProviders = computed(() => providers.value.filter(p => p.is_active))
  const hasActiveProvider = computed(() => activeProviders.value.length > 0)
  const quotaAlerts = computed(() =>
    providers.value.filter(p => {
      if (!p.quota_limit || p.quota_limit <= 0) return false
      return (p.quota_used / p.quota_limit) >= 0.8
    })
  )

  async function fetchProviders() {
    loading.value = true
    try {
      const { data } = await providersApi.list()
      providers.value = data.providers || data.data || []
    } catch (err) {
      console.error('Failed to fetch providers:', err)
    } finally {
      loading.value = false
    }
  }

  async function toggleProvider(id) {
    try {
      const provider = providers.value.find(p => p.id === id)
      if (!provider) return { success: false, message: 'المزود غير موجود' }
      const action = provider.is_active ? 'deactivate' : 'activate'
      const { data } = await providersApi.activate(id, { action })
      provider.is_active = data.provider?.is_active ?? !provider.is_active
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل تغيير حالة المزود'
      return { success: false, message }
    }
  }

  async function updateCredentials(id, credentials) {
    try {
      const { data } = await providersApi.update(id, credentials)
      const idx = providers.value.findIndex(p => p.id === id)
      if (idx >= 0) {
        providers.value[idx] = { ...providers.value[idx], ...data.provider }
      }
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل تحديث بيانات المزود'
      return { success: false, message }
    }
  }

  async function testConnection(id) {
    try {
      const { data } = await providersApi.testConnection(id)
      return { success: true, message: data.message || 'تم الاتصال بنجاح', latency: data.latency }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل الاتصال بالمزود'
      return { success: false, message }
    }
  }

  async function createProvider(payload) {
    try {
      const { data } = await providersApi.create(payload)
      providers.value.push(data.data)
      return { success: true, provider: data.data }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل إضافة المزود'
      return { success: false, message }
    }
  }

  function getProviderById(id) {
    return providers.value.find(p => p.id === id) || null
  }

  return {
    providers,
    loading,
    activeProviders,
    hasActiveProvider,
    quotaAlerts,
    fetchProviders,
    toggleProvider,
    updateCredentials,
    testConnection,
    createProvider,
    getProviderById
  }
})
