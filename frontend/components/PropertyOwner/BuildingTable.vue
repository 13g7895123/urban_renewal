<template>
  <div v-if="buildings.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">建號列表</h3>
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
            <th class="p-3 text-left text-sm font-medium text-green-600">縣市/行政區/段小段</th>
            <th class="p-3 text-left text-sm font-medium text-green-600">建號</th>
            <th class="p-3 text-left text-sm font-medium text-green-600">建物總面積</th>
            <th class="p-3 text-left text-sm font-medium text-green-600">持有比例</th>
            <th class="p-3 text-center text-sm font-medium text-green-600">操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(building, index) in buildings" :key="index" class="border-b border-gray-100">
            <td class="p-3 text-sm">{{ building.location || '-' }}</td>
            <td class="p-3 text-sm">{{ formatBuilding(building) }}</td>
            <td class="p-3 text-sm">{{ building.building_area || building.area || '-' }}</td>
            <td class="p-3 text-sm">{{ building.ownership_numerator || 0 }}/{{ building.ownership_denominator || 1 }}</td>
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
  buildings: {
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
 * 格式化建號顯示
 */
const formatBuilding = (building) => {
  if (props.formatFunction) {
    return props.formatFunction(building)
  }
  // 預設格式化
  return `${building.building_number_main}-${building.building_number_sub}`
}
</script>
