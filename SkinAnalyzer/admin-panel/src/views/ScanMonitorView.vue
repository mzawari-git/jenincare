<template>
  <div class="scan-monitor">
    <div class="monitor-header">
      <div class="header-left">
        <h2 class="page-title">🔍 مراقبة تحليل البشرة</h2>
        <p class="page-desc">مراقبة وإدارة تحاليل البشرة الواردة في الوقت الفعلي</p>
      </div>
      <div class="header-right">
        <div class="ws-indicator" :class="{ connected: connected }">
          <span class="status-dot" :class="connected ? 'online' : 'offline'"></span>
          <span>{{ connected ? 'متصل' : 'غير متصل' }}</span>
        </div>
        <select v-model="filterStatus" class="filter-select" @change="refreshScans">
          <option value="">الكل</option>
          <option value="pending">معلق</option>
          <option value="processing">جاري المعالجة</option>
          <option value="approved">معتمد</option>
          <option value="rejected">مرفوض</option>
        </select>
        <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="بحث باسم المستخدم..."
          @input="filterScans"
        />
        <button class="btn btn-secondary" @click="refreshScans">
          <span>↻</span> تحديث
        </button>
      </div>
    </div>

    <div v-if="connected" class="new-scan-alert" v-show="showNewScanAlert">
      <span>🔔</span> تحليل جديد تم استلامه!
    </div>

    <div class="monitor-content">
      <div v-if="loading && allFilteredScans.length === 0" class="loading-state">
        <div class="spinner spinner-lg"></div>
        <p>جاري تحميل التحاليل...</p>
      </div>

      <div v-else-if="allFilteredScans.length === 0" class="empty-state">
        <div class="empty-state-icon">🔍</div>
        <div class="empty-state-title">لا توجد تحاليل</div>
        <div class="empty-state-desc">لم يتم العثور على تحاليل مطابقة للبحث</div>
      </div>

      <div v-else class="scans-list">
        <ScanRow
          v-for="scan in paginatedScans"
          :key="scan.id"
          :scan="scan"
          @view="viewScan"
          @approve="approveScan"
          @reject="rejectScan"
          @generate-pin="generatePin"
        />
      </div>
    </div>

    <div v-if="totalPages > 1" class="pagination">
      <button :disabled="currentPage === 1" @click="currentPage--">‹</button>
      <button
        v-for="page in visiblePages"
        :key="page"
        :class="{ active: page === currentPage }"
        @click="currentPage = page"
      >{{ page }}</button>
      <button :disabled="currentPage === totalPages" @click="currentPage++">›</button>
    </div>

    <PinDisplayModal
      v-if="pinModal.show"
      :pin="pinModal.pin"
      :expiresAt="pinModal.expiresAt"
      @close="pinModal.show = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useScansStore } from '@/stores/scans'
import { scansApi } from '@/api/endpoints'
import Swal from 'sweetalert2'
import ScanRow from '@/components/ScanRow.vue'
import PinDisplayModal from '@/components/PinDisplayModal.vue'

const router = useRouter()
const scansStore = useScansStore()

const loading = ref(false)
const connected = ref(true)
const showNewScanAlert = ref(false)
const filterStatus = ref('')
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 15
const allScans = ref([])
let pollTimer = null

const pinModal = reactive({
  show: false,
  pin: '',
  expiresAt: null
})

const allFilteredScans = computed(() => {
  let scans = allScans.value

  if (filterStatus.value) {
    scans = scans.filter(s => s.status === filterStatus.value)
  }

  if (searchQuery.value.trim()) {
    const query = searchQuery.value.trim().toLowerCase()
    scans = scans.filter(s =>
      (s.user_name || '').toLowerCase().includes(query) ||
      (s.user_phone || '').toLowerCase().includes(query)
    )
  }

  return scans
})

const totalPages = computed(() => Math.ceil(allFilteredScans.value.length / perPage) || 1)

