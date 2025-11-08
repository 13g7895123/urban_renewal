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

// Use SweetAlert composable
const { showSuccess, showError, showConfirm, showLoading, showCustom, close } = useSweetAlert()

// For special dialogs with deny button (3-button dialogs)
const { $swal } = useNuxtApp()

const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()

// Use API composable for authenticated requests
const { get, post, delete: del } = useApi()

// Get urban renewal ID from route (reactive)
const urbanRenewalId = computed(() => route.params.id)

const pageSize = ref(10)
const loading = ref(false)
const propertyOwners = ref([])

// API Functions
const fetchPropertyOwners = async () => {
  loading.value = true

  try {
    console.log('[Property Owners] Fetching from:', `/urban-renewals/${urbanRenewalId.value}/property-owners`)

    // Use useApi() composable which automatically adds Authorization header
    const response = await get(`/urban-renewals/${urbanRenewalId.value}/property-owners`)

    if (response.success && response.data.status === 'success') {
      propertyOwners.value = response.data.data || []
      console.log('[Property Owners] Data loaded:', propertyOwners.value.length, 'records')
    } else {
      console.error('Failed to fetch property owners:', response.data?.message || response.error?.message)
      propertyOwners.value = []
    }
  } catch (err) {
    console.error('[Property Owners] Fetch error:', err)
    console.error('[Property Owners] Error details:', err.data || err.message)

    // Show more detailed error message
    const errorMessage = err.data?.message || err.message || '無法載入所有權人資料'
    showError('載入失敗', errorMessage)
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
    console.log('[Property Owners] DELETE request to:', `/property-owners/${id}`)
    // Use useApi() composable which automatically adds Authorization header
    const response = await del(`/property-owners/${id}`)
    console.log('[Property Owners] Raw delete response:', response)
    return response
  } catch (err) {
    console.error('[Property Owners] Delete error:', err)
    console.error('[Property Owners] Error details:', err.data || err)
    throw new Error(err.data?.message || err.message || '刪除失敗')
  }
}

// UI Functions
const goBack = () => {
  router.push('/tables/urban-renewal')
}

