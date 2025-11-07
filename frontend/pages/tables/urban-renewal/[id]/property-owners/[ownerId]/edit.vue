<template>
  <NuxtLayout name="main">
    <template #title>編輯所有權人</template>

    <div class="p-8">
      <form @submit.prevent="onSubmit" class="max-w-6xl mx-auto">
        <!-- 基本資料元件 -->
        <PropertyOwnerBaseInfoForm
          :form-data="formData"
          :urban-renewal-name="urbanRenewalName"
          owner-code-label="所有權人編號"
          @fill-test-data="fillTestData"
        />

        <!-- 地號和建號新增區域 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">地號和建號管理</h2>
            <div class="flex gap-2">
              <button
                type="button"
                @click="showAddLandModal = true"
                class="inline-flex items-center px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
              >
                <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
                新增地號
              </button>
              <button
                type="button"
                @click="showAddBuildingModal = true"
                class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
              >
                <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
                新增建號
              </button>
            </div>
          </div>
        </div>

        <!-- 建號列表元件 -->
        <PropertyOwnerBuildingTable
          :buildings="formData.buildings"
          :show-reload-button="true"
          :is-reloading="isReloadingBuildings"
          :format-function="formatBuildingNumber"
          @remove="removeBuilding"
          @reload="reloadBuildings"
        />

        <!-- 地號列表元件 -->
        <PropertyOwnerLandTable
          :lands="formData.lands"
          :show-reload-button="true"
          :is-reloading="isReloadingLands"
          :format-function="formatLandNumber"
          @remove="removeLand"
          @reload="reloadLands"
        />

        <!-- 備註 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">備註</h3>
          <textarea
            v-model="formData.notes"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
            placeholder="請輸入備註內容"
          ></textarea>
        </div>

        <!-- 表單按鈕 -->
        <div class="flex justify-end gap-4">
          <button
            type="button"
            @click="goBack"
            class="px-6 py-2 text-gray-700 bg-gray-200 border border-gray-300 rounded-lg hover:bg-gray-300 transition-colors duration-200"
          >
            回上一頁
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-2 text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isSubmitting ? '更新中...' : '更新' }}
          </button>
        </div>
      </form>

      <!-- Land Modal 元件 -->
      <PropertyOwnerLandModal
        :is-open="showAddLandModal"
        :available-plots="availablePlots"
        :show-test-button="true"
        @close="showAddLandModal = false"
        @submit="addLand"
        @fill-test-data="fillLandTestData"
      />

      <!-- Building Modal 元件 -->
      <PropertyOwnerBuildingModal
        :is-open="showAddBuildingModal"
        :counties="counties"
        :districts="buildingDistricts"
        :sections="buildingSections"
        :show-test-button="true"
        @close="showAddBuildingModal = false"
        @submit="addBuilding"
        @county-change="onBuildingCountyChange"
        @district-change="onBuildingDistrictChange"
        @fill-test-data="fillBuildingTestData"
      />
    </div>
  </NuxtLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'

// Use SweetAlert composable
const { showSuccess } = useSweetAlert()

const route = useRoute()

// Get IDs from route (reactive)
const urbanRenewalId = computed(() => route.params.id)
const ownerId = computed(() => route.params.ownerId)

// Use Property Owner Form composable with edit mode
const {
  // 狀態
  loading,
  isSubmitting,
  urbanRenewalName,
  availablePlots,
  formData,
  landForm,
  buildingForm,
  showAddLandModal,
  showAddBuildingModal,
  counties,
  buildingDistricts,
  buildingSections,
  isReloadingLands,
  isReloadingBuildings,

  // 方法
  addLand,
  removeLand,
  addBuilding,
  removeBuilding,
  onBuildingCountyChange,
  onBuildingDistrictChange,
  reloadLands,
  reloadBuildings,
  formatLandNumber,
  formatBuildingNumber,
  submit: onSubmit,
  goBack,
  initialize
} = usePropertyOwnerForm({
  mode: 'edit',
  urbanRenewalId: urbanRenewalId.value,
  ownerId: ownerId.value
})


