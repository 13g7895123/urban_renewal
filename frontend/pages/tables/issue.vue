<template>
  <NuxtLayout name="main">
    <template #title>會議管理_會議選擇</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:users" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">會議管理</h2>
        </div>
      </div>

      <!-- Selection Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">選擇條件</h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left Column: Urban Renewal Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">選擇更新會 <span
                class="text-red-500">*</span></label>
            <div style="background-color: white !important;">
              <USelectMenu v-model="selectedRenewal" :options="renewalOptions" placeholder="請選擇更新會" by="id"
                option-attribute="name" class="w-full" :loading="loadingRenewals" :disabled="loadingRenewals" :ui="{
                  base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
                  background: 'bg-white dark:bg-white',
                  color: 'text-gray-900 dark:text-gray-900'
                }" :ui-menu="{ background: 'bg-white' }" @update:model-value="onRenewalChange">
                <template #label>
                  <span v-if="selectedRenewal">{{ selectedRenewal.name }}</span>
                  <span v-else class="text-gray-400">請選擇更新會</span>
                </template>
                <template #option="{ option }">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ option.name }}</span>
                    <span class="text-xs text-gray-500">理事長: {{ option.chairman_name }} | 所有權人數: {{ option.member_count
                    }}</span>
                  </div>
                </template>
              </USelectMenu>
            </div>
          </div>

          <!-- Right Column: Meeting Date Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">選擇會議日期</label>
            <div style="background-color: white !important;">
              <USelectMenu v-model="selectedMeeting" :options="meetingOptions" placeholder="請先選擇更新會" by="id"
                option-attribute="meeting_date" class="w-full" :loading="loadingMeetings"
                :disabled="!selectedRenewal || loadingMeetings" :ui="{
                  base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
                  background: 'bg-white dark:bg-white',
                  color: 'text-gray-900 dark:text-gray-900'
                }" :ui-menu="{ background: 'bg-white' }" @update:model-value="onMeetingChange">
                <template #label>
                  <span v-if="selectedMeeting">{{ formatDate(selectedMeeting.meeting_date) }} - {{
                    selectedMeeting.meeting_name || '未命名' }}</span>
                  <span v-else class="text-gray-400">{{ selectedRenewal ? '所有會議' : '請先選擇更新會' }}</span>
                </template>
                <template #option="{ option }">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ formatDate(option.meeting_date) }}</span>
                    <span class="text-xs text-gray-500">會議名稱: {{ option.meeting_name || '未命名' }}</span>
                  </div>
                </template>
              </USelectMenu>
            </div>
          </div>
        </div>
      </div>

      <!-- Meeting List -->
      <div v-if="selectedRenewal" class="relative pb-20">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">會議名稱列表</h3>
          </div>

          <div v-if="loadingMeetings" class="flex justify-center items-center py-12">
            <div class="flex items-center text-gray-500">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              載入中...
            </div>
          </div>

          <div v-else-if="displayedMeetings.length === 0" class="text-center py-12">
            <Icon name="heroicons:document-text" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500 mb-4">沒有符合條件的會議</p>
          </div>

          <div v-else class="space-y-3">
            <div v-for="meeting in displayedMeetings" :key="meeting.id"
              class="p-4 border rounded-lg transition-colors cursor-pointer" :class="[
                selectedListMeeting?.id === meeting.id
                  ? 'bg-green-50 border-green-500'
                  : 'border-gray-200 hover:bg-gray-50'
              ]" @click="selectListMeeting(meeting)">
              <div class="flex justify-between items-center">
                <div class="flex-1">
                  <div class="flex items-center gap-3">
                    <h4 class="font-medium"
                      :class="selectedListMeeting?.id === meeting.id ? 'text-green-700' : 'text-gray-900'">
                      {{ formatDate(meeting.meeting_date) }} - {{ meeting.meeting_name || '未命名會議' }}
                    </h4>
                    <UBadge :color="getMeetingStatusColor(meeting.status)" size="xs">
                      {{ getMeetingStatusLabel(meeting.status) }}
                    </UBadge>
                  </div>
                  <div class="mt-1 text-sm text-gray-600 flex gap-4">
                    <span>
                      <Icon name="heroicons:map-pin" class="w-4 h-4 inline mr-1" />{{ meeting.location || '未設定地點' }}
                    </span>
                    <span>
                      <Icon name="heroicons:clock" class="w-4 h-4 inline mr-1" />{{ meeting.meeting_time || '--:--' }}
                    </span>
                  </div>
                </div>
                <div v-if="selectedListMeeting?.id === meeting.id">
                  <Icon name="heroicons:check-circle" class="w-6 h-6 text-green-500" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Confirm Button (Outside list container) -->
        <div class="flex justify-end">
          <UButton color="green" size="lg" :disabled="!selectedListMeeting" @click="confirmSelection">
            確認
          </UButton>
        </div>
      </div>

      <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <Icon name="heroicons:information-circle" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500 text-lg">請選擇更新會以查看會議列表</p>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

