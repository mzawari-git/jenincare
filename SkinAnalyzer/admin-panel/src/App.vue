<template>
  <div class="app-layout" :class="{ 'sidebar-collapsed': sidebarCollapsed }" dir="rtl">
    <SidebarLayout
      :collapsed="sidebarCollapsed"
      @toggle="sidebarCollapsed = !sidebarCollapsed"
    />

    <div class="main-wrapper">
      <header class="top-header">
        <div class="header-left">
          <button class="menu-toggle" @click="sidebarCollapsed = !sidebarCollapsed" title="القائمة">
            <span class="icon">☰</span>
          </button>
          <div class="header-breadcrumb">
            <span class="breadcrumb-item">{{ currentPageTitle }}</span>
          </div>
        </div>

        <div class="header-right">
          <div class="header-actions">
            <button
              class="header-btn tooltip"
              data-tooltip="تحديث"
              @click="refreshCurrentView"
            >
              <span>↻</span>
            </button>

            <button
              class="header-btn tooltip"
              data-tooltip="الوضع الليلي"
              @click="toggleTheme"
            >
              <span>{{ isDark ? '☀️' : '🌙' }}</span>
            </button>

            <div class="notification-bell" @click="showNotifications">
              <span class="bell-icon">🔔</span>
              <span v-if="pendingScanCount > 0" class="notification-badge">
                {{ pendingScanCount }}
              </span>
            </div>
          </div>

          <div class="user-menu" ref="userMenuRef">
            <button class="user-trigger" @click="showUserMenu = !showUserMenu">
              <div class="user-avatar">
                <span>{{ userInitial }}</span>
              </div>
              <span class="user-name">{{ authStore.userName }}</span>
              <span class="arrow" :class="{ open: showUserMenu }">▼</span>
            </button>

            <div v-if="showUserMenu" class="user-dropdown animate-fadeIn">
              <div class="dropdown-header">
                <div class="dropdown-avatar">
                  <span>{{ userInitial }}</span>
                </div>
                <div>
                  <div class="dropdown-name">{{ authStore.userName }}</div>
                  <div class="dropdown-role">مدير النظام</div>
                </div>
              </div>
              <div class="dropdown-divider"></div>
              <router-link to="/settings" class="dropdown-item" @click="showUserMenu = false">
                <span>⚙️</span> الإعدادات
              </router-link>
              <button class="dropdown-item logout" @click="handleLogout">
                <span>🚪</span> تسجيل الخروج
              </button>
            </div>
          </div>
        </div>
      </header>

      <main class="main-content">
        <router-view v-slot="{ Component, route }">
          <transition name="page" mode="out-in">
            <component :is="Component" :key="route.path" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useScansStore } from '@/stores/scans'
import { useDark, useToggle } from '@vueuse/core'
import SidebarLayout from '@/components/SidebarLayout.vue'

const route = useRoute()
const authStore = useAuthStore()
const scansStore = useScansStore()

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light'
})
const toggleDark = useToggle(isDark)

const sidebarCollapsed = ref(false)
const showUserMenu = ref(false)
const userMenuRef = ref(null)

const userInitial = computed(() => {
  return authStore.userName?.charAt(0)?.toUpperCase() || 'م'
})

const pendingScanCount = computed(() => scansStore.pendingCount)

const pageTitles = {
  Dashboard: 'لوحة التحكم',
  ScanMonitor: 'مراقبة تحليل البشرة',
  ScanDetail: 'تفاصيل التحليل',
  ScanHistory: 'سجل التحاليل',
  AIProviders: 'مزودي الذكاء الاصطناعي',
  PromptManager: 'إدارة التعليمات',
  WhiteLabel: 'العلامة التجارية',
  ProductManager: 'المنتجات',
  AdminSettings: 'الإعدادات'
}

const currentPageTitle = computed(() => {
  return pageTitles[route.name] || 'لوحة التحكم'
})

function toggleTheme() {
  toggleDark()
}

function handleLogout() {
  showUserMenu.value = false
  authStore.logout()
}

function showNotifications() {
  scansStore.fetchPendingScans()
}

function refreshCurrentView() {
  window.dispatchEvent(new CustomEvent('admin-refresh'))
}

function handleClickOutside(e) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  scansStore.fetchPendingScans()
  const interval = setInterval(() => {
    scansStore.fetchPendingScans()
  }, 30000)

  onUnmounted(() => {
    clearInterval(interval)
  })
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style lang="scss" scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-body);
  transition: all var(--transition-normal);
}

.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  margin-right: var(--sidebar-width);
  transition: margin-right var(--transition-normal);
}

.sidebar-collapsed .main-wrapper {
  margin-right: var(--sidebar-collapsed);
}

.top-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: var(--header-height);
  padding: 0 1.5rem;
  background: var(--bg-header);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 100;
  transition: background var(--transition-normal);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.menu-toggle {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
  font-size: 1.1rem;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.menu-toggle:hover {
  background: var(--primary-bg);
  color: var(--primary);
}

.header-breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.breadcrumb-item {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border-radius: var(--radius-sm);
  font-size: 1.1rem;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.header-btn:hover {
  background: var(--bg-body);
  color: var(--text-primary);
}

.notification-bell {
  position: relative;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: background var(--transition-fast);
}

.notification-bell:hover {
  background: var(--bg-body);
}

.bell-icon {
  font-size: 1.2rem;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--danger);
  color: #fff;
  font-size: 0.6875rem;
  font-weight: 700;
  border-radius: var(--radius-full);
  padding: 0 4px;
  animation: bounce 2s ease infinite;
}

.user-menu {
  position: relative;
}

.user-trigger {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.375rem 0.75rem;
  background: var(--bg-body);
  border-radius: var(--radius-full);
  transition: all var(--transition-fast);
}

.user-trigger:hover {
  background: var(--primary-bg);
}

.user-avatar {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary);
  color: #fff;
  font-weight: 700;
  font-size: 0.875rem;
  border-radius: 50%;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.arrow {
  font-size: 0.625rem;
  color: var(--text-muted);
  transition: transform var(--transition-fast);
}

.arrow.open {
  transform: rotate(180deg);
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 240px;
  background: var(--bg-card);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-xl);
  border: 1px solid var(--border-color);
  overflow: hidden;
  z-index: 200;
}

.dropdown-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
}

.dropdown-avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary);
  color: #fff;
  font-weight: 700;
  font-size: 1rem;
  border-radius: 50%;
}

.dropdown-name {
  font-weight: 700;
  font-size: 0.875rem;
  color: var(--text-primary);
}

.dropdown-role {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.dropdown-divider {
  height: 1px;
  background: var(--border-color);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  font-size: 0.875rem;
  color: var(--text-primary);
  transition: background var(--transition-fast);
  text-align: right;
}

.dropdown-item:hover {
  background: var(--primary-bg);
}

.dropdown-item.logout {
  color: var(--danger);
}

.dropdown-item.logout:hover {
  background: var(--danger-bg);
}

.main-content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}

.page-enter-active,
.page-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@media (max-width: 768px) {
  .main-wrapper {
    margin-right: 0;
  }

  .sidebar-collapsed .main-wrapper {
    margin-right: 0;
  }

  .top-header {
    padding: 0 1rem;
  }

  .main-content {
    padding: 1rem;
  }

  .user-name {
    display: none;
  }

  .header-breadcrumb {
    display: none;
  }
}
</style>
