<template>
  <NuxtLayout name="main">
    <template #title>投票議題基本資料</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:chart-bar" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">{{ selectedTopic ? '投票議題基本資料' : '新增投票議題' }}</h2>
        </div>
      </div>

      <UCard>
        <div class="space-y-6 p-6">
          <!-- Test Data Button -->
          <div v-if="!selectedTopic" class="flex justify-end">
            <UButton
              color="blue"
              size="sm"
              @click="fillVotingTopicTestData"
            >
              <Icon name="heroicons:beaker" class="w-4 h-4 mr-1" />
              填入測試資料
            </UButton>
          </div>

          <!-- Basic Topic Info -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 所屬會議 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">所屬會議</label>
              <UInput :value="meetingInfo?.name" readonly class="bg-gray-50" />
            </div>

            <!-- 會議日期時間 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議日期時間</label>
              <UInput :value="meetingInfo?.dateTime" readonly class="bg-gray-50" />
            </div>

            <!-- 所屬更新會 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">所屬更新會</label>
              <UInput :value="meetingInfo?.renewalGroup" readonly class="bg-gray-50" />
            </div>
          </div>

          <!-- 議題名稱 -->
          <div v-if="!selectedTopic">
            <label class="block text-sm font-medium text-gray-700 mb-2">議題名稱</label>
            <UInput v-model="topicName" placeholder="請輸入議題名稱" />
          </div>

          <!-- 匿名與否 -->
          <div class="space-y-3">
            <label class="block text-sm font-medium text-gray-700">匿名與否</label>
            <div class="flex gap-6">
              <div class="flex items-center">
                <input
                  type="radio"
                  id="not-anonymous"
                  v-model="isAnonymous"
                  :value="false"
                  class="w-4 h-4 text-blue-600"
                >
                <label for="not-anonymous" class="ml-2 text-sm text-gray-700">不匿名</label>
              </div>
              <div class="flex items-center">
                <input
                  type="radio"
                  id="anonymous"
                  v-model="isAnonymous"
                  :value="true"
                  class="w-4 h-4 text-blue-600"
                >
                <label for="anonymous" class="ml-2 text-sm text-gray-700">匿名</label>
              </div>
            </div>
          </div>

          <!-- 所有權人 Group -->
          <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-lg font-medium text-gray-900">所有權人</h4>
              <UButton color="blue" size="sm" @click="importPropertyOwners">
                <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />
                匯入所有權人
              </UButton>
            </div>

            <div class="border border-gray-200 rounded-lg">
              <div class="max-h-64 overflow-y-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">編號</th>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">選項名稱(所有權人)</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">置頂</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="propertyOwners.length === 0">
                      <td colspan="4" class="px-4 py-8 text-center text-gray-500">請點擊"匯入所有權人"或手動新增</td>
                    </tr>
                    <tr v-for="(owner, index) in propertyOwners" :key="index" class="border-b border-gray-100">
                      <td class="px-4 py-2 text-center">{{ index + 1 }}</td>
                      <td class="px-4 py-2">
                        <UInput v-model="owner.name" size="sm" placeholder="所有權人姓名" />
                      </td>
                      <td class="px-4 py-2 text-center">
                        <UCheckbox v-model="owner.isPinned" />
                      </td>
                      <td class="px-4 py-2 text-center">
                        <div class="flex gap-1 justify-center">
                          <UButton color="green" size="xs" @click="addPropertyOwner(index)">新增選項</UButton>
                          <UButton color="red" size="xs" @click="removePropertyOwner(index)">刪除</UButton>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Voting Settings -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">最大有效圈選數</label>
              <UInput v-model="maxSelections" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">正取數量</label>
              <UInput v-model="acceptedCount" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">備取數量</label>
              <UInput v-model="alternateCount" type="number" />
            </div>
          </div>

          <!-- Pass Conditions -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Land Area -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件土地面積比例分子</label>
              <UInput v-model="landAreaRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件土地面積比例分母</label>
              <UInput v-model="landAreaRatioDenominator" type="number" />
            </div>

            <!-- Building Area -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件建物面積比例分子</label>
              <UInput v-model="buildingAreaRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件建物面積比例分母</label>
              <UInput v-model="buildingAreaRatioDenominator" type="number" />
            </div>

            <!-- People Count -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件人數比例分子</label>
              <UInput v-model="peopleRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件人數比例分母</label>
              <UInput v-model="peopleRatioDenominator" type="number" />
            </div>
          </div>

          <!-- 備註 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">備註</label>
            <UTextarea v-model="remarks" rows="3" />
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between pt-6 border-t border-gray-200">
            <div class="flex gap-3">
              <UButton v-if="selectedTopic" color="green" @click="exportVotingBallot">
                <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
                匯出投票單
              </UButton>
            </div>
            <div class="flex gap-3">
              <UButton variant="outline" @click="goBack" :disabled="isSaving">
                回上一頁
              </UButton>
              <UButton color="green" @click="saveBasicInfo" :disabled="isSaving" :loading="isSaving">
                <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
                儲存
              </UButton>
            </div>
          </div>
        </div>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, navigateTo } from '#app'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const route = useRoute()
