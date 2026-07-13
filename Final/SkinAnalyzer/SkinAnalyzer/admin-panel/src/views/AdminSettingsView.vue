<template>
  <div class="settings-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">⚙️ الإعدادات</h2>
        <p class="page-desc">إعدادات حساب المشرف والنظام</p>
      </div>
    </div>

    <div class="tabs">
      <button class="tab-item" :class="{ active: activeTab === 'profile' }" @click="activeTab = 'profile'">
        👤 الملف الشخصي
      </button>
      <button class="tab-item" :class="{ active: activeTab === 'security' }" @click="activeTab = 'security'">
        🔒 الأمان
      </button>
      <button class="tab-item" :class="{ active: activeTab === 'notifications' }" @click="activeTab = 'notifications'">
        🔔 الإشعارات
      </button>
      <button class="tab-item" :class="{ active: activeTab === 'system' }" @click="activeTab = 'system'">
        🖥️ النظام
      </button>
      <button class="tab-item" :class="{ active: activeTab === 'skinanalyzer' }" @click="activeTab = 'skinanalyzer'">
        🔬 SkinAnalyzer
      </button>
    </div>

    <div class="tab-content">
      <div v-if="activeTab === 'profile'" class="card animate-fadeIn">
        <h3 class="card-title">المعلومات الشخصية</h3>

        <div class="profile-avatar-section">
          <div class="profile-avatar">
            <span>{{ userInitial }}</span>
          </div>
          <div>
            <h4>{{ authStore.userName }}</h4>
            <p class="text-muted">مدير النظام</p>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">الاسم</label>
            <input v-model="profile.name" type="text" class="form-input" placeholder="اسم المستخدم" />
          </div>
          <div class="form-group">
            <label class="form-label">البريد الإلكتروني</label>
            <input v-model="profile.email" type="email" class="form-input" placeholder="admin@example.com" />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">رقم الهاتف</label>
          <input v-model="profile.phone" type="text" class="form-input" placeholder="+966 5xxxxxxxx" />
        </div>

        <button class="btn btn-primary" @click="updateProfile" :disabled="saving">
          <span v-if="saving" class="spinner"></span>
          <span v-else>💾 حفظ التغييرات</span>
        </button>
      </div>

      <div v-if="activeTab === 'security'" class="card animate-fadeIn">
        <h3 class="card-title">تغيير كلمة المرور</h3>

        <div class="form-group">
          <label class="form-label">كلمة المرور الحالية</label>
          <input v-model="security.currentPassword" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label class="form-label">كلمة المرور الجديدة</label>
          <input v-model="security.newPassword" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label class="form-label">تأكيد كلمة المرور</label>
          <input v-model="security.confirmPassword" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <button class="btn btn-primary" @click="changePassword" :disabled="savingPass">
          <span v-if="savingPass" class="spinner"></span>
          <span v-else>🔒 تغيير كلمة المرور</span>
        </button>
      </div>

      <div v-if="activeTab === 'notifications'" class="card animate-fadeIn">
        <h3 class="card-title">إعدادات الإشعارات</h3>

        <div class="notification-option">
          <div class="option-info">
            <span class="option-label">إشعار التحاليل الجديدة</span>
            <span class="option-desc">تلقي إشعار عند وصول تحليل جديد</span>
          </div>
          <span
            class="status-toggle"
            :class="{ active: notifications.scanNew }"
            @click="notifications.scanNew = !notifications.scanNew"
          >
            <span class="toggle-knob"></span>
          </span>
        </div>

        <div class="notification-option">
          <div class="option-info">
            <span class="option-label">إشعار الصوت</span>
            <span class="option-desc">تشغيل صوت عند وصول تحليل جديد</span>
          </div>
          <span
            class="status-toggle"
            :class="{ active: notifications.audio }"
            @click="notifications.audio = !notifications.audio"
          >
            <span class="toggle-knob"></span>
          </span>
        </div>

        <div class="notification-option">
          <div class="option-info">
            <span class="option-label">تنبيه الحصص</span>
            <span class="option-desc">إشعار عند اقتراب الحصص من النفاد</span>
          </div>
          <span
            class="status-toggle"
            :class="{ active: notifications.quota }"
            @click="notifications.quota = !notifications.quota"
          >
            <span class="toggle-knob"></span>
          </span>
        </div>

        <button class="btn btn-primary" @click="saveNotifications" :disabled="savingNotif">
          <span v-if="savingNotif" class="spinner"></span>
          <span v-else>💾 حفظ</span>
        </button>
      </div>

      <div v-if="activeTab === 'system'" class="card animate-fadeIn">
        <h3 class="card-title">معلومات النظام</h3>

        <div class="system-info-grid">
          <div class="system-info-item">
            <span class="si-label">الإصدار</span>
            <span class="si-value">v1.0.0</span>
          </div>
          <div class="system-info-item">
            <span class="si-label">الخادم</span>
            <span class="si-value">https://jenincare.shop</span>
          </div>
          <div class="system-info-item">
            <span class="si-label">آخر تحديث</span>
            <span class="si-value">2026-06-01</span>
          </div>
          <div class="system-info-item">
            <span class="si-label">حالة الخادم</span>
            <span class="si-value">
              <span class="status-dot online"></span> متصل
            </span>
          </div>
        </div>

        <div class="system-actions">
          <button class="btn btn-secondary" @click="clearCache">
            🗑️ مسح ذاكرة التخزين المؤقت
          </button>
          <button class="btn btn-secondary" @click="exportLogs">
            📋 تصدير السجلات
          </button>
        </div>
      </div>

      <div v-if="activeTab === 'skinanalyzer'" class="card animate-fadeIn">
        <h3 class="card-title">إعدادات SkinAnalyzer</h3>

        <div class="notification-option">
          <div class="option-info">
            <span class="option-label">وضع الفحص المجاني</span>
            <span class="option-desc">عند التفعيل، يتم قبول التحاليل تلقائياً بدون الحاجة لرمز PIN أو موافقة المشرف</span>
          </div>
          <span
            class="status-toggle"
            :class="{ active: freeScanMode }"
            @click="toggleFreeScanMode"
          >
            <span class="toggle-knob"></span>
          </span>
        </div>

        <div class="sa-status" style="margin-top: 1rem; padding: 0.75rem; background: var(--bg-body); border-radius: var(--radius-md); text-align: center;">
          <span v-if="freeScanMode" style="color: var(--success); font-weight: 600;">✅ التحاليل تمر مباشرة — بدون موافقة أو رمز</span>
          <span v-else style="color: var(--text-muted);">🔒 التحاليل تحتاج موافقة المشرف أو رمز PIN</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'
