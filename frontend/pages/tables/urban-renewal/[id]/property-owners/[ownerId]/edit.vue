<template>
  <NuxtLayout name="main">
    <template #title>編輯所有權人</template>

    <div class="p-8">
      <form @submit.prevent="onSubmit" class="max-w-6xl mx-auto">
        <!-- 基本資料 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">基本資料</h2>

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
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">建號列表</h3>
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
                  <td class="p-3 text-sm">{{ building.location }}</td>
                  <td class="p-3 text-sm">{{ building.building_number_main }}-{{ building.building_number_sub }}</td>
                  <td class="p-3 text-sm">{{ building.building_area }}</td>
                  <td class="p-3 text-sm">{{ building.ownership_numerator }}/{{ building.ownership_denominator }}</td>
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
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">地號列表</h3>
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
                  <td class="p-3 text-sm">{{ land.plot_number }}</td>
                  <td class="p-3 text-sm">{{ land.total_area }}</td>
                  <td class="p-3 text-sm">{{ land.ownership_numerator }}/{{ land.ownership_denominator }}</td>
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
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAddLandModal = false"></div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-gray-900">新增地號</h3>
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
                    <option v-for="plot in availablePlots" :key="plot.id" :value="plot.plot_number">
                      {{ plot.plot_number }}
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
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAddBuildingModal = false"></div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-gray-900">新增建號</h3>
            </div>

            <form @submit.prevent="addBuilding">
              <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">縣市</label>
                    <select
                      v-model="buildingForm.county"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                      <option value="">請選擇縣市</option>
                      <option value="台北市">台北市</option>
                      <option value="新北市">新北市</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">行政區</label>
                    <select
                      v-model="buildingForm.district"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                      <option value="">請選擇行政區</option>
                      <option value="大安區">大安區</option>
                      <option value="信義區">信義區</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">段小段</label>
                    <select
                      v-model="buildingForm.section"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                      <option value="">請選擇段小段</option>
                      <option value="大安段">大安段</option>
                      <option value="信義段">信義段</option>
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

// Use SweetAlert2
const { $swal } = useNuxtApp()

const route = useRoute()
const router = useRouter()
const runtimeConfig = useRuntimeConfig()

// Get IDs from route (reactive)
const urbanRenewalId = computed(() => route.params.id)
const ownerId = computed(() => route.params.ownerId)

const isSubmitting = ref(false)
const loading = ref(true)
const urbanRenewalName = ref('')
const showAddLandModal = ref(false)
const showAddBuildingModal = ref(false)
const availablePlots = ref([])

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
    const response = await $fetch(`http://localhost:9228/api/property-owners/${ownerId.value}`, {
    })

    if (response.status === 'success') {
      const data = response.data
      Object.assign(formData, {
        id: data.id,
        urban_renewal_id: data.urban_renewal_id,
        owner_name: data.owner_name || '',
        identity_number: data.identity_number || '',
        owner_code: data.owner_code || '',
        phone1: data.phone1 || '',
        phone2: data.phone2 || '',
        contact_address: data.contact_address || '',
        registered_address: data.registered_address || '',
        exclusion_type: data.exclusion_type || '',
        buildings: data.buildings || [],
        lands: data.lands || [],
        notes: data.notes || ''
      })
    }
  } catch (err) {
    console.error('Failed to fetch property owner:', err)
    $swal.fire({
      title: '載入失敗',
      text: '無法載入所有權人資料',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    loading.value = false
  }
}

// Fetch urban renewal info
const fetchUrbanRenewalInfo = async () => {
  try {
    const response = await $fetch(`http://localhost:9228/api/urban-renewals/${urbanRenewalId.value}`, {
    })

    if (response.status === 'success') {
      urbanRenewalName.value = response.data.name
    }
  } catch (err) {
    console.error('Failed to fetch urban renewal info:', err)
  }
}

// Fetch available land plots
const fetchAvailablePlots = async () => {
  try {
    const response = await $fetch(`http://localhost:9228/api/urban-renewals/${urbanRenewalId.value}/land-plots`, {
    })

    if (response.status === 'success') {
      availablePlots.value = response.data || []
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

// Add building to form (same logic as create page)
const addBuilding = () => {
  formData.buildings.push({
    county: buildingForm.county,
    district: buildingForm.district,
    section: buildingForm.section,
    location: `${buildingForm.county}/${buildingForm.district}/${buildingForm.section}`,
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
    $swal.fire({
      title: '驗證失敗',
      text: '請填寫所有權人名稱',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
    return
  }

  isSubmitting.value = true

  try {
    const response = await $fetch(`http://localhost:9228/api/property-owners/${ownerId.value}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: formData
    })

    if (response.status === 'success') {
      $swal.fire({
        title: '更新成功！',
        text: '所有權人資料已成功更新',
        icon: 'success',
        confirmButtonText: '確定',
        confirmButtonColor: '#10b981'
      }).then(() => {
        router.push(`/tables/urban-renewal/${urbanRenewalId.value}/property-owners`)
      })
    } else {
      $swal.fire({
        title: '更新失敗',
        text: response.message || '更新失敗',
        icon: 'error',
        confirmButtonText: '確定',
        confirmButtonColor: '#ef4444'
      })
    }
  } catch (err) {
    console.error('Submit error:', err)
    $swal.fire({
      title: '更新失敗',
      text: '更新失敗，請稍後再試',
      icon: 'error',
      confirmButtonText: '確定',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    isSubmitting.value = false
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
    fetchAvailablePlots()
  ])
})
</script>