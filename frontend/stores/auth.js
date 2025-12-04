export const useAuthStore = defineStore('auth', () => {
  // 用戶狀態 (token 不再存前端，由 httpOnly cookie 管理)
  const user = ref(null)
  const tokenExpiresAt = ref(null)
  const isLoggedIn = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isCompanyManager = computed(() => {
    const value = user.value?.is_company_manager
    return value === 1 || value === '1' || value === true
  })
  const userType = computed(() => user.value?.user_type || 'general')
  const userId = computed(() => user.value?.id)
  const companyId = computed(() => user.value?.company_id)
  const isLoading = ref(false)

  /**
   * 檢查 token 是否即將過期（在過期前 5 分鐘）
   */
  const isTokenExpiringSoon = () => {
    if (!tokenExpiresAt.value) return false
    const now = Date.now()
    const expiresAt = new Date(tokenExpiresAt.value).getTime()
    const fiveMinutes = 5 * 60 * 1000
    return (expiresAt - now) <= fiveMinutes
  }

  /**
   * 檢查 token 是否已過期
   */
  const isTokenExpired = () => {
    if (!tokenExpiresAt.value) return true
    const now = Date.now()
    const expiresAt = new Date(tokenExpiresAt.value).getTime()
    return now >= expiresAt
  }

  // 登入功能
  const login = async (credentials) => {
    try {
      isLoading.value = true
      
      const loginData = {
        username: credentials.username,
        password: credentials.password
      }
      
      const { post } = useApi()
      const response = await post('/auth/login', loginData)
      
      console.log('[Auth Store] API response:', response)
      
      if (!response.success) {
        throw new Error(response.error?.message || '登入失敗')
      }
      
      // 後端現在只回傳 user 和 expires_in，token 存在 httpOnly cookie
      const backendData = response.data.data || response.data
      const { user: userData, expires_in } = backendData
      
      console.log('[Auth Store] Extracted data:', { userData, expires_in })

      // 設定用戶資料
      user.value = userData

      // 計算 token 過期時間
      if (expires_in) {
        const expiresAt = new Date(Date.now() + (expires_in * 1000))
        tokenExpiresAt.value = expiresAt.toISOString()
      } else {
        const expiresAt = new Date(Date.now() + (24 * 60 * 60 * 1000))
        tokenExpiresAt.value = expiresAt.toISOString()
      }

      console.log('[Auth Store] Login successful:', {
        hasUser: !!user.value,
        isLoggedIn: isLoggedIn.value,
        userData
      })

      return { success: true, user: userData }
    } catch (error) {
      console.error('Login error:', error)
      throw new Error(error.message || '登入失敗')
    } finally {
      isLoading.value = false
    }
  }

  // 登出功能
  const logout = async (skipApiCall = false) => {
    try {
      if (!skipApiCall) {
        const { post } = useApi()
        await post('/auth/logout')
      }
    } catch (error) {
      console.error('Logout API error:', error)
    } finally {
      clearLocalState()
      await navigateTo('/login')
    }
  }

  // 清除本地狀態 (不呼叫 API)
  const clearLocalState = () => {
    user.value = null
    tokenExpiresAt.value = null
  }

  // 獲取當前用戶信息
  const fetchUser = async () => {
    try {
      const { get } = useApi()
      const response = await get('/auth/me')

      if (!response.success) {
        throw new Error('Failed to fetch user data')
      }

      const userData = response.data.data || response.data
      user.value = userData

      return userData
    } catch (error) {
      console.error('Fetch user error:', error)
      clearLocalState()
      return null
    }
  }
  
  // 初始化用戶狀態
  const initializeAuth = async () => {
    // 嘗試從 cookie 驗證並取得用戶資料
    if (process.client) {
      try {
        await fetchUser()
        console.log('[Auth Store] Auth initialized from cookie')
      } catch (error) {
        console.log('[Auth Store] No valid auth cookie found')
      }
    }
  }

  // 更新用戶資料
  const updateProfile = async (profileData) => {
    try {
      const { put } = useApi()
      const response = await put('/profile', profileData)

      if (!response.success) {
        throw new Error(response.error?.message || '更新失敗')
      }

      const updatedUser = response.data
      user.value = updatedUser

      return { success: true, user: updatedUser }
    } catch (error) {
      console.error('Update profile error:', error)
      throw new Error(error.message || '更新失敗')
    }
  }
  
  // 更改密碼
  const changePassword = async (passwordData) => {
    try {
      const { put } = useApi()
      const response = await put('/auth/change-password', passwordData)
      
      if (!response.success) {
        throw new Error(response.error?.message || '密碼更改失敗')
      }
      
      return { success: true, message: '密碼更改成功' }
    } catch (error) {
      console.error('Change password error:', error)
      throw new Error(error.message || '密碼更改失敗')
    }
  }
  
  // 刷新 token
  const refreshAuthToken = async () => {
    const doRefresh = async () => {
      try {
        const { post } = useApi()
        const response = await post('/auth/refresh', {})

        if (!response.success) {
          throw new Error('Token refresh failed')
        }

        const backendData = response.data.data || response.data
        const { expires_in } = backendData

        if (expires_in) {
          const expiresAt = new Date(Date.now() + (expires_in * 1000))
          tokenExpiresAt.value = expiresAt.toISOString()
        }

        return true
      } catch (error) {
        console.error('Refresh token error:', error)
        await logout(true)
        throw error
      }
    }

    // 使用 Web Locks API 避免多個分頁同時刷新導致 Race Condition
    if (typeof navigator !== 'undefined' && navigator.locks) {
      return navigator.locks.request('auth_refresh_token', async () => {
        return await doRefresh()
      })
    }

    return await doRefresh()
  }

  return {
    // 狀態
    user,
    tokenExpiresAt,
    isLoggedIn,
    isAdmin,
    isCompanyManager,
    userType,
    userId,
    companyId,
    isLoading,

    // Token 輔助函數
    isTokenExpiringSoon,
    isTokenExpired,

    // 認證方法
    login,
    logout,
    clearLocalState,
    initializeAuth,
    fetchUser,

    // 用戶管理
    updateProfile,
    changePassword,
    refreshAuthToken
  }
}, {
  persist: {
    paths: ['user', 'tokenExpiresAt']
  }
})