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
      const { getAuthToken } = useApi()

      // 使用 runtimeConfig 取得正確的後端 URL
      const backendUrl = config.public.backendUrl ||
                        config.public.apiBaseUrl?.replace('/api', '') ||
                        `http://localhost:${config.public.backendPort || 9228}`

      console.log('[Export] Using backend URL:', backendUrl)

      // 使用 useApi 的 getAuthToken 方法取得 token
      const token = getAuthToken()
      console.log('[Export] Token status:', token ? 'Token exists' : 'No token')

      if (!token) {
        console.error('[Export] No authentication token found')
        return {
          success: false,
          error: { message: '請先登入' }
        }
      }

      // 使用完整的後端 URL 發送請求
      const response = await fetch(`${backendUrl}/api/meetings/${id}/export-notice`, {
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

  /**
   * Export signature book
   */
  const exportSignatureBook = async (id, isAnonymous = false) => {
    try {
      const config = useRuntimeConfig()
      const { getAuthToken } = useApi()

      // 使用 runtimeConfig 取得正確的後端 URL
      const backendUrl = config.public.backendUrl ||
                        config.public.apiBaseUrl?.replace('/api', '') ||
                        `http://localhost:${config.public.backendPort || 9228}`

      console.log('[Export] Using backend URL:', backendUrl)

      // 使用 useApi 的 getAuthToken 方法取得 token
      const token = getAuthToken()
      
      if (!token) {
        return {
          success: false,
          error: { message: '請先登入' }
        }
      }

      // 使用完整的後端 URL 發送請求
      const response = await fetch(`${backendUrl}/api/meetings/${id}/export-signature-book?anonymous=${isAnonymous}`, {
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
      let filename = '簽到冊.docx'
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
      console.error('Export signature book error:', error)
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
    exportMeetingNotice,
    exportSignatureBook
  }
}