<template>
  <div class="dashboard">
    <div class="stats-grid">
      <StatCard
        v-for="stat in stats"
        :key="stat.label"
        :icon="stat.icon"
        :value="stat.value"
        :label="stat.label"
        :color="stat.color"
        :trend="stat.trend"
      />
    </div>

    <div class="dashboard-grid">
      <div class="card pending-scans-card">
        <div class="card-header">
          <h3 class="card-title">📋 تحاليل بانتظار المراجعة</h3>
          <button v-if="pendingScans.length > 0" class="btn btn-sm btn-primary" @click="handleBatchApprove">
            اعتماد الكل
          </button>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner spinner-lg"></div>
        </div>

        <div v-else-if="pendingScans.length === 0" class="empty-state">
          <div class="empty-state-icon">✅</div>
          <div class="empty-state-title">لا توجد تحاليل معلقة</div>
          <div class="empty-state-desc">جميع التحاليل تمت مراجعتها</div>
        </div>

        <div v-else class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>المستخدم</th>
                <th>التاريخ</th>
                <th>المزود</th>
                <th>الحالة</th>
                <th>إجراءات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="scan in pendingScans.slice(0, 5)" :key="scan.id">
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-sm">{{ scan.user_name?.charAt(0) || 'م' }}</div>
                    <div>
                      <div class="user-name-cell">{{ scan.user_name || 'مستخدم' }}</div>
                      <div class="user-phone-cell">{{ scan.user_phone || '' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ formatDate(scan.created_at) }}</td>
                <td>
                  <span class="badge badge-info">{{ scan.provider_name || 'الافتراضي' }}</span>
                </td>
                <td>
                  <span class="badge" :class="statusClass(scan.status)">
                    {{ statusLabel(scan.status) }}
                  </span>
                </td>
                <td>
                  <div class="action-btns">
                    <button class="btn btn-sm btn-success" @click="approveScanAction(scan.id)" title="اعتماد">
                      ✓
                    </button>
                    <button class="btn btn-sm btn-danger" @click="rejectScanAction(scan.id)" title="رفض">
                      ✕
                    </button>
                    <button class="btn btn-sm btn-info" @click="viewScan(scan.id)" title="تفاصيل">
                      👁
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="pendingScans.length > 5" class="see-more">
            <router-link to="/scans/monitor">عرض الكل ({{ pendingScans.length }}) ←</router-link>
          </div>
        </div>
      </div>

      <div class="card quota-card">
        <div class="card-header">
          <h3 class="card-title">📊 استخدام حصص الذكاء الاصطناعي</h3>
        </div>
        <div v-if="quotaData.length === 0" class="empty-state">
          <div class="empty-state-icon">🤖</div>
          <div class="empty-state-desc">لا توجد بيانات حصص</div>
        </div>
        <div v-else class="quota-bars">
          <div v-for="item in quotaData" :key="item.name" class="quota-bar-item">
            <div class="quota-bar-header">
              <span class="quota-bar-label">{{ item.name }}</span>
              <span class="quota-bar-value">{{ item.used }} / {{ item.limit }}</span>
            </div>
            <div class="progress-bar">
              <div
                class="progress-fill"
                :style="{ width: item.percentage + '%', background: item.color }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="card chart-card">
        <div class="card-header">
          <h3 class="card-title">📈 اتجاه التحاليل</h3>
          <select v-model="chartDays" class="chart-select" @change="fetchChartData">
            <option value="7">آخر 7 أيام</option>
            <option value="30">آخر 30 يوم</option>
          </select>
        </div>
        <div class="chart-wrapper">
          <canvas ref="trendChartRef"></canvas>
        </div>
      </div>

      <div class="card quick-actions-card">
        <div class="card-header">
          <h3 class="card-title">⚡ روابط سريعة</h3>
        </div>
        <div class="quick-actions">
          <router-link to="/scans/monitor" class="quick-action-item">
            <span class="quick-action-icon">🔍</span>
            <span class="quick-action-text">مراقبة التحاليل</span>
          </router-link>
          <router-link to="/prompts" class="quick-action-item">
            <span class="quick-action-icon">📝</span>
            <span class="quick-action-text">إدارة التعليمات</span>
          </router-link>
          <router-link to="/white-label" class="quick-action-item">
            <span class="quick-action-icon">🎨</span>
            <span class="quick-action-text">العلامة التجارية</span>
          </router-link>
          <router-link to="/products" class="quick-action-item">
            <span class="quick-action-icon">🛍️</span>
            <span class="quick-action-text">المنتجات</span>
          </router-link>
          <router-link to="/scans/history" class="quick-action-item">
            <span class="quick-action-icon">📋</span>
            <span class="quick-action-text">سجل التحاليل</span>
          </router-link>
          <router-link to="/settings" class="quick-action-item">
            <span class="quick-action-icon">⚙️</span>
            <span class="quick-action-text">الإعدادات</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useScansStore } from '@/stores/scans'
