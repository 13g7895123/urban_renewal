<template>
  <NuxtLayout name="main">
    <template #title>投票結果</template>

    <div class="p-4 max-w-5xl mx-auto">
      <!-- Header Banner -->
      <div class="bg-green-500 text-white p-6 rounded-t-lg shadow-md text-center mb-8">
        <h1 class="text-2xl font-bold mb-2">
          {{ renewalGroupName }}
        </h1>
        <h2 class="text-xl font-semibold">
          {{ meetingName }}
        </h2>
      </div>

      <!-- Topic and Time -->
      <div class="text-center mb-8">
        <h3 class="text-2xl text-gray-700 mb-4">議題：{{ topicName }}</h3>
        <div class="text-xl text-gray-600 font-light">
          {{ currentTime }}
        </div>
      </div>

      <!-- Results Table -->
      <div class="space-y-8">
        <!-- Rank 1: Agree -->
        <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
          <!-- Rank Header -->
          <div class="bg-blue-500 text-white flex border-b border-gray-300">
            <div class="w-32 py-3 text-center border-r border-blue-400 font-medium">名次</div>
            <div class="flex-1 py-3 text-center font-bold text-lg">1</div>
          </div>
          
          <!-- Option Row -->
          <div class="flex border-b border-gray-300 bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">選項</div>
            <div class="flex-1 py-3 text-center text-gray-800">同意</div>
          </div>

          <!-- Count Row -->
          <div class="flex border-b border-gray-300 bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">人數</div>
            <div class="flex-1 flex">
              <div class="flex-1 py-3 text-center border-r border-gray-200">{{ formatNumber(stats.agree_votes) }}</div>
              <div class="flex-1 py-3 text-center">{{ calculatePercentage(stats.agree_votes, stats.total_votes) }}%</div>
            </div>
          </div>

          <!-- Land Row -->
          <div class="flex border-b border-gray-300 bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">土地</div>
            <div class="flex-1 flex">
              <div class="flex-1 py-3 text-center border-r border-gray-200">{{ formatNumber(stats.agree_land_area, 2) }}</div>
              <div class="flex-1 py-3 text-center">{{ calculatePercentage(stats.agree_land_area, stats.total_land_area) }}%</div>
            </div>
          </div>

          <!-- Building Row -->
          <div class="flex bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">建物</div>
            <div class="flex-1 flex">
              <div class="flex-1 py-3 text-center border-r border-gray-200">{{ formatNumber(stats.agree_building_area, 2) }}</div>
              <div class="flex-1 py-3 text-center">{{ calculatePercentage(stats.agree_building_area, stats.total_building_area) }}%</div>
            </div>
          </div>
        </div>

        <!-- Rank 2: Disagree -->
        <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
          <!-- Rank Header -->
          <div class="bg-indigo-600 text-white flex border-b border-gray-300">
            <div class="w-32 py-3 text-center border-r border-indigo-500 font-medium">名次</div>
            <div class="flex-1 py-3 text-center font-bold text-lg">2</div>
          </div>
          
          <!-- Option Row -->
          <div class="flex border-b border-gray-300 bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">選項</div>
            <div class="flex-1 py-3 text-center text-gray-800">不同意</div>
          </div>

          <!-- Count Row -->
          <div class="flex border-b border-gray-300 bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">人數</div>
            <div class="flex-1 flex">
              <div class="flex-1 py-3 text-center border-r border-gray-200">{{ formatNumber(stats.disagree_votes) }}</div>
              <div class="flex-1 py-3 text-center">{{ calculatePercentage(stats.disagree_votes, stats.total_votes) }}%</div>
            </div>
          </div>

          <!-- Land Row -->
          <div class="flex border-b border-gray-300 bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">土地</div>
            <div class="flex-1 flex">
              <div class="flex-1 py-3 text-center border-r border-gray-200">{{ formatNumber(stats.disagree_land_area, 2) }}</div>
              <div class="flex-1 py-3 text-center">{{ calculatePercentage(stats.disagree_land_area, stats.total_land_area) }}%</div>
            </div>
          </div>

          <!-- Building Row -->
          <div class="flex bg-white">
            <div class="w-32 py-3 text-center border-r border-gray-300 text-gray-700 font-medium">建物</div>
            <div class="flex-1 flex">
              <div class="flex-1 py-3 text-center border-r border-gray-200">{{ formatNumber(stats.disagree_building_area, 2) }}</div>
              <div class="flex-1 py-3 text-center">{{ calculatePercentage(stats.disagree_building_area, stats.total_building_area) }}%</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Print Button -->
      <!-- <div class="mt-8 text-center no-print">
        <UButton
          color="gray"
          variant="solid"
          @click="printResults"
          class="px-6"
        >
          <Icon name="heroicons:printer" class="w-5 h-5 mr-2" />
          列印結果
        </UButton>
      </div> -->
      <!-- Back Button -->
      <div class="mt-8 text-center no-print flex justify-center gap-2">
        <UButton
          color="gray"
          variant="solid"
          @click="goBack"
          class="px-6"
        >
          <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
          回到投票議題頁面
        </UButton>
        <UButton
          @click="loadResults"
          :loading="isLoading"
          color="gray"
          variant="solid"
          class="px-4"
          title="重新整理"
        >
          <Icon name="heroicons:arrow-path" class="w-5 h-5" />
        </UButton>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const router = useRouter()
