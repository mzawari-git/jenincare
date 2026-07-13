<template>
  <div class="ai-providers-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">🤖 مزودي الذكاء الاصطناعي</h2>
        <p class="page-desc">إدارة مزودي الذكاء الاصطناعي وصلاحياتهم</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddModal">
          <span>+</span> إضافة مزود جديد
        </button>
        <button class="btn btn-secondary" @click="refreshProviders">
          <span>↻</span> تحديث
        </button>
      </div>
    </div>

    <div v-if="quotaAlerts.length > 0" class="quota-alerts">
      <div v-for="alert in quotaAlerts" :key="alert.id" class="alert-item animate-fadeIn">
        <span>⚠️</span>
        <span>{{ alert.name }}: تجاوز {{ Math.round((alert.quota_used / alert.quota_limit) * 100) }}% من الحد المسموح</span>
      </div>
    </div>

    <div v-if="loading && providers.length === 0" class="loading-state">
      <div class="spinner spinner-lg"></div>
      <p>جاري تحميل المزودين...</p>
    </div>

    <div v-else class="providers-grid">
      <div
        v-for="provider in providers"
        :key="provider.id"
        class="provider-card card"
        :class="{ 'provider-native': provider.engine === 'native', 'provider-active': provider.is_active }"
      >
        <div class="provider-card-header">
          <div class="provider-icon">
            <span>{{ providerIcon(provider.engine) }}</span>
          </div>
          <div class="provider-name-section">
            <h3 class="provider-name">{{ provider.name }}</h3>
            <span class="provider-engine">
              {{ provider.engine_label || provider.engine }}
              <span v-if="provider.engine === 'native'" class="badge badge-primary native-badge">أساسي مدمج</span>
            </span>
          </div>
          <div class="provider-status">
            <span
              class="status-toggle"
              :class="{ active: provider.is_active }"
              @click="toggleProvider(provider)"
              :title="provider.is_active ? 'إلغاء التفعيل' : 'تفعيل'"
            >
              <span class="toggle-knob"></span>
            </span>
            <span class="status-label">{{ provider.is_active ? 'نشط' : 'غير نشط' }}</span>
          </div>
        </div>

        <div class="provider-quota" v-if="provider.quota_limit > 0">
          <div class="quota-header">
            <span class="quota-label">الحصة المستخدمة</span>
            <span class="quota-value">{{ provider.quota_used || 0 }} / {{ provider.quota_limit }}</span>
          </div>
          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: quotaPercent(provider) + '%', background: quotaColor(provider) }"
            ></div>
          </div>
        </div>

        <div class="provider-meta" v-if="provider.last_check_at">
          <span class="meta-item">🕐 آخر فحص: {{ formatDate(provider.last_check_at) }}</span>
        </div>

        <div class="provider-actions">
          <button
            class="btn btn-sm btn-info"
            @click="openCredentialsModal(provider)"
            :disabled="provider.engine === 'native'"
          >
            ⚙️ بيانات الاعتماد
          </button>
          <button
            class="btn btn-sm btn-secondary"
            @click="testConnection(provider)"
          >
            🔌 اختبار الاتصال
          </button>
        </div>
      </div>

      <div v-if="providers.length === 0 && !loading" class="empty-state">
        <div class="empty-state-icon">🤖</div>
        <div class="empty-state-title">لا يوجد مزودين</div>
        <div class="empty-state-desc">لم يتم إضافة مزودي ذكاء اصطناعي بعد</div>
      </div>
    </div>

    <div v-if="credentialsModal.provider" class="modal-overlay" @click.self="credentialsModal.provider = null">
      <div class="modal-content animate-slideUp">
        <div class="modal-header">
          <h3 class="modal-title">⚙️ بيانات اعتماد {{ credentialsModal.provider.name }}</h3>
          <button class="btn btn-sm btn-secondary" @click="credentialsModal.provider = null">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">مفتاح API</label>
            <input
              v-model="credentialsModal.api_key"
              type="password"
              class="form-input"
              placeholder="sk-..."
            />
          </div>
          <div class="form-group">
            <label class="form-label">رابط API Endpoint</label>
            <input
              v-model="credentialsModal.endpoint"
              type="text"
              class="form-input"
              placeholder="https://api.example.com/v1"
            />
          </div>
          <div class="form-group">
            <label class="form-label">معرف النموذج</label>
            <input
              v-model="credentialsModal.model_id"
              type="text"
              class="form-input"
              placeholder="gpt-4-vision-preview"
            />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="credentialsModal.provider = null">إلغاء</button>
          <button class="btn btn-primary" @click="saveCredentials" :disabled="saving">
            <span v-if="saving" class="spinner"></span>
            <span v-else>حفظ</span>
          </button>
        </div>
      </div>
    </div>
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal-content animate-slideUp" style="max-width: 520px;">
        <div class="modal-header">
          <h3 class="modal-title">➕ إضافة مزود ذكاء اصطناعي جديد</h3>
          <button class="btn btn-sm btn-secondary" @click="showAddModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">الاسم</label>
            <input v-model="addForm.name" type="text" class="form-input" placeholder="مثال: My Custom AI" />
          </div>
          <div class="form-group">
            <label class="form-label">المعرف (driver_key)</label>
            <input v-model="addForm.driver_key" type="text" class="form-input" placeholder="مثال: mycustomai" />
            <small class="form-hint">يستخدم معرف فريد باللغة الإنجليزية بدون مسافات</small>
          </div>
          <div class="form-group">
            <label class="form-label">نوع المحرك</label>
            <select v-model="addForm.engine_type" class="form-input">
              <option value="structured">منظم (Structured) — تحليل عددي</option>
              <option value="generative">توليدي (Generative) — نصوص وتقارير</option>
              <option value="hybrid">هجين (Hybrid) — تحليل وتقارير</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">مفتاح API</label>
            <input v-model="addForm.api_key" type="password" class="form-input" placeholder="sk-..." />
          </div>
          <div class="form-group">
            <label class="form-label">رابط API Endpoint</label>
            <input v-model="addForm.endpoint_url" type="text" class="form-input" placeholder="https://api.example.com/v1" />
          </div>
          <div class="form-group">
            <label class="form-label">معرف النموذج</label>
            <input v-model="addForm.model" type="text" class="form-input" placeholder="gpt-4-vision-preview" />
          </div>
          <div class="form-group">
            <label class="form-label">الحد الأقصى للاستخدام (0 = غير محدود)</label>
            <input v-model.number="addForm.quota_limit" type="number" class="form-input" placeholder="1000" min="0" />
          </div>
          <div class="form-group">
            <label class="form-label">وصف</label>
            <input v-model="addForm.description" type="text" class="form-input" placeholder="وصف مختصر للمزود" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showAddModal = false">إلغاء</button>
          <button class="btn btn-primary" @click="createProvider" :disabled="creating">
            <span v-if="creating" class="spinner"></span>
            <span v-else>إضافة</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAIProvidersStore } from '@/stores/aiProviders'
