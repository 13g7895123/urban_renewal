<template>
  <NuxtLayout name="main">
    <template #title>所有權人管理</template>

    <div class="p-8">
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
          @click="exportOwners"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:document-arrow-down" class="w-5 h-5 mr-2" />
          匯出所有權人資料
        </button>
        <button
          @click="importOwners"
          class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:document-arrow-up" class="w-5 h-5 mr-2" />
          匯入所有權人
        </button>
        <button
          @click="createOwner"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新增所有權人
        </button>
      </div>

      <!-- Property Owners Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="p-4 text-left text-sm font-medium text-green-600">所有權人編號</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">所有權人名稱</th>
                <th class="p-4 text-center text-sm font-medium text-green-600">土地總面積(平方公尺)</th>
                <th class="p-4 text-center text-sm font-medium text-green-600">建物總面積(平方公尺)</th>
                <th class="p-4 text-center text-sm font-medium text-green-600">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="p-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    載入中...
                  </div>
                </td>
              </tr>
              <tr v-else-if="propertyOwners.length === 0">
                <td colspan="5" class="p-8 text-center text-gray-500">
                  暫無資料，請點擊「新增所有權人」新增資料
                </td>
              </tr>
              <tr v-for="(owner, index) in propertyOwners" :key="owner.id || index" class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                <td class="p-4 text-sm text-gray-900">{{ owner.owner_code }}</td>
                <td class="p-4 text-sm text-gray-900">{{ owner.name }}</td>
                <td class="p-4 text-sm text-gray-900 text-center">{{ owner.total_land_area || '0.00' }}</td>
                <td class="p-4 text-sm text-gray-900 text-center">{{ owner.total_building_area || '0.00' }}</td>
                <td class="p-4 text-center">
                  <div class="flex justify-center gap-2 flex-wrap">
                    <button
                      @click="viewOwnerDetails(owner)"
                      class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"
                    >
                      基本資料
                    </button>
                    <button
                      @click="deleteOwner(owner)"
                      class="px-2 py-1 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded transition-colors duration-200"
                    >
                      刪除
                    </button>
                  </div>
                </td>
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
            {{ propertyOwners.length > 0 ? `1-${propertyOwners.length} 共 ${propertyOwners.length}` : '0-0 共 0' }}
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
import { ref, onMounted, watch, computed } from 'vue'

// Use SweetAlert2
const { $swal } = useNuxtApp()

const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()

// Get urban renewal ID from route (reactive)
const urbanRenewalId = computed(() => route.params.id)

const pageSize = ref(10)
const loading = ref(false)
const propertyOwners = ref([])

// API Functions
const fetchPropertyOwners = async () => {
  loading.value = true

  try {
    // Use direct URL for development - check multiple conditions
    const isDev = process.dev || process.env.NODE_ENV === 'development'
    const baseURL = isDev ? 'http://localhost:9228' : (runtimeConfig.public.apiBaseUrl || '')

    console.log('[Property Owners] Fetching from:', `${baseURL}/api/urban-renewals/${urbanRenewalId.value}/property-owners`)

    const response = await $fetch(`/api/urban-renewals/${urbanRenewalId.value}/property-owners`, {
      baseURL
    })

    if (response.status === 'success') {
      propertyOwners.value = response.data || []
      console.log('[Property Owners] Data loaded:', propertyOwners.value.length, 'records')
    } else {
      console.error('Failed to fetch property owners:', response.message)
      propertyOwners.value = []
    }
  } catch (err) {
    console.error('[Property Owners] Fetch error:', err)
    console.error('[Property Owners] Error details:', err.data || err.message)

    // Show more detailed error message
    const errorMessage = err.data?.message || err.message || '無法載入所有權人資料'
    $swal.fire({
      title: '載入失敗',
      text: errorMessage,
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
    propertyOwners.value = []
  } finally {
    loading.value = false
  }
}

// Refresh data function
const refreshData = async () => {
  await fetchPropertyOwners()
}

const deletePropertyOwner = async (id) => {
  try {
    // Use direct URL for development - check multiple conditions
    const isDev = process.dev || process.env.NODE_ENV === 'development'
    const baseURL = isDev ? 'http://localhost:9228' : (runtimeConfig.public.apiBaseUrl || '')

    const response = await $fetch(`/api/property-owners/${id}`, {
      method: 'DELETE',
      baseURL
    })

    return response
  } catch (err) {
    console.error('Delete error:', err)
    throw new Error(err.data?.message || '刪除失敗')
  }
}

// UI Functions
const goBack = () => {
  router.push('/tables/urban-renewal')
}

const exportOwners = () => {
  console.log('Exporting property owners')
  // TODO: Implement export functionality
  $swal.fire({
    title: '功能開發中',
    text: '匯出功能正在開發中',
    icon: 'info',
    confirmButtonText: '確定',
    confirmButtonColor: '#6b7280'
  })
}

const importOwners = () => {
  console.log('Importing property owners')
  // TODO: Implement import functionality
  $swal.fire({
    title: '功能開發中',
    text: '匯入功能正在開發中',
    icon: 'info',
    confirmButtonText: '確定',
    confirmButtonColor: '#6b7280'
  })
}

const createOwner = () => {
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners/create`)
}

const viewOwnerDetails = (owner) => {
  console.log('[Property Owners] View owner details clicked:', owner)

  if (!owner || !owner.id) {
    console.error('[Property Owners] Owner or owner.id is missing:', owner)
    $swal.fire({
      title: '錯誤',
      text: '無法取得所有權人資料',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
    return
  }

  console.log('[Property Owners] Navigating to:', `/tables/urban-renewal/${urbanRenewalId.value}/property-owners/${owner.id}/edit`)
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners/${owner.id}/edit`)
}

const deleteOwner = async (owner) => {
  const result = await $swal.fire({
    title: '確認刪除',
    text: `確定要刪除「${owner.owner_name}」嗎？`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: '確定刪除',
    cancelButtonText: '取消'
  })

  if (!result.isConfirmed) {
    return
  }

  try {
    const response = await deletePropertyOwner(owner.id)

    if (response.status === 'success') {
      // Refresh the list
      await fetchPropertyOwners()
      $swal.fire({
        title: '刪除成功！',
        text: '所有權人已成功刪除',
        icon: 'success',
        confirmButtonText: '確定',
        confirmButtonColor: '#10b981'
      })
    } else {
      $swal.fire({
        title: '刪除失敗',
        text: response.message || '刪除失敗',
        icon: 'error',
        confirmButtonText: '確定',
        confirmButtonColor: '#ef4444'
      })
    }
  } catch (err) {
    $swal.fire({
      title: '刪除失敗',
      text: err.message || '刪除失敗，請稍後再試',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  }
}

// Load data when component mounts or route changes
onMounted(() => {
  console.log('Property owners page mounted, urbanRenewalId:', urbanRenewalId.value)
  fetchPropertyOwners()
})

// Watch for route changes to reload data (only when route actually changes)
watch(() => route.params.id, (newId, oldId) => {
  if (newId && oldId && newId !== oldId) {
    console.log('Route changed, new ID:', newId, 'old ID:', oldId)
    fetchPropertyOwners()
  }
})
</script>