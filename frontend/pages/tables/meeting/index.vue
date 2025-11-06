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
          :disabled="isDeleting || selectedMeetings.length === 0"
          :loading="isDeleting"
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
                <th class="p-3 text-center text-sm font-medium text-gray-700">出席人數</th>
                <th class="p-3 text-center text-sm font-medium text-gray-700">納入計算總人數</th>
                <th class="p-3 text-center text-sm font-medium text-gray-700">列席總人數</th>
                <th class="p-3 text-center text-sm font-medium text-gray-700">投票議題數</th>
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
                <td class="p-3 text-sm">
                  <div class="text-center">
                    <div>{{ meeting.date }}</div>
                    <div>{{ meeting.time }}</div>
                  </div>
                </td>
                <td class="p-3 text-sm text-center">{{ meeting.attendees }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.totalCountedAttendees }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.totalObservers }}</td>
                <td class="p-3 text-sm text-center">{{ meeting.votingTopicCount }}</td>
                <td class="p-3 text-center">
                  <div class="flex flex-wrap gap-1 justify-center">
                    <UButton
                      color="green"
                      size="xs"
                      @click="showBasicInfo(meeting)"
                    >
                      基本資料
                    </UButton>
                    <UButton
                      color="blue"
                      size="xs"
                      @click="showVotingTopics(meeting)"
                    >
                      投票議題
                    </UButton>
                    <UButton
                      color="purple"
                      size="xs"
                      @click="showMemberCheckin(meeting)"
                    >
                      會員報到
                    </UButton>
                    <UButton
                      color="orange"
                      size="xs"
                      @click="showCheckinDisplay(meeting)"
                    >
                      報到顯示
                    </UButton>
                  </div>
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


    <!-- Voting Topics Modal -->
    <UModal v-model="showVotingTopicsModal" :ui="{ width: 'max-w-6xl' }">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">投票議題管理</h3>
            <UButton
              variant="ghost"
              icon="i-heroicons-x-mark-20-solid"
              @click="showVotingTopicsModal = false"
            />
          </div>
        </template>

        <div class="space-y-6 p-6">
          <!-- Action Buttons -->
          <div class="flex justify-end gap-4">
            <UButton
              color="green"
              @click="addVotingTopic"
            >
              <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
              新增議題
            </UButton>
          </div>

          <!-- Voting Topics Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b">
                  <th class="p-3 text-left text-sm font-medium text-green-600">議題名稱</th>
                  <th class="p-3 text-left text-sm font-medium text-green-600">所屬會議</th>
                  <th class="p-3 text-center text-sm font-medium text-green-600">最大有效圈選數</th>
                  <th class="p-3 text-center text-sm font-medium text-green-600">正取數量</th>
                  <th class="p-3 text-center text-sm font-medium text-green-600">備取數量</th>
                  <th class="p-3 text-center text-sm font-medium text-green-600">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(topic, index) in votingTopics" :key="index" class="border-b hover:bg-gray-50">
                  <td class="p-3 text-sm">{{ topic.name }}</td>
                  <td class="p-3 text-sm">{{ topic.meetingName }}</td>
                  <td class="p-3 text-sm text-center">{{ topic.maxSelections }}</td>
                  <td class="p-3 text-sm text-center">{{ topic.winnerCount }}</td>
                  <td class="p-3 text-sm text-center">{{ topic.backupCount }}</td>
                  <td class="p-3 text-center">
                    <div class="flex flex-wrap gap-1 justify-center">
                      <UButton
                        color="green"
                        size="xs"
                        @click="showVotingTopicBasicInfo(topic)"
                      >
                        <span class="text-white">基本資料</span>
                      </UButton>
                      <UButton
                        color="blue"
                        size="xs"
                        @click="startVotingTopic(topic)"
                      >
                        <span class="text-white">開始投票</span>
                      </UButton>
                      <UButton
                        color="purple"
                        size="xs"
                        @click="showVotingTopicResults(topic)"
                      >
                        <span class="text-white">投票結果</span>
                      </UButton>
                      <UButton
                        color="gray"
                        size="xs"
                        @click="showVotingTopicOther(topic)"
                      >
                        <span class="text-white">其他</span>
                      </UButton>
                    </div>
                  </td>
                </tr>
                <tr v-if="votingTopics.length === 0">
                  <td colspan="6" class="p-8 text-center text-gray-500">暫無投票議題資料</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-center pt-6 border-t border-gray-200">
            <UButton variant="outline" @click="showVotingTopicsModal = false">
              關閉
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { navigateTo } from '#app'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

// API composables
const { getMeetings, deleteMeeting } = useMeetings()
const { showSuccess, showError, showConfirm } = useSweetAlert()

const selectAll = ref(false)
const selectedMeetings = ref([])
const pageSize = ref(10)
const loading = ref(false)
const isDeleting = ref(false)

// Modal state
const showVotingTopicsModal = ref(false)


