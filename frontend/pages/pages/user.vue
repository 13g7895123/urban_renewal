<template>
  <NuxtLayout name="main">
    <template #title>使用者基本資料</template>
    
    <div class="p-8">
      <!-- Header with green background -->
      <div class="bg-green-500 text-white p-4 rounded-t-lg flex justify-between items-center">
        <h2 class="text-xl font-semibold">使用者基本資料</h2>
        <UButton 
          color="white" 
          variant="solid"
          @click="changePassword"
        >
          <Icon name="heroicons:lock-closed" class="w-5 h-5 mr-2" />
          變更密碼
        </UButton>
      </div>
      
      <!-- Form Content -->
      <UCard class="rounded-t-none">
        <form @submit.prevent="saveProfile" class="space-y-6">
          <!-- Account ID -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">帳號</label>
              <UInput 
                v-model="form.accountId" 
                disabled
                placeholder="AW94070886"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">姓名</label>
              <UInput 
                v-model="form.name" 
                placeholder="許湘淳"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Nickname and Email -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">暱稱</label>
              <UInput 
                v-model="form.nickname" 
                placeholder="許湘淳"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">信箱</label>
              <UInput 
                v-model="form.email" 
                type="email"
                placeholder="k80686159@yahoo.com.tw"
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Phone and Line -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">手機號碼</label>
              <UInput 
                v-model="form.phone" 
                placeholder="0986820260"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Line帳號</label>
              <UInput 
                v-model="form.lineId" 
                placeholder=""
                class="w-full"
              />
            </div>
          </div>
          
          <!-- Company and Job Title -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">公司名稱</label>
              <UInput 
                v-model="form.company" 
                placeholder="中華開發建築經理股份有限公司"
                class="w-full"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">職稱</label>
              <UInput 
                v-model="form.jobTitle" 
                placeholder=""
                class="w-full"
              />
            </div>
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
  accountId: 'AW94070886',
  name: '許湘淳',
  nickname: '許湘淳',
  email: 'k80686159@yahoo.com.tw',
  phone: '0986820260',
  lineId: '',
  company: '中華開發建築經理股份有限公司',
  jobTitle: ''
})

const saveProfile = () => {
  console.log('Saving profile:', form.value)
  // TODO: Implement save functionality
}

const changePassword = () => {
  console.log('Change password clicked')
  // TODO: Implement password change functionality
}
</script>