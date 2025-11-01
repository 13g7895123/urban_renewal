import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  // SPA 模式下都在客戶端執行，Pinia 持久化插件會自動從 sessionStorage 恢復狀態
  const authStore = useAuthStore()

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