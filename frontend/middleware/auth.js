import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  // 只在客戶端執行
  if (process.server) {
    return
  }

  const authStore = useAuthStore()

  console.log('[Auth Middleware] Checking authentication:', {
    isLoggedIn: authStore.isLoggedIn,
    hasUser: !!authStore.user
  })

  // 如果沒有用戶資料，嘗試從 cookie 驗證
  if (!authStore.user) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.warn('[Auth Middleware] Failed to fetch user:', error.message)
    }
  }

  // 檢查是否已登入
  if (!authStore.isLoggedIn) {
    console.log('[Auth Middleware] User not authenticated, redirecting to login')
    return navigateTo(`/login?redirect=${to.fullPath}`)
  }
})