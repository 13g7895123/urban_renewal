import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { useApi } from '../../composables/useApi'

// Mock $fetch
const mockFetch = vi.fn()
global.$fetch = mockFetch

describe('useApi composable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    // Mock localStorage
    Object.defineProperty(window, 'localStorage', {
      value: {
        getItem: vi.fn(),
        setItem: vi.fn(),
        removeItem: vi.fn(),
        clear: vi.fn(),
      },
      writable: true
    })
  })

  afterEach(() => {
    vi.resetAllMocks()
  })

  it('should make successful GET request', async () => {
    const mockResponse = { data: 'test' }
    mockFetch.mockResolvedValue(mockResponse)

    const api = useApi()
    const result = await api.get('/test')

    expect(result.success).toBe(true)
    expect(result.data).toEqual(mockResponse)
    expect(result.error).toBe(null)
    expect(mockFetch).toHaveBeenCalledWith('/test', expect.objectContaining({
      method: 'GET'
    }))
  })

  it('should make successful POST request', async () => {
    const mockResponse = { id: 1, name: 'test' }
    const requestData = { name: 'test' }
    mockFetch.mockResolvedValue(mockResponse)

    const api = useApi()
    const result = await api.post('/test', requestData)

    expect(result.success).toBe(true)
    expect(result.data).toEqual(mockResponse)
    expect(mockFetch).toHaveBeenCalledWith('/test', expect.objectContaining({
      method: 'POST',
      body: requestData
    }))
  })

  it('should handle API errors', async () => {
    const mockError = {
      status: 404,
      statusText: 'Not Found',
      data: { message: 'Resource not found' }
    }
    mockFetch.mockRejectedValue(mockError)

    const api = useApi()
    const result = await api.get('/test')

    expect(result.success).toBe(false)
    expect(result.data).toBe(null)
    expect(result.error).toEqual(expect.objectContaining({
      status: 404,
      message: 'API endpoint not found: http://localhost:8000/api/test',
      statusText: 'Not Found',
      url: 'http://localhost:8000/api/test',
      method: 'GET',
      errors: null
    }))
  })

  it('should include auth token when available', () => {
    const mockToken = 'test-token'
    vi.mocked(localStorage.getItem).mockReturnValue(mockToken)

    const api = useApi()
    const headers = api.getAuthHeaders()

    expect(headers).toEqual({
      'Authorization': `Bearer ${mockToken}`
    })
  })

  it('should return empty headers when no token available', () => {
    vi.mocked(localStorage.getItem).mockReturnValue(null)

    const api = useApi()
    const headers = api.getAuthHeaders()

    expect(headers).toEqual({})
  })

  it('should set auth token in localStorage', () => {
    const testToken = 'new-token'
    const api = useApi()
    
    api.setAuthToken(testToken)
    
    expect(localStorage.setItem).toHaveBeenCalledWith('auth_token', testToken)
  })

  it('should clear auth data from localStorage', () => {
    const api = useApi()
    
    api.clearAuthToken()
    
    expect(localStorage.removeItem).toHaveBeenCalledWith('auth_token')
    expect(localStorage.removeItem).toHaveBeenCalledWith('auth_user')
  })

  it('should handle 401 authentication errors', async () => {
    const mockError = {
      status: 401,
      statusText: 'Unauthorized',
      data: { message: 'Token expired' }
    }
    mockFetch.mockRejectedValue(mockError)

    const api = useApi()
    const result = await api.get('/test')

    expect(result.success).toBe(false)
    expect(result.error?.status).toBe(401)
    expect(localStorage.removeItem).toHaveBeenCalledWith('auth_token')
    expect(localStorage.removeItem).toHaveBeenCalledWith('auth_user')
  })
})