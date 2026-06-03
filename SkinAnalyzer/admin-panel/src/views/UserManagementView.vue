<template>
  <div class="users-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">👤 إدارة المستخدمين</h2>
        <p class="page-desc">إضافة وتعديل وتعطيل حسابات المستخدمين</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openAddModal">
          <span>+</span> إضافة مستخدم
        </button>
        <button class="btn btn-secondary" @click="fetchUsers">
          <span>↻</span> تحديث
        </button>
      </div>
    </div>

    <div class="search-bar">
      <input
        v-model="searchQuery"
        type="text"
        class="form-input"
        placeholder="بحث بالاسم، البريد الإلكتروني أو رقم الهاتف..."
        @input="debouncedSearch"
      />
    </div>

    <div v-if="loading && users.length === 0" class="loading-state">
      <div class="spinner spinner-lg"></div>
      <p>جاري تحميل المستخدمين...</p>
    </div>

    <div v-else class="users-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الهاتف</th>
            <th>المسوحات</th>
            <th>الحالة</th>
            <th>مسؤول</th>
            <th>تاريخ التسجيل</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td class="user-name">{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.phone || '---' }}</td>
            <td>{{ user.total_scans }}</td>
            <td>
              <span class="badge" :class="user.is_active ? 'badge-success' : 'badge-danger'">
                {{ user.is_active ? 'نشط' : 'موقوف' }}
              </span>
            </td>
            <td>
              <span v-if="user.is_admin" class="badge badge-primary">مدير</span>
              <span v-else class="badge badge-secondary">مستخدم</span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td class="actions-cell">
              <button class="btn btn-sm btn-info" @click="openEditModal(user)" title="تعديل">
                ✏️
              </button>
              <button
                class="btn btn-sm"
                :class="user.is_active ? 'btn-warning' : 'btn-success'"
                @click="toggleActive(user)"
                :title="user.is_active ? 'تعطيل' : 'تفعيل'"
              >
                {{ user.is_active ? '🔴' : '🟢' }}
              </button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="9" class="empty-row">لا يوجد مستخدمين</td>
          </tr>
        </tbody>
      </table>

      <div v-if="meta.last_page > 1" class="pagination">
        <button
          class="btn btn-sm btn-secondary"
          :disabled="meta.current_page <= 1"
          @click="changePage(meta.current_page - 1)"
        >
          السابق
        </button>
        <span class="page-info">صفحة {{ meta.current_page }} من {{ meta.last_page }}</span>
        <button
          class="btn btn-sm btn-secondary"
          :disabled="meta.current_page >= meta.last_page"
          @click="changePage(meta.current_page + 1)"
        >
          التالي
        </button>
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content animate-slideUp" style="max-width: 520px;">
        <div class="modal-header">
          <h3 class="modal-title">{{ editingUser ? '✏️ تعديل مستخدم' : '➕ إضافة مستخدم' }}</h3>
          <button class="btn btn-sm btn-secondary" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">الاسم</label>
            <input v-model="form.name" type="text" class="form-input" placeholder="الاسم الكامل" />
          </div>
          <div class="form-group">
            <label class="form-label">البريد الإلكتروني</label>
            <input v-model="form.email" type="email" class="form-input" placeholder="user@example.com" />
          </div>
          <div class="form-group">
            <label class="form-label">رقم الهاتف</label>
            <input v-model="form.phone" type="text" class="form-input" placeholder="+972..." />
          </div>
          <div class="form-group">
            <label class="form-label">{{ editingUser ? 'كلمة المرور (اترك فارغاً إذا لا تريد التغيير)' : 'كلمة المرور' }}</label>
            <input v-model="form.password" type="password" class="form-input" placeholder="********" />
          </div>
          <div class="form-row">
            <div class="form-group form-checkbox">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_active" />
                <span>حساب نشط</span>
              </label>
            </div>
            <div class="form-group form-checkbox">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_admin" />
                <span>صلاحية مسؤول</span>
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = false">إلغاء</button>
          <button class="btn btn-primary" @click="saveUser" :disabled="saving">
            <span v-if="saving" class="spinner"></span>
            <span v-else>{{ editingUser ? 'حفظ' : 'إضافة' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import apiClient from '@/api/client'
import Swal from 'sweetalert2'
import dayjs from 'dayjs'

const users = ref([])
const meta = reactive({ current_page: 1, last_page: 1, per_page: 20, total: 0 })
const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const editingUser = ref(null)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  is_active: true,
  is_admin: false
})

