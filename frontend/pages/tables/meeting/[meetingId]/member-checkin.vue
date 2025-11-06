<template>
  <NuxtLayout name="main">
    <template #title>會員報到</template>

    <div class="p-8">
      <!-- Content Title -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">更新會名稱</h1>
        <h2 class="text-lg text-gray-700 mb-4">{{ meeting.name }}</h2>

        <!-- Topics Section -->
        <div class="mb-4">
          <h3 class="text-md font-semibold text-gray-800 mb-2">議題</h3>
          <div class="bg-gray-50 p-3 rounded-lg">
            <p class="text-sm text-gray-600">{{ meeting.topics || '理事會選舉、監事會選舉' }}</p>
          </div>
        </div>

        <!-- Time Section -->
        <div class="mb-6">
          <h3 class="text-md font-semibold text-gray-800 mb-2">時間</h3>
          <div class="bg-gray-50 p-3 rounded-lg">
            <p class="text-sm text-gray-600">{{ meeting.date }} {{ meeting.time }}</p>
          </div>
        </div>

        <!-- Separator Line -->
        <hr class="border-gray-300 my-6">
      </div>

      <!-- Content Area - Property Owners Cards -->
      <div class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-10 gap-4">
          <div
            v-for="(owner, index) in propertyOwners"
            :key="owner.id || index"
            class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer"
            @click="openAttendanceModal(owner)"
          >
            <!-- Card Number -->
            <div class="bg-green-100 text-center py-2 rounded-t-lg">
              <span class="text-sm font-bold text-green-800">{{ String(index + 1).padStart(2, '0') }}</span>
            </div>

            <!-- Owner Name -->
            <div
              class="p-3 text-center rounded-b-lg transition-colors duration-200"
              :class="getCardNameClass(owner.attendance_status)"
            >
              <p class="text-sm font-medium" :class="getCardTextClass(owner.attendance_status)">
                {{ owner.owner_name }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Buttons -->
      <div class="flex justify-between">
        <!-- Export Button (Left) -->
        <UButton
          color="green"
          @click="exportCheckinResults"
        >
          <Icon name="heroicons:document-arrow-down" class="w-5 h-5 mr-2" />
          匯出簽到結果
        </UButton>

        <!-- Back Button (Right) -->
        <UButton
          variant="outline"
          @click="goBack"
        >
          <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
          回上一頁
        </UButton>
      </div>
    </div>

    <!-- Attendance Selection Modal -->
    <UModal 
      v-model="showAttendanceModal" 
      :ui="{ 
        width: 'max-w-2xl',
        background: 'bg-white',
        overlay: { background: 'bg-gray-900/75' }
      }"
    >
      <UCard 
        :ui="{ 
          background: 'bg-white',
          ring: 'ring-0',
          divide: '',
          body: { 
            base: 'bg-white',
            padding: 'p-8'
          },
          header: {
            base: 'bg-white',
            padding: 'px-8 pt-6 pb-4'
          },
          footer: {
            base: 'bg-white',
            padding: 'px-8 pb-6 pt-4'
          }
        }"
      >
        <template #header>
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <h3 class="text-2xl font-bold text-gray-900">
                報到人 {{ selectedOwner?.owner_name }}
              </h3>
              <span
                v-if="selectedOwner?.attendance_status"
                class="ml-3 px-3 py-1 text-sm font-medium text-white bg-green-500 rounded-md relative"
              >
                {{ getStatusText(selectedOwner.attendance_status) }}
                <button
                  @click="clearAttendanceStatus"
                  class="ml-2 text-white hover:text-gray-200"
                >
                  <Icon name="heroicons:x-mark" class="w-4 h-4" />
                </button>
              </span>
            </div>
            <UButton
              variant="ghost"
              icon="i-heroicons-x-mark-20-solid"
              @click="closeAttendanceModal"
            />
          </div>
        </template>

        <div class="py-6">
          <!-- Attendance Buttons - Horizontal Layout -->
          <div class="grid grid-cols-3 gap-4">
            <!-- 親自出席 - 藍色 -->
            <button
              @click="setAttendanceStatus('personal')"
              :class="[
                'py-12 px-6 rounded-xl text-xl font-bold transition-all duration-200 shadow-md hover:shadow-lg',
                selectedOwner?.attendance_status === 'personal'
                  ? 'bg-blue-500 text-white scale-105'
                  : 'bg-blue-50 text-blue-700 hover:bg-blue-100'
              ]"
            >
              親自出席
            </button>

            <!-- 委託出席 - 橘色 -->
            <button
              @click="setAttendanceStatus('delegated')"
              :class="[
                'py-12 px-6 rounded-xl text-xl font-bold transition-all duration-200 shadow-md hover:shadow-lg',
                selectedOwner?.attendance_status === 'delegated'
                  ? 'bg-orange-500 text-white scale-105'
                  : 'bg-orange-50 text-orange-700 hover:bg-orange-100'
              ]"
            >
              委託出席
            </button>

            <!-- 取消出席 - 灰色 -->
            <button
              @click="setAttendanceStatus('cancelled')"
              :class="[
                'py-12 px-6 rounded-xl text-xl font-bold transition-all duration-200 shadow-md hover:shadow-lg',
                selectedOwner?.attendance_status === 'cancelled'
                  ? 'bg-gray-500 text-white scale-105'
                  : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
              ]"
            >
              取消出席
            </button>
          </div>
        </div>

        <template #footer>
          <div class="flex justify-end gap-3">
            <UButton variant="outline" size="lg" @click="closeAttendanceModal">
              取消
            </UButton>
            <UButton color="blue" size="lg" @click="confirmAttendance">
              確認
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>
  </NuxtLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const route = useRoute()
