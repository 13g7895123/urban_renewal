<template>
  <NuxtLayout name="main">
    <template #title>投票結果</template>

    <div class="p-8 max-w-6xl mx-auto">
      <!-- Main Title -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
          {{ renewalGroupName }} {{ meetingName }}
        </h1>
      </div>

      <!-- Topic and Time Info -->
      <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
        <div class="text-center">
          <h2 class="text-2xl font-semibold text-gray-800 mb-2">{{ topicName }}</h2>
          <p class="text-gray-600">{{ votingTime }}</p>
        </div>
      </div>

      <!-- Results Section -->
      <div class="space-y-6">
        <!-- Rank 1 - 同意 -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-800 rounded-full text-2xl font-bold mb-4">
              1
            </div>
            <h3 class="text-2xl font-bold text-green-800">同意</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 人數 -->
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
              <h4 class="text-lg font-semibold text-gray-700 mb-2">人數</h4>
              <div class="text-3xl font-bold text-green-600 mb-2">{{ agreeResults.people.count }}</div>
              <div class="text-sm text-gray-600">
                {{ agreeResults.people.numerator }} / {{ agreeResults.people.denominator }}
              </div>
              <div class="text-sm font-medium text-green-600">
                {{ agreeResults.people.percentage }}%
              </div>
            </div>

            <!-- 土地 -->
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
              <h4 class="text-lg font-semibold text-gray-700 mb-2">土地</h4>
              <div class="text-3xl font-bold text-green-600 mb-2">{{ agreeResults.land.area }}</div>
              <div class="text-xs text-gray-500 mb-1">平方公尺</div>
              <div class="text-sm text-gray-600">
                {{ agreeResults.land.numerator }} / {{ agreeResults.land.denominator }}
              </div>
              <div class="text-sm font-medium text-green-600">
                {{ agreeResults.land.percentage }}%
              </div>
            </div>

            <!-- 建物 -->
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
              <h4 class="text-lg font-semibold text-gray-700 mb-2">建物</h4>
              <div class="text-3xl font-bold text-green-600 mb-2">{{ agreeResults.building.area }}</div>
              <div class="text-xs text-gray-500 mb-1">平方公尺</div>
              <div class="text-sm text-gray-600">
                {{ agreeResults.building.numerator }} / {{ agreeResults.building.denominator }}
              </div>
              <div class="text-sm font-medium text-green-600">
                {{ agreeResults.building.percentage }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Rank 2 - 不同意 -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
          <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 text-red-800 rounded-full text-2xl font-bold mb-4">
              2
            </div>
            <h3 class="text-2xl font-bold text-red-800">不同意</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 人數 -->
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
              <h4 class="text-lg font-semibold text-gray-700 mb-2">人數</h4>
              <div class="text-3xl font-bold text-red-600 mb-2">{{ disagreeResults.people.count }}</div>
              <div class="text-sm text-gray-600">
                {{ disagreeResults.people.numerator }} / {{ disagreeResults.people.denominator }}
              </div>
              <div class="text-sm font-medium text-red-600">
                {{ disagreeResults.people.percentage }}%
              </div>
            </div>

            <!-- 土地 -->
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
              <h4 class="text-lg font-semibold text-gray-700 mb-2">土地</h4>
              <div class="text-3xl font-bold text-red-600 mb-2">{{ disagreeResults.land.area }}</div>
              <div class="text-xs text-gray-500 mb-1">平方公尺</div>
              <div class="text-sm text-gray-600">
                {{ disagreeResults.land.numerator }} / {{ disagreeResults.land.denominator }}
              </div>
              <div class="text-sm font-medium text-red-600">
                {{ disagreeResults.land.percentage }}%
              </div>
            </div>

            <!-- 建物 -->
            <div class="bg-white rounded-lg p-4 text-center shadow-sm">
              <h4 class="text-lg font-semibold text-gray-700 mb-2">建物</h4>
              <div class="text-3xl font-bold text-red-600 mb-2">{{ disagreeResults.building.area }}</div>
              <div class="text-xs text-gray-500 mb-1">平方公尺</div>
              <div class="text-sm text-gray-600">
                {{ disagreeResults.building.numerator }} / {{ disagreeResults.building.denominator }}
              </div>
              <div class="text-sm font-medium text-red-600">
                {{ disagreeResults.building.percentage }}%
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Statistics -->
      <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">投票統計總覽</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-700">{{ totalVoters }}</div>
            <div class="text-sm text-gray-600">總投票人數</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ votedCount }}</div>
            <div class="text-sm text-gray-600">已投票人數</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ agreeResults.people.count }}</div>
            <div class="text-sm text-gray-600">同意票數</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-red-600">{{ disagreeResults.people.count }}</div>
            <div class="text-sm text-gray-600">不同意票數</div>
          </div>
        </div>
      </div>

      <!-- Vote Status -->
      <div class="mt-6 text-center">
        <div
          class="inline-flex items-center px-6 py-3 rounded-full text-lg font-semibold"
          :class="isVotePassed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
        >
          <Icon
            :name="isVotePassed ? 'heroicons:check-circle' : 'heroicons:x-circle'"
            class="w-6 h-6 mr-2"
          />
          {{ isVotePassed ? '投票通過' : '投票未通過' }}
        </div>
      </div>

      <!-- Print Button -->
      <div class="mt-8 text-center">
        <UButton
          color="blue"
          @click="printResults"
        >
          <Icon name="heroicons:printer" class="w-5 h-5 mr-2" />
          列印結果
        </UButton>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const route = useRoute()
const meetingId = route.params.meetingId
const topicId = route.params.topicId

