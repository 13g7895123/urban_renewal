/**
 * Dashboard API composable
 */
export const useDashboard = () => {
  const { get } = useApi()

  /**
   * Get dashboard overview statistics
   */
  const getDashboardStats = async () => {
    return await get('/dashboard/stats')
  }

  /**
   * Get revenue statistics by period
   */
  const getRevenueStats = async (period = 'month', params = {}) => {
    return await get(`/dashboard/revenue/${period}`, params)
  }

  /**
   * Get project count statistics by period
   */
  const getProjectCountStats = async (period = 'month', params = {}) => {
    return await get(`/dashboard/projects/${period}`, params)
  }

  /**
   * Get client statistics
   */
  const getClientStats = async (params = {}) => {
    return await get('/dashboard/clients', params)
  }

  /**
   * Get recent activities
   */
  const getRecentActivities = async (limit = 10) => {
    return await get('/dashboard/activities', { limit })
  }

  /**
   * Get upcoming deadlines
   */
  const getUpcomingDeadlines = async (days = 30) => {
    return await get('/dashboard/deadlines', { days })
  }

  /**
   * Get monthly revenue trend
   */
  const getMonthlyRevenueTrend = async (months = 12) => {
    return await get('/dashboard/revenue/trend', { months })
  }

  /**
   * Get project status distribution
   */
  const getProjectStatusDistribution = async () => {
    return await get('/dashboard/projects/status-distribution')
  }

  /**
   * Get category revenue breakdown
   */
  const getCategoryRevenueBreakdown = async (period = 'year') => {
    return await get('/dashboard/revenue/by-category', { period })
  }

  /**
   * Get top clients by revenue
   */
  const getTopClientsByRevenue = async (limit = 10, period = 'year') => {
    return await get('/dashboard/clients/top-revenue', { limit, period })
  }

  /**
   * Get daily statistics for specific date range
   */
  const getDailyStats = async (startDate, endDate) => {
    return await get('/dashboard/daily-stats', { 
      start_date: startDate,
      end_date: endDate 
    })
  }

  /**
   * Get weekly statistics for specific date range
   */
  const getWeeklyStats = async (startDate, endDate) => {
    return await get('/dashboard/weekly-stats', { 
      start_date: startDate,
      end_date: endDate 
    })
  }

  /**
   * Get yearly statistics
   */
  const getYearlyStats = async (year) => {
    return await get('/dashboard/yearly-stats', { year })
  }

  return {
    getDashboardStats,
    getRevenueStats,
    getProjectCountStats,
    getClientStats,
    getRecentActivities,
    getUpcomingDeadlines,
    getMonthlyRevenueTrend,
    getProjectStatusDistribution,
    getCategoryRevenueBreakdown,
    getTopClientsByRevenue,
    getDailyStats,
    getWeeklyStats,
    getYearlyStats
  }
}