import Swal from 'sweetalert2'

const authStore = useAuthStore()
const activeTab = ref('profile')
const saving = ref(false)
const savingPass = ref(false)
const savingNotif = ref(false)
const freeScanMode = ref(false)

const userInitial = computed(() => {
  return authStore.userName?.charAt(0)?.toUpperCase() || 'م'
})

const profile = reactive({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || ''
})

const security = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const notifications = reactive({
  scanNew: true,
  audio: true,
  quota: true
})

async function updateProfile() {
  saving.value = true
  try {
    const res = await authStore.updateProfile({ ...profile })
    if (res.success) {
      Swal.fire({ title: 'تم الحفظ', icon: 'success', timer: 1500, showConfirmButton: false })
    } else {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: 'فشل تحديث الملف الشخصي', icon: 'error' })
  } finally {
    saving.value = false
  }
}

function changePassword() {
  if (!security.currentPassword) {
    Swal.fire({ title: 'خطأ', text: 'الرجاء إدخال كلمة المرور الحالية', icon: 'warning' })
    return
  }

  if (security.newPassword.length < 8) {
    Swal.fire({ title: 'خطأ', text: 'كلمة المرور يجب أن تكون 8 أحرف على الأقل', icon: 'warning' })
    return
  }

  if (security.newPassword !== security.confirmPassword) {
    Swal.fire({ title: 'خطأ', text: 'كلمتا المرور غير متطابقتين', icon: 'warning' })
    return
  }

  savingPass.value = true
  authStore.updateProfile({
    current_password: security.currentPassword,
    new_password: security.newPassword
  }).then((res) => {
    if (res.success) {
      Swal.fire({ title: 'تم تغيير كلمة المرور', icon: 'success', timer: 1500, showConfirmButton: false })
      security.currentPassword = ''
      security.newPassword = ''
      security.confirmPassword = ''
    } else {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  }).finally(() => {
    savingPass.value = false
  })
}

function saveNotifications() {
  savingNotif.value = true
  setTimeout(() => {
    savingNotif.value = false
    Swal.fire({ title: 'تم الحفظ', icon: 'success', timer: 1500, showConfirmButton: false })
  }, 500)
}

function clearCache() {
  Swal.fire({
    title: 'مسح التخزين المؤقت؟',
    text: 'سيتم مسح البيانات المخزنة محلياً',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'مسح',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#ef4444'
  }).then((result) => {
    if (result.isConfirmed) {
      localStorage.clear()
      Swal.fire({ title: 'تم المسح', icon: 'success', timer: 1500, showConfirmButton: false })
    }
  })
}

function exportLogs() {
  Swal.fire({
    title: 'تصدير السجلات',
    text: 'سيتم تصدير سجلات النظام كملف CSV',
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'تصدير',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#1a8870'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({ title: 'تم التصدير', icon: 'success', timer: 1500, showConfirmButton: false })
    }
  })
}

