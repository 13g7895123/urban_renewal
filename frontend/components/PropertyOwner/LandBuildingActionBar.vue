<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold text-gray-900">地號和建號管理</h2>
      <div class="flex gap-2">
        <button
          type="button"
          @click="$emit('addLand')"
          class="inline-flex items-center px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
          新增地號
        </button>
        <button
          type="button"
          @click="$emit('addBuilding')"
          class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
          新增建號
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * Emits
 */
const emit = defineEmits(['addLand', 'addBuilding'])
</script>
