/**
 * Base API composable for handling HTTP requests
 */
// Token refresh state (shared across all instances)
let isRefreshing = false
let refreshPromise = null

export const useApi = () => {
  const config = useRuntimeConfig()
  // Avoid circular dependency - don't use authStore in useApi
  // const authStore = process.client ? useAuthStore() : null

  // Get base URL from environment - handle development vs production
  const getBaseURL = () => {
    // Check if we're running on client-side (browser) or server-side (SSR)
    const isClient = process.client

    // On client side, we need to use the host-accessible URL (localhost or actual domain)
    if (isClient) {
      // Check if browser-specific URL is configured
      if (typeof window !== 'undefined') {
        // For local development in browser, use localhost with backend port
        const isDev = config.public.backendHost === 'backend' || config.public.backendHost === 'localhost'
        console.log('test');
        if (isDev) {
          const clientUrl = `http://localhost:${config.public.backendPort || 9228}/api`
          console.log('[API] Client-side using localhost URL:', clientUrl)
          return clientUrl
        }
      }

      // For production or configured public URL
      if (config.public.apiBaseUrl && !config.public.apiBaseUrl.includes('backend:')) {
        const baseUrl = config.public.apiBaseUrl
        const apiUrl = baseUrl.endsWith('/api') ? baseUrl : `${baseUrl}/api`
        console.log('[API] Client-side using configured URL:', apiUrl)
        return apiUrl
      }
    }

    // Server-side rendering: use internal Docker network URL
    if (config.public.apiBaseUrl) {
      const baseUrl = config.public.apiBaseUrl
      const apiUrl = baseUrl.endsWith('/api') ? baseUrl : `${baseUrl}/api`
      console.log('[API] Server-side using internal URL:', apiUrl)
      return apiUrl
    }

    // Fallback for development
    const isDev = process.dev || process.env.NODE_ENV === 'development'
    if (isDev) {
      const fallbackUrl = isClient ? 'http://localhost:9228/api' : 'http://backend:8000/api'
      console.log('[API] Using fallback URL:', fallbackUrl)
      return fallbackUrl
    }

    // Final fallback
    console.error('[API] No API base URL configured! Check environment variables.')
    throw new Error('API base URL not configured. Please set NUXT_PUBLIC_API_BASE_URL environment variable.')
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
      
      // Handle 401 authentication errors
      if ((error.status === 401 || error.statusCode === 401) && process.client) {
        // Skip retry for auth endpoints to avoid infinite loops
        const isAuthEndpoint = endpoint.includes('/auth/login') ||
                               endpoint.includes('/auth/refresh') ||
                               endpoint.includes('/auth/logout')

        if (!isAuthEndpoint) {
          const refreshToken = localStorage.getItem('auth_refresh_token')

          if (refreshToken) {
            console.log('[API] 401 error detected, attempting token refresh...')

            try {
              // Wait for ongoing refresh or start new one
              if (isRefreshing) {
                console.log('[API] Token refresh already in progress, waiting...')
                await refreshPromise
              } else {
                isRefreshing = true
                refreshPromise = (async () => {
                  const response = await $fetch('/auth/refresh', {
                    baseURL,
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json'
                    },
                    body: { refresh_token: refreshToken }
                  })

                  if (response?.data?.token) {
                    localStorage.setItem('auth_token', response.data.token)
                    localStorage.setItem('auth_refresh_token', response.data.refresh_token)
                    const expiresAt = new Date(Date.now() + (response.data.expires_in * 1000))
                    localStorage.setItem('auth_token_expires_at', expiresAt.toISOString())
                  }

                  return response
                })()
                  .finally(() => {
                    isRefreshing = false
                    refreshPromise = null
                  })
                await refreshPromise
              }

              // Retry the original request with new token
              console.log('[API] Token refreshed successfully, retrying original request...')
              const retryOptions = {
                ...defaultOptions,
                headers: {
                  ...defaultOptions.headers,
                  ...getAuthHeaders() // Get updated token
                }
              }

              const retryResponse = await $fetch(endpoint, retryOptions)
              console.log(`[API] Retry success: ${baseURL}${endpoint}`)
              return {
                success: true,
                data: retryResponse,
                error: null
              }
            } catch (refreshError) {
              console.error('[API] Token refresh failed:', refreshError)
              // Refresh failed, proceed with logout
            }
          }
        }

        // Token refresh failed or not available, clear auth and redirect
        console.warn('[API] Authentication error - clearing auth state')

        // Clear authentication data
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_refresh_token')
        localStorage.removeItem('auth_token_expires_at')
        localStorage.removeItem('auth_user')

        // Redirect to login page (only if not already on login page)
        if (!window.location.pathname.includes('/login')) {
          try {
            await navigateTo('/login')
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