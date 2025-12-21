<template>
  <NuxtLayout name="main">
    <template #title>會員報到</template>

    <div class="p-8">
      <!-- Header -->
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ meeting.renewalGroup }}</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ meeting.name }}</h2>

        <!-- Topic and Time -->
        <div class="mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-1">議題：{{ meeting.topics || '理事會選舉、監事會選舉' }}</h3>
          <p class="text-gray-600">{{ meeting.date }} {{ meeting.time }}</p>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 mb-6"></div>
      </div>

      <!-- Content Area - Property Owners Cards -->
      <div class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-10 gap-4">
          <div
            v-for="(owner, index) in propertyOwners"
            :key="owner.id || index"
            class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer"
            :class="{
              'hover:border-blue-300': !owner.attendance_status,
              'opacity-75': owner.attendance_status
            }"
            @click="!owner.attendance_status && openAttendanceModal(owner)"
          >
            <!-- Card Number -->
            <div class="bg-green-100 text-center py-3">
              <span class="text-2xl font-bold text-gray-700">{{ String(index + 1).padStart(2, '0') }}</span>
            </div>

            <!-- Owner Name -->
            <div
              class="text-center py-3 px-2"
              :class="getCardNameClass(owner.attendance_status)"
            >
              <div class="text-sm font-medium truncate">
                <span v-if="owner.owner_code" class="mr-1">{{ String(owner.owner_code).padStart(2, '0') }}</span>
                {{ owner.owner_name }}
              </div>
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
        <div class="flex gap-2">
          <UButton
            color="blue"
            variant="outline"
            :to="`/tables/meeting/${meetingId}/checkin-display`"
            target="_blank"
          >
            <Icon name="heroicons:presentation-chart-bar" class="w-5 h-5 mr-2" />
            開啟報到顯示
          </UButton>
          <UButton
            variant="outline"
            @click="goBack"
          >
            <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
            回上一頁
          </UButton>
          <UButton
            @click="refreshData"
            :loading="isLoading"
            variant="ghost"
            class="text-gray-600 hover:text-green-600 hover:bg-green-50"
            title="重新整理"
          >
            <Icon name="heroicons:arrow-path" class="w-5 h-5" />
          </UButton>
        </div>
      </div>
    </div>

    <!-- Attendance Selection Modal -->
    <UModal
      v-model="showAttendanceModal"
      :ui="{
        width: 'max-w-md',
        overlay: {
          background: 'bg-gray-200/75'
        }
      }"
      :prevent-close="true"
    >
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              報到人 姓名: {{ selectedOwner?.owner_name }}
            </h3>
          </div>
        </template>

        <div class="p-6">
          <!-- Attendance Options -->
          <div class="space-y-4 mb-6">
            <UButton
              block
              color="blue"
              size="xl"
              @click="setAttendanceStatus('personal')"
              :variant="selectedOwner?.attendance_status === 'personal' ? 'solid' : 'outline'"
              class="h-16 text-xl font-semibold"
            >
              <Icon name="heroicons:user" class="w-8 h-8 mr-3" />
              親自出席
            </UButton>

            <UButton
              block
              color="orange"
              size="xl"
              @click="setAttendanceStatus('delegated')"
              :variant="selectedOwner?.attendance_status === 'delegated' ? 'solid' : 'outline'"
              class="h-16 text-xl font-semibold"
            >
              <Icon name="heroicons:users" class="w-8 h-8 mr-3" />
              委託出席
            </UButton>

            <UButton
              block
              color="gray"
              size="xl"
              @click="setAttendanceStatus('cancelled')"
              :variant="selectedOwner?.attendance_status === 'cancelled' ? 'solid' : 'outline'"
              class="h-16 text-xl font-semibold"
            >
              <Icon name="heroicons:x-circle" class="w-8 h-8 mr-3" />
              取消出席
            </UButton>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3">
            <UButton
              variant="outline"
              @click="closeAttendanceModal"
            >
              取消
            </UButton>
            <UButton
              color="green"
              @click="confirmAttendance"
              :disabled="!selectedOwner?.attendance_status"
            >
              <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
              確認
            </UButton>
          </div>
        </div>
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
const { getMeeting, getEligibleVoters } = useMeetings()
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
    showError('載入失敗', response.error?.message || '無法載入會議資料')
    meeting.value = null
  }
}

