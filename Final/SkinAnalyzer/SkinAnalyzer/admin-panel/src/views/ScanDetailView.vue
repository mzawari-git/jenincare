<template>
  <div class="scan-detail" v-if="scan">
    <div class="detail-header">
      <button class="btn btn-secondary" @click="goBack">
        ← العودة
      </button>
      <h2 class="page-title">تفاصيل التحليل #{{ scan.id }}</h2>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printReport">
          🖨️ طباعة التقرير
        </button>
        <template v-if="scan.status === 'pending'">
          <button class="btn btn-success" @click="handleApprove">
            ✓ اعتماد التحليل
          </button>
          <button class="btn btn-warning" @click="handleGeneratePin">
            🔑 إنشاء رمز PIN
          </button>
          <button class="btn btn-danger" @click="handleReject">
            ✕ رفض
          </button>
        </template>
      </div>
    </div>

    <!-- Professional Printable Report -->
    <div id="printable-report" class="printable-report">
      <div class="report-header">
        <div class="report-logo">
          <img v-if="reportConfig.logo_url" :src="reportConfig.logo_url" alt="Logo" class="report-logo-img" />
          <div v-else class="report-logo-placeholder">🧬</div>
        </div>
        <div class="report-clinic-info">
          <h1 class="report-clinic-name">{{ reportConfig.clinic_name_ar || 'مركز جنين للعناية بالبشرة' }}</h1>
          <p v-if="reportConfig.clinic_address_ar" class="report-clinic-address">{{ reportConfig.clinic_address_ar }}</p>
          <p class="report-clinic-contact">
            <span v-if="reportConfig.clinic_phone">📱 {{ reportConfig.clinic_phone }}</span>
            <span v-if="reportConfig.clinic_phone && reportConfig.clinic_email">&nbsp;|&nbsp;</span>
            <span v-if="reportConfig.clinic_email">📧 {{ reportConfig.clinic_email }}</span>
          </p>
        </div>
        <div class="report-title-section">
          <h2 class="report-title">تقرير تحليل البشرة</h2>
          <p class="report-date">{{ today }}</p>
          <p class="report-id">رقم التقرير: #{{ scan.id }}</p>
        </div>
      </div>

      <div class="report-divider"></div>

      <div class="report-section report-patient">
        <h3>معلومات المريض</h3>
        <table class="report-table">
          <tr><td class="label">الاسم:</td><td>{{ scan.user_name || '—' }}</td></tr>
          <tr v-if="scan.user_phone"><td class="label">الهاتف:</td><td>{{ scan.user_phone }}</td></tr>
          <tr v-if="scan.user_email"><td class="label">البريد:</td><td>{{ scan.user_email }}</td></tr>
          <tr><td class="label">تاريخ التحليل:</td><td>{{ formatDate(scan.created_at) }}</td></tr>
          <tr><td class="label">مزود الذكاء الاصطناعي:</td><td>{{ scan.provider_name || 'Native Engine' }}</td></tr>
        </table>
      </div>

      <div class="report-section report-results">
        <h3>نتائج التحليل</h3>

        <div class="report-score-box">
          <div class="report-score-value">{{ scan.score ?? scan.overall_health_score ?? '—' }}%</div>
          <div class="report-score-label">المعدل الصحي العام</div>
        </div>

        <table v-if="scan.dimensions?.length" class="report-table report-metrics">
          <thead><tr><th>المؤشر</th><th>النسبة</th><th>التقييم</th></tr></thead>
          <tbody>
            <tr v-for="d in scan.dimensions" :key="d.name">
              <td>{{ d.name }}</td>
              <td>{{ d.value }}%</td>
              <td>{{ metricLabel(d.value) }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="scan.defects?.length" style="margin-top: 1rem;">
          <h4 style="margin-bottom: 0.5rem;">المشاكل المكتشفة:</h4>
          <table class="report-table">
            <thead><tr><th>المشكلة</th><th>الشدة</th><th>نصيحة</th></tr></thead>
            <tbody>
              <tr v-for="d in scan.defects" :key="d.type">
                <td>{{ d.name || d.type }}</td>
                <td>{{ severityLabel(d.severity) }}</td>
                <td>{{ d.tip || d.description || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="scan.recommended_products?.length" class="report-section report-products">
        <h3>المنتجات الموصى بها</h3>
        <table class="report-table">
          <thead><tr><th>المنتج</th><th>السعر</th><th>سبب التوصية</th></tr></thead>
          <tbody>
            <tr v-for="p in scan.recommended_products" :key="p.id">
              <td>{{ p.name }}</td>
              <td>{{ p.price ? p.price + ' ' + (p.currency || 'ريال') : '—' }}</td>
              <td>{{ p.reason || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="scan.analysis_text_ar" class="report-section">
        <h3>التحليل التفصيلي</h3>
        <p class="report-analysis-text">{{ scan.analysis_text_ar }}</p>
      </div>

      <div class="report-footer">
        <p>{{ reportConfig.footer_text_ar || '© 2026 جميع الحقوق محفوظة' }}</p>
      </div>
    </div>

    <!-- Normal admin view below -->
    <div class="detail-grid no-print">
      <PinDisplayModal v-if="pinModal.show" :pin="pinModal.pin" :expires-at="pinModal.expiresAt" @close="pinModal.show = false" />
      <div class="detail-sidebar">
        <div class="card">
          <h4 class="card-title">👤 معلومات المستخدم</h4>
          <div class="user-info">
            <div class="user-avatar-lg">{{ scan.user_name?.charAt(0) || 'م' }}</div>
            <div class="user-name-lg">{{ scan.user_name || 'غير معروف' }}</div>
            <div v-if="scan.user_phone" class="user-phone-lg">📱 {{ scan.user_phone }}</div>
            <div v-if="scan.user_email" class="user-email-lg">📧 {{ scan.user_email }}</div>
          </div>
        </div>

        <div class="card">
          <h4 class="card-title">📋 حالة التحليل</h4>
          <div class="status-timeline">
            <div class="timeline-step" :class="{ done: true }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <span class="timeline-label">تم الإنشاء</span>
                <span class="timeline-date">{{ formatDate(scan.created_at) }}</span>
              </div>
            </div>
            <div class="timeline-step" :class="{ done: scan.status !== 'pending' }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <span class="timeline-label">تمت المعالجة</span>
                <span v-if="scan.processed_at" class="timeline-date">{{ formatDate(scan.processed_at) }}</span>
              </div>
            </div>
            <div class="timeline-step" :class="{ done: scan.status === 'approved' }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <span class="timeline-label">اعتماد</span>
                <span v-if="scan.approved_at" class="timeline-date">{{ formatDate(scan.approved_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="scan.provider_name" class="card">
          <h4 class="card-title">🤖 مزود الذكاء الاصطناعي</h4>
          <p class="provider-info">
            <span class="badge badge-info">{{ scan.provider_name }}</span>
          </p>
          <p v-if="scan.processing_time" class="processing-time">
            ⏱️ وقت المعالجة: {{ scan.processing_time }} ثانية
          </p>
        </div>
      </div>

      <div class="detail-main">
        <div class="card scan-images-card">
          <h4 class="card-title">📸 صور التحليل</h4>
          <div class="scan-images">
            <div class="scan-image-main">
              <img
                v-if="scan.image_url"
                :src="scan.image_url"
                alt="صورة البشرة"
                @error="mainImgError = true"
              />
              <div v-else class="image-placeholder">📸 صورة البشرة</div>
            </div>
            <div v-if="scan.heatmap_url" class="scan-image-overlay">
              <span class="overlay-label">الخريطة الحرارية</span>
              <img :src="scan.heatmap_url" alt="Heatmap" />
            </div>
          </div>
        </div>

        <div v-if="scan.score !== undefined" class="analysis-grid">
          <div class="card score-card">
            <h4 class="card-title">💚 المعدل الصحي</h4>
            <HealthScoreGauge :score="scan.score" />
          </div>

          <div class="card radar-card">
            <h4 class="card-title">📊 تحليل الأبعاد</h4>
            <RadarChart :data="radarData" />
          </div>
        </div>

        <div v-if="scan.defects?.length" class="card">
          <h4 class="card-title">🔍 المشاكل المكتشفة</h4>
          <div class="defects-list">
            <div v-for="defect in scan.defects" :key="defect.type" class="defect-item">
              <div class="defect-header">
                <span class="defect-severity" :class="'severity-' + (defect.severity || 'medium')">
                  {{ severityLabel(defect.severity) }}
                </span>
                <span class="defect-name">{{ defect.name || defect.type }}</span>
              </div>
              <p v-if="defect.description || defect.tip" class="defect-tip">
                💡 {{ defect.description || defect.tip }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="scan.recommended_products?.length" class="card">
          <h4 class="card-title">🛍️ المنتجات الموصى بها</h4>
          <div class="products-grid">
            <div v-for="product in scan.recommended_products" :key="product.id" class="product-card">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="product-img" />
              <div class="product-info">
                <span class="product-name">{{ product.name }}</span>
                <span class="product-price" v-if="product.price">{{ product.price }} {{ product.currency || 'ريال' }}</span>
                <p v-if="product.reason" class="product-reason">📌 {{ product.reason }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="scan.analysis_text_ar" class="card">
          <h4 class="card-title">📝 نص التحليل (بالعربية)</h4>
          <div class="analysis-text">
            {{ scan.analysis_text_ar }}
          </div>
        </div>

        <div class="card raw-data-card">
          <div class="card-header">
            <h4 class="card-title">📄 استجابة مزود الذكاء الاصطناعي</h4>
            <button class="btn btn-sm btn-secondary" @click="showRawJson = !showRawJson">
              {{ showRawJson ? 'إخفاء' : 'عرض' }}
            </button>
          </div>
          <pre v-if="showRawJson" class="raw-json">{{ formattedRawResponse }}</pre>
        </div>
      </div>
    </div>
  </div>

  <div v-else-if="loading" class="loading-state">
    <div class="spinner spinner-lg"></div>
    <p>جاري تحميل تفاصيل التحليل...</p>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useScansStore } from '@/stores/scans'
import dayjs from 'dayjs'
import Swal from 'sweetalert2'
import HealthScoreGauge from '@/components/HealthScoreGauge.vue'
import RadarChart from '@/components/RadarChart.vue'
import PinDisplayModal from '@/components/PinDisplayModal.vue'
import apiClient from '@/api/client'

const route = useRoute()
const router = useRouter()
const scansStore = useScansStore()

const scan = ref(null)
const loading = ref(false)
const showRawJson = ref(false)
const mainImgError = ref(false)

const reportConfig = reactive({
  logo_url: null,
  clinic_name_ar: '',
  clinic_address_ar: '',
  clinic_phone: '',
  clinic_email: '',
  footer_text_ar: ''
})

const today = dayjs().format('DD/MM/YYYY')

async function fetchReportConfig() {
  try {
    const { data } = await apiClient.get('/white-label')
    if (data?.data) {
      reportConfig.logo_url = data.data.logo_url || null
      reportConfig.clinic_name_ar = data.data.clinic_name_ar || 'مركز جنين للعناية بالبشرة'
      reportConfig.clinic_address_ar = data.data.clinic_address_ar || ''
      reportConfig.clinic_phone = data.data.clinic_phone || ''
      reportConfig.clinic_email = data.data.clinic_email || ''
      reportConfig.footer_text_ar = data.data.footer_text_ar || ''
    }
  } catch (e) {
    console.error('Failed to fetch report config:', e)
  }
}

const pinModal = reactive({
  show: false,
  pin: '',
  expiresAt: null
})

const radarData = computed(() => {
  if (!scan.value?.dimensions) return []
  return (scan.value.dimensions || []).map(d => ({
    name: d.name,
    value: d.value
  }))
})

const formattedRawResponse = computed(() => {
  if (!scan.value?.raw_response) return '{}'
  try {
    const parsed = typeof scan.value.raw_response === 'string'
      ? JSON.parse(scan.value.raw_response)
      : scan.value.raw_response
    return JSON.stringify(parsed, null, 2)
  } catch {
    return scan.value.raw_response
  }
})

function formatDate(date) {
  if (!date) return ''
  return dayjs(date).format('YYYY/MM/DD hh:mm A')
}

function severityLabel(level) {
  const map = { high: 'عالٍ', medium: 'متوسط', low: 'منخفض' }
  return map[level] || level
}

function goBack() {
  router.back()
}

async function handleApprove() {
  const result = await Swal.fire({
    title: 'اعتماد التحليل؟',
    text: 'سيتم إرسال النتيجة إلى العميل',
    input: 'text',
    inputPlaceholder: 'ملاحظات (اختياري)...',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'اعتماد وإرسال',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#22c55e'
  })

  if (result.isConfirmed) {
    const res = await scansStore.approveScan(scan.value.id, result.value || '')
    if (res.success) {
      scan.value.status = 'approved'
      Swal.fire({ title: 'تم الاعتماد والإرسال', icon: 'success', timer: 2000, showConfirmButton: false })
    } else {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  }
}

async function handleReject() {
  const result = await Swal.fire({
    title: 'رفض التحليل؟',
    input: 'text',
    inputPlaceholder: 'سبب الرفض...',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'رفض',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#ef4444'
  })

  if (result.isConfirmed) {
    const res = await scansStore.rejectScan(scan.value.id, result.value || '')
    if (res.success) {
      scan.value.status = 'rejected'
      Swal.fire({ title: 'تم الرفض', icon: 'info', timer: 2000, showConfirmButton: false })
    } else {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  }
}

async function handleGeneratePin() {
  const res = await scansStore.generatePin(scan.value.id)
  if (res.success) {
    pinModal.pin = res.pin
    pinModal.expiresAt = res.expiresAt
    pinModal.show = true
  } else {
    Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
  }
}

onMounted(async () => {
  loading.value = true
  fetchReportConfig()
  const id = route.params.id
  if (id) {
    scan.value = await scansStore.fetchScanDetail(id)
  }
  loading.value = false
})

function printReport() {
  window.print()
}

function metricLabel(val) {
  if (val >= 80) return 'ممتاز'
  if (val >= 60) return 'جيد'
  if (val >= 40) return 'متوسط'
  return 'ضعيف'
}
</script>

<style lang="scss" scoped>
.scan-detail {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--text-primary);
  flex: 1;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 1.5rem;
}

.detail-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
}

.user-avatar-lg {
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary);
  color: #fff;
  font-size: 1.5rem;
  font-weight: 800;
  border-radius: 50%;
}

.user-name-lg {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
}

.user-phone-lg,
.user-email-lg {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.status-timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.timeline-step {
  display: flex;
  gap: 0.75rem;
  position: relative;
  padding-bottom: 1.25rem;
}

.timeline-step:not(:last-child)::after {
  content: '';
  position: absolute;
  right: 7px;
  top: 20px;
  bottom: 0;
  width: 2px;
  background: var(--border-color);
}

.timeline-step.done:not(:last-child)::after {
  background: var(--primary);
}

.timeline-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--border-color);
  border: 2px solid var(--bg-card);
  flex-shrink: 0;
  margin-top: 2px;
}

.timeline-step.done .timeline-dot {
  background: var(--primary);
  border-color: var(--primary-bg);
}

.timeline-label {
  display: block;
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--text-primary);
}

.timeline-date {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.provider-info {
  margin: 0.5rem 0;
}

.processing-time {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.detail-main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  min-width: 0;
}

.scan-images {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.scan-image-main {
  flex: 1;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 2px solid var(--border-light);
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
}

.scan-image-main img {
  width: 100%;
  max-height: 400px;
  object-fit: contain;
}

.scan-image-overlay {
  flex-shrink: 0;
  width: 160px;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 2px solid var(--border-light);
}

.scan-image-overlay img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.overlay-label {
  display: block;
  text-align: center;
  padding: 0.25rem;
  background: var(--primary);
  color: #fff;
  font-size: 0.6875rem;
  font-weight: 600;
}

.image-placeholder {
  padding: 3rem;
  color: var(--text-muted);
  font-size: 1.5rem;
}

.analysis-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.score-card,
.radar-card {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.defects-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.defect-item {
  padding: 0.75rem 1rem;
  background: var(--bg-body);
  border-radius: var(--radius-md);
  border-right: 3px solid var(--border-color);
}

.defect-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.defect-severity {
  padding: 0.125rem 0.5rem;
  border-radius: var(--radius-full);
  font-size: 0.625rem;
  font-weight: 700;
  color: #fff;
}

.severity-high { background: var(--danger); }
.severity-medium { background: var(--warning); }
.severity-low { background: var(--success); }

.defect-name {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-primary);
}

.defect-tip {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.product-card {
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--bg-body);
  transition: all var(--transition-fast);
}

.product-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.product-img {
  width: 100%;
  height: 140px;
  object-fit: cover;
}

.product-info {
  padding: 0.75rem;
}

.product-name {
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--text-primary);
  display: block;
}

.product-price {
  font-weight: 700;
  color: var(--primary);
  font-size: 0.875rem;
}

.product-reason {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

.analysis-text {
  line-height: 1.8;
  font-size: 0.9375rem;
  color: var(--text-primary);
  white-space: pre-wrap;
  padding: 1rem;
  background: var(--bg-body);
  border-radius: var(--radius-md);
}

.raw-json {
  background: var(--bg-body);
  border-radius: var(--radius-md);
  padding: 1rem;
  font-size: 0.75rem;
  color: var(--text-primary);
  overflow-x: auto;
  max-height: 500px;
  direction: ltr;
  text-align: left;
  font-family: 'Courier New', monospace;
  line-height: 1.5;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
  color: var(--text-secondary);
}

@media (max-width: 992px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .detail-sidebar {
    order: -1;
  }

  .analysis-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .detail-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    flex-wrap: wrap;
  }

  .header-actions .btn {
    flex: 1;
  }
}

.printable-report {
  display: none;
}

.report-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.report-logo-img {
  width: 80px;
  height: 80px;
  object-fit: contain;
}

.report-logo-placeholder {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  background: #f0f0f0;
  border-radius: 8px;
}

.report-clinic-info {
  flex: 1;
}

.report-clinic-name {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0 0 0.25rem;
}

.report-clinic-address,
.report-clinic-contact {
  font-size: 0.8125rem;
  color: #666;
  margin: 0.125rem 0;
}

.report-title-section {
  text-align: left;
}

.report-title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0;
}

.report-date,
.report-id {
  font-size: 0.75rem;
  color: #666;
  margin: 0.125rem 0;
}

.report-divider {
  height: 2px;
  background: #1a8870;
  margin: 1rem 0;
}

.report-section {
  margin-bottom: 1.25rem;
}

.report-section h3 {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #1a8870;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8125rem;
}

.report-table th,
.report-table td {
  border: 1px solid #ddd;
  padding: 0.5rem;
  text-align: right;
}

.report-table th {
  background: #f5f5f5;
  font-weight: 600;
}

.report-table .label {
  font-weight: 600;
  width: 120px;
  background: #fafafa;
}

.report-score-box {
  text-align: center;
  padding: 1.5rem;
  background: #e8f5e9;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.report-score-value {
  font-size: 3rem;
  font-weight: 800;
  color: #1a8870;
}

.report-score-label {
  font-size: 0.875rem;
  color: #555;
  margin-top: 0.25rem;
}

.report-analysis-text {
  font-size: 0.8125rem;
  line-height: 1.8;
  color: #333;
  white-space: pre-wrap;
}

.report-footer {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
  font-size: 0.75rem;
  color: #999;
}

@media print {
  .sidebar,
  .top-header,
  .detail-header,
  .detail-grid,
  .no-print,
  .loading-state,
  .raw-data-card,
  .modal-overlay {
    display: none !important;
  }

  .printable-report {
    display: block !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .scan-detail {
    gap: 0;
  }

  body {
    background: white !important;
    color: black !important;
  }
}
</style>
