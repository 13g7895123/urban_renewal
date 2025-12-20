<template>
  <NuxtLayout name="main">
    <template #title>共有部分資料管理</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:building-library" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">共有部分列表</h2>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 mb-6">
        <button
          @click="goBack"
          class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:arrow-left" class="w-5 h-5 mr-2" />
          回上一頁
        </button>
        <button
          @click="createJointArea"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新增共有部分
        </button>
      </div>

      <!-- Joint Common Areas Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="p-4 text-left text-sm font-medium text-green-600">共有部分建號</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">共有部份建物總面積(平方公尺)</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">共有部份對應建物建號</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">共有部份對應建物建號</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="4" class="p-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    載入中...
                  </div>
                </td>
              </tr>
              <tr v-else-if="jointAreas.length === 0">
                <td colspan="4" class="p-8 text-center text-gray-500">
                  暫無資料，請點擊「新增共有部分」新增資料
                </td>
              </tr>
              <tr v-for="(area, index) in jointAreas" :key="area.id || index" class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                <td class="p-4 text-sm text-gray-900">{{ area.building_number || '-' }}</td>
                <td class="p-4 text-sm text-gray-900">{{ area.total_area || '-' }}</td>
                <td class="p-4 text-sm text-gray-900">{{ area.corresponding_building_number_1 || '-' }}</td>
                <td class="p-4 text-sm text-gray-900">{{ area.corresponding_building_number_2 || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center p-4 border-t border-gray-200">
          <div class="text-sm text-gray-500 flex items-center">
            每頁顯示：
            <select
              v-model="pageSize"
              class="ml-2 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
            </select>
          </div>
          <div class="text-sm text-gray-500">
            {{ jointAreas.length > 0 ? `1-${jointAreas.length} 共 ${jointAreas.length}` : '0-0 共 0' }}
          </div>
          <div class="flex gap-1 items-center">
            <button
              disabled
              class="p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"
            >
              <Icon name="heroicons:chevron-left" class="w-4 h-4" />
            </button>
            <button class="px-3 py-2 text-sm text-white bg-blue-500 rounded font-medium">1</button>
            <button
              disabled
              class="p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"
            >
              <Icon name="heroicons:chevron-right" class="w-4 h-4" />
            </button>

            <!-- Refresh Button -->
            <button
              @click="refreshData"
              :disabled="loading"
              class="ml-2 p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              title="重新整理"
            >
              <Icon name="heroicons:arrow-path" :class="['w-4 h-4', { 'animate-spin': loading }]" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

// Use composables
const { get } = useApi()
const route = useRoute()
const router = useRouter()

// Get urban renewal ID from route
const urbanRenewalId = computed(() => route.params.id)

const pageSize = ref(10)
const loading = ref(false)
const jointAreas = ref([])

// API Functions
const fetchJointAreas = async () => {
  loading.value = true

  try {
    console.log('[Joint Common Areas] Fetching from:', `/urban-renewals/${urbanRenewalId.value}/joint-common-areas`)

    const response = await get(`/urban-renewals/${urbanRenewalId.value}/joint-common-areas`)

    if (response.success && response.data.status === 'success') {
      console.log('[Joint Common Areas] API Response Data:', response.data)
      jointAreas.value = response.data.data || []
      console.log('[Joint Common Areas] Data loaded:', jointAreas.value.length, 'records')
      if (jointAreas.value.length > 0) {
        console.log('[Joint Common Areas] First record:', jointAreas.value[0])
      }
    } else {
      console.error('Failed to fetch joint common areas:', response.data?.message || response.error?.message)
      jointAreas.value = []
    }
  } catch (err) {
    console.error('[Joint Common Areas] Fetch error:', err)
    console.error('[Joint Common Areas] Error details:', err.data || err.message)
    jointAreas.value = []
  } finally {
    loading.value = false
  }
}

// Refresh data function
const refreshData = async () => {
  await fetchJointAreas()
}

// UI Functions
const goBack = () => {
  router.push('/tables/urban-renewal')
}

const createJointArea = () => {
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/joint-common-areas/create`)
}

// Load data when component mounts
onMounted(() => {
  fetchJointAreas()
})
</script>
