<template>
  <div class="stat-card" :class="'stat-' + color">
    <div class="stat-icon-wrapper">
      <span class="stat-icon">{{ icon }}</span>
    </div>
    <div class="stat-info">
      <span class="stat-value">{{ value }}</span>
      <span class="stat-label">{{ label }}</span>
    </div>
    <div v-if="trend !== null" class="stat-trend" :class="trend > 0 ? 'trend-up' : 'trend-down'">
      <span>{{ trend > 0 ? '↑' : '↓' }}</span>
      <span>{{ Math.abs(trend) }}%</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: '📊' },
  value: { type: [String, Number], default: '0' },
  label: { type: String, default: '' },
  color: { type: String, default: 'primary' },
  trend: { type: Number, default: null }
})
</script>

<style lang="scss" scoped>
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  transition: all var(--transition-normal);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  border-radius: 0 4px 4px 0;
}

.stat-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.stat-primary::before { background: var(--primary); }
.stat-success::before { background: var(--success); }
.stat-warning::before { background: var(--warning); }
.stat-danger::before { background: var(--danger); }
.stat-info::before { background: var(--info); }

.stat-icon-wrapper {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-md);
  flex-shrink: 0;
}

.stat-primary .stat-icon-wrapper { background: var(--primary-bg); }
.stat-success .stat-icon-wrapper { background: var(--success-bg); }
.stat-warning .stat-icon-wrapper { background: var(--warning-bg); }
.stat-danger .stat-icon-wrapper { background: var(--danger-bg); }
.stat-info .stat-icon-wrapper { background: var(--info-bg); }

.stat-icon {
  font-size: 1.5rem;
}

.stat-info {
  flex: 1;
  min-width: 0;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1.2;
}

.stat-label {
  display: block;
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin-top: 0.125rem;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: var(--radius-full);
  flex-shrink: 0;
}

.trend-up {
  background: var(--success-bg);
  color: var(--success);
}

.trend-down {
  background: var(--danger-bg);
  color: var(--danger);
}
</style>
