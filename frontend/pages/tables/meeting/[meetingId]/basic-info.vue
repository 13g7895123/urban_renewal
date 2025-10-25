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

      <UCard>
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
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 所屬更新會 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">所屬更新會</label>
              <UInput
                v-if="selectedMeeting"
                :value="selectedMeeting.renewalGroup || ''"
                readonly
                class="bg-gray-50"
              />
              <UInput
                v-else
                v-model="renewalGroup"
                placeholder="請輸入所屬更新會"
              />
            </div>

            <!-- 會議名稱 -->
            <div v-if="!selectedMeeting">
              <label class="block text-sm font-medium text-gray-700 mb-2">會議名稱</label>
              <UInput v-model="meetingName" placeholder="請輸入會議名稱" />
            </div>

            <!-- 會議類型 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議類型</label>
              <UInput 
                :value="selectedMeeting?.meetingType || '會員大會'" 
                readonly 
                class="bg-gray-50" 
              />
            </div>

            <!-- 會議日期時間 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議日期時間</label>
              <UInput v-model="meetingDateTime" />
            </div>

            <!-- 開會地點 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">開會地點</label>
              <UInput v-model="meetingLocation" />
            </div>

            <!-- 出席人數 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">出席人數</label>
              <UInput :value="selectedMeeting?.attendees" readonly class="bg-gray-50" />
            </div>

            <!-- 納入計算總人數 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">納入計算總人數</label>
              <UInput :value="selectedMeeting?.totalCountedAttendees" readonly class="bg-gray-50" />
            </div>

            <!-- 列席總人數 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">列席總人數</label>
              <UInput v-model="totalObservers" type="number" />
            </div>
          </div>

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
              <UButton variant="outline" @click="goBack">
                回上一頁
              </UButton>
              <UButton color="green" @click="saveBasicInfo">
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
  layout: false
})

const route = useRoute()
const meetingId = route.params.meetingId

// Get meeting data (this would typically come from an API)
const selectedMeeting = ref(null)

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

onMounted(() => {
  if (meetingId === 'new') {
    // Creating new meeting
    selectedMeeting.value = null
    resetFormFields()
  } else {
    // Load existing meeting data
    selectedMeeting.value = meetings.find(m => m.id === meetingId)
    console.log('[Basic Info] Loading meeting:', meetingId)
    console.log('[Basic Info] Selected meeting:', selectedMeeting.value)
    
    if (selectedMeeting.value) {
      // Initialize form fields with existing data
      meetingDateTime.value = `${selectedMeeting.value.date} ${selectedMeeting.value.time}`
      meetingLocation.value = selectedMeeting.value.location || ''
      totalObservers.value = selectedMeeting.value.totalObservers || 0
      
      console.log('[Basic Info] Form initialized:', {
        renewalGroup: selectedMeeting.value.renewalGroup,
        meetingType: selectedMeeting.value.meetingType,
        meetingDateTime: meetingDateTime.value,
        meetingLocation: meetingLocation.value
      })
    }
  }
})

const resetFormFields = () => {
  renewalGroup.value = ''
  meetingName.value = ''
  meetingDateTime.value = ''
  meetingLocation.value = ''
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
  renewalGroup.value = '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會'
  meetingName.value = '114年度第一屆第3次會員大會'
  meetingDateTime.value = '2025年12月15日 下午2:00:00'
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
const saveBasicInfo = () => {
  if (selectedMeeting.value) {
    // Editing existing meeting
    console.log('Updating existing meeting:', selectedMeeting.value)
    console.log('Form data:', {
      meetingDateTime: meetingDateTime.value,
      meetingLocation: meetingLocation.value,
      totalObservers: totalObservers.value,
      observers: observers.value,
      descriptions: descriptions.value
    })
    // TODO: Implement update functionality
  } else {
    // Creating new meeting
    console.log('Creating new meeting')
    console.log('Form data:', {
      renewalGroup: renewalGroup.value,
      meetingName: meetingName.value,
      meetingDateTime: meetingDateTime.value,
      meetingLocation: meetingLocation.value,
      totalObservers: totalObservers.value,
      landAreaRatioNumerator: landAreaRatioNumerator.value,
      landAreaRatioDenominator: landAreaRatioDenominator.value,
      totalLandArea: totalLandArea.value,
      buildingAreaRatioNumerator: buildingAreaRatioNumerator.value,
      buildingAreaRatioDenominator: buildingAreaRatioDenominator.value,
      totalBuildingArea: totalBuildingArea.value,
      peopleRatioNumerator: peopleRatioNumerator.value,
      peopleRatioDenominator: peopleRatioDenominator.value,
      totalPeopleCount: totalPeopleCount.value,
      observers: observers.value,
      noticeDocNumber: noticeDocNumber.value,
      noticeWordNumber: noticeWordNumber.value,
      noticeMidNumber: noticeMidNumber.value,
      noticeEndNumber: noticeEndNumber.value,
      chairmanName: chairmanName.value,
      contactName: contactName.value,
      contactPhone: contactPhone.value,
      attachments: attachments.value,
      descriptions: descriptions.value
    })
    // TODO: Implement create functionality and add to meetings list
  }

  // Navigate back to meeting list after save
  navigateTo('/tables/meeting')
}
</script>