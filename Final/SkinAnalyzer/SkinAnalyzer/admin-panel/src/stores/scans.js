import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { scansApi } from '@/api/endpoints'
import dayjs from 'dayjs'

export const useScansStore = defineStore('scans', () => {
  const pendingScans = ref([])
  const allScans = ref([])
  const currentScan = ref(null)
  const loading = ref(false)
  const pagination = ref({ page: 1, perPage: 20, total: 0, totalPages: 0 })
  const wsConnected = ref(false)

  const pendingCount = computed(() => pendingScans.value.length)
  const hasPending = computed(() => pendingScans.value.length > 0)

  async function fetchPendingScans() {
    loading.value = true
    try {
      const { data } = await scansApi.list({ status: 'pending', per_page: 100 })
      pendingScans.value = data.data || data.scans || []
    } catch (err) {
      console.error('Failed to fetch pending scans:', err)
      pendingScans.value = []
    } finally {
      loading.value = false
    }
  }

  async function fetchScans(params = {}) {
    loading.value = true
    try {
      const queryParams = {
        page: pagination.value.page,
        per_page: pagination.value.perPage,
        ...params
      }
      const { data } = await scansApi.list(queryParams)
      allScans.value = data.data || data.scans || []
      if (data.meta || data.pagination) {
        const meta = data.meta || data.pagination
        pagination.value = {
          page: meta.current_page || meta.page || 1,
          perPage: meta.per_page || meta.perPage || 20,
          total: meta.total || 0,
          totalPages: meta.last_page || meta.totalPages || 0
        }
      }
    } catch (err) {
      console.error('Failed to fetch scans:', err)
    } finally {
      loading.value = false
    }
  }

  async function fetchScanDetail(id) {
    loading.value = true
    try {
      const { data } = await scansApi.detail(id)
      currentScan.value = data.scan || data.data || data
      return currentScan.value
    } catch (err) {
      console.error('Failed to fetch scan detail:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function approveScan(id, notes = '') {
    try {
      const { data } = await scansApi.approve(id, { notes })
      pendingScans.value = pendingScans.value.filter(s => s.id !== id)
      if (currentScan.value?.id === id) {
        currentScan.value = { ...currentScan.value, ...data.scan, status: 'approved' }
      }
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل اعتماد التحليل'
      return { success: false, message }
    }
  }

  async function rejectScan(id, reason = '') {
    try {
      const { data } = await scansApi.reject(id, { reason })
      pendingScans.value = pendingScans.value.filter(s => s.id !== id)
      if (currentScan.value?.id === id) {
        currentScan.value = { ...currentScan.value, ...data.scan, status: 'rejected' }
      }
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل رفض التحليل'
      return { success: false, message }
    }
  }

  async function generatePin(id) {
    try {
      const { data } = await scansApi.generatePin(id)
      if (currentScan.value?.id === id) {
        currentScan.value = { ...currentScan.value, pin: data.pin, pin_expires_at: data.expires_at }
      }
      return { success: true, pin: data.pin, expiresAt: data.expires_at }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل إنشاء رمز PIN'
      return { success: false, message }
    }
  }

  async function batchApprove(ids) {
    try {
      await scansApi.batchApprove({ ids })
      pendingScans.value = pendingScans.value.filter(s => !ids.includes(s.id))
      return { success: true }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل الاعتماد الجماعي'
      return { success: false, message }
    }
  }

  async function broadcastResult(id, channel = 'whatsapp') {
    try {
      const { data } = await scansApi.broadcast(id, { channel })
      return { success: true, data: data }
    } catch (err) {
      const message = err.response?.data?.message || 'فشل إرسال النتيجة'
      return { success: false, message }
    }
  }

  async function fetchScanStats(days = 7) {
    try {
      const { data } = await scansApi.stats({ days })
      return data
    } catch (err) {
      console.error('Failed to fetch scan stats:', err)
      return null
    }
  }

  function addPendingScan(scan) {
    const exists = pendingScans.value.find(s => s.id === scan.id)
    if (!exists) {
      pendingScans.value.unshift(scan)
    }
  }

  function removePendingScan(id) {
    pendingScans.value = pendingScans.value.filter(s => s.id !== id)
  }

  function setPage(page) {
    pagination.value.page = page
  }

  return {
    pendingScans,
    allScans,
    currentScan,
    loading,
    pagination,
    wsConnected,
    pendingCount,
    hasPending,
    fetchPendingScans,
    fetchScans,
    fetchScanDetail,
    approveScan,
    rejectScan,
    generatePin,
    batchApprove,
    broadcastResult,
    fetchScanStats,
    addPendingScan,
    removePendingScan,
    setPage
  }
})
