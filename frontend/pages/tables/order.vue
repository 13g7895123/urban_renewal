<template>
  <NuxtLayout name="main">
    <template #title>購買紀錄</template>
    
    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">購買紀錄</h2>
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
                <td colspan="4" class="p-8 text-center text-gray-500">
                  沒有資料
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
          <div class="flex gap-2">
            <UButton variant="ghost" size="sm" disabled>
              <Icon name="heroicons:chevron-left" class="w-4 h-4" />
            </UButton>
            <UButton variant="ghost" size="sm" class="bg-blue-500 text-white">1</UButton>
            <UButton variant="ghost" size="sm" disabled>
              <Icon name="heroicons:chevron-right" class="w-4 h-4" />
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
  layout: false
})

const pageSize = ref(10)

// Empty orders array to show "no data" state
const orders = ref([
  // Example order structure (commented out to show empty state):
  // {
  //   orderNumber: 'ORD001',
  //   content: '增開更新會',
  //   amount: 3000,
  //   orderDate: '2024-01-15 14:30:00'
  // }
])
</script>