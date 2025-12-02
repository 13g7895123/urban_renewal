import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  // 只在客戶端執行
  if (process.server) {
    return
  }

  const authStore = useAuthStore()

  // 在 SPA 模式下，頁面重整時可能需要等待 Pinia 持久化插件恢復狀態
  // 如果 store 沒有 token，嘗試從 localStorage 或 sessionStorage 直接讀取
  if (!authStore.token && process.client) {
    let persistedAuth = localStorage.getItem('auth')
    
    // 如果 localStorage 沒有，嘗試 sessionStorage (舊版兼容)
    if (!persistedAuth) {
      persistedAuth = sessionStorage.getItem('auth')
    }

    if (persistedAuth) {
      try {
        const authData = JSON.parse(persistedAuth)
        console.log('[Auth Middleware] Restoring auth from storage:', {
          hasToken: !!authData.token,
          hasUser: !!authData.user
        })
        
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
        console.error('[Auth Middleware] Failed to parse auth from sessionStorage:', e)
      }
    }
  }

  console.log('[Auth Middleware] Checking authentication:', {
    isLoggedIn: authStore.isLoggedIn,
    hasUser: !!authStore.user,
    hasToken: !!authStore.token,
    user: authStore.user
  })

  // 檢查是否已登入且有有效token
  if (!authStore.isLoggedIn) {
    console.log('[Auth Middleware] User not authenticated, redirecting to login')
    return navigateTo('/login')
  }

  // 嘗試驗證token是否仍然有效
  try {
    // 只有當用戶數據不存在時才重新獲取
    if (!authStore.user) {
      await authStore.fetchUser()
    }
  } catch (error) {
    console.warn('[Auth Middleware] Token validation failed:', error.message)
    return navigateTo('/login')
  }
})