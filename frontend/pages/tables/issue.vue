<template>
  <NuxtLayout name="main">
    <template #title>議題管理_會議選擇</template>
    
    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">投票管理</h2>
        </div>
      </div>
      
      <!-- Selection Dropdowns -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">選擇更新會</label>
          <USelectMenu 
            v-model="selectedRenewal"
            :options="renewalOptions"
            placeholder="請選擇更新會"
            class="w-full"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">選擇會議日期</label>
          <USelectMenu 
            v-model="selectedMeetingDate"
            :options="meetingDateOptions"
            placeholder="請選擇會議日期"
            class="w-full"
          />
        </div>
      </div>
      
      <!-- Issue Form -->
      <UCard>
        <form @submit.prevent="submitIssue">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議名稱列表</label>
              <div class="border rounded-lg p-6 min-h-[300px] bg-gray-50">
                <!-- Placeholder for meeting list content -->
                <p class="text-gray-500 text-center">請選擇更新會及會議日期</p>
              </div>
            </div>
          </div>
          
          <!-- Confirm Button -->
          <div class="flex justify-end mt-6">
            <UButton 
              type="submit"
              color="green" 
              size="lg"
            >
              確認
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

const selectedRenewal = ref(null)
const selectedMeetingDate = ref(null)

const renewalOptions = ref([
  { label: '更新會 1', value: 'renewal1' },
  { label: '更新會 2', value: 'renewal2' },
  { label: '更新會 3', value: 'renewal3' }
])

const meetingDateOptions = ref([
  { label: '2024-01-15', value: '2024-01-15' },
  { label: '2024-02-15', value: '2024-02-15' },
  { label: '2024-03-15', value: '2024-03-15' }
])

const submitIssue = () => {
  console.log('Submitting issue:', {
    renewal: selectedRenewal.value,
    meetingDate: selectedMeetingDate.value
  })
  // TODO: Implement issue submission
}
</script>