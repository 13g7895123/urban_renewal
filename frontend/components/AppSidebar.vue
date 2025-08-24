<template>
  <div>
    <!-- Desktop Sidebar -->
    <aside
      class="fixed top-0 left-0 h-full bg-white dark:bg-gray-800 shadow-lg transition-sidebar z-40 hidden lg:block flex flex-col"
      :class="[
        sidebarCollapsed ? 'sidebar-collapsed' : 'sidebar-expanded',
        { 'sidebar-transitioning': sidebarTransitioning }
      ]"
    >
      <!-- Logo/Brand -->
      <div class="h-16 flex items-center justify-center border-b border-gray-200 dark:border-gray-700 px-4">
        <transition 
          mode="out-in"
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 transform scale-90"
          enter-to-class="opacity-100 transform scale-100"
          leave-active-class="transition-all duration-300"
          leave-from-class="opacity-100 transform scale-100"
          leave-to-class="opacity-0 transform scale-90"
        >
          <!-- Expanded state -->
          <div v-if="!sidebarCollapsed" key="expanded" class="flex flex-col items-center">
            <div v-if="showLogo && logoUrl" class="h-10 flex items-center">
              <img :src="logoUrl" :alt="websiteName" class="h-8 object-contain" />
            </div>
            <div v-else class="text-center">
              <div class="text-lg font-bold text-gray-800 dark:text-white truncate">
                {{ displayName }}
              </div>
              <div v-if="displaySecondaryName" class="text-xs text-gray-600 dark:text-gray-400 truncate">
                {{ displaySecondaryName }}
              </div>
            </div>
          </div>
          
          <!-- Collapsed state -->
          <div v-else key="collapsed" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
            <div v-if="showLogo && logoUrl">
              <img :src="logoUrl" :alt="websiteName" class="w-8 h-8 object-contain" />
            </div>
            <div v-else class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">
                {{ (displayName || 'A').charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>
        </transition>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-4 py-6 space-y-2">
        <SidebarMenuItem
          v-for="item in menuItems"
          :key="item.name"
          :item="item"
          :collapsed="sidebarCollapsed"
          class="transition-all duration-300"
        />
      </nav>

      <!-- Logout Button -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="handleLogout"
          :disabled="isLoading"
          class="w-full flex items-center px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
          :class="{ 'justify-center': sidebarCollapsed }"
        >
          <ArrowRightOnRectangleIcon class="w-5 h-5 flex-shrink-0" />
          <transition
            enter-active-class="transition-all duration-300 delay-150"
            enter-from-class="opacity-0 transform -translate-x-2"
            enter-to-class="opacity-100 transform translate-x-0"
            leave-active-class="transition-all duration-150"
            leave-from-class="opacity-100 transform translate-x-0"
            leave-to-class="opacity-0 transform -translate-x-2"
          >
            <span v-if="!sidebarCollapsed" class="ml-3 whitespace-nowrap">
              {{ isLoading ? '登出中...' : '登出' }}
            </span>
          </transition>
        </button>
      </div>

      <!-- Collapse Toggle -->
      <button
        @click="toggleSidebar"
        :disabled="sidebarTransitioning"
        class="absolute -right-3 top-6 w-6 h-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 hover:scale-110 transition-all duration-200 shadow-md"
        :class="{ 'cursor-not-allowed opacity-50': sidebarTransitioning }"
        :title="sidebarCollapsed ? '展開側邊欄' : '收起側邊欄'"
      >
        <ChevronLeftIcon 
          class="w-4 h-4 text-gray-500 transition-transform duration-300"
          :class="{ 'rotate-180': sidebarCollapsed }"
        />
      </button>
    </aside>

    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="sidebarMobileOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="closeMobileSidebar"
    />

    <!-- Mobile Sidebar -->
    <aside
      class="fixed top-0 left-0 h-full sidebar-expanded bg-white dark:bg-gray-800 shadow-xl transition-transform duration-300 z-50 lg:hidden flex flex-col sidebar-mobile"
      :class="[
        sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo/Brand -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-3">
          <div v-if="showLogo && logoUrl" class="h-10 flex items-center">
            <img :src="logoUrl" :alt="websiteName" class="h-8 object-contain" />
          </div>
          <div v-else>
            <div class="text-lg font-bold text-gray-800 dark:text-white">
              {{ displayName }}
            </div>
            <div v-if="displaySecondaryName" class="text-xs text-gray-600 dark:text-gray-400">
              {{ displaySecondaryName }}
            </div>
          </div>
        </div>
        <button
          @click="closeMobileSidebar"
          class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
        >
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-4 py-6 space-y-2">
        <SidebarMenuItem
          v-for="item in menuItems"
          :key="item.name"
          :item="item"
          :collapsed="false"
          @click="closeMobileSidebar"
        />
      </nav>

      <!-- Logout Button -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="handleLogout"
          :disabled="isLoading"
          class="w-full flex items-center px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <ArrowRightOnRectangleIcon class="w-5 h-5" />
          <span class="ml-3">{{ isLoading ? '登出中...' : '登出' }}</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { 
  ChevronLeftIcon,
  XMarkIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const sidebarStore = useSidebarStore()
const { sidebarCollapsed, sidebarMobileOpen, sidebarTransitioning } = storeToRefs(sidebarStore)
const { toggleSidebar, closeMobileSidebar } = sidebarStore

const settingsStore = useSettingsStore()
const { sidebarMenuItems } = storeToRefs(settingsStore)

const websiteSettingsStore = useWebsiteSettingsStore()
const { websiteName, showLogo, logoUrl, displayName, displaySecondaryName } = storeToRefs(websiteSettingsStore)

const authStore = useAuthStore()
const { isLoading } = storeToRefs(authStore)
const { logout } = authStore

const menuItems = computed(() => sidebarMenuItems.value)

// Time display functionality has been permanently removed

const handleLogout = async () => {
  try {
    await logout()
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>

<style scoped>
.w-70 {
  width: 280px;
}
</style>