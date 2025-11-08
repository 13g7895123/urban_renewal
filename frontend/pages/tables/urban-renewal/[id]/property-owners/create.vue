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

        <!-- 地號和建號新增區域元件 -->
        <PropertyOwnerLandBuildingActionBar
          @add-land="showAddLandModal = true"
          @add-building="showAddBuildingModal = true"
        />

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
const { showCustom } = useSweetAlert()

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
  initialize,

  // 測試資料填充
  fillTestData,
  fillLandTestData,
  fillBuildingTestData
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

// Initialize
onMounted(() => {
  initialize()
})
</script>