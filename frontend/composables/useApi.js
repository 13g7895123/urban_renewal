/**
 * Base API composable for handling HTTP requests
 * 使用 httpOnly Cookie 進行認證
 */

export const useApi = () => {
  const config = useRuntimeConfig()

  // Get base URL from environment
  const getBaseURL = () => {
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

    // No configuration found
    console.error('[API] No API base URL configured! Check environment variables.')
    throw new Error('API base URL not configured. Please set NUXT_PUBLIC_API_BASE_URL environment variable.')
  }

  const baseURL = getBaseURL()

  console.log('[API] Base URL resolved to:', baseURL)

  /**
   * Generic API request handler
   * 使用 credentials: 'include' 自動發送 httpOnly cookies
   */
  const apiRequest = async (endpoint, options = {}) => {
    const isFormData = options.body instanceof FormData

    const defaultOptions = {
      baseURL,
      credentials: 'include', // 重要：發送 cookies
      headers: {
        ...(isFormData ? {} : {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }),
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

      // Handle 401 authentication errors
      if ((error.status === 401 || error.statusCode === 401) && process.client) {
        const isAuthEndpoint = endpoint.includes('/auth/login') ||
          endpoint.includes('/auth/refresh') ||
          endpoint.includes('/auth/logout') ||
          endpoint.includes('/auth/me')


        if (!isAuthEndpoint) {
          console.log('[API] 401 error detected, attempting token refresh...')

          try {
            // 嘗試刷新 token (cookie 會自動發送)
            const refreshResponse = await $fetch('/auth/refresh', {
              baseURL,
              method: 'POST',
              credentials: 'include',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            })

            if (refreshResponse?.success) {
              // Token 刷新成功，重試原始請求
              console.log('[API] Token refreshed successfully, retrying original request...')
              const retryResponse = await $fetch(endpoint, defaultOptions)
              console.log(`[API] Retry success: ${baseURL}${endpoint}`)
              return {
                success: true,
                data: retryResponse,
                error: null
              }
            }
          } catch (refreshError) {
            console.error('[API] Token refresh failed:', refreshError)
          }
        }

        // 認證失敗，清除前端狀態並跳轉登入頁
        console.warn('[API] Authentication error - redirecting to login')

        if (process.client) {
          try {
            const authStore = useAuthStore()
            authStore.clearLocalState()
          } catch (storeError) {
            console.error('[API] Failed to clear auth store:', storeError)
          }

          if (!window.location.pathname.includes('/login')) {
            try {
              await navigateTo('/login')
            } catch (navError) {
              window.location.href = '/login'
            }
          }
        }
      }

      // 提供詳細錯誤資訊
      const errorDetails = {
        message: error.data?.message || error.message || '請求失敗',
        status: error.status || error.statusCode || 500,
        statusText: error.statusText || error.statusMessage || 'Internal Server Error',
        url: `${baseURL}${endpoint}`,
        method: defaultOptions.method || 'GET',
        errors: error.data?.errors || null
      }

      if (errorDetails.status === 404) {
        errorDetails.message = `API endpoint not found: ${errorDetails.url}`
      } else if (errorDetails.status === 401) {
        errorDetails.message = error.data?.message || '登入已過期，請重新登入'
      } else if (errorDetails.status === 403) {
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

  return {
    get,
    post,
    put,
    patch,
    delete: del,
    apiRequest
  }
}