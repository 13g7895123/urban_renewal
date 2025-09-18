/**
 * Urban Renewal API composable
 */
export const useUrbanRenewal = () => {
  const { get, post, put, delete: del } = useApi()

  /**
   * Get all urban renewal projects
   */
  const getUrbanRenewals = async (params = {}) => {
    return await get('/api/urban-renewals', params)
  }

  /**
   * Get specific urban renewal project
   */
  const getUrbanRenewal = async (id) => {
    return await get(`/api/urban-renewals/${id}`)
  }

  /**
   * Create new urban renewal project
   */
  const createUrbanRenewal = async (data) => {
    return await post('/api/urban-renewals', data)
  }

  /**
   * Update urban renewal project
   */
  const updateUrbanRenewal = async (id, data) => {
    return await put(`/api/urban-renewals/${id}`, data)
  }

  /**
   * Delete urban renewal project
   */
  const deleteUrbanRenewal = async (id) => {
    return await del(`/api/urban-renewals/${id}`)
  }

  /**
   * Get land plots for specific urban renewal
   */
  const getLandPlots = async (renewalId, params = {}) => {
    return await get(`/api/urban-renewals/${renewalId}/land-plots`, params)
  }

  /**
   * Create new land plot for urban renewal
   */
  const createLandPlot = async (renewalId, data) => {
    return await post(`/api/urban-renewals/${renewalId}/land-plots`, data)
  }

  /**
   * Get property owners for specific urban renewal
   */
  const getPropertyOwners = async (renewalId, params = {}) => {
    return await get(`/api/urban-renewals/${renewalId}/property-owners`, params)
  }

  return {
    getUrbanRenewals,
    getUrbanRenewal,
    createUrbanRenewal,
    updateUrbanRenewal,
    deleteUrbanRenewal,
    getLandPlots,
    createLandPlot,
    getPropertyOwners
  }
}