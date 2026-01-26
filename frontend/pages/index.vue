<template>
  <NuxtLayout name="main">
    <template #title>都更計票系統首頁</template>

    <div class="p-8">
      <!-- Sky background with clouds and sun -->
      <div class="relative">
        <div class="absolute top-0 right-20 w-24 h-24 bg-yellow-300 rounded-full"></div>
        <div class="absolute top-10 left-32 w-32 h-20 bg-white rounded-full opacity-80"></div>
        <div class="absolute top-20 right-40 w-40 h-24 bg-white rounded-full opacity-60"></div>
      </div>

      <!-- Hero Section -->
      <div class="mt-8 text-center" v-if="!authStore.isLoggedIn">
        <h2 class="text-4xl font-extrabold text-green-700 mb-4">歡迎來到都更計票系統</h2>
        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
          專業的都市更新管理平台，提供您最高效的會議管理、投票計票與更新會運作支援。
        </p>
        <div class="flex justify-center gap-4">
          <UButton to="/login" size="xl" color="green">立即登入</UButton>
          <UButton to="/pages/shopping" size="xl" color="blue" variant="outline">瀏覽商品服務</UButton>
        </div>
      </div>

      <!-- Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-16">
        <!-- Shopping Mall Card (Always Visible) -->
        <NuxtLink to="/pages/shopping" class="h-full transform hover:scale-105 transition-transform">
          <UCard class="h-full bg-gradient-to-br from-green-50 to-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
            <div class="h-full flex flex-col items-center justify-center">
              <div class="w-48 h-48 mb-6 flex items-center justify-center bg-white rounded-full shadow-inner p-4">
                <Icon name="heroicons:shopping-bag" class="w-32 h-32 text-green-600" />
              </div>
              <h3 class="text-2xl font-bold text-green-700">線上商城</h3>
            </div>
          </UCard>
        </NuxtLink>

        <template v-if="authStore.isLoggedIn">
          <!-- Urban Renewal Management Card -->
          <NuxtLink 
            v-if="canAccess('admin') || authStore.user?.is_company_manager"
            to="/tables/urban-renewal" 
            class="h-full transform hover:scale-105 transition-transform"
          >
            <UCard class="h-full bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
              <div class="h-full flex flex-col items-center justify-center">
                <div class="w-48 h-48 mb-6 flex items-center justify-center">
                  <img src="~/assets/images/urban.png" alt="更新會管理" class="w-full h-full object-contain" />
                </div>
                <h3 class="text-2xl font-bold text-gray-700">更新會管理</h3>
              </div>
            </UCard>
          </NuxtLink>

          <!-- Meeting Management Card -->
          <NuxtLink 
            v-if="canAccess('admin') || authStore.user?.is_company_manager"
            to="/tables/meeting" 
            class="h-full transform hover:scale-105 transition-transform"
          >
            <UCard class="h-full bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
              <div class="h-full flex flex-col items-center justify-center">
                <div class="w-48 h-48 mb-6 flex items-center justify-center">
                  <img src="~/assets/images/meeting.png" alt="會議管理" class="w-full h-full object-contain" />
                </div>
                <h3 class="text-2xl font-bold text-gray-700">會議管理</h3>
              </div>
            </UCard>
          </NuxtLink>

          <!-- Voting Management Card -->
          <NuxtLink 
            v-if="canAccess('member')"
            to="/tables/issue" 
            class="h-full transform hover:scale-105 transition-transform"
          >
            <UCard class="h-full bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
              <div class="h-full flex flex-col items-center justify-center">
                <div class="w-48 h-48 mb-6 flex items-center justify-center">
                  <img src="~/assets/images/voting.png" alt="投票管理" class="w-full h-full object-contain" />
                </div>
                <h3 class="text-2xl font-bold text-gray-700">投票管理</h3>
              </div>
            </UCard>
          </NuxtLink>

          <!-- User Profile Update Card -->
          <NuxtLink to="/pages/user" class="h-full transform hover:scale-105 transition-transform">
            <UCard class="h-full bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
              <div class="h-full flex flex-col items-center justify-center">
                <div class="w-48 h-48 mb-6 flex items-center justify-center">
                  <img src="~/assets/images/profile.png" alt="使用者基本資料變更" class="w-full h-full object-contain" />
                </div>
                <h3 class="text-2xl font-bold text-gray-700">使用者基本資料變更</h3>
              </div>
            </UCard>
          </NuxtLink>
        </template>
        
        <!-- Registration/Login Card for Guests -->
        <template v-else>
          <NuxtLink to="/login" class="h-full transform hover:scale-105 transition-transform">
            <UCard class="h-full bg-gray-50 shadow-md hover:shadow-lg transition-all duration-300 py-8 border-dashed border-2 border-gray-300">
              <div class="h-full flex flex-col items-center justify-center text-center px-4">
                <Icon name="heroicons:lock-closed" class="w-24 h-24 text-gray-400 mb-6" />
                <h3 class="text-xl font-bold text-gray-500">管理功能位元登入</h3>
                <p class="text-sm text-gray-400 mt-2">登入後即可存取所有更新會管理與會議功能</p>
                <UButton color="gray" variant="soft" class="mt-6">登入帳號</UButton>
              </div>
            </UCard>
          </NuxtLink>
        </template>
      </div>

      <!-- Bottom Decorative Elements -->
      <div class="mt-16 relative h-64 overflow-hidden rounded-t-3xl">
        <div class="absolute bottom-0 left-0 w-full">
          <!-- Trees and buildings decorative background -->
          <div class="flex justify-between items-end">
            <div class="w-20 h-32 bg-green-400 rounded-t-full"></div>
            <div class="w-24 h-40 bg-yellow-500 rounded-t-2xl"></div>
            <div class="w-16 h-28 bg-orange-400 rounded-t-xl"></div>
            <div class="w-20 h-36 bg-blue-400 rounded-t-3xl"></div>
            <div class="w-24 h-32 bg-green-400 rounded-t-full"></div>
            <div class="w-32 h-56 bg-gray-200 rounded-t-lg relative">
              <div class="absolute inset-2 grid grid-cols-2 gap-1">
                <div v-for="i in 8" :key="i" class="w-full h-4 bg-white/60"></div>
              </div>
            </div>
            <div class="w-20 h-44 bg-orange-500 rounded-t-2xl"></div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const canAccess = (minRole) => {
  if (!authStore.isLoggedIn) return false
  const roles = ['observer', 'member', 'chairman', 'admin']
  const userRoleIndex = roles.indexOf(authStore.user?.role || 'observer')
  const minRoleIndex = roles.indexOf(minRole)
  return userRoleIndex >= minRoleIndex
}

definePageMeta({
  layout: false
})
</script>