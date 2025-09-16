<template>
  <NuxtLayout name="main">
    <template #title>開始投票</template>

    <div class="p-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ renewalGroupName }}</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ meetingName }}</h2>

        <!-- Topic and Time -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-2">{{ topicName }}</h3>
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
          <!-- Two-digit Number -->
          <div class="text-2xl font-bold text-gray-700 mb-2">
            {{ String(index + 1).padStart(2, '0') }}
          </div>

          <!-- Voter Name -->
          <div
            class="text-sm font-medium truncate"
            :class="{
              'text-gray-400 bg-gray-200 px-2 py-1 rounded': voter.hasVoted,
              'text-gray-900': !voter.hasVoted
            }"
          >
            {{ voter.name }}
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
import { ref } from 'vue'

definePageMeta({
  layout: false
})

const route = useRoute()
const router = useRouter()
const meetingId = route.params.meetingId
const topicId = route.params.topicId

// Modal states
const showVotingModal = ref(false)
const showCancelModal = ref(false)
const selectedVoter = ref(null)
const selectedVote = ref('')

// Page data
const renewalGroupName = ref('臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會')
const meetingName = ref('114年度第一屆第1次會員大會')
const topicName = ref('理事長選舉')
const votingTime = ref('2025年3月15日 下午2:30:00')

// Sample voters data
const voters = ref([
  { id: 1, name: '王小明', hasVoted: false, vote: null },
  { id: 2, name: '李小華', hasVoted: true, vote: 'agree' },
  { id: 3, name: '張小美', hasVoted: false, vote: null },
  { id: 4, name: '陳小強', hasVoted: false, vote: null },
  { id: 5, name: '林小芳', hasVoted: true, vote: 'disagree' },
  { id: 6, name: '黃小龍', hasVoted: false, vote: null },
  { id: 7, name: '吳小玲', hasVoted: false, vote: null },
  { id: 8, name: '劉小軍', hasVoted: false, vote: null },
  { id: 9, name: '蔡小慧', hasVoted: false, vote: null },
  { id: 10, name: '鄭小勇', hasVoted: false, vote: null },
  { id: 11, name: '謝小文', hasVoted: false, vote: null },
  { id: 12, name: '胡小雯', hasVoted: false, vote: null },
  { id: 13, name: '馬小峰', hasVoted: false, vote: null },
  { id: 14, name: '楊小琪', hasVoted: false, vote: null },
  { id: 15, name: '孫小宇', hasVoted: false, vote: null },
  { id: 16, name: '許小蓮', hasVoted: false, vote: null },
  { id: 17, name: '蘇小偉', hasVoted: false, vote: null },
  { id: 18, name: '袁小雅', hasVoted: false, vote: null },
  { id: 19, name: '鍾小浩', hasVoted: false, vote: null },
  { id: 20, name: '江小薇', hasVoted: false, vote: null }
])

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

const confirmVote = () => {
  if (!selectedVote.value || !selectedVoter.value) return

  // Update voter status
  selectedVoter.value.hasVoted = true
  selectedVoter.value.vote = selectedVote.value

  console.log(`Voter ${selectedVoter.value.name} voted: ${selectedVote.value}`)

  // TODO: Send vote to backend

  closeVotingModal()
}

const cancelVote = () => {
  if (!selectedVoter.value.hasVoted) {
    closeVotingModal()
    return
  }

  showCancelModal.value = true
}

const confirmCancelVote = () => {
  if (selectedVoter.value) {
    selectedVoter.value.hasVoted = false
    selectedVoter.value.vote = null

    console.log(`Cancelled vote for ${selectedVoter.value.name}`)

    // TODO: Remove vote from backend
  }

  showCancelModal.value = false
  closeVotingModal()
}
</script>