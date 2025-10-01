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
    getStatusStatistics
  }
}