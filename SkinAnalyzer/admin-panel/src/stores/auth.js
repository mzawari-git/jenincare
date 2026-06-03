import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/client'
import { authApi } from '@/api/endpoints'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('admin_token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const userName = computed(() => user.value?.name || 'مدير النظام')
  const userRole = computed(() => user.value?.role || 'admin')

  function setToken(newToken) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('admin_token', newToken)
    } else {
      localStorage.removeItem('admin_token')
    }
  }

  async function login(email, password) {
    loading.value = true
    try {
      const { data } = await authApi.login({ email, password })
      setToken(data.token)
      user.value = data.user
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'بيانات الدخول غير صحيحة'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  async function checkAuth() {
    if (!token.value) return false
    loading.value = true
    try {
      const { data } = await authApi.me()
      user.value = data.user
      return true
    } catch {
      setToken(null)
      user.value = null
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch {
    }
    setToken(null)
    user.value = null
    router.push('/login')
  }

  async function updateProfile(payload) {
    loading.value = true
    try {
      const { data } = await authApi.updateProfile(payload)
      user.value = { ...user.value, ...data.user }
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل تحديث الملف الشخصي'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    userName,
    userRole,
    login,
    logout,
    checkAuth,
    updateProfile,
    setToken
  }
})
