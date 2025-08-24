export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  
  // Skip middleware on server-side for better SSR handling
  if (process.server) {
    return
  }
  
  // 初始化認證狀態 (只在客戶端運行)
  await authStore.initializeAuth()
  
  // 如果已經登入且token有效，重定向到首頁
  if (authStore.isLoggedIn) {
    console.log('[Guest Middleware] User is authenticated, redirecting to home')
    return navigateTo('/')
  }
})