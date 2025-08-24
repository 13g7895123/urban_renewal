export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  
  // Skip middleware on server-side for better SSR handling
  if (process.server) {
    return
  }
  
  // 初始化認證狀態 (只在客戶端運行)
  await authStore.initializeAuth()
  
  // 檢查是否已登入且有有效token
  if (!authStore.isLoggedIn) {
    console.log('[Auth Middleware] User not authenticated, redirecting to login')
    return navigateTo('/auth/login')
  }
  
  // 嘗試驗證token是否仍然有效 (但不要太頻繁)
  try {
    // 只有當用戶數據不存在時才重新獲取
    if (!authStore.user) {
      await authStore.fetchUser()
    }
  } catch (error) {
    console.warn('[Auth Middleware] Token validation failed:', error.message)
    return navigateTo('/auth/login')
  }
})