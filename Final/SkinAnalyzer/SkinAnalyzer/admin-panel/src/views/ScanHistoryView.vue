<template>
  <div class="scan-history-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">📋 سجل التحاليل</h2>
        <p class="page-desc">جميع التحاليل السابقة مع إمكانية التصدير</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-info" @click="exportCSV" :disabled="exporting">
          <span v-if="exporting" class="spinner"></span>
          <span v-else>📥</span> تصدير CSV
        </button>
        <button class="btn btn-secondary" @click="refreshHistory">
          <span>↻</span> تحديث
        </button>
      </div>
    </div>

    <div class="filters-bar card">
      <div class="filter-row">
        <div class="filter-group">
          <label class="filter-label">من تاريخ</label>
          <input v-model="filters.dateFrom" type="date" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">إلى تاريخ</label>
          <input v-model="filters.dateTo" type="date" class="form-input" />
        </div>
        <div class="filter-group">
          <label class="filter-label">الحالة</label>
          <select v-model="filters.status" class="form-input">
            <option value="">الكل</option>
            <option value="pending">معلق</option>
            <option value="processing">جاري المعالجة</option>
            <option value="approved">معتمد</option>
            <option value="rejected">مرفوض</option>
            <option value="completed">مكتمل</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">المزود</label>
          <select v-model="filters.provider" class="form-input">
            <option value="">الكل</option>
            <option v-for="p in stores.providers?.providers || []" :key="p.id" :value="p.id">
              {{ p.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label">بحث</label>
          <input v-model="filters.search" type="text" class="form-input" placeholder="اسم أو رقم..." />
        </div>
        <div class="filter-group filter-actions">
          <button class="btn btn-primary" @click="applyFilters">🔍 بحث</button>
          <button class="btn btn-secondary" @click="resetFilters">↺ مسح</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th @click="sortBy('id')" class="sortable">
                الرقم {{ sortIcon('id') }}
              </th>
              <th @click="sortBy('user_name')" class="sortable">
                المستخدم {{ sortIcon('user_name') }}
              </th>
              <th @click="sortBy('created_at')" class="sortable">
                التاريخ {{ sortIcon('created_at') }}
              </th>
              <th>المزود</th>
              <th @click="sortBy('score')" class="sortable">
                النتيجة {{ sortIcon('score') }}
              </th>
              <th>الحالة</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" style="text-align: center; padding: 2rem;">
                <div class="spinner spinner-lg"></div>
              </td>
            </tr>
            <tr v-else-if="allScans.length === 0">
              <td colspan="7">
                <div class="empty-state">
                  <div class="empty-state-icon">📋</div>
                  <div class="empty-state-title">لا توجد تحاليل</div>
                  <div class="empty-state-desc">لم يتم العثور على تحاليل مطابقة</div>
                </div>
              </td>
            </tr>
            <tr v-for="scan in allScans" :key="scan.id" @click="viewScan(scan.id)" class="clickable-row">
              <td class="scan-id-cell">
                <code>#{{ scan.id }}</code>
              </td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-sm">{{ scan.user_name?.charAt(0) || 'م' }}</div>
                  <span>{{ scan.user_name || 'غير معروف' }}</span>
                </div>
              </td>
              <td>{{ formatDate(scan.created_at) }}</td>
              <td>
                <span class="badge badge-info">{{ scan.provider_name || 'افتراضي' }}</span>
              </td>
              <td>
                <span v-if="scan.score" class="score-cell" :class="scoreClass(scan.score)">
                  {{ scan.score }}%
                </span>
                <span v-else class="text-muted">-</span>
              </td>
              <td>
                <span class="badge" :class="statusBadgeClass(scan.status)">
                  {{ statusLabel(scan.status) }}
                </span>
              </td>
              <td>
                <div class="action-btns" @click.stop>
                  <button class="btn btn-sm btn-info" @click="viewScan(scan.id)" title="عرض">
                    👁
                  </button>
                  <button class="btn btn-sm btn-secondary" @click="exportSingle(scan.id)" title="تصدير">
                    📥
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination.totalPages > 1" class="pagination">
        <button :disabled="pagination.page <= 1" @click="goToPage(pagination.page - 1)">‹</button>
        <button
          v-for="page in paginationPages"
          :key="page"
          :class="{ active: page === pagination.page }"
          @click="goToPage(page)"
        >{{ page }}</button>
        <button :disabled="pagination.page >= pagination.totalPages" @click="goToPage(pagination.page + 1)">›</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useScansStore } from '@/stores/scans'
import { useAIProvidersStore } from '@/stores/aiProviders'
import { scansApi } from '@/api/endpoints'
import dayjs from 'dayjs'
import Swal from 'sweetalert2'
import { saveAs } from 'file-saver'

const router = useRouter()
const scansStore = useScansStore()
const stores = reactive({ providers: useAIProvidersStore() })

const loading = ref(false)
const exporting = ref(false)
const allScans = ref([])
const sortField = ref('created_at')
const sortDir = ref('desc')

const filters = reactive({
  dateFrom: '',
  dateTo: '',
  status: '',
  provider: '',
  search: ''
})

const pagination = reactive({
  page: 1,
  perPage: 20,
  total: 0,
  totalPages: 0
})

const paginationPages = computed(() => {
  const pages = []
  const total = pagination.totalPages
  const current = pagination.page
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  if (end - start < 4) {
    if (start === 1) end = Math.min(total, start + 4)
    else start = Math.max(1, end - 4)
  }
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const statusMap = {
  pending: { label: 'معلق', class: 'badge-warning' },
  processing: { label: 'جاري المعالجة', class: 'badge-info' },
  approved: { label: 'معتمد', class: 'badge-success' },
  rejected: { label: 'مرفوض', class: 'badge-danger' },
  completed: { label: 'مكتمل', class: 'badge-primary' }
}

function statusLabel(status) {
  return statusMap[status]?.label || status
}

function statusBadgeClass(status) {
  return statusMap[status]?.class || 'badge-muted'
}

function scoreClass(score) {
  if (score >= 80) return 'score-good'
  if (score >= 50) return 'score-medium'
  return 'score-low'
}

function sortIcon(field) {
  if (sortField.value !== field) return '↕'
  return sortDir.value === 'asc' ? '↑' : '↓'
}

function formatDate(date) {
  if (!date) return ''
  return dayjs(date).format('YYYY/MM/DD hh:mm A')
}

function sortBy(field) {
  if (sortField.value === field) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDir.value = 'desc'
  }
  fetchScans()
}

function buildParams() {
  const params = {
    page: pagination.page,
    per_page: pagination.perPage,
    sort_by: sortField.value,
    sort_dir: sortDir.value
  }
  if (filters.dateFrom) params.date_from = filters.dateFrom
  if (filters.dateTo) params.date_to = filters.dateTo
  if (filters.status) params.status = filters.status
  if (filters.provider) params.provider_id = filters.provider
  if (filters.search) params.search = filters.search
  return params
}

async function fetchScans() {
  loading.value = true
  try {
    const params = buildParams()
    const { data } = await scansApi.list(params)
    allScans.value = data.data || data.scans || []
    const meta = data.meta || data.pagination || {}
    pagination.total = meta.total || 0
    pagination.totalPages = meta.last_page || meta.totalPages || 1
    pagination.page = meta.current_page || meta.page || 1
  } catch (err) {
    console.error('Failed to fetch scans:', err)
    allScans.value = []
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  pagination.page = 1
  fetchScans()
}

function resetFilters() {
  filters.dateFrom = ''
  filters.dateTo = ''
  filters.status = ''
  filters.provider = ''
  filters.search = ''
  sortField.value = 'created_at'
  sortDir.value = 'desc'
  pagination.page = 1
  fetchScans()
}

function goToPage(page) {
  pagination.page = page
  fetchScans()
}

function viewScan(id) {
  router.push(`/scans/${id}`)
}

async function exportCSV() {
  exporting.value = true
  try {
    const params = buildParams()
    delete params.page
    delete params.per_page
    const response = await scansApi.export(params)
    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8' })
    saveAs(blob, `scans-export-${dayjs().format('YYYY-MM-DD')}.csv`)
    Swal.fire({ title: 'تم التصدير', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: 'فشل تصدير البيانات', icon: 'error' })
  } finally {
    exporting.value = false
  }
}

async function exportSingle(id) {
  try {
    const response = await scansApi.export({ id })
    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8' })
    saveAs(blob, `scan-${id}-${dayjs().format('YYYY-MM-DD')}.csv`)
  } catch {
    Swal.fire({ title: 'خطأ', text: 'فشل التصدير', icon: 'error' })
  }
}

function refreshHistory() {
  fetchScans()
}

function handleRefresh() {
  fetchScans()
}

onMounted(() => {
  fetchScans()
  stores.providers.fetchProviders()
  window.addEventListener('admin-refresh', handleRefresh)
})
</script>

<style lang="scss" scoped>
.scan-history-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
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

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.filters-bar {
  padding: 1rem 1.25rem;
}

.filter-row {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 140px;
  flex: 1;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.filter-actions {
  flex-direction: row;
  gap: 0.5rem;
  align-items: flex-end;
  min-width: auto;
  flex: 0;
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  color: var(--primary);
}

.clickable-row {
  cursor: pointer;
}

.scan-id-cell code {
  background: var(--bg-body);
  padding: 0.125rem 0.5rem;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.user-avatar-sm {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary);
  color: #fff;
  font-weight: 700;
  font-size: 0.6875rem;
  border-radius: 50%;
  flex-shrink: 0;
}

.score-cell {
  font-weight: 700;
  font-size: 0.875rem;
}

.score-good { color: var(--success); }
.score-medium { color: var(--warning); }
.score-low { color: var(--danger); }

.text-muted {
  color: var(--text-muted);
}

.action-btns {
  display: flex;
  gap: 0.375rem;
}

@media (max-width: 768px) {
  .filter-row {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }

  .filter-actions {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
