<template>
  <div class="scan-monitor">
    <div class="monitor-header">
      <div class="header-left">
        <h2 class="page-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="title-icon">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
          مراقبة تحليل البشرة
        </h2>
        <p class="page-desc">مراقبة وإدارة تحاليل البشرة الواردة في الوقت الفعلي</p>
      </div>
      <div class="header-right">
        <div class="ws-indicator" :class="{ connected }">
          <span class="pulse-dot" :class="{ active: connected }"></span>
          <span>{{ connected ? 'متصل مباشرة' : 'غير متصل' }}</span>
        </div>
        <div class="filter-group">
          <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="search-icon">
              <circle cx="11" cy="11" r="8"/>
              <path d="M21 21l-4.35-4.35"/>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              class="search-input"
              placeholder="بحث باسم المستخدم أو الهاتف..."
              @input="filterScans"
            />
          </div>
          <select v-model="filterStatus" class="filter-select" @change="refreshScans">
            <option value="">جميع الحالات</option>
            <option value="pending">معلق</option>
            <option value="processing">جاري المعالجة</option>
            <option value="approved">معتمد</option>
            <option value="rejected">مرفوض</option>
          </select>
        </div>
        <button class="btn btn-secondary refresh-btn" @click="refreshScans">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="refresh-icon" :class="{ spinning: loading }">
            <path d="M23 4v6h-6M1 20v-6h6"/>
            <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
          </svg>
          <span>تحديث</span>
        </button>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-summary">
      <div class="summary-item summary-total">
        <span class="summary-value">{{ allScans.length }}</span>
        <span class="summary-label">إجمالي</span>
      </div>
      <div class="summary-item summary-pending">
        <span class="summary-value">{{ allScans.filter(s => s.status === 'pending').length }}</span>
        <span class="summary-label">معلق</span>
      </div>
      <div class="summary-item summary-processing">
        <span class="summary-value">{{ allScans.filter(s => s.status === 'processing').length }}</span>
        <span class="summary-label">جاري المعالجة</span>
      </div>
      <div class="summary-item summary-approved">
        <span class="summary-value">{{ allScans.filter(s => s.status === 'approved').length }}</span>
        <span class="summary-label">معتمد</span>
      </div>
    </div>

    <div v-if="connected" class="new-scan-alert" v-show="showNewScanAlert">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="alert-icon">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      <span>تحليل جديد تم استلامه!</span>
    </div>

    <div class="monitor-content">
      <div v-if="loading && allFilteredScans.length === 0" class="loading-state">
        <div class="loader">
          <div class="loader-ring"></div>
          <div class="loader-ring"></div>
          <div class="loader-ring"></div>
        </div>
        <p>جاري تحميل التحاليل...</p>
      </div>

      <div v-else-if="allFilteredScans.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
            <path d="M8 11h6"/>
          </svg>
        </div>
        <div class="empty-title">لا توجد تحاليل</div>
        <div class="empty-desc">لم يتم العثور على تحاليل مطابقة للبحث</div>
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
      <button :disabled="currentPage === 1" @click="currentPage--" class="page-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </button>
      <button
        v-for="page in visiblePages"
        :key="page"
        :class="{ active: page === currentPage }"
        @click="currentPage = page"
        class="page-btn"
      >{{ page }}</button>
      <button :disabled="currentPage === totalPages" @click="currentPage++" class="page-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </button>
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
const connected = ref(false)
let pollTimer = null
let socket = null

const loading = ref(false)
const allScans = ref([])
const searchQuery = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const perPage = 10
const showNewScanAlert = ref(false)

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