const meetingId = route.params.meetingId

// API composables
const { getMeeting } = useMeetings()
const { createVotingTopic } = useVotingTopics()
const { showSuccess, showError } = useSweetAlert()

// Loading states
const isLoading = ref(false)
const isSaving = ref(false)

// This is for creating a new topic, so no existing topic selected
const selectedTopic = ref(null)
const meetingInfo = ref(null)

// Form fields
const topicName = ref('')
const isAnonymous = ref(false)
const propertyOwners = ref([])
const maxSelections = ref(1)
const acceptedCount = ref(1)
const alternateCount = ref(0)
const landAreaRatioNumerator = ref(0)
const landAreaRatioDenominator = ref(0)
const buildingAreaRatioNumerator = ref(0)
const buildingAreaRatioDenominator = ref(0)
const peopleRatioNumerator = ref(0)
const peopleRatioDenominator = ref(0)
const remarks = ref('')

// Mock data for testing
const mockTopics = [
  {
    id: '1',
    name: '理事長選舉',
    meetingName: '114年度第一屆第1次會員大會',
    maxSelections: 1,
    acceptedCount: 1,
    alternateCount: 0,
    isAnonymous: false,
    landAreaRatioNumerator: 3,
    landAreaRatioDenominator: 4,
    buildingAreaRatioNumerator: 2,
    buildingAreaRatioDenominator: 3,
    peopleRatioNumerator: 50,
    peopleRatioDenominator: 100,
    remarks: '理事長選舉相關說明'
  },
  {
    id: '2',
    name: '更新計畫同意案',
    meetingName: '114年度第一屆第1次會員大會',
    maxSelections: 1,
    acceptedCount: 1,
    alternateCount: 0,
    isAnonymous: false,
    landAreaRatioNumerator: 2,
    landAreaRatioDenominator: 3,
    buildingAreaRatioNumerator: 3,
    buildingAreaRatioDenominator: 4,
    peopleRatioNumerator: 60,
    peopleRatioDenominator: 100,
    remarks: '更新計畫同意案相關說明'
  }
]

const mockMeeting = {
  id: '1',
  name: '114年度第一屆第1次會員大會',
  dateTime: '2025年3月15日 下午2:00:00',
  renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會'
}

onMounted(async () => {
  await loadMeetingInfo()
  // Creating new topic - reset form fields
  selectedTopic.value = null
  resetFormFields()
})

// Load meeting info
const loadMeetingInfo = async () => {
  isLoading.value = true
  console.log('[New Voting Topic] Loading meeting info:', meetingId)

  const response = await getMeeting(meetingId)
  isLoading.value = false

  if (response.success && response.data) {
    const meeting = response.data.data || response.data
    meetingInfo.value = {
      id: meeting.id,
      name: meeting.meeting_name || meeting.name || '',
      dateTime: `${meeting.meeting_date || meeting.date || ''} ${meeting.meeting_time || meeting.time || ''}`,
      renewalGroup: meeting.renewal_group || meeting.renewalGroup || ''
    }
    console.log('[New Voting Topic] Meeting info loaded:', meetingInfo.value)
  } else {
    console.error('[New Voting Topic] Failed to load meeting info:', response.error)
    // Use fallback mock data
    meetingInfo.value = {
      id: meetingId,
      name: '114年度第一屆第1次會員大會',
      dateTime: '2025年3月15日 下午2:00:00',
      renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會'
    }
  }
}

