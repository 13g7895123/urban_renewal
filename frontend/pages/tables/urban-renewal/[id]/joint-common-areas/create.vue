<template>
  <NuxtLayout name="main">
    <template #title>新增共有部分</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:building-library" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">新增共有部分</h2>
        </div>
      </div>

      <!-- Form Card -->
      <UCard>
        <!-- Test Data Button and Reload Button -->
        <div class="flex justify-end gap-2 p-6 pb-0">
          <button
            type="button"
            @click="fetchAllBuildings"
            :disabled="isLoadingBuildings"
            class="px-3 py-1 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-md transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <Icon
              name="heroicons:arrow-path"
              :class="['w-4 h-4 mr-1 inline', { 'animate-spin': isLoadingBuildings }]"
            />
            {{ isLoadingBuildings ? '載入中...' : '重新載入' }}
          </button>
          <button
            type="button"
            @click="fillTestData"
            class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
          >
            <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
            填入測試資料
          </button>
        </div>

        <form @submit.prevent="onSubmit" class="space-y-6 p-6">
          <!-- 第一列：地號資訊 -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">地號資訊</h3>
            <div class="grid grid-cols-3 gap-4">
            <!-- 縣市 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">縣市 <span class="text-red-500">*</span></label>
              <select
                v-model="formData.county"
                @change="onCountyChange"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="">請選擇縣市</option>
                <option v-for="county in counties" :key="county.id" :value="county.code">
                  {{ county.name }}
                </option>
              </select>
            </div>

            <!-- 行政區 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">行政區 <span class="text-red-500">*</span></label>
              <select
                v-model="formData.district"
                @change="onDistrictChange"
                required
                :disabled="!formData.county"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option value="">請選擇行政區</option>
                <option v-for="district in districts" :key="district.id" :value="district.code">
                  {{ district.name }}
                </option>
              </select>
            </div>

            <!-- 段小段 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">段小段 <span class="text-red-500">*</span></label>
              <select
                v-model="formData.section"
                required
                :disabled="!formData.district"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option value="">請選擇段小段</option>
                <option v-for="section in sections" :key="section.id" :value="section.code">
                  {{ section.name }}
                </option>
              </select>
            </div>
            </div>
          </div>

          <!-- 第二列：建號與建物資訊 -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">建號與建物資訊</h3>

            <!-- 建號母號和子號 -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">建號母號 <span class="text-red-500">*</span></label>
                <input
                  v-model="formData.building_number_main"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="例：00001"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">建號子號 <span class="text-red-500">*</span></label>
                <input
                  v-model="formData.building_number_sub"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="例：000"
                />
              </div>
            </div>

            <!-- 建物總面積 -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">建物總面積(平方公尺) <span class="text-red-500">*</span></label>
              <input
                v-model="formData.building_total_area"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入建物總面積"
              />
            </div>

            <!-- 共有部分對應建物建號 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">共有部分對應建物建號 <span class="text-red-500">*</span></label>
              <select
                v-model="formData.corresponding_building_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="">請選擇對應的建物</option>
                <option v-for="building in filterCorrespondingBuildings" :key="building.building_id" :value="building.building_id">
                  {{ building.display_name }}
                </option>
              </select>
            </div>
          </div>

          <!-- 第三列：持有比例 -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">持有比例</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分子 <span class="text-red-500">*</span></label>
                <input
                  v-model="formData.ownership_numerator"
                  type="number"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="分子"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分母 <span class="text-red-500">*</span></label>
                <input
                  v-model="formData.ownership_denominator"
                  type="number"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="分母"
                />
              </div>
            </div>
          </div>

          <!-- 錯誤訊息 -->
          <div v-if="error" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ error }}
          </div>

          <!-- 按鈕區 -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button
              type="button"
              @click="goBack"
              :disabled="isSubmitting"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              取消
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isSubmitting ? '儲存中...' : '儲存' }}
            </button>
          </div>
        </form>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'

definePageMeta({
  middleware: ['auth', 'company-manager'],
  layout: false
})

// Use composables
const { get, post } = useApi()
const { showSuccess, showError } = useSweetAlert()
const route = useRoute()
const router = useRouter()
const {
  counties,
  districts,
  sections,
  fetchCounties,
  onCountyChange: locationOnCountyChange,
  onDistrictChange: locationOnDistrictChange
} = useLocationCascade()

// Get urban renewal ID from route
const urbanRenewalId = computed(() => route.params.id)

// State
const isSubmitting = ref(false)
const isLoadingBuildings = ref(false)
const error = ref('')
const allBuildings = ref([])

// Watch for allBuildings changes to help debug
watch(allBuildings, (newVal) => {
  console.log('[Joint Common Areas Create] ⚠️ allBuildings 已更新，新建物數:', newVal.length)
}, { deep: false })

