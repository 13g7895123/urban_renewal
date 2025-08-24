/**
 * Authentication API composable - Updated to match Laravel backend
 */
export const useAuth = () => {
  const { post, get, put } = useApi()

  /**
   * Login user with email/username and password
   */
  const login = async (credentials) => {
    const loginData = {
      login: credentials.username || credentials.email, // Backend expects 'login' field
      password: credentials.password
    }
    return await post('/auth/login', loginData)
  }

  /**
   * Register new user
   */
  const register = async (userData) => {
    return await post('/auth/register', userData)
  }

  /**
   * Logout current user
   */
  const logout = async () => {
    return await post('/auth/logout')
  }

  /**
   * Logout from all devices
   */
  const logoutAll = async () => {
    return await post('/auth/logout-all')
  }

  /**
   * Get current authenticated user data
   */
  const getCurrentUser = async () => {
    return await get('/auth/me')
  }

  /**
   * Update user profile (using profile endpoint)
   */
  const updateProfile = async (profileData) => {
    return await put('/profile', profileData)
  }

  /**
   * Change password
   */
  const changePassword = async (passwordData) => {
    return await put('/auth/change-password', passwordData)
  }

  /**
   * Refresh authentication token
   */
  const refreshToken = async () => {
    return await post('/auth/refresh')
  }

  /**
   * Request password reset (if implemented in backend)
   */
  const requestPasswordReset = async (email) => {
    return await post('/auth/forgot-password', { email })
  }

  /**
   * Reset password with token (if implemented in backend)
   */
  const resetPassword = async (resetData) => {
    return await post('/auth/reset-password', resetData)
  }

  /**
   * Verify email address (if implemented in backend)
   */
  const verifyEmail = async (token) => {
    return await post('/auth/verify-email', { token })
  }

  /**
   * Check if user is authenticated by trying to fetch user data
   */
  const checkAuth = async () => {
    try {
      const response = await getCurrentUser()
      return response.success && response.data?.user
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