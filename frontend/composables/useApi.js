/**
 * Base API composable for handling HTTP requests
 */
export const useApi = () => {
  const config = useRuntimeConfig()
  
  // Get base URL from environment - handle development vs production
  const getBaseURL = () => {
    // Check if we're in development mode
    const isDev = process.dev || process.env.NODE_ENV === 'development'

    if (isDev) {
      // In development, use proxy - return empty string since endpoints already include /api
      console.log('[API] Using development proxy: (empty baseURL)')
      return ''
    }

    // In production, always use the full API URL with /api path
    const apiBaseUrl = config.public.apiBaseUrl || config.public.backendUrl
    if (apiBaseUrl) {
      const fullApiUrl = `${apiBaseUrl}/api`
      console.log('[API] Using production API URL:', fullApiUrl)
      return fullApiUrl
    }

    // Fallback - use localhost backend
    const fallbackUrl = 'http://localhost:9228/api'
    console.log('[API] Using fallback API URL:', fallbackUrl)
    return fallbackUrl
  }
  
  const baseURL = getBaseURL()
  
  // Log the resolved base URL for debugging
  console.log('[API] Base URL resolved to:', baseURL)
  
  // Get authentication token
  const getAuthToken = () => {
    if (process.client) {
      return localStorage.getItem('auth_token')
    }
    return null
  }
  
  // Get authentication headers
  const getAuthHeaders = () => {
    const token = getAuthToken()
    if (token) {
      return {
        'Authorization': `Bearer ${token}`
      }
    }
    return {}
  }

  /**
   * Generic API request handler
   */
  const apiRequest = async (endpoint, options = {}) => {
    const authHeaders = getAuthHeaders()
    
    const defaultOptions = {
      baseURL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...authHeaders,
        ...options.headers
      },
      ...options
    }

    try {
      console.log(`[API] ${defaultOptions.method || 'GET'} ${baseURL}${endpoint}`)
      const response = await $fetch(endpoint, defaultOptions)
      console.log(`[API] Success: ${baseURL}${endpoint}`)
      return {
        success: true,
        data: response,
        error: null
      }
    } catch (error) {
      console.error(`[API] Error ${defaultOptions.method || 'GET'} ${baseURL}${endpoint}:`, error)
      console.error('[API] Full URL:', `${baseURL}${endpoint}`)
      console.error('[API] Options:', defaultOptions)
      
      // Handle authentication errors
      if (error.status === 401 || error.statusCode === 401) {
        console.warn('[API] Authentication error - clearing auth state')
        
        // Clear authentication data
        if (process.client) {
          localStorage.removeItem('auth_token')
          localStorage.removeItem('auth_user')
        }
        
        // Redirect to login page (only if not already on login page)
        if (process.client && !window.location.pathname.includes('/auth/login')) {
          try {
            await navigateTo('/auth/login')
          } catch (navError) {
            // If Nuxt navigation fails, fall back to window.location
            console.warn('[API] Navigation failed, using window.location fallback:', navError)
            window.location.href = '/login'
          }
        }
      }
      
      // Provide more detailed error information
      const errorDetails = {
        message: error.data?.message || error.message || '請求失敗',
        status: error.status || error.statusCode || 500,
        statusText: error.statusText || error.statusMessage || 'Internal Server Error',
        url: `${baseURL}${endpoint}`,
        method: defaultOptions.method || 'GET',
        errors: error.data?.errors || null
      }
      
      // Special handling for different error status codes
      if (errorDetails.status === 404) {
        errorDetails.message = `API endpoint not found: ${errorDetails.url}`
      } else if (errorDetails.status === 401) {
        errorDetails.message = error.data?.message || '登入已過期，請重新登入'
      } else if (errorDetails.status === 403) {
        // Preserve the specific 403 error message from backend
        errorDetails.message = error.data?.message || '權限不足'
      } else if (errorDetails.status === 422) {
        errorDetails.message = error.data?.message || '表單驗證失敗'
      } else if (errorDetails.status >= 500) {
        errorDetails.message = error.data?.message || '伺服器錯誤，請稍後再試'
      }
      
      return {
        success: false,
        data: null,
        error: errorDetails
      }
    }
  }

  /**
   * GET request
   */
  const get = async (endpoint, params = {}) => {
    return await apiRequest(endpoint, {
      method: 'GET',
      params
    })
  }

  /**
   * POST request
   */
  const post = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: 'POST',
      body
    })
  }

  /**
   * PUT request
   */
  const put = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: 'PUT',
      body
    })
  }

  /**
   * PATCH request
   */
  const patch = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: 'PATCH',
      body
    })
  }

  /**
   * DELETE request
   */
  const del = async (endpoint) => {
    return await apiRequest(endpoint, {
      method: 'DELETE'
    })
  }

  // Token management utilities
  const setAuthToken = (token) => {
    if (process.client) {
      localStorage.setItem('auth_token', token)
    }
  }
  
  const clearAuthToken = () => {
    if (process.client) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }
  
  return {
    get,
    post,
    put,
    patch,
    delete: del,
    apiRequest,
    getAuthToken,
    getAuthHeaders,
    setAuthToken,
    clearAuthToken
  }
}