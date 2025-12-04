<template>
  <NuxtLayout name="main">
    <template #title>開始投票</template>

    <div class="p-8">
      <!-- Header -->
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ renewalGroupName }}</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ meetingName }}</h2>

        <!-- Topic and Time -->
        <div class="mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-1">{{ topicName }}</h3>
          <p class="text-gray-600">{{ votingTime }}</p>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 mb-6"></div>
      </div>

      <!-- Voting Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-10 gap-4 mb-8">
        <div
          v-for="(voter, index) in voters"
          :key="index"
          class="bg-white border border-gray-200 rounded-lg p-4 text-center shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          :class="{
            'bg-gray-100': voter.hasVoted,
            'hover:border-blue-300': !voter.hasVoted
          }"
          @click="!voter.hasVoted && openVotingModal(voter)"
        >
          <!-- Sequence Number -->
          <div class="text-2xl font-bold text-gray-700 mb-2">
            {{ String(index + 1).padStart(2, '0') }}
          </div>

          <!-- Voter Info (Code + Name) -->
          <div
            class="text-sm font-medium truncate"
            :class="{
              'text-gray-400 bg-gray-200 px-2 py-1 rounded': voter.hasVoted,
              'text-gray-900': !voter.hasVoted
            }"
          >
            {{ voter.ownerCode }} {{ voter.name }}
          </div>

          <!-- Voted Indicator -->
          <div v-if="voter.hasVoted" class="mt-2 text-xs text-gray-500">
            已投票
          </div>
        </div>
      </div>

      <!-- Back Button -->
      <div class="flex justify-end">
        <UButton
          variant="outline"
          @click="goBack"
        >
          <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
          回上一頁
        </UButton>
      </div>
    </div>

    <!-- Voting Modal -->
    <UModal v-model="showVotingModal" :ui="{ width: 'max-w-md' }" :prevent-close="true">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              投票人 姓名: {{ selectedVoter?.name }}
            </h3>
          </div>
        </template>

        <div class="p-6">
          <!-- Voting Options -->
          <div class="space-y-4 mb-6">
            <UButton
              block
              color="green"
              size="xl"
              @click="selectVote('agree')"
              :variant="selectedVote === 'agree' ? 'solid' : 'outline'"
              class="h-16 text-xl font-semibold"
            >
              <Icon name="heroicons:check-circle" class="w-8 h-8 mr-3" />
              同意
            </UButton>

            <UButton
              block
              color="red"
              size="xl"
              @click="selectVote('disagree')"
              :variant="selectedVote === 'disagree' ? 'solid' : 'outline'"
              class="h-16 text-xl font-semibold"
            >
              <Icon name="heroicons:x-circle" class="w-8 h-8 mr-3" />
              不同意
            </UButton>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between">
            <UButton
              color="red"
              variant="outline"
              @click="cancelVote"
            >
              <Icon name="heroicons:arrow-uturn-left" class="w-4 h-4 mr-2" />
              取消投票
            </UButton>

            <div class="flex gap-3">
              <UButton
                variant="outline"
                @click="closeVotingModal"
              >
                取消
              </UButton>
              <UButton
                color="green"
                @click="confirmVote"
                :disabled="!selectedVote"
              >
                <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
                確認
              </UButton>
            </div>
          </div>
        </div>
      </UCard>
    </UModal>

    <!-- Cancel Vote Confirmation Modal -->
    <UModal v-model="showCancelModal" :ui="{ width: 'max-w-md' }">
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900">確認取消投票</h3>
        </template>

        <div class="p-6">
          <p class="text-gray-700 mb-6">
            確定要取消 {{ selectedVoter?.name }} 的投票嗎？此操作將清除該投票人的投票記錄。
          </p>

          <div class="flex justify-end gap-3">
            <UButton variant="outline" @click="showCancelModal = false">
              取消
            </UButton>
            <UButton color="red" @click="confirmCancelVote">
              確定取消投票
            </UButton>
          </div>
        </div>
      </UCard>
    </UModal>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

const route = useRoute()
const router = useRouter()
const meetingId = route.params.meetingId
const topicId = route.params.topicId

// API composables
const { getMeeting } = useMeetings()
const { getVotingTopic } = useVotingTopics()
const { getVotes, vote: submitVote, removeVote } = useVoting()
const { showSuccess, showError } = useSweetAlert()

