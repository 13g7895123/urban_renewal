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
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
        <!-- Modal 標題列 -->
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
          <h3 class="text-lg font-semibold text-gray-900">分配更新會</h3>
          <button
            type="button"
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-500"
          >
            <Icon name="heroicons:x-mark" class="w-6 h-6" />
          </button>
        </div>

        <!-- 更新會列表 -->
        <div class="max-h-96 overflow-y-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  更新會名稱
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  分配管理者
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="renewal in urbanRenewals" :key="renewal.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                  {{ renewal.name }}
                </td>
                <td class="px-4 py-3 text-sm">
                  <select
                    v-model="assignments[renewal.id]"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    :disabled="!getManagersForRenewal(renewal.id) || getManagersForRenewal(renewal.id).length === 0"
                  >
                    <option value="">-- 未分配 --</option>
                    <option
                      v-for="manager in getManagersForRenewal(renewal.id)"
                      :key="manager.id"
                      :value="manager.id"
                    >
                      {{ manager.full_name }}
                    </option>
                  </select>
                  <p v-if="!getManagersForRenewal(renewal.id) || getManagersForRenewal(renewal.id).length === 0" class="mt-1 text-xs text-gray-500">
                    此更新會無可用管理者
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 按鈕區 -->
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            取消
          </button>
          <button
            type="button"
            @click="handleSubmit"
            :disabled="isSubmitting"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isSubmitting ? '分配中...' : '確認分配' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  urbanRenewals: {
    type: Array,
    required: true
  },
  companyManagers: {
    type: Array, // 統一的管理者列表（所有更新會使用相同列表）
    required: true
  }
})

// Debug: 監控 props 變化
watch(() => props.companyManagers, (newVal) => {
  console.log('Company managers in modal (unified list):', newVal)
}, { immediate: true })

const emit = defineEmits(['close', 'submit'])

const assignments = ref({})
const isSubmitting = ref(false)

// 根據更新會 ID 取得該更新會的管理者列表
// 新架構：所有更新會顯示相同的管理者列表
const getManagersForRenewal = (renewalId) => {
  return props.companyManagers || []
}

// 當 Modal 打開時，初始化分配資料
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    // 重置分配資料，並使用已有的分配作為預設值
    assignments.value = {}
    props.urbanRenewals.forEach(renewal => {
      assignments.value[renewal.id] = renewal.assigned_admin_id || ''
    })
  }
})

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    await emit('submit', { ...assignments.value })
  } finally {
    isSubmitting.value = false
  }
}
</script>
