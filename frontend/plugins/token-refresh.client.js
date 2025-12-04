/**
 * Token 自動更新 Plugin
 * 主動排程更新策略：在 token 過期前 5 分鐘自動更新
 */
export default defineNuxtPlugin((nuxtApp) => {
  const authStore = useAuthStore()
  let refreshTimer = null

  /**
   * 排程 token 更新
   * 計算距離 token 過期的時間，並在適當時機觸發更新
   */
  const scheduleTokenRefresh = () => {
    // 清除現有的定時器
    if (refreshTimer) {
      clearTimeout(refreshTimer)
      refreshTimer = null
    }

    // 檢查是否有 tokenExpiresAt
    if (!authStore.tokenExpiresAt) {
      return
    }

    const now = Date.now()
    const expiresAt = new Date(authStore.tokenExpiresAt).getTime()
    const fiveMinutes = 5 * 60 * 1000 // 5 分鐘

    // 計算應該在何時更新 token（過期前 5 分鐘）
    const refreshAt = expiresAt - fiveMinutes

    // 如果已經超過更新時間，立即更新
    if (now >= refreshAt) {
      console.log('[Token Refresh] Token expiring soon, refreshing now...')
      refreshTokenNow()
      return
    }

    // 計算延遲時間（毫秒）
    const delay = refreshAt - now

    console.log(`[Token Refresh] Scheduling refresh in ${Math.floor(delay / 1000)} seconds`)

    // 設定定時器
    refreshTimer = setTimeout(() => {
      refreshTokenNow()
    }, delay)
  }

  /**
   * 立即更新 token
   */
  const refreshTokenNow = async () => {
    try {
      console.log('[Token Refresh] Refreshing token...')
      await authStore.refreshAuthToken()
      console.log('[Token Refresh] Token refreshed successfully')

      // 更新成功後，重新排程下一次更新
      scheduleTokenRefresh()
    } catch (error) {
      console.error('[Token Refresh] Failed to refresh token:', error)
      // 更新失敗，authStore.refreshAuthToken 會自動登出
    }
  }

  /**
   * 監聽 tokenExpiresAt 的變化，重新排程
   */
  watch(
    () => authStore.tokenExpiresAt,
    (newExpiresAt) => {
      if (newExpiresAt) {
        console.log('[Token Refresh] Token expiration updated, rescheduling...')
        scheduleTokenRefresh()
      } else {
        // tokenExpiresAt 被清除（登出），清除定時器
        if (refreshTimer) {
          clearTimeout(refreshTimer)
          refreshTimer = null
        }
      }
    },
    { immediate: true }
  )

  /**
   * 監聽 isLoggedIn 狀態
   * 用戶登入時開始排程，登出時清除定時器
   */
  watch(
    () => authStore.isLoggedIn,
    (isLoggedIn) => {
      if (isLoggedIn) {
        console.log('[Token Refresh] User logged in, starting token refresh scheduler')
        scheduleTokenRefresh()
      } else {
        console.log('[Token Refresh] User logged out, stopping token refresh scheduler')
        if (refreshTimer) {
          clearTimeout(refreshTimer)
          refreshTimer = null
        }
      }
    },
    { immediate: true }
  )

  /**
   * 頁面可見性變化監聽
   * 當頁面重新可見時，檢查 token 是否需要更新
   */
  if (process.client) {
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden && authStore.isLoggedIn) {
        // 頁面重新可見，檢查是否需要更新 token
        if (authStore.isTokenExpiringSoon() || authStore.isTokenExpired()) {
          console.log('[Token Refresh] Page visible and token expired or expiring soon, attempting refresh...')
          refreshTokenNow()
        } else {
          // 重新排程
          scheduleTokenRefresh()
        }
      }
    })
  }

  /**
   * 窗口/標籤頁關閉前清理
   */
  if (process.client) {
    window.addEventListener('beforeunload', () => {
      if (refreshTimer) {
        clearTimeout(refreshTimer)
        refreshTimer = null
      }
    })
  }

  // 提供給應用程式的公開方法
  return {
    provide: {
      tokenRefresh: {
        scheduleRefresh: scheduleTokenRefresh,
        refreshNow: refreshTokenNow
      }
    }
  }
})
