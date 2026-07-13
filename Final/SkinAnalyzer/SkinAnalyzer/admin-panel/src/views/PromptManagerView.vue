<template>
  <div class="prompts-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">📝 إدارة التعليمات</h2>
        <p class="page-desc">إدارة تعليمات الذكاء الاصطناعي (System Prompts)</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">
        <span>+</span> تعليمة جديدة
      </button>
    </div>

    <div class="prompts-layout">
      <div class="prompts-list">
        <div v-if="loading && prompts.length === 0" class="loading-state">
          <div class="spinner spinner-lg"></div>
          <p>جاري التحميل...</p>
        </div>

        <div v-else-if="prompts.length === 0" class="empty-state">
          <div class="empty-state-icon">📝</div>
          <div class="empty-state-title">لا توجد تعليمات</div>
          <div class="empty-state-desc">قم بإنشاء تعليماتك الأولى</div>
        </div>

        <div v-else class="prompts-items">
          <div
            v-for="prompt in prompts"
            :key="prompt.id"
            class="prompt-item"
            :class="{ active: selectedPrompt?.id === prompt.id }"
            @click="selectPrompt(prompt)"
          >
            <div class="prompt-item-header">
              <span class="prompt-item-name">{{ prompt.name }}</span>
              <span class="badge" :class="'badge-' + (prompt.is_active ? 'success' : 'muted')">
                {{ prompt.is_active ? 'نشط' : 'غير نشط' }}
              </span>
            </div>
            <div class="prompt-item-meta">
              <span>{{ prompt.provider_name || 'كل المزودين' }}</span>
              <span>{{ prompt.tone || 'متوازن' }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="prompts-editor" v-if="selectedPrompt">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">تحرير التعليمة</h3>
            <div class="editor-actions">
              <button class="btn btn-sm btn-secondary" @click="togglePreview">
                👁 {{ showPreview ? 'تحرير' : 'معاينة' }}
              </button>
              <button class="btn btn-sm btn-primary" @click="savePrompt" :disabled="saving">
                <span v-if="saving" class="spinner"></span>
                <span v-else>💾 حفظ</span>
              </button>
            </div>
          </div>

          <div class="editor-form" v-if="!showPreview">
            <div class="form-group">
              <label class="form-label">اسم التعليمة</label>
              <input v-model="editForm.name" type="text" class="form-input" placeholder="اسم التعليمة" />
            </div>

            <div class="form-group">
              <label class="form-label">المزود</label>
              <select v-model="editForm.provider_id" class="form-input">
                <option value="">كل المزودين</option>
                <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">نبرة الرد</label>
              <select v-model="editForm.tone" class="form-input">
                <option value="balanced">متوازن</option>
                <option value="medical">طبي</option>
                <option value="promotional">ترويجي</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">نص التعليمة</label>
              <textarea
                v-model="editForm.prompt_text"
                class="form-input prompt-textarea"
                rows="12"
                placeholder="أدخل نص التعليمة هنا..."
              ></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">نشط</label>
              <div class="toggle-wrapper">
                <span
                  class="status-toggle"
                  :class="{ active: editForm.is_active }"
                  @click="editForm.is_active = !editForm.is_active"
                >
                  <span class="toggle-knob"></span>
                </span>
              </div>
            </div>
          </div>

          <div v-else class="preview-section">
            <div class="preview-box">
              <h4>معاينة الناتج</h4>
              <p class="preview-text">{{ previewText }}</p>
              <p class="preview-note">هذه معاينة مبدئية. الناتج الفعلي يعتمد على نموذج الذكاء الاصطناعي.</p>
            </div>
          </div>
        </div>

        <div class="card" v-if="promptHistory.length > 0">
          <h4 class="card-title">📜 سجل الإصدارات</h4>
          <div class="history-list">
            <div v-for="ver in promptHistory" :key="ver.id" class="history-item">
              <span class="history-date">{{ formatDate(ver.created_at) }}</span>
              <span class="history-version">v{{ ver.version }}</span>
              <button class="btn btn-sm btn-secondary" @click="restoreVersion(ver)">استعادة</button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="prompts-editor empty-editor">
        <div class="empty-state">
          <div class="empty-state-icon">👈</div>
          <div class="empty-state-title">اختر تعليمة</div>
          <div class="empty-state-desc">اختر تعليمة من القائمة لتحريرها</div>
        </div>
      </div>

      <div class="variables-sidebar">
        <div class="card">
          <h4 class="card-title">🔤 متغيرات القالب</h4>
          <div class="variables-list">
            <div
              v-for="v in templateVariables"
              :key="v.key"
              class="variable-item"
              @click="insertVariable(v.key)"
              title="انقر للإدراج"
            >
              <code>{{ '{' + v.key + '}' }}</code>
              <span>{{ v.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="createModal" class="modal-overlay" @click.self="createModal = false">
      <div class="modal-content animate-slideUp">
        <div class="modal-header">
          <h3 class="modal-title">تعليمة جديدة</h3>
          <button class="btn btn-sm btn-secondary" @click="createModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">اسم التعليمة</label>
            <input v-model="newPrompt.name" type="text" class="form-input" placeholder="أدخل الاسم" />
          </div>
          <div class="form-group">
            <label class="form-label">نص التعليمة</label>
            <textarea v-model="newPrompt.prompt_text" class="form-input prompt-textarea" rows="8" placeholder="نص التعليمة..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="createModal = false">إلغاء</button>
          <button class="btn btn-primary" @click="createPrompt" :disabled="creating">
            <span v-if="creating" class="spinner"></span>
            <span v-else>إنشاء</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { promptsApi } from '@/api/endpoints'
import { useAIProvidersStore } from '@/stores/aiProviders'
import dayjs from 'dayjs'
import Swal from 'sweetalert2'

const providersStore = useAIProvidersStore()
const providers = computed(() => providersStore.providers)

const prompts = ref([])
const selectedPrompt = ref(null)
const loading = ref(false)
const saving = ref(false)
const creating = ref(false)
const showPreview = ref(false)
const promptHistory = ref([])
const createModal = ref(false)

const editForm = reactive({
  name: '',
  provider_id: '',
  tone: 'balanced',
  prompt_text: '',
  is_active: true
})

const newPrompt = reactive({
  name: '',
  prompt_text: ''
})

const templateVariables = [
  { key: 'skin_type', label: 'نوع البشرة' },
  { key: 'defects', label: 'المشاكل المكتشفة' },
  { key: 'product_names', label: 'أسماء المنتجات' },
  { key: 'customer_name', label: 'اسم العميل' },
  { key: 'center_name', label: 'اسم المركز' },
  { key: 'score', label: 'المعدل الصحي' },
  { key: 'recommendations', label: 'التوصيات' }
]

const previewText = computed(() => {
  if (!editForm.prompt_text) return 'لا يوجد نص للمعاينة'
  return editForm.prompt_text
    .replace(/{skin_type}/g, 'دهنية')
    .replace(/{defects}/g, 'حب شباب، مسام واسعة')
    .replace(/{product_names}/g, 'غسول نيفيا، كريم بانثينول')
    .replace(/{customer_name}/g, 'أحمد')
    .replace(/{center_name}/g, 'مركز العناية')
    .replace(/{score}/g, '78%')
    .replace(/{recommendations}/g, 'استخدام غسول يومي + كريم واقي شمس')
})

function formatDate(date) {
  if (!date) return ''
  return dayjs(date).format('YYYY/MM/DD hh:mm')
}

function selectPrompt(prompt) {
  selectedPrompt.value = prompt
  editForm.name = prompt.name
  editForm.provider_id = prompt.provider_id || ''
  editForm.tone = prompt.tone || 'balanced'
  editForm.prompt_text = prompt.prompt_text || ''
  editForm.is_active = prompt.is_active
  showPreview.value = false
  fetchHistory(prompt.id)
}

function insertVariable(key) {
  if (!editForm.prompt_text.includes('{')) {
    editForm.prompt_text += ` {${key}}`
  } else {
    editForm.prompt_text += ` {${key}}`
  }
}

async function fetchPrompts() {
  loading.value = true
  try {
    const { data } = await promptsApi.list()
    prompts.value = data.prompts || data.data || []
  } catch (err) {
    console.error('Failed to fetch prompts:', err)
  } finally {
    loading.value = false
  }
}

async function fetchHistory(promptId) {
  try {
    const { data } = await promptsApi.history(promptId)
    promptHistory.value = data.versions || data.history || []
  } catch {
    promptHistory.value = []
  }
}

async function savePrompt() {
  if (!selectedPrompt.value) return
  saving.value = true
  try {
    const payload = { ...editForm }
    const { data } = await promptsApi.update(selectedPrompt.value.id, payload)
    const idx = prompts.value.findIndex(p => p.id === selectedPrompt.value.id)
    if (idx >= 0) prompts.value[idx] = { ...prompts.value[idx], ...payload }
    Swal.fire({ title: 'تم الحفظ', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل الحفظ', icon: 'error' })
  } finally {
    saving.value = false
  }
}

async function createPrompt() {
  if (!newPrompt.name.trim()) {
    Swal.fire({ title: 'خطأ', text: 'الرجاء إدخال اسم التعليمة', icon: 'warning' })
    return
  }
  creating.value = true
  try {
    const { data } = await promptsApi.create({ ...newPrompt })
    prompts.value.unshift(data.prompt || data)
    createModal.value = false
    newPrompt.name = ''
    newPrompt.prompt_text = ''
    Swal.fire({ title: 'تم الإنشاء', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل الإنشاء', icon: 'error' })
  } finally {
    creating.value = false
  }
}

function openCreateModal() {
  newPrompt.name = ''
  newPrompt.prompt_text = ''
  createModal.value = true
}

async function restoreVersion(version) {
  const result = await Swal.fire({
    title: 'استعادة الإصدار؟',
    text: `استعادة الإصدار v${version.version}؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'استعادة',
    cancelButtonText: 'إلغاء'
  })

  if (result.isConfirmed && selectedPrompt.value) {
    editForm.prompt_text = version.prompt_text
    await savePrompt()
  }
}

function togglePreview() {
  showPreview.value = !showPreview.value
}

function handleRefresh() {
  fetchPrompts()
}

onMounted(() => {
  fetchPrompts()
  providersStore.fetchProviders()
  window.addEventListener('admin-refresh', handleRefresh)
})
</script>

<style lang="scss" scoped>
.prompts-page {
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

.prompts-layout {
  display: grid;
  grid-template-columns: 280px 1fr 220px;
  gap: 1.5rem;
  min-height: 500px;
}

.prompts-list {
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--bg-card);
  overflow: hidden;
}

.prompts-items {
  display: flex;
  flex-direction: column;
}

.prompt-item {
  padding: 0.875rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid var(--border-light);
  transition: background var(--transition-fast);
}

.prompt-item:hover {
  background: var(--primary-bg);
}

.prompt-item.active {
  background: var(--primary-bg);
  border-right: 3px solid var(--primary);
}

.prompt-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.prompt-item-name {
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--text-primary);
}

.prompt-item-meta {
  font-size: 0.6875rem;
  color: var(--text-muted);
  display: flex;
  gap: 0.75rem;
}

.empty-editor {
  display: flex;
  align-items: center;
  justify-content: center;
}

.editor-actions {
  display: flex;
  gap: 0.5rem;
}

.prompt-textarea {
  font-family: 'Courier New', monospace;
  font-size: 0.8125rem;
  direction: ltr;
  text-align: left;
  min-height: 250px;
}

.toggle-wrapper {
  display: flex;
  align-items: center;
}

.status-toggle {
  width: 44px;
  height: 24px;
  background: var(--border-color);
  border-radius: var(--radius-full);
  position: relative;
  cursor: pointer;
  display: block;
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

.preview-section {
  padding: 1rem 0;
}

.preview-box {
  background: var(--bg-body);
  border-radius: var(--radius-md);
  padding: 1.25rem;
}

.preview-box h4 {
  margin-bottom: 0.75rem;
  color: var(--text-primary);
}

.preview-text {
  font-size: 0.875rem;
  line-height: 1.8;
  color: var(--text-primary);
  white-space: pre-wrap;
}

.preview-note {
  margin-top: 1rem;
  font-size: 0.75rem;
  color: var(--text-muted);
  font-style: italic;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.history-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.75rem;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
}

.history-date {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.history-version {
  font-weight: 600;
  font-size: 0.75rem;
  color: var(--primary);
}

.variables-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.variables-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.variable-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
  border: 1px solid transparent;
  font-size: 0.8125rem;
}

.variable-item:hover {
  border-color: var(--primary);
  background: var(--primary-bg);
}

.variable-item code {
  background: var(--primary-bg);
  color: var(--primary);
  padding: 0.125rem 0.375rem;
  border-radius: var(--radius-sm);
  font-size: 0.6875rem;
  direction: ltr;
}

@media (max-width: 992px) {
  .prompts-layout {
    grid-template-columns: 1fr;
  }

  .variables-sidebar {
    order: -1;
  }
}
</style>
