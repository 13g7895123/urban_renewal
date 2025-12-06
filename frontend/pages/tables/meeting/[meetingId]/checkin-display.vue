<template>
  <div class="min-h-screen bg-white flex flex-col">
    <!-- Header -->
    <header class="bg-[#4CAF50] text-white py-6 px-4 shadow-md">
      <div class="container mx-auto text-center">
        <h1 class="text-2xl md:text-3xl font-bold mb-2 leading-tight">
          {{ meeting.renewalGroup }}
        </h1>
        <h2 class="text-xl md:text-2xl font-medium">
          {{ meeting.name }}
        </h2>
      </div>
    </header>

    <!-- Sub-header -->
    <div class="container mx-auto px-4 py-6 text-center">
      <h3 class="text-2xl text-gray-700 mb-4">議題：會員報到</h3>
      <div class="text-2xl text-gray-600 font-light">
        {{ currentTime }}
      </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow w-full px-4 pb-8 flex items-center justify-center bg-white">
      <div class="w-full h-full flex items-start justify-center pt-4">
        <table class="w-[98%] border-collapse border border-gray-400 bg-white shadow-lg">
          <thead>
            <!-- Top Header Row -->
            <tr class="bg-[#2196F3] text-white">
              <th class="border border-gray-400 p-6 w-1/6"></th> <!-- Empty corner -->
              <th colspan="2" class="border border-gray-400 p-6 text-3xl font-bold w-1/3 tracking-wide">土地</th>
              <th colspan="2" class="border border-gray-400 p-6 text-3xl font-bold w-1/3 tracking-wide">建物</th>
              <th colspan="2" class="border border-gray-400 p-6 text-3xl font-bold w-1/6 tracking-wide">出席人數</th>
            </tr>
            <!-- Sub Header Row -->
            <tr class="bg-[#2196F3] text-white">
              <th class="border border-gray-400 p-5"></th>
              <th class="border border-gray-400 p-5 text-2xl font-bold">面積<br><span class="text-lg font-normal">(平方公尺)</span></th>
              <th class="border border-gray-400 p-5 text-2xl font-bold">比例</th>
              <th class="border border-gray-400 p-5 text-2xl font-bold">面積<br><span class="text-lg font-normal">(平方公尺)</span></th>
              <th class="border border-gray-400 p-5 text-2xl font-bold">比例</th>
              <th class="border border-gray-400 p-5 text-2xl font-bold">人數</th>
              <th class="border border-gray-400 p-5 text-2xl font-bold">比例</th>
            </tr>
          </thead>
          <tbody class="text-gray-800">
            <!-- Total Expected -->
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="border border-gray-400 p-6 text-2xl font-bold text-center bg-gray-50">應出席總數</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ formatNumber(stats.total_land_area) }}</td>
              <td class="border border-gray-400 p-6 text-2xl text-center text-gray-400">--</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ formatNumber(stats.total_building_area) }}</td>
              <td class="border border-gray-400 p-6 text-2xl text-center text-gray-400">--</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ stats.total_count }}</td>
              <td class="border border-gray-400 p-6 text-2xl text-center text-gray-400">--</td>
            </tr>

            <!-- Personal Attendance -->
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="border border-gray-400 p-6 text-2xl font-bold text-center bg-gray-50">親自出席數</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ formatNumber(stats.present_land_area) }}</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ calculatePercentage(stats.present_land_area, stats.total_land_area) }}%</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ formatNumber(stats.present_building_area) }}</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ calculatePercentage(stats.present_building_area, stats.total_building_area) }}%</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ stats.present_count }}</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ calculatePercentage(stats.present_count, stats.total_count) }}%</td>
            </tr>

            <!-- Proxy Attendance -->
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="border border-gray-400 p-6 text-2xl font-bold text-center bg-gray-50">委託出席數</td>
              <td class="border border-gray-400 p-6 text-2xl text-center font-medium">{{ formatNumber(stats.proxy_land_area) }}</td>
              <td class="border border-gray-400 p-6 text-center text-2xl font-medium">{{ calculatePercentage(stats.proxy_land_area, stats.total_land_area) }}%</td>
              <td class="border border-gray-400 p-6 text-center text-2xl font-medium">{{ formatNumber(stats.proxy_building_area) }}</td>
              <td class="border border-gray-400 p-6 text-center text-2xl font-medium">{{ calculatePercentage(stats.proxy_building_area, stats.total_building_area) }}%</td>
              <td class="border border-gray-400 p-6 text-center text-2xl font-medium">{{ stats.proxy_count }}</td>
              <td class="border border-gray-400 p-6 text-center text-2xl font-medium">{{ calculatePercentage(stats.proxy_count, stats.total_count) }}%</td>
            </tr>

            <!-- Total Attendance -->
            <tr class="bg-gray-100 font-bold">
              <td class="border border-gray-400 p-6 text-center text-2xl">合計總出席數</td>
              <td class="border border-gray-400 p-6 text-center text-2xl">{{ formatNumber(totalAttendedLandArea) }}</td>
              <td class="border border-gray-400 p-6 text-center text-2xl">{{ calculatePercentage(totalAttendedLandArea, stats.total_land_area) }}%</td>
              <td class="border border-gray-400 p-6 text-center text-2xl">{{ formatNumber(totalAttendedBuildingArea) }}</td>
              <td class="border border-gray-400 p-6 text-center text-2xl">{{ calculatePercentage(totalAttendedBuildingArea, stats.total_building_area) }}%</td>
              <td class="border border-gray-400 p-6 text-center text-2xl">{{ totalAttendedCount }}</td>
              <td class="border border-gray-400 p-6 text-center text-2xl">{{ calculatePercentage(totalAttendedCount, stats.total_count) }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'

definePageMeta({
  layout: false,
  middleware: ['auth']
})

const route = useRoute()
const meetingId = route.params.meetingId

// API composables
const { getMeeting } = useMeetings()
const { getAttendanceStatistics } = useAttendance()

// State
const currentTime = ref('')
const timer = ref(null)
const pollingTimer = ref(null)
const meeting = ref({
  name: '',
  renewalGroup: ''
})
const stats = ref({
  total_count: 0,
  total_land_area: 0,
  total_building_area: 0,
  present_count: 0,
  present_land_area: 0,
  present_building_area: 0,
  proxy_count: 0,
  proxy_land_area: 0,
  proxy_building_area: 0
})

// Computed totals
const totalAttendedCount = computed(() => stats.value.present_count + stats.value.proxy_count)
const totalAttendedLandArea = computed(() => stats.value.present_land_area + stats.value.proxy_land_area)
const totalAttendedBuildingArea = computed(() => stats.value.present_building_area + stats.value.proxy_building_area)

// Formatters
const formatNumber = (num) => {
  if (!num) return '0.00'
  return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const calculatePercentage = (part, total) => {
  if (!total || total === 0) return '0.00'
  return ((part / total) * 100).toFixed(2)
}

// Time update
const updateTime = () => {
  const now = new Date()
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric', 
    hour: 'numeric', 
    minute: 'numeric', 
    second: 'numeric',
    hour12: true 
  }
  currentTime.value = now.toLocaleString('zh-TW', options)
}

// Data loading
const loadData = async () => {
  try {
    // Load meeting info if not loaded
    if (!meeting.value.name) {
      const meetingRes = await getMeeting(meetingId)
      if (meetingRes.success && meetingRes.data) {
        const data = meetingRes.data.data || meetingRes.data
        meeting.value = {
          name: data.meeting_name || data.name || '',
          renewalGroup: data.urban_renewal_name || data.renewal_group || data.renewalGroup || ''
        }
      }
    }

    // Load statistics
    const statsRes = await getAttendanceStatistics(meetingId)
    if (statsRes.success && statsRes.data) {
      stats.value = statsRes.data
    }
  } catch (error) {
    console.error('Failed to load data:', error)
  }
}

onMounted(() => {
  // Initial load
  updateTime()
  loadData()

  // Start timers
  timer.value = setInterval(updateTime, 1000)
  pollingTimer.value = setInterval(loadData, 5000) // Refresh every 5 seconds
})

onUnmounted(() => {
  if (timer.value) clearInterval(timer.value)
  if (pollingTimer.value) clearInterval(pollingTimer.value)
})
</script>

<style scoped>
/* Ensure table borders are visible and crisp */
table {
  border-spacing: 0;
}
th, td {
  border-color: #9ca3af; /* gray-400 */
}
</style>