// Form data
const formData = reactive({
  county: '',
  district: '',
  section: '',
  building_number_main: '',
  building_number_sub: '',
  building_total_area: '',
  corresponding_building_id: '',
  ownership_numerator: '',
  ownership_denominator: ''
})

// Computed property to filter buildings based on selected county, district, section
const filterCorrespondingBuildings = computed(() => {
  const county = formData.county
  const district = formData.district
  const section = formData.section

  // Debug logging
  console.log('[Joint Common Areas Create] filterCorrespondingBuildings 觸發:')
  console.log('  縣市:', county || '(未選擇)')
  console.log('  行政區:', district || '(未選擇)')
  console.log('  段小段:', section || '(未選擇)')
  console.log('  所有建物數:', allBuildings.value.length)

  // If any location field is not selected, return all buildings
  if (!county || !district || !section) {
    console.log('[Joint Common Areas Create] 有欄位未選擇，返回所有建物:', allBuildings.value.length)
    return allBuildings.value
  }

  // Filter buildings that match all three location fields
  const filtered = allBuildings.value.filter(building => {
    const match = building.county === county &&
                  building.district === district &&
                  building.section === section
    if (!match) {
      console.log(`[Joint Common Areas Create] 建物不匹配:`, {
        building_id: building.id,
        search: { county, district, section },
        building: { county: building.county, district: building.district, section: building.section }
      })
    }
    return match
  })

  console.log('[Joint Common Areas Create] 篩選後建物數:', filtered.length)
  return filtered
})

// API Functions
const fetchAllBuildings = async () => {
  isLoadingBuildings.value = true

  try {
    const apiPath = `/urban-renewals/${urbanRenewalId.value}/property-owners/all-buildings`
    console.log('[Joint Common Areas Create] ========== 開始載入建物資料 ==========')
    console.log('[Joint Common Areas Create] API 端點:', apiPath)
    console.log('[Joint Common Areas Create] Urban Renewal ID:', urbanRenewalId.value)

    const response = await get(apiPath)

    console.log('[Joint Common Areas Create] API 回應狀態:', response.success)
    console.log('[Joint Common Areas Create] API 完整回應:', response)

    if (response.success && response.data.status === 'success') {
      const rawData = response.data.data || []
      console.log('[Joint Common Areas Create] 原始建物數量:', rawData.length)

      // 將建物轉換為顯示格式
      allBuildings.value = rawData.map(building => {
        // 構建顯示名稱
        let displayName = ''

        if (building.location && building.location.trim() !== '') {
          // 如果有完整的 location 資訊，使用中文顯示
          displayName = `${building.location.replace(/\//g, '')} (${building.building_number_main}-${building.building_number_sub})`
        } else {
          // 否則使用代碼格式
          displayName = `${building.county || ''}-${building.district || ''}-${building.section || ''}-${building.building_number_main}-${building.building_number_sub}`
        }

        return {
          ...building,
          display_name: displayName
        }
      })

      console.log('[Joint Common Areas Create] 轉換後的建物數量:', allBuildings.value.length)
      console.log('[Joint Common Areas Create] 建物資料結構範例（前 3 筆）:')
      allBuildings.value.slice(0, 3).forEach((building, index) => {
        console.log(`  [建物 ${index + 1}]:`, {
          id: building.id,
          building_id: building.building_id, // Check this
          county: building.county,
          district: building.district,
          section: building.section,
          building_number_main: building.building_number_main,
          building_number_sub: building.building_number_sub,
          display_name: building.display_name
        })
      })

      showSuccess('載入成功！', `已載入 ${allBuildings.value.length} 筆建物資料`)
      console.log('[Joint Common Areas Create] ========== 建物資料載入完成 ==========')
    } else {
      const errorMsg = response.data?.message || response.error?.message || '未知錯誤'
      console.error('[Joint Common Areas Create] API 返回失敗:', errorMsg)
      console.error('[Joint Common Areas Create] 回應內容:', response.data)
      allBuildings.value = []
      showError('載入失敗', errorMsg)
    }
  } catch (err) {
    console.error('[Joint Common Areas Create] ========== 發生例外錯誤 ==========')
    console.error('[Joint Common Areas Create] 錯誤訊息:', err.message)
    console.error('[Joint Common Areas Create] 錯誤詳情:', err.data || err)
    console.error('[Joint Common Areas Create] 完整錯誤物件:', err)
    allBuildings.value = []
    showError('載入失敗', err.message || '無法連接到伺服器')
  } finally {
    isLoadingBuildings.value = false
  }
}

const submitJointArea = async (data) => {
  try {
    console.log('[Joint Common Areas Create] POST request to:', `/urban-renewals/${urbanRenewalId.value}/joint-common-areas`)
    console.log('[Joint Common Areas Create] Submitting data:', data)

    const response = await post(`/urban-renewals/${urbanRenewalId.value}/joint-common-areas`, data)

    return response
  } catch (err) {
    console.error('[Joint Common Areas Create] Submit error:', err)
    throw new Error(err.data?.message || err.message || '新增失敗')
  }
}

