/**
 * Role-based access control middleware
 * Usage in pages:
 *
 * definePageMeta({
 *   middleware: ['auth', 'role'],
 *   role: 'admin' // or ['admin', 'chairman']
 * })
 */
export default defineNuxtRouteMiddleware(async (to) => {
  // 只在客戶端執行
  if (process.server) {
    return
  }

  const { useAuthStore } = await import('~/stores/auth')
  const authStore = useAuthStore()

  // 在 SPA 模式下，頁面重整時可能需要等待 Pinia 持久化插件恢復狀態
  if (!authStore.token && process.client) {
    const persistedAuth = sessionStorage.getItem('auth')
    if (persistedAuth) {
      try {
        const authData = JSON.parse(persistedAuth)
        if (authData.token) authStore.token = authData.token
        if (authData.user) authStore.user = authData.user
        if (authData.refreshToken) authStore.refreshToken = authData.refreshToken
        if (authData.tokenExpiresAt) authStore.tokenExpiresAt = authData.tokenExpiresAt
      } catch (e) {
        console.error('[Role Middleware] Failed to parse auth from sessionStorage:', e)
      }
    }
  }

  // Get required role from page meta
  const requiredRole = to.meta.role

  // If no role requirement, allow access
  if (!requiredRole) {
    return
  }

  // Check if user is authenticated
  if (!authStore.user) {
    return navigateTo('/login')
  }

  // Check if user has required role
  const userRole = authStore.user.role

  // Handle array of roles
  if (Array.isArray(requiredRole)) {
    if (!requiredRole.includes(userRole)) {
      // User doesn't have required role
      return navigateTo('/unauthorized')
    }
  } else {
    // Handle single role
    if (userRole !== requiredRole) {
      // User doesn't have required role
      return navigateTo('/unauthorized')
    }
  }

  // User has required role, allow access
})
