<template>
  <div class="scan-row animate-fadeIn" :class="'scan-' + scan.status">
    <div class="scan-thumbnail" @click="$emit('view', scan)">
      <img v-if="scan.thumbnail_url || scan.image_url" :src="scan.thumbnail_url || scan.image_url" :alt="scan.user_name" @error="imgError = true" />
      <div v-else class="thumb-placeholder">📸</div>
    </div>

    <div class="scan-info" @click="$emit('view', scan)">
      <div class="scan-user">
        <span class="scan-user-name">{{ scan.user_name || 'مستخدم' }}</span>
        <span v-if="scan.user_phone" class="scan-user-phone">{{ scan.user_phone }}</span>
      </div>
      <div class="scan-meta">
        <span class="scan-date">{{ formatDate(scan.created_at) }}</span>
        <span v-if="scan.provider_name" class="badge badge-info">{{ scan.provider_name }}</span>
        <span v-if="scan.score" class="scan-score">
          النتيجة: <strong>{{ scan.score }}%</strong>
        </span>
      </div>
    </div>

    <div class="scan-status">
      <span class="badge" :class="statusClass">{{ statusLabel }}</span>
    </div>

    <div class="scan-actions">
      <button
        class="btn btn-sm btn-info"
        @click.stop="$emit('view', scan)"
        title="عرض التفاصيل"
      >
        👁 عرض
      </button>
      <button
        v-if="scan.status === 'pending'"
        class="btn btn-sm btn-success"
        @click.stop="$emit('approve', scan)"
        title="اعتماد وإرسال"
      >
        ✓ اعتماد
      </button>
      <button
        v-if="scan.status === 'pending'"
        class="btn btn-sm btn-warning"
        @click.stop="$emit('generate-pin', scan)"
        title="إنشاء رمز PIN"
      >
        🔑 PIN
      </button>
      <button
        v-if="scan.status === 'pending'"
        class="btn btn-sm btn-danger"
        @click.stop="$emit('reject', scan)"
        title="رفض"
      >
        ✕
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ar'

dayjs.locale('ar')

const props = defineProps({
  scan: { type: Object, required: true }
})

defineEmits(['view', 'approve', 'reject', 'generate-pin'])

const imgError = ref(false)

const statusMap = {
  pending: { label: 'معلق', class: 'badge-warning' },
  processing: { label: 'جاري المعالجة', class: 'badge-info' },
  approved: { label: 'معتمد', class: 'badge-success' },
  rejected: { label: 'مرفوض', class: 'badge-danger' },
  completed: { label: 'مكتمل', class: 'badge-primary' }
}

const statusLabel = computed(() => statusMap[props.scan.status]?.label || props.scan.status)
const statusClass = computed(() => statusMap[props.scan.status]?.class || 'badge-muted')

function formatDate(date) {
  if (!date) return ''
  return dayjs(date).format('YYYY/MM/DD hh:mm A')
}
</script>

<style lang="scss" scoped>
.scan-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
  transition: all var(--transition-fast);
  border-right: 4px solid transparent;
}

.scan-row:hover {
  box-shadow: var(--shadow-md);
  transform: translateX(-2px);
}

.scan-pending { border-right-color: var(--warning); }
.scan-processing { border-right-color: var(--info); }
.scan-approved { border-right-color: var(--success); }
.scan-rejected { border-right-color: var(--danger); }

.scan-thumbnail {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-md);
  overflow: hidden;
  flex-shrink: 0;
  cursor: pointer;
  border: 2px solid var(--border-light);
  background: var(--bg-body);
}

.scan-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--text-muted);
}

.scan-info {
  flex: 1;
  min-width: 0;
  cursor: pointer;
}

.scan-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.scan-user-name {
  font-weight: 700;
  font-size: 0.9375rem;
  color: var(--text-primary);
}

.scan-user-phone {
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.scan-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.scan-date {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.scan-score {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.scan-score strong {
  color: var(--primary);
}

.scan-status {
  flex-shrink: 0;
}

.scan-actions {
  display: flex;
  gap: 0.375rem;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .scan-row {
    flex-wrap: wrap;
  }

  .scan-info {
    flex: 1 1 calc(100% - 80px);
  }

  .scan-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>
