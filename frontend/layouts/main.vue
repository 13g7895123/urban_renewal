<template>
  <div class="min-h-screen flex flex-col bg-gray-100">
    <!-- Header -->
    <header class="navbar-gradient h-16 flex items-center px-6 shadow-sm">
      <div class="flex-1"></div>
      
      <h1 class="text-white text-xl font-bold">
        <slot name="title">更新會管理</slot>
      </h1>
      
      <div class="flex-1 flex justify-end items-center space-x-4">
        <UButton 
          variant="ghost" 
          color="white" 
          size="sm"
          class="text-white hover:bg-green-600"
        >
          <Icon name="heroicons:shopping-cart" class="w-5 h-5 mr-2" />
          購物車
        </UButton>
        
        <UButton 
          variant="ghost" 
          color="white" 
          size="sm"
          class="text-white hover:bg-green-600"
        >
          <Icon name="heroicons:arrow-right-on-rectangle" class="w-5 h-5 mr-2" />
          登出
        </UButton>
      </div>
    </header>

    <div class="flex flex-1">
      <!-- Sidebar -->
      <aside class="w-64 bg-gray-800/90 min-h-full text-white">
        <div class="p-4">
          <div class="flex items-center space-x-3 mb-8">
            <div class="w-16 h-16 bg-gray-600/90 rounded-full flex items-center justify-center">
              <Icon name="heroicons:user" class="w-8 h-8 text-white" />
            </div>
            <div>
              <div class="text-white font-medium">許湘淳</div>
              <div class="w-6 h-6 bg-gray-600/90 rounded-full mt-1"></div>
            </div>
          </div>
          
          <nav class="space-y-2">
            <NuxtLink to="/" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/' }">
              <Icon name="heroicons:home" class="w-5 h-5" />
              <span>首頁</span>
            </NuxtLink>
            
            <NuxtLink to="/tables/urban-renewal" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/tables/urban-renewal' }">
              <Icon name="heroicons:building-office-2" class="w-5 h-5" />
              <span>更新會管理</span>
            </NuxtLink>
            
            <NuxtLink to="/tables/meeting" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/tables/meeting' }">
              <Icon name="heroicons:document-text" class="w-5 h-5" />
              <span>會議管理</span>
            </NuxtLink>
            
            <NuxtLink to="/tables/issue" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/tables/issue' }">
              <Icon name="heroicons:check-badge" class="w-5 h-5" />
              <span>投票管理</span>
            </NuxtLink>
            
            <NuxtLink to="/pages/shopping" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/pages/shopping' }">
              <Icon name="heroicons:shopping-bag" class="w-5 h-5" />
              <span>商城</span>
            </NuxtLink>
            
            <NuxtLink to="/tables/order" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/tables/order' }">
              <Icon name="heroicons:clipboard-document-list" class="w-5 h-5" />
              <span>購買紀錄</span>
            </NuxtLink>
            
            <NuxtLink to="/pages/user" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/pages/user' }">
              <Icon name="heroicons:user" class="w-5 h-5" />
              <span>使用者基本資料變更</span>
            </NuxtLink>
            
            <NuxtLink to="/tables/company-profile" class="flex items-center space-x-3 p-3 rounded hover:bg-gray-700 transition-colors" :class="{ 'bg-blue-600': $route.path === '/tables/company-profile' }">
              <Icon name="heroicons:building-office" class="w-5 h-5" />
              <span>企業管理</span>
            </NuxtLink>
          </nav>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 bg-gray-50 flex flex-col">
        <div class="flex-1">
          <slot />
        </div>
        <Footer copyright-text="© 2020, made with ❤️ by Thread Tech" />
      </main>
    </div>
  </div>
</template>

<script setup>
// Main layout for authenticated pages
</script>

<style scoped>
.navbar-gradient {
  background: linear-gradient(to right, #2FA633, #72BB29);
}
</style>