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

    <!-- Basic Info Modal -->
    <UModal v-model="showBasicInfoModal" :ui="{ width: 'max-w-6xl' }">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ selectedMeeting ? '會議基本資料' : '新增會議' }}
            </h3>
            <div class="flex items-center gap-2">
              <UButton
                v-if="!selectedMeeting"
                color="blue"
                size="sm"
                @click="fillMeetingTestData"
              >
                <Icon name="heroicons:beaker" class="w-4 h-4 mr-1" />
                填入測試資料
              </UButton>
              <UButton
                variant="ghost"
                icon="i-heroicons-x-mark-20-solid"
                @click="showBasicInfoModal = false"
              />
            </div>
          </div>
        </template>

        <div class="space-y-6 p-6">
          <!-- Basic Meeting Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 所屬更新會 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">所屬更新會</label>
              <UInput
                v-if="selectedMeeting"
                :value="selectedMeeting?.renewalGroup"
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

            <!-- 會議類型 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議類型</label>
              <UInput value="會員大會" readonly class="bg-gray-50" />
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
              <UButton variant="outline" @click="showBasicInfoModal = false">
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
    </UModal>
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

// Modal state
const showBasicInfoModal = ref(false)
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

const meetings = ref([
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
  selectedMeeting.value = null // Clear selected meeting for new creation

  // Reset all form fields to empty/default values
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

  // Show modal
  showBasicInfoModal.value = true
}

const deleteMeetings = () => {
  console.log('Deleting meetings:', selectedMeetings.value)
  // TODO: Implement delete functionality
}

const showBasicInfo = (meeting) => {
  console.log('Showing basic info for:', meeting)
  selectedMeeting.value = meeting

  // Initialize form fields with existing data or defaults
  meetingDateTime.value = `${meeting.date} ${meeting.time}`
  meetingLocation.value = meeting.location || ''
  totalObservers.value = meeting.totalObservers || 0

  // Show modal
  showBasicInfoModal.value = true
}

const showVotingTopics = (meeting) => {
  console.log('Showing voting topics for:', meeting)
  navigateTo(`/tables/meeting/${meeting.id}/voting-topics`)
}

const showMemberCheckin = (meeting) => {
  console.log('Showing member check-in for:', meeting)
  // TODO: Implement member check-in functionality
}

const showCheckinDisplay = (meeting) => {
  console.log('Showing check-in display for:', meeting)
  // TODO: Implement check-in display functionality
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
  // For numbers > 10, we can add more logic if needed
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
  showBasicInfoModal.value = false
}

// Fill meeting test data
const fillMeetingTestData = () => {
  const testRenewalGroups = [
    '臺北市大安區忠孝東路更新事宜臺北市政府會',
    '臺北市信義區松仁路更新事宜臺北市政府會',
    '臺北市中山區民權東路更新事宜臺北市政府會',
    '臺北市萬華區西門町更新事宜臺北市政府會',
    '臺北市士林區天母更新事宜臺北市政府會'
  ]

  const testMeetingNames = [
    '114年度第一屆第1次會員大會',
    '114年度第一屆第2次會員大會',
    '114年度第一屆第3次會員大會',
    '114年度理事會第1次會議',
    '114年度理事會第2次會議'
  ]

  const testLocations = [
    '台北市大安區信義路三段134號3樓會議室',
    '台北市信義區松仁路123號會議廳',
    '台北市中山區民權東路88號2樓',
    '台北市萬華區西門町168號活動中心',
    '台北市士林區天母東路99號社區中心'
  ]

  const testChairmanNames = ['王大明', '李美華', '張志強', '陳淑芬', '林建國']
  const testContactNames = ['王秘書', '李助理', '張專員', '陳經理', '林主任']

  // Current date and time for meeting
  const currentDate = new Date()
  const futureDate = new Date(currentDate.getTime() + (Math.floor(Math.random() * 30) + 1) * 24 * 60 * 60 * 1000)
  const meetingDate = futureDate.toISOString().split('T')[0]
  const meetingTime = `${String(Math.floor(Math.random() * 12) + 1).padStart(2, '0')}:${['00', '30'][Math.floor(Math.random() * 2)]}:00`

  // Fill form data
  renewalGroup.value = testRenewalGroups[Math.floor(Math.random() * testRenewalGroups.length)]
  meetingName.value = testMeetingNames[Math.floor(Math.random() * testMeetingNames.length)]
  meetingDateTime.value = `${meetingDate} ${meetingTime}`
  meetingLocation.value = testLocations[Math.floor(Math.random() * testLocations.length)]
  totalObservers.value = Math.floor(Math.random() * 10)
  landAreaRatioNumerator.value = Math.floor(Math.random() * 500) + 100
  landAreaRatioDenominator.value = Math.floor(Math.random() * 1000) + 500
  totalLandArea.value = Math.floor(Math.random() * 2000) + 1000
  buildingAreaRatioNumerator.value = Math.floor(Math.random() * 300) + 50
  buildingAreaRatioDenominator.value = Math.floor(Math.random() * 800) + 200
  totalBuildingArea.value = Math.floor(Math.random() * 1500) + 500
  peopleRatioNumerator.value = Math.floor(Math.random() * 50) + 20
  peopleRatioDenominator.value = Math.floor(Math.random() * 100) + 50
  totalPeopleCount.value = Math.floor(Math.random() * 80) + 40

  // Notice information
  noticeDocNumber.value = `北市都更字第${Math.floor(Math.random() * 100000).toString().padStart(6, '0')}號`
  noticeWordNumber.value = Math.floor(Math.random() * 1000).toString().padStart(4, '0')
  noticeMidNumber.value = Math.floor(Math.random() * 100000).toString().padStart(6, '0')
  noticeEndNumber.value = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
  chairmanName.value = testChairmanNames[Math.floor(Math.random() * testChairmanNames.length)]
  contactName.value = testContactNames[Math.floor(Math.random() * testContactNames.length)]
  contactPhone.value = `02-${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
  attachments.value = '會議資料、財務報表、工程進度報告'

  // Add some test observers
  observers.value = [
    { name: '政府代表', title: '都市更新處科長', phone: '02-27208889' },
    { name: '建築師代表', title: '主持建築師', phone: '02-27654321' }
  ]

  // Add some test descriptions
  descriptions.value = [
    { content: '討論都市更新計畫執行進度' },
    { content: '審議財務收支狀況' },
    { content: '決議下階段工作項目' }
  ]

  console.log('Meeting test data filled successfully')
}
</script>