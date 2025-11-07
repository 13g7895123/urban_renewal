<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-semibold text-gray-900">基本資料</h2>
      <div class="flex gap-2">
        <button
          type="button"
          @click="$emit('fillTestData')"
          class="px-3 py-1 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors duration-200"
        >
          <Icon name="heroicons:beaker" class="w-4 h-4 mr-1 inline" />
          填入測試資料
        </button>
        <button
          v-if="showPreviewButton"
          type="button"
          @click="$emit('previewSubmitData')"
          class="px-3 py-1 text-sm font-medium text-white bg-purple-500 hover:bg-purple-600 rounded-md transition-colors duration-200"
        >
          <Icon name="heroicons:eye" class="w-4 h-4 mr-1 inline" />
          預覽提交資料
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- 所屬更新會 -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          所屬更新會 <span class="text-red-500">*</span>
        </label>
        <input
          :value="urbanRenewalName"
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
          {{ ownerCodeLabel }}
        </label>
        <input
          :value="formData.owner_code"
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
</template>

<script setup>
/**
 * Props
 */
const props = defineProps({
  formData: {
    type: Object,
    required: true
  },
  urbanRenewalName: {
    type: String,
    required: true
  },
  showPreviewButton: {
    type: Boolean,
    default: false
  },
  ownerCodeLabel: {
    type: String,
    default: '所有權人編號(自動產生)'
  }
})

/**
 * Emits
 */
const emit = defineEmits(['fillTestData', 'previewSubmitData'])
</script>
