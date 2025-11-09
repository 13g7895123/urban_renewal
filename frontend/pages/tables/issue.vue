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
          <h2 class="text-2xl font-semibold">議題管理</h2>
        </div>
      </div>

      <!-- Selection Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">選擇條件</h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left Column: Urban Renewal Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">選擇更新會 <span class="text-red-500">*</span></label>
            <div style="background-color: white !important;">
              <USelectMenu
                v-model="selectedRenewal"
                :options="renewalOptions"
                placeholder="請選擇更新會"
                value-attribute="id"
                option-attribute="name"
                class="w-full"
                :loading="loadingRenewals"
                :disabled="loadingRenewals"
                :ui="{
                  base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
                  background: 'bg-white dark:bg-white',
                  color: 'text-gray-900 dark:text-gray-900'
                }"
                :ui-menu="{ background: 'bg-white' }"
                @update:model-value="onRenewalChange"
              >
                <template #label>
                  <span v-if="selectedRenewal">{{ selectedRenewal.name }}</span>
                  <span v-else class="text-gray-400">請選擇更新會</span>
                </template>
                <template #option="{ option }">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ option.name }}</span>
                    <span class="text-xs text-gray-500">理事長: {{ option.chairman_name }} | 所有權人數: {{ option.member_count }}</span>
                  </div>
                </template>
              </USelectMenu>
            </div>

            <!-- Renewal Info Card -->
            <div v-if="selectedRenewal" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <h4 class="text-sm font-semibold text-gray-700 mb-2">更新會資訊</h4>
              <div class="text-sm text-gray-600 space-y-1">
                <div class="flex justify-between">
                  <span>更新會名稱:</span>
                  <span class="font-medium">{{ selectedRenewal.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span>土地面積:</span>
                  <span class="font-medium">{{ selectedRenewal.area }} 平方公尺</span>
                </div>
                <div class="flex justify-between">
                  <span>所有權人數:</span>
                  <span class="font-medium">{{ selectedRenewal.member_count }} 人</span>
                </div>
                <div class="flex justify-between">
                  <span>理事長:</span>
                  <span class="font-medium">{{ selectedRenewal.chairman_name }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Meeting Date Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">選擇會議日期 <span class="text-red-500">*</span></label>
            <div style="background-color: white !important;">
              <USelectMenu
                v-model="selectedMeeting"
                :options="meetingOptions"
                placeholder="請先選擇更新會"
                value-attribute="id"
                option-attribute="meeting_date"
                class="w-full"
                :loading="loadingMeetings"
                :disabled="!selectedRenewal || loadingMeetings"
                :ui="{
                  base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
                  background: 'bg-white dark:bg-white',
                  color: 'text-gray-900 dark:text-gray-900'
                }"
                :ui-menu="{ background: 'bg-white' }"
                @update:model-value="onMeetingChange"
              >
                <template #label>
                  <span v-if="selectedMeeting">{{ formatDate(selectedMeeting.meeting_date) }}</span>
                  <span v-else class="text-gray-400">{{ selectedRenewal ? '請選擇會議日期' : '請先選擇更新會' }}</span>
                </template>
                <template #option="{ option }">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ formatDate(option.meeting_date) }}</span>
                    <span class="text-xs text-gray-500">會議名稱: {{ option.meeting_name || '未命名' }}</span>
                  </div>
                </template>
              </USelectMenu>
            </div>

            <!-- Meeting Info Card -->
            <div v-if="selectedMeeting" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <h4 class="text-sm font-semibold text-gray-700 mb-2">會議資訊</h4>
              <div class="text-sm text-gray-600 space-y-1">
                <div class="flex justify-between">
                  <span>會議名稱:</span>
                  <span class="font-medium">{{ selectedMeeting.meeting_name || '未命名' }}</span>
                </div>
                <div class="flex justify-between">
                  <span>會議日期:</span>
                  <span class="font-medium">{{ formatDate(selectedMeeting.meeting_date) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>會議地點:</span>
                  <span class="font-medium">{{ selectedMeeting.location || '未設定' }}</span>
                </div>
                <div class="flex justify-between">
                  <span>狀態:</span>
                  <span class="font-medium">{{ getMeetingStatusLabel(selectedMeeting.status) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Meeting Topics List -->
      <div v-if="selectedMeeting" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">會議議題列表</h3>
          <UButton
            color="green"
            icon="heroicons:plus"
            @click="createNewTopic"
          >
            新增議題
          </UButton>
        </div>

        <div v-if="loadingTopics" class="flex justify-center items-center py-12">
          <div class="flex items-center text-gray-500">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            載入中...
          </div>
        </div>

        <div v-else-if="meetingTopics.length === 0" class="text-center py-12">
          <Icon name="heroicons:document-text" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <p class="text-gray-500 mb-4">目前沒有議題</p>
          <UButton
            color="green"
            @click="createNewTopic"
          >
            新增第一個議題
          </UButton>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="topic in meetingTopics"
            :key="topic.id"
            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
            @click="viewTopic(topic)"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h4 class="font-medium text-gray-900 mb-1">{{ topic.title || '未命名議題' }}</h4>
                <p class="text-sm text-gray-600">{{ topic.description || '無描述' }}</p>
              </div>
              <div class="flex gap-2 ml-4">
                <UButton
                  color="blue"
                  size="xs"
                  @click.stop="editTopic(topic)"
                >
                  編輯
                </UButton>
                <UButton
                  color="red"
                  size="xs"
                  @click.stop="deleteTopic(topic)"
                >
                  刪除
                </UButton>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <Icon name="heroicons:information-circle" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500 text-lg">請選擇更新會及會議日期以查看議題</p>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'

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
const loadingTopics = ref(false)

const selectedRenewal = ref(null)
const selectedMeeting = ref(null)

const renewalOptions = ref([])
const meetingOptions = ref([])
const meetingTopics = ref([])

// Fetch urban renewals
const fetchRenewals = async () => {
  loadingRenewals.value = true
  try {
    const response = await getUrbanRenewals()
    if (response.success && response.data.status === 'success') {
      renewalOptions.value = response.data.data || []
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

  try {
    const response = await getMeetings({ urban_renewal_id: renewalId })
    if (response.success && response.data.status === 'success') {
      meetingOptions.value = response.data.data || []
    }
  } catch (error) {
    console.error('Failed to fetch meetings:', error)
    alert.error('錯誤', '無法載入會議列表')
  } finally {
    loadingMeetings.value = false
  }
}

// Fetch topics for selected meeting
const fetchTopics = async (meetingId) => {
  if (!meetingId) return

  loadingTopics.value = true
  try {
    const response = await get(`/meetings/${meetingId}/topics`)
    if (response.success && response.data.status === 'success') {
      meetingTopics.value = response.data.data || []
    }
  } catch (error) {
    console.error('Failed to fetch topics:', error)
    alert.error('錯誤', '無法載入議題列表')
  } finally {
    loadingTopics.value = false
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
    meetingTopics.value = []
  }
}

const onMeetingChange = (meeting) => {
  selectedMeeting.value = meeting
  if (meeting) {
    fetchTopics(meeting.id)
  } else {
    meetingTopics.value = []
  }
}

// Actions
const createNewTopic = () => {
  if (!selectedMeeting.value) return
  router.push(`/tables/meeting/${selectedMeeting.value.id}/voting-topics/create`)
}

const viewTopic = (topic) => {
  router.push(`/tables/meeting/${selectedMeeting.value.id}/voting-topics/${topic.id}`)
}

const editTopic = (topic) => {
  router.push(`/tables/meeting/${selectedMeeting.value.id}/voting-topics/${topic.id}/edit`)
}

const deleteTopic = async (topic) => {
  const result = await alert.confirm(
    '確認刪除',
    `確定要刪除議題「${topic.title}」嗎？`,
    {
      confirmButtonText: '確定刪除'
    }
  )

  if (!result.isConfirmed) return

  try {
    const response = await useApi().delete(`/meetings/${selectedMeeting.value.id}/topics/${topic.id}`)
    if (response.success && response.data.status === 'success') {
      alert.success('刪除成功', '議題已成功刪除')
      await fetchTopics(selectedMeeting.value.id)
    } else {
      alert.error('刪除失敗', response.data?.message || '刪除失敗')
    }
  } catch (error) {
    console.error('Failed to delete topic:', error)
    alert.error('刪除失敗', '無法刪除議題')
  }
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

// Initialize
onMounted(() => {
  fetchRenewals()
})
</script>