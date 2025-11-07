<template>
  <NuxtLayout name="main">
    <template #title>新增所有權人</template>

    <!-- Loading Overlay -->
    <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-white">
      <div class="text-center">
        <div class="mb-4">
          <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-green-500 border-t-transparent"></div>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">載入資料中...</h3>
        <div class="w-64 bg-gray-200 rounded-full h-2">
          <div
            class="bg-green-500 h-2 rounded-full transition-all duration-300"
            :style="{ width: `${loadingProgress}%` }"
          ></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">{{ loadingProgress }}%</p>
      </div>
    </div>

    <div class="p-8" :class="{ 'opacity-50 pointer-events-none': isLoading }">
      <form @submit.prevent="onSubmit" class="max-w-6xl mx-auto">
        <!-- 基本資料元件 -->
        <PropertyOwnerBaseInfoForm
          :form-data="formData"
          :urban-renewal-name="urbanRenewalName"
          :show-preview-button="true"
          @fill-test-data="fillTestData"
          @preview-submit-data="previewSubmitData"
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
          @remove="removeBuilding"
        />

        <!-- 地號列表元件 -->
        <PropertyOwnerLandTable
          :lands="formData.lands"
          @remove="removeLand"
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
            :disabled="isSubmitting || isLoading"
            class="px-6 py-2 text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isSubmitting ? '儲存中...' : isLoading ? '載入中...' : '儲存' }}
          </button>
        </div>
      </form>

      <!-- Land Modal 元件 -->
      <PropertyOwnerLandModal
        :is-open="showAddLandModal"
        :available-plots="availablePlots"
        @close="showAddLandModal = false"
        @submit="addLand"
      />

      <!-- Building Modal 元件 -->
      <PropertyOwnerBuildingModal
        :is-open="showAddBuildingModal"
        :counties="counties"
        :districts="buildingDistricts"
        :sections="buildingSections"
        @close="showAddBuildingModal = false"
        @submit="addBuilding"
        @county-change="onBuildingCountyChange"
        @district-change="onBuildingDistrictChange"
      />
    </div>
  </NuxtLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'

// Use SweetAlert composable
const { showSuccess, showCustom } = useSweetAlert()

const route = useRoute()

// Get urban renewal ID from route (reactive)
const urbanRenewalId = computed(() => route.params.id)

// Use Property Owner Form composable with create mode
const {
  // 狀態
  isLoading,
  loadingProgress,
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

  // 方法
  addLand,
  removeLand,
  addBuilding,
  removeBuilding,
  onBuildingCountyChange,
  onBuildingDistrictChange,
  submit: onSubmit,
  goBack,
  initialize
} = usePropertyOwnerForm({
  mode: 'create',
  urbanRenewalId: urbanRenewalId.value
})

// 預覽提交資料
const previewSubmitData = () => {
  const submitData = {
    urban_renewal_id: formData.urban_renewal_id,
    owner_name: formData.owner_name,
    identity_number: formData.identity_number,
    phone1: formData.phone1,
    phone2: formData.phone2,
    contact_address: formData.contact_address,
    registered_address: formData.registered_address,
    exclusion_type: formData.exclusion_type,
    lands: formData.lands.map(land => {
      const selectedPlot = availablePlots.find(plot =>
        (plot.landNumber || plot.plot_number) === land.plot_number
      )

      return {
        plot_number: selectedPlot?.landNumberMain || land.plot_number.split('-')[0] || land.plot_number,
        plot_display: land.plot_number_display || land.plot_number,
        total_area: land.total_area,
        ownership_numerator: land.ownership_numerator,
        ownership_denominator: land.ownership_denominator
      }
    }),
    buildings: formData.buildings,
    notes: formData.notes
  }

  showCustom({
    title: '預覽提交資料',
    html: `
      <div class="text-left">
        <p><strong>所有權人名稱：</strong>${submitData.owner_name || '(未填寫)'}</p>
        <p><strong>身分證字號：</strong>${submitData.identity_number || '(未填寫)'}</p>
        <p><strong>電話：</strong>${submitData.phone1 || '(未填寫)'}</p>
        <p><strong>地址：</strong>${submitData.contact_address || '(未填寫)'}</p>
        <p><strong>地號數量：</strong>${submitData.lands.length}筆</p>
        ${submitData.lands.length > 0 ? '<p><strong>地號列表：</strong></p><ul>' + submitData.lands.map(land =>
          `<li>- ${land.plot_display} (${land.ownership_numerator}/${land.ownership_denominator})</li>`
        ).join('') + '</ul>' : ''}
        <p><strong>建號數量：</strong>${submitData.buildings.length}筆</p>
      </div>
    `,
    icon: 'info',
    timer: null,
    showConfirmButton: true,
    confirmButtonText: '關閉',
    toast: false,
    position: 'center'
  })

  console.log('預覽提交資料：', submitData)
}

// Fill test data
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
  formData.notes = `${randomName}的測試資料，包含相關地號和建號資訊。`

  // Show notification
  showSuccess('測試資料已填入', '所有表單欄位已自動填入測試資料')
}

// Initialize
onMounted(() => {
  initialize()
})
</script>