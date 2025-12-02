/**
 * Plugin to clear invalid authentication data and migrate from old localStorage
 * This runs once when the app initializes on the client side
 */
export default defineNuxtPlugin(() => {
  if (process.client) {
    // 1. 清理舊的 localStorage 數據（從舊版本遷移）
    const oldLocalStorageKeys = [
      'auth_user',
      'auth_token',
      'auth_refresh_token',
      'auth_token_expires_at'
    ]

    oldLocalStorageKeys.forEach(key => {
      const value = localStorage.getItem(key)
      if (value) {
        console.log(`[Auth Cleanup] Removing old localStorage key: ${key}`)
        localStorage.removeItem(key)
      }
    })

    // 2. 驗證 storage 中的 Pinia 持久化數據
    const checkAndCleanStorage = (storage, name) => {
      const persistedAuth = storage.getItem('auth')
      if (persistedAuth) {
        try {
          const authData = JSON.parse(persistedAuth)

          // 檢查是否有無效的值
          if (authData.token === 'undefined' ||
              authData.token === 'null' ||
              authData.user === 'undefined' ||
              authData.user === 'null') {
            console.log(`[Auth Cleanup] Removing invalid auth data from ${name}`)
            storage.removeItem('auth')
          }
        } catch (e) {
          console.log(`[Auth Cleanup] Removing corrupted auth data from ${name}`)
          storage.removeItem('auth')
        }
      }
    }

    checkAndCleanStorage(localStorage, 'localStorage')
    checkAndCleanStorage(sessionStorage, 'sessionStorage')
  }
})