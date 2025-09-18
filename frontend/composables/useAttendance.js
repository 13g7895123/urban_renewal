/**
 * Meeting Attendance API composable
 */
export const useAttendance = () => {
  const { get, post, patch } = useApi()

  /**
   * Get meeting attendance records
   */
  const getAttendance = async (params = {}) => {
    return await get('/api/meeting-attendance', params)
  }

  /**
   * Check in for a meeting
   */
  const checkIn = async (data) => {
    return await post('/api/meeting-attendance/check-in', data)
  }

  /**
   * Batch check in multiple attendees
   */
  const batchCheckIn = async (data) => {
    return await post('/api/meeting-attendance/batch-check-in', data)
  }

  /**
   * Update attendance status
   */
  const updateAttendanceStatus = async (id, status) => {
    return await patch(`/api/meeting-attendance/${id}/update-status`, { status })
  }

  /**
   * Get attendance summary for meeting
   */
  const getAttendanceSummary = async (meetingId) => {
    return await get(`/api/meeting-attendance/${meetingId}/summary`)
  }

  /**
   * Export attendance data
   */
  const exportAttendance = async (meetingId) => {
    return await get(`/api/meeting-attendance/${meetingId}/export`)
  }

  /**
   * Get attendance statistics
   */
  const getAttendanceStatistics = async (meetingId) => {
    return await get(`/api/meeting-attendance/${meetingId}/statistics`)
  }

  return {
    getAttendance,
    checkIn,
    batchCheckIn,
    updateAttendanceStatus,
    getAttendanceSummary,
    exportAttendance,
    getAttendanceStatistics
  }
}