// Loading states
const isLoading = ref(false)
const isVoting = ref(false)

// Modal states
const showVotingModal = ref(false)
const showCancelModal = ref(false)
const selectedVoter = ref(null)
const selectedVote = ref('')

// Page data
const renewalGroupName = ref('')
const meetingName = ref('')
const topicName = ref('')
const votingTime = ref('')

// Voters data
const voters = ref([])

// Load data on mount
onMounted(async () => {
  await loadTopicInfo()
  await loadVoters()
})

// Load topic info
const loadTopicInfo = async () => {
  console.log('[Voting] Loading topic info:', topicId)

  const [meetingResponse, topicResponse] = await Promise.all([
    getMeeting(meetingId),
    getVotingTopic(topicId)
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
  } else {
    console.error('[Voting] Failed to load topic info:', topicResponse.error)
    showError('載入失敗', topicResponse.error?.message || '無法載入投票主題資料')
    renewalGroupName.value = ''
    meetingName.value = ''
    topicName.value = ''
    votingTime.value = ''
  }
}

// Load voters
const loadVoters = async () => {
  isLoading.value = true
  console.log('[Voting] Loading voters for topic:', topicId)

  const response = await getVotes({ topic_id: topicId })
  isLoading.value = false

  if (response.success && response.data) {
    const votesData = response.data.data || response.data

    voters.value = Array.isArray(votesData) ? votesData.map(v => ({
      id: v.voter_id || v.id,
      name: v.voter_name || v.name || '',
      ownerCode: v.owner_code || '',
      hasVoted: v.has_voted || v.hasVoted || false,
      vote: v.vote || null
    })) : []

    console.log('[Voting] Voters loaded:', voters.value.length)
  } else {
    console.error('[Voting] Failed to load voters:', response.error)
    showError('載入失敗', response.error?.message || '無法載入投票人資料')
    voters.value = []
  }
}

const goBack = () => {
  router.push(`/tables/meeting/${meetingId}/voting-topics`)
}

const openVotingModal = (voter) => {
  if (voter.hasVoted) return

  selectedVoter.value = voter
  selectedVote.value = ''
  showVotingModal.value = true
}

const closeVotingModal = () => {
  showVotingModal.value = false
  selectedVoter.value = null
  selectedVote.value = ''
}

const selectVote = (vote) => {
  selectedVote.value = vote
}

const confirmVote = async () => {
  if (!selectedVote.value || !selectedVoter.value) return

  isVoting.value = true

  const voteData = {
    topic_id: topicId,
    voter_id: selectedVoter.value.id,
    vote: selectedVote.value
  }

  console.log('[Voting] Submitting vote:', voteData)

  const response = await submitVote(voteData)
  isVoting.value = false

  if (response.success) {
    // Update voter status locally
    selectedVoter.value.hasVoted = true
    selectedVoter.value.vote = selectedVote.value

    showSuccess('投票成功', `已為 ${selectedVoter.value.name} 記錄投票`)
    console.log('[Voting] Vote submitted successfully')
    closeVotingModal()
  } else {
    console.error('[Voting] Failed to submit vote:', response.error)
    showError('投票失敗', response.error?.message || '無法記錄投票')
  }
}

const cancelVote = () => {
  if (!selectedVoter.value.hasVoted) {
    closeVotingModal()
    return
  }

  showCancelModal.value = true
}

const confirmCancelVote = async () => {
  if (!selectedVoter.value) return

  isVoting.value = true

  const voteData = {
    topic_id: topicId,
    voter_id: selectedVoter.value.id
  }

  console.log('[Voting] Cancelling vote:', voteData)

  const response = await removeVote(voteData)
  isVoting.value = false

  if (response.success) {
    // Update voter status locally
    selectedVoter.value.hasVoted = false
    selectedVoter.value.vote = null

    showSuccess('取消成功', `已取消 ${selectedVoter.value.name} 的投票`)
    console.log('[Voting] Vote cancelled successfully')
  } else {
    console.error('[Voting] Failed to cancel vote:', response.error)
    showError('取消失敗', response.error?.message || '無法取消投票')
  }

  showCancelModal.value = false
  closeVotingModal()
}
</script>