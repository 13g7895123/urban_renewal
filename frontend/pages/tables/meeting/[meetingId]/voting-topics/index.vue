<template>
  <NuxtLayout name="main">
    <template #title>投票議題管理</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:chart-bar" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">投票議題</h2>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 mb-6">
        <UButton
          variant="outline"
          @click="goBack"
        >
          <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
          回上一頁
        </UButton>
        <UButton
          color="green"
          @click="addVotingTopic"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新增議題
        </UButton>
      </div>

      <!-- Voting Topics Table -->
      <UCard>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="p-3 text-left text-sm font-medium text-green-600">議題名稱</th>
                <th class="p-3 text-left text-sm font-medium text-green-600">所屬會議</th>
                <th class="p-3 text-center text-sm font-medium text-green-600">最大有效圈選數</th>
                <th class="p-3 text-center text-sm font-medium text-green-600">正取數量</th>
                <th class="p-3 text-center text-sm font-medium text-green-600">備取數量</th>
                <th class="p-3 text-center text-sm font-medium text-green-600">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(topic, index) in votingTopics" :key="index" class="border-b hover:bg-gray-50">
                <td class="p-3 text-sm">{{ topic.name }}</td>
                <td class="p-3 text-sm">{{ topic.meetingName }}</td>
                <td class="p-3 text-sm text-center">{{ topic.maxSelections }}</td>
                <td class="p-3 text-sm text-center">{{ topic.acceptedCount }}</td>
                <td class="p-3 text-sm text-center">{{ topic.alternateCount }}</td>
                <td class="p-3 text-center">
                  <div class="flex flex-wrap gap-1 justify-center">
                    <UButton
                      color="green"
                      size="xs"
                      @click="showBasicInfo(topic)"
                    >
                      <span class="text-white">基本資料</span>
                    </UButton>
                    <UButton
                      color="blue"
                      size="xs"
                      @click="startVoting(topic)"
                    >
                      <span class="text-white">開始投票</span>
                    </UButton>
                    <UButton
                      color="purple"
                      size="xs"
                      @click="showVotingResults(topic)"
                    >
                      <span class="text-white">投票結果</span>
                    </UButton>
                    <UButton
                      color="gray"
                      size="xs"
                      @click="showOtherOptions(topic)"
                    >
                      <span class="text-white">其他</span>
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
            1-{{ votingTopics.length }} 共 {{ votingTopics.length }}
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


    <!-- Other Options Dropdown -->
    <UModal v-model="showOtherModal" :ui="{ width: 'max-w-md' }">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">其他選項</h3>
            <UButton
              variant="ghost"
              icon="i-heroicons-x-mark-20-solid"
              @click="showOtherModal = false"
            />
          </div>
        </template>

        <div class="p-6 space-y-4">
          <UButton
            block
            color="green"
            @click="exportVotingResultsAction"
          >
            <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
            匯出投票結果
          </UButton>
          <UButton
            block
            color="red"
            @click="deleteVotingTopic"
          >
            <Icon name="heroicons:trash" class="w-4 h-4 mr-2" />
            刪除
          </UButton>
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

// API composables
const { getVotingTopics, deleteVotingTopic: deleteTopicApi, exportVotingResults } = useVotingTopics()
const { showSuccess, showError, showConfirm } = useSweetAlert()

const pageSize = ref(10)
const isLoading = ref(false)
const isDeleting = ref(false)

// Modal states
const showOtherModal = ref(false)
const selectedTopic = ref(null)

// Voting topics data
const votingTopics = ref([])

// Load data on mount
onMounted(async () => {
  await loadVotingTopics()
})

// Load voting topics
const loadVotingTopics = async () => {
  isLoading.value = true
  console.log('[Voting Topics] Loading voting topics for meeting:', meetingId)

  const response = await getVotingTopics({ meeting_id: meetingId })
  isLoading.value = false

  if (response.success && response.data) {
    const topicsData = response.data.data || response.data

    votingTopics.value = Array.isArray(topicsData) ? topicsData.map(t => ({
      id: t.id,
      name: t.topic_name || t.name || '',
      meetingName: t.meeting_name || t.meetingName || '',
      maxSelections: t.max_selections || t.maxSelections || 0,
      acceptedCount: t.accepted_count || t.acceptedCount || 0,
      alternateCount: t.alternate_count || t.alternateCount || 0
    })) : []

    console.log('[Voting Topics] Voting topics loaded:', votingTopics.value.length)
  } else {
    console.error('[Voting Topics] Failed to load voting topics:', response.error)
    showError('載入失敗', response.error?.message || '無法載入投票議題')
    // Use fallback mock data
    votingTopics.value = [
      {
        id: 1,
        name: '理事長選舉',
        meetingName: '114年度第一屆第1次會員大會',
        maxSelections: 1,
        acceptedCount: 1,
        alternateCount: 0
      },
      {
        id: 2,
        name: '更新計畫同意案',
        meetingName: '114年度第一屆第1次會員大會',
        maxSelections: 1,
        acceptedCount: 1,
        alternateCount: 0
      }
    ]
  }
}

const goBack = () => {
  router.push('/tables/meeting')
}

const addVotingTopic = () => {
  console.log('Adding new voting topic')
  router.push(`/tables/meeting/${meetingId}/voting-topics/new/basic-info`)
}

const showBasicInfo = (topic) => {
  console.log('Navigating to basic info for topic:', topic)
  router.push(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/basic-info`)
}

const startVoting = (topic) => {
  console.log('Starting voting for topic:', topic)
  router.push(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/voting`)
}

const showVotingResults = (topic) => {
  console.log('Navigating to voting results for topic:', topic)
  router.push(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/results`)
}

const showOtherOptions = (topic) => {
  console.log('Showing other options for topic:', topic)
  selectedTopic.value = topic
  showOtherModal.value = true
}


const exportVotingResultsAction = async () => {
  if (!selectedTopic.value) return

  console.log('[Voting Topics] Exporting results for topic:', selectedTopic.value)

  const response = await exportVotingResults(selectedTopic.value.id)

  if (response.success) {
    showSuccess('匯出成功', '投票結果已匯出')
    // TODO: Handle file download
  } else {
    console.error('[Voting Topics] Failed to export results:', response.error)
    showError('匯出失敗', response.error?.message || '無法匯出投票結果')
  }

  showOtherModal.value = false
}

const deleteVotingTopic = async () => {
  if (!selectedTopic.value) return

  showOtherModal.value = false

  const confirmed = await showConfirm(
    '確認刪除',
    `確定要刪除投票議題「${selectedTopic.value.name}」嗎？此操作無法復原。`
  )

  if (!confirmed) {
    return
  }

  isDeleting.value = true
  console.log('[Voting Topics] Deleting topic:', selectedTopic.value)

  const response = await deleteTopicApi(selectedTopic.value.id)
  isDeleting.value = false

  if (response.success) {
    showSuccess('刪除成功', '投票議題已刪除')
    await loadVotingTopics()
  } else {
    console.error('[Voting Topics] Failed to delete topic:', response.error)
    showError('刪除失敗', response.error?.message || '無法刪除投票議題')
  }
}


</script>