// Voting topics data
const votingTopics = ref([
  {
    id: 1,
    name: '理事會選舉',
    meetingName: '114年度第一屆第1次會員大會',
    maxSelections: 3,
    winnerCount: 3,
    backupCount: 2
  },
  {
    id: 2,
    name: '監事會選舉',
    meetingName: '114年度第一屆第1次會員大會',
    maxSelections: 2,
    winnerCount: 2,
    backupCount: 1
  }
])

// Meetings data
const meetings = ref([])

// Load meetings data on mount
onMounted(async () => {
  await loadMeetings()
})

// Load meetings function
const loadMeetings = async () => {
  loading.value = true
  console.log('[Meeting Index] Loading meetings...')

  const response = await getMeetings()
  loading.value = false

  if (response.success && response.data) {
    const meetingsData = response.data.data || response.data

    // Transform API data to match UI format
    meetings.value = Array.isArray(meetingsData) ? meetingsData.map(m => ({
      id: m.id,
      name: m.meeting_name || m.name || '',
      renewalGroup: m.renewal_group || m.renewalGroup || '',
      date: m.meeting_date || m.date || '',
      time: m.meeting_time || m.time || '',
      attendees: m.attendees || 0,
      totalCountedAttendees: m.total_counted_attendees || m.totalCountedAttendees || 0,
      totalObservers: m.total_observers || m.totalObservers || 0,
      votingTopicCount: m.voting_topic_count || m.votingTopicCount || 0
    })) : []

    console.log('[Meeting Index] Meetings loaded:', meetings.value.length)
  } else {
    console.error('[Meeting Index] Failed to load meetings:', response.error)
    showError('載入失敗', response.error?.message || '無法載入會議資料')
    // Use fallback mock data if API fails
    meetings.value = [
      {
        id: 1,
        name: '114年度第一屆第1次會員大會',
        renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
        date: '2025年3月15日',
        time: '下午2:00:00',
        attendees: 73,
        totalCountedAttendees: 72,
        totalObservers: 0,
        votingTopicCount: 5
      },
      {
        id: 2,
        name: '114年度第一屆第2次會員大會',
        renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
        date: '2025年8月9日',
        time: '下午2:00:00',
        attendees: 74,
        totalCountedAttendees: 74,
        totalObservers: 0,
        votingTopicCount: 3
      }
    ]
  }
}

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedMeetings.value = meetings.value.map(m => m.id)
  } else {
    selectedMeetings.value = []
  }
}

const addMeeting = () => {
  console.log('Adding new meeting')
  navigateTo('/tables/meeting/new/basic-info')
}

// Refresh data function
const refreshData = async () => {
  await loadMeetings()
}

// Delete meetings function
const deleteMeetings = async () => {
  if (selectedMeetings.value.length === 0) {
    showError('請選擇會議', '請至少選擇一個會議進行刪除')
    return
  }

  const confirmed = await showConfirm(
    '確認刪除',
    `確定要刪除 ${selectedMeetings.value.length} 個會議嗎？此操作無法復原。`
  )

  if (!confirmed) {
    return
  }

  isDeleting.value = true
  let successCount = 0
  let failCount = 0

  for (const meetingId of selectedMeetings.value) {
    console.log('[Meeting Index] Deleting meeting:', meetingId)
    const response = await deleteMeeting(meetingId)

    if (response.success) {
      successCount++
    } else {
      failCount++
      console.error('[Meeting Index] Failed to delete meeting:', meetingId, response.error)
    }
  }

  isDeleting.value = false

  if (successCount > 0) {
    showSuccess('刪除成功', `成功刪除 ${successCount} 個會議`)
    selectedMeetings.value = []
    selectAll.value = false
    await loadMeetings()
  }

  if (failCount > 0) {
    showError('部分刪除失敗', `${failCount} 個會議刪除失敗`)
  }
}

const showBasicInfo = (meeting) => {
  console.log('Showing basic info for:', meeting)
  navigateTo(`/tables/meeting/${meeting.id}/basic-info`)
}

const showVotingTopics = (meeting) => {
  console.log('Showing voting topics for:', meeting)
  navigateTo(`/tables/meeting/${meeting.id}/voting-topics`)
}

const showMemberCheckin = (meeting) => {
  console.log('Showing member check-in for:', meeting)
  navigateTo(`/tables/meeting/${meeting.id}/member-checkin`)
}

const showCheckinDisplay = (meeting) => {
  console.log('Showing check-in display for:', meeting)
  navigateTo(`/tables/meeting/${meeting.id}/checkin-display`)
}


// Voting topics functions
const addVotingTopic = () => {
  console.log('Adding new voting topic')
  // TODO: Implement add voting topic functionality
}

const showVotingTopicBasicInfo = (topic) => {
  console.log('Showing basic info for voting topic:', topic)
  // TODO: Implement voting topic basic info functionality
}

const startVotingTopic = (topic) => {
  console.log('Starting voting for topic:', topic)
  // TODO: Implement start voting functionality
}

const showVotingTopicResults = (topic) => {
  console.log('Showing voting results for topic:', topic)
  // TODO: Implement voting results functionality
}

const showVotingTopicOther = (topic) => {
  console.log('Showing other options for topic:', topic)
  // TODO: Implement other options functionality
}
</script>