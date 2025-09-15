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
          @click="addTopic"
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
                <td class="p-3 text-sm text-center">{{ topic.winnerCount }}</td>
                <td class="p-3 text-sm text-center">{{ topic.backupCount }}</td>
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
              <tr v-if="votingTopics.length === 0">
                <td colspan="6" class="p-8 text-center text-gray-500">暫無投票議題資料</td>
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

    <!-- Other Options Modal -->
    <UModal v-model="showOtherModal">
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

        <div class="space-y-4 p-6">
          <UButton
            color="blue"
            class="w-full"
            @click="exportVotingResults"
          >
            <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
            匯出投票結果
          </UButton>
          <UButton
            color="red"
            class="w-full"
            @click="deleteTopic"
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
import { useRoute, useRouter } from 'vue-router'

definePageMeta({
  layout: false
})

const route = useRoute()
const router = useRouter()

const meetingId = route.params.id
const pageSize = ref(10)
const showOtherModal = ref(false)
const selectedTopic = ref(null)

const votingTopics = ref([
  {
    id: 1,
    name: '理事會選舉',
    meetingName: '114年度第一屆第1次會員大會',
    maxSelections: 3,
    winnerCount: 3,
    backupCount: 2,
    meetingId: meetingId
  },
  {
    id: 2,
    name: '監事會選舉',
    meetingName: '114年度第一屆第1次會員大會',
    maxSelections: 2,
    winnerCount: 2,
    backupCount: 1,
    meetingId: meetingId
  }
])

const goBack = () => {
  router.push('/tables/meeting')
}

const addTopic = () => {
  router.push(`/tables/meeting/${meetingId}/voting-topics/create`)
}

const showBasicInfo = (topic) => {
  router.push(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/basic-info`)
}

const startVoting = (topic) => {
  router.push(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/voting`)
}

const showVotingResults = (topic) => {
  // Open in new tab
  window.open(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/results`, '_blank')
}

const showOtherOptions = (topic) => {
  selectedTopic.value = topic
  showOtherModal.value = true
}

const exportVotingResults = () => {
  console.log('Exporting voting results for topic:', selectedTopic.value)
  // TODO: Implement export functionality
  showOtherModal.value = false
}

const deleteTopic = () => {
  console.log('Deleting topic:', selectedTopic.value)
  // TODO: Implement delete functionality with confirmation
  showOtherModal.value = false
}

onMounted(() => {
  console.log('Voting topics page loaded for meeting:', meetingId)
  // TODO: Load voting topics from API
})
</script>