const router = useRouter()

// Get meeting ID from route params
const meetingId = route.params.meetingId

// API composables
const { getMeeting } = useMeetings()
const { getAttendance, checkIn, updateAttendanceStatus } = useAttendance()
const { showSuccess, showError } = useSweetAlert()

// Loading states
const isLoading = ref(false)
const isSaving = ref(false)

// Modal state
const showAttendanceModal = ref(false)
const selectedOwner = ref(null)
const tempAttendanceStatus = ref(null)

// Meeting data
const meeting = ref({
  id: meetingId,
  name: '',
  renewalGroup: '',
  date: '',
  time: '',
  topics: ''
})

// Property owners data
const propertyOwners = ref([])

// Load data on mount
onMounted(async () => {
  await loadMeetingData()
  await loadAttendanceData()
})

// Load meeting data
const loadMeetingData = async () => {
  console.log('[Member Checkin] Loading meeting:', meetingId)
  const response = await getMeeting(meetingId)

  if (response.success && response.data) {
    const meetingData = response.data.data || response.data
    meeting.value = {
      id: meetingData.id,
      name: meetingData.meeting_name || meetingData.name || '',
      renewalGroup: meetingData.renewal_group || meetingData.renewalGroup || '',
      date: meetingData.meeting_date || meetingData.date || '',
      time: meetingData.meeting_time || meetingData.time || '',
      topics: meetingData.topics || '理事會選舉、監事會選舉'
    }
    console.log('[Member Checkin] Meeting loaded:', meeting.value)
  } else {
    console.error('[Member Checkin] Failed to load meeting:', response.error)
    // Use fallback mock data
    meeting.value = {
      id: meetingId,
      name: '114年度第一屆第1次會員大會',
      renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會',
      date: '2025年3月15日',
      time: '下午2:00:00',
      topics: '理事會選舉、監事會選舉'
    }
  }
}

// Load attendance data
const loadAttendanceData = async () => {
  isLoading.value = true
  console.log('[Member Checkin] Loading attendance data for meeting:', meetingId)

  const response = await getAttendance({ meeting_id: meetingId })
  isLoading.value = false

  if (response.success && response.data) {
    const attendanceData = response.data.data || response.data

    propertyOwners.value = Array.isArray(attendanceData) ? attendanceData.map(a => ({
      id: a.id,
      owner_id: a.owner_id || a.ownerId,
      owner_code: a.owner_code || a.ownerCode || '',
      owner_name: a.owner_name || a.ownerName || '',
      attendance_status: a.attendance_status || a.attendanceStatus || null
    })) : []

    console.log('[Member Checkin] Attendance data loaded:', propertyOwners.value.length)
  } else {
    console.error('[Member Checkin] Failed to load attendance data:', response.error)
    // Use fallback mock data
    propertyOwners.value = [
      { id: 1, owner_code: '001', owner_name: '王小明', attendance_status: null },
      { id: 2, owner_code: '002', owner_name: '李美華', attendance_status: 'personal' },
      { id: 3, owner_code: '003', owner_name: '張大同', attendance_status: 'delegated' },
      { id: 4, owner_code: '004', owner_name: '陳雅婷', attendance_status: null },
      { id: 5, owner_code: '005', owner_name: '林志強', attendance_status: null },
      { id: 6, owner_code: '006', owner_name: '黃淑芬', attendance_status: 'personal' },
      { id: 7, owner_code: '007', owner_name: '吳家豪', attendance_status: null },
      { id: 8, owner_code: '008', owner_name: '蔡雨辰', attendance_status: 'cancelled' },
      { id: 9, owner_code: '009', owner_name: '劉建國', attendance_status: null },
      { id: 10, owner_code: '010', owner_name: '楊麗娟', attendance_status: 'delegated' },
      { id: 11, owner_code: '011', owner_name: '許志明', attendance_status: null },
      { id: 12, owner_code: '012', owner_name: '鄭雅芳', attendance_status: null },
      { id: 13, owner_code: '013', owner_name: '徐建華', attendance_status: 'personal' },
      { id: 14, owner_code: '014', owner_name: '謝淑貞', attendance_status: null },
      { id: 15, owner_code: '015', owner_name: '蘇志偉', attendance_status: null },
      { id: 16, owner_code: '016', owner_name: '江美玲', attendance_status: null },
      { id: 17, owner_code: '017', owner_name: '廖家宏', attendance_status: null },
      { id: 18, owner_code: '018', owner_name: '賴淑華', attendance_status: null },
      { id: 19, owner_code: '019', owner_name: '范志強', attendance_status: null },
      { id: 20, owner_code: '020', owner_name: '葉雅婷', attendance_status: null }
    ]
  }
}