function setupPolling() {
  if (pollTimer) return
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
  setupPolling()
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

.header-left {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.page-title {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 1.375rem;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0;
}

.title-icon {
  width: 24px;
  height: 24px;
  color: var(--primary);
}

.page-desc {
  font-size: 0.8125rem;
  color: var(--text-secondary);
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
  padding: 0.5rem 0.875rem;
  background: var(--bg-card);
  border-radius: var(--radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.ws-indicator.connected {
  color: var(--success);
  border-color: rgba(34, 197, 94, 0.3);
  background: var(--success-bg);
}

.pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--text-muted);
  position: relative;
}

.pulse-dot.active {
  background: var(--success);
}

.pulse-dot.active::after {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  border: 2px solid var(--success);
  animation: pulse 2s ease-out infinite;
}

@keyframes pulse {
  0% { transform: scale(0.8); opacity: 1; }
  100% { transform: scale(2); opacity: 0; }
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.search-box {
  position: relative;
}

.search-icon {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: var(--text-muted);
  pointer-events: none;
}

.search-input {
  padding: 0.5rem 2.25rem 0.5rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 0.8125rem;
  width: 220px;
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
  outline: none;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 0.8125rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:focus {
  border-color: var(--primary);
  outline: none;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.refresh-icon {
  width: 16px;
  height: 16px;
}

.refresh-icon.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Stats Summary */
.stats-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.summary-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 1rem;
  background: var(--bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
  transition: all 0.2s ease;
}

.summary-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 800;
  line-height: 1;
}

.summary-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.summary-total .summary-value { color: var(--text-primary); }
.summary-pending .summary-value { color: var(--warning); }
.summary-processing .summary-value { color: var(--info); }
.summary-approved .summary-value { color: var(--success); }

.summary-total { border-top: 3px solid var(--text-muted); }
.summary-pending { border-top: 3px solid var(--warning); }
.summary-processing { border-top: 3px solid var(--info); }
.summary-approved { border-top: 3px solid var(--success); }

.new-scan-alert {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.875rem 1.25rem;
  background: linear-gradient(135deg, var(--primary-bg), rgba(26, 136, 112, 0.15));
  color: var(--primary);
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.875rem;
  border: 1px solid rgba(26, 136, 112, 0.2);
  animation: slideInRight 0.3s ease;
}

.alert-icon {
  width: 20px;
  height: 20px;
  animation: ring 1s ease infinite;
}

@keyframes ring {
  0%, 100% { transform: rotate(0); }
  25% { transform: rotate(15deg); }
  75% { transform: rotate(-15deg); }
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
  gap: 1.5rem;
  color: var(--text-secondary);
}

.loader {
  position: relative;
  width: 60px;
  height: 60px;
}

.loader-ring {
  position: absolute;
  inset: 0;
  border: 3px solid transparent;
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
}

.loader-ring:nth-child(2) {
  inset: 6px;
  border-top-color: var(--primary-light);
  animation-delay: -0.15s;
}

.loader-ring:nth-child(3) {
  inset: 12px;
  border-top-color: var(--primary-bg);
  animation-delay: -0.3s;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
  border-radius: 50%;
  margin-bottom: 1rem;
}

.empty-icon svg {
  width: 40px;
  height: 40px;
  color: var(--text-muted);
}

.empty-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.375rem;
}

.empty-desc {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.scans-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.375rem;
  padding: 1rem;
  background: var(--bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
}

.page-btn {
  min-width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-btn svg {
  width: 16px;
  height: 16px;
}

.page-btn:hover:not(:disabled) {
  background: var(--primary-bg);
  border-color: var(--primary);
  color: var(--primary);
}

.page-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: #fff;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

@media (max-width: 992px) {
  .stats-summary {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .monitor-header {
    flex-direction: column;
  }

  .header-right {
    width: 100%;
    flex-wrap: wrap;
  }

  .filter-group {
    flex: 1;
    min-width: 200px;
  }

  .search-input {
    width: 100%;
    flex: 1;
  }

  .stats-summary {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-summary {
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
  }

  .summary-item {
    padding: 0.75rem;
  }

  .summary-value {
    font-size: 1.25rem;
  }
}
</style>
