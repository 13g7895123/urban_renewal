<template>
  <NuxtLayout name="main">
    <template #title>更新會基本資料管理</template>

    <div class="p-8">
      <!-- Page Title -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ renewalData.name || '' }}</h1>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="flex items-center">
          <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="text-lg text-gray-600">載入資料中...</span>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-8">
        <!-- Basic Info Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">基本資訊</h2>
            <button
              type="button"
              @click="fillBasicInfoTestData"
              class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
            >
              <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
              填入測試資料
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">更新會名稱</label>
              <input
                id="name"
                v-model="renewalData.name"
                type="text"
                @input="updatePageTitle"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入更新會名稱"
              />
            </div>
            <div>
              <label for="area" class="block text-sm font-medium text-gray-700 mb-2">土地面積(平方公尺)</label>
              <input
                id="area"
                v-model="renewalData.area"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入土地面積"
              />
            </div>
            <div>
              <label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2">所有權人數</label>
              <input
                id="memberCount"
                v-model="renewalData.member_count"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入所有權人數"
              />
            </div>
          </div>
        </div>

        <!-- Land Plot Management Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">地號管理</h2>
            <button
              type="button"
              @click="fillLandPlotTestData"
              class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
            >
              <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
              填入測試地號
            </button>
          </div>

          <!-- Add Land Plot Form -->
          <div class="bg-gray-50 rounded-lg p-6 shadow-sm mb-6">
            <h3 class="text-md font-medium text-gray-900 mb-4">新增地號</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- County/City -->
              <div>
                <label for="county" class="block text-sm font-medium text-gray-700 mb-2">縣市 <span class="text-red-500">*</span></label>
                <select
                  id="county"
                  v-model="landForm.county"
                  @change="onCountyChange"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  required
                >
                  <option value="">請選擇縣市</option>
                  <option v-for="county in counties" :key="county.code" :value="county.name">
                    {{ county.name }}
                  </option>
                </select>
              </div>

              <!-- District -->
              <div>
                <label for="district" class="block text-sm font-medium text-gray-700 mb-2">行政區 <span class="text-red-500">*</span></label>
                <select
                  id="district"
                  v-model="landForm.district"
                  @change="onDistrictChange"
                  :disabled="!landForm.county"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                  required
                >
                  <option value="">請選擇行政區</option>
                  <option v-for="district in districts" :key="district.code" :value="district.name">
                    {{ district.name }}
                  </option>
                </select>
              </div>

              <!-- Section -->
              <div>
                <label for="section" class="block text-sm font-medium text-gray-700 mb-2">段小段 <span class="text-red-500">*</span></label>
                <select
                  id="section"
                  v-model="landForm.section"
                  :disabled="!landForm.district"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                  required
                >
                  <option value="">請選擇段小段</option>
                  <option v-for="section in sections" :key="section.code" :value="section.name">
                    {{ section.name }}
                  </option>
                </select>
              </div>

              <!-- Land Number Main -->
              <div>
                <label for="landNumberMain" class="block text-sm font-medium text-gray-700 mb-2">地號母號 <span class="text-red-500">*</span></label>
                <input
                  id="landNumberMain"
                  v-model="landForm.landNumberMain"
                  type="text"
                  @input="validateLandNumber('main')"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="例: 0001"
                  maxlength="4"
                  required
                />
                <p v-if="landNumberErrors.main" class="mt-1 text-sm text-red-600">{{ landNumberErrors.main }}</p>
              </div>

              <!-- Land Number Sub -->
              <div>
                <label for="landNumberSub" class="block text-sm font-medium text-gray-700 mb-2">地號子號</label>
                <input
                  id="landNumberSub"
                  v-model="landForm.landNumberSub"
                  type="text"
                  @input="validateLandNumber('sub')"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="例: 0000"
                  maxlength="4"
                />
                <p v-if="landNumberErrors.sub" class="mt-1 text-sm text-red-600">{{ landNumberErrors.sub }}</p>
              </div>

              <!-- Land Area -->
              <div>
                <label for="landArea" class="block text-sm font-medium text-gray-700 mb-2">土地總面積(平方公尺)</label>
                <input
                  id="landArea"
                  v-model="landForm.landArea"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="請輸入土地面積"
                />
              </div>
            </div>

            <!-- Add Button -->
            <div class="flex justify-end mt-4">
              <button
                @click="addLandPlot"
                :disabled="!canAddLandPlot"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                新增地號
              </button>
            </div>
          </div>

          <!-- Land Plots List -->
          <div class="bg-white rounded-lg border border-gray-200">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="p-4 text-left text-sm font-medium text-gray-700">地號</th>
                    <th class="p-4 text-left text-sm font-medium text-gray-700">土地總面積(平方公尺)</th>
                    <th class="p-4 text-center text-sm font-medium text-gray-700">操作</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="landPlots.length === 0">
                    <td colspan="3" class="p-8 text-center text-gray-500">
                      暫無地號資料，請新增地號
                    </td>
                  </tr>
                  <tr v-for="(plot, index) in landPlots" :key="plot.id || index" class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                    <td class="p-4 text-sm text-gray-900">
                      <div class="flex items-center">
                        <span>{{ plot.chineseFullLandNumber || plot.fullLandNumber }}</span>
                        <span v-if="plot.isRepresentative" class="ml-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                          代表號
                        </span>
                      </div>
                    </td>
                    <td class="p-4 text-sm text-gray-900">{{ plot.landArea || '未設定' }}</td>
                    <td class="p-4 text-center">
                      <div class="flex justify-center gap-2 flex-wrap">
                        <button
                          v-if="!plot.isRepresentative"
                          @click="setAsRepresentative(plot)"
                          class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                        >
                          設為代表號
                        </button>
                        <span v-else class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                          代表號
                        </span>
                        <button
                          @click="editLandPlot(plot)"
                          class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"
                        >
                          編輯
                        </button>
                        <button
                          @click="deleteLandPlot(plot)"
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
          </div>
        </div>

        <!-- Other Info Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">其他資訊</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="chairmanName" class="block text-sm font-medium text-gray-700 mb-2">理事長姓名</label>
              <input
                id="chairmanName"
                v-model="renewalData.chairman_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入理事長姓名"
              />
            </div>
            <div>
              <label for="chairmanPhone" class="block text-sm font-medium text-gray-700 mb-2">理事長電話</label>
              <input
                id="chairmanPhone"
                v-model="renewalData.chairman_phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入理事長電話"
              />
            </div>
            <div>
              <label for="address" class="block text-sm font-medium text-gray-700 mb-2">設立地址</label>
              <input
                id="address"
                v-model="renewalData.address"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入設立地址"
              />
            </div>
            <div>
              <label for="representative" class="block text-sm font-medium text-gray-700 mb-2">負責人</label>
              <input
                id="representative"
                v-model="renewalData.representative"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入負責人"
              />
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
          <button
            @click="goBack"
            class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-300 border border-transparent rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
          >
            回上一頁
          </button>
          <button
            @click="saveChanges"
            :disabled="isSaving"
            class="px-6 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isSaving ? '儲存中...' : '儲存' }}
          </button>
        </div>
      </div>

      <!-- Edit Land Plot Modal -->
      <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="closeEditModal"></div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
              <h3 class="text-lg font-semibold text-gray-900">編輯地號</h3>
            </div>

            <div class="space-y-4">
              <div>
                <label for="editLandArea" class="block text-sm font-medium text-gray-700 mb-2">土地總面積(平方公尺)</label>
                <input
                  id="editLandArea"
                  v-model="editingLandPlot.landArea"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="請輸入土地面積"
                />
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
              <button
                type="button"
                @click="closeEditModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
              >
                取消
              </button>
              <button
                @click="updateLandPlot"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
              >
                確認更新
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'

