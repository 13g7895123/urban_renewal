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
              :value="renewalGroup"
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
                :value="meetingType"
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
              <UInput v-model="totalObservers" readonly class="bg-gray-50" />
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
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 w-24">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="observers.length === 0">
                      <td colspan="2" class="px-4 py-8 text-center text-gray-500">暫無列席者資料</td>
                    </tr>
                    <tr v-for="(observer, index) in observers" :key="index" class="border-b border-gray-100">
                      <td class="px-4 py-2">
                        <UInput v-model="observer.name" size="sm" placeholder="請輸入姓名" />
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
const { getMeeting, createMeeting, updateMeeting, exportMeetingNotice: exportMeetingNoticeApi } = useMeetings()
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

// Computed: 列席總人數（自動計算有名字的列席者）
const totalObservers = computed(() => {
  return observers.value.filter(observer => observer.name && observer.name.trim() !== '').length
})

// Basic info form fields
const renewalGroup = ref('')
const meetingName = ref('')
const meetingDateTime = ref('')
const meetingLocation = ref('')
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
      member_count: renewal.member_count,
      chairman_name: renewal.chairman_name,
      chairman_phone: renewal.chairman_phone
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

      console.log('[Basic Info] Meeting loaded:', meeting)
      console.log('[Basic Info] urban_renewal_name:', meeting.urban_renewal_name)
      console.log('[Basic Info] meeting_type:', meeting.meeting_type)

      // Initialize form fields with existing data
      // 使用 trim() 和空字串檢查確保正確處理
      renewalGroup.value = (meeting.urban_renewal_name && meeting.urban_renewal_name.trim() !== '')
                          ? meeting.urban_renewal_name
                          : (meeting.renewal_group || meeting.renewalGroup || '')

      meetingType.value = (meeting.meeting_type && meeting.meeting_type.trim() !== '')
                         ? meeting.meeting_type
                         : '會員大會'

      console.log('[Basic Info] renewalGroup set to:', renewalGroup.value)
      console.log('[Basic Info] meetingType set to:', meetingType.value)

      // Set selectedMeeting with normalized fields for display
      selectedMeeting.value = {
        ...meeting,
        renewalGroup: renewalGroup.value,
        meetingType: meetingType.value
      }

      // 組合 meeting_date 和 meeting_time 為 meeting_datetime (符合 datetime-local 格式)
      if (meeting.meeting_date && meeting.meeting_time) {
        // meeting_time 格式可能是 "HH:MM:SS" 或 "HH:MM",取前 5 個字元 "HH:MM"
        const timeStr = meeting.meeting_time.substring(0, 5)
        meetingDateTime.value = `${meeting.meeting_date}T${timeStr}`
      } else {
        meetingDateTime.value = ''
      }

      meetingLocation.value = meeting.meeting_location || meeting.meetingLocation || meeting.location || ''

      // Load attendance data
      attendees.value = meeting.attendee_count || 0
      baseAttendees.value = meeting.attendee_count || 0
      excludeOwnerFromCount.value = meeting.exclude_owner_from_count || false

      // Load ratio and area data
      landAreaRatioNumerator.value = meeting.quorum_land_area_numerator || 0
      landAreaRatioDenominator.value = meeting.quorum_land_area_denominator || 1
      totalLandArea.value = meeting.quorum_land_area || 0
      buildingAreaRatioNumerator.value = meeting.quorum_building_area_numerator || 0
      buildingAreaRatioDenominator.value = meeting.quorum_building_area_denominator || 1
      totalBuildingArea.value = meeting.quorum_building_area || 0
      peopleRatioNumerator.value = meeting.quorum_member_numerator || 0
      peopleRatioDenominator.value = meeting.quorum_member_denominator || 1
      totalPeopleCount.value = meeting.quorum_member_count || 0

      // Load observers (只載入 name 欄位)
      if (meeting.observers && Array.isArray(meeting.observers)) {
        observers.value = meeting.observers.map(o => ({
          name: o.name || ''
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
        // 組合日期和時間為 datetime-local 格式
        meetingDateTime.value = `${selectedMeeting.value.date}T${selectedMeeting.value.time}`
        meetingLocation.value = selectedMeeting.value.location || ''
        // totalObservers 會由 computed property 自動計算
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
    console.log('[Basic Info] Chairman name:', newValue.chairman_name)

    // 自動帶入開會地址
    meetingLocation.value = newValue.address || ''

    // 自動帶入所有權人數
    const memberCount = newValue.member_count || 0
    attendees.value = memberCount
    baseAttendees.value = memberCount

    // 自動帶入理事長姓名
    chairmanName.value = newValue.chairman_name || ''

    console.log('[Basic Info] Auto-filled data:', {
      address: meetingLocation.value,
      memberCount: memberCount,
      attendees: attendees.value,
      baseAttendees: baseAttendees.value,
      chairmanName: chairmanName.value
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
  // totalObservers 由 computed property 自動計算
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
    name: ''
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

const exportMeetingNotice = async () => {
  if (!selectedMeeting.value || !meetingId) {
    showError('匯出失敗', '找不到會議資料')
    return
  }

  isLoading.value = true
  console.log('[Basic Info] Exporting meeting notice for meeting:', meetingId)

  const response = await exportMeetingNoticeApi(meetingId)
  isLoading.value = false

  if (response.success) {
    showSuccess('匯出成功', '會議通知已成功匯出')
  } else {
    showError('匯出失敗', response.error?.message || '無法匯出會議通知')
  }
}

// Fill test data function
const fillMeetingTestData = () => {
  // Select first urban renewal if available
  // (理事長姓名、開會地址、所有權人數會由 watch 自動帶入)
  if (urbanRenewalOptions.value.length > 0) {
    selectedUrbanRenewal.value = urbanRenewalOptions.value[0]
  }
  meetingName.value = '114年度第一屆第3次會員大會'
  meetingDateTime.value = '2025-12-15T14:00'
  // totalObservers 由 computed property 自動計算（根據 observers 陣列）
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
    { name: '張三' },
    { name: '李四' },
    { name: '王五' }
  ]

  noticeDocNumber.value = '北市都更'
  noticeWordNumber.value = '字第'
  noticeMidNumber.value = '1140001'
  noticeEndNumber.value = '號'
  // chairmanName 由 watch 自動帶入,不在此處設定
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
    // 拆分 meeting_datetime 為 meeting_date 和 meeting_time
    let meetingDate = ''
    let meetingTime = ''
    if (meetingDateTime.value) {
      const [date, time] = meetingDateTime.value.split('T')
      meetingDate = date || ''
      meetingTime = time || '00:00'
    }

    const formData = {
      meeting_date: meetingDate,
      meeting_time: meetingTime,
      meeting_location: meetingLocation.value,
      attendee_count: parseInt(attendees.value) || 0,
      calculated_total_count: parseInt(totalCountedAttendees.value) || 0,
      exclude_owner_from_count: excludeOwnerFromCount.value,
      observer_count: parseInt(totalObservers.value) || 0,
      quorum_land_area_numerator: parseInt(landAreaRatioNumerator.value) || 0,
      quorum_land_area_denominator: parseInt(landAreaRatioDenominator.value) || 1,
      quorum_land_area: parseFloat(totalLandArea.value) || 0,
      quorum_building_area_numerator: parseInt(buildingAreaRatioNumerator.value) || 0,
      quorum_building_area_denominator: parseInt(buildingAreaRatioDenominator.value) || 1,
      quorum_building_area: parseFloat(totalBuildingArea.value) || 0,
      quorum_member_numerator: parseInt(peopleRatioNumerator.value) || 0,
      quorum_member_denominator: parseInt(peopleRatioDenominator.value) || 1,
      quorum_member_count: parseInt(totalPeopleCount.value) || 0,
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
      const createFormData = {
        ...formData,
        meeting_name: meetingName.value,
        meeting_type: meetingType.value,
        urban_renewal_id: selectedUrbanRenewal.value?.value || selectedUrbanRenewal.value?.id || 0
      }

      console.log('[Basic Info] Selected urban renewal:', selectedUrbanRenewal.value)
      console.log('[Basic Info] urban_renewal_id:', createFormData.urban_renewal_id)
      console.log('[Basic Info] Creating new meeting:', createFormData)
      response = await createMeeting(createFormData)
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