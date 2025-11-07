<template>
  <div v-if="lands.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">地號列表</h3>
      <button
        v-if="showReloadButton"
        type="button"
        @click="$emit('reload')"
        :disabled="isReloading"
        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
      >
        <Icon name="heroicons:arrow-path" :class="['w-4 h-4 mr-1', { 'animate-spin': isReloading }]" />
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
          <tr v-for="(land, index) in lands" :key="index" class="border-b border-gray-100">
            <td class="p-3 text-sm">{{ formatLand(land) }}</td>
            <td class="p-3 text-sm">{{ land.total_area || land.land_area || '-' }}</td>
            <td class="p-3 text-sm">{{ land.ownership_numerator || 0 }}/{{ land.ownership_denominator || 1 }}</td>
            <td class="p-3 text-center">
              <button
                type="button"
                @click="$emit('remove', index)"
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
</template>

<script setup>
/**
 * Props
 */
const props = defineProps({
  lands: {
    type: Array,
    required: true
  },
  showReloadButton: {
    type: Boolean,
    default: false
  },
  isReloading: {
    type: Boolean,
    default: false
  },
  formatFunction: {
    type: Function,
    default: null
  }
})

/**
 * Emits
 */
const emit = defineEmits(['remove', 'reload'])

/**
 * 格式化地號顯示
 */
const formatLand = (land) => {
  if (props.formatFunction) {
    return props.formatFunction(land)
  }
  // 預設格式化
  return land.plot_number_display || land.plot_number || '-'
}
</script>