const resetFormFields = () => {
  topicName.value = ''
  isAnonymous.value = false
  propertyOwners.value = []
  maxSelections.value = 1
  acceptedCount.value = 1
  alternateCount.value = 0
  landAreaRatioNumerator.value = 0
  landAreaRatioDenominator.value = 0
  buildingAreaRatioNumerator.value = 0
  buildingAreaRatioDenominator.value = 0
  peopleRatioNumerator.value = 0
  peopleRatioDenominator.value = 0
  remarks.value = ''
}

// Property owner management functions
const addPropertyOwner = (index) => {
  propertyOwners.value.splice(index + 1, 0, {
    name: '',
    isPinned: false
  })
}

const removePropertyOwner = (index) => {
  propertyOwners.value.splice(index, 1)
}

const importPropertyOwners = () => {
  // TODO: Implement import functionality
  console.log('Import property owners')

  // For demo, add some sample owners
  propertyOwners.value = [
    { name: '王五', isPinned: false },
    { name: '趙六', isPinned: false },
    { name: '錢七', isPinned: true }
  ]
}

// Export function
const exportVotingBallot = () => {
  console.log('Exporting voting ballot for topic:', selectedTopic.value)
  // TODO: Implement export voting ballot functionality
}

// Fill test data function
const fillVotingTopicTestData = () => {
  topicName.value = '監事會選舉'
  isAnonymous.value = false
  maxSelections.value = 2
  acceptedCount.value = 2
  alternateCount.value = 1
  landAreaRatioNumerator.value = 2
  landAreaRatioDenominator.value = 3
  buildingAreaRatioNumerator.value = 3
  buildingAreaRatioDenominator.value = 4
  peopleRatioNumerator.value = 55
  peopleRatioDenominator.value = 100
  remarks.value = '監事會選舉相關備註說明'

  propertyOwners.value = [
    { name: '測試所有權人1', isPinned: false },
    { name: '測試所有權人2', isPinned: true },
    { name: '測試所有權人3', isPinned: false }
  ]
}

// Navigation functions
const goBack = () => {
  navigateTo(`/tables/meeting/${meetingId}/voting-topics`)
}

// Save function
const saveBasicInfo = async () => {
  // Validation
  if (!topicName.value.trim()) {
    showError('資料不完整', '請輸入議題名稱')
    return
  }

  isSaving.value = true

  const formData = {
    meeting_id: meetingId,
    topic_name: topicName.value,
    is_anonymous: isAnonymous.value,
    property_owners: propertyOwners.value.map(owner => ({
      name: owner.name,
      is_pinned: owner.isPinned
    })),
    max_selections: parseInt(maxSelections.value) || 1,
    accepted_count: parseInt(acceptedCount.value) || 1,
    alternate_count: parseInt(alternateCount.value) || 0,
    land_area_ratio_numerator: parseInt(landAreaRatioNumerator.value) || 0,
    land_area_ratio_denominator: parseInt(landAreaRatioDenominator.value) || 0,
    building_area_ratio_numerator: parseInt(buildingAreaRatioNumerator.value) || 0,
    building_area_ratio_denominator: parseInt(buildingAreaRatioDenominator.value) || 0,
    people_ratio_numerator: parseInt(peopleRatioNumerator.value) || 0,
    people_ratio_denominator: parseInt(peopleRatioDenominator.value) || 0,
    remarks: remarks.value
  }

  console.log('[New Voting Topic] Creating new topic:', formData)

  const response = await createVotingTopic(formData)
  isSaving.value = false

  if (response.success) {
    showSuccess('建立成功', '投票議題已成功建立')
    console.log('[New Voting Topic] Topic created successfully:', response.data)

    // Navigate back to voting topics list after save
    setTimeout(() => {
      navigateTo(`/tables/meeting/${meetingId}/voting-topics`)
    }, 1500)
  } else {
    console.error('[New Voting Topic] Failed to create topic:', response.error)
    showError('建立失敗', response.error?.message || '無法建立投票議題')
  }
}
</script>