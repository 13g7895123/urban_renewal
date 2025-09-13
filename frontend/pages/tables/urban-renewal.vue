<template>
  <NuxtLayout name="main">
    <template #title>更新會管理</template>

    <div class="p-8">
      <!-- Header with green background and icon -->
      <div class="bg-green-500 text-white p-6 rounded-lg mb-6">
        <div class="flex items-center">
          <div class="bg-white/20 p-3 rounded-lg mr-4">
            <Icon name="heroicons:document-text" class="w-8 h-8 text-white" />
          </div>
          <h2 class="text-2xl font-semibold">更新會</h2>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 mb-6">
        <button
          @click="allocateRenewal"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:users" class="w-5 h-5 mr-2" />
          分配更新會
        </button>
        <button
          @click="createRenewal"
          class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
          <Icon name="heroicons:plus" class="w-5 h-5 mr-2" />
          新建更新會
        </button>
      </div>

      <!-- Create Urban Renewal Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <!-- Header -->
            <div class="border-b border-gray-200 pb-4 mb-6">
              <h3 class="text-lg font-semibold text-gray-900">新建更新會</h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="onSubmit">
              <div class="space-y-6">
                <!-- 更新會名稱 -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">更新會名稱 <span class="text-red-500">*</span></label>
                  <input
                    id="name"
                    v-model="formData.name"
                    type="text"
                    placeholder="請輸入更新會名稱"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 土地面積 -->
                <div>
                  <label for="area" class="block text-sm font-medium text-gray-700 mb-2">土地面積(平方公尺) <span class="text-red-500">*</span></label>
                  <input
                    id="area"
                    v-model="formData.area"
                    type="number"
                    placeholder="請輸入土地面積"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 所有權人數 -->
                <div>
                  <label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2">所有權人數 <span class="text-red-500">*</span></label>
                  <input
                    id="memberCount"
                    v-model="formData.memberCount"
                    type="number"
                    placeholder="請輸入所有權人數"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 理事長姓名 -->
                <div>
                  <label for="chairmanName" class="block text-sm font-medium text-gray-700 mb-2">理事長姓名 <span class="text-red-500">*</span></label>
                  <input
                    id="chairmanName"
                    v-model="formData.chairmanName"
                    type="text"
                    placeholder="請輸入理事長姓名"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>

                <!-- 理事長電話 -->
                <div>
                  <label for="chairmanPhone" class="block text-sm font-medium text-gray-700 mb-2">理事長電話 <span class="text-red-500">*</span></label>
                  <input
                    id="chairmanPhone"
                    v-model="formData.chairmanPhone"
                    type="tel"
                    placeholder="請輸入理事長電話"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    required
                  />
                </div>
              </div>

              <!-- Footer -->
              <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                >
                  取消
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                >
                  確認新建
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Urban Renewal Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="p-4 text-left text-sm font-medium text-gray-700">更新會名稱</th>
                <th class="p-4 text-left text-sm font-medium text-gray-700">土地面積 (平方公尺)</th>
                <th class="p-4 text-left text-sm font-medium text-gray-700">所有權人數</th>
                <th class="p-4 text-left text-sm font-medium text-gray-700">理事長姓名</th>
                <th class="p-4 text-left text-sm font-medium text-gray-700">理事長電話</th>
                <th class="p-4 text-center text-sm font-medium text-gray-700">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="renewals.length === 0">
                <td colspan="6" class="p-8 text-center text-gray-500">
                  暫無資料，請點擊「新建更新會」新增資料
                </td>
              </tr>
              <tr v-for="(renewal, index) in renewals" :key="index" class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                <td class="p-4 text-sm text-gray-900">{{ renewal.name }}</td>
                <td class="p-4 text-sm text-gray-900 text-center">{{ renewal.area }}</td>
                <td class="p-4 text-sm text-gray-900 text-center">{{ renewal.memberCount }}</td>
                <td class="p-4 text-sm text-gray-900">{{ renewal.chairmanName }}</td>
                <td class="p-4 text-sm text-gray-900">{{ renewal.chairmanPhone }}</td>
                <td class="p-4 text-center">
                  <div class="flex justify-center gap-2 flex-wrap">
                    <button
                      @click="viewBasicInfo(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition-colors duration-200"
                    >
                      基本資料
                    </button>
                    <button
                      @click="viewMembers(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                    >
                      查詢會員
                    </button>
                    <button
                      @click="viewJointInfo(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors duration-200"
                    >
                      共舉資訊
                    </button>
                    <button
                      @click="deleteRenewal(renewal)"
                      class="px-2 py-1 text-xs font-medium text-white bg-red-500 hover:bg-red-600 rounded transition-colors duration-200"
                    >
                      刪除
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center p-4 border-t border-gray-200">
          <div class="text-sm text-gray-500 flex items-center">
            每頁顯示：
            <select
              v-model="pageSize"
              class="ml-2 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
            </select>
          </div>
          <div class="text-sm text-gray-500">
            {{ renewals.length > 0 ? `1-${renewals.length} 共 ${renewals.length}` : '0-0 共 0' }}
          </div>
          <div class="flex gap-1">
            <button
              disabled
              class="p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"
            >
              <Icon name="heroicons:chevron-left" class="w-4 h-4" />
            </button>
            <button class="px-3 py-2 text-sm text-white bg-blue-500 rounded font-medium">1</button>
            <button
              disabled
              class="p-2 text-gray-400 bg-gray-100 rounded cursor-not-allowed"
            >
              <Icon name="heroicons:chevron-right" class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'

definePageMeta({
  layout: false
})

const pageSize = ref(10)
const showCreateModal = ref(false)

// Form data
const formData = reactive({
  name: '',
  area: '',
  memberCount: '',
  chairmanName: '',
  chairmanPhone: ''
})

const renewals = ref([])

const allocateRenewal = () => {
  console.log('Allocating renewal meeting')
  // TODO: Implement allocate functionality
}

const createRenewal = () => {
  showCreateModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  resetForm()
}

const resetForm = () => {
  formData.name = ''
  formData.area = ''
  formData.memberCount = ''
  formData.chairmanName = ''
  formData.chairmanPhone = ''
}

const onSubmit = () => {
  // Basic validation
  if (!formData.name || !formData.area || !formData.memberCount || !formData.chairmanName || !formData.chairmanPhone) {
    alert('請填寫所有必填項目')
    return
  }

  const newRenewal = {
    id: renewals.value.length + 1,
    name: formData.name,
    area: formData.area,
    memberCount: parseInt(formData.memberCount),
    chairmanName: formData.chairmanName,
    chairmanPhone: formData.chairmanPhone
  }

  renewals.value.push(newRenewal)
  closeModal()

  // Show success message or handle as needed
  console.log('New urban renewal association created:', newRenewal)
}

const viewBasicInfo = (renewal) => {
  console.log('Viewing basic info for:', renewal)
  // TODO: Implement view basic info functionality
}

const viewMembers = (renewal) => {
  console.log('Viewing members for:', renewal)
  // TODO: Implement view members functionality
}

const viewJointInfo = (renewal) => {
  console.log('Viewing joint info for:', renewal)
  // TODO: Implement view joint info functionality
}

const deleteRenewal = (renewal) => {
  console.log('Deleting renewal:', renewal)
  // TODO: Implement delete functionality
}
</script>