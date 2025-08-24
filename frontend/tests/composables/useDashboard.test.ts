import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useDashboard } from '../../composables/useDashboard'

// Mock useApi
const mockGet = vi.fn()
const mockUseApi = vi.fn(() => ({
  get: mockGet
}))

// Set global mock before importing
global.useApi = mockUseApi

describe('useDashboard composable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('should get dashboard stats', async () => {
    const mockResponse = {
      success: true,
      data: {
        total_projects: 10,
        total_clients: 5,
        total_revenue: 100000,
        in_progress_projects: 3
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getDashboardStats()

    expect(mockGet).toHaveBeenCalledWith('/dashboard/stats')
    expect(result).toEqual(mockResponse)
  })

  it('should get recent activities', async () => {
    const limit = 5
    const mockResponse = {
      success: true,
      data: [
        { id: 1, description: 'Project started', time: '2 hours ago' },
        { id: 2, description: 'Project completed', time: '1 day ago' }
      ]
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getRecentActivities(limit)

    expect(mockGet).toHaveBeenCalledWith('/dashboard/activities', { limit })
    expect(result).toEqual(mockResponse)
  })

  it('should get monthly revenue trend', async () => {
    const months = 6
    const mockResponse = {
      success: true,
      data: [
        { month: '2025-01', revenue: 50000 },
        { month: '2025-02', revenue: 75000 }
      ]
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getMonthlyRevenueTrend(months)

    expect(mockGet).toHaveBeenCalledWith('/dashboard/revenue/trend', { months })
    expect(result).toEqual(mockResponse)
  })

  it('should get project status distribution', async () => {
    const mockResponse = {
      success: true,
      data: {
        contacted: { count: 3, total_amount: 30000 },
        in_progress: { count: 2, total_amount: 50000 },
        completed: { count: 1, total_amount: 25000 }
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getProjectStatusDistribution()

    expect(mockGet).toHaveBeenCalledWith('/dashboard/projects/status-distribution')
    expect(result).toEqual(mockResponse)
  })

  it('should get revenue stats by period', async () => {
    const period = 'month'
    const params = { year: 2025 }
    const mockResponse = {
      success: true,
      data: {
        period: 'month',
        revenue: 75000,
        project_count: 5
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getRevenueStats(period, params)

    expect(mockGet).toHaveBeenCalledWith('/dashboard/revenue/month', params)
    expect(result).toEqual(mockResponse)
  })

  it('should get client stats', async () => {
    const mockResponse = {
      success: true,
      data: {
        total_clients: 8,
        active_clients: 6,
        clients_with_projects: 5
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getClientStats()

    expect(mockGet).toHaveBeenCalledWith('/dashboard/clients', {})
    expect(result).toEqual(mockResponse)
  })

  it('should get upcoming deadlines', async () => {
    const days = 7
    const mockResponse = {
      success: true,
      data: [
        { id: 1, name: 'Project A', due_date: '2025-01-15' },
        { id: 2, name: 'Project B', due_date: '2025-01-18' }
      ]
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getUpcomingDeadlines(days)

    expect(mockGet).toHaveBeenCalledWith('/dashboard/deadlines', { days })
    expect(result).toEqual(mockResponse)
  })

  it('should get top clients by revenue', async () => {
    const limit = 5
    const period = 'year'
    const mockResponse = {
      success: true,
      data: {
        period: 'year',
        clients: [
          { id: 1, name: 'Client A', total_revenue: 100000 },
          { id: 2, name: 'Client B', total_revenue: 75000 }
        ]
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getTopClientsByRevenue(limit, period)

    expect(mockGet).toHaveBeenCalledWith('/dashboard/clients/top-revenue', { limit, period })
    expect(result).toEqual(mockResponse)
  })

  it('should get category revenue breakdown', async () => {
    const period = 'year'
    const mockResponse = {
      success: true,
      data: {
        website: { count: 5, total_amount: 150000 },
        script: { count: 3, total_amount: 75000 }
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const dashboard = useDashboard()
    const result = await dashboard.getCategoryRevenueBreakdown(period)

    expect(mockGet).toHaveBeenCalledWith('/dashboard/revenue/by-category', { period })
    expect(result).toEqual(mockResponse)
  })
})