const paginatedScans = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return allFilteredScans.value.slice(start, start + perPage)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)

  if (end - start < 4) {
    if (start === 1) end = Math.min(total, start + 4)
    else start = Math.max(1, end - 4)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

function filterScans() {
  currentPage.value = 1
}

function startPolling() {
  connected.value = true
  pollTimer = setInterval(async () => {
    try {
      const params = { per_page: 5, sort: 'latest' }
      const { data } = await scansApi.list(params)
      const latest = data.data || data.scans || []
      for (const scan of latest) {
        const existing = allScans.value.find(s => s.id === scan.id)
        if (!existing) {
          allScans.value.unshift(scan)
          showNewScanAlert.value = true
          setTimeout(() => { showNewScanAlert.value = false }, 3000)
        }
      }
    } catch {}
  }, 5000)
}

async function refreshScans() {
  loading.value = true
  currentPage.value = 1
  try {
    const params = { per_page: 100 }
    if (filterStatus.value) params.status = filterStatus.value
    const { data } = await scansApi.list(params)
    allScans.value = data.data || data.scans || []
  } catch (err) {
    console.error('Failed to fetch scans:', err)
    allScans.value = []
  } finally {
    loading.value = false
  }
}

function viewScan(scan) {
  router.push(`/scans/${scan.id}`)
}

async function approveScan(scan) {
  const result = await Swal.fire({
    title: 'اعتماد التحليل؟',
    text: `هل تريد اعتماد تحليل ${scan.user_name || 'المستخدم'}؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'اعتماد',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#22c55e'
  })

  if (result.isConfirmed) {
    const res = await scansStore.approveScan(scan.id)
    if (res.success) {
      const idx = allScans.value.findIndex(s => s.id === scan.id)
      if (idx >= 0) allScans.value[idx] = { ...allScans.value[idx], status: 'approved' }
      Swal.fire({ title: 'تم الاعتماد', icon: 'success', timer: 1500, showConfirmButton: false })
    } else {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  }
}

async function rejectScan(scan) {
  const result = await Swal.fire({
    title: 'رفض التحليل؟',
    text: `سبب رفض تحليل ${scan.user_name || 'المستخدم'}`,
    input: 'text',
    inputPlaceholder: 'سبب الرفض...',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'رفض',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#ef4444'
  })

  if (result.isConfirmed) {
    const res = await scansStore.rejectScan(scan.id, result.value || '')
    if (res.success) {
      const idx = allScans.value.findIndex(s => s.id === scan.id)
      if (idx >= 0) allScans.value[idx] = { ...allScans.value[idx], status: 'rejected' }
      Swal.fire({ title: 'تم الرفض', icon: 'info', timer: 1500, showConfirmButton: false })
    } else {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  }
}

async function generatePin(scan) {
  const res = await scansStore.generatePin(scan.id)
  if (res.success) {
    pinModal.pin = res.pin
    pinModal.expiresAt = res.expiresAt
    pinModal.show = true
  } else {
    Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
  }
}

function handleRefresh() {
  refreshScans()
}

onMounted(() => {
  refreshScans()
  startPolling()
  window.addEventListener('admin-refresh', handleRefresh)
})

onUnmounted(() => {
  window.removeEventListener('admin-refresh', handleRefresh)
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<style lang="scss" scoped>
.scan-monitor {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.monitor-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 1.375rem;
  font-weight: 800;
  color: var(--text-primary);
}

.page-desc {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.ws-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  background: var(--bg-card);
  border-radius: var(--radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  border: 1px solid var(--border-color);
}

.ws-indicator.connected {
  color: var(--success);
  border-color: rgba(34, 197, 94, 0.3);
}

.filter-select,
.search-input {
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 0.8125rem;
}

.filter-select {
  width: 130px;
}

.search-input {
  width: 200px;
}

.new-scan-alert {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--primary-bg);
  color: var(--primary);
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.875rem;
  animation: slideInRight 0.3s ease;
}

.monitor-content {
  min-height: 300px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  gap: 1rem;
  color: var(--text-secondary);
}

.scans-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

@media (max-width: 768px) {
  .monitor-header {
    flex-direction: column;
  }

  .header-right {
    width: 100%;
  }

  .search-input,
  .filter-select {
    flex: 1;
    width: auto;
  }
}
</style>
