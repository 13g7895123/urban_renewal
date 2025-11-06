<template>
  <NuxtLayout name="main">
    <template #title>編輯所有權人</template>

    <div class="p-8">
      <form @submit.prevent="onSubmit" class="max-w-6xl mx-auto">
        <!-- 基本資料 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">基本資料</h2>
            <button
              type="button"
              @click="fillTestData"
              class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
            >
              <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
              填入測試資料
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 所屬更新會 -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所屬更新會 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="urbanRenewalName"
                type="text"
                readonly
                class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none cursor-not-allowed"
              />
            </div>

            <!-- 所有權人名稱 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人名稱 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.owner_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入所有權人名稱"
              />
            </div>

            <!-- 所有權人身分證字號 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人身分證字號
              </label>
              <input
                v-model="formData.identity_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入身分證字號"
              />
            </div>

            <!-- 所有權人編號 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人編號
              </label>
              <input
                v-model="formData.owner_code"
                type="text"
                readonly
                class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none cursor-not-allowed"
              />
            </div>

            <!-- 所有權人電話1 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人電話1
              </label>
              <input
                v-model="formData.phone1"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入電話號碼"
              />
            </div>

            <!-- 所有權人電話2 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人電話2
              </label>
              <input
                v-model="formData.phone2"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入電話號碼"
              />
            </div>

            <!-- 所有權人聯絡地址 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人聯絡地址
              </label>
              <input
                v-model="formData.contact_address"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入聯絡地址"
              />
            </div>

            <!-- 所有權人戶籍地址 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                所有權人戶籍地址
              </label>
              <input
                v-model="formData.registered_address"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入戶籍地址"
              />
            </div>

            <!-- 排除計算 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                排除計算
              </label>
              <select
                v-model="formData.exclusion_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="">請選擇排除類型</option>
                <option value="法院囑託查封">法院囑託查封</option>
                <option value="假扣押">假扣押</option>
                <option value="假處分">假處分</option>
                <option value="破產登記">破產登記</option>
                <option value="未經繼承">未經繼承</option>
              </select>
            </div>
          </div>
        </div>

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

        <!-- 建號列表 -->
        <div v-if="formData.buildings.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">建號列表</h3>
            <button
              type="button"
              @click="reloadBuildings"
              :disabled="isReloadingBuildings"
              class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <Icon name="heroicons:arrow-path" :class="['w-4 h-4 mr-1', { 'animate-spin': isReloadingBuildings }]" />
              重新整理
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="p-3 text-left text-sm font-medium text-green-600">縣市/行政區/段小段</th>
                  <th class="p-3 text-left text-sm font-medium text-green-600">建號</th>
                  <th class="p-3 text-left text-sm font-medium text-green-600">建物總面積</th>
                  <th class="p-3 text-left text-sm font-medium text-green-600">持有比例</th>
                  <th class="p-3 text-center text-sm font-medium text-green-600">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(building, index) in formData.buildings" :key="index" class="border-b border-gray-100">
                  <td class="p-3 text-sm">{{ building.location || '-' }}</td>
                  <td class="p-3 text-sm">{{ formatBuildingNumber(building) }}</td>
                  <td class="p-3 text-sm">{{ building.building_area || building.area || '-' }}</td>
                  <td class="p-3 text-sm">{{ building.ownership_numerator || 0 }}/{{ building.ownership_denominator || 1 }}</td>
                  <td class="p-3 text-center">
                    <button
                      type="button"
                      @click="removeBuilding(index)"
                      class="text-red-500 hover:text-red-700"
                    >
                      <Icon name="heroicons:trash" class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 地號列表 -->
        <div v-if="formData.lands.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">地號列表</h3>
            <button
              type="button"
              @click="reloadLands"
              :disabled="isReloadingLands"
              class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <Icon name="heroicons:arrow-path" :class="['w-4 h-4 mr-1', { 'animate-spin': isReloadingLands }]" />
              重新整理
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="p-3 text-left text-sm font-medium text-green-600">地號</th>
                  <th class="p-3 text-left text-sm font-medium text-green-600">土地總面積</th>
                  <th class="p-3 text-left text-sm font-medium text-green-600">持有比例</th>
                  <th class="p-3 text-center text-sm font-medium text-green-600">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(land, index) in formData.lands" :key="index" class="border-b border-gray-100">
                  <td class="p-3 text-sm">{{ formatLandNumber(land) }}</td>
                  <td class="p-3 text-sm">{{ land.total_area || land.land_area || '-' }}</td>
                  <td class="p-3 text-sm">{{ land.ownership_numerator || 0 }}/{{ land.ownership_denominator || 1 }}</td>
                  <td class="p-3 text-center">
                    <button
                      type="button"
                      @click="removeLand(index)"
                      class="text-red-500 hover:text-red-700"
                    >
                      <Icon name="heroicons:trash" class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

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

      <!-- Modals (same as create page) -->
      <!-- Add Land Modal -->
      <div v-if="showAddLandModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showAddLandModal = false"></div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900">新增地號</h3>
              <button
                type="button"
                @click="fillLandTestData"
                class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
              >
                <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
                填入測試資料
              </button>
            </div>

            <form @submit.prevent="addLand">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">地號</label>
                  <select
                    v-model="landForm.plot_number"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  >
                    <option value="">請選擇地號</option>
                    <option v-for="plot in availablePlots" :key="plot.id" :value="plot.landNumber || plot.plot_number">
                      {{ plot.chineseFullLandNumber || plot.fullLandNumber || plot.plot_number }}
                      <span v-if="plot.isRepresentative" class="text-blue-600 font-medium"> (代表號)</span>
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">土地總面積(平方公尺)</label>
                  <input
                    v-model="landForm.total_area"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="請輸入土地總面積"
                  />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分子</label>
                    <input
                      v-model="landForm.ownership_numerator"
                      type="number"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="分子"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分母</label>
                    <input
                      v-model="landForm.ownership_denominator"
                      type="number"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="分母"
                    />
                  </div>
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  @click="showAddLandModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  取消
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
                >
                  新增
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Add Building Modal -->
      <div v-if="showAddBuildingModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showAddBuildingModal = false"></div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900">新增建號</h3>
              <button
                type="button"
                @click="fillBuildingTestData"
                class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
              >
                <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
                填入測試資料
              </button>
            </div>

            <form @submit.prevent="addBuilding">
              <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">縣市</label>
                    <select
                      v-model="buildingForm.county"
                      @change="onBuildingCountyChange"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                      <option value="">請選擇縣市</option>
                      <option v-for="county in counties" :key="county.id" :value="county.code">
                        {{ county.name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">行政區</label>
                    <select
                      v-model="buildingForm.district"
                      @change="onBuildingDistrictChange"
                      required
                      :disabled="!buildingForm.county"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    >
                      <option value="">請選擇行政區</option>
                      <option v-for="district in buildingDistricts" :key="district.id" :value="district.code">
                        {{ district.name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">段小段</label>
                    <select
                      v-model="buildingForm.section"
                      required
                      :disabled="!buildingForm.district"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                    >
                      <option value="">請選擇段小段</option>
                      <option v-for="section in buildingSections" :key="section.id" :value="section.code">
                        {{ section.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">建號母號</label>
                    <input
                      v-model="buildingForm.building_number_main"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="例：00001"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">建號子號</label>
                    <input
                      v-model="buildingForm.building_number_sub"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="例：000"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">建物總面積(平方公尺)</label>
                  <input
                    v-model="buildingForm.building_area"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="請輸入建物總面積"
                  />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分子</label>
                    <input
                      v-model="buildingForm.ownership_numerator"
                      type="number"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="分子"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分母</label>
                    <input
                      v-model="buildingForm.ownership_denominator"
                      type="number"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="分母"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">建物門牌</label>
                  <input
                    v-model="buildingForm.building_address"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="請輸入建物門牌"
                  />
                </div>
              </div>

              <div class="flex justify-end gap-3 mt-6">
                <button
                  type="button"
                  @click="showAddBuildingModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  取消
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
                >
                  新增
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'

// Use SweetAlert composable
const { showSuccess, showError } = useSweetAlert()

const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()

// Use API composable for authenticated requests
const { get, put } = useApi()

// Get API base URL for development vs production
const getApiBaseUrl = () => {
  const isDev = process.dev || process.env.NODE_ENV === 'development'
  return isDev ? 'http://localhost:9228' : (runtimeConfig.public.apiBaseUrl || '')
}
const apiBaseUrl = getApiBaseUrl()

// Get IDs from route (reactive)
const urbanRenewalId = computed(() => route.params.id)
const ownerId = computed(() => route.params.ownerId)

const isSubmitting = ref(false)
const loading = ref(true)
const urbanRenewalName = ref('')
const showAddLandModal = ref(false)
const showAddBuildingModal = ref(false)
const availablePlots = ref([])
const isReloadingBuildings = ref(false)
const isReloadingLands = ref(false)

// Location data
const counties = ref([])
const buildingDistricts = ref([])
const buildingSections = ref([])

// Form data
const formData = reactive({
  id: ownerId.value,
  urban_renewal_id: urbanRenewalId.value,
  owner_name: '',
  identity_number: '',
  owner_code: '',
  phone1: '',
  phone2: '',
  contact_address: '',
  registered_address: '',
  exclusion_type: '',
  buildings: [],
  lands: [],
  notes: ''
})

// Land form
const landForm = reactive({
  plot_number: '',
  total_area: '',
  ownership_numerator: '',
  ownership_denominator: ''
})

// Building form
const buildingForm = reactive({
  county: '',
  district: '',
  section: '',
  building_number_main: '',
  building_number_sub: '',
  building_area: '',
  ownership_numerator: '',
  ownership_denominator: '',
  building_address: ''
})

// Fetch property owner data
const fetchPropertyOwner = async () => {
  try {
    const response = await get(`/property-owners/${ownerId.value}`)

    if (response.data?.status === 'success') {
      const data = response.data.data
      Object.assign(formData, {
        id: data.id,
        urban_renewal_id: data.urban_renewal_id,
        owner_name: data.name || '',  // Backend returns 'name', not 'owner_name'
        identity_number: data.id_number || '',  // Backend returns 'id_number', not 'identity_number'
        owner_code: data.owner_code || '',
        phone1: data.phone1 || '',
        phone2: data.phone2 || '',
        contact_address: data.contact_address || '',
        registered_address: data.household_address || '',  // Backend returns 'household_address', not 'registered_address'
        exclusion_type: data.exclusion_type || '',
        buildings: data.buildings || [],
        lands: data.lands || [],
        notes: data.notes || ''
      })
    }
  } catch (err) {
    console.error('Failed to fetch property owner:', err)
    showError('載入失敗', '無法載入所有權人資料')
  } finally {
    loading.value = false
  }
}

// Fetch counties
const fetchCounties = async () => {
  try {
    const response = await get('/locations/counties')
    if (response.data?.status === 'success') {
      counties.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch counties:', err)
  }
}

// Fetch urban renewal info
const fetchUrbanRenewalInfo = async () => {
  try {
    const response = await get(`/urban-renewals/${urbanRenewalId.value}`)

    if (response.data?.status === 'success') {
      urbanRenewalName.value = response.data.data.name
    }
  } catch (err) {
    console.error('Failed to fetch urban renewal info:', err)
  }
}

// Fetch available land plots
const fetchAvailablePlots = async () => {
  try {
    const response = await get(`/urban-renewals/${urbanRenewalId.value}/land-plots`)

    if (response.data?.status === 'success') {
      availablePlots.value = response.data.data || []
    }
  } catch (err) {
    console.error('Failed to fetch land plots:', err)
  }
}

// Add land to form (same logic as create page)
const addLand = () => {
  formData.lands.push({
    plot_number: landForm.plot_number,
    total_area: landForm.total_area,
    ownership_numerator: landForm.ownership_numerator,
    ownership_denominator: landForm.ownership_denominator
  })

  // Reset form
  landForm.plot_number = ''
  landForm.total_area = ''
  landForm.ownership_numerator = ''
  landForm.ownership_denominator = ''

  showAddLandModal.value = false
}

// Handle building county change
const onBuildingCountyChange = async () => {
  // Reset district and section when county changes
  buildingForm.district = ''
  buildingForm.section = ''
  buildingDistricts.value = []
  buildingSections.value = []

  if (!buildingForm.county) return

  try {
    const response = await get(`/locations/districts/${buildingForm.county}`)
    if (response.data?.status === 'success') {
      buildingDistricts.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching districts:', error)
  }
}

// Handle building district change
const onBuildingDistrictChange = async () => {
  // Reset section when district changes
  buildingForm.section = ''
  buildingSections.value = []

  if (!buildingForm.district) return

  try {
    const response = await get(`/locations/sections/${buildingForm.county}/${buildingForm.district}`)
    if (response.data?.status === 'success') {
      buildingSections.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching sections:', error)
  }
}

// Add building to form
const addBuilding = () => {
  // Get the Chinese names for display
  const countyObj = counties.value.find(c => c.code === buildingForm.county)
  const districtObj = buildingDistricts.value.find(d => d.code === buildingForm.district)
  const sectionObj = buildingSections.value.find(s => s.code === buildingForm.section)

  const countyName = countyObj ? countyObj.name : buildingForm.county
  const districtName = districtObj ? districtObj.name : buildingForm.district
  const sectionName = sectionObj ? sectionObj.name : buildingForm.section

  formData.buildings.push({
    county: buildingForm.county,
    district: buildingForm.district,
    section: buildingForm.section,
    location: `${countyName}/${districtName}/${sectionName}`,
    building_number_main: buildingForm.building_number_main,
    building_number_sub: buildingForm.building_number_sub,
    building_area: buildingForm.building_area,
    ownership_numerator: buildingForm.ownership_numerator,
    ownership_denominator: buildingForm.ownership_denominator,
    building_address: buildingForm.building_address
  })

  // Reset form
  buildingForm.county = ''
  buildingForm.district = ''
  buildingForm.section = ''
  buildingForm.building_number_main = ''
  buildingForm.building_number_sub = ''
  buildingForm.building_area = ''
  buildingForm.ownership_numerator = ''
  buildingForm.ownership_denominator = ''
  buildingForm.building_address = ''

  // Reset cascading dropdowns
  buildingDistricts.value = []
  buildingSections.value = []

  showAddBuildingModal.value = false
}

// Remove land
const removeLand = (index) => {
  formData.lands.splice(index, 1)
}

// Remove building
const removeBuilding = (index) => {
  formData.buildings.splice(index, 1)
}

// Submit form
const onSubmit = async () => {
  if (!formData.owner_name) {
    showError('驗證失敗', '請填寫所有權人名稱')
    return
  }

  isSubmitting.value = true

  try {
    // Send formData as-is - backend expects owner_name, identity_number, registered_address
    const response = await put(`/property-owners/${ownerId.value}`, formData)

    if (response.data?.status === 'success') {
      await showSuccess('更新成功！', '所有權人資料已成功更新')
      router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners`)
    } else {
      showError('更新失敗', response.data?.message || '更新失敗')
    }
  } catch (err) {
    console.error('Submit error:', err)
    showError('更新失敗', '更新失敗，請稍後再試')
  } finally {
    isSubmitting.value = false
  }
}

// Fill test data for main form
const fillTestData = () => {
  const alert = useAlert()

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

  alert.success('測試資料已填入', '所有表單欄位已自動填入測試資料')
}

// Fill test data for land form
const fillLandTestData = () => {
  const alert = useAlert()

  if (availablePlots.value.length === 0) {
    alert.warning('無可用地號', '請先新增地號到更新會')
    return
  }

  // Select random plot - use landNumber or plot_number to match the select value
  const randomPlot = availablePlots.value[Math.floor(Math.random() * availablePlots.value.length)]
  landForm.plot_number = randomPlot.landNumber || randomPlot.plot_number

  // Random area and ownership
  landForm.total_area = (Math.random() * 500 + 100).toFixed(2)
  landForm.ownership_numerator = Math.floor(Math.random() * 10) + 1
  landForm.ownership_denominator = Math.floor(Math.random() * 100) + 10

  alert.success('測試地號已填入', '地號表單已自動填入測試資料')
}

// Fill test data for building form
const fillBuildingTestData = async () => {
  const alert = useAlert()

  if (counties.value.length === 0) {
    alert.warning('無可用縣市', '請稍後再試')
    return
  }

  // Select random county from available options
  const randomCounty = counties.value[Math.floor(Math.random() * counties.value.length)]
  buildingForm.county = randomCounty.code

  // Fetch districts for selected county
  await onBuildingCountyChange()

  if (buildingDistricts.value.length === 0) {
    alert.warning('無可用行政區', '該縣市沒有可用的行政區資料')
    return
  }

  // Select random district
  const randomDistrict = buildingDistricts.value[Math.floor(Math.random() * buildingDistricts.value.length)]
  buildingForm.district = randomDistrict.code

  // Fetch sections for selected district
  await onBuildingDistrictChange()

  if (buildingSections.value.length === 0) {
    alert.warning('無可用段小段', '該行政區沒有可用的段小段資料')
    return
  }

  // Select random section
  const randomSection = buildingSections.value[Math.floor(Math.random() * buildingSections.value.length)]
  buildingForm.section = randomSection.code

  // Fill other fields
  buildingForm.building_number_main = String(Math.floor(Math.random() * 9999) + 1).padStart(5, '0')
  buildingForm.building_number_sub = String(Math.floor(Math.random() * 999)).padStart(3, '0')
  buildingForm.building_area = (Math.random() * 200 + 50).toFixed(2)
  buildingForm.ownership_numerator = Math.floor(Math.random() * 10) + 1
  buildingForm.ownership_denominator = Math.floor(Math.random() * 100) + 10
  buildingForm.building_address = `${randomCounty.name}${randomDistrict.name}測試路${Math.floor(Math.random() * 999) + 1}號`

  alert.success('測試建號已填入', '建號表單已自動填入測試資料')
}

// Format building number for display
const formatBuildingNumber = (building) => {
  const main = building.building_number_main || building.buildingNumberMain || ''
  const sub = building.building_number_sub || building.buildingNumberSub || ''
  
  if (!main && !sub) return '-'
  if (!sub || sub === '0' || sub === 0) return main
  return `${main}-${sub}`
}

// Format land number for display
const formatLandNumber = (land) => {
  // Check if plot_number is already formatted
  if (land.plot_number && land.plot_number.includes('-')) {
    return land.plot_number
  }
  
  // Try to construct from main and sub
  const main = land.land_number_main || land.landNumberMain || ''
  const sub = land.land_number_sub || land.landNumberSub || ''
  
  if (!main && !sub) return land.plot_number || '-'
  if (!sub || sub === '0' || sub === 0) return main
  return `${main}-${sub}`
}

// Reload buildings from server
const reloadBuildings = async () => {
  isReloadingBuildings.value = true
  try {
    const response = await get(`/property-owners/${ownerId.value}`)
    
    if (response.data?.status === 'success') {
      const data = response.data.data
      formData.buildings = data.buildings || []
      
      console.log('[Reload Buildings] Raw data:', data.buildings)
      console.log('[Reload Buildings] Formatted:', formData.buildings.map(b => ({
        location: b.location,
        building_number_main: b.building_number_main,
        building_number_sub: b.building_number_sub,
        formatted: formatBuildingNumber(b)
      })))
      
      showSuccess('重新整理成功', `已載入 ${formData.buildings.length} 筆建號資料`)
    }
  } catch (err) {
    console.error('Failed to reload buildings:', err)
    showError('重新整理失敗', '無法載入建號資料')
  } finally {
    isReloadingBuildings.value = false
  }
}

// Reload lands from server
const reloadLands = async () => {
  isReloadingLands.value = true
  try {
    const response = await get(`/property-owners/${ownerId.value}`)
    
    if (response.data?.status === 'success') {
      const data = response.data.data
      formData.lands = data.lands || []
      
      console.log('[Reload Lands] Raw data:', data.lands)
      console.log('[Reload Lands] Formatted:', formData.lands.map(l => ({
        plot_number: l.plot_number,
        land_number_main: l.land_number_main,
        land_number_sub: l.land_number_sub,
        formatted: formatLandNumber(l)
      })))
      
      showSuccess('重新整理成功', `已載入 ${formData.lands.length} 筆地號資料`)
    }
  } catch (err) {
    console.error('Failed to reload lands:', err)
    showError('重新整理失敗', '無法載入地號資料')
  } finally {
    isReloadingLands.value = false
  }
}

// Go back
const goBack = () => {
  router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners`)
}

// Initialize
onMounted(async () => {
  await Promise.all([
    fetchPropertyOwner(),
    fetchUrbanRenewalInfo(),
    fetchAvailablePlots(),
    fetchCounties()
  ])
})
</script>