import dayjs from 'dayjs'
import Swal from 'sweetalert2'

const store = useAIProvidersStore()
const saving = ref(false)
const creating = ref(false)
const showAddModal = ref(false)

const { providers, loading, quotaAlerts } = store

const credentialsModal = reactive({
  provider: null,
  api_key: '',
  endpoint: '',
  model_id: ''
})

const addForm = reactive({
  name: '',
  driver_key: '',
  engine_type: 'structured',
  api_key: '',
  endpoint_url: '',
  model: '',
  quota_limit: 1000,
  description: ''
})

function openAddModal() {
  Object.assign(addForm, {
    name: '',
    driver_key: '',
    engine_type: 'structured',
    api_key: '',
    endpoint_url: '',
    model: '',
    quota_limit: 1000,
    description: ''
  })
  showAddModal.value = true
}

async function createProvider() {
  if (!addForm.name || !addForm.driver_key || !addForm.engine_type) {
    Swal.fire({ title: 'حقول مطلوبة', text: 'الرجاء تعبئة الاسم والمعرف ونوع المحرك', icon: 'warning' })
    return
  }
  creating.value = true
  const res = await store.createProvider({ ...addForm })
  creating.value = false
  if (res.success) {
    Swal.fire({ title: 'تمت الإضافة', text: `تم إضافة ${addForm.name} بنجاح`, icon: 'success', timer: 1500, showConfirmButton: false })
    showAddModal.value = false
  } else {
    Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
  }
}

function providerIcon(engine) {
    const icons = {
      native: '🏠',
      openai: '🧠',
      gemini: '💎',
      claude: '🟣',
      huggingface: '🤗',
      custom: '🔧'
    }
    return icons[engine] || '🤖'
  }

function quotaPercent(provider) {
  if (!provider.quota_limit) return 0
  return Math.round((provider.quota_used / provider.quota_limit) * 100)
}

