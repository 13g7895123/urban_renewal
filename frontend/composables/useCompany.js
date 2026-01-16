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
    // 使用 /users API 並篩選特定企業的成員，使用 company_id 而非 urban_renewal_id
    // 不限制 user_type，以返回該企業的所有成員（無論 user_type 是 general 或 enterprise）
    return await get('/users', {
      ...params,
      company_id: companyId
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

  /**
   * Get pending approval users
   */
  const getPendingUsers = async (params = {}) => {
    return await get('/companies/me/pending-users', params)
  }

  /**
   * Approve or reject a user application
   * @param {number} userId 
   * @param {string} action 'approve' or 'reject'
   */
  const approveUser = async (userId, action = 'approve') => {
    return await post(`/companies/me/approve-user/${userId}`, { action })
  }

  /**
   * Get company invite code
   */
  const getInviteCode = async () => {
    return await get('/companies/me/invite-code')
  }

  /**
   * Generate or reset company invite code
   */
  const generateInviteCode = async () => {
    return await post('/companies/me/generate-invite-code')
  }

  /**
   * Get urban renewals belonging to the company
   */
  const getCompanyRenewals = async (params = {}) => {
    return await get('/companies/me/renewals', params)
  }

  /**
   * Get members assigned to a specific renewal
   */
  const getRenewalMembers = async (renewalId) => {
    return await get(`/companies/me/renewals/${renewalId}/members`)
  }

  /**
   * Assign a member to a renewal
   */
  const assignMemberToRenewal = async (renewalId, userId, permissions = []) => {
    return await post(`/companies/me/renewals/${renewalId}/assign`, { user_id: userId, permissions })
  }

  /**
   * Unassign a member from a renewal
   */
  const unassignMemberFromRenewal = async (renewalId, userId) => {
    return await del(`/companies/me/renewals/${renewalId}/members/${userId}`)
  }

  /**
   * Get available members (approved company members)
   */
  const getAvailableMembers = async () => {
    return await get('/companies/me/available-members')
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
    deleteUser,
    getPendingUsers,
    approveUser,
    getInviteCode,
    generateInviteCode,
    getCompanyRenewals,
    getRenewalMembers,
    assignMemberToRenewal,
    unassignMemberFromRenewal,
    getAvailableMembers
  }
}
