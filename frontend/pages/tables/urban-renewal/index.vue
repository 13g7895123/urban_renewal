<template>
  <NuxtLayout name="main">
    <template #title>更新會管理</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">更新會</h2>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 mb-6">
        <button
          @click="allocateRenewal"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:users" class="w-5 h-5 mr-2" />
          分配更新會
        </button>
        <button
          @click="createRenewal"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新建更新會
        </button>
      </div>

      <!-- Create Urban Renewal Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-900 transition-opacity" @click="closeModal"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <!-- Header -->
            <div class="border-b border-gray-200 pb-4 mb-6">
              <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">新建更新會</h3>
                <button
                  type="button"
                  @click="fillRandomTestData"
                  class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
                >
                  <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
                  填入測試資料
                </button>
              </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="onSubmit">
              <div class="space-y-6">
                <!-- 更新會名稱 -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">更新會名稱 <span class="text-red-500">*</span></label>
                  <input
                    id="name"
                    v-model="formData.name"
                    type="text"
                    placeholder="請輸入更新會名稱"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 土地面積 -->
                <div>
                  <label for="area" class="block text-sm font-medium text-gray-700 mb-2">土地面積(平方公尺) <span class="text-red-500">*</span></label>
                  <input
                    id="area"
                    v-model="formData.area"
                    type="number"
                    placeholder="請輸入土地面積"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 所有權人數 -->
                <div>
                  <label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2">所有權人數 <span class="text-red-500">*</span></label>
                  <input
                    id="memberCount"
                    v-model="formData.memberCount"
                    type="number"
                    placeholder="請輸入所有權人數"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 理事長姓名 -->
                <div>
                  <label for="chairmanName" class="block text-sm font-medium text-gray-700 mb-2">理事長姓名 <span class="text-red-500">*</span></label>
                  <input
                    id="chairmanName"
                    v-model="formData.chairmanName"
                    type="text"
                    placeholder="請輸入理事長姓名"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 理事長電話 -->
                <div>
                  <label for="chairmanPhone" class="block text-sm font-medium text-gray-700 mb-2">理事長電話 <span class="text-red-500">*</span></label>
                  <input
                    id="chairmanPhone"
                    v-model="formData.chairmanPhone"
                    type="tel"
                    placeholder="請輸入理事長電話"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>
              </div>

              <!-- Error message -->
              <div v-if="error" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ error }}
              </div>

              <!-- Footer -->
              <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                <button
                  type="button"
                  @click="closeModal"
                  :disabled="isSubmitting"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  取消
                </button>
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                >
                  <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSubmitting ? '新增中...' : '確認新建' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Urban Renewal Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="p-4 text-left text-sm font-medium text-green-600">更新會名稱</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">土地面積 (平方公尺)</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">所有權人數</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">理事長姓名</th>
                <th class="p-4 text-left text-sm font-medium text-green-600">理事長電話</th>
                <th class="p-4 text-center text-sm font-medium text-green-600">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="p-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    載入中...
                  </div>
                </td>
              </tr>
              <tr v-else-if="renewals.length === 0">
                <td colspan="6" class="p-8 text-center text-gray-500">
                  暫無資料，請點擊「新建更新會」新增資料
                </td>
              </tr>
              <tr v-for="(renewal, index) in renewals" :key="renewal.id || index" class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                <td class="p-4 text-sm text-gray-900">{{ renewal.name }}</td>
                <td class="p-4 text-sm text-gray-900 text-center">{{ renewal.area }}</td>
                <td class="p-4 text-sm text-gray-900 text-center">{{ renewal.member_count }}</td>
                <td class="p-4 text-sm text-gray-900">{{ renewal.chairman_name }}</td>
                <td class="p-4 text-sm text-gray-900">{{ renewal.chairman_phone }}</td>
                <td class="p-4 text-center">
                  <div class="flex justify-center gap-2 flex-wrap">
                    <button
                      @click="viewBasicInfo(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"
                    >
                      基本資料
                    </button>
                    <button
                      @click="viewMembers(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                    >
                      所有權人
                    </button>
                    <button
                      @click="viewJointInfo(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-blue-800 hover:bg-blue-900 rounded transition-colors duration-200"
                    >
                      共有部分
                    </button>
                    <button
                      @click="deleteRenewal(renewal)"
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
            {{ renewals.length > 0 ? `1-${renewals.length} 共 ${renewals.length}` : '0-0 共 0' }}
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
import { ref, reactive, onMounted } from 'vue'

definePageMeta({
  middleware: ['auth', 'role'],
  role: 'admin',
  layout: false
})

// Use composables
const { get, post, delete: del } = useApi()
const alert = useAlert()
const { $swal } = useNuxtApp()

const pageSize = ref(10)
const showCreateModal = ref(false)
const loading = ref(false)
const isSubmitting = ref(false)
const error = ref('')

// Form data
const formData = reactive({
  name: '',
  area: '',
  memberCount: '',
  chairmanName: '',
  chairmanPhone: ''
})

const renewals = ref([])
const runtimeConfig = useRuntimeConfig()
const router = useRouter()

// API Functions
const fetchRenewals = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await get('/urban-renewals')

    if (response.success && response.data.status === 'success') {
      renewals.value = response.data.data || []
    } else {
      error.value = response.error?.message || response.data?.message || '獲取資料失敗'
    }
  } catch (err) {
    console.error('Fetch error:', err)
    error.value = '無法連接到伺服器'
  } finally {
    loading.value = false
  }
}