const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()
const { $swal } = useNuxtApp()
const { get, post, put } = useApi()

// State
const loading = ref(true)
const isSaving = ref(false)
const showEditModal = ref(false)

// Data
const renewalData = reactive({
  id: null,
  name: '',
  area: '',
  member_count: '',
  chairman_name: '',
  chairman_phone: '',
  address: '',
  representative: ''
})

const landForm = reactive({
  county: '',
  district: '',
  section: '',
  landNumberMain: '',
  landNumberSub: '',
  landArea: ''
})

const landNumberErrors = reactive({
  main: '',
  sub: ''
})

const landPlots = ref([])
const editingLandPlot = ref({})
const originalLandPlots = ref([]) // 保存原始地號資料
const deletedLandPlots = ref([]) // 保存已刪除的地號ID

// Location Data from API
const counties = ref([])
const districts = ref([])
const sections = ref([])

// 地區映射緩存
const locationMappings = ref({
  counties: new Map(),
  districts: new Map(),
  sections: new Map()
})

// Computed
const canAddLandPlot = computed(() => {
  return landForm.county &&
         landForm.district &&
         landForm.section &&
         landForm.landNumberMain &&
         !landNumberErrors.main &&
         !landNumberErrors.sub
})

// Methods
const getChineseLandNumber = async (plot) => {
  // 如果 fullLandNumber 已經是中文格式，直接使用
  if (plot.fullLandNumber && !plot.fullLandNumber.match(/^[A-Z]{3,}/)) {
    return plot.fullLandNumber
  }

  let countyName = plot.county
  let districtName = plot.district
  let sectionName = plot.section

  try {
    // 獲取縣市名稱
    if (locationMappings.value.counties.has(plot.county)) {
      countyName = locationMappings.value.counties.get(plot.county)
    } else if (counties.value.length > 0) {
      const county = counties.value.find(c => c.code === plot.county)
      if (county) {
        countyName = county.name
        locationMappings.value.counties.set(plot.county, county.name)
      }
    }

    // 獲取行政區名稱
    const districtKey = `${plot.county}_${plot.district}`
    if (locationMappings.value.districts.has(districtKey)) {
      districtName = locationMappings.value.districts.get(districtKey)
    } else {
      try {
        const response = await get(`/locations/districts/${plot.county}`)
        if (response.success && response.data.status === 'success') {
          const district = response.data.data.find(d => d.code === plot.district)
          if (district) {
            districtName = district.name
            locationMappings.value.districts.set(districtKey, district.name)
          }
        }
      } catch (error) {
        console.error('Error fetching district:', error)
      }
    }

    // 獲取段小段名稱
    const sectionKey = `${plot.county}_${plot.district}_${plot.section}`
    if (locationMappings.value.sections.has(sectionKey)) {
      sectionName = locationMappings.value.sections.get(sectionKey)
    } else {
      try {
        const response = await get(`/locations/sections/${plot.county}/${plot.district}`)
        if (response.success && response.data.status === 'success') {
          const section = response.data.data.find(s => s.code === plot.section)
          if (section) {
            sectionName = section.name
            locationMappings.value.sections.set(sectionKey, section.name)
          }
        }
      } catch (error) {
        console.error('Error fetching section:', error)
      }
    }
  } catch (error) {
    console.error('Error in getChineseLandNumber:', error)
  }

  return `${countyName}${districtName}${sectionName}${plot.landNumber}`
}

