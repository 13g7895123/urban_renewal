/**
 * Authentication plugin for automatic initialization
 * This plugin runs on the client-side to restore authentication state
 */
export default defineNuxtPlugin(async () => {
  const authStore = useAuthStore()

  // Only run on client-side
  if (process.client) {
    console.log('[Auth Plugin] Initializing authentication...')

    try {
      // Pinia 持久化插件會自動從 sessionStorage 恢復狀態
      // initializeAuth() 只是一個向後兼容的空方法
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

  // Make auth store globally available
  return {
    provide: {
      authStore
    }
  }
})