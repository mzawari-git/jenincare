<template>
  <div class="white-label-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">🎨 العلامة التجارية</h2>
        <p class="page-desc">تخصيص مظهر التطبيق حسب علامتك التجارية</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-secondary" @click="resetConfig">
          ↺ استعادة الافتراضي
        </button>
        <button class="btn btn-primary" @click="saveConfig" :disabled="saving">
          <span v-if="saving" class="spinner"></span>
          <span v-else>💾 حفظ ونشر</span>
        </button>
      </div>
    </div>

    <div class="wl-layout">
      <div class="wl-form card">
        <h3 class="card-title">إعدادات العلامة التجارية</h3>

        <div class="form-group">
          <label class="form-label">اسم التطبيق (بالعربية)</label>
          <input
            v-model="localConfig.app_name_ar"
            type="text"
            class="form-input"
            placeholder="محلل البشرة"
          />
        </div>

        <div class="form-group">
          <label class="form-label">اسم التطبيق (بالإنجليزية)</label>
          <input
            v-model="localConfig.app_name_en"
            type="text"
            class="form-input"
            placeholder="SkinAnalyzer"
          />
        </div>

        <div class="form-group">
          <label class="form-label">اللون الأساسي</label>
          <div class="color-picker-wrapper">
            <input v-model="localConfig.primary_color" type="color" />
            <input v-model="localConfig.primary_color" type="text" class="form-input" placeholder="#1a8870" />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">اللون الثانوي</label>
          <div class="color-picker-wrapper">
            <input v-model="localConfig.accent_color" type="color" />
            <input v-model="localConfig.accent_color" type="text" class="form-input" placeholder="#f0a04b" />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">لون الخلفية</label>
          <div class="color-picker-wrapper">
            <input v-model="localConfig.background_color" type="color" />
            <input v-model="localConfig.background_color" type="text" class="form-input" placeholder="#f8fafc" />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">رابط الخادم (Server URL)</label>
          <input
            v-model="localConfig.server_url"
            type="text"
            class="form-input"
            placeholder="https://jenincare.shop"
          />
        </div>

        <div class="form-group">
          <label class="form-label">رقم التواصل</label>
          <input
            v-model="localConfig.contact_phone"
            type="text"
            class="form-input"
            placeholder="+966 5xxxxxxxx"
          />
        </div>

        <div class="form-group">
          <label class="form-label">البريد الإلكتروني</label>
          <input
            v-model="localConfig.contact_email"
            type="email"
            class="form-input"
            placeholder="info@example.com"
          />
        </div>

        <h4 style="margin: 1.5rem 0 0.75rem; color: var(--text-primary);">🏥 معلومات العيادة للتقرير</h4>

        <div class="form-group">
          <label class="form-label">اسم العيادة (بالعربية)</label>
          <input v-model="localConfig.clinic_name_ar" type="text" class="form-input" placeholder="مركز جنين للعناية بالبشرة" />
        </div>
        <div class="form-group">
          <label class="form-label">اسم العيادة (بالإنجليزية)</label>
          <input v-model="localConfig.clinic_name_en" type="text" class="form-input" placeholder="Jenin Skin Care Center" />
        </div>
        <div class="form-group">
          <label class="form-label">العنوان (بالعربية)</label>
          <input v-model="localConfig.clinic_address_ar" type="text" class="form-input" placeholder="شارع الرئيسي، مدينة..." />
        </div>
        <div class="form-group">
          <label class="form-label">العنوان (بالإنجليزية)</label>
          <input v-model="localConfig.clinic_address_en" type="text" class="form-input" placeholder="Main Street, City..." />
        </div>
        <div class="form-group">
          <label class="form-label">هاتف العيادة</label>
          <input v-model="localConfig.clinic_phone" type="text" class="form-input" placeholder="+966 5xxxxxxxx" />
        </div>
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني للعيادة</label>
          <input v-model="localConfig.clinic_email" type="email" class="form-input" placeholder="clinic@example.com" />
        </div>

        <div class="form-group">
          <label class="form-label">نص التذييل (بالعربية)</label>
          <input
            v-model="localConfig.footer_text_ar"
            type="text"
            class="form-input"
            placeholder="© 2026 جميع الحقوق محفوظة"
          />
        </div>

        <div class="form-group">
          <label class="form-label">نص التذييل (بالإنجليزية)</label>
          <input
            v-model="localConfig.footer_text_en"
            type="text"
            class="form-input"
            placeholder="© 2026 All Rights Reserved"
          />
        </div>

        <div class="form-group">
          <label class="form-label">الشعار</label>
          <div class="logo-upload">
            <div class="logo-preview">
              <img v-if="localConfig.logo_url" :src="localConfig.logo_url" alt="Logo" />
              <div v-else class="logo-placeholder">🖼️</div>
            </div>
            <label class="upload-btn btn btn-secondary">
              📁 رفع شعار
              <input type="file" accept="image/*" hidden @change="handleLogoUpload" />
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <input v-model="localConfig.powered_by" type="checkbox" style="width: auto; margin-left: 0.5rem;" />
            إظهار "Powered by SkinAnalyzer"
          </label>
        </div>
      </div>

      <div class="wl-preview card">
        <h3 class="card-title">👁️ معاينة التطبيق</h3>
        <div class="phone-preview">
          <div class="phone-frame">
            <div class="phone-notch"></div>
            <div class="phone-screen" :style="previewStyles">
              <div class="phone-header" :style="{ background: localConfig.primary_color }">
                <span class="phone-app-name">{{ localConfig.app_name_ar }}</span>
              </div>
              <div class="phone-body">
                <div class="phone-scan-badge" :style="{ background: localConfig.primary_color }">
                  <span>🔍</span>
                  <span>تحليل البشرة</span>
                </div>
                <div class="phone-card">
                  <div class="phone-card-title" :style="{ color: localConfig.primary_color }">نتيجة التحليل</div>
                  <div class="phone-score" :style="{ color: localConfig.primary_color }">85%</div>
                  <div class="phone-details">
                    <span class="phone-badge" :style="{ background: localConfig.accent_color }">دهنية</span>
                    <span class="phone-badge" :style="{ background: localConfig.primary_color }">صحية</span>
                  </div>
                </div>
                <button class="phone-btn" :style="{ background: localConfig.primary_color }">
                  عرض التفاصيل
                </button>
              </div>
              <div class="phone-footer" :style="{ background: localConfig.primary_color }">
                <span>{{ localConfig.footer_text_ar || '© 2026 جميع الحقوق محفوظة' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useWhiteLabelStore } from '@/stores/whiteLabel'
import Swal from 'sweetalert2'

const store = useWhiteLabelStore()
const saving = ref(false)

const localConfig = reactive({ ...store.config })

const previewStyles = computed(() => ({
  backgroundColor: localConfig.background_color
}))

function handleLogoUpload(e) {
  const file = e.target.files?.[0]
  if (!file) return

  if (file.size > 5 * 1024 * 1024) {
    Swal.fire({ title: 'الملف كبير جداً', text: 'الحد الأقصى 5MB', icon: 'warning' })
    return
  }

  const reader = new FileReader()
  reader.onload = (ev) => {
    localConfig.logo_url = ev.target.result
  }
  reader.readAsDataURL(file)

  store.uploadLogo(file).then((res) => {
    if (!res.success) {
      Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
    }
  })
}

async function saveConfig() {
  saving.value = true
  const res = await store.updateConfig({ ...localConfig })
  saving.value = false

  if (res.success) {
    Swal.fire({
      title: 'تم الحفظ والنشر',
      text: 'تم تحديث إعدادات العلامة التجارية',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    })
  } else {
    Swal.fire({ title: 'خطأ', text: res.message, icon: 'error' })
  }
}

function resetConfig() {
  Swal.fire({
    title: 'استعادة الإعدادات الافتراضية؟',
    text: 'سيتم فقدان جميع التغييرات',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'استعادة',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#ef4444'
  }).then((result) => {
    if (result.isConfirmed) {
      Object.assign(localConfig, {
        app_name_ar: 'محلل البشرة',
        app_name_en: 'SkinAnalyzer',
        primary_color: '#1a8870',
        accent_color: '#f0a04b',
        background_color: '#f8fafc',
        logo_url: null,
        server_url: 'https://jenincare.shop',
        powered_by: true,
        contact_phone: '',
        contact_email: '',
        footer_text_ar: '',
        footer_text_en: '',
        clinic_name_ar: '',
        clinic_name_en: '',
        clinic_address_ar: '',
        clinic_address_en: '',
        clinic_phone: '',
        clinic_email: ''
      })
    }
  })
}

function handleRefresh() {
  store.fetchConfig().then(() => {
    Object.assign(localConfig, store.config)
  })
}

onMounted(() => {
  store.fetchConfig().then(() => {
    Object.assign(localConfig, store.config)
  })
  window.addEventListener('admin-refresh', handleRefresh)
})
</script>

<style lang="scss" scoped>
.white-label-page {
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

.wl-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 1.5rem;
}

.wl-form {
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}

.logo-upload {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-preview {
  width: 80px;
  height: 80px;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 2px dashed var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
}

.logo-preview img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.logo-placeholder {
  font-size: 2rem;
  color: var(--text-muted);
}

.upload-btn {
  position: relative;
  cursor: pointer;
}

.phone-preview {
  display: flex;
  justify-content: center;
}

.phone-frame {
  width: 280px;
  height: 580px;
  background: #1e293b;
  border-radius: 32px;
  padding: 12px;
  box-shadow: var(--shadow-xl);
  position: relative;
}

.phone-notch {
  width: 80px;
  height: 6px;
  background: #334155;
  border-radius: 3px;
  margin: 0 auto 12px;
}

.phone-screen {
  height: calc(100% - 18px);
  border-radius: 24px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: background var(--transition-normal);
}

.phone-header {
  padding: 1rem;
  text-align: center;
  color: #fff;
  transition: background var(--transition-normal);
}

.phone-app-name {
  font-size: 0.8125rem;
  font-weight: 700;
}

.phone-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  gap: 0.75rem;
  overflow-y: auto;
}

.phone-scan-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #fff;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  transition: background var(--transition-normal);
}

.phone-card {
  background: #fff;
  border-radius: var(--radius-md);
  padding: 1rem;
  width: 100%;
  box-shadow: var(--shadow-sm);
}

.phone-card-title {
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  transition: color var(--transition-normal);
}

.phone-score {
  font-size: 1.75rem;
  font-weight: 800;
  text-align: center;
  margin-bottom: 0.5rem;
  transition: color var(--transition-normal);
}

.phone-details {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.phone-badge {
  padding: 0.25rem 0.625rem;
  border-radius: var(--radius-full);
  color: #fff;
  font-size: 0.625rem;
  font-weight: 600;
  transition: background var(--transition-normal);
}

.phone-btn {
  width: 100%;
  padding: 0.625rem;
  color: #fff;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 600;
  transition: background var(--transition-normal);
}

.phone-footer {
  padding: 0.5rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.5625rem;
  transition: background var(--transition-normal);
}

@media (max-width: 992px) {
  .wl-layout {
    grid-template-columns: 1fr;
  }

  .phone-preview {
    order: -1;
  }
}
</style>
