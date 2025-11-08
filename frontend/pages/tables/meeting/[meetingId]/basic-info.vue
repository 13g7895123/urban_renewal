<template>
  <NuxtLayout name="main">
    <template #title>會議基本資料</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">{{ selectedMeeting ? '會議基本資料' : '新增會議' }}</h2>
        </div>
      </div>

      <!-- Loading overlay -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="text-center">
          <Icon name="heroicons:arrow-path" class="w-12 h-12 text-green-500 animate-spin mx-auto mb-4" />
          <p class="text-gray-600">載入中...</p>
        </div>
      </div>

      <UCard v-else>
        <div class="space-y-6 p-6">
          <!-- Test Data Button -->
          <div v-if="!selectedMeeting" class="flex justify-end">
            <UButton
              color="blue"
              size="sm"
              @click="fillMeetingTestData"
            >
              <Icon name="heroicons:beaker" class="w-4 h-4 mr-1" />
              填入測試資料
            </UButton>
          </div>

          <!-- Basic Meeting Info -->
          <!-- 所屬更新會 - 單一列 -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">所屬更新會</label>
            <UInput
              v-if="selectedMeeting"
              :value="selectedMeeting.renewalGroup || renewalGroup"
              readonly
              class="bg-gray-50"
            />
            <USelectMenu
              v-else
              v-model="selectedUrbanRenewal"
              :options="urbanRenewalOptions"
              placeholder="請選擇所屬更新會"
              option-attribute="label"
              class="w-full"
            />
          </div>

          <!-- 會議類型與會議日期時間 - 兩欄 -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- 會議類型 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議類型</label>
              <UInput
                v-if="selectedMeeting"
                :value="selectedMeeting?.meetingType || '會員大會'"
                readonly
                class="bg-gray-50"
              />
              <USelectMenu
                v-else
                v-model="meetingType"
                :options="meetingTypeOptions"
                placeholder="請選擇會議類型"
                class="w-full"
              />
            </div>

            <!-- 會議日期時間 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議日期時間</label>
              <input
                v-model="meetingDateTime"
                type="datetime-local"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
              />
            </div>
          </div>

          <!-- 會議名稱 - 單一列 -->
          <div v-if="!selectedMeeting" class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">會議名稱</label>
            <UInput v-model="meetingName" placeholder="請輸入會議名稱" />
          </div>

          <!-- 開會地址 - 單一列 -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">開會地址</label>
            <UInput v-model="meetingLocation" placeholder="選擇更新會後自動帶入" />
          </div>

          <!-- 出席人數、納入計算總人數、列席總人數 -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- 出席人數 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">出席人數</label>
              <UInput v-model="attendees" readonly class="bg-gray-50" />
            </div>

            <!-- 納入計算總人數 (readonly) with checkbox -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">納入計算總人數</label>
              <div class="space-y-2">
                <UInput v-model="totalCountedAttendees" readonly class="bg-gray-50" />
                <div class="flex items-center">
                  <input
                    v-model="excludeOwnerFromCount"
                    type="checkbox"
                    id="excludeOwner"
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                  />
                  <label for="excludeOwner" class="ml-2 text-sm text-gray-600">
                    排除所有權人不列計
                  </label>
                </div>
              </div>
            </div>

            <!-- 列席總人數 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">列席總人數</label>
              <UInput v-model="totalObservers" type="number" />
            </div>
          </div>

          <!-- 其他欄位 -->
          <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> -->

          <!-- Meeting Ratios and Areas -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Land Area Ratio -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會土地面積比例分子</label>
              <UInput v-model="landAreaRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會土地面積比例分母</label>
              <UInput v-model="landAreaRatioDenominator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會土地面積(平方公尺)</label>
              <UInput v-model="totalLandArea" type="number" />
            </div>

            <!-- Building Area Ratio -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會建物面積比例分子</label>
              <UInput v-model="buildingAreaRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會建物面積比例分母</label>
              <UInput v-model="buildingAreaRatioDenominator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會建物面積(平方公尺)</label>
              <UInput v-model="totalBuildingArea" type="number" />
            </div>

            <!-- People Count Ratio -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會人數比例分子</label>
              <UInput v-model="peopleRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會人數比例分母</label>
              <UInput v-model="peopleRatioDenominator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">成會人數</label>
              <UInput v-model="totalPeopleCount" type="number" />
            </div>
          </div>

          <!-- 列席者 Table -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <label class="block text-sm font-medium text-gray-700">列席者</label>
              <UButton color="green" size="sm" @click="addObserver">
                <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
                新增列席者
              </UButton>
            </div>
            <div class="border border-gray-200 rounded-lg">
              <div class="max-h-48 overflow-y-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">姓名</th>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">職稱</th>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">聯絡電話</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="observers.length === 0">
                      <td colspan="4" class="px-4 py-8 text-center text-gray-500">暫無列席者資料</td>
                    </tr>
                    <tr v-for="(observer, index) in observers" :key="index" class="border-b border-gray-100">
                      <td class="px-4 py-2">
                        <UInput v-model="observer.name" size="sm" />
                      </td>
                      <td class="px-4 py-2">
                        <UInput v-model="observer.title" size="sm" />
                      </td>
                      <td class="px-4 py-2">
                        <UInput v-model="observer.phone" size="sm" />
                      </td>
                      <td class="px-4 py-2 text-center">
                        <UButton color="red" size="xs" @click="removeObserver(index)">刪除</UButton>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 會議通知單資訊 -->
          <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
            <h4 class="text-lg font-medium text-gray-900 mb-4">會議通知單資訊</h4>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">發文字號</label>
                <UInput v-model="noticeDocNumber" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">字第</label>
                <UInput v-model="noticeWordNumber" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">oooooo</label>
                <UInput v-model="noticeMidNumber" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">號</label>
                <UInput v-model="noticeEndNumber" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">理事長姓名</label>
                <UInput v-model="chairmanName" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">聯絡人姓名</label>
                <UInput v-model="contactName" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">聯絡人電話</label>
                <UInput v-model="contactPhone" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">附件</label>
                <UInput v-model="attachments" />
              </div>
            </div>

            <!-- 發文說明 -->
            <div>
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">發文說明</label>
                <UButton color="blue" size="sm" @click="addDescription">
                  <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
                  新增說明
                </UButton>
              </div>
              <div class="space-y-2">
                <div v-for="(desc, index) in descriptions" :key="index" class="flex items-center gap-3">
                  <span class="text-sm font-medium text-gray-600 w-8">{{ getChineseNumber(index + 1) }}、</span>
                  <UInput v-model="desc.content" class="flex-1" />
                  <UButton color="red" size="xs" @click="removeDescription(index)">刪除</UButton>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between pt-6 border-t border-gray-200">
            <div class="flex gap-3">
              <UButton v-if="selectedMeeting" color="green" @click="exportSignatureBook">
                <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
                匯出簽到冊
              </UButton>
              <UButton v-if="selectedMeeting" color="blue" @click="exportMeetingNotice">
                <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
                匯出會議通知
              </UButton>
            </div>
            <div class="flex gap-3">
              <UButton variant="outline" @click="goBack" :disabled="isLoading">
                回上一頁
              </UButton>
              <UButton color="green" @click="saveBasicInfo" :disabled="isLoading" :loading="isLoading">
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
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, navigateTo } from '#app'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const route = useRoute()
const meetingId = route.params.meetingId

