<template>
  <NuxtLayout name="main">
    <template #title>報到顯示</template>

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

        <!-- Real-time Clock Section -->
        <div class="mb-6">
          <h3 class="text-md font-semibold text-gray-800 mb-2">時間</h3>
          <div class="bg-gray-50 p-3 rounded-lg">
            <p class="text-sm text-gray-600">{{ currentDateTime }}</p>
          </div>
        </div>

        <!-- Separator Line -->
        <hr class="border-gray-300 my-6">
      </div>

      <!-- Attendance Display Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <!-- Two-layer Headers -->
            <thead>
              <!-- First Layer - Main Categories -->
              <tr class="border-b border-gray-200">
                <th class="p-4 text-center text-sm font-medium text-green-600 border-r border-gray-200" rowspan="2">
                  類別
                </th>
                <th class="p-4 text-center text-sm font-medium text-green-600 border-r border-gray-200" colspan="2">
                  土地
                </th>
                <th class="p-4 text-center text-sm font-medium text-green-600 border-r border-gray-200" colspan="2">
                  建物
                </th>
                <th class="p-4 text-center text-sm font-medium text-green-600" colspan="2">
                  出席人數
                </th>
              </tr>
              <!-- Second Layer - Sub Categories -->
              <tr class="border-b border-gray-200 bg-gray-50">
                <th class="p-3 text-center text-xs font-medium text-green-600 border-r border-gray-200">
                  面積(平方公尺)
                </th>
                <th class="p-3 text-center text-xs font-medium text-green-600 border-r border-gray-200">
                  比例
                </th>
                <th class="p-3 text-center text-xs font-medium text-green-600 border-r border-gray-200">
                  面積(平方公尺)
                </th>
                <th class="p-3 text-center text-xs font-medium text-green-600 border-r border-gray-200">
                  比例
                </th>
                <th class="p-3 text-center text-xs font-medium text-green-600 border-r border-gray-200">
                  人數
                </th>
                <th class="p-3 text-center text-xs font-medium text-green-600">
                  比例
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Total Should Attend -->
              <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="p-4 text-sm font-medium text-gray-900 border-r border-gray-200">
                  應出席總數
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatNumber(totalStats.land.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatPercentage(totalStats.land.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatNumber(totalStats.building.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatPercentage(totalStats.building.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ totalStats.attendance.count }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900">
                  {{ formatPercentage(totalStats.attendance.ratio) }}
                </td>
              </tr>

              <!-- Personal Attendance -->
              <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="p-4 text-sm font-medium text-blue-600 border-r border-gray-200">
                  親自出席數
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatNumber(personalStats.land.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatPercentage(personalStats.land.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatNumber(personalStats.building.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatPercentage(personalStats.building.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-blue-600 font-medium border-r border-gray-200">
                  {{ personalStats.attendance.count }}
                </td>
                <td class="p-4 text-sm text-center text-blue-600 font-medium">
                  {{ formatPercentage(personalStats.attendance.ratio) }}
                </td>
              </tr>

              <!-- Delegated Attendance -->
              <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="p-4 text-sm font-medium text-orange-600 border-r border-gray-200">
                  委託出席數
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatNumber(delegatedStats.land.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatPercentage(delegatedStats.land.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatNumber(delegatedStats.building.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 border-r border-gray-200">
                  {{ formatPercentage(delegatedStats.building.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-orange-600 font-medium border-r border-gray-200">
                  {{ delegatedStats.attendance.count }}
                </td>
                <td class="p-4 text-sm text-center text-orange-600 font-medium">
                  {{ formatPercentage(delegatedStats.attendance.ratio) }}
                </td>
              </tr>

              <!-- Total Attendance -->
              <tr class="border-b border-gray-100 hover:bg-gray-50 bg-green-50">
                <td class="p-4 text-sm font-bold text-green-700 border-r border-gray-200">
                  合計總出席數
                </td>
                <td class="p-4 text-sm text-center text-gray-900 font-medium border-r border-gray-200">
                  {{ formatNumber(attendedStats.land.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 font-medium border-r border-gray-200">
                  {{ formatPercentage(attendedStats.land.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 font-medium border-r border-gray-200">
                  {{ formatNumber(attendedStats.building.area) }}
                </td>
                <td class="p-4 text-sm text-center text-gray-900 font-medium border-r border-gray-200">
                  {{ formatPercentage(attendedStats.building.ratio) }}
                </td>
                <td class="p-4 text-sm text-center text-green-700 font-bold border-r border-gray-200">
                  {{ attendedStats.attendance.count }}
                </td>
                <td class="p-4 text-sm text-center text-green-700 font-bold">
                  {{ formatPercentage(attendedStats.attendance.ratio) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bottom Navigation -->
      <div class="flex justify-end mt-6">
        <UButton
          variant="outline"
          @click="goBack"
        >
          <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
          回上一頁
        </UButton>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const route = useRoute()
const router = useRouter()

// API composables
const { getMeeting } = useMeetings()
const { getAttendance } = useMeetingAttendance()
const { showError } = useSweetAlert()

// Get meeting ID from route params
const meetingId = route.params.meetingId

// Loading state
const isLoading = ref(false)

// Real-time clock
const currentDateTime = ref('')
let clockInterval = null

// Meeting data
const meeting = ref({
  id: meetingId,
  name: '',
  renewalGroup: '',
  topics: ''
})

// Property owners data with land/building areas and attendance status
const propertyOwners = ref([])

// Load meeting data
const loadMeeting = async () => {
  console.log('[Checkin Display] Loading meeting:', meetingId)

  const response = await getMeeting(meetingId)

  if (response.success && response.data) {
    const meetingData = response.data.data || response.data
    meeting.value = {
      id: meetingData.id,
      name: meetingData.meeting_name || meetingData.name || '',
      renewalGroup: meetingData.renewal_group || meetingData.renewalGroup || '',
      topics: meetingData.topics || ''
    }
    console.log('[Checkin Display] Meeting loaded:', meeting.value)
  } else {
    console.error('[Checkin Display] Failed to load meeting:', response.error)
    showError('載入失敗', response.error?.message || '無法載入會議資料')
  }
}

// Load attendance data with statistics
const loadAttendanceData = async () => {
  isLoading.value = true
  console.log('[Checkin Display] Loading attendance data for meeting:', meetingId)

  const response = await getAttendance({ meeting_id: meetingId })
  isLoading.value = false

  if (response.success && response.data) {
    const attendanceData = response.data.data || response.data

    propertyOwners.value = Array.isArray(attendanceData) ? attendanceData.map(a => ({
      id: a.id,
      owner_name: a.owner_name || a.ownerName || '',
      land_area: parseFloat(a.land_area || a.landArea) || 0,
      building_area: parseFloat(a.building_area || a.buildingArea) || 0,
      attendance_status: a.attendance_status || a.attendanceStatus || null
    })) : []

    console.log('[Checkin Display] Attendance data loaded:', propertyOwners.value.length)
  } else {
    console.error('[Checkin Display] Failed to load attendance data:', response.error)
    showError('載入失敗', response.error?.message || '無法載入出席資料')
    propertyOwners.value = []
  }
}

// Calculate statistics
const totalStats = computed(() => {
  const totalLandArea = propertyOwners.value.reduce((sum, owner) => sum + owner.land_area, 0)
  const totalBuildingArea = propertyOwners.value.reduce((sum, owner) => sum + owner.building_area, 0)
  const totalCount = propertyOwners.value.length

  return {
    land: {
      area: totalLandArea,
      ratio: 100 // Total is always 100%
    },
    building: {
      area: totalBuildingArea,
      ratio: 100 // Total is always 100%
    },
    attendance: {
      count: totalCount,
      ratio: 100 // Total is always 100%
    }
  }
})

const personalStats = computed(() => {
  const personalOwners = propertyOwners.value.filter(owner => owner.attendance_status === 'personal')
  const personalLandArea = personalOwners.reduce((sum, owner) => sum + owner.land_area, 0)
  const personalBuildingArea = personalOwners.reduce((sum, owner) => sum + owner.building_area, 0)
  const personalCount = personalOwners.length

  return {
    land: {
      area: personalLandArea,
      ratio: totalStats.value.land.area > 0 ? (personalLandArea / totalStats.value.land.area) * 100 : 0
    },
    building: {
      area: personalBuildingArea,
      ratio: totalStats.value.building.area > 0 ? (personalBuildingArea / totalStats.value.building.area) * 100 : 0
    },
    attendance: {
      count: personalCount,
      ratio: totalStats.value.attendance.count > 0 ? (personalCount / totalStats.value.attendance.count) * 100 : 0
    }
  }
})

const delegatedStats = computed(() => {
  const delegatedOwners = propertyOwners.value.filter(owner => owner.attendance_status === 'delegated')
  const delegatedLandArea = delegatedOwners.reduce((sum, owner) => sum + owner.land_area, 0)
  const delegatedBuildingArea = delegatedOwners.reduce((sum, owner) => sum + owner.building_area, 0)
  const delegatedCount = delegatedOwners.length

  return {
    land: {
      area: delegatedLandArea,
      ratio: totalStats.value.land.area > 0 ? (delegatedLandArea / totalStats.value.land.area) * 100 : 0
    },
    building: {
      area: delegatedBuildingArea,
      ratio: totalStats.value.building.area > 0 ? (delegatedBuildingArea / totalStats.value.building.area) * 100 : 0
    },
    attendance: {
      count: delegatedCount,
      ratio: totalStats.value.attendance.count > 0 ? (delegatedCount / totalStats.value.attendance.count) * 100 : 0
    }
  }
})

const attendedStats = computed(() => {
  const attendedOwners = propertyOwners.value.filter(owner =>
    owner.attendance_status === 'personal' || owner.attendance_status === 'delegated'
  )
  const attendedLandArea = attendedOwners.reduce((sum, owner) => sum + owner.land_area, 0)
  const attendedBuildingArea = attendedOwners.reduce((sum, owner) => sum + owner.building_area, 0)
  const attendedCount = attendedOwners.length

  return {
    land: {
      area: attendedLandArea,
      ratio: totalStats.value.land.area > 0 ? (attendedLandArea / totalStats.value.land.area) * 100 : 0
    },
    building: {
      area: attendedBuildingArea,
      ratio: totalStats.value.building.area > 0 ? (attendedBuildingArea / totalStats.value.building.area) * 100 : 0
    },
    attendance: {
      count: attendedCount,
      ratio: totalStats.value.attendance.count > 0 ? (attendedCount / totalStats.value.attendance.count) * 100 : 0
    }
  }
})

// Helper functions
const formatNumber = (num) => {
  return num.toFixed(2)
}

const formatPercentage = (num) => {
  return `${num.toFixed(1)}%`
}

const updateClock = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = (now.getMonth() + 1).toString()
  const day = now.getDate().toString()
  const hours = now.getHours().toString().padStart(2, '0')
  const minutes = now.getMinutes().toString().padStart(2, '0')
  const seconds = now.getSeconds().toString().padStart(2, '0')

  const period = now.getHours() >= 12 ? '下午' : '上午'
  const displayHour = now.getHours() > 12 ? now.getHours() - 12 : (now.getHours() === 0 ? 12 : now.getHours())

  currentDateTime.value = `${year}年${month}月${day}日 ${period}${displayHour}:${minutes}:${seconds}`
}

const goBack = () => {
  router.push('/tables/meeting')
}

// Lifecycle
onMounted(async () => {
  updateClock()
  clockInterval = setInterval(updateClock, 1000)
  
  // Load data from API
  await Promise.all([
    loadMeeting(),
    loadAttendanceData()
  ])
})

onUnmounted(() => {
  if (clockInterval) {
    clearInterval(clockInterval)
  }
})
</script>