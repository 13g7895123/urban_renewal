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

        <!-- 地號和建號新增區域元件 -->
        <PropertyOwnerLandBuildingActionBar
          @add-land="showAddLandModal = true"
          @add-building="showAddBuildingModal = true"
        />

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
        v-model="landForm"
        :is-open="showAddLandModal"
        :available-plots="availablePlots"
        :show-test-button="true"
        @close="showAddLandModal = false"
        @submit="addLand"
        @fill-test-data="fillLandTestData"
      />

      <!-- Building Modal 元件 -->
      <PropertyOwnerBuildingModal
        v-model="buildingForm"
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
  initialize,

  // 測試資料填充
  fillTestData,
  fillLandTestData,
  fillBuildingTestData
} = usePropertyOwnerForm({
  mode: 'edit',
  urbanRenewalId: urbanRenewalId.value,
  ownerId: ownerId.value
})

// Initialize
onMounted(() => {
  initialize()
})
</script>