<template>
  <div class="pin-overlay" @click.self="$emit('close')">
    <div class="pin-modal animate-slideUp">
      <div class="pin-header">
        <h3 class="pin-title">🔑 رمز PIN للتحليل</h3>
        <button class="pin-close" @click="$emit('close')">✕</button>
      </div>

      <div class="pin-body">
        <div class="pin-display">
          <div class="pin-digits">
            <span v-for="(digit, i) in pinDigits" :key="i" class="pin-digit animate-fadeIn" :style="{ animationDelay: i * 0.1 + 's' }">
              {{ digit }}
            </span>
          </div>
        </div>

        <div class="pin-copy-section">
          <button class="btn btn-primary" @click="copyPin">
            <span>{{ copied ? '✓' : '📋' }}</span>
            {{ copied ? 'تم النسخ!' : 'نسخ الرمز' }}
          </button>
        </div>

        <div v-if="expiresAt" class="pin-expiry">
          <div class="expiry-timer">
            <span>⏱️</span>
            <span>ينتهي في:</span>
            <strong>{{ countdownDisplay }}</strong>
          </div>
        </div>

        <div class="pin-note">
          <p>مشاركة هذا الرمز مع العميل للاطلاع على نتيجة التحليل.</p>
          <p>الرمز صالح للاستخدام مرة واحدة فقط.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import dayjs from 'dayjs'

const props = defineProps({
  pin: { type: String, default: '0000' },
  expiresAt: { type: String, default: null }
})

defineEmits(['close'])

const copied = ref(false)
const remainingSeconds = ref(300)

const pinDigits = computed(() => {
  return String(props.pin).padStart(4, '0').split('')
})

const countdownDisplay = computed(() => {
  const mins = Math.floor(remainingSeconds.value / 60)
  const secs = remainingSeconds.value % 60
  return `${mins}:${String(secs).padStart(2, '0')}`
})

let timer = null

function calculateRemaining() {
  if (props.expiresAt) {
    const diff = dayjs(props.expiresAt).diff(dayjs(), 'second')
    remainingSeconds.value = Math.max(0, diff)
  }
}

function copyPin() {
  navigator.clipboard.writeText(props.pin).then(() => {
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  }).catch(() => {
    const input = document.createElement('input')
    input.value = props.pin
    document.body.appendChild(input)
    input.select()
    document.execCommand('copy')
    document.body.removeChild(input)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  })
}

onMounted(() => {
  calculateRemaining()
  timer = setInterval(() => {
    if (remainingSeconds.value > 0) {
      remainingSeconds.value--
    } else {
      clearInterval(timer)
    }
  }, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style lang="scss" scoped>
.pin-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.pin-modal {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  width: 90%;
  max-width: 440px;
  box-shadow: var(--shadow-xl);
  overflow: hidden;
}

.pin-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  background: var(--primary);
  color: #fff;
}

.pin-title {
  font-size: 1.125rem;
  font-weight: 700;
}

.pin-close {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  border-radius: 50%;
  font-size: 0.875rem;
  transition: background var(--transition-fast);
}

.pin-close:hover {
  background: rgba(255, 255, 255, 0.35);
}

.pin-body {
  padding: 2rem 1.5rem;
  text-align: center;
}

.pin-display {
  margin-bottom: 1.5rem;
}

.pin-digits {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
}

.pin-digit {
  width: 56px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-md);
  font-size: 2rem;
  font-weight: 800;
  color: var(--primary);
  font-family: 'Courier New', monospace;
  letter-spacing: 2px;
}

.pin-copy-section {
  margin-bottom: 1rem;
}

.pin-expiry {
  margin-bottom: 1rem;
}

.expiry-timer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.expiry-timer strong {
  color: var(--warning);
  font-size: 1rem;
  font-family: 'Courier New', monospace;
}

.pin-note {
  background: var(--warning-bg);
  border-radius: var(--radius-md);
  padding: 0.75rem 1rem;
  font-size: 0.8125rem;
  color: var(--text-secondary);
  line-height: 1.5;
}
</style>
