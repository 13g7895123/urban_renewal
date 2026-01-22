/**
 * Authentication plugin for automatic initialization
 * 使用 httpOnly Cookie，在頁面載入時驗證 cookie 是否有效
 */
export default defineNuxtPlugin(async (nuxtApp) => {
  const authStore = useAuthStore()
  const route = useRoute()

  if (process.client) {
    // 跳過測試頁面的 auth 初始化
    if (route.path === '/test-all-features' || route.path.startsWith('/test-')) {
      console.log('[Auth Plugin] Skipping auth initialization for test page')
      return {
        provide: {
          authStore
        }
      }
    }

    console.log('[Auth Plugin] Initializing authentication...')

    try {
      // 嘗試從 cookie 驗證並取得用戶資料
      await authStore.initializeAuth()

      if (authStore.isLoggedIn) {
        console.log('[Auth Plugin] User authenticated:', authStore.user?.name || authStore.user?.username)
      } else {
        console.log('[Auth Plugin] No valid authentication found')
      }
    } catch (error) {
      console.error('[Auth Plugin] Failed to initialize auth:', error)
    }
  }

  return {
    provide: {
      authStore
    }
  }
})