<template>
  <NuxtLayout name="main">
    <template #title>購買紀錄</template>
    
    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="bg-white/20 p-3 rounded-lg mr-4">
              <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h2 class="text-2xl font-semibold">購買紀錄</h2>
              <p class="text-green-100 text-sm mt-1">查看您的所有訂單</p>
            </div>
          </div>
          <UButton color="white" variant="outline" @click="router.push('/pages/shopping')">
            <Icon name="heroicons:shopping-bag" class="w-5 h-5 mr-2" />
            前往購買
          </UButton>
        </div>
      </div>
      
      <!-- Orders Table -->
      <UCard>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="p-3 text-left text-sm font-medium text-gray-700">訂單編號</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">訂購內容</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">總金額</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">下單時間</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="orders.length === 0">
                <td colspan="4" class="p-8 text-center">
                  <div class="flex flex-col items-center justify-center py-8">
                    <Icon name="heroicons:shopping-bag" class="w-16 h-16 text-gray-300 mb-4" />
                    <p class="text-gray-500 mb-2">尚無購買紀錄</p>
                    <p class="text-sm text-gray-400 mb-4">您還沒有進行任何購買</p>
                    <UButton color="green" @click="router.push('/pages/shopping')">
                      <Icon name="heroicons:shopping-cart" class="w-4 h-4 mr-2" />
                      前往購買
                    </UButton>
                  </div>
                </td>
              </tr>
              <tr v-for="(order, index) in orders" :key="index" class="border-b hover:bg-gray-50">
                <td class="p-3 text-sm">{{ order.orderNumber }}</td>
                <td class="p-3 text-sm">{{ order.content }}</td>
                <td class="p-3 text-sm">${{ order.amount }}</td>
                <td class="p-3 text-sm">{{ order.orderDate }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4 pt-4 border-t">
          <div class="text-sm text-gray-500">
            每頁顯示：
            <USelectMenu 
              v-model="pageSize" 
              :options="[10, 20, 50]" 
              size="sm"
              class="inline-block w-20 ml-2"
            />
          </div>
          <div class="text-sm text-gray-500">
            - -
          </div>
          <div class="flex gap-2 items-center">
            <UButton variant="ghost" size="sm" disabled>
              <Icon name="heroicons:chevron-left" class="w-4 h-4" />
            </UButton>
            <UButton variant="ghost" size="sm" class="bg-blue-500 text-white">1</UButton>
            <UButton variant="ghost" size="sm" disabled>
              <Icon name="heroicons:chevron-right" class="w-4 h-4" />
            </UButton>

            <!-- Refresh Button -->
            <UButton
              @click="refreshData"
              :loading="loading"
              variant="ghost"
              size="sm"
              class="ml-2 text-gray-600 hover:text-green-600 hover:bg-green-50"
              title="重新整理"
            >
              <Icon name="heroicons:arrow-path" class="w-4 h-4" />
            </UButton>
          </div>
        </div>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref } from 'vue'

definePageMeta({
  middleware: 'auth',  // 購買紀錄需要登入才能查看
  layout: false
})

const router = useRouter()
const pageSize = ref(10)
const loading = ref(false)

// TODO: 從 API 取得訂單資料
// 目前使用模擬資料
const orders = ref([
  // 模擬訂單資料
  // {
  //   orderNumber: 'ORD001',
  //   content: '增開更新會',
  //   amount: 3000,
  //   orderDate: '2024-01-15 14:30:00'
  // }
])

// Refresh data function
const refreshData = async () => {
  loading.value = true
  try {
    // TODO: Replace with actual API call when backend is ready
    // For now, just simulate a loading state
    await new Promise(resolve => setTimeout(resolve, 500))
    console.log('Orders data refreshed')
  } finally {
    loading.value = false
  }
}
</script>