// API composables
const { getMeeting, createMeeting, updateMeeting } = useMeetings()
const { getUrbanRenewals } = useUrbanRenewal()
const { showSuccess, showError } = useSweetAlert()

// Loading state
const isLoading = ref(false)

// Get meeting data (this would typically come from an API)
const selectedMeeting = ref(null)

// Urban renewal options
const urbanRenewalOptions = ref([])
const selectedUrbanRenewal = ref(null)

// Meeting type options
const meetingTypeOptions = ref(['會員大會', '理監事會', '公聽會'])
const meetingType = ref('會員大會')

// Attendance fields
const attendees = ref(0)
const baseAttendees = ref(0) // 原始所有權人數
const excludeOwnerFromCount = ref(false)

// Computed: 納入計算總人數（根據勾選狀態）
const totalCountedAttendees = computed(() => {
  if (excludeOwnerFromCount.value) {
    return Math.max(0, baseAttendees.value - 1)
  }
  return baseAttendees.value
})

// Basic info form fields
const renewalGroup = ref('')
const meetingName = ref('')
const meetingDateTime = ref('')
const meetingLocation = ref('')
const totalObservers = ref(0)
const landAreaRatioNumerator = ref(0)
const landAreaRatioDenominator = ref(0)
const totalLandArea = ref(0)
const buildingAreaRatioNumerator = ref(0)
const buildingAreaRatioDenominator = ref(0)
const totalBuildingArea = ref(0)
const peopleRatioNumerator = ref(0)
const peopleRatioDenominator = ref(0)
const totalPeopleCount = ref(0)

