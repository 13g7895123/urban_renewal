/**
 * Company (Urban Renewal) and User Management API composable
 */
export const useCompany = () => {
  const { get, post, put, delete: del } = useApi()

  /**
   * Get company profile (current user's company)
   */
  const getCompanyProfile = async () => {
    return await get('/companies/me')
  }

  /**
   * Update company profile (current user's company)
   */
  const updateCompanyProfile = async (data) => {
    return await put('/companies/me', data)
  }

  /**
   * Get company managers list
   */
  const getCompanyManagers = async (companyId, params = {}) => {
    return await get(`/companies/${companyId}/managers`, params)
  }

  /**
   * Get company users list (excluding managers)
   */
  const getCompanyUsers = async (companyId, params = {}) => {
    return await get(`/companies/${companyId}/users`, params)
  }

  /**
   * Get all company members (managers + users)
   */
  const getAllCompanyMembers = async (companyId, params = {}) => {
    // 使用 /users API 並篩選特定企業的成員
    return await get('/users', {
      ...params,
      urban_renewal_id: companyId,
      user_type: 'enterprise'
    })
  }

  /**
   * Set user as company user (remove manager privileges)
   */
  const setAsCompanyUser = async (userId) => {
    return await post(`/users/${userId}/set-as-company-user`)
  }

  /**
   * Set user as company manager
   */
  const setAsCompanyManager = async (userId) => {
    return await post(`/users/${userId}/set-as-company-manager`)
  }

  /**
   * Create new user
   */
  const createUser = async (data) => {
    return await post('/users', data)
  }

  /**
   * Delete user
   */
  const deleteUser = async (userId) => {
    return await del(`/users/${userId}`)
  }

  return {
    getCompanyProfile,
    updateCompanyProfile,
    getCompanyManagers,
    getCompanyUsers,
    getAllCompanyMembers,
    setAsCompanyUser,
    setAsCompanyManager,
    createUser,
    deleteUser
  }
}
