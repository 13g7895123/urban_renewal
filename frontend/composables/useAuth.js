/**
 * Authentication API composable - Updated to match CodeIgniter backend
 */
export const useAuth = () => {
  const { post, get, put } = useApi()

  /**
   * Login user with username/email and password
   */
  const login = async (credentials) => {
    const loginData = {
      username: credentials.username || credentials.email,
      password: credentials.password
    }
    return await post('/auth/login', loginData)
  }

  /**
   * Logout current user
   */
  const logout = async () => {
    return await post('/auth/logout')
  }

  /**
   * Get current authenticated user data
   */
  const getCurrentUser = async () => {
    return await get('/auth/me')
  }

  /**
   * Refresh authentication token
   */
  const refreshToken = async () => {
    return await post('/auth/refresh')
  }

  /**
   * Request password reset
   */
  const requestPasswordReset = async (email) => {
    return await post('/auth/forgot-password', { email })
  }

  /**
   * Reset password with token
   */
  const resetPassword = async (resetData) => {
    return await post('/auth/reset-password', resetData)
  }

  /**
   * Change password (through users endpoint)
   */
  const changePassword = async (passwordData) => {
    return await post('/users/change-password', passwordData)
  }

  /**
   * Update user profile (through users endpoint)
   */
  const updateProfile = async (profileData) => {
    return await get('/users/profile')
  }

  /**
   * Check if user is authenticated by trying to fetch user data
   */
  const checkAuth = async () => {
    try {
      const response = await getCurrentUser()
      return response.success && response.data
    } catch (error) {
      return false
    }
  }

  /**
   * Validate authentication token
   */
  const validateToken = async () => {
    return await checkAuth()
  }

  return {
    // Core authentication
    login,
    register,
    logout,
    logoutAll,
    getCurrentUser,
    checkAuth,
    validateToken,
    
    // Profile management
    updateProfile,
    changePassword,
    
    // Token management
    refreshToken,
    
    // Password reset (optional - depends on backend implementation)
    requestPasswordReset,
    resetPassword,
    
    // Email verification (optional - depends on backend implementation)
    verifyEmail
  }
}