function quotaColor(provider) {
  const pct = quotaPercent(provider)
  if (pct > 80) return '#ef4444'
  if (pct > 60) return '#f59e0b'
  return '#22c55e'
}

function formatDate(date) {
  if (!date) return ''
  return dayjs(date).format('YYYY/MM/DD hh:mm A')
}

async function toggleProvider(provider) {
  if (provider.engine === 'native') {
    Swal.fire({ title: 'لا يمكن تعطيل المزود الأساسي', text: 'المزود المدمج نشط دائماً', icon: 'info' })
    return
  }

  const res = await store.toggleProvider(provider.id)
  if (!res.success) {
    Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
  }
}

function openCredentialsModal(provider) {
  if (provider.engine === 'native') return
  credentialsModal.provider = provider
  credentialsModal.api_key = provider.api_key || ''
  credentialsModal.endpoint = provider.endpoint || ''
  credentialsModal.model_id = provider.model_id || ''
}

async function saveCredentials() {
  if (!credentialsModal.provider) return
  saving.value = true
  const res = await store.updateCredentials(credentialsModal.provider.id, {
    api_key: credentialsModal.api_key,
    endpoint: credentialsModal.endpoint,
    model_id: credentialsModal.model_id
  })
  saving.value = false

  if (res.success) {
    Swal.fire({ title: 'تم الحفظ', icon: 'success', timer: 1500, showConfirmButton: false })
    credentialsModal.provider = null
  } else {
    Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
  }
}

async function testConnection(provider) {
  Swal.fire({
    title: 'جاري اختبار الاتصال...',
    text: `اختبار الاتصال بـ ${provider.name}`,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading()
    }
  })

  const res = await store.testConnection(provider.id)

  if (res.success) {
    Swal.fire({
      title: 'تم الاتصال بنجاح',
      text: `${res.message} - زمن الاستجابة: ${res.latency || '---'}ms`,
      icon: 'success',
      confirmButtonColor: '#1a8870'
    })
  } else {
    Swal.fire({
      title: 'فشل الاتصال',
      text: res.message,
      icon: 'error'
    })
  }
}

async function refreshProviders() {
  await store.fetchProviders()
}

function handleRefresh() {
  refreshProviders()
}

onMounted(() => {
  store.fetchProviders()
  window.addEventListener('admin-refresh', handleRefresh)
})
</script>

<style lang="scss" scoped>
.ai-providers-page {
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

.header-actions {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
}

.form-hint {
  display: block;
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
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

.quota-alerts {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--warning-bg);
  border-radius: var(--radius-md);
  font-size: 0.8125rem;
  color: var(--warning);
  font-weight: 500;
}

.providers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 1.25rem;
}

.provider-card {
  transition: all var(--transition-normal);
}

.provider-card.provider-active {
  border-color: rgba(34, 197, 94, 0.3);
}

.provider-card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.provider-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
  border-radius: var(--radius-md);
  font-size: 1.5rem;
}

.provider-name-section {
  flex: 1;
  min-width: 0;
}

.provider-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.provider-engine {
  font-size: 0.75rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.native-badge {
  font-size: 0.625rem;
}

.provider-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.status-toggle {
  width: 44px;
  height: 24px;
  background: var(--border-color);
  border-radius: var(--radius-full);
  position: relative;
  cursor: pointer;
  transition: background var(--transition-fast);
}

.status-toggle.active {
  background: var(--success);
}

.toggle-knob {
  position: absolute;
  top: 3px;
  right: 3px;
  width: 18px;
  height: 18px;
  background: #fff;
  border-radius: 50%;
  transition: transform var(--transition-fast);
  box-shadow: var(--shadow-sm);
}

.status-toggle.active .toggle-knob {
  transform: translateX(-20px);
}

.status-label {
  font-size: 0.6875rem;
  color: var(--text-muted);
}

.provider-quota {
  margin-bottom: 0.75rem;
}

.quota-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  margin-bottom: 0.375rem;
}

.quota-label {
  color: var(--text-secondary);
}

.quota-value {
  font-weight: 600;
  color: var(--text-primary);
}

.progress-bar {
  height: 6px;
  background: var(--bg-body);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: var(--radius-full);
  transition: width 0.6s ease;
}

.provider-meta {
  margin-bottom: 0.75rem;
}

.meta-item {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.provider-actions {
  display: flex;
  gap: 0.5rem;
}

@media (max-width: 768px) {
  .providers-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .page-header {
    flex-direction: column;
  }

  .provider-actions {
    flex-wrap: wrap;
  }
}
</style>
