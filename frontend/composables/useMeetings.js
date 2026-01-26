/**
 * Meetings API composable
 */
export const useMeetings = () => {
  const { get, post, put, patch, delete: del } = useApi()

  /**
   * Get all meetings
   */
  const getMeetings = async (params = {}) => {
    return await get('/meetings', params)
  }

  /**
   * Get specific meeting
   */
  const getMeeting = async (id) => {
    return await get(`/meetings/${id}`)
  }

  /**
   * Create new meeting
   */
  const createMeeting = async (data) => {
    return await post('/meetings', data)
  }

  /**
   * Update meeting
   */
  const updateMeeting = async (id, data) => {
    return await put(`/meetings/${id}`, data)
  }

  /**
   * Delete meeting
   */
  const deleteMeeting = async (id) => {
    return await del(`/meetings/${id}`)
  }

  /**
   * Update meeting status
   */
  const updateMeetingStatus = async (id, status) => {
    return await patch(`/meetings/${id}/status`, { status })
  }

  /**
   * Get meeting statistics
   */
  const getMeetingStatistics = async (id) => {
    return await get(`/meetings/${id}/statistics`)
  }

  /**
   * Search meetings (using standard list endpoint with parameter)
   */
  const searchMeetings = async (params) => {
    // API doc says GET /api/meetings?search=...
    return await getMeetings(params)
  }

  /**
   * Get upcoming meetings
   */
  const getUpcomingMeetings = async () => {
    return await get('/meetings/upcoming')
  }

  /**
   * Get meeting status statistics
   */
  const getStatusStatistics = async () => {
    return await get('/meetings/status-statistics')
  }

  /**
   * Export meeting notice
   */
  const exportMeetingNotice = async (id) => {
    try {
      const config = useRuntimeConfig()

      console.log('[Export] Exporting meeting notice...')

      // Use $fetch to download the file with automatic authentication
      const blob = await $fetch(`/meetings/${id}/export-notice`, {
        method: 'GET',
        responseType: 'blob'
      })

      // Default filename
      let filename = `會議通知_${id}_${new Date().toISOString().split('T')[0]}.docx`

      // Download file
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = filename
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)

      return {
        success: true,
        message: '匯出成功'
      }
    } catch (error) {
      console.error('Export meeting notice error:', error)
      return {
        success: false,
        error: { message: error.message || '匯出失敗' }
      }
    }
  }

  /**
   * Export signature book
   */
  const exportSignatureBook = async (id, isAnonymous = false) => {
    try {
      const config = useRuntimeConfig()

      console.log('[Export] Exporting signature book...')

      // Use $fetch to download the file with automatic authentication
      const blob = await $fetch(`/meetings/${id}/export-signature-book?anonymous=${isAnonymous}`, {
        method: 'GET',
        responseType: 'blob'
      })

      // Default filename
      let filename = `簽到冊_${id}_${new Date().toISOString().split('T')[0]}.docx`

      // Download file
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = filename
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)

      return {
        success: true,
        message: '匯出成功'
      }
    } catch (error) {
      console.error('Export signature book error:', error)
      return {
        success: false,
        error: { message: error.message || '匯出失敗' }
      }
    }
  }

  /**
   * Get eligible voters (snapshot) for a meeting
   */
  const getEligibleVoters = async (meetingId, params = {}) => {
    return await get(`/meetings/${meetingId}/eligible-voters`, params)
  }

  /**
   * Refresh eligible voters snapshot for a meeting
   */
  const refreshEligibleVoters = async (meetingId) => {
    return await post(`/meetings/${meetingId}/eligible-voters/refresh`)
  }

  return {
    getMeetings,
    getMeeting,
    createMeeting,
    updateMeeting,
    deleteMeeting,
    updateMeetingStatus,
    getMeetingStatistics,
    searchMeetings,
    getUpcomingMeetings,
    getStatusStatistics,
    exportMeetingNotice,
    exportSignatureBook,
    getEligibleVoters,
    refreshEligibleVoters
  }
}