// Helper functions for card styling
const getCardNameClass = (status) => {
  switch (status) {
    case 'personal':
      return 'bg-blue-500'
    case 'delegated':
      return 'bg-orange-500'
    default:
      return 'bg-white'
  }
}

const getCardTextClass = (status) => {
  switch (status) {
    case 'personal':
    case 'delegated':
      return 'text-white'
    default:
      return 'text-gray-900'
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 'personal':
      return '親自出席'
    case 'delegated':
      return '委託出席'
    case 'cancelled':
      return '取消出席'
    default:
      return ''
  }
}

// Modal functions
const openAttendanceModal = (owner) => {
  selectedOwner.value = { ...owner }
  tempAttendanceStatus.value = owner.attendance_status
  showAttendanceModal.value = true
}

const closeAttendanceModal = () => {
  showAttendanceModal.value = false
  selectedOwner.value = null
  tempAttendanceStatus.value = null
}

const setAttendanceStatus = (status) => {
  if (selectedOwner.value) {
    selectedOwner.value.attendance_status = status
  }
}

const clearAttendanceStatus = () => {
  if (selectedOwner.value) {
    selectedOwner.value.attendance_status = null
  }
}

const confirmAttendance = async () => {
  if (!selectedOwner.value) {
    return
  }

  isSaving.value = true

  const attendanceData = {
    meeting_id: meetingId,
    owner_id: selectedOwner.value.owner_id || selectedOwner.value.id,
    attendance_status: selectedOwner.value.attendance_status
  }

  console.log('[Member Checkin] Updating attendance:', attendanceData)

  let response
  if (selectedOwner.value.id) {
    // Update existing attendance record
    response = await updateAttendanceStatus(selectedOwner.value.id, selectedOwner.value.attendance_status)
  } else {
    // Create new attendance record
    response = await checkIn(attendanceData)
  }

  isSaving.value = false

  if (response.success) {
    // Find the owner in the main array and update their status
    const ownerIndex = propertyOwners.value.findIndex(owner => owner.id === selectedOwner.value.id)
    if (ownerIndex !== -1) {
      propertyOwners.value[ownerIndex].attendance_status = selectedOwner.value.attendance_status
    }

    showSuccess('更新成功', '報到狀態已更新')
    console.log('[Member Checkin] Attendance updated successfully')
    closeAttendanceModal()
  } else {
    console.error('[Member Checkin] Failed to update attendance:', response.error)
    showError('更新失敗', response.error?.message || '無法更新報到狀態')
  }
}

// Action functions
const goBack = () => {
  router.push('/tables/meeting')
}

const exportCheckinResults = async () => {
  try {
    const config = useRuntimeConfig()
    const backendUrl = config.public.backendUrl || 'http://localhost:8000'
    
    // 呼叫後端 API 匯出 Excel
    const response = await fetch(`${backendUrl}/api/meetings/${meetingId}/attendances/export`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        format: 'excel'
      })
    })

    if (!response.ok) {
      throw new Error('匯出失敗')
    }

    // 取得檔案 blob
    const blob = await response.blob()
    
    // 建立下載連結
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `會員報到結果_${meeting.value.name}_${new Date().getTime()}.xlsx`
    document.body.appendChild(link)
    link.click()
    
    // 清理
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    
    console.log('匯出成功')
  } catch (error) {
    console.error('匯出簽到結果失敗:', error)
    alert('匯出失敗，請稍後再試')
  }
}
</script>