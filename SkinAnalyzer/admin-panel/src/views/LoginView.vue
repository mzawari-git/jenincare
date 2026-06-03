<template>
  <div class="login-page" dir="rtl">
    <div class="login-bg">
      <div class="bg-shape bg-shape-1"></div>
      <div class="bg-shape bg-shape-2"></div>
      <div class="bg-shape bg-shape-3"></div>
    </div>

    <div class="login-container">
      <div class="login-card animate-slideUp">
        <div class="login-header">
          <div class="login-logo">
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="20" cy="20" r="18" fill="var(--primary)" />
              <path d="M12 20c0-4 3-7 8-7s8 3 8 7-3 7-8 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" />
              <circle cx="16" cy="18" r="1.5" fill="#fff" />
              <circle cx="24" cy="18" r="1.5" fill="#fff" />
              <path d="M17 24s1.5 2 3 2 3-2 3-2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
            </svg>
          </div>
          <h1 class="login-title">SkinAnalyzer</h1>
          <p class="login-subtitle">لوحة تحكم المشرفين</p>
        </div>

        <form class="login-form" @submit.prevent="handleLogin">
          <div class="form-group">
            <label class="form-label" for="email">البريد الإلكتروني</label>
            <div class="input-wrapper">
              <span class="input-icon">📧</span>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="admin@skinanalyzer.com"
                required
                autocomplete="username"
                :disabled="loading"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">كلمة المرور</label>
            <div class="input-wrapper">
              <span class="input-icon">🔒</span>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="••••••••"
                required
                autocomplete="current-password"
                :disabled="loading"
              />
              <button
                type="button"
                class="password-toggle"
                @click="showPassword = !showPassword"
                :title="showPassword ? 'إخفاء' : 'إظهار'"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <div v-if="errorMsg" class="error-message animate-fadeIn">
            <span>⚠️</span> {{ errorMsg }}
          </div>

          <button type="submit" class="login-btn" :disabled="loading">
            <span v-if="loading" class="spinner"></span>
            <span v-else>تسجيل الدخول</span>
          </button>
        </form>

        <div class="login-footer">
          <p>© 2026 SkinAnalyzer. جميع الحقوق محفوظة.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const loading = ref(false)
const errorMsg = ref('')
const showPassword = ref(false)

async function handleLogin() {
  errorMsg.value = ''
  if (!form.email || !form.password) {
    errorMsg.value = 'الرجاء إدخال البريد الإلكتروني وكلمة المرور'
    return
  }

  loading.value = true
  const result = await authStore.login(form.email, form.password)
  loading.value = false

  if (result.success) {
    router.push('/')
  } else {
    errorMsg.value = result.message
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
  position: relative;
  overflow: hidden;
}

.login-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.bg-shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.08;
}

.bg-shape-1 {
  width: 500px;
  height: 500px;
  background: var(--primary);
  top: -200px;
  left: -150px;
  animation: floatSlow 15s ease-in-out infinite;
}

.bg-shape-2 {
  width: 350px;
  height: 350px;
  background: var(--accent);
  bottom: -100px;
  right: -100px;
  animation: floatSlow 20s ease-in-out infinite reverse;
}

.bg-shape-3 {
  width: 200px;
  height: 200px;
  background: var(--info);
  top: 50%;
  right: 5%;
  animation: floatSlow 18s ease-in-out infinite;
}

@keyframes floatSlow {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
  padding: 1.5rem;
}

.login-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  padding: 2.5rem 2rem;
  box-shadow: var(--shadow-xl);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
}

.login-logo svg {
  width: 100%;
  height: 100%;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.login-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  right: 0.875rem;
  font-size: 1.1rem;
  pointer-events: none;
  z-index: 1;
}

.form-input {
  width: 100%;
  padding: 0.75rem 2.75rem 0.75rem 0.875rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--bg-input);
  color: var(--text-primary);
  font-size: 0.9375rem;
  transition: all var(--transition-fast);
}

.form-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(26, 136, 112, 0.12);
}

.form-input:disabled {
  opacity: 0.6;
}

.password-toggle {
  position: absolute;
  left: 0.75rem;
  background: none;
  font-size: 1.1rem;
  padding: 0.25rem;
  opacity: 0.6;
  transition: opacity var(--transition-fast);
}

.password-toggle:hover {
  opacity: 1;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--danger-bg);
  color: var(--danger);
  border-radius: var(--radius-md);
  font-size: 0.8125rem;
  font-weight: 500;
}

.login-btn {
  width: 100%;
  padding: 0.875rem;
  background: var(--primary);
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all var(--transition-fast);
  margin-top: 0.5rem;
}

.login-btn:hover:not(:disabled) {
  background: var(--primary-light);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.login-footer {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

@media (max-width: 480px) {
  .login-card {
    padding: 2rem 1.5rem;
  }

  .login-logo {
    width: 52px;
    height: 52px;
  }

  .login-title {
    font-size: 1.25rem;
  }
}
</style>