// Fill test data for main form
const fillTestData = () => {
  const testNames = [
    '張三丰', '李四海', '王五明', '陳六福', '林七星',
    '黃八方', '劉九龍', '吳十全', '鄭一品', '謝二郎'
  ]

  const testExcludeReasons = [
    '法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承'
  ]

  const randomName = testNames[Math.floor(Math.random() * testNames.length)]

  // Generate random ID
  const idPrefixes = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2', 'E1', 'E2']
  const randomPrefix = idPrefixes[Math.floor(Math.random() * idPrefixes.length)]
  const randomNumbers = Math.floor(Math.random() * 100000000).toString().padStart(8, '0')

  // Fill form data
  formData.owner_name = randomName
  formData.identity_number = randomPrefix + randomNumbers
  formData.phone1 = `09${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
  formData.phone2 = `02-${Math.floor(Math.random() * 100000000).toString().padStart(8, '0')}`
  formData.contact_address = `台北市${['大安區', '信義區', '中山區', '松山區', '萬華區'][Math.floor(Math.random() * 5)]}${randomName}路${Math.floor(Math.random() * 999) + 1}號`
  formData.registered_address = `台北市${['中正區', '大同區', '中山區', '松山區', '大安區'][Math.floor(Math.random() * 5)]}${randomName}街${Math.floor(Math.random() * 999) + 1}號`
  formData.exclusion_type = testExcludeReasons[Math.floor(Math.random() * testExcludeReasons.length)]
  const now = new Date()
  const dateString = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日 ${now.getHours()}:${String(now.getMinutes()).padStart(2, '0')}`
  formData.notes = `${randomName}的測試資料，更新於${dateString}`

  showSuccess('測試資料已填入', '所有表單欄位已自動填入測試資料')
}

// Fill test data for land form
const fillLandTestData = () => {
  if (availablePlots.length === 0) {
    showSuccess('無可用地號', '請先新增地號到更新會')
    return
  }

  // Select random plot - use landNumber or plot_number to match the select value
  const randomPlot = availablePlots[Math.floor(Math.random() * availablePlots.length)]
  landForm.plot_number = randomPlot.landNumber || randomPlot.plot_number

  // Random area and ownership
  landForm.total_area = (Math.random() * 500 + 100).toFixed(2)
  landForm.ownership_numerator = Math.floor(Math.random() * 10) + 1
  landForm.ownership_denominator = Math.floor(Math.random() * 100) + 10

  showSuccess('測試地號已填入', '地號表單已自動填入測試資料')
}

// Fill test data for building form
const fillBuildingTestData = async () => {
  if (counties.length === 0) {
    showSuccess('無可用縣市', '請稍後再試')
    return
  }

  // Select random county from available options
  const randomCounty = counties[Math.floor(Math.random() * counties.length)]
  buildingForm.county = randomCounty.code

  // Fetch districts for selected county
  await onBuildingCountyChange()

  if (buildingDistricts.length === 0) {
    showSuccess('無可用行政區', '該縣市沒有可用的行政區資料')
    return
  }

  // Select random district
  const randomDistrict = buildingDistricts[Math.floor(Math.random() * buildingDistricts.length)]
  buildingForm.district = randomDistrict.code

  // Fetch sections for selected district
  await onBuildingDistrictChange()

  if (buildingSections.length === 0) {
    showSuccess('無可用段小段', '該行政區沒有可用的段小段資料')
    return
  }

  // Select random section
  const randomSection = buildingSections[Math.floor(Math.random() * buildingSections.length)]
  buildingForm.section = randomSection.code

  // Fill other fields
  buildingForm.building_number_main = String(Math.floor(Math.random() * 9999) + 1).padStart(5, '0')
  buildingForm.building_number_sub = String(Math.floor(Math.random() * 999)).padStart(3, '0')
  buildingForm.building_area = (Math.random() * 200 + 50).toFixed(2)
  buildingForm.ownership_numerator = Math.floor(Math.random() * 10) + 1
  buildingForm.ownership_denominator = Math.floor(Math.random() * 100) + 10
  buildingForm.building_address = `${randomCounty.name}${randomDistrict.name}測試路${Math.floor(Math.random() * 999) + 1}號`

  showSuccess('測試建號已填入', '建號表單已自動填入測試資料')
}

// Initialize
onMounted(() => {
  initialize()
})
</script>