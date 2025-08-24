/**
 * Projects API composable
 */
export const useProjects = () => {
  const { get, post, put, delete: del } = useApi()

  /**
   * Get all projects with optional search and filters
   */
  const getProjects = async (params = {}) => {
    return await get('/projects', params)
  }

  /**
   * Get a single project by ID
   */
  const getProject = async (id) => {
    return await get(`/projects/${id}`)
  }

  /**
   * Create a new project
   */
  const createProject = async (projectData) => {
    return await post('/projects', projectData)
  }

  /**
   * Update an existing project
   */
  const updateProject = async (id, projectData) => {
    return await put(`/projects/${id}`, projectData)
  }

  /**
   * Delete a project
   */
  const deleteProject = async (id) => {
    return await del(`/projects/${id}`)
  }

  /**
   * Update project status
   */
  const updateProjectStatus = async (id, status) => {
    return await put(`/projects/${id}/status`, { status })
  }

  /**
   * Get project milestones
   */
  const getProjectMilestones = async (id) => {
    return await get(`/projects/${id}/milestones`)
  }

  /**
   * Create project milestone
   */
  const createProjectMilestone = async (projectId, milestoneData) => {
    return await post(`/projects/${projectId}/milestones`, milestoneData)
  }

  /**
   * Update project milestone
   */
  const updateProjectMilestone = async (projectId, milestoneId, milestoneData) => {
    return await put(`/projects/${projectId}/milestones/${milestoneId}`, milestoneData)
  }

  /**
   * Delete project milestone
   */
  const deleteProjectMilestone = async (projectId, milestoneId) => {
    return await del(`/projects/${projectId}/milestones/${milestoneId}`)
  }

  /**
   * Get project statistics
   */
  const getProjectStats = async (params = {}) => {
    return await get('/projects/stats', params)
  }

  /**
   * Get projects by category
   */
  const getProjectsByCategory = async (category, params = {}) => {
    return await get(`/projects/category/${category}`, params)
  }

  /**
   * Get projects by status
   */
  const getProjectsByStatus = async (status, params = {}) => {
    return await get(`/projects/status/${status}`, params)
  }

  /**
   * Export projects data
   */
  const exportProjects = async (format = 'excel', params = {}) => {
    return await get(`/projects/export/${format}`, params)
  }

  return {
    getProjects,
    getProject,
    createProject,
    updateProject,
    deleteProject,
    updateProjectStatus,
    getProjectMilestones,
    createProjectMilestone,
    updateProjectMilestone,
    deleteProjectMilestone,
    getProjectStats,
    getProjectsByCategory,
    getProjectsByStatus,
    exportProjects
  }
}