const createUrbanRenewal = async (data) => {
  try {
    const response = await post('/urban-renewals', {
      name: data.name,
      area: parseFloat(data.area),
      memberCount: parseInt(data.memberCount),
      chairmanName: data.chairmanName,
      chairmanPhone: data.chairmanPhone
    })

    return response
  } catch (err) {
    console.error('Create error:', err)
    throw new Error(err.data?.message || '新增失敗')
  }
}

const deleteUrbanRenewal = async (id) => {
  try {
    const response = await del(`/urban-renewals/${id}`)

    return response
  } catch (err) {
    console.error('Delete error:', err)
    throw new Error(err.data?.message || '刪除失敗')
  }
}

// UI Functions
const allocateRenewal = () => {
  console.log('Allocating renewal meeting')
  // TODO: Implement allocate functionality
}

const createRenewal = () => {
  showCreateModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  resetForm()
  error.value = ''
}

const resetForm = () => {
  formData.name = ''
  formData.area = ''
  formData.memberCount = ''
  formData.chairmanName = ''
  formData.chairmanPhone = ''
}

// Generate random test data
const fillRandomTestData = () => {
  const urbanRenewalNames = [
    '大安區忠孝更新會',
    '信義區松仁更新會',
    '中山區民權更新會',
    '萬華區西門更新會',
    '士林區天母更新會',
    '內湖區科技更新會',
    '南港區經貿更新會',
    '文山區木柵更新會',
    '北投區石牌更新會',
    '松山區民生更新會',
    '中正區博愛更新會',
    '大同區迪化更新會'
  ]

  const chairmanNames = [
    '陳志明', '林美玲', '王建國', '張淑芬', '李國華',
    '劉玉婷', '吳明德', '黃秀英', '鄭文昌', '謝雅雯',
    '周志偉', '徐淑惠', '蔡政宏', '許雅琴', '楊明峰',
    '游淑華', '賴志強', '沈美珠', '潘文傑', '蘇雅雲'
  ]

  // Random name selection
  const randomName = urbanRenewalNames[Math.floor(Math.random() * urbanRenewalNames.length)]

  // Random area between 500-5000 square meters
  const randomArea = Math.floor(Math.random() * 4500) + 500

  // Random member count between 15-150
  const randomMemberCount = Math.floor(Math.random() * 135) + 15

  // Random chairman name
  const randomChairmanName = chairmanNames[Math.floor(Math.random() * chairmanNames.length)]

  // Generate Taiwan mobile phone number (09XXXXXXXX)
  const generateTaiwanPhone = () => {
    const prefixes = ['09']
    const prefix = prefixes[Math.floor(Math.random() * prefixes.length)]
    const suffix = Math.floor(Math.random() * 100000000).toString().padStart(8, '0')
    return prefix + suffix
  }

  // Fill form data
  formData.name = randomName
  formData.area = randomArea.toString()
  formData.memberCount = randomMemberCount.toString()
  formData.chairmanName = randomChairmanName
  formData.chairmanPhone = generateTaiwanPhone()
}

const onSubmit = async () => {
  // Basic validation
  if (!formData.name || !formData.area || !formData.memberCount || !formData.chairmanName || !formData.chairmanPhone) {
    error.value = '請填寫所有必填項目'
    return
  }

  isSubmitting.value = true
  error.value = ''

  try {
    const response = await createUrbanRenewal(formData)

    if (response.success && response.data.status === 'success') {
      // Refresh the list to get updated data
      await fetchRenewals()
      closeModal()

      // Show success message
      alert.success('新增成功！', '更新會已成功建立')
    } else {
      error.value = response.data?.message || response.error?.message || '新增失敗'
    }
  } catch (err) {
    error.value = err.message || '新增失敗，請稍後再試'
  } finally {
    isSubmitting.value = false
  }
}

const viewBasicInfo = (renewal) => {
  router.push(`/tables/urban-renewal/${renewal.id}/basic-info`)
}

const viewMembers = (renewal) => {
  router.push(`/tables/urban-renewal/${renewal.id}/property-owners`)
}

const viewJointInfo = (renewal) => {
  console.log('Viewing joint info for:', renewal)
  // TODO: Implement view joint info functionality
}

const deleteRenewal = async (renewal) => {
  const result = await alert.confirm(
    '確認刪除',
    `確定要刪除「${renewal.name}」嗎？`,
    {
      confirmButtonText: '確定刪除'
    }
  )

  if (!result.isConfirmed) {
    return
  }

  try {
    const response = await deleteUrbanRenewal(renewal.id)

    if (response.success && response.data.status === 'success') {
      // Refresh the list
      await fetchRenewals()
      alert.success('刪除成功！', '更新會已成功刪除')
    } else {
      alert.error('刪除失敗', response.data?.message || response.error?.message || '刪除失敗')
    }
  } catch (err) {
    alert.error('刪除失敗', err.message || '刪除失敗，請稍後再試')
  }
}

// Refresh data function
const refreshData = async () => {
  await fetchRenewals()
}

// Load data when component mounts
onMounted(() => {
  fetchRenewals()
})
</script>