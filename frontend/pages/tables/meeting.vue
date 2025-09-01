<template>
  <NuxtLayout name="main">
    <template #title>會議管理</template>
    
    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">會議</h2>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 mb-6">
        <UButton 
          color="red" 
          @click="deleteMeetings"
        >
          <Icon name="heroicons:trash" class="w-5 h-5 mr-2" />
          刪除
        </UButton>
        <UButton 
          color="green" 
          @click="addMeeting"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新增會議
        </UButton>
      </div>
      
      <!-- Meetings Table -->
      <UCard>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="p-3 text-left">
                  <UCheckbox v-model="selectAll" @change="toggleSelectAll" />
                </th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">會議名稱</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">所屬更新會</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">會議日期時間</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">出席人數</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">缺入對象人數</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">列席人數</th>
                <th class="p-3 text-left text-sm font-medium text-gray-700">記錄狀態</th>
                <th class="p-3 text-center text-sm font-medium text-gray-700">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(meeting, index) in meetings" :key="index" class="border-b hover:bg-gray-50">
                <td class="p-3">
                  <UCheckbox v-model="selectedMeetings" :value="meeting.id" />
                </td>
                <td class="p-3 text-sm">{{ meeting.name }}</td>
                <td class="p-3 text-sm">{{ meeting.renewalGroup }}</td>
                <td class="p-3 text-sm">{{ meeting.date }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.attendees }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.absent }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.observers }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.recordStatus }}</td>
                <td class="p-3 text-center space-x-2">
                  <UButton 
                    color="green" 
                    size="xs"
                    @click="viewRecord(meeting)"
                  >
                    基本資料
                  </UButton>
                  <UButton 
                    color="blue" 
                    size="xs"
                    @click="viewMeeting(meeting)"
                  >
                    投票會議
                  </UButton>
                  <UButton 
                    color="purple" 
                    size="xs"
                    @click="viewResults(meeting)"
                  >
                    會議結果
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
            1-2 共 2
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

const selectAll = ref(false)
const selectedMeetings = ref([])
const pageSize = ref(10)

const meetings = ref([
  {
    id: 1,
    name: '114年度第一屆第1次會員大會',
    renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
    date: '2025年3月15日下午2:00:00',
    attendees: 73,
    absent: 72,
    observers: 0,
    recordStatus: 3
  },
  {
    id: 2,
    name: '114年度第一屆第2次會員大會',
    renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
    date: '2025年8月9日下午2:00:00',
    attendees: 74,
    absent: 74,
    observers: 0,
    recordStatus: 3
  }
])

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedMeetings.value = meetings.value.map(m => m.id)
  } else {
    selectedMeetings.value = []
  }
}

const addMeeting = () => {
  console.log('Adding new meeting')
  // TODO: Implement add meeting functionality
}

const deleteMeetings = () => {
  console.log('Deleting meetings:', selectedMeetings.value)
  // TODO: Implement delete functionality
}

const viewRecord = (meeting) => {
  console.log('Viewing record for:', meeting)
  // TODO: Implement view record functionality
}

const viewMeeting = (meeting) => {
  console.log('Viewing meeting for:', meeting)
  // TODO: Implement view meeting functionality
}

const viewResults = (meeting) => {
  console.log('Viewing results for:', meeting)
  // TODO: Implement view results functionality
}
</script>