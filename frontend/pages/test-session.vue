<template>
  <div class="p-8">
    <h1 class="text-2xl mb-4">測試 SessionStorage</h1>

    <div class="space-y-4">
      <UCard>
        <template #header>
          <h2 class="text-lg font-semibold">Auth Store 狀態</h2>
        </template>

        <div class="space-y-2">
          <p>登入狀態: {{ authStore.isLoggedIn ? '已登入' : '未登入' }}</p>
          <p>用戶名: {{ authStore.user?.username || 'N/A' }}</p>
          <p>Token: {{ authStore.token ? '存在' : '不存在' }}</p>
          <p>RefreshToken: {{ authStore.refreshToken ? '存在' : '不存在' }}</p>
        </div>
      </UCard>

      <UCard>
        <template #header>
          <h2 class="text-lg font-semibold">SessionStorage 內容</h2>
        </template>

        <div class="space-y-2">
          <pre class="text-sm bg-gray-100 p-2 rounded overflow-auto">{{ sessionStorageContent }}</pre>
        </div>
      </UCard>

      <UCard>
        <template #header>
          <h2 class="text-lg font-semibold">操作</h2>
        </template>

        <div class="space-x-2">
          <UButton @click="testLogin">測試登入</UButton>
          <UButton @click="refreshSessionStorage" variant="outline">重新載入 SessionStorage</UButton>
          <UButton @click="clearSessionStorage" color="red" variant="outline">清除 SessionStorage</UButton>
        </div>
      </UCard>

      <UCard v-if="loginResult">
        <template #header>
          <h2 class="text-lg font-semibold">登入結果</h2>
        </template>

        <pre class="text-sm bg-gray-100 p-2 rounded overflow-auto">{{ loginResult }}</pre>
      </UCard>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const sessionStorageContent = ref('')
const loginResult = ref('')

const refreshSessionStorage = () => {
  const auth = sessionStorage.getItem('auth')
  if (auth) {
    try {
      sessionStorageContent.value = JSON.stringify(JSON.parse(auth), null, 2)
    } catch (e) {
      sessionStorageContent.value = auth
    }
  } else {
    sessionStorageContent.value = 'SessionStorage 中沒有 auth 資料'
  }
}

const clearSessionStorage = () => {
  sessionStorage.clear()
  refreshSessionStorage()
}

const testLogin = async () => {
  try {
    loginResult.value = '登入中...'
    const result = await authStore.login({
      username: 'admin',
      password: 'password'
    })

    loginResult.value = JSON.stringify(result, null, 2)

    // 等待一下讓 Pinia 持久化插件有時間寫入
    await new Promise(resolve => setTimeout(resolve, 100))

    // 重新載入 sessionStorage 內容
    refreshSessionStorage()
  } catch (error) {
    loginResult.value = `錯誤: ${error.message}`
  }
}

// 頁面載入時檢查
onMounted(() => {
  refreshSessionStorage()
})

// 監聽 auth store 變化
watch(() => authStore.token, () => {
  console.log('Token 變化，重新載入 sessionStorage')
  refreshSessionStorage()
})
</script>