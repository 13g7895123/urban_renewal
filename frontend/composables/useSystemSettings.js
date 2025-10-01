/**
 * System Settings API composable
 */
export const useSystemSettings = () => {
  const { get, post, patch, delete: del } = useApi()

  /**
   * Get all system settings (admin only)
   */
  const getSystemSettings = async (params = {}) => {
    return await get('/system-settings', params)
  }

  /**
   * Get public settings
   */
  const getPublicSettings = async () => {
    return await get('/system-settings/public')
  }

  /**
   * Get settings by category
   */
  const getSettingsByCategory = async (category) => {
    return await get(`/system-settings/category/${category}`)
  }

  /**
   * Get specific setting value
   */
  const getSetting = async (key) => {
    return await get(`/system-settings/get/${key}`)
  }

  /**
   * Set setting value
   */
  const setSetting = async (data) => {
    return await post('/system-settings/set', data)
  }

  /**
   * Batch set multiple settings
   */
  const batchSetSettings = async (data) => {
    return await post('/system-settings/batch-set', data)
  }

  /**
   * Reset setting to default value
   */
  const resetSetting = async (key) => {
    return await patch(`/system-settings/reset/${key}`)
  }

  /**
   * Get available categories
   */
  const getCategories = async () => {
    return await get('/system-settings/categories')
  }

  /**
   * Clear settings cache
   */
  const clearCache = async (key = null) => {
    const params = key ? { key } : {}
    return await del('/system-settings/clear-cache', params)
  }

  /**
   * Validate setting value
   */
  const validateSetting = async (data) => {
    return await post('/system-settings/validate', data)
  }

  /**
   * Get system information
   */
  const getSystemInfo = async () => {
    return await get('/system-settings/system-info')
  }

  return {
    getSystemSettings,
    getPublicSettings,
    getSettingsByCategory,
    getSetting,
    setSetting,
    batchSetSettings,
    resetSetting,
    getCategories,
    clearCache,
    validateSetting,
    getSystemInfo
  }
}