/**
 * Website Settings API composable
 */
export const useWebsiteSettingsApi = () => {
  const api = useApi()

  /**
   * Get all website settings
   */
  const getSettings = async () => {
    return await api.get('/website-settings')
  }

  /**
   * Update website settings
   */
  const updateSettings = async (settings) => {
    return await api.post('/website-settings', settings)
  }

  /**
   * Get specific setting
   */
  const getSetting = async (key) => {
    return await api.get(`/website-settings/${key}`)
  }

  /**
   * Reset settings to defaults
   */
  const resetDefaults = async () => {
    return await api.post('/website-settings/reset-defaults')
  }

  return {
    getSettings,
    updateSettings,
    getSetting,
    resetDefaults
  }
}