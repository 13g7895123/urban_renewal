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
        class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal 內容 -->
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <!-- Modal 標題列 -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">新增地號</h3>
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
            <!-- 地號選擇 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">地號</label>
              <select
                :value="modelValue.plot_number"
                @input="updateField('plot_number', $event.target.value)"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="">請選擇地號</option>
                <option
                  v-for="plot in availablePlots"
                  :key="plot.id"
                  :value="plot.landNumber || plot.plot_number"
                >
                  {{ plot.chineseFullLandNumber || plot.fullLandNumber || plot.plot_number }}
                  <span v-if="plot.isRepresentative" class="text-blue-600 font-medium"> (代表號)</span>
                </option>
              </select>
            </div>

            <!-- 土地總面積 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">土地總面積(平方公尺)</label>
              <input
                :value="modelValue.total_area"
                @input="updateField('total_area', $event.target.value)"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="請輸入土地總面積"
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
import { computed } from 'vue'

/**
 * Props
 */
const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  availablePlots: {
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
const emit = defineEmits(['close', 'submit', 'fillTestData', 'update:modelValue'])

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
 * 處理表單提交
 */
const handleSubmit = () => {
  emit('submit', { ...props.modelValue })
  // 表單重置交給父元件的 resetLandForm() 處理，避免破壞 reactive 物件
}
</script>
