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
      // Initialize authentication state from localStorage
      await authStore.initializeAuth()
      
      if (authStore.isLoggedIn) {
        console.log('[Auth Plugin] User authenticated:', authStore.user?.name)
      } else {
        console.log('[Auth Plugin] No valid authentication found')
      }
    } catch (error) {
      console.error('[Auth Plugin] Failed to initialize auth:', error)
      
      // Clear any invalid auth data
      if (process.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    }
  }

  // Make auth store globally available
  return {
    provide: {
      authStore
    }
  }
})