// API composables
const { getMeeting } = useMeetings()
const { getVotingTopic } = useVotingTopics()
const { getDetailedResults } = useVoting()
const { showError } = useSweetAlert()

// Loading state
const isLoading = ref(false)

// Page data
const renewalGroupName = ref('')
const meetingName = ref('')
const topicName = ref('')
const votingTime = ref('')

// Voting results data
const agreeResults = ref({
  people: { count: 0, numerator: 0, denominator: 0, percentage: 0 },
  land: { area: 0, numerator: 0, denominator: 0, percentage: 0 },
  building: { area: 0, numerator: 0, denominator: 0, percentage: 0 }
})

const disagreeResults = ref({
  people: { count: 0, numerator: 0, denominator: 0, percentage: 0 },
  land: { area: 0, numerator: 0, denominator: 0, percentage: 0 },
  building: { area: 0, numerator: 0, denominator: 0, percentage: 0 }
})

// Load data on mount
onMounted(async () => {
  await loadResults()
})

// Load voting results
const loadResults = async () => {
  isLoading.value = true
  console.log('[Results] Loading results for topic:', topicId)

  const [meetingResponse, topicResponse, resultsResponse] = await Promise.all([
    getMeeting(meetingId),
    getVotingTopic(topicId),
    getDetailedResults(topicId)
  ])

  if (meetingResponse.success && meetingResponse.data) {
    const meeting = meetingResponse.data.data || meetingResponse.data
    renewalGroupName.value = meeting.renewal_group || meeting.renewalGroup || ''
    meetingName.value = meeting.meeting_name || meeting.name || ''
  }

  if (topicResponse.success && topicResponse.data) {
    const topic = topicResponse.data.data || topicResponse.data
    topicName.value = topic.topic_name || topic.name || ''
    votingTime.value = topic.voting_time || topic.votingTime || new Date().toLocaleString('zh-TW')
  }

  if (resultsResponse.success && resultsResponse.data) {
    const results = resultsResponse.data.data || resultsResponse.data

    // Parse agree results
    if (results.agree) {
      agreeResults.value = {
        people: {
          count: results.agree.people?.count || 0,
          numerator: results.agree.people?.numerator || 0,
          denominator: results.agree.people?.denominator || 0,
          percentage: results.agree.people?.percentage || 0
        },
        land: {
          area: results.agree.land?.area || 0,
          numerator: results.agree.land?.numerator || 0,
          denominator: results.agree.land?.denominator || 0,
          percentage: results.agree.land?.percentage || 0
        },
        building: {
          area: results.agree.building?.area || 0,
          numerator: results.agree.building?.numerator || 0,
          denominator: results.agree.building?.denominator || 0,
          percentage: results.agree.building?.percentage || 0
        }
      }
    }

    // Parse disagree results
    if (results.disagree) {
      disagreeResults.value = {
        people: {
          count: results.disagree.people?.count || 0,
          numerator: results.disagree.people?.numerator || 0,
          denominator: results.disagree.people?.denominator || 0,
          percentage: results.disagree.people?.percentage || 0
        },
        land: {
          area: results.disagree.land?.area || 0,
          numerator: results.disagree.land?.numerator || 0,
          denominator: results.disagree.land?.denominator || 0,
          percentage: results.disagree.land?.percentage || 0
        },
        building: {
          area: results.disagree.building?.area || 0,
          numerator: results.disagree.building?.numerator || 0,
          denominator: results.disagree.building?.denominator || 0,
          percentage: results.disagree.building?.percentage || 0
        }
      }
    }

    console.log('[Results] Results loaded successfully')
  } else {
    console.error('[Results] Failed to load results:', resultsResponse.error)
    showError('載入失敗', resultsResponse.error?.message || '無法載入投票結果')
    // Use fallback mock data
    renewalGroupName.value = '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會'
    meetingName.value = '114年度第一屆第1次會員大會'
    topicName.value = '理事長選舉'
    votingTime.value = '2025年3月15日 下午2:30:00'

    agreeResults.value = {
      people: { count: 45, numerator: 45, denominator: 72, percentage: 62.5 },
      land: { area: 1250.5, numerator: 1250.5, denominator: 2000.0, percentage: 62.5 },
      building: { area: 890.3, numerator: 890.3, denominator: 1500.0, percentage: 59.4 }
    }

    disagreeResults.value = {
      people: { count: 27, numerator: 27, denominator: 72, percentage: 37.5 },
      land: { area: 749.5, numerator: 749.5, denominator: 2000.0, percentage: 37.5 },
      building: { area: 609.7, numerator: 609.7, denominator: 1500.0, percentage: 40.6 }
    }
  }

  isLoading.value = false
}

// Computed values
const totalVoters = computed(() => agreeResults.value.people.denominator)
const votedCount = computed(() => agreeResults.value.people.count + disagreeResults.value.people.count)

// Vote passing criteria (example: need > 60% for people, land, and building)
const isVotePassed = computed(() => {
  return agreeResults.value.people.percentage > 60 &&
         agreeResults.value.land.percentage > 60 &&
         agreeResults.value.building.percentage > 60
})

const printResults = () => {
  window.print()
}

// Auto-refresh every 30 seconds
let refreshInterval = null
onMounted(() => {
  refreshInterval = setInterval(() => {
    console.log('[Results] Auto-refreshing results...')
    loadResults()
  }, 30000) // Refresh every 30 seconds
})

// Clean up interval on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style>
@media print {
  /* Hide navigation and other non-essential elements when printing */
  nav, button, .no-print {
    display: none !important;
  }

  .print-break {
    page-break-before: always;
  }
}
</style>