import { dashboardApi } from '@/api/endpoints'
import { Chart, registerables } from 'chart.js'
import dayjs from 'dayjs'
import 'dayjs/locale/ar'
import StatCard from '@/components/StatCard.vue'
import Swal from 'sweetalert2'

dayjs.locale('ar')

Chart.register(...registerables)

const router = useRouter()
const scansStore = useScansStore()

const loading = ref(false)
const chartDays = ref(7)
const trendChartRef = ref(null)
let trendChart = null

const stats = reactive([
  { icon: '📸', value: '0', label: 'تحليل اليوم', color: 'primary', trend: null },
  { icon: '⏳', value: '0', label: 'بانتظار المراجعة', color: 'warning', trend: null },
  { icon: '✅', value: '0', label: 'تم اعتمادها اليوم', color: 'success', trend: null },
  { icon: '🤖', value: '0', label: 'مزود نشط', color: 'info', trend: null }
])

const pendingScans = ref([])
const quotaData = ref([])

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

function statusClass(status) {
  return statusMap[status]?.class || 'badge-muted'
}

function formatDate(date) {
  return dayjs(date).format('YYYY/MM/DD hh:mm A')
}

async function fetchDashboardData() {
  loading.value = true
  try {
    const [statsRes, scansRes, quotaRes] = await Promise.all([
      dashboardApi.stats(),
      dashboardApi.recentScans(5),
      dashboardApi.quotaUsage()
    ])

    if (statsRes.data) {
      const d = statsRes.data
      stats[0].value = d.today_scans?.toString() || '0'
      stats[1].value = d.pending_reviews?.toString() || '0'
      stats[2].value = d.approved_today?.toString() || '0'
      stats[3].value = d.active_providers?.toString() || '0'
    }

    if (scansRes.data) {
      pendingScans.value = scansRes.data.scans || scansRes.data.data || []
    }

    if (quotaRes.data) {
      quotaData.value = (quotaRes.data.providers || quotaRes.data.data || []).map(p => ({
        name: p.name || p.provider_name,
        used: p.quota_used || 0,
        limit: p.quota_limit || 0,
        percentage: p.quota_limit > 0 ? Math.round((p.quota_used / p.quota_limit) * 100) : 0,
        color: (p.quota_used / p.quota_limit) > 0.8 ? '#ef4444' :
               (p.quota_used / p.quota_limit) > 0.6 ? '#f59e0b' : '#22c55e'
      }))
    }
  } catch (err) {
    console.error('Dashboard data fetch error:', err)
  } finally {
    loading.value = false
  }
}

async function fetchChartData() {
  try {
    const { data } = await dashboardApi.chartData({ days: chartDays.value })
    const chartData = data.data || data
    renderChart(chartData.labels || [], chartData.values || [])
  } catch (err) {
    console.error('Chart data fetch error:', err)
  }
}

function renderChart(labels, values) {
  if (trendChart) {
    trendChart.destroy()
  }
  if (!trendChartRef.value) return

  const ctx = trendChartRef.value.getContext('2d')
  const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim()
  const borderColor = getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim()

  trendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'عدد التحاليل',
        data: values,
        borderColor: '#1a8870',
        backgroundColor: 'rgba(26, 136, 112, 0.08)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#1a8870',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          grid: { color: borderColor },
          ticks: { color: textColor, font: { family: 'Tajawal' } }
        },
        y: {
          beginAtZero: true,
          grid: { color: borderColor },
          ticks: { color: textColor, font: { family: 'Tajawal' }, precision: 0 }
        }
      },
      interaction: {
        intersect: false,
        mode: 'index'
      }
    }
  })
}