// Observer table
const observers = ref([])

// Meeting notice fields
const noticeDocNumber = ref('')
const noticeWordNumber = ref('')
const noticeMidNumber = ref('')
const noticeEndNumber = ref('')
const chairmanName = ref('')
const contactName = ref('')
const contactPhone = ref('')
const attachments = ref('')
const descriptions = ref([])

// Mock meetings data (this would typically come from an API)
const meetings = [
  {
    id: '1',
    name: '114年度第一屆第1次會員大會',
    renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
    meetingType: '會員大會',
    date: '2025年3月15日',
    time: '下午2:00:00',
    attendees: 73,
    totalCountedAttendees: 72,
    totalObservers: 0,
    votingTopicCount: 5,
    location: '台北市南港區玉成街1號'
  },
  {
    id: '2',
    name: '114年度第一屆第2次會員大會',
    renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
    meetingType: '會員大會',
    date: '2025年8月9日',
    time: '下午2:00:00',
    attendees: 74,
    totalCountedAttendees: 74,
    totalObservers: 0,
    votingTopicCount: 3,
    location: '台北市南港區玉成街2號'
  }
]

onMounted(async () => {
  // Load urban renewal options (request all items without pagination)
  const urbanRenewalResponse = await getUrbanRenewals({ per_page: 9999 })
  console.log('[Basic Info] API Response:', urbanRenewalResponse)

  if (urbanRenewalResponse.success && urbanRenewalResponse.data) {
    // Backend returns: { status, message, data: [...], pagination }
    const renewals = urbanRenewalResponse.data.data || urbanRenewalResponse.data
    console.log('[Basic Info] Renewals array:', renewals)
    console.log('[Basic Info] First renewal:', renewals[0])

    urbanRenewalOptions.value = renewals.map(renewal => ({
      label: renewal.name,
      value: renewal.id,
      name: renewal.name,
      address: renewal.address,
      member_count: renewal.member_count
    }))
    console.log('[Basic Info] Urban renewals loaded:', urbanRenewalOptions.value)
  }

  if (meetingId === 'new') {
    // Creating new meeting
    selectedMeeting.value = null
    resetFormFields()
  } else {
    // Load existing meeting data from API
    isLoading.value = true
    console.log('[Basic Info] Loading meeting:', meetingId)

    const response = await getMeeting(meetingId)
    isLoading.value = false

    if (response.success && response.data) {
      const meeting = response.data.data || response.data
      selectedMeeting.value = meeting

      console.log('[Basic Info] Meeting loaded:', meeting)

      // Initialize form fields with existing data
      renewalGroup.value = meeting.renewal_group || meeting.renewalGroup || ''
      meetingDateTime.value = meeting.meeting_datetime || meeting.meetingDateTime || ''
      meetingLocation.value = meeting.meeting_location || meeting.meetingLocation || meeting.location || ''
      totalObservers.value = meeting.total_observers || meeting.totalObservers || 0

      // Load ratio and area data
      landAreaRatioNumerator.value = meeting.land_area_ratio_numerator || 0
      landAreaRatioDenominator.value = meeting.land_area_ratio_denominator || 0
      totalLandArea.value = meeting.total_land_area || 0
      buildingAreaRatioNumerator.value = meeting.building_area_ratio_numerator || 0
      buildingAreaRatioDenominator.value = meeting.building_area_ratio_denominator || 0
      totalBuildingArea.value = meeting.total_building_area || 0
      peopleRatioNumerator.value = meeting.people_ratio_numerator || 0
      peopleRatioDenominator.value = meeting.people_ratio_denominator || 0
      totalPeopleCount.value = meeting.total_people_count || 0

      // Load observers
      if (meeting.observers && Array.isArray(meeting.observers)) {
        observers.value = meeting.observers.map(o => ({
          name: o.name || '',
          title: o.title || '',
          phone: o.phone || ''
        }))
      }

      // Load notice data
      noticeDocNumber.value = meeting.notice_doc_number || ''
      noticeWordNumber.value = meeting.notice_word_number || ''
      noticeMidNumber.value = meeting.notice_mid_number || ''
      noticeEndNumber.value = meeting.notice_end_number || ''
      chairmanName.value = meeting.chairman_name || ''
      contactName.value = meeting.contact_name || ''
      contactPhone.value = meeting.contact_phone || ''
      attachments.value = meeting.attachments || ''

      // Load descriptions
      if (meeting.descriptions && Array.isArray(meeting.descriptions)) {
        descriptions.value = meeting.descriptions.map(d => ({
          content: d.content || d
        }))
      }

      console.log('[Basic Info] Form initialized successfully')
    } else {
      console.error('[Basic Info] Failed to load meeting:', response.error)
      showError('載入會議資料失敗', response.error?.message || '無法載入會議資料')
      // Use fallback mock data if API fails
      selectedMeeting.value = meetings.find(m => m.id === meetingId)
      if (selectedMeeting.value) {
        meetingDateTime.value = `${selectedMeeting.value.date} ${selectedMeeting.value.time}`
        meetingLocation.value = selectedMeeting.value.location || ''
        totalObservers.value = selectedMeeting.value.totalObservers || 0
      }
    }
  }
})

