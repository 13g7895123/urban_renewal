/**
 * Role-based access control middleware
 * Usage in pages:
 *
 * definePageMeta({
 *   middleware: ['auth', 'role'],
 *   role: 'admin' // or ['admin', 'chairman']
 * })
 */
export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()

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
