<template>
  <NuxtLayout name="auth">
    <template #title>都更計票系統首頁</template>
    
    <div class="flex items-center justify-center py-12">
      <UCard class="login-card w-full max-w-md">
        <template #header>
          <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800">登入</h2>
          </div>
        </template>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <div class="input-group">
            <Icon name="heroicons:user" class="input-icon" />
            <UInput
              v-model="username"
              placeholder="帳號"
              variant="none"
              class="custom-input"
              :ui="{ base: 'w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500' }"
            />
          </div>
          
          <div class="input-group">
            <Icon name="heroicons:lock-closed" class="input-icon" />
            <UInput
              v-model="password"
              placeholder="密碼"
              :type="showPassword ? 'text' : 'password'"
              variant="none"
              class="custom-input"
              :ui="{ base: 'w-full border-0 border-b border-gray-300 rounded-none bg-transparent focus:border-primary-500' }"
            >
              <template #trailing>
                <UButton
                  variant="ghost"
                  size="xs"
                  @click="showPassword = !showPassword"
                  class="password-toggle"
                >
                  <Icon 
                    :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" 
                    class="w-4 h-4 text-gray-500"
                  />
                </UButton>
              </template>
            </UInput>
          </div>
          
          <UButton
            type="submit"
            block
            size="lg"
            class="login-btn mt-8"
            :loading="loading"
          >
            登入
          </UButton>
        </form>
      </UCard>
    </div>
  </NuxtLayout>
</template>

<script setup>
const username = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)

const rules = {
  required: value => !!value || '此欄位為必填'
}

const handleLogin = async () => {
  if (!username.value || !password.value) {
    return
  }
  
  loading.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    // Redirect to urban renewal management page (no authentication required for now)
    await navigateTo('/tables/urban-renewal')
  } catch (error) {
    console.error('Login error:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  padding: 2rem;
}

.input-group {
  display: flex;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}

.input-icon {
  width: 20px;
  height: 20px;
  margin-right: 16px;
  color: #6b7280;
  flex-shrink: 0;
}

.custom-input {
  flex: 1;
  background: transparent;
  border: none;
}

.password-toggle {
  padding: 0;
  background: transparent;
  border: none;
}

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


/* Override Nuxt UI input styles for underline effect */
.input-group .custom-input :deep(input) {
  background: transparent !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
  padding: 8px 0 !important;
  color: #000000 !important;
}

.input-group .custom-input :deep(input::placeholder) {
  color: #6b7280 !important;
}

.input-group .custom-input :deep(.ui-input-wrapper) {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}
</style>