<template>
  <NuxtLayout name="main">
    <template #title>使用者基本資料</template>

    <div class="p-8">
      <!-- Header with green background -->
      <div class="bg-green-500 text-white p-4 rounded-t-lg flex justify-between items-center">
        <h2 class="text-xl font-semibold">使用者基本資料</h2>
        <UButton
          color="white"
          variant="solid"
          @click="openChangePasswordModal"
        >
          <Icon name="heroicons:lock-closed" class="w-5 h-5 mr-2" />
          變更密碼
        </UButton>
      </div>

      <!-- Form Content -->
      <UCard class="rounded-t-none">
        <div v-if="loading" class="flex justify-center items-center py-12">
          <Icon name="heroicons:arrow-path" class="w-8 h-8 animate-spin text-green-500" />
        </div>

        <form v-else @submit.prevent="saveProfile" class="space-y-6">
          <!-- Account ID -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">帳號</label>
              <UInput
                v-model="form.accountId"
                disabled
                class="w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">姓名</label>
              <UInput
                v-model="form.name"
                placeholder="請輸入姓名"
                class="w-full"
              />
            </div>
          </div>

          <!-- Nickname and Email -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">暱稱</label>
              <UInput
                v-model="form.nickname"
                placeholder="請輸入暱稱"
                class="w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">信箱</label>
              <UInput
                v-model="form.email"
                type="email"
                placeholder="請輸入電子信箱"
                class="w-full"
              />
            </div>
          </div>

          <!-- Phone and Line -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">手機號碼</label>
              <UInput
                v-model="form.phone"
                placeholder="請輸入手機號碼"
                class="w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Line帳號</label>
              <UInput
                v-model="form.lineId"
                placeholder="請輸入Line帳號"
                class="w-full"
              />
            </div>
          </div>

          <!-- Company and Job Title -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">公司名稱</label>
              <UInput
                v-model="form.company"
                disabled
                placeholder="公司名稱"
                class="w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">職稱</label>
              <UInput
                v-model="form.jobTitle"
                placeholder="請輸入職稱"
                class="w-full"
              />
            </div>
          </div>

          <!-- Save Button -->
          <div class="flex justify-end pt-4">
            <UButton
              type="submit"
              color="green"
              size="lg"
              :loading="saving"
            >
              儲存
            </UButton>
          </div>
        </form>
      </UCard>
    </div>

    <!-- Change Password Modal -->
    <UModal v-model="showPasswordModal">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">變更密碼</h3>
            <UButton
              color="gray"
              variant="ghost"
              icon="heroicons:x-mark"
              @click="showPasswordModal = false"
            />
          </div>
        </template>

        <form @submit.prevent="submitChangePassword" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">目前密碼</label>
            <UInput
              v-model="passwordForm.currentPassword"
              type="password"
              placeholder="請輸入目前密碼"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">新密碼</label>
            <UInput
              v-model="passwordForm.newPassword"
              type="password"
              placeholder="請輸入新密碼（至少6個字元）"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">確認新密碼</label>
            <UInput
              v-model="passwordForm.confirmPassword"
              type="password"
              placeholder="請再次輸入新密碼"
              required
            />
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <UButton
              color="gray"
              variant="outline"
              @click="showPasswordModal = false"
            >
              取消
            </UButton>
            <UButton
              type="submit"
              color="green"
              :loading="changingPassword"
            >
              確認變更
            </UButton>
          </div>
        </form>
      </UCard>
    </UModal>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: false,
  middleware: 'auth'
})

const authStore = useAuthStore()
const { get, put, post } = useApi()
const { $swal } = useNuxtApp()
const { showSuccess, showError, showWarning } = useSweetAlert()

// 表單資料
const loading = ref(true)
const saving = ref(false)
const form = ref({
  accountId: '',
  name: '',
  nickname: '',
  email: '',
  phone: '',
  lineId: '',
  company: '',
  jobTitle: ''
})

// 密碼變更相關
const showPasswordModal = ref(false)
const changingPassword = ref(false)
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// 載入使用者資料
const loadUserProfile = async () => {
  try {
    loading.value = true
    const response = await get('/users/profile')

    if (response.success && response.data) {
      const userData = response.data.data || response.data

      // 填充表單資料
      form.value = {
        accountId: userData.username || '',
        name: userData.full_name || '',
        nickname: userData.nickname || '',
        email: userData.email || '',
        phone: userData.phone || '',
        lineId: userData.line_account || '',
        company: userData.urban_renewal_name || userData.company_name || '',
        jobTitle: userData.position || ''
      }
    } else {
      throw new Error(response.error?.message || '載入使用者資料失敗')
    }
  } catch (error) {
    console.error('Load profile error:', error)
    await showError('載入失敗', error.message || '無法載入使用者資料')
  } finally {
    loading.value = false
  }
}

// 儲存個人資料
const saveProfile = async () => {
  try {
    saving.value = true

    const updateData = {
      full_name: form.value.name,
      nickname: form.value.nickname,
      email: form.value.email,
      phone: form.value.phone,
      line_account: form.value.lineId,
      position: form.value.jobTitle
    }

    const userId = authStore.user.id
    const response = await put(`/users/${userId}`, updateData)

    if (response.success) {
      await showSuccess('儲存成功', '個人資料已更新')

      // 重新載入資料
      await loadUserProfile()
    } else {
      throw new Error(response.error?.message || '儲存失敗')
    }
  } catch (error) {
    console.error('Save profile error:', error)
    await showError('儲存失敗', error.message || '無法儲存個人資料')
  } finally {
    saving.value = false
  }
}

// 開啟變更密碼彈窗
const openChangePasswordModal = () => {
  passwordForm.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
  showPasswordModal.value = true
}

// 提交變更密碼
const submitChangePassword = async () => {
  try {
    // 驗證新密碼
    if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
      await showWarning('密碼不符', '新密碼與確認密碼不相符')
      return
    }

    if (passwordForm.value.newPassword.length < 6) {
      await showWarning('密碼太短', '新密碼至少需要6個字元')
      return
    }

    changingPassword.value = true

    const response = await post('/users/change-password', {
      current_password: passwordForm.value.currentPassword,
      new_password: passwordForm.value.newPassword,
      confirm_password: passwordForm.value.confirmPassword
    })

    if (response.success) {
      showPasswordModal.value = false
      await showSuccess('變更成功', '密碼已成功變更')

      // 清空表單
      passwordForm.value = {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      }
    } else {
      throw new Error(response.error?.message || '密碼變更失敗')
    }
  } catch (error) {
    console.error('Change password error:', error)
    await showError('變更失敗', error.message || '無法變更密碼')
  } finally {
    changingPassword.value = false
  }
}

// 頁面載入時取得使用者資料
onMounted(() => {
  loadUserProfile()
})
</script>
