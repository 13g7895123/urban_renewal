export const useAuthStore = defineStore('auth', () => {
  // 用戶狀態
  const user = ref(null)
  const token = ref(null)
  const refreshToken = ref(null)
  const tokenExpiresAt = ref(null)
  const isLoggedIn = computed(() => !!user.value && !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isCompanyManager = computed(() => {
    // 後端返回的可能是字串 "1" 或數字 1，都要處理
    const value = user.value?.is_company_manager
    return value === 1 || value === '1' || value === true
  })
  const userType = computed(() => user.value?.user_type || 'general')
  const userId = computed(() => user.value?.id)
  const companyId = computed(() => user.value?.company_id)
  const isLoading = ref(false)

  /**
   * 解碼 JWT Token
   * @param {string} token - JWT token
   * @returns {object|null} 解碼後的 payload
   */
  const decodeToken = (token) => {
    if (!token) return null

    try {
      const parts = token.split('.')
      if (parts.length !== 3) return null

      const payload = parts[1]
      const decoded = JSON.parse(atob(payload.replace(/-/g, '+').replace(/_/g, '/')))
      return decoded
    } catch (error) {
      console.error('Failed to decode token:', error)
      return null
    }
  }

  /**
   * 檢查 token 是否即將過期（在過期前 5 分鐘）
   * @returns {boolean}
   */
  const isTokenExpiringSoon = () => {
    if (!tokenExpiresAt.value) return false

    const now = Date.now()
    const expiresAt = new Date(tokenExpiresAt.value).getTime()
    const fiveMinutes = 5 * 60 * 1000 // 5 分鐘

    // 如果 token 在 5 分鐘內過期，返回 true
    return (expiresAt - now) <= fiveMinutes
  }

  /**
   * 檢查 token 是否已過期
   * @returns {boolean}
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
      
      // 準備登入數據 - 後端期望 'login' 字段而非 'username'
      const loginData = {
        username: credentials.username,
        password: credentials.password
      }
      
      // 使用API composable進行登入
      const { post } = useApi()
      const response = await post('/auth/login', loginData)
      
      console.log('[Auth Store] API response:', response)
      
      if (!response.success) {
        throw new Error(response.error?.message || '登入失敗')
      }
      
      // 後端回傳格式: { success: true, data: { user, token, refresh_token, expires_in } }
      // useApi 包裝後: { success: true, data: <後端response>, error: null }
      const backendData = response.data.data || response.data
      const { user: userData, token: userToken, refresh_token, expires_in } = backendData
      
      console.log('[Auth Store] Extracted data:', { userData, userToken, refresh_token, expires_in })

      // 設定用戶資料和token
      user.value = userData
      token.value = userToken
      refreshToken.value = refresh_token

      // 計算 token 過期時間 - 添加錯誤處理
      if (expires_in) {
        const expiresAt = new Date(Date.now() + (expires_in * 1000))
        tokenExpiresAt.value = expiresAt.toISOString()
      } else {
        // 如果沒有 expires_in，預設設定為 24 小時
        const expiresAt = new Date(Date.now() + (24 * 60 * 60 * 1000))
        tokenExpiresAt.value = expiresAt.toISOString()
      }

      console.log('[Auth Store] Login successful:', {
        hasUser: !!user.value,
        hasToken: !!token.value,
        isLoggedIn: isLoggedIn.value,
        userData
      })
      
      // 驗證 sessionStorage 是否成功保存
      if (process.client) {
        setTimeout(() => {
          const stored = sessionStorage.getItem('auth')
          console.log('[Auth Store] SessionStorage after login:', stored ? 'Data saved' : 'No data')
          if (stored) {
            const parsed = JSON.parse(stored)
            console.log('[Auth Store] Stored token:', parsed.token ? 'Token exists' : 'No token')
          }
        }, 100)
      }

      return { success: true, user: userData, token: userToken }
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
      // 如果有token且不跳過API調用，則調用後端登出
      if (token.value && !skipApiCall) {
        const { post } = useApi()
        await post('/auth/logout')
      }
    } catch (error) {
      console.error('Logout API error:', error)
      // 即使API調用失敗，仍然清除本地狀態
    } finally {
      // 清除所有認證狀態（Pinia 持久化插件會自動清除 sessionStorage）
      user.value = null
      token.value = null
      refreshToken.value = null
      tokenExpiresAt.value = null

      // 重定向到登入頁面
      await navigateTo('/login')
    }
  }

  // 獲取當前用戶信息
  const fetchUser = async () => {
    try {
      if (!token.value) return null

      const { get } = useApi()
      const response = await get('/auth/me')

      if (!response.success) {
        throw new Error('Failed to fetch user data')
      }

      const userData = response.data
      user.value = userData

      return userData
    } catch (error) {
      console.error('Fetch user error:', error)
      // 如果獲取用戶資料失敗，清除認證狀態
      await logout(true)
      return null
    }
  }
  
  // 初始化用戶狀態（使用 Pinia 持久化插件後不再需要手動處理）
  const initializeAuth = async () => {
    // Pinia 持久化插件會自動從 sessionStorage 恢復狀態
    // 這個方法保留是為了向後相容，但不需要做任何事情
    console.log('[Auth Store] Auth initialized from sessionStorage')
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
  
  // 刷新token
  const refreshAuthToken = async () => {
    try {
      if (!refreshToken.value) {
        throw new Error('No refresh token available')
      }

      const { post } = useApi()
      const response = await post('/auth/refresh', {
        refresh_token: refreshToken.value
      })

      if (!response.success) {
        throw new Error('Token refresh failed')
      }

      // 與登入邏輯一致，從 response.data.data 或 response.data 取得資料
      const backendData = response.data.data || response.data
      const { token: newToken, refresh_token: newRefreshToken, expires_in } = backendData
      
      token.value = newToken
      refreshToken.value = newRefreshToken

      // 計算新的 token 過期時間 - 添加錯誤處理
      if (expires_in) {
        const expiresAt = new Date(Date.now() + (expires_in * 1000))
        tokenExpiresAt.value = expiresAt.toISOString()
      } else {
        // 如果沒有 expires_in，預設設定為 24 小時
        const expiresAt = new Date(Date.now() + (24 * 60 * 60 * 1000))
        tokenExpiresAt.value = expiresAt.toISOString()
      }

      return newToken
    } catch (error) {
      console.error('Refresh token error:', error)
      await logout(true)
      throw error
    }
  }

  // 確保返回響應式引用
  return {
    // 狀態 - 直接返回 ref
    user,
    token,
    refreshToken,
    tokenExpiresAt,
    isLoggedIn,
    isAdmin,
    isCompanyManager,
    userType,
    userId,
    companyId,
    isLoading,

    // Token 輔助函數
    decodeToken,
    isTokenExpiringSoon,
    isTokenExpired,

    // 認證方法
    login,
    logout,
    initializeAuth,
    fetchUser,

    // 用戶管理
    updateProfile,
    changePassword,
    refreshAuthToken
  }
}, {
  persist: {
    // 使用 nuxt.config.ts 中 piniaPersistedstate 的全局 storage 配置 (sessionStorage)
    paths: ['user', 'token', 'refreshToken', 'tokenExpiresAt']
  }
})