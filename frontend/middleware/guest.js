import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  // SPA 模式下都在客戶端執行，Pinia 持久化插件會自動從 sessionStorage 恢復狀態
  const authStore = useAuthStore()

  // 如果已經登入且token有效，重定向到首頁
  if (authStore.isLoggedIn) {
    console.log('[Guest Middleware] User is authenticated, redirecting to home')
    return navigateTo('/')
  }
})