definePageMeta({
  middleware: ['auth'],
  layout: false
})

// Composables
const { getUrbanRenewals } = useUrbanRenewal()
const { getMeetings } = useMeetings()
const { get } = useApi()
const alert = useAlert()
const router = useRouter()

// State
const loadingRenewals = ref(false)
const loadingMeetings = ref(false)

const selectedRenewal = ref(null)
const selectedMeeting = ref(null) // From Dropdown
const selectedListMeeting = ref(null) // From List

const renewalOptions = ref([])
const meetingOptions = ref([])

// Computed
const displayedMeetings = computed(() => {
  if (selectedMeeting.value) {
    return [selectedMeeting.value]
  }
  return meetingOptions.value
})

// Fetch urban renewals
const fetchRenewals = async () => {
  loadingRenewals.value = true
  try {
    const response = await getUrbanRenewals()
    // console.log('fetchRenewals response:', response)

    if (response.success) {
      if (Array.isArray(response.data)) {
        renewalOptions.value = response.data
      } else if (response.data && Array.isArray(response.data.data)) {
        renewalOptions.value = response.data.data
      } else if (response.data && response.data.status === 'success' && Array.isArray(response.data.data)) {
        renewalOptions.value = response.data.data
      } else {
        renewalOptions.value = []
        console.warn('Unexpected response format for renewals:', response)
      }
    } else {
      console.error('Failed response for renewals:', response)
    }
  } catch (error) {
    console.error('Failed to fetch renewals:', error)
    alert.error('錯誤', '無法載入更新會列表')
  } finally {
    loadingRenewals.value = false
  }
}

// Fetch meetings for selected renewal
const fetchMeetings = async (renewalId) => {
  if (!renewalId) return

  loadingMeetings.value = true
  meetingOptions.value = []
  selectedMeeting.value = null
  selectedListMeeting.value = null

  try {
    const response = await getMeetings({ urban_renewal_id: renewalId })
    // console.log('fetchMeetings response:', response)

    if (response.success) {
      if (Array.isArray(response.data)) {
        meetingOptions.value = response.data
      } else if (response.data && Array.isArray(response.data.data)) {
        meetingOptions.value = response.data.data
      } else {
        meetingOptions.value = []
        // console.warn('Unexpected response format for meetings:', response)
      }
    }
  } catch (error) {
    console.error('Failed to fetch meetings:', error)
    alert.error('錯誤', '無法載入會議列表')
  } finally {
    loadingMeetings.value = false
  }
}

// Event handlers
const onRenewalChange = (renewal) => {
  selectedRenewal.value = renewal
  if (renewal) {
    fetchMeetings(renewal.id)
  } else {
    meetingOptions.value = []
    selectedMeeting.value = null
    selectedListMeeting.value = null
  }
}

const onMeetingChange = (meeting) => {
  selectedMeeting.value = meeting
  if (meeting) {
    // If user selects a specific meeting from dropdown, auto-select it in the list too
    selectedListMeeting.value = meeting
  } else {
    selectedListMeeting.value = null
  }
}

const selectListMeeting = (meeting) => {
  selectedListMeeting.value = meeting
}

const confirmSelection = () => {
  if (!selectedListMeeting.value) return

  // Route to the voting topics page which seems to be the main management page for this flow
  router.push(`/tables/meeting/${selectedListMeeting.value.id}/voting-topics`)
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    weekday: 'short'
  })
}

const getMeetingStatusLabel = (status) => {
  const statusMap = {
    'pending': '待開始',
    'in_progress': '進行中',
    'completed': '已結束',
    'cancelled': '已取消'
  }
  return statusMap[status] || status
}

const getMeetingStatusColor = (status) => {
  const colorMap = {
    'pending': 'gray',
    'in_progress': 'blue',
    'completed': 'green',
    'cancelled': 'red'
  }
  return colorMap[status] || 'gray'
}

// Initialize
onMounted(() => {
  fetchRenewals()
})
</script>