function approveScanAction(id) {
  Swal.fire({
    title: 'اعتماد التحليل؟',
    text: 'هل أنت متأكد من اعتماد هذا التحليل؟',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'اعتماد',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#22c55e'
  }).then(async (result) => {
    if (result.isConfirmed) {
      const res = await scansStore.approveScan(id)
      if (res.success) {
        Swal.fire({ title: 'تم الاعتماد', icon: 'success', timer: 1500, showConfirmButton: false })
        pendingScans.value = pendingScans.value.filter(s => s.id !== id)
        stats[1].value = (parseInt(stats[1].value) - 1).toString()
        stats[2].value = (parseInt(stats[2].value) + 1).toString()
      } else {
        Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
      }
    }
  })
}

function rejectScanAction(id) {
  Swal.fire({
    title: 'رفض التحليل؟',
    text: 'لماذا تريد رفض هذا التحليل؟',
    input: 'text',
    inputPlaceholder: 'سبب الرفض...',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'رفض',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#ef4444'
  }).then(async (result) => {
    if (result.isConfirmed) {
      const reason = result.value || ''
      const res = await scansStore.rejectScan(id, reason)
      if (res.success) {
        Swal.fire({ title: 'تم الرفض', icon: 'info', timer: 1500, showConfirmButton: false })
        pendingScans.value = pendingScans.value.filter(s => s.id !== id)
        stats[1].value = (parseInt(stats[1].value) - 1).toString()
      } else {
        Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
      }
    }
  })
}

function viewScan(id) {
  router.push(`/scans/${id}`)
}

async function handleBatchApprove() {
  const pendingIds = pendingScans.value.map(s => s.id)
  if (pendingIds.length === 0) return

  Swal.fire({
    title: 'اعتماد جميع التحاليل؟',
    text: `سيتم اعتماد ${pendingIds.length} تحليل`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'اعتماد الكل',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#22c55e'
  }).then(async (result) => {
    if (result.isConfirmed) {
      const res = await scansStore.batchApprove(pendingIds)
      if (res.success) {
        Swal.fire({ title: 'تم اعتماد الكل', icon: 'success', timer: 1500, showConfirmButton: false })
        pendingScans.value = []
        stats[1].value = '0'
      } else {
        Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
      }
    }
  })
}

function handleRefresh() {
  fetchDashboardData()
  fetchChartData()
}

onMounted(() => {
  fetchDashboardData()
  fetchChartData()
  window.addEventListener('admin-refresh', handleRefresh)
})

onUnmounted(() => {
  window.removeEventListener('admin-refresh', handleRefresh)
  if (trendChart) trendChart.destroy()
})
</script>

<style lang="scss" scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 1.5rem;
}

.pending-scans-card {
  min-height: 300px;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar-sm {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary);
  color: #fff;
  font-weight: 700;
  font-size: 0.75rem;
  border-radius: 50%;
  flex-shrink: 0;
}

.user-name-cell {
  font-weight: 600;
  font-size: 0.8125rem;
}

.user-phone-cell {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.action-btns {
  display: flex;
  gap: 0.375rem;
}

.see-more {
  text-align: center;
  padding: 0.75rem;
  border-top: 1px solid var(--border-light);
}

.see-more a {
  color: var(--primary);
  font-weight: 600;
  font-size: 0.8125rem;
}

.see-more a:hover {
  color: var(--primary-light);
}

.quota-bars {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.quota-bar-item {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.quota-bar-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.8125rem;
}

.quota-bar-label {
  font-weight: 600;
  color: var(--text-primary);
}

.quota-bar-value {
  color: var(--text-muted);
}

.progress-bar {
  height: 8px;
  background: var(--bg-body);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: var(--radius-full);
  transition: width 0.6s ease;
}

.chart-card {
  min-height: 350px;
}

.chart-select {
  width: auto;
  padding: 0.375rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-input);
  color: var(--text-primary);
  font-size: 0.8125rem;
}

.chart-wrapper {
  height: 280px;
  position: relative;
}

.quick-actions-card {
  min-height: 350px;
}

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.quick-action-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-md);
  color: var(--text-primary);
  transition: all var(--transition-fast);
  border: 1px solid var(--border-light);
  text-decoration: none;
}

.quick-action-item:hover {
  background: var(--primary-bg);
  border-color: var(--primary);
  transform: translateX(-4px);
}

.quick-action-icon {
  font-size: 1.25rem;
}

.quick-action-text {
  font-weight: 500;
  font-size: 0.875rem;
}

@media (max-width: 992px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
