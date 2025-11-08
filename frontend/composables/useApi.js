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
    const isClient = process.client

    // Priority 1: Use configured API base URL
    if (config.public.apiBaseUrl) {
      const baseUrl = config.public.apiBaseUrl
      const apiUrl = baseUrl.endsWith('/api') ? baseUrl : `${baseUrl}/api`
      console.log('[API] Using configured apiBaseUrl:', apiUrl)
      return apiUrl
    }

    // Priority 2: Construct from backend URL
    if (config.public.backendUrl) {
      const baseUrl = config.public.backendUrl
      const apiUrl = baseUrl.endsWith('/api') ? baseUrl : `${baseUrl}/api`
      console.log('[API] Using backendUrl:', apiUrl)
      return apiUrl
    }

    // Priority 3: Development fallback
    const isDev = process.dev || process.env.NODE_ENV === 'development'
    if (isDev) {
      // For client-side in development, use localhost with backend port
      if (isClient) {
        const port = config.public.backendPort || 9228
        const devUrl = `http://localhost:${port}/api`
        console.log('[API] Development client using:', devUrl)
        return devUrl
      } else {
        // For server-side in development, use Docker network name
        const devUrl = 'http://backend:8000/api'
        console.log('[API] Development server using:', devUrl)
        return devUrl
      }
    }

    // No configuration found
    console.error('[API] No API base URL configured! Check environment variables.')
    throw new Error('API base URL not configured. Please set NUXT_PUBLIC_API_BASE_URL environment variable.')
  }
  
  const baseURL = getBaseURL()
  
  // Log the resolved base URL for debugging
  console.log('[API] Base URL resolved to:', baseURL)
  
  // Get authentication token
  const getAuthToken = () => {
    if (process.client) {
      // 優先從 Pinia store 取得 token（使用 sessionStorage 持久化）
      try {
        const authStore = useAuthStore()
        console.log('[API] Checking authStore.token:', authStore.token ? 'Token exists' : 'No token')
        if (authStore.token) {
          console.log('[API] Using token from authStore:', authStore.token.substring(0, 20) + '...')
          return authStore.token
        }
      } catch (error) {
        // 如果 store 未初始化，從 sessionStorage 讀取
        console.warn('[API] Could not access auth store, falling back to sessionStorage', error)
      }

      // 回退方案：從 sessionStorage 讀取（Pinia 持久化的資料）
      // Pinia 使用 store id 'auth' 作為 key
      const persistedAuth = sessionStorage.getItem('auth')
      console.log('[API] SessionStorage auth data:', persistedAuth ? 'Data exists' : 'No data')
      if (persistedAuth) {
        try {
          const authData = JSON.parse(persistedAuth)
          console.log('[API] Parsed auth data:', authData)
          // Pinia 持久化會保存整個 store 的指定 paths
          const token = authData.token || null
          if (token) {
            console.log('[API] Using token from sessionStorage:', token.substring(0, 20) + '...')
          }
          return token
        } catch (e) {
          console.error('[API] Failed to parse auth from sessionStorage:', e)
        }
      }
      
      console.warn('[API] No token found in authStore or sessionStorage')
    }
    return null
  }

  // Get authentication headers
  const getAuthHeaders = () => {
    const token = getAuthToken()
    console.log('[API] getAuthHeaders - token:', token ? 'Token exists' : 'No token')
    if (token) {
      console.log('[API] Adding Authorization header')
      return {
        'Authorization': `Bearer ${token}`
      }
    }
    console.warn('[API] No Authorization header added - token is missing')
    return {}
  }

  /**
   * Generic API request handler
   */
  const apiRequest = async (endpoint, options = {}) => {
    const authHeaders = getAuthHeaders()

    // 檢查 body 是否為 FormData,如果是則不設定 Content-Type
    // 讓瀏覽器自動設定正確的 multipart/form-data boundary
    const isFormData = options.body instanceof FormData

    const defaultOptions = {
      baseURL,
      headers: {
        // 如果是 FormData,不設定 Content-Type
        ...(isFormData ? {} : {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }),
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
          // 從 Pinia store 或 sessionStorage 取得 refresh token
          let refreshToken = null
          try {
            const authStore = useAuthStore()
            refreshToken = authStore.refreshToken
          } catch (e) {
            // 從 sessionStorage 讀取（Pinia 持久化使用 'auth' 作為 key）
            const persistedAuth = sessionStorage.getItem('auth')
            if (persistedAuth) {
              try {
                const authData = JSON.parse(persistedAuth)
                refreshToken = authData.refreshToken || null
              } catch (parseError) {
                console.error('[API] Failed to parse auth from sessionStorage:', parseError)
              }
            }
          }

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
                    // 更新 Pinia store
                    try {
                      const authStore = useAuthStore()
                      authStore.token = response.data.token
                      authStore.refreshToken = response.data.refresh_token
                      if (response.data.expires_in) {
                        const expiresAt = new Date(Date.now() + (response.data.expires_in * 1000))
                        authStore.tokenExpiresAt = expiresAt.toISOString()
                      }
                    } catch (storeError) {
                      console.error('[API] Failed to update auth store:', storeError)
                    }
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

        // Clear authentication data from sessionStorage and Pinia store
        try {
          const authStore = useAuthStore()
          authStore.logout(true) // skipApiCall = true 避免再次呼叫 logout API
        } catch (storeError) {
          console.error('[API] Failed to access auth store for logout:', storeError)
          // 手動清除 sessionStorage
          sessionStorage.removeItem('auth')
        }

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
      try {
        const authStore = useAuthStore()
        authStore.token = token
      } catch (error) {
        console.error('[API] Failed to set auth token in store:', error)
      }
    }
  }

  const clearAuthToken = () => {
    if (process.client) {
      try {
        const authStore = useAuthStore()
        authStore.logout(true)
      } catch (error) {
        console.error('[API] Failed to clear auth token from store:', error)
        // 手動清除 sessionStorage
        sessionStorage.removeItem('auth')
      }
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