// UI Functions
const goBack = () => {
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/joint-common-areas`)
}

const onCountyChange = async () => {
  await locationOnCountyChange(formData.county)
  // Reset district and section when county changes
  formData.district = ''
  formData.section = ''
}

const onDistrictChange = async () => {
  await locationOnDistrictChange(formData.county, formData.district)
  // Reset section when district changes
  formData.section = ''
}

const fillTestData = async () => {
  console.log('[Joint Common Areas Create] ===== 開始填入測試資料 =====')

  // 首先載入所有建物資料，確保有可用的建物
  console.log('[Joint Common Areas Create] 步驟 1: 載入建物資料...')
  await fetchAllBuildings()

  if (allBuildings.value.length === 0) {
    console.error('[Joint Common Areas Create] 無可用建物資料，無法填入測試資料')
    showError('失敗', '無可用建物資料，請先點擊「重新載入」')
    return
  }

  console.log('[Joint Common Areas Create] 步驟 2: 從建物資料中隨機選擇地點...')

  // 隨機選擇一個建物作為地點參考
  const selectedBuilding = allBuildings.value[Math.floor(Math.random() * allBuildings.value.length)]

  console.log('[Joint Common Areas Create] 選擇的地點:', {
    county: selectedBuilding.county,
    district: selectedBuilding.district,
    section: selectedBuilding.section
  })

  // 填入地點資訊（根據選中的建物，確保下拉選單有可選項目）
  formData.county = selectedBuilding.county
  await locationOnCountyChange(selectedBuilding.county)

  formData.district = selectedBuilding.district
  await locationOnDistrictChange(selectedBuilding.county, selectedBuilding.district)

  formData.section = selectedBuilding.section

  console.log('[Joint Common Areas Create] 步驟 3: 填入建號和面積...')

  // 填入隨機建號
  formData.building_number_main = String(Math.floor(Math.random() * 10000)).padStart(5, '0')
  formData.building_number_sub = String(Math.floor(Math.random() * 1000)).padStart(3, '0')

  // 填入隨機建物總面積 (100-1000 平方公尺)
  formData.building_total_area = (Math.random() * 900 + 100).toFixed(2)

  console.log('[Joint Common Areas Create] 步驟 4: 填入持有比例...')

  // 填入隨機持有比例 (分子 1-10, 分母 2-20)
  const numerator = Math.floor(Math.random() * 10) + 1
  const denominator = Math.floor(Math.random() * 19) + 2

  formData.ownership_numerator = numerator
  formData.ownership_denominator = denominator

  console.log('[Joint Common Areas Create] 步驟 5: 對應建物欄位已重置，請手動選擇...')
  // 不自動填入對應建物，讓使用者手動選擇

  console.log('[Joint Common Areas Create] ===== 測試資料填入完成 =====')
  showSuccess('成功', '測試資料已填入，請在下拉選單中選擇對應建物')
}

const onSubmit = async () => {
  console.log('[Joint Common Areas Create] Submitting form data:', JSON.parse(JSON.stringify(formData)))

  // Basic validation
  const requiredFields = [
    'county', 'district', 'section',
    'building_number_main', 'building_number_sub',
    'building_total_area', 'corresponding_building_id',
    'ownership_numerator', 'ownership_denominator'
  ]

  const missingFields = requiredFields.filter(field => {
    const value = formData[field]
    return value === '' || value === null || value === undefined
  })

  if (missingFields.length > 0) {
    console.log('[Joint Common Areas Create] Missing fields:', missingFields)
    error.value = '請填寫所有必填項目'
    return
  }

  isSubmitting.value = true
  error.value = ''

  try {
    const submitData = {
      county: formData.county,
      district: formData.district,
      section: formData.section,
      building_number_main: formData.building_number_main,
      building_number_sub: formData.building_number_sub,
      building_total_area: parseFloat(formData.building_total_area),
      corresponding_building_id: parseInt(formData.corresponding_building_id),
      ownership_numerator: parseInt(formData.ownership_numerator),
      ownership_denominator: parseInt(formData.ownership_denominator)
    }

    const response = await submitJointArea(submitData)

    if (response.success && response.data.status === 'success') {
      showSuccess('新增成功！', '共有部分已成功建立')
      goBack()
    } else {
      error.value = response.data?.message || response.error?.message || '新增失敗'
      showError('新增失敗', error.value)
    }
  } catch (err) {
    error.value = err.message || '新增失敗，請稍後再試'
    showError('新增失敗', error.value)
  } finally {
    isSubmitting.value = false
  }
}

// Load data when component mounts
onMounted(async () => {
  await fetchCounties()
  await fetchAllBuildings()
})
</script>
