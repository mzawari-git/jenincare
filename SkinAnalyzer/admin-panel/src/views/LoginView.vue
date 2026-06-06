<template>
  <div class="login-page" dir="rtl">
    <div class="login-bg">
      <div class="bg-gradient"></div>
      <div class="bg-pattern"></div>
      <div class="floating-orb orb-1"></div>
      <div class="floating-orb orb-2"></div>
      <div class="floating-orb orb-3"></div>
    </div>

    <div class="login-container">
      <div class="login-card">
        <div class="card-glow"></div>
        
        <div class="login-header">
          <div class="logo-wrapper">
            <div class="logo-ring"></div>
            <svg class="logo-svg" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="30" cy="30" r="24" fill="url(#logoGrad)" />
              <path d="M18 30c0-6.627 5.373-12 12-12s12 5.373 12 12-5.373 12-12 12" stroke="rgba(255,255,255,0.9)" stroke-width="2.5" stroke-linecap="round" />
              <circle cx="24" cy="27" r="2" fill="rgba(255,255,255,0.9)" />
              <circle cx="36" cy="27" r="2" fill="rgba(255,255,255,0.9)" />
              <path d="M25 35s2 3 5 3 5-3 5-3" stroke="rgba(255,255,255,0.9)" stroke-width="2" stroke-linecap="round" />
              <defs>
                <linearGradient id="logoGrad" x1="6" y1="6" x2="54" y2="54">
                  <stop offset="0%" stop-color="#1a8870" />
                  <stop offset="100%" stop-color="#20a384" />
                </linearGradient>
              </defs>
            </svg>
          </div>
          <div class="brand-text">
            <h1 class="login-title">SkinAnalyzer</h1>
            <p class="login-subtitle">لوحة تحكم تحليل البشرة بالذكاء الاصطناعي</p>
          </div>
        </div>

        <form class="login-form" @submit.prevent="handleLogin">
          <div class="form-group">
            <label class="form-label" for="email">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="label-icon">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="M22 7l-10 6L2 7"/>
              </svg>
              البريد الإلكتروني
            </label>
            <div class="input-wrapper">
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="admin@jenincare.shop"
                required
                autocomplete="username"
                :disabled="loading"
              />
              <div class="input-focus-ring"></div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="label-icon">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
              كلمة المرور
            </label>
            <div class="input-wrapper">
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
                <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
              <div class="input-focus-ring"></div>
            </div>
          </div>

          <div v-if="errorMsg" class="error-message">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="error-icon">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ errorMsg }}</span>
          </div>

          <button type="submit" class="login-btn" :disabled="loading">
            <span v-if="loading" class="btn-loader">
              <span class="spinner-ring"></span>
              <span>جاري تسجيل الدخول...</span>
            </span>
            <span v-else class="btn-content">
              <span>تسجيل الدخول</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-arrow">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
              </svg>
            </span>
          </button>
        </form>

        <div class="login-footer">
          <div class="footer-divider">
            <span class="divider-line"></span>
            <span class="divider-text">SkinAnalyzer Pro</span>
            <span class="divider-line"></span>
          </div>
          <p class="copyright">© 2026 Jenin Care. جميع الحقوق محفوظة.</p>
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
  background: #0a1628;
  position: relative;
  overflow: hidden;
}

.login-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.bg-gradient {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(ellipse at 20% 50%, rgba(26, 136, 112, 0.15) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 20%, rgba(32, 163, 132, 0.1) 0%, transparent 50%),
    radial-gradient(ellipse at 50% 80%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
}

.bg-pattern {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 60px 60px;
}

.floating-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  animation: orbFloat 20s ease-in-out infinite;
}

.orb-1 {
  width: 400px;
  height: 400px;
  background: rgba(26, 136, 112, 0.2);
  top: -100px;
  right: -100px;
  animation-delay: 0s;
}

.orb-2 {
  width: 300px;
  height: 300px;
  background: rgba(59, 130, 246, 0.15);
  bottom: -50px;
  left: -50px;
  animation-delay: -7s;
}

.orb-3 {
  width: 200px;
  height: 200px;
  background: rgba(240, 160, 75, 0.1);
  top: 50%;
  left: 50%;
  animation-delay: -14s;
}

@keyframes orbFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  25% { transform: translate(30px, -40px) scale(1.1); }
  50% { transform: translate(-20px, 20px) scale(0.95); }
  75% { transform: translate(40px, 30px) scale(1.05); }
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 440px;
  padding: 1.5rem;
}

.login-card {
  position: relative;
  background: rgba(30, 41, 59, 0.8);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem 2rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  overflow: hidden;
}

.card-glow {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 200px;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(26, 136, 112, 0.5), transparent);
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-wrapper {
  position: relative;
  width: 72px;
  height: 72px;
  margin: 0 auto 1.25rem;
}

.logo-ring {
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  border: 2px solid transparent;
  border-top-color: var(--primary);
  border-right-color: var(--primary-light);
  animation: spin 3s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.logo-svg {
  width: 100%;
  height: 100%;
  filter: drop-shadow(0 4px 12px rgba(26, 136, 112, 0.4));
}

.brand-text {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.login-title {
  font-size: 1.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.7) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.5px;
}

.login-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 400;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.7);
}

.label-icon {
  width: 14px;
  height: 14px;
  color: var(--primary);
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
  font-size: 0.9375rem;
  transition: all 0.3s ease;
  outline: none;
}

.form-input::placeholder {
  color: rgba(255, 255, 255, 0.3);
}

.form-input:focus {
  border-color: var(--primary);
  background: rgba(26, 136, 112, 0.08);
  box-shadow: 0 0 0 4px rgba(26, 136, 112, 0.15);
}

.form-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.input-focus-ring {
  position: absolute;
  inset: -1px;
  border-radius: 13px;
  opacity: 0;
  transition: opacity 0.3s ease;
  background: linear-gradient(135deg, var(--primary), var(--primary-light));
  z-index: -1;
}

.form-input:focus ~ .input-focus-ring {
  opacity: 1;
}

.password-toggle {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0.25rem;
  color: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: color 0.2s ease;
}

.password-toggle:hover {
  color: rgba(255, 255, 255, 0.7);
}

.password-toggle svg {
  width: 18px;
  height: 18px;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.875rem 1rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 12px;
  color: #fca5a5;
  font-size: 0.8125rem;
  font-weight: 500;
  animation: shake 0.4s ease;
}

.error-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  color: #ef4444;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-8px); }
  75% { transform: translateX(8px); }
}

.login-btn {
  position: relative;
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.5rem;
  overflow: hidden;
}

.login-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.login-btn:hover:not(:disabled)::before {
  opacity: 1;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px -5px rgba(26, 136, 112, 0.5);
}

.login-btn:active:not(:disabled) {
  transform: translateY(0);
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-content,
.btn-loader {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
}

.btn-arrow {
  width: 18px;
  height: 18px;
  transition: transform 0.3s ease;
}

.login-btn:hover:not(:disabled) .btn-arrow {
  transform: translateX(-4px);
}

.spinner-ring {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.login-footer {
  margin-top: 2rem;
  text-align: center;
}

.footer-divider {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.divider-line {
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
}

.divider-text {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.3);
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.copyright {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.3);
}

@media (max-width: 480px) {
  .login-card {
    padding: 2rem 1.5rem;
    border-radius: 20px;
  }

  .logo-wrapper {
    width: 60px;
    height: 60px;
  }

  .login-title {
    font-size: 1.5rem;
  }
}
</style>