const exportOwners = async () => {
  try {
    const authStore = useAuthStore()
    const token = authStore.token
    
    if (!token) {
      showError('未授權', '請先登入')
      return
    }

    const isDev = process.dev || process.env.NODE_ENV === 'development'
    const baseURL = isDev ? 'http://localhost:9228' : (runtimeConfig.public.apiBaseUrl || '')
    const exportUrl = `${baseURL}/api/urban-renewals/${urbanRenewalId.value}/property-owners/export`

    console.log('[Export] Downloading from:', exportUrl)

    // Create a temporary link with authorization header using fetch + blob
    const response = await fetch(exportUrl, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (!response.ok) {
      throw new Error('匯出失敗')
    }
    
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `所有權人清單_${urbanRenewalId.value}_${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)

    showSuccess('匯出成功！', 'Excel檔案下載中...')
  } catch (err) {
    console.error('[Export] Error:', err)
    showError('匯出失敗', err.message || '匯出失敗，請稍後再試')
  }
}

const downloadTemplate = async () => {
  try {
    const authStore = useAuthStore()
    const token = authStore.token
    
    if (!token) {
      showError('未授權', '請先登入')
      return
    }

    const isDev = process.dev || process.env.NODE_ENV === 'development'
    const baseURL = isDev ? 'http://localhost:9228' : (runtimeConfig.public.apiBaseUrl || '')
    const templateUrl = `${baseURL}/api/property-owners/template`

    console.log('[Template] Downloading from:', templateUrl)

    // Download with authorization header
    const response = await fetch(templateUrl, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (!response.ok) {
      throw new Error('下載失敗')
    }
    
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `所有權人匯入範本_${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  } catch (err) {
    console.error('[Template] Error:', err)
    showError('下載失敗', err.message || '下載失敗，請稍後再試')
  }
}

const importOwners = async () => {
  // Show modal with instructions and template download
  const result = await $swal.fire({
    title: '匯入所有權人',
    html: `
      <div class="text-left space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="font-semibold text-blue-900 mb-2">📋 匯入步驟：</h3>
          <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
            <li>下載Excel範本檔案</li>
            <li>依據欄位填入所有權人資料</li>
            <li>點選「選擇檔案」上傳填好的Excel檔案</li>
          </ol>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h3 class="font-semibold text-yellow-900 mb-2">⚠️ 注意事項：</h3>
          <ul class="list-disc list-inside space-y-1 text-sm text-yellow-800">
            <li>所有權人名稱為必填欄位</li>
            <li>所有權人編號不可重複</li>
            <li>身分證字號不可重複</li>
            <li>範例資料（灰色底）會自動略過，不會被匯入</li>
            <li>請從第3列開始填寫真實資料</li>
          </ul>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <h3 class="font-semibold text-gray-900 mb-2">📊 Excel欄位說明：</h3>
          <div class="text-sm text-gray-700 space-y-1">
            <p><strong>A欄：</strong>所有權人編號</p>
            <p><strong>B欄：</strong>所有權人名稱（必填）</p>
            <p><strong>C欄：</strong>身分證字號</p>
            <p><strong>D-E欄：</strong>電話1、電話2</p>
            <p><strong>F-G欄：</strong>聯絡地址、戶籍地址</p>
            <p><strong>H欄：</strong>排除類型</p>
            <p><strong>I欄：</strong>備註</p>
          </div>
        </div>
      </div>
    `,
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: '選擇檔案',
    denyButtonText: '下載範本',
    cancelButtonText: '返回',
    confirmButtonColor: '#10b981',
    denyButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
    width: '600px',
    customClass: {
      htmlContainer: 'text-left'
    }
  })

  if (result.isDenied) {
    // Download template
    downloadTemplate()
    // Show the modal again
    setTimeout(() => importOwners(), 500)
    return
  }

  if (!result.isConfirmed) {
    return
  }

  // Create file input element
  const fileInput = document.createElement('input')
  fileInput.type = 'file'
  fileInput.accept = '.xlsx,.xls'

  fileInput.onchange = async (e) => {
    const file = e.target.files[0]
    if (!file) {
      console.log('[Import] 使用者取消選擇檔案')
      return
    }

    // Validate file type
    const extension = file.name.split('.').pop().toLowerCase()
    if (!['xlsx', 'xls'].includes(extension)) {
      showError('檔案格式錯誤', '僅支援 .xlsx 或 .xls 格式的檔案')
      return
    }

    // Validate file size (10MB limit)
    const maxSizeInBytes = 10 * 1024 * 1024 // 10MB
    if (file.size > maxSizeInBytes) {
      showError('檔案太大', `檔案大小不能超過 10MB，目前檔案大小為 ${(file.size / 1024 / 1024).toFixed(2)}MB`)
      return
    }

    try {
      // Show loading
      showLoading('匯入中...', '正在驗證並匯入資料，請稍候...')
      console.log('[Import] 開始上傳檔案:', file.name, `大小: ${(file.size / 1024).toFixed(2)}KB`)

      const isDev = process.dev || process.env.NODE_ENV === 'development'
      // Create form data
      const formData = new FormData()
      formData.append('file', file)

      // Use post() from useApi() composable for automatic authentication
      const response = await post(`/urban-renewals/${urbanRenewalId.value}/property-owners/import`, formData)

      console.log('[Import] Response:', response)

      // Close loading first
      close()

      // Check if API call was successful
      if (!response.success) {
        // API call failed (network error, HTTP error status, etc.)
        showError(
          '匯入失敗',
          response.error?.message || '匯入失敗，請稍後再試'
        )
        return
      }

      // API call succeeded, check backend response status
      if (response.data?.status === 'success') {
        let message = response.data.message || '匯入完成'

        // Show errors if any
        if (response.data?.data?.errors && response.data.data.errors.length > 0) {
          message += '<br><br><div class="text-left"><strong>錯誤訊息：</strong><br><ul class="list-disc list-inside mt-2">'
          response.data.data.errors.slice(0, 10).forEach(error => {
            message += `<li class="text-sm text-red-600">${error}</li>`
          })
          message += '</ul>'
          if (response.data.data.errors.length > 10) {
            message += `<p class="text-sm text-gray-600 mt-2">...以及其他 ${response.data.data.errors.length - 10} 個錯誤</p>`
          }
          message += '</div>'
        }

        await showCustom({
          title: '匯入完成！',
          html: message,
          icon: response.data?.data?.error_count > 0 ? 'warning' : 'success',
          timer: null, // Don't auto-close for detailed results
          showConfirmButton: true,
          confirmButtonText: '關閉',
          confirmButtonColor: '#10b981',
          toast: false,
          position: 'center',
          width: '600px'
        })

        // Refresh the list
        await fetchPropertyOwners()
      } else {
        // Backend returned error status
        showError(
          '匯入失敗',
          response.data?.message || '匯入失敗，請稍後再試'
        )
      }
    } catch (err) {
      console.error('[Import] Error:', err)
      close()
      showError('匯入失敗', err.message || '匯入失敗，請稍後再試')
    }
  }

  // Trigger file picker
  fileInput.click()
}

const createOwner = () => {
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners/create`)
}

const viewOwnerDetails = (owner) => {
  console.log('[Property Owners] View owner details clicked:', owner)

  if (!owner || !owner.id) {
    console.error('[Property Owners] Owner or owner.id is missing:', owner)
    showError('錯誤', '無法取得所有權人資料')
    return
  }

  console.log('[Property Owners] Navigating to:', `/tables/urban-renewal/${urbanRenewalId.value}/property-owners/${owner.id}/edit`)
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners/${owner.id}/edit`)
}

const deleteOwner = async (owner) => {
  const ownerName = owner.owner_name || owner.name || '此所有權人'

  const result = await showConfirm(
    '確認刪除',
    `確定要刪除「${ownerName}」嗎？此操作無法復原。`,
    '刪除',
    '取消'
  )

  if (!result.isConfirmed) {
    return
  }

  try {
    console.log('[Property Owners] Deleting owner:', owner)
    console.log('[Property Owners] Owner ID:', owner.id)
    console.log('[Property Owners] Full owner object:', JSON.stringify(owner, null, 2))

    const response = await deletePropertyOwner(owner.id)

    console.log('[Property Owners] Delete response:', response)

    if (response.status === 'success' || (response.success && response.data?.status === 'success')) {
      // Refresh the list
      await fetchPropertyOwners()
      showSuccess('刪除成功！', '所有權人已成功刪除')
    } else {
      const errorMsg = response.message || response.error?.message || response.data?.message || '刪除失敗'
      console.error('[Property Owners] Delete failed:', errorMsg)
      showError('刪除失敗', errorMsg)
    }
  } catch (err) {
    console.error('[Property Owners] Delete exception:', err)
    const errorMsg = err.message || err.data?.message || err.error?.message || '刪除失敗，請稍後再試'
    showError('刪除失敗', errorMsg)
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