// Watch for urban renewal selection changes
watch(selectedUrbanRenewal, (newValue) => {
  if (newValue && !selectedMeeting.value) {
    console.log('[Basic Info] Urban renewal selected:', newValue)
    console.log('[Basic Info] Address:', newValue.address)
    console.log('[Basic Info] Member count:', newValue.member_count)

    // 自動帶入開會地址
    meetingLocation.value = newValue.address || ''

    // 自動帶入所有權人數
    const memberCount = newValue.member_count || 0
    attendees.value = memberCount
    baseAttendees.value = memberCount

    console.log('[Basic Info] Auto-filled data:', {
      address: meetingLocation.value,
      memberCount: memberCount,
      attendees: attendees.value,
      baseAttendees: baseAttendees.value
    })
  }
})

const resetFormFields = () => {
  renewalGroup.value = ''
  meetingName.value = ''
  meetingType.value = '會員大會'
  meetingDateTime.value = ''
  meetingLocation.value = ''
  attendees.value = 0
  baseAttendees.value = 0
  excludeOwnerFromCount.value = false
  totalObservers.value = 0
  landAreaRatioNumerator.value = 0
  landAreaRatioDenominator.value = 0
  totalLandArea.value = 0
  buildingAreaRatioNumerator.value = 0
  buildingAreaRatioDenominator.value = 0
  totalBuildingArea.value = 0
  peopleRatioNumerator.value = 0
  peopleRatioDenominator.value = 0
  totalPeopleCount.value = 0
  observers.value = []
  noticeDocNumber.value = ''
  noticeWordNumber.value = ''
  noticeMidNumber.value = ''
  noticeEndNumber.value = ''
  chairmanName.value = ''
  contactName.value = ''
  contactPhone.value = ''
  attachments.value = ''
  descriptions.value = []
}

// Observer management functions
const addObserver = () => {
  observers.value.push({
    name: '',
    title: '',
    phone: ''
  })
}

const removeObserver = (index) => {
  observers.value.splice(index, 1)
}

// Description management functions
const addDescription = () => {
  descriptions.value.push({
    content: ''
  })
}

const removeDescription = (index) => {
  descriptions.value.splice(index, 1)
}

// Chinese number conversion helper
const getChineseNumber = (num) => {
  const chineseNumbers = ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十']
  if (num <= 10) {
    return chineseNumbers[num]
  }
  return num.toString()
}

// Export functions
const exportSignatureBook = () => {
  console.log('Exporting signature book for meeting:', selectedMeeting.value)
  // TODO: Implement export signature book functionality
}

const exportMeetingNotice = () => {
  console.log('Exporting meeting notice for meeting:', selectedMeeting.value)
  // TODO: Implement export meeting notice functionality
}

// Fill test data function
const fillMeetingTestData = () => {
  // Select first urban renewal if available
  if (urbanRenewalOptions.value.length > 0) {
    selectedUrbanRenewal.value = urbanRenewalOptions.value[0]
  }
  meetingName.value = '114年度第一屆第3次會員大會'
  meetingDateTime.value = '2025-12-15T14:00'
  meetingLocation.value = '台北市南港區玉成街1號'
  totalObservers.value = 5
  landAreaRatioNumerator.value = 3
  landAreaRatioDenominator.value = 4
  totalLandArea.value = 1500
  buildingAreaRatioNumerator.value = 2
  buildingAreaRatioDenominator.value = 3
  totalBuildingArea.value = 2000
  peopleRatioNumerator.value = 50
  peopleRatioDenominator.value = 100
  totalPeopleCount.value = 75

  observers.value = [
    { name: '張三', title: '市政府代表', phone: '02-1234-5678' },
    { name: '李四', title: '建築師', phone: '02-2345-6789' }
  ]

  noticeDocNumber.value = '北市都更'
  noticeWordNumber.value = '字第'
  noticeMidNumber.value = '1140001'
  noticeEndNumber.value = '號'
  chairmanName.value = '王理事長'
  contactName.value = '陳小明'
  contactPhone.value = '02-3456-7890'
  attachments.value = '會議議程、投票單'

  descriptions.value = [
    { content: '請各位會員準時出席會議' },
    { content: '會議當日請攜帶身分證明文件' },
    { content: '如有疑問請聯繫承辦人員' }
  ]
}

