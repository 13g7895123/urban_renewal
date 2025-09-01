<template>
  <NuxtLayout name="main">
    <template #title>企業基本資料</template>
    
    <div class="p-8">
      <!-- Header with green background -->
      <div class="bg-green-500 text-white p-4 rounded-t-lg">
        <h2 class="text-xl font-semibold">企業基本資料</h2>
      </div>
      
      <!-- Form Content -->
      <UCard class="rounded-t-none">
        <form @submit.prevent="saveCompanyProfile" class="space-y-6">
          <!-- Company Name and ID -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">企業名稱</label>
              <UInput 
                v-model="form.companyName" 
                placeholder="中華開發建築經理股份有限公司"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">統一編號</label>
              <UInput 
                v-model="form.taxId" 
                placeholder="94070886"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Company Phone and Max Updates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">企業電話</label>
              <UInput 
                v-model="form.companyPhone" 
                placeholder="02-6604-3889"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">最大更新會數量</label>
              <UInput 
                v-model="form.maxRenewalCount" 
                type="number"
                placeholder="1"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Max Vote Count -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">最大議題數量</label>
              <UInput 
                v-model="form.maxIssueCount" 
                type="number"
                placeholder="8"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- New Manager Section -->
          <div class="mt-8 flex items-end gap-4">
            <UButton 
              color="green" 
              size="sm"
              @click="addNewManager"
            >
              <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
              新增使用者
            </UButton>
          </div>
          
          <!-- Managers Table -->
          <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">企業管理者</h3>
            <div class="border rounded-lg overflow-hidden">
              <table class="w-full">
                <tbody>
                  <tr v-for="(manager, index) in managers" :key="index" class="border-b">
                    <td class="p-4 text-gray-700">{{ manager.id }}</td>
                    <td class="p-4 text-gray-700">{{ manager.name }}</td>
                    <td class="p-4 text-gray-700">{{ manager.company }}</td>
                    <td class="p-4 text-right space-x-2">
                      <UButton 
                        color="orange" 
                        size="xs"
                        @click="viewUser(manager)"
                      >
                        查詢取用者
                      </UButton>
                      <UButton 
                        color="red" 
                        size="xs"
                        @click="deleteManager(index)"
                      >
                        刪除
                      </UButton>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">企業使用者</h3>
            <!-- Empty state or additional users can be added here -->
          </div>
          
          <!-- Save Button -->
          <div class="flex justify-end pt-4">
            <UButton 
              type="submit"
              color="green" 
              size="lg"
            >
              儲存
            </UButton>
          </div>
        </form>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref } from 'vue'

definePageMeta({
  layout: false
})

const form = ref({
  companyName: '中華開發建築經理股份有限公司',
  taxId: '94070886',
  companyPhone: '02-6604-3889',
  maxRenewalCount: 1,
  maxIssueCount: 8
})

const managers = ref([
  {
    id: 'AW94070886',
    name: '',
    company: ''
  },
  {
    id: 'ted3',
    name: '',
    company: ''
  },
  {
    id: 'wenchi886',
    name: '',
    company: ''
  }
])

const saveCompanyProfile = () => {
  console.log('Saving company profile:', form.value)
  // TODO: Implement save functionality
}

const addNewManager = () => {
  console.log('Adding new manager')
  // TODO: Implement add manager functionality
}

const viewUser = (manager) => {
  console.log('Viewing user:', manager)
  // TODO: Implement view user functionality
}

const deleteManager = (index) => {
  managers.value.splice(index, 1)
}
</script>