// Load attendance data
const loadAttendanceData = async () => {
  isLoading.value = true
  console.log('[Member Checkin] Loading attendance data for meeting:', meetingId)

  // Fetch both eligible voters (snapshot) and existing attendance records
  const [eligibleResponse, attendanceResponse] = await Promise.all([
    getEligibleVoters(meetingId, { per_page: 1000 }),
    getAttendance({ meeting_id: meetingId, per_page: 1000 })
  ])

  isLoading.value = false

  if (eligibleResponse.success && eligibleResponse.data) {
    const eligibleVoters = eligibleResponse.data.data || eligibleResponse.data || []
    const hasSnapshot = eligibleResponse.data.has_snapshot
    
    // If no snapshot exists, show warning
    if (!hasSnapshot) {
      console.warn('[Member Checkin] No voter snapshot found for this meeting')
      showError('提示', '此會議尚未建立投票人快照，請先重新整理投票人名單')
    }

    // Process attendance data
    const attendanceData = (attendanceResponse.success && attendanceResponse.data) 
      ? (attendanceResponse.data.data || attendanceResponse.data || []) 
      : []
      
    console.log('[Member Checkin] Raw attendance response:', attendanceResponse)
    console.log('[Member Checkin] Processed attendance data:', attendanceData)

    // Create a map of attendance for quick lookup
    const attendanceMap = new Map()
    attendanceData.forEach(record => {
      attendanceMap.set(parseInt(record.property_owner_id), record)
    })

    // 後端狀態轉換為前端格式
    const backendToFrontendStatus = {
      'present': 'personal',
      'proxy': 'delegated',
      'absent': 'cancelled'
    }

    // Merge data - use snapshot data as base
    propertyOwners.value = Array.isArray(eligibleVoters) ? eligibleVoters.map(voter => {
      const attendanceRecord = attendanceMap.get(parseInt(voter.property_owner_id))
      if (attendanceRecord) {
        console.log(`[Member Checkin] Found attendance for owner ${voter.property_owner_id}:`, attendanceRecord)
      }
      // Convert backend status to frontend status
      const backendStatus = attendanceRecord ? attendanceRecord.attendance_type : null
      const frontendStatus = backendStatus ? (backendToFrontendStatus[backendStatus] || backendStatus) : null

      return {
        id: voter.property_owner_id, // Use property_owner_id as the main ID for operations
        owner_id: voter.property_owner_id,
        owner_code: voter.owner_code || '',
        owner_name: voter.owner_name || '',
        attendance_status: frontendStatus,
        attendance_id: attendanceRecord ? attendanceRecord.id : null
      }
    }) : []

    console.log('[Member Checkin] Property owners loaded from snapshot:', propertyOwners.value.length)
  } else {
    console.error('[Member Checkin] Failed to load eligible voters:', eligibleResponse.error)
    showError('載入失敗', eligibleResponse.error?.message || '無法載入投票人資料')
    propertyOwners.value = []
  }
}

// Helper functions for card styling
const getCardNameClass = (status) => {
  switch (status) {
    case 'personal':
      return 'bg-blue-500 text-white'
    case 'delegated':
      return 'bg-orange-500 text-white'
    case 'cancelled':
      return 'bg-gray-500 text-white'
    default:
      return 'bg-white text-gray-900'
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

  // 前端狀態轉換為後端格式
  const statusMap = {
    'personal': 'present',
    'delegated': 'proxy',
    'cancelled': 'absent'
  }

  const attendanceData = {
    attendance_type: statusMap[selectedOwner.value.attendance_status] || selectedOwner.value.attendance_status
  }

  const ownerId = selectedOwner.value.owner_id || selectedOwner.value.id

  console.log('[Member Checkin] Updating attendance:', { meetingId, ownerId, attendanceData })

  let response
  // Check if we have an attendance_id (meaning record exists)
  if (selectedOwner.value.attendance_id) {
    // Update existing attendance record
    // Note: updateAttendanceStatus expects ID of the attendance record, not property owner
    // But wait, the composable might be using property_owner_id? Let's check useAttendance.js
    // It uses patch(`/meeting-attendance/${id}/update-status`) where id is attendance ID.
    // However, the backend controller `update` method signature is `update($meetingId = null, $ownerId = null)`
    // And route is `PUT /api/meetings/{meetingId}/attendances/{ownerId}`
    
    // Let's use the correct API endpoint for update based on backend controller
    // The backend controller `update` takes meetingId and ownerId.
    // We need to make sure useAttendance.js calls the right endpoint or we use a direct call here.
    
    // Let's look at useAttendance.js again.
    // updateAttendanceStatus calls `patch('/meeting-attendance/${id}/update-status')`
    // This seems to map to `MeetingAttendanceController::updateStatus` which takes attendance ID.
    
    // But we also have `PUT /api/meetings/{meetingId}/attendances/{ownerId}` mapped to `MeetingAttendanceController::update`
    
    // Let's try to use the checkIn function for both create and update if possible, 
    // or use a custom call for update if checkIn doesn't handle updates.
    // The backend checkIn method handles both insert and update!
    // "if ($existingAttendance) { ... update ... } else { ... insert ... }"
    
    // So we can just use checkIn for everything!
    response = await checkIn(meetingId, ownerId, attendanceData)
  } else {
    // Create new attendance record
    response = await checkIn(meetingId, ownerId, attendanceData)
  }


  isSaving.value = false

  if (response.success) {
    // Find the owner in the main array and update their status
    const ownerIndex = propertyOwners.value.findIndex(owner => owner.id === selectedOwner.value.id)
    if (ownerIndex !== -1) {
      propertyOwners.value[ownerIndex].attendance_status = selectedOwner.value.attendance_status
      // Update attendance_id if it was a new record
      if (response.data && response.data.id) {
        propertyOwners.value[ownerIndex].attendance_id = response.data.id
      }
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

const refreshData = async () => {
  await Promise.all([
    loadMeetingData(),
    loadAttendanceData()
  ])
}

const exportCheckinResults = async () => {
  try {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl || config.public.backendUrl || ''
    const backendUrl = apiBaseUrl.replace(/\/api$/, '')
    
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