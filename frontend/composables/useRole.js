/**
 * Role-based access control composable
 * Provides helpers for checking user roles and permissions
 */
export const useRole = () => {
  const authStore = useAuthStore()

  /**
   * Check if current user has a specific role
   * @param {string|string[]} roles - Role(s) to check
   * @returns {boolean}
   */
  const hasRole = (roles) => {
    if (!authStore.user?.role) return false

    if (Array.isArray(roles)) {
      return roles.includes(authStore.user.role)
    }

    return authStore.user.role === roles
  }

  /**
   * Check if user is admin
   * @returns {boolean}
   */
  const isAdmin = computed(() => authStore.isAdmin)

  /**
   * Check if user is chairman
   * @returns {boolean}
   */
  const isChairman = computed(() => hasRole('chairman'))

  /**
   * Check if user is member
   * @returns {boolean}
   */
  const isMember = computed(() => hasRole('member'))

  /**
   * Check if user is observer
   * @returns {boolean}
   */
  const isObserver = computed(() => hasRole('observer'))

  /**
   * Check if user is company manager
   * @returns {boolean}
   */
  const isCompanyManager = computed(() => authStore.isCompanyManager)

  /**
   * Check if user can manage urban renewals
   * Admin and chairman can manage
   * @returns {boolean}
   */
  const canManageUrbanRenewal = computed(() => {
    return hasRole(['admin', 'chairman'])
  })

  /**
   * Check if user can manage meetings
   * Admin and chairman can manage
   * @returns {boolean}
   */
  const canManageMeetings = computed(() => {
    return hasRole(['admin', 'chairman'])
  })

  /**
   * Check if user can vote
   * Chairman and members can vote
   * @returns {boolean}
   */
  const canVote = computed(() => {
    return hasRole(['chairman', 'member'])
  })

  /**
   * Check if user can view voting results
   * All authenticated users can view
   * @returns {boolean}
   */
  const canViewResults = computed(() => {
    return !!authStore.user
  })

  /**
   * Check if user can manage users
   * Only admin can manage users
   * @returns {boolean}
   */
  const canManageUsers = computed(() => {
    return hasRole('admin')
  })

  /**
   * Check if user can manage system settings
   * Only admin can manage system settings
   * @returns {boolean}
   */
  const canManageSettings = computed(() => {
    return hasRole('admin')
  })

  /**
   * Check if user can access company profile
   * Admin and company managers can access
   * @returns {boolean}
   */
  const canAccessCompanyProfile = computed(() => {
    return isAdmin.value || isCompanyManager.value
  })

  /**
   * Check if user can manage company users
   * Admin and company managers can manage company users
   * @returns {boolean}
   */
  const canManageCompanyUsers = computed(() => {
    return isAdmin.value || isCompanyManager.value
  })

  /**
   * Get user role display name
   * @returns {string}
   */
  const getRoleDisplayName = () => {
    const roleMap = {
      admin: '管理員',
      chairman: '理事長',
      member: '會員',
      observer: '觀察員'
    }
    return roleMap[authStore.user?.role] || '未知角色'
  }

  /**
   * Check if user has access to a specific urban renewal
   * @param {number|string} urbanRenewalId - Urban renewal ID to check
   * @returns {boolean}
   */
  const canAccessUrbanRenewal = (urbanRenewalId) => {
    // Admin can access all
    if (isAdmin.value) return true

    // User must belong to the urban renewal or have no restriction
    if (authStore.user?.urban_renewal_id === null) return true

    return String(authStore.user?.urban_renewal_id) === String(urbanRenewalId)
  }

  return {
    // Role checks
    hasRole,
    isAdmin,
    isChairman,
    isMember,
    isObserver,
    isCompanyManager,

    // Permission checks
    canManageUrbanRenewal,
    canManageMeetings,
    canVote,
    canViewResults,
    canManageUsers,
    canManageSettings,
    canAccessCompanyProfile,
    canManageCompanyUsers,
    canAccessUrbanRenewal,

    // Utilities
    getRoleDisplayName
  }
}
