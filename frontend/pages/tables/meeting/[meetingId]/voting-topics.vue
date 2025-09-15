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

    <!-- Basic Info Modal -->
    <UModal v-model="showBasicInfoModal" :ui="{ width: 'max-w-6xl' }">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ selectedTopic ? '投票議題基本資料' : '新增投票議題' }}
            </h3>
            <div class="flex items-center gap-2">
              <UButton
                v-if="!selectedTopic"
                color="blue"
                size="sm"
                @click="fillVotingTopicTestData"
              >
                <Icon name="heroicons:beaker" class="w-4 h-4 mr-1" />
                填入測試資料
              </UButton>
              <UButton
                variant="ghost"
                icon="i-heroicons-x-mark-20-solid"
                @click="showBasicInfoModal = false"
              />
            </div>
          </div>
        </template>

        <div class="space-y-6 p-6">
          <!-- Basic Topic Info -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 所屬會議 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">所屬會議</label>
              <UInput :value="meetingInfo?.name" readonly class="bg-gray-50" />
            </div>

            <!-- 會議日期時間 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">會議日期時間</label>
              <UInput :value="meetingInfo?.dateTime" readonly class="bg-gray-50" />
            </div>

            <!-- 所屬更新會 (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">所屬更新會</label>
              <UInput :value="meetingInfo?.renewalGroup" readonly class="bg-gray-50" />
            </div>
          </div>

          <!-- 議題名稱 -->
          <div v-if="!selectedTopic">
            <label class="block text-sm font-medium text-gray-700 mb-2">議題名稱</label>
            <UInput v-model="topicName" placeholder="請輸入議題名稱" />
          </div>

          <!-- 匿名與否 -->
          <div class="space-y-3">
            <label class="block text-sm font-medium text-gray-700">匿名與否</label>
            <div class="flex gap-6">
              <div class="flex items-center">
                <input
                  type="radio"
                  id="not-anonymous"
                  v-model="isAnonymous"
                  :value="false"
                  class="w-4 h-4 text-blue-600"
                >
                <label for="not-anonymous" class="ml-2 text-sm text-gray-700">不匿名</label>
              </div>
              <div class="flex items-center">
                <input
                  type="radio"
                  id="anonymous"
                  v-model="isAnonymous"
                  :value="true"
                  class="w-4 h-4 text-blue-600"
                >
                <label for="anonymous" class="ml-2 text-sm text-gray-700">匿名</label>
              </div>
            </div>
          </div>

          <!-- 所有權人 Group -->
          <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-lg font-medium text-gray-900">所有權人</h4>
              <UButton color="blue" size="sm" @click="importPropertyOwners">
                <Icon name="heroicons:arrow-down-tray" class="w-4 h-4 mr-2" />
                匯入所有權人
              </UButton>
            </div>

            <div class="border border-gray-200 rounded-lg">
              <div class="max-h-64 overflow-y-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">編號</th>
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">選項名稱(所有權人)</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">置頂</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="propertyOwners.length === 0">
                      <td colspan="4" class="px-4 py-8 text-center text-gray-500">請點擊"匯入所有權人"或手動新增</td>
                    </tr>
                    <tr v-for="(owner, index) in propertyOwners" :key="index" class="border-b border-gray-100">
                      <td class="px-4 py-2 text-center">{{ index + 1 }}</td>
                      <td class="px-4 py-2">
                        <UInput v-model="owner.name" size="sm" placeholder="所有權人姓名" />
                      </td>
                      <td class="px-4 py-2 text-center">
                        <UCheckbox v-model="owner.isPinned" />
                      </td>
                      <td class="px-4 py-2 text-center">
                        <div class="flex gap-1 justify-center">
                          <UButton color="green" size="xs" @click="addPropertyOwner(index)">新增選項</UButton>
                          <UButton color="red" size="xs" @click="removePropertyOwner(index)">刪除</UButton>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Voting Settings -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">最大有效圈選數</label>
              <UInput v-model="maxSelections" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">正取數量</label>
              <UInput v-model="acceptedCount" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">備取數量</label>
              <UInput v-model="alternateCount" type="number" />
            </div>
          </div>

          <!-- Pass Conditions -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Land Area -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件土地面積比例分子</label>
              <UInput v-model="landAreaRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件土地面積比例分母</label>
              <UInput v-model="landAreaRatioDenominator" type="number" />
            </div>

            <!-- Building Area -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件建物面積比例分子</label>
              <UInput v-model="buildingAreaRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件建物面積比例分母</label>
              <UInput v-model="buildingAreaRatioDenominator" type="number" />
            </div>

            <!-- People Count -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件人數比例分子</label>
              <UInput v-model="peopleRatioNumerator" type="number" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">通過條件人數比例分母</label>
              <UInput v-model="peopleRatioDenominator" type="number" />
            </div>
          </div>

          <!-- 備註 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">備註</label>
            <UTextarea v-model="remarks" rows="3" />
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between pt-6 border-t border-gray-200">
            <div class="flex gap-3">
              <UButton v-if="selectedTopic" color="green" @click="exportVotingBallot">
                <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
                匯出投票單
              </UButton>
            </div>
            <div class="flex gap-3">
              <UButton variant="outline" @click="showBasicInfoModal = false">
                回上一頁
              </UButton>
              <UButton color="green" @click="saveBasicInfo">
                <Icon name="heroicons:check" class="w-4 h-4 mr-2" />
                儲存
              </UButton>
            </div>
          </div>
        </div>
      </UCard>
    </UModal>

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
import { ref } from 'vue'

definePageMeta({
  layout: false
})

const route = useRoute()
const router = useRouter()
const meetingId = route.params.meetingId

const pageSize = ref(10)

// Modal states
const showBasicInfoModal = ref(false)
const showOtherModal = ref(false)
const selectedTopic = ref(null)

// Meeting info (readonly fields)
const meetingInfo = ref({
  name: '114年度第一屆第1次會員大會',
  dateTime: '2025年3月15日 下午2:00:00',
  renewalGroup: '臺北市南港區玉成段二小段435地號等17筆土地更新事宜臺北市政府會'
})

// Basic info form fields
const topicName = ref('')
const isAnonymous = ref(false)
const maxSelections = ref(1)
const acceptedCount = ref(1)
const alternateCount = ref(0)
const landAreaRatioNumerator = ref(0)
const landAreaRatioDenominator = ref(0)
const buildingAreaRatioNumerator = ref(0)
const buildingAreaRatioDenominator = ref(0)
const peopleRatioNumerator = ref(0)
const peopleRatioDenominator = ref(0)
const remarks = ref('')

// Property owners table
const propertyOwners = ref([
  { name: '', isPinned: false },
  { name: '', isPinned: false }
])

const votingTopics = ref([
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
])

const goBack = () => {
  router.push('/tables/meeting')
}

const addVotingTopic = () => {
  console.log('Adding new voting topic')
  selectedTopic.value = null

  // Reset all form fields
  topicName.value = ''
  isAnonymous.value = false
  maxSelections.value = 1
  acceptedCount.value = 1
  alternateCount.value = 0
  landAreaRatioNumerator.value = 0
  landAreaRatioDenominator.value = 0
  buildingAreaRatioNumerator.value = 0
  buildingAreaRatioDenominator.value = 0
  peopleRatioNumerator.value = 0
  peopleRatioDenominator.value = 0
  remarks.value = ''
  propertyOwners.value = [
    { name: '', isPinned: false },
    { name: '', isPinned: false }
  ]

  showBasicInfoModal.value = true
}

const showBasicInfo = (topic) => {
  console.log('Showing basic info for topic:', topic)
  selectedTopic.value = topic

  // Load existing data
  topicName.value = topic.name
  maxSelections.value = topic.maxSelections
  acceptedCount.value = topic.acceptedCount
  alternateCount.value = topic.alternateCount

  showBasicInfoModal.value = true
}

const startVoting = (topic) => {
  console.log('Starting voting for topic:', topic)
  router.push(`/tables/meeting/${meetingId}/voting-topics/${topic.id}/voting`)
}

const showVotingResults = (topic) => {
  console.log('Showing voting results for topic:', topic)
  const resultsUrl = `/tables/meeting/${meetingId}/voting-topics/${topic.id}/results`
  window.open(resultsUrl, '_blank')
}

const showOtherOptions = (topic) => {
  console.log('Showing other options for topic:', topic)
  selectedTopic.value = topic
  showOtherModal.value = true
}

// Property owners management
const addPropertyOwner = (index) => {
  propertyOwners.value.splice(index + 1, 0, { name: '', isPinned: false })
}

const removePropertyOwner = (index) => {
  if (propertyOwners.value.length > 1) {
    propertyOwners.value.splice(index, 1)
  }
}

const importPropertyOwners = () => {
  console.log('Importing property owners from meeting')
  // TODO: Implement import functionality
  // This should fetch property owners from the meeting and populate the table
}

// Export functions
const exportVotingBallot = () => {
  console.log('Exporting voting ballot for topic:', selectedTopic.value)
  // TODO: Implement export voting ballot functionality
}

const exportVotingResultsAction = () => {
  console.log('Exporting voting results for topic:', selectedTopic.value)
  showOtherModal.value = false
  // TODO: Implement export voting results functionality
}

const deleteVotingTopic = () => {
  console.log('Deleting voting topic:', selectedTopic.value)
  showOtherModal.value = false
  // TODO: Implement delete functionality with confirmation
}

// Fill voting topic test data
const fillVotingTopicTestData = () => {
  const testTopicNames = [
    '理事長選舉',
    '更新計畫同意案',
    '權利變換同意案',
    '工程發包案',
    '預算審核案',
    '委託實施者同意案',
    '都市計畫變更同意案',
    '分配計畫確認案'
  ]

  const testPropertyOwnerNames = [
    '王大明', '李美華', '張志強', '陳淑芬', '林建國',
    '吳雅文', '劉明德', '黃秀琴', '鄭文山', '謝美玲',
    '蔡志豪', '許雅雯', '馬建華', '楊淑惠', '孫明光'
  ]

  // Fill basic topic info
  topicName.value = testTopicNames[Math.floor(Math.random() * testTopicNames.length)]
  isAnonymous.value = Math.random() > 0.5
  maxSelections.value = Math.floor(Math.random() * 3) + 1
  acceptedCount.value = Math.floor(Math.random() * 5) + 1
  alternateCount.value = Math.floor(Math.random() * 3)

  // Fill pass conditions
  landAreaRatioNumerator.value = Math.floor(Math.random() * 30) + 50
  landAreaRatioDenominator.value = 100
  buildingAreaRatioNumerator.value = Math.floor(Math.random() * 30) + 50
  buildingAreaRatioDenominator.value = 100
  peopleRatioNumerator.value = Math.floor(Math.random() * 30) + 50
  peopleRatioDenominator.value = 100

  // Fill property owners
  const ownerCount = Math.floor(Math.random() * 8) + 3 // 3-10 owners
  propertyOwners.value = []

  for (let i = 0; i < ownerCount; i++) {
    const randomName = testPropertyOwnerNames[Math.floor(Math.random() * testPropertyOwnerNames.length)]
    propertyOwners.value.push({
      name: randomName,
      isPinned: Math.random() > 0.8 // 20% chance to be pinned
    })
  }

  // Fill remarks
  remarks.value = `${topicName.value}相關事宜，需要所有權人投票決議。測試資料自動生成。`

  console.log('Voting topic test data filled successfully')
}

// Save function
const saveBasicInfo = () => {
  if (selectedTopic.value) {
    // Editing existing topic
    console.log('Updating existing topic:', selectedTopic.value)
  } else {
    // Creating new topic
    console.log('Creating new voting topic')
    console.log('Form data:', {
      topicName: topicName.value,
      isAnonymous: isAnonymous.value,
      maxSelections: maxSelections.value,
      acceptedCount: acceptedCount.value,
      alternateCount: alternateCount.value,
      propertyOwners: propertyOwners.value,
      remarks: remarks.value
    })
  }
  showBasicInfoModal.value = false
}
</script>