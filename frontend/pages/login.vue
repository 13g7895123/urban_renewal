<template>
  <AuthPageLayout max-width-class="max-w-lg"
  card-class="">
    <template #title>都更計票系統首頁</template>

    <template #header>
      <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-800">登入</h2>
      </div>
    </template>

    <form @submit.prevent="handleLogin" class="space-y-6">
      <div class="input-group">
        <Icon name="heroicons:user" class="input-icon" />
        <UInput v-model="username" placeholder="帳號" variant="none" class="custom-input"
          :ui="{ base: 'w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500' }" />
      </div>

      <div class="input-group">
        <Icon name="heroicons:lock-closed" class="input-icon" />
        <UInput v-model="password" placeholder="密碼" :type="showPassword ? 'text' : 'password'" variant="none"
          class="custom-input"
          :ui="{ base: 'w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500' }">
          <template #trailing>
            <UButton variant="ghost" size="xs" @click="showPassword = !showPassword" class="password-toggle">
              <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-4 h-4 text-gray-500" />
            </UButton>
          </template>
        </UInput>
      </div>

      <UButton type="submit" block size="lg" class="login-btn mt-8" :loading="loading">
        登入
      </UButton>
    </form>
  </AuthPageLayout>
</template>

<script setup>
import Swal from 'sweetalert2'

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)

const handleLogin = async () => {
  if (!username.value || !password.value) {
    return
  }

  loading.value = true
  try {
    const authStore = useAuthStore()

    // Use authStore login method
    await authStore.login({
      username: username.value,
      password: password.value
    })

    // 確保認證狀態已完全初始化
    await authStore.initializeAuth()

    // Show success message
    const toast = useToast()
    toast.add({
      title: '登入成功',
      description: `歡迎回來，${authStore.user?.full_name || authStore.user?.username || '用戶'}！`,
      color: 'green'
    })

    // 使用 nextTick 確保狀態更新完成後再導航
    await nextTick()

    // Redirect based on user role
    const userRole = authStore.user?.role
    if (userRole === 'admin') {
      // Admin goes to urban renewal management
      await navigateTo('/tables/urban-renewal')
    } else if (userRole === 'chairman' || userRole === 'member') {
      // Chairman and members go to meeting list
      await navigateTo('/tables/meeting')
    } else {
      // Default to home page
      await navigateTo('/')
    }
  } catch (error) {
    console.error('Login error:', error)

    // 使用 SweetAlert2 顯示錯誤訊息
    await Swal.fire({
      icon: 'error',
      title: '登入失敗',
      text: error.message || '帳號或密碼錯誤',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  } finally {
    loading.value = false
  }
}

// Page metadata - guest only (redirect if already authenticated)
definePageMeta({
  middleware: 'guest',
  layout: false
})
</script>

<style scoped>
.login-btn {
  border-radius: 8px;
  font-weight: 600;
  background: linear-gradient(to right, #2FA633, #72BB29);
  border: none;
  color: white;
  padding: 12px 24px;
  font-size: 16px;
}

.login-btn:hover {
  background: linear-gradient(to right, #267a2b, #5fa022);
}
</style>