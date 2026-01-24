export default defineNuxtRouteMiddleware(async (to) => {
  // 只在客戶端執行
  if (process.server) {
    return
  }

  const { useAuthStore } = await import('~/stores/auth')
  const authStore = useAuthStore()

  // 檢查是否已登入（使用 HttpOnly Cookie）
  if (!authStore.isLoggedIn) {
    console.log('[Company Manager Middleware] User not authenticated, redirecting to login')
    return navigateTo('/login')
  }

  // 驗證 token 是否仍然有效並獲取用戶資訊
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

