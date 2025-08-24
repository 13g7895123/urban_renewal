import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useProjects } from '../../composables/useProjects'

// Mock useApi
const mockGet = vi.fn()
const mockPost = vi.fn()
const mockPut = vi.fn()
const mockDelete = vi.fn()
const mockUseApi = vi.fn(() => ({
  get: mockGet,
  post: mockPost,
  put: mockPut,
  delete: mockDelete
}))

// Set global mock before importing
global.useApi = mockUseApi

describe('useProjects composable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('should get projects list', async () => {
    const mockResponse = {
      success: true,
      data: {
        data: [
          { id: 1, name: 'Project 1' },
          { id: 2, name: 'Project 2' }
        ]
      }
    }
    mockGet.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.getProjects()

    expect(mockGet).toHaveBeenCalledWith('/projects', {})
    expect(result).toEqual(mockResponse)
  })

  it('should get projects with filters', async () => {
    const filters = {
      search: 'test',
      category: 'website',
      status: 'in_progress'
    }
    mockGet.mockResolvedValue({ success: true, data: [] })

    const projects = useProjects()
    await projects.getProjects(filters)

    expect(mockGet).toHaveBeenCalledWith('/projects', filters)
  })

  it('should get single project', async () => {
    const projectId = 1
    const mockResponse = {
      success: true,
      data: { id: 1, name: 'Test Project' }
    }
    mockGet.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.getProject(projectId)

    expect(mockGet).toHaveBeenCalledWith('/projects/1')
    expect(result).toEqual(mockResponse)
  })

  it('should create new project', async () => {
    const projectData = {
      name: 'New Project',
      client_id: 1,
      category: 'website',
      amount: 50000
    }
    const mockResponse = {
      success: true,
      data: { id: 1, ...projectData }
    }
    mockPost.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.createProject(projectData)

    expect(mockPost).toHaveBeenCalledWith('/projects', projectData)
    expect(result).toEqual(mockResponse)
  })

  it('should update existing project', async () => {
    const projectId = 1
    const updateData = {
      name: 'Updated Project',
      status: 'completed'
    }
    const mockResponse = {
      success: true,
      data: { id: 1, ...updateData }
    }
    mockPut.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.updateProject(projectId, updateData)

    expect(mockPut).toHaveBeenCalledWith('/projects/1', updateData)
    expect(result).toEqual(mockResponse)
  })

  it('should delete project', async () => {
    const projectId = 1
    const mockResponse = { success: true }
    mockDelete.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.deleteProject(projectId)

    expect(mockDelete).toHaveBeenCalledWith('/projects/1')
    expect(result).toEqual(mockResponse)
  })

  it('should update project status', async () => {
    const projectId = 1
    const status = 'completed'
    const mockResponse = {
      success: true,
      data: { id: 1, status }
    }
    mockPut.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.updateProjectStatus(projectId, status)

    expect(mockPut).toHaveBeenCalledWith('/projects/1/status', { status })
    expect(result).toEqual(mockResponse)
  })

  it('should get projects by category', async () => {
    const category = 'website'
    const mockResponse = {
      success: true,
      data: [{ id: 1, category: 'website' }]
    }
    mockGet.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.getProjectsByCategory(category)

    expect(mockGet).toHaveBeenCalledWith('/projects/category/website', {})
    expect(result).toEqual(mockResponse)
  })

  it('should get projects by status', async () => {
    const status = 'in_progress'
    const mockResponse = {
      success: true,
      data: [{ id: 1, status: 'in_progress' }]
    }
    mockGet.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.getProjectsByStatus(status)

    expect(mockGet).toHaveBeenCalledWith('/projects/status/in_progress', {})
    expect(result).toEqual(mockResponse)
  })

  it('should export projects', async () => {
    const format = 'excel'
    const mockResponse = {
      success: true,
      data: { download_url: '/export/projects.xlsx' }
    }
    mockGet.mockResolvedValue(mockResponse)

    const projects = useProjects()
    const result = await projects.exportProjects(format)

    expect(mockGet).toHaveBeenCalledWith('/projects/export/excel', {})
    expect(result).toEqual(mockResponse)
  })
})