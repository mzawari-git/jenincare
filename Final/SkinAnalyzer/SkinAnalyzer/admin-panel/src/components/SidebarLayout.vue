<template>
  <aside class="sidebar" :class="{ collapsed }" dir="rtl">
    <div class="sidebar-header">
      <div class="sidebar-logo" v-show="!collapsed">
        <div class="logo-icon">
          <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="20" cy="20" r="18" fill="var(--primary)" />
            <path d="M12 20c0-4 3-7 8-7s8 3 8 7-3 7-8 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="16" cy="18" r="1.5" fill="#fff" />
            <circle cx="24" cy="18" r="1.5" fill="#fff" />
            <path d="M17 24s1.5 2 3 2 3-2 3-2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        </div>
      </div>
      <div class="sidebar-brand" v-show="!collapsed">
        <span class="brand-name">SkinAnalyzer</span>
        <span class="brand-sub">لوحة التحكم</span>
      </div>
      <button class="collapse-btn" @click="$emit('toggle')" :title="collapsed ? 'توسيع' : 'طي'">
        <span>{{ collapsed ? '▶' : '◀' }}</span>
      </button>
    </div>

    <nav class="sidebar-nav">
      <router-link
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="nav-item"
        :class="{ active: isActive(item.to) }"
        :title="collapsed ? item.label : ''"
      >
        <span class="nav-icon">{{ item.icon }}</span>
        <span class="nav-label" v-show="!collapsed">{{ item.label }}</span>
        <span v-if="item.badge && !collapsed" class="nav-badge">{{ item.badge }}</span>
      </router-link>
    </nav>

    <div class="sidebar-footer" v-show="!collapsed">
      <div class="footer-info">
        <span class="version">v1.0.0</span>
        <span class="copyright">© 2026 SkinAnalyzer</span>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useScansStore } from '@/stores/scans'

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle'])

const route = useRoute()
const scansStore = useScansStore()

const pendingCount = computed(() => scansStore.pendingCount)

const navItems = computed(() => [
  { to: '/dashboard', icon: '📊', label: 'لوحة التحكم', badge: null },
  { to: '/scans/monitor', icon: '🔍', label: 'مراقبة تحليل البشرة', badge: pendingCount.value > 0 ? pendingCount.value : null },
  { to: '/users', icon: '👤', label: 'المستخدمين', badge: null },
  { to: '/ai-providers', icon: '🤖', label: 'مزودي الذكاء الاصطناعي', badge: null },
  { to: '/prompts', icon: '📝', label: 'إدارة التعليمات', badge: null },
  { to: '/white-label', icon: '🎨', label: 'العلامة التجارية', badge: null },
  { to: '/scans/history', icon: '📋', label: 'سجل التحاليل', badge: null },
  { to: '/products', icon: '🛍️', label: 'المنتجات', badge: null },
  { to: '/spin-codes', icon: '🎡', label: 'أكواد الدولب', badge: null }
])

function isActive(to) {
  if (to === '/dashboard') return route.path === '/dashboard'
  return route.path.startsWith(to)
}
</script>

<style lang="scss" scoped>
.sidebar {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: var(--sidebar-width);
  background: var(--bg-sidebar);
  display: flex;
  flex-direction: column;
  z-index: 150;
  transition: width var(--transition-normal);
  overflow: hidden;
  border-left: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar.collapsed {
  width: var(--sidebar-collapsed);
}

.sidebar-header {
  display: flex;
  align-items: center;
  padding: 1rem;
  height: var(--header-height);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  gap: 0.75rem;
}

.sidebar-logo {
  flex-shrink: 0;
}

.logo-icon {
  width: 36px;
  height: 36px;
}

.logo-icon svg {
  width: 100%;
  height: 100%;
}

.sidebar-brand {
  flex: 1;
  min-width: 0;
}

.brand-name {
  display: block;
  font-size: 1rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.2;
}

.brand-sub {
  display: block;
  font-size: 0.6875rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 400;
}

.collapse-btn {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.6);
  border-radius: var(--radius-sm);
  font-size: 0.625rem;
  flex-shrink: 0;
  transition: all var(--transition-fast);
}

.collapse-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 0.875rem;
  border-radius: var(--radius-sm);
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.875rem;
  font-weight: 500;
  transition: all var(--transition-fast);
  white-space: nowrap;
  text-decoration: none;
  position: relative;
}

.nav-item:hover {
  background: var(--bg-sidebar-hover);
  color: #fff;
}

.nav-item.active {
  background: var(--bg-sidebar-active);
  color: #fff;
  font-weight: 600;
}

.nav-icon {
  font-size: 1.2rem;
  flex-shrink: 0;
  width: 24px;
  text-align: center;
}

.nav-label {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-badge {
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--danger);
  color: #fff;
  font-size: 0.6875rem;
  font-weight: 700;
  border-radius: var(--radius-full);
  padding: 0 5px;
  flex-shrink: 0;
}

.sidebar-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.footer-info {
  display: flex;
  justify-content: space-between;
  font-size: 0.6875rem;
  color: rgba(255, 255, 255, 0.3);
}

.version {
  font-weight: 600;
}

.collapsed .nav-item {
  justify-content: center;
  padding: 0.7rem;
}

.collapsed .nav-icon {
  width: auto;
}

@media (max-width: 768px) {
  .sidebar {
    width: var(--sidebar-width);
    transform: translateX(100%);
    transition: transform var(--transition-normal);
  }

  .sidebar:not(.collapsed) {
    transform: translateX(0);
  }
}
</style>
