<template>
  <div class="spin-codes-page" dir="rtl">
    <div class="page-header">
      <div>
        <h1 class="page-title">أكواد الدولب</h1>
        <p class="page-subtitle">إدارة أكواد عجلة الحظ - إنشاء وتتبع الاستخدام</p>
      </div>
      <button class="btn btn-primary" @click="showGenerateModal = true">
        <span class="btn-icon">+</span>
        <span>إنشاء كود جديد</span>
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card stat-total">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <path d="M9 9h6v6H9z"/>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">إجمالي الأكواد</span>
        </div>
      </div>

      <div class="stat-card stat-active">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9 12l2 2 4-4"/>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.active }}</span>
          <span class="stat-label">أكواد فعالة</span>
        </div>
      </div>

      <div class="stat-card stat-used">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 6L9 17l-5-5"/>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.used }}</span>
          <span class="stat-label">أكواد مستخدمة</span>
        </div>
      </div>

      <div class="stat-card stat-rate">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.usageRate }}%</span>
          <span class="stat-label">نسبة الاستخدام</span>
        </div>
      </div>
    </div>

    <!-- Progress Bar Card -->
    <div class="progress-card">
      <div class="progress-header">
        <span class="progress-title">تقدم استخدام الأكواد</span>
        <span class="progress-stats">{{ stats.used }} من {{ stats.total }} كود مستخدم</span>
      </div>
      <div class="progress-bar-container">
        <div class="progress-bar">
          <div class="progress-fill-used" :style="{ width: stats.usageRate + '%' }"></div>
          <div class="progress-fill-active" :style="{ width: (100 - stats.usageRate) + '%' }"></div>
        </div>
      </div>
      <div class="progress-legend">
        <div class="legend-item">
          <span class="legend-dot used"></span>
          <span>مستخدم ({{ stats.usageRate }}%)</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot active"></span>
          <span>فعال ({{ 100 - stats.usageRate }}%)</span>
        </div>
      </div>
    </div>

    <!-- Codes Table Card -->
    <div class="card">
      <div class="card-header">
        <div class="filters">
          <div class="search-box">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <path d="M21 21l-4.35-4.35"/>
            </svg>
            <input
              v-model="filters.search"
              type="text"
              placeholder="بحث عن كود أو بريد إلكتروني..."
              @input="debouncedSearch"
            />
          </div>
          <select v-model="filters.used" @change="loadCodes">
            <option :value="null">جميع الحالات</option>
            <option :value="false">غير مستخدم</option>
            <option :value="true">مستخدم</option>
          </select>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>الكود</th>
              <th>البريد الإلكتروني</th>
              <th>رقم الطلب</th>
              <th>الهدية</th>
              <th>الحالة</th>
              <th>تاريخ الإنشاء</th>
              <th>تاريخ الاستخدام</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="text-center">
                <div class="loading-spinner"></div>
                <span>جاري التحميل...</span>
              </td>
            </tr>
            <tr v-else-if="codes.length === 0">
              <td colspan="8" class="text-center empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="3" width="18" height="18" rx="2"/>
                  <path d="M9 9h6v6H9z"/>
                </svg>
                <span>لا توجد أكواد بعد</span>
              </td>
            </tr>
            <tr v-for="item in codes" :key="item.id" class="code-row">
              <td class="id-cell">{{ item.id }}</td>
              <td>
                <code class="code-badge">{{ item.code }}</code>
              </td>
              <td class="email-cell">{{ item.customer_email || '-' }}</td>
              <td>
                <span v-if="item.order_id" class="order-link">#{{ item.order_id }}</span>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="gift-cell">{{ item.gift || '-' }}</td>
              <td>
                <span class="status-badge" :class="item.is_used ? 'used' : 'active'">
                  <span class="status-dot"></span>
                  {{ item.is_used ? 'مستخدم' : 'فعال' }}
                </span>
              </td>
              <td class="date-cell">{{ formatDate(item.created_at) }}</td>
              <td class="date-cell">{{ item.used_at ? formatDate(item.used_at) : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta.last_page > 1" class="pagination">
        <button
          :disabled="meta.current_page <= 1"
          @click="changePage(meta.current_page - 1)"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6"/>
          </svg>
          السابق
        </button>
        <div class="page-indicator">
          <span>صفحة</span>
          <span class="current-page">{{ meta.current_page }}</span>
          <span>من</span>
          <span>{{ meta.last_page }}</span>
        </div>
        <button
          :disabled="meta.current_page >= meta.last_page"
          @click="changePage(meta.current_page + 1)"
        >
          التالي
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Generate Modal -->
    <div v-if="showGenerateModal" class="modal-overlay" @click.self="showGenerateModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>إنشاء كود دولب جديد</h3>
          <button class="modal-close" @click="showGenerateModal = false">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>البريد الإلكتروني (اختياري)</label>
            <input v-model="newCode.email" type="email" placeholder="customer@example.com" />
          </div>
          <div class="form-group">
            <label>رقم الطلب (اختياري)</label>
            <input v-model="newCode.order_id" type="number" placeholder="معرف الطلب" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showGenerateModal = false">إلغاء</button>
          <button class="btn btn-primary" @click="generateCode" :disabled="generating">
            {{ generating ? 'جاري الإنشاء...' : 'إنشاء الكود' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'

const codes = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const loading = ref(false)
const showGenerateModal = ref(false)
const generating = ref(false)
const newCode = ref({ email: '', order_id: '' })
const filters = ref({ search: '', used: null })

let searchTimeout = null

const stats = computed(() => {
  const total = meta.value.total || 0
  const used = codes.value.filter(c => c.is_used).length
  const active = codes.value.filter(c => !c.is_used).length
  const usageRate = total > 0 ? Math.round((used / total) * 100) : 0
  return { total, used, active, usageRate }
})

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(loadCodes, 300)
}

async function loadCodes() {
  loading.value = true
  try {
    const params = { page: meta.value.current_page, per_page: 20 }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.used !== null) params.used = filters.value.used

    const res = await apiClient.get('/spin-codes', { params })
    codes.value = res.data.data
    meta.value = res.data.meta
  } catch (e) {
    console.error('Failed to load spin codes', e)
  } finally {
    loading.value = false
  }
}

function changePage(page) {
  meta.value.current_page = page
  loadCodes()
}

async function generateCode() {
  generating.value = true
  try {
    const payload = {}
    if (newCode.value.email) payload.email = newCode.value.email
    if (newCode.value.order_id) payload.order_id = parseInt(newCode.value.order_id)

    await apiClient.post('/spin-codes/generate', payload)
    showGenerateModal.value = false
    newCode.value = { email: '', order_id: '' }
    loadCodes()
  } catch (e) {
    console.error('Failed to generate code', e)
  } finally {
    generating.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(loadCodes)
</script>

<style scoped>
.spin-codes-page {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0;
}

.page-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 4px 0 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: var(--bg-card);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
  box-shadow: var(--shadow-sm);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-total .stat-icon {
  background: var(--primary-bg);
  color: var(--primary);
}

.stat-active .stat-icon {
  background: var(--success-bg);
  color: var(--success);
}

.stat-used .stat-icon {
  background: var(--warning-bg);
  color: var(--warning);
}

.stat-rate .stat-icon {
  background: var(--info-bg);
  color: var(--info);
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1.2;
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-top: 2px;
}

.progress-card {
  background: var(--bg-card);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  padding: 20px 24px;
  margin-bottom: 24px;
  box-shadow: var(--shadow-sm);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.progress-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-primary);
}

.progress-stats {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.progress-bar-container {
  margin-bottom: 12px;
}

.progress-bar {
  height: 12px;
  background: var(--bg-body);
  border-radius: 6px;
  overflow: hidden;
  display: flex;
}

.progress-fill-used {
  height: 100%;
  background: linear-gradient(90deg, #f59e0b, #fbbf24);
  transition: width 0.5s ease;
}

.progress-fill-active {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #34d399);
  transition: width 0.5s ease;
}

.progress-legend {
  display: flex;
  gap: 24px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.legend-dot.used {
  background: #f59e0b;
}

.legend-dot.active {
  background: #10b981;
}

.card {
  background: var(--bg-card);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.card-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color);
}

.filters {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box {
  position: relative;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: var(--text-muted);
}

.search-box input {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  padding: 10px 14px;
  padding-right: 36px;
  border-radius: 10px;
  color: var(--text-primary);
  font-size: 0.875rem;
  width: 280px;
  outline: none;
  transition: all 0.2s;
}

.search-box input::placeholder {
  color: var(--text-muted);
}

.search-box input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
}

.filters select {
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  padding: 10px 14px;
  border-radius: 10px;
  color: var(--text-primary);
  font-size: 0.875rem;
  outline: none;
  cursor: pointer;
  transition: all 0.2s;
}

.filters select:focus {
  border-color: var(--primary);
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: right;
  padding: 14px 16px;
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid var(--border-color);
  white-space: nowrap;
  background: var(--bg-body);
}

.data-table td {
  padding: 14px 16px;
  font-size: 0.875rem;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-light);
}

.code-row {
  transition: background 0.2s;
}

.code-row:hover td {
  background: var(--primary-bg);
}

.id-cell {
  color: var(--text-muted);
  font-size: 0.8rem;
}

.code-badge {
  background: var(--primary-bg);
  color: var(--primary);
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  letter-spacing: 1px;
}

.email-cell {
  color: var(--text-secondary);
}

.order-link {
  color: var(--primary);
  font-weight: 600;
}

.text-muted {
  color: var(--text-muted);
}

.gift-cell {
  color: var(--text-secondary);
}

.date-cell {
  color: var(--text-secondary);
  font-size: 0.8rem;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.status-badge.active {
  background: var(--success-bg);
  color: var(--success);
}

.status-badge.active .status-dot {
  background: var(--success);
}

.status-badge.used {
  background: var(--warning-bg);
  color: var(--warning);
}

.status-badge.used .status-dot {
  background: var(--warning);
}

.text-center {
  text-align: center;
  padding: 48px 16px;
  color: var(--text-muted);
}

.text-center span {
  display: block;
  margin-top: 12px;
}

.empty-state svg {
  width: 48px;
  height: 48px;
  color: var(--text-muted);
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-top: 1px solid var(--border-color);
}

.pagination button {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.2s;
}

.pagination button svg {
  width: 14px;
  height: 14px;
}

.pagination button:hover:not(:disabled) {
  background: var(--primary-bg);
  border-color: var(--primary);
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.current-page {
  color: var(--primary);
  font-weight: 700;
}

.btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.875rem;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-icon {
  font-size: 1.2rem;
  line-height: 1;
}

.btn-primary {
  background: var(--primary);
  color: #fff;
  box-shadow: 0 4px 12px rgba(26, 136, 112, 0.3);
}

.btn-primary:hover {
  background: var(--primary-light);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(26, 136, 112, 0.4);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: var(--bg-body);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--border-color);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}

.modal-card {
  background: var(--bg-card);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  width: 100%;
  max-width: 440px;
  overflow: hidden;
  box-shadow: var(--shadow-xl);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px 0;
}

.modal-header h3 {
  color: var(--text-primary);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 1.5rem;
  cursor: pointer;
}

.modal-body {
  padding: 20px 24px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid var(--border-color);
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-bottom: 6px;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  padding: 10px 14px;
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.9rem;
  outline: none;
  box-sizing: border-box;
}

.form-group input::placeholder {
  color: var(--text-muted);
}

.form-group input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
  }
  
  .filters {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box input {
    width: 100%;
  }
}
</style>
