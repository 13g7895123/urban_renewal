<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- 背景遮罩 -->
      <div
        class="fixed inset-0 bg-gray-200/75 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal 內容 -->
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
        <!-- Modal 標題列 -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">新增建號</h3>
          <button
            v-if="showTestButton"
            type="button"
            @click="$emit('fillTestData')"
            class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
          >
            <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
            填入測試資料
          </button>
        </div>

        <!-- 表單 -->
        <form @submit.prevent="handleSubmit">
          <div class="space-y-4">
            <!-- 縣市/行政區/段小段 -->
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">縣市</label>
                <select
                  :value="modelValue.county"
                  @change="updateField('county', $event.target.value); handleCountyChange()"
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
                  :value="modelValue.district"
                  @change="updateField('district', $event.target.value); handleDistrictChange()"
                  required
                  :disabled="!modelValue.county"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                  <option value="">請選擇行政區</option>
                  <option v-for="district in districts" :key="district.id" :value="district.code">
                    {{ district.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">段小段</label>
                <select
                  :value="modelValue.section"
                  @input="updateField('section', $event.target.value)"
                  required
                  :disabled="!modelValue.district"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                  <option value="">請選擇段小段</option>
                  <option v-for="section in sections" :key="section.id" :value="section.code">
                    {{ section.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- 建號母號/子號 -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">建號母號</label>
                <input
                  :value="modelValue.building_number_main"
                  @input="updateField('building_number_main', $event.target.value)"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="例：00001"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">建號子號</label>
                <input
                  :value="modelValue.building_number_sub"
                  @input="updateField('building_number_sub', $event.target.value)"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="例：000"
                />
              </div>
            </div>

            <!-- 建物總面積 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">建物總面積(平方公尺)</label>
              <input
                :value="modelValue.building_area"
                @input="updateField('building_area', $event.target.value)"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入建物總面積"
              />
            </div>

            <!-- 持有比例 -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分子</label>
                <input
                  :value="modelValue.ownership_numerator"
                  @input="updateField('ownership_numerator', $event.target.value)"
                  type="number"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="分子"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">持有比例分母</label>
                <input
                  :value="modelValue.ownership_denominator"
                  @input="updateField('ownership_denominator', $event.target.value)"
                  type="number"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="分母"
                />
              </div>
            </div>

            <!-- 建物門牌 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">建物門牌</label>
              <input
                :value="modelValue.building_address"
                @input="updateField('building_address', $event.target.value)"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入建物門牌"
              />
            </div>
          </div>

          <!-- 按鈕區 -->
          <div class="flex justify-end gap-3 mt-6">
            <button
              type="button"
              @click="$emit('close')"
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
</template>

<script setup>

/**
 * Props
 */
const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  counties: {
    type: Array,
    required: true
  },
  districts: {
    type: Array,
    required: true
  },
  sections: {
    type: Array,
    required: true
  },
  showTestButton: {
    type: Boolean,
    default: false
  },
  modelValue: {
    type: Object,
    required: true
  }
})

/**
 * Emits
 */
const emit = defineEmits(['close', 'submit', 'fillTestData', 'countyChange', 'districtChange', 'update:modelValue'])

/**
 * 更新單一欄位
 */
const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value
  })
}

/**
 * 處理縣市改變
 */
const handleCountyChange = () => {
  emit('countyChange', props.modelValue.county)
}

/**
 * 處理行政區改變
 */
const handleDistrictChange = () => {
  emit('districtChange', props.modelValue.county, props.modelValue.district)
}

/**
 * 處理表單提交
 */
const handleSubmit = () => {
  emit('submit', { ...props.modelValue })
  // 表單重置交給父元件的 resetBuildingForm() 處理，避免破壞 reactive 物件
}
</script>
