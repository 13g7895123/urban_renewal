/**
 * Clients API composable
 */
export const useClients = () => {
  const { get, post, put, delete: del } = useApi()

  /**
   * Get all clients with optional search and filters
   */
  const getClients = async (params = {}) => {
    return await get('/clients', params)
  }

  /**
   * Get a single client by ID
   */
  const getClient = async (id) => {
    return await get(`/clients/${id}`)
  }

  /**
   * Create a new client
   */
  const createClient = async (clientData) => {
    return await post('/clients', clientData)
  }

  /**
   * Update an existing client
   */
  const updateClient = async (id, clientData) => {
    return await put(`/clients/${id}`, clientData)
  }

  /**
   * Delete a client
   */
  const deleteClient = async (id) => {
    return await del(`/clients/${id}`)
  }

  /**
   * Get client statistics
   */
  const getClientStats = async (id) => {
    return await get(`/clients/${id}/stats`)
  }

  /**
   * Get projects for a specific client
   */
  const getClientProjects = async (id, params = {}) => {
    return await get(`/clients/${id}/projects`, params)
  }

  /**
   * Add contact method to client
   */
  const addContactMethod = async (clientId, contactData) => {
    return await post(`/clients/${clientId}/contacts`, contactData)
  }

  /**
   * Update contact method
   */
  const updateContactMethod = async (clientId, contactId, contactData) => {
    return await put(`/clients/${clientId}/contacts/${contactId}`, contactData)
  }

  /**
   * Delete contact method
   */
  const deleteContactMethod = async (clientId, contactId) => {
    return await del(`/clients/${clientId}/contacts/${contactId}`)
  }

  return {
    getClients,
    getClient,
    createClient,
    updateClient,
    deleteClient,
    getClientStats,
    getClientProjects,
    addContactMethod,
    updateContactMethod,
    deleteContactMethod
  }
}