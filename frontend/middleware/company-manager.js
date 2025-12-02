export default defineNuxtRouteMiddleware(async (to) => {
  // 只在客戶端執行
  if (process.server) {
    return
  }

  const { useAuthStore } = await import('~/stores/auth')
  const authStore = useAuthStore()

  // 在 SPA 模式下，頁面重整時可能需要等待 Pinia 持久化插件恢復狀態
  // 如果 store 沒有 token，嘗試從 sessionStorage 直接讀取
  if (!authStore.token && process.client) {
    const persistedAuth = sessionStorage.getItem('auth')
    if (persistedAuth) {
      try {
        const authData = JSON.parse(persistedAuth)
        console.log('[Company Manager Middleware] Restoring auth from sessionStorage')
        
        // 手動恢復狀態
        if (authData.token) {
          authStore.token = authData.token
        }
        if (authData.user) {
          authStore.user = authData.user
        }
        if (authData.refreshToken) {
          authStore.refreshToken = authData.refreshToken
        }
        if (authData.tokenExpiresAt) {
          authStore.tokenExpiresAt = authData.tokenExpiresAt
        }
      } catch (e) {
        console.error('[Company Manager Middleware] Failed to parse auth from sessionStorage:', e)
      }
    }
  }

  // 檢查是否已登入
  if (!authStore.isLoggedIn) {
    console.log('[Company Manager Middleware] User not authenticated, redirecting to login')
    return navigateTo('/login')
  }

  // 嘗試驗證token是否仍然有效
  try {
    // 只有當用戶數據不存在時才重新獲取
    if (!authStore.user) {
      await authStore.fetchUser()
    }
  } catch (error) {
    console.warn('[Company Manager Middleware] Token validation failed:', error.message)
    return navigateTo('/login')
  }

  // 檢查是否有企業管理權限（系統管理員或企業管理者）
  const hasAccess = authStore.isAdmin || authStore.isCompanyManager

  if (!hasAccess) {
    console.log('[Company Manager Middleware] User does not have company management privileges')
    return navigateTo('/unauthorized')
  }
})
