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
   * Search meetings
   */
  const searchMeetings = async (params) => {
    return await get('/meetings/search', params)
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
      // Use the same URL configuration as useApi
      const baseUrl = config.public.apiBaseUrl || config.public.backendUrl || 'http://localhost:4002'
      const apiUrl = baseUrl.endsWith('/api') ? baseUrl : `${baseUrl}/api`

      // Get auth token
      const token = localStorage.getItem('token')
      if (!token) {
        return {
          success: false,
          error: { message: '請先登入' }
        }
      }

      // Make request to download file
      const response = await fetch(`${apiUrl}/meetings/${id}/export-notice`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      if (!response.ok) {
        const errorData = await response.json()
        return {
          success: false,
          error: errorData.error || { message: '匯出失敗' }
        }
      }

      // Get filename from response header
      const contentDisposition = response.headers.get('Content-Disposition')
      let filename = '會議通知.docx'
      if (contentDisposition) {
        const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition)
        if (matches != null && matches[1]) {
          filename = decodeURIComponent(matches[1].replace(/['"]/g, ''))
        }
      }

      // Download file
      const blob = await response.blob()
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
    exportMeetingNotice
  }
}