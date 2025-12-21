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

      <!-- Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-16">
        <!-- Urban Renewal Management Card -->
        <NuxtLink to="/tables/urban-renewal" class="transform hover:scale-105 transition-transform">
          <UCard class="bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
            <div class="h-full flex flex-col items-center justify-center">
              <div class="w-48 h-48 mb-6 flex items-center justify-center">
                <img src="~/assets/images/urban.png" alt="更新會管理" class="w-full h-full object-contain" />
              </div>
              <h3 class="text-2xl font-bold text-gray-700">更新會管理</h3>
            </div>
          </UCard>
        </NuxtLink>

        <!-- Meeting Management Card -->
        <NuxtLink to="/tables/meeting" class="transform hover:scale-105 transition-transform">
          <UCard class="bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
            <div class="h-full flex flex-col items-center justify-center">
              <div class="w-48 h-48 mb-6 flex items-center justify-center">
                <img src="~/assets/images/meeting.png" alt="會議管理" class="w-full h-full object-contain" />
              </div>
              <h3 class="text-2xl font-bold text-gray-700">會議管理</h3>
            </div>
          </UCard>
        </NuxtLink>

        <!-- Voting Management Card -->
        <NuxtLink to="/tables/issue" class="transform hover:scale-105 transition-transform">
          <UCard class="bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
            <div class="h-full flex flex-col items-center justify-center">
              <div class="w-48 h-48 mb-6 flex items-center justify-center">
                <img src="~/assets/images/voting.png" alt="投票管理" class="w-full h-full object-contain" />
              </div>
              <h3 class="text-2xl font-bold text-gray-700">投票管理</h3>
            </div>
          </UCard>
        </NuxtLink>

        <!-- User Profile Update Card -->
        <NuxtLink to="/pages/user" class="transform hover:scale-105 transition-transform">
          <UCard class="bg-white shadow-lg hover:shadow-xl transition-all duration-300 py-8">
            <div class="h-full flex flex-col items-center justify-center">
              <div class="w-48 h-48 mb-6 flex items-center justify-center">
                <img src="~/assets/images/profile.png" alt="使用者基本資料變更" class="w-full h-full object-contain" />
              </div>
              <h3 class="text-2xl font-bold text-gray-700">使用者基本資料變更</h3>
            </div>
          </UCard>
        </NuxtLink>
      </div>

      <!-- Bottom Decorative Elements -->
      <div class="mt-16 relative h-64">
        <div class="absolute bottom-0 left-0 w-full">
          <!-- Trees and buildings decorative background -->
          <div class="flex justify-between items-end">
            <div class="w-20 h-32 bg-green-400 rounded-t-full"></div>
            <div class="w-24 h-40 bg-yellow-500"></div>
            <div class="w-16 h-28 bg-orange-400"></div>
            <div class="w-20 h-36 bg-blue-400"></div>
            <div class="w-24 h-32 bg-green-400 rounded-t-full"></div>
            <div class="w-20 h-44 bg-orange-500"></div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
definePageMeta({
  middleware: 'auth',
  layout: false
})
</script>