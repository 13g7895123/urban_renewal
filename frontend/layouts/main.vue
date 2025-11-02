<template>
  <div class="h-screen flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="navbar-gradient h-16 flex-shrink-0 flex items-center px-6 shadow-sm">
      <div class="flex-1"></div>

      <h1 class="text-white text-xl font-bold">
        <slot name="title">更新會管理</slot>
      </h1>

      <div class="flex-1 flex justify-end items-center space-x-4">
        <span class="text-white text-sm">{{ userName }}</span>
        
        <UButton
          variant="ghost"
          color="white"
          size="sm"
          class="text-white hover:bg-green-600"
          @click="handleLogout"
        >
          <Icon name="heroicons:arrow-right-on-rectangle" class="w-5 h-5 mr-2" />
          登出
        </UButton>
      </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <aside class="w-64 bg-gray-800 text-white flex-shrink-0 overflow-y-auto">
        <div class="p-4">
          <div class="flex items-center space-x-3 mb-8">
            <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center">
              <Icon name="heroicons:user" class="w-8 h-8 text-white" />
            </div>
            <div>
              <div class="text-white font-medium">{{ userName }}</div>
              <div class="text-gray-400 text-sm">{{ roleLabel }}</div>
            </div>
          </div>
          
          <nav class="space-y-2">
            <NuxtLink 
              v-for="item in visibleMenuItems" 
              :key="item.path"
              :to="item.path" 
              class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" 
              :class="{ 'bg-blue-600': $route.path === item.path }"
            >
              <Icon :name="item.icon" class="w-5 h-5" />
              <span>{{ item.label }}</span>
            </NuxtLink>
          </nav>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 bg-gray-50 flex flex-col overflow-hidden">
        <div class="flex-1 p-6 overflow-y-auto">
          <div class="bg-white rounded-lg shadow-lg min-h-full p-6">
            <slot />
          </div>
        </div>
        <Footer copyright-text="© 2020, made with ❤️ by Thread Tech" />
      </main>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const router = useRouter()

// Watch for user state changes - redirect if user becomes null
watchEffect(() => {
  if (process.client && !authStore.user && !authStore.isLoading) {
    console.warn('[Main Layout] User state is null, redirecting to login')
    navigateTo('/login')
  }
})

// User display name
const userName = computed(() => {
  return authStore.user?.full_name || authStore.user?.username || ''
})

// Role label
const roleLabel = computed(() => {
  const roleMap = {
    'admin': '系統管理員',
    'chairman': '主任委員',
    'member': '地主成員',
    'observer': '觀察員'
  }
  return roleMap[authStore.user?.role] || authStore.user?.role || ''
})

// Menu items configuration with role restrictions
const menuItems = [
  {
    path: '/',
    icon: 'heroicons:home',
    label: '首頁',
    roles: ['admin', 'chairman', 'member', 'observer']
  },
  {
    path: '/tables/urban-renewal',
    icon: 'heroicons:building-office-2',
    label: '更新會管理',
    roles: ['admin'],
    customCheck: (user) => {
      // Admin 或企業管理者都可以訪問
      const isManager = user?.is_company_manager === 1 ||
                        user?.is_company_manager === '1' ||
                        user?.is_company_manager === true
      return user?.role === 'admin' || isManager
    }
  },
  {
    path: '/tables/meeting',
    icon: 'heroicons:document-text',
    label: '會議管理',
    roles: ['admin', 'chairman', 'member']
  },
  {
    path: '/tables/issue',
    icon: 'heroicons:check-badge',
    label: '投票管理',
    roles: ['admin', 'chairman', 'member']
  },
  {
    path: '/pages/shopping',
    icon: 'heroicons:shopping-bag',
    label: '商城',
    roles: ['admin', 'chairman', 'member']
  },
  {
    path: '/tables/order',
    icon: 'heroicons:clipboard-document-list',
    label: '購買紀錄',
    roles: ['admin', 'chairman', 'member']
  },
  {
    path: '/pages/user',
    icon: 'heroicons:user',
    label: '使用者基本資料變更',
    roles: ['admin', 'chairman', 'member', 'observer']
  },
  {
    path: '/tables/company-profile',
    icon: 'heroicons:building-office',
    label: '企業管理',
    roles: ['admin'],
    customCheck: (user) => {
      // 後端返回的可能是字串 "1" 或數字 1
      const isManager = user?.is_company_manager === 1 ||
                        user?.is_company_manager === '1' ||
                        user?.is_company_manager === true
      return user?.role === 'admin' || isManager
    }
  }
]

// Filter menu items based on user role and custom checks
const visibleMenuItems = computed(() => {
  const currentUser = authStore.user
  if (!currentUser) return []

  return menuItems.filter(item => {
    // 如果有自訂檢查函數，優先使用
    if (item.customCheck) {
      return item.customCheck(currentUser)
    }
    // 否則使用角色檢查
    return item.roles.includes(currentUser.role)
  })
})

// Logout handler
const handleLogout = async () => {
  try {
    await authStore.logout()
  } catch (error) {
    console.error('Logout error:', error)
    // Force redirect to login even if API call fails
    await navigateTo('/login')
  }
}
</script>

<style scoped>
.navbar-gradient {
  background: linear-gradient(to right, #2FA633, #72BB29);
}
</style>