const route = useRoute()
const meetingId = route.params.meetingId
const topicId = route.params.topicId

// API composables
const { getMeeting } = useMeetings()
const { getVotingTopic } = useVotingTopics()
const { getVotingStatistics } = useVoting()
const { getUrbanRenewal } = useUrbanRenewal()
const { showError } = useSweetAlert()

// State
const isLoading = ref(false)
const renewalGroupName = ref('')
const meetingName = ref('')
const topicName = ref('')
const currentTime = ref('')
const stats = ref({
  total_votes: 0,
  agree_votes: 0,
  disagree_votes: 0,
  total_land_area: 0,
  agree_land_area: 0,
  disagree_land_area: 0,
  total_building_area: 0,
  agree_building_area: 0,
  disagree_building_area: 0
})

// Timer for clock
let clockInterval = null
let refreshInterval = null

onMounted(async () => {
  startClock()
  await loadResults()
  
  // Auto refresh every 30 seconds
  refreshInterval = setInterval(loadResults, 30000)
})

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval)
  if (refreshInterval) clearInterval(refreshInterval)
})

const startClock = () => {
  const updateTime = () => {
    const now = new Date()
    currentTime.value = now.toLocaleString('zh-TW', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    })
  }
  updateTime()
  clockInterval = setInterval(updateTime, 1000)
}

const loadResults = async () => {
  isLoading.value = true
  try {
    const [meetingResponse, topicResponse, statsResponse] = await Promise.all([
      getMeeting(meetingId),
      getVotingTopic(topicId),
      getVotingStatistics(topicId)
    ])

    if (meetingResponse.success && meetingResponse.data) {
      const meeting = meetingResponse.data.data || meetingResponse.data
      meetingName.value = meeting.meeting_name || meeting.name || ''
      
      // Fetch Urban Renewal Name if available
      if (meeting.urban_renewal_id) {
        const renewalResponse = await getUrbanRenewal(meeting.urban_renewal_id)
        if (renewalResponse.success && renewalResponse.data) {
          const renewal = renewalResponse.data.data || renewalResponse.data
          renewalGroupName.value = renewal.name || ''
        }
      }
    }

    if (topicResponse.success && topicResponse.data) {
      const topic = topicResponse.data.data || topicResponse.data
      topicName.value = topic.topic_title || topic.topic_name || topic.name || ''
    }

    if (statsResponse.success && statsResponse.data) {
      const data = statsResponse.data.data || statsResponse.data
      if (data.statistics) {
        stats.value = data.statistics
      }
    }
  } catch (error) {
    console.error('Failed to load results:', error)
    showError('載入失敗', '無法載入投票結果')
  } finally {
    isLoading.value = false
  }
}

const calculatePercentage = (value, total) => {
  if (!total || total === 0) return '0.00'
  return ((value / total) * 100).toFixed(2)
}

const formatNumber = (num, decimals = 0) => {
  if (num === undefined || num === null) return '0'
  return Number(num).toFixed(decimals)
}

const printResults = () => {
  window.print()
}

const goBack = () => {
  router.push(`/tables/meeting/${meetingId}/voting-topics`)
}
</script>

<style>
@media print {
  nav, button, .no-print {
    display: none !important;
  }
  body {
    background: white;
  }
  .shadow-md, .shadow-sm {
    box-shadow: none !important;
  }
}
</style>