let debounceTimer = null
function debouncedSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    meta.current_page = 1
    fetchUsers()
  }, 400)
}

async function fetchUsers() {
  loading.value = true
  try {
    const params = { page: meta.current_page, per_page: meta.per_page }
    if (searchQuery.value) params.search = searchQuery.value
    const { data } = await apiClient.get('/users', { params })
    users.value = data.data || []
    meta.current_page = data.meta?.current_page || 1
    meta.last_page = data.meta?.last_page || 1
    meta.total = data.meta?.total || 0
  } catch (err) {
    console.error('Failed to fetch users:', err)
  } finally {
    loading.value = false
  }
}

function changePage(page) {
  meta.current_page = page
  fetchUsers()
}

function openAddModal() {
  editingUser.value = null
  form.name = ''
  form.email = ''
  form.phone = ''
  form.password = ''
  form.is_active = true
  form.is_admin = false
  showModal.value = true
}

function openEditModal(user) {
  editingUser.value = user
  form.name = user.name
  form.email = user.email
  form.phone = user.phone || ''
  form.password = ''
  form.is_active = user.is_active
  form.is_admin = user.is_admin
  showModal.value = true
}

async function saveUser() {
  if (!form.name || !form.email) {
    Swal.fire({ title: 'حقول مطلوبة', text: 'الاسم والبريد الإلكتروني مطلوبان', icon: 'warning' })
    return
  }

  if (!editingUser.value && !form.password) {
    Swal.fire({ title: 'حقل مطلوب', text: 'كلمة المرور مطلوبة للمستخدم الجديد', icon: 'warning' })
    return
  }

  saving.value = true
  try {
    const payload = {
      name: form.name,
      email: form.email,
      phone: form.phone || null,
      is_active: form.is_active,
      is_admin: form.is_admin
    }
    if (form.password) payload.password = form.password

    if (editingUser.value) {
      await apiClient.put(`/users/${editingUser.value.id}`, payload)
      Swal.fire({ title: 'تم التحديث', icon: 'success', timer: 1500, showConfirmButton: false })
    } else {
      await apiClient.post('/users', payload)
      Swal.fire({ title: 'تمت الإضافة', icon: 'success', timer: 1500, showConfirmButton: false })
    }
    showModal.value = false
    fetchUsers()
  } catch (err) {
    const msg = err.response?.data?.message || 'فشل حفظ المستخدم'
    Swal.fire({ title: 'خطأ', text: msg, icon: 'error' })
  } finally {
    saving.value = false
  }
}

async function toggleActive(user) {
  const action = user.is_active ? 'تعطيل' : 'تفعيل'
  const result = await Swal.fire({
    title: `${action} المستخدم؟`,
    text: `هل أنت متأكد من ${action} ${user.name}؟`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: action,
    cancelButtonText: 'إلغاء'
  })

  if (!result.isConfirmed) return

  try {
    await apiClient.post(`/users/${user.id}/toggle-active`)
    user.is_active = !user.is_active
    Swal.fire({ title: `تم ${action}`, icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل تغيير الحالة', icon: 'error' })
  }
}

function formatDate(date) {
  if (!date) return '---'
  return dayjs(date).format('YYYY/MM/DD')
}

onMounted(fetchUsers)
</script>

<style scoped>
.users-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
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
}

.page-desc {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
}

.search-bar {
  max-width: 400px;
}

.users-table-container {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: right;
  padding: 0.875rem 1rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  background: var(--bg-body);
  border-bottom: 1px solid var(--border-color);
}

.data-table td {
  padding: 0.75rem 1rem;
  font-size: 0.8125rem;
  border-bottom: 1px solid var(--border-color);
}

.data-table tr:hover td {
  background: var(--bg-body);
}

.user-name {
  font-weight: 600;
}

.actions-cell {
  display: flex;
  gap: 0.375rem;
}

.empty-row {
  text-align: center;
  padding: 2rem;
  color: var(--text-muted);
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem;
}

.page-info {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.form-row {
  display: flex;
  gap: 1.5rem;
  margin-top: 1rem;
}

.form-checkbox {
  display: flex;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}
</style>