// Navigation functions
const goBack = () => {
  navigateTo('/tables/meeting')
}

// Save function
const saveBasicInfo = async () => {
  isLoading.value = true

  try {
    const formData = {
      meeting_datetime: meetingDateTime.value,
      meeting_location: meetingLocation.value,
      attendees: parseInt(attendees.value) || 0,
      total_counted_attendees: parseInt(totalCountedAttendees.value) || 0,
      exclude_owner_from_count: excludeOwnerFromCount.value,
      total_observers: parseInt(totalObservers.value) || 0,
      land_area_ratio_numerator: parseInt(landAreaRatioNumerator.value) || 0,
      land_area_ratio_denominator: parseInt(landAreaRatioDenominator.value) || 0,
      total_land_area: parseFloat(totalLandArea.value) || 0,
      building_area_ratio_numerator: parseInt(buildingAreaRatioNumerator.value) || 0,
      building_area_ratio_denominator: parseInt(buildingAreaRatioDenominator.value) || 0,
      total_building_area: parseFloat(totalBuildingArea.value) || 0,
      people_ratio_numerator: parseInt(peopleRatioNumerator.value) || 0,
      people_ratio_denominator: parseInt(peopleRatioDenominator.value) || 0,
      total_people_count: parseInt(totalPeopleCount.value) || 0,
      observers: observers.value,
      notice_doc_number: noticeDocNumber.value,
      notice_word_number: noticeWordNumber.value,
      notice_mid_number: noticeMidNumber.value,
      notice_end_number: noticeEndNumber.value,
      chairman_name: chairmanName.value,
      contact_name: contactName.value,
      contact_phone: contactPhone.value,
      attachments: attachments.value,
      descriptions: descriptions.value.map(d => d.content)
    }

    let response

    if (selectedMeeting.value) {
      // Editing existing meeting
      console.log('[Basic Info] Updating meeting:', meetingId, formData)
      response = await updateMeeting(meetingId, formData)
    } else {
      // Creating new meeting
      if (selectedUrbanRenewal.value) {
        // selectedUrbanRenewal is now the full object: { label, value, name, address, member_count }
        formData.urban_renewal_id = selectedUrbanRenewal.value.value || selectedUrbanRenewal.value.id
        formData.renewal_group = selectedUrbanRenewal.value.name || selectedUrbanRenewal.value.label
        console.log('[Basic Info] Selected urban renewal:', selectedUrbanRenewal.value)
        console.log('[Basic Info] urban_renewal_id:', formData.urban_renewal_id)
        console.log('[Basic Info] renewal_group:', formData.renewal_group)
      }
      formData.meeting_name = meetingName.value
      formData.meeting_type = meetingType.value
      console.log('[Basic Info] Creating new meeting:', formData)
      response = await createMeeting(formData)
    }

    isLoading.value = false

    if (response.success) {
      const action = selectedMeeting.value ? '更新' : '建立'
      showSuccess(`${action}成功`, `會議資料已成功${action}`)
      console.log(`[Basic Info] Meeting ${action} successfully:`, response.data)

      // Navigate back to meeting list after save
      setTimeout(() => {
        navigateTo('/tables/meeting')
      }, 1500)
    } else {
      const action = selectedMeeting.value ? '更新' : '建立'
      console.error(`[Basic Info] Failed to ${action} meeting:`, response.error)
      showError(`${action}失敗`, response.error?.message || `無法${action}會議資料`)
    }
  } catch (error) {
    isLoading.value = false
    const action = selectedMeeting.value ? '更新' : '建立'
    console.error(`[Basic Info] Error ${action} meeting:`, error)
    showError(`${action}失敗`, error.message || `${action}會議資料時發生錯誤`)
  }
}
</script>