async function fetchSkinAnalyzerSettings() {
  try {
    const { data } = await apiClient.get('/settings/skinanalyzer')
    freeScanMode.value = data.free_scan_mode || false
  } catch (err) {
    console.error('Failed to fetch SkinAnalyzer settings:', err)
  }
}

async function toggleFreeScanMode() {
  freeScanMode.value = !freeScanMode.value
  try {
    await apiClient.post('/settings/skinanalyzer', { free_scan_mode: freeScanMode.value })
    Swal.fire({
      title: 'تم الحفظ',
      text: freeScanMode.value ? 'تم تفعيل وضع الفحص المجاني' : 'تم تعطيل وضع الفحص المجاني',
      icon: 'success',
      timer: 1500,
      showConfirmButton: false
    })
  } catch (err) {
    freeScanMode.value = !freeScanMode.value
    Swal.fire({ title: 'خطأ', text: 'فشل حفظ الإعدادات', icon: 'error' })
  }
}

onMounted(() => {
  fetchSkinAnalyzerSettings()
})
</script>

<style lang="scss" scoped>
.settings-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
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

.tab-content {
  max-width: 700px;
}

.profile-avatar-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid var(--border-light);
}

.profile-avatar {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary);
  color: #fff;
  font-size: 1.5rem;
  font-weight: 800;
  border-radius: 50%;
}

.text-muted {
  color: var(--text-muted);
  font-size: 0.8125rem;
}

.notification-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 0;
  border-bottom: 1px solid var(--border-light);
}

.option-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.option-label {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-primary);
}

.option-desc {
  font-size: 0.75rem;
  color: var(--text-muted);
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

.system-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.system-info-item {
  padding: 0.75rem 1rem;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
}

.si-label {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-bottom: 0.25rem;
}

.si-value {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--text-primary);
}

.system-actions {
  display: flex;
  gap: 0.75rem;
}

@media (max-width: 768px) {
  .tab-content {
    max-width: 100%;
  }

  .system-info-grid {
    grid-template-columns: 1fr;
  }

  .system-actions {
    flex-direction: column;
  }
}
</style>
