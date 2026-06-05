<template>
  <div class="spin-codes-page" dir="rtl">
    <div class="page-header">
      <div>
        <h1 class="page-title">أكواد الدولب</h1>
        <p class="page-subtitle">إدارة أكواد عجلة الحظ - إنشاء وتتبع الاستخدام</p>
      </div>
      <button class="btn btn-primary" @click="showGenerateModal = true">
        <span>+ إنشاء كود جديد</span>
      </button>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="filters">
          <div class="search-box">
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
              <td colspan="8" class="text-center">جاري التحميل...</td>
            </tr>
            <tr v-else-if="codes.length === 0">
              <td colspan="8" class="text-center">لا توجد أكواد بعد</td>
            </tr>
            <tr v-for="item in codes" :key="item.id">
              <td>{{ item.id }}</td>
              <td>
                <code class="code-badge">{{ item.code }}</code>
              </td>
              <td>{{ item.customer_email || '-' }}</td>
              <td>
                <span v-if="item.order_id" class="order-link">#{{ item.order_id }}</span>
                <span v-else>-</span>
              </td>
              <td>{{ item.gift || '-' }}</td>
              <td>
                <span class="status-badge" :class="item.is_used ? 'used' : 'active'">
                  {{ item.is_used ? 'مستخدم' : 'فعال' }}
                </span>
              </td>
              <td>{{ formatDate(item.created_at) }}</td>
              <td>{{ item.used_at ? formatDate(item.used_at) : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta.last_page > 1" class="pagination">
        <button
          :disabled="meta.current_page <= 1"
          @click="changePage(meta.current_page - 1)"
        >السابق</button>
        <span>صفحة {{ meta.current_page }} من {{ meta.last_page }}</span>
        <button
          :disabled="meta.current_page >= meta.last_page"
          @click="changePage(meta.current_page + 1)"
        >التالي</button>
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
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'

const codes = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const loading = ref(false)
const showGenerateModal = ref(false)
const generating = ref(false)
const newCode = ref({ email: '', order_id: '' })
const filters = ref({ search: '', used: null })

let searchTimeout = null

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(loadCodes, 300)
}

async function loadCodes() {
  loading.value = true
  try {
    const params = { page: meta.value.current_page }
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
  color: #fff;
  margin: 0;
}

.page-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  margin: 4px 0 0;
}

.card {
  background: var(--bg-card, #1e293b);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.06);
  overflow: hidden;
}

.card-header {
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.filters {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box input {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 14px;
  border-radius: 8px;
  color: #fff;
  font-size: 0.875rem;
  width: 260px;
  outline: none;
  transition: border-color 0.2s;
}

.search-box input:focus {
  border-color: var(--primary, #6366f1);
}

.filters select {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 14px;
  border-radius: 8px;
  color: #fff;
  font-size: 0.875rem;
  outline: none;
  cursor: pointer;
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
  padding: 12px 16px;
  font-size: 0.75rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  white-space: nowrap;
}

.data-table td {
  padding: 12px 16px;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.data-table tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}

.code-badge {
  background: rgba(99, 102, 241, 0.15);
  color: #818cf8;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  letter-spacing: 1px;
}

.order-link {
  color: var(--primary, #6366f1);
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-badge.active {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.status-badge.used {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.text-center {
  text-align: center;
  padding: 40px 16px;
  color: rgba(255, 255, 255, 0.4);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
}

.pagination button {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  padding: 6px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.pagination button:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}

.modal-card {
  background: var(--bg-card, #1e293b);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  width: 100%;
  max-width: 440px;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px 0;
}

.modal-header h3 {
  color: #fff;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.4);
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
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 6px;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 10px 14px;
  border-radius: 8px;
  color: #fff;
  font-size: 0.9rem;
  outline: none;
  box-sizing: border-box;
}

.form-group input:focus {
  border-color: var(--primary, #6366f1);
}

.btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.875rem;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--primary, #6366f1);
  color: #fff;
}

.btn-primary:hover {
  background: var(--primary-hover, #5457e5);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.7);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}
</style>
