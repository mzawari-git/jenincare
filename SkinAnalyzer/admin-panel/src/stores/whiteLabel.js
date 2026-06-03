import { defineStore } from 'pinia'
import { ref } from 'vue'
import { whiteLabelApi } from '@/api/endpoints'

export const useWhiteLabelStore = defineStore('whiteLabel', () => {
  const config = ref({
    app_name_ar: 'محلل البشرة',
    app_name_en: 'SkinAnalyzer',
    primary_color: '#1a8870',
    accent_color: '#f0a04b',
    background_color: '#f8fafc',
    logo_url: null,
    server_url: 'https://jenincare.shop',
    powered_by: true,
    contact_phone: '',
    contact_email: '',
    footer_text_ar: '',
    footer_text_en: ''
  })

  const loading = ref(false)
  const previewLoading = ref(false)

  async function fetchConfig() {
    loading.value = true
    try {
      const { data } = await whiteLabelApi.get()
      config.value = { ...config.value, ...(data.config || data) }
    } catch (err) {
      console.error('Failed to fetch white label config:', err)
    } finally {
      loading.value = false
    }
  }

  async function updateConfig(payload) {
    loading.value = true
    try {
      const { data } = await whiteLabelApi.update(payload)
      config.value = { ...config.value, ...(data.config || payload) }
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل حفظ الإعدادات'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  async function uploadLogo(file) {
    loading.value = true
    try {
      const formData = new FormData()
      formData.append('logo', file)
      const { data } = await whiteLabelApi.uploadLogo(formData)
      config.value.logo_url = data.logo_url || data.url
      return { success: true, url: config.value.logo_url }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل رفع الشعار'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  return {
    config,
    loading,
    previewLoading,
    fetchConfig,
    updateConfig,
    uploadLogo
  }
})
