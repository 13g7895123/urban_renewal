<template>
  <NuxtLayout name="main">
    <template #title>更新會管理</template>
    
    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">更新會</h2>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 mb-6">
        <UButton 
          color="green" 
          @click="allocateRenewal"
        >
          <Icon name="heroicons:users" class="w-5 h-5 mr-2" />
          分配更新會
        </UButton>
        <UButton 
          color="green" 
          @click="createRenewal"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新建更新會
        </UButton>
      </div>
      
      <!-- Urban Renewal Table -->
      <UCard>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="p-3 text-left text-sm font-medium text-gray-700">更新會名稱</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">土地面積 (平方公尺)</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">所有權人數</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">理事長姓名</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">理事長電話</th>
                <th class="p-3 text-center text-sm font-medium text-gray-700">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(renewal, index) in renewals" :key="index" class="border-b hover:bg-gray-50">
                <td class="p-3 text-sm">{{ renewal.name }}</td>
                <td class="p-3 text-sm text-center">{{ renewal.area }}</td>
                <td class="p-3 text-sm text-center">{{ renewal.memberCount }}</td>
                <td class="p-3 text-sm">{{ renewal.chairmanName }}</td>
                <td class="p-3 text-sm">{{ renewal.chairmanPhone }}</td>
                <td class="p-3 text-center space-x-2">
                  <UButton 
                    color="green" 
                    size="xs"
                    @click="viewBasicInfo(renewal)"
                  >
                    基本資料
                  </UButton>
                  <UButton 
                    color="blue" 
                    size="xs"
                    @click="viewMembers(renewal)"
                  >
                    查詢會員
                  </UButton>
                  <UButton 
                    color="blue" 
                    size="xs"
                    @click="viewJointInfo(renewal)"
                  >
                    共舉資訊
                  </UButton>
                  <UButton 
                    color="red" 
                    size="xs"
                    @click="deleteRenewal(renewal)"
                  >
                    刪除
                  </UButton>
                </td>
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
            1-1 共 1
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

const renewals = ref([
  {
    id: 1,
    name: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
    area: '1955.00',
    memberCount: 74,
    chairmanName: '王文忠',
    chairmanPhone: '0933-734063'
  }
])

const allocateRenewal = () => {
  console.log('Allocating renewal meeting')
  // TODO: Implement allocate functionality
}

const createRenewal = () => {
  console.log('Creating new renewal meeting')
  // TODO: Implement create functionality
}

const viewBasicInfo = (renewal) => {
  console.log('Viewing basic info for:', renewal)
  // TODO: Implement view basic info functionality
}

const viewMembers = (renewal) => {
  console.log('Viewing members for:', renewal)
  // TODO: Implement view members functionality
}

const viewJointInfo = (renewal) => {
  console.log('Viewing joint info for:', renewal)
  // TODO: Implement view joint info functionality
}

const deleteRenewal = (renewal) => {
  console.log('Deleting renewal:', renewal)
  // TODO: Implement delete functionality
}
</script>