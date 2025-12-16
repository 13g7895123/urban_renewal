/**
 * Meeting Attendance API composable
 */
export const useAttendance = () => {
  const { get, post, patch } = useApi()

  /**
   * Get meeting attendance records
   */
  const getAttendance = async (meetingId, params = {}) => {
    return await get(`/meetings/${meetingId}/attendances`, params)
  }

  /**
   * Check in for a meeting
   */
  const checkIn = async (meetingId, ownerId, data) => {
    return await post(`/meetings/${meetingId}/attendances/${ownerId}`, data)
  }

  /**
   * Batch check in multiple attendees
   */
  const batchCheckIn = async (meetingId, data) => {
    return await post(`/meetings/${meetingId}/attendances/batch`, data)
  }

  /**
   * Update attendance status
   */
  const updateAttendanceStatus = async (meetingId, ownerId, status) => {
    return await patch(`/meetings/${meetingId}/attendances/${ownerId}`, {
      attendance_type: status
    })
  }

  /**
   * Get attendance summary for meeting
   * Note: Using getAttendanceStatistics as they seem redundant in doc or one is missing
   * Doc says: GET /api/meetings/{id}/attendances/statistics
   */
  const getAttendanceSummary = async (meetingId) => {
    return await get(`/meetings/${meetingId}/attendances/statistics`)
  }

  /**
   * Export attendance data
   */
  const exportAttendance = async (meetingId, params = {}) => {
    // Doc says POST /api/meetings/{id}/attendances/export
    return await post(`/meetings/${meetingId}/attendances/export`, params)
  }

  /**
   * Get attendance statistics
   */
  const getAttendanceStatistics = async (meetingId) => {
    return await get(`/meetings/${meetingId}/attendances/statistics`)
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