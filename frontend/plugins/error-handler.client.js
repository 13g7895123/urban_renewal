/**
 * Global error handler plugin for authentication and API errors
 */
export default defineNuxtPlugin(() => {
  // Handle authentication errors globally
  const handleAuthError = (error) => {
    console.error('[Auth Error]', error)
    
    const authStore = useAuthStore()
    const notificationStore = useNotificationsStore()
    
    // Clear auth state and redirect to login
    authStore.logout(true)
    
    // Show error notification
    notificationStore.addNotification({
      type: 'error',
      title: '認證錯誤',
      message: '登入已過期，請重新登入',
      priority: 'high',
      icon: 'ExclamationCircleIcon'
    })
  }
  
  // Handle API errors globally
  const handleApiError = (error) => {
    console.error('[API Error]', error)
    
    const notificationStore = useNotificationsStore()
    
    let message = '請求失敗，請稍後再試'
    let title = 'API 錯誤'
    
    if (error.status === 401) {
      return handleAuthError(error)
    } else if (error.status === 403) {
      message = '權限不足，無法執行此操作'
      title = '權限錯誤'
    } else if (error.status === 404) {
      message = '請求的資源不存在'
      title = '資源不存在'
    } else if (error.status === 422) {
      message = '表單驗證失敗，請檢查輸入內容'
      title = '驗證錯誤'
    } else if (error.status >= 500) {
      message = '伺服器內部錯誤，請聯繫管理員'
      title = '伺服器錯誤'
    }
    
    // Show error notification
    notificationStore.addNotification({
      type: 'error',
      title: title,
      message: message,
      priority: error.status >= 500 ? 'high' : 'medium',
      icon: 'ExclamationCircleIcon'
    })
  }
  
  // Handle network errors
  const handleNetworkError = (error) => {
    console.error('[Network Error]', error)
    
    const notificationStore = useNotificationsStore()
    
    notificationStore.addNotification({
      type: 'error',
      title: '網路錯誤',
      message: '無法連接到伺服器，請檢查網路連接',
      priority: 'high',
      icon: 'ExclamationCircleIcon'
    })
  }
  
  // Listen for global errors
  if (process.client) {
    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', (event) => {
      const error = event.reason
      
      if (error?.status === 401) {
        handleAuthError(error)
      } else if (error?.status) {
        handleApiError(error)
      } else if (error?.message?.includes('fetch')) {
        handleNetworkError(error)
      }
    })
    
    // Handle global errors
    window.addEventListener('error', (event) => {
      console.error('[Global Error]', event.error)
    })
  }
  
  // Provide error handlers
  return {
    provide: {
      handleAuthError,
      handleApiError,
      handleNetworkError
    }
  }
})