const fetchCounties = async () => {
  try {
    const response = await get('/locations/counties')
    if (response.success && response.data.status === 'success') {
      counties.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch counties:', err)
  }
}

const fetchDistricts = async (countyCode) => {
  try {
    const response = await get(`/locations/districts/${countyCode}`)
    if (response.success && response.data.status === 'success') {
      districts.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch districts:', err)
    districts.value = []
  }
}

const fetchSections = async (countyCode, districtCode) => {
  try {
    const response = await get(`/locations/sections/${countyCode}/${districtCode}`)
    if (response.success && response.data.status === 'success') {
      sections.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch sections:', err)
    sections.value = []
  }
}

const fetchRenewalData = async () => {
  loading.value = true
  try {
    const response = await get(`/urban-renewals/${route.params.id}`)

    if (response.success && response.data.status === 'success') {
      Object.assign(renewalData, response.data.data)
    } else {
      throw new Error(response.error?.message || response.data?.message || '獲取資料失敗')
    }
  } catch (err) {
    console.error('Fetch error:', err)
    await $swal.fire({
      title: '載入失敗',
      text: '無法載入資料，請稍後再試',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
    router.push('/tables/urban-renewal')
  } finally {
    loading.value = false
  }
}

const fetchLandPlots = async () => {
  try {
    const response = await get(`/urban-renewals/${route.params.id}/land-plots`)

    if (response.success && response.data.status === 'success') {
      // 將地號轉換為中文格式
      const plotsWithChineseNames = await Promise.all(
        response.data.data.map(async (plot) => {
          const chineseFullLandNumber = await getChineseLandNumber(plot)
          return {
            ...plot,
            chineseFullLandNumber
          }
        })
      )

      landPlots.value = plotsWithChineseNames
      originalLandPlots.value = JSON.parse(JSON.stringify(plotsWithChineseNames)) // 深拷貝保存原始資料
    }
  } catch (err) {
    console.error('Fetch land plots error:', err)
    landPlots.value = []
    originalLandPlots.value = []
  }
}

const updatePageTitle = () => {
  // This would update the page title when name changes
  document.title = `${renewalData.name} - 更新會基本資料管理`
}

const onCountyChange = async () => {
  landForm.district = ''
  landForm.section = ''
  sections.value = []

  if (landForm.county) {
    // 根據中文名稱找到對應的縣市代號
    const selectedCounty = counties.value.find(c => c.name === landForm.county)
    if (selectedCounty) {
      await fetchDistricts(selectedCounty.code)
    }
  } else {
    districts.value = []
  }
}

const onDistrictChange = async () => {
  landForm.section = ''

  if (landForm.county && landForm.district) {
    // 根據中文名稱找到對應的縣市和行政區代號
    const selectedCounty = counties.value.find(c => c.name === landForm.county)
    const selectedDistrict = districts.value.find(d => d.name === landForm.district)
    if (selectedCounty && selectedDistrict) {
      await fetchSections(selectedCounty.code, selectedDistrict.code)
    }
  } else {
    sections.value = []
  }
}

const validateLandNumber = (type) => {
  const value = type === 'main' ? landForm.landNumberMain : landForm.landNumberSub
  const pattern = /^\d{4}$/

  if (value && !pattern.test(value)) {
    landNumberErrors[type] = '請輸入4位數字格式（例：0001）'
  } else {
    landNumberErrors[type] = ''
  }
}

const addLandPlot = () => {
  if (!canAddLandPlot.value) return

  // 直接使用表單中的中文名稱
  const countyName = landForm.county
  const districtName = landForm.district
  const sectionName = landForm.section

  // 找到對應的代號用於API調用
  const selectedCounty = counties.value.find(c => c.name === landForm.county)
  const selectedDistrict = districts.value.find(d => d.name === landForm.district)
  const selectedSection = sections.value.find(s => s.name === landForm.section)

  const landNumber = landForm.landNumberSub
    ? `${landForm.landNumberMain}-${landForm.landNumberSub}`
    : landForm.landNumberMain

  const fullLandNumber = `${countyName}${districtName}${sectionName}${landNumber}`

  const newPlot = {
    id: `temp_${Date.now()}`, // Temporary ID for new plots
    county: selectedCounty?.code || landForm.county,
    district: selectedDistrict?.code || landForm.district,
    section: selectedSection?.code || landForm.section,
    landNumberMain: landForm.landNumberMain,
    landNumberSub: landForm.landNumberSub,
    landNumber: landNumber,
    fullLandNumber: fullLandNumber,
    landArea: landForm.landArea,
    isRepresentative: landPlots.value.length === 0, // First plot is representative by default
    isNew: true // 標記為新增的地號
  }

  landPlots.value.push(newPlot)

  // Reset form
  Object.assign(landForm, {
    county: '',
    district: '',
    section: '',
    landNumberMain: '',
    landNumberSub: '',
    landArea: ''
  })
  districts.value = []
  sections.value = []
}

const setAsRepresentative = (plot) => {
  // Remove representative status from all plots
  landPlots.value.forEach(p => p.isRepresentative = false)
  // Set current plot as representative
  plot.isRepresentative = true
}

const editLandPlot = (plot) => {
  editingLandPlot.value = { ...plot }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingLandPlot.value = {}
}

const updateLandPlot = () => {
  const index = landPlots.value.findIndex(p => p.id === editingLandPlot.value.id)
  if (index !== -1) {
    landPlots.value[index] = { ...editingLandPlot.value }
  }
  closeEditModal()
}

const deleteLandPlot = async (plot) => {
  const result = await $swal.fire({
    title: '確認刪除',
    text: `確定要刪除地號「${plot.fullLandNumber}」嗎？`,
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

  const index = landPlots.value.findIndex(p => p.id === plot.id)
  if (index !== -1) {
    const wasRepresentative = plot.isRepresentative

    // 如果是已存在的地號，加入刪除列表
    if (!plot.isNew && !plot.id.toString().startsWith('temp_')) {
      deletedLandPlots.value.push(plot.id)
    }

    landPlots.value.splice(index, 1)

    // If deleted plot was representative, set first remaining plot as representative
    if (wasRepresentative && landPlots.value.length > 0) {
      landPlots.value[0].isRepresentative = true
    }
  }
}

const saveChanges = async () => {
  isSaving.value = true
  try {
    // 1. 先儲存基本資料
    const response = await put(`/urban-renewals/${route.params.id}`, {
      name: renewalData.name,
      area: parseFloat(renewalData.area),
      member_count: parseInt(renewalData.member_count),
      chairman_name: renewalData.chairman_name,
      chairman_phone: renewalData.chairman_phone,
      address: renewalData.address,
      representative: renewalData.representative
    })

    if (!response.success || response.data.status !== 'success') {
      throw new Error(response.error?.message || response.data?.message || '基本資料儲存失敗')
    }

    // 2. 處理地號刪除
    for (const deletedId of deletedLandPlots.value) {
      try {
        await del(`/land-plots/${deletedId}`, {
          method: 'DELETE'
        })
      } catch (err) {
        console.error('Delete land plot error:', err)
      }
    }

    // 3. 處理地號新增
    const newPlots = landPlots.value.filter(plot => plot.isNew || plot.id.toString().startsWith('temp_'))
    let landPlotErrors = []

    for (const newPlot of newPlots) {
      try {
        const plotResponse = await post(`/urban-renewals/${route.params.id}/land-plots`, {
          county: newPlot.county,
          district: newPlot.district,
          section: newPlot.section,
          landNumberMain: newPlot.landNumberMain,
          landNumberSub: newPlot.landNumberSub,
          landArea: parseFloat(newPlot.landArea) || null,
          isRepresentative: newPlot.isRepresentative ? 1 : 0
        })

        if (!plotResponse.success || plotResponse.data.status !== 'success') {
          landPlotErrors.push(`地號 ${newPlot.fullLandNumber} 新增失敗`)
        }
      } catch (err) {
        console.error('Create land plot error:', err)
        landPlotErrors.push(`地號 ${newPlot.fullLandNumber} 新增失敗：${err.message || '網路錯誤'}`)
      }
    }

    // 如果有地號新增失敗，顯示警告
    if (landPlotErrors.length > 0) {
      await $swal.fire({
        title: '部分地號新增失敗',
        html: landPlotErrors.join('<br>'),
        icon: 'warning',
        confirmButtonText: '確定',
        confirmButtonColor: '#f59e0b'
      })
    }

    // 4. 處理地號更新（編輯過的現有地號）
    const existingPlots = landPlots.value.filter(plot => !plot.isNew && !plot.id.toString().startsWith('temp_'))
    for (const plot of existingPlots) {
      const original = originalLandPlots.value.find(orig => orig.id === plot.id)
      if (original && (
        original.landArea !== plot.landArea ||
        original.isRepresentative !== plot.isRepresentative
      )) {
        try {
          await put(`/land-plots/${plot.id}`, {
            landArea: parseFloat(plot.landArea) || null,
            isRepresentative: plot.isRepresentative ? 1 : 0
          })
        } catch (err) {
          console.error('Update land plot error:', err)
        }
      }
    }

    // 5. 重新載入地號資料
    await fetchLandPlots()

    // 6. 清除變更追蹤
    deletedLandPlots.value = []

    // 7. 顯示成功訊息（1.5秒自動關閉）
    await $swal.fire({
      title: '儲存成功！',
      text: '資料已成功更新',
      icon: 'success',
      timer: 1500,
      timerProgressBar: true,
      showConfirmButton: false
    })

  } catch (err) {
    console.error('Save error:', err)
    await $swal.fire({
      title: '儲存失敗',
      text: err.message || '儲存失敗，請稍後再試',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    isSaving.value = false
  }
}

// Fill basic info test data
const fillBasicInfoTestData = () => {
  const testNames = [
    '臺北市大安區忠孝東路更新事宜臺北市政府會',
    '臺北市信義區松仁路更新事宜臺北市政府會',
    '臺北市中山區民權東路更新事宜臺北市政府會',
    '臺北市萬華區西門町更新事宜臺北市政府會',
    '臺北市士林區天母更新事宜臺北市政府會'
  ]

  const testChairmanNames = ['王大明', '李美華', '張志強', '陳淑芬', '林建國']
  const testAddresses = [
    '台北市大安區信義路三段134號',
    '台北市信義區松仁路123號',
    '台北市中山區民權東路88號',
    '台北市萬華區西門町168號',
    '台北市士林區天母東路99號'
  ]

  renewalData.name = testNames[Math.floor(Math.random() * testNames.length)]
  renewalData.area = Math.floor(Math.random() * 5000) + 1000
  renewalData.member_count = Math.floor(Math.random() * 100) + 20
  renewalData.chairman_name = testChairmanNames[Math.floor(Math.random() * testChairmanNames.length)]
  renewalData.chairman_phone = `09${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
  renewalData.address = testAddresses[Math.floor(Math.random() * testAddresses.length)]
  renewalData.representative = testChairmanNames[Math.floor(Math.random() * testChairmanNames.length)]

  $swal.fire({
    title: '基本資料已填入',
    text: '基本資訊已自動填入測試資料',
    icon: 'success',
    timer: 1500,
    timerProgressBar: true,
    showConfirmButton: false
  })
}

// Fill land plot test data
const fillLandPlotTestData = async () => {
  // Set to Taipei City and 大安區
  landForm.county = '臺北市'
  await onCountyChange() // Wait for districts to load

  // Set district after county data is loaded
  if (districts.value.length > 0) {
    landForm.district = '大安區'
    await onDistrictChange() // Wait for sections to load

    // Set section after district data is loaded
    if (sections.value.length > 0) {
      landForm.section = sections.value[0]?.name || '大安段'
    }
  }

  // Generate random land plot numbers
  landForm.landNumberMain = String(Math.floor(Math.random() * 999) + 1).padStart(4, '0')
  landForm.landNumberSub = String(Math.floor(Math.random() * 99)).padStart(4, '0')
  landForm.landArea = Math.floor(Math.random() * 500) + 100

  $swal.fire({
    title: '測試地號已填入',
    text: '地號表單已自動填入測試資料',
    icon: 'success',
    timer: 1500,
    timerProgressBar: true,
    showConfirmButton: false
  })
}

const goBack = () => {
  router.push('/tables/urban-renewal')
}

// Watch for name changes to update page title
watch(() => renewalData.name, updatePageTitle)

// Load data when component mounts
onMounted(async () => {
  await Promise.all([
    fetchCounties(),
    fetchRenewalData(),
    fetchLandPlots()
  ])
})
</script>