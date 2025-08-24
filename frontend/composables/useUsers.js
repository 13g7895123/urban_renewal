/**
 * User Management API composable
 */
export const useUsers = () => {
  const { get, post, put, patch, delete: del } = useApi()

  /**
   * Get all users with pagination and filtering
   */
  const getUsers = async (params = {}) => {
    const queryParams = new URLSearchParams()
    
    if (params.search) queryParams.append('search', params.search)
    if (params.role) queryParams.append('role', params.role)
    if (params.status) queryParams.append('status', params.status)
    if (params.page) queryParams.append('page', params.page)
    if (params.per_page) queryParams.append('per_page', params.per_page)
    if (params.sort) queryParams.append('sort', params.sort)
    if (params.order) queryParams.append('order', params.order)

    const queryString = queryParams.toString()
    const endpoint = queryString ? `/users?${queryString}` : '/users'
    
    return await get(endpoint)
  }

  /**
   * Get specific user by ID
   */
  const getUser = async (id) => {
    return await get(`/users/${id}`)
  }

  /**
   * Create new user
   */
  const createUser = async (userData) => {
    return await post('/users', userData)
  }

  /**
   * Update user
   */
  const updateUser = async (id, userData) => {
    return await put(`/users/${id}`, userData)
  }

  /**
   * Delete user (soft delete)
   */
  const deleteUser = async (id) => {
    return await del(`/users/${id}`)
  }

  /**
   * Toggle user status (active/inactive/suspended)
   */
  const toggleUserStatus = async (id, status) => {
    return await patch(`/users/${id}/status`, { status })
  }

  /**
   * Change user password
   */
  const changeUserPassword = async (id, passwordData) => {
    return await patch(`/users/${id}/password`, passwordData)
  }

  /**
   * Restore soft deleted user
   */
  const restoreUser = async (id) => {
    return await patch(`/users/${id}/restore`)
  }

  /**
   * Permanently delete user
   */
  const forceDeleteUser = async (id) => {
    return await del(`/users/${id}/force`)
  }

  /**
   * Get user statistics
   */
  const getUserStats = async () => {
    return await get('/users/stats')
  }

  return {
    // Core operations
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    
    // Status management
    toggleUserStatus,
    changeUserPassword,
    
    // Advanced operations
    restoreUser,
    forceDeleteUser,
    getUserStats
  }
}