// 手動設置 Pinia 持久化插件
export default defineNuxtPlugin((nuxtApp) => {
  // 監聽 auth store 的變化並手動保存到 sessionStorage
  const authStore = useAuthStore()

  // 初始化時從 sessionStorage 恢復狀態
  if (process.client) {
    const stored = sessionStorage.getItem('auth')
    if (stored) {
      try {
        const data = JSON.parse(stored)
        console.log('[Pinia Persist] 從 sessionStorage 恢復狀態:', data)

        // 恢復狀態
        if (data.user) authStore.user = data.user
        if (data.token) authStore.token = data.token
        if (data.refreshToken) authStore.refreshToken = data.refreshToken
        if (data.tokenExpiresAt) authStore.tokenExpiresAt = data.tokenExpiresAt
      } catch (error) {
        console.error('[Pinia Persist] 解析 sessionStorage 失敗:', error)
      }
    }

    // 監聽變化並保存
    watch(
      () => ({
        user: authStore.user,
        token: authStore.token,
        refreshToken: authStore.refreshToken,
        tokenExpiresAt: authStore.tokenExpiresAt
      }),
      (newValue) => {
        console.log('[Pinia Persist] 保存狀態到 sessionStorage:', newValue)
        sessionStorage.setItem('auth', JSON.stringify(newValue))
      },
      { deep: true, immediate: false }
    )
  }
})