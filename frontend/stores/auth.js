export const useAuthStore = defineStore('auth', () => {
  // 用戶狀態
  const user = ref(null)
  const token = ref(null)
  const isLoggedIn = computed(() => !!user.value && !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isLoading = ref(false)
  
  // Token storage keys
  const TOKEN_KEY = 'auth_token'
  const USER_KEY = 'auth_user'

  // 登入功能
  const login = async (credentials) => {
    try {
      isLoading.value = true
      
      // 準備登入數據 - 後端期望 'login' 字段而非 'username'
      const loginData = {
        login: credentials.username,
        password: credentials.password
      }
      
      // 使用API composable進行登入
      const { post } = useApi()
      const response = await post('/auth/login', loginData)
      
      if (!response.success) {
        throw new Error(response.error?.message || '登入失敗')
      }
      
      const { user: userData, token: userToken } = response.data.data
      
      // 設定用戶資料和token
      user.value = userData
      token.value = userToken
      
      // 儲存到 localStorage
      if (process.client) {
        localStorage.setItem(TOKEN_KEY, userToken)
        localStorage.setItem(USER_KEY, JSON.stringify(userData))
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
      // 清除所有認證狀態
      user.value = null
      token.value = null
      
      // 清除 localStorage
      if (process.client) {
        localStorage.removeItem(TOKEN_KEY)
        localStorage.removeItem(USER_KEY)
      }
      
      // 重定向到登入頁面
      await navigateTo('/auth/login')
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
      
      const userData = response.data.data.user
      user.value = userData
      
      // 更新 localStorage 中的用戶資料
      if (process.client) {
        localStorage.setItem(USER_KEY, JSON.stringify(userData))
      }
      
      return userData
    } catch (error) {
      console.error('Fetch user error:', error)
      // 如果獲取用戶資料失敗，清除認證狀態
      await logout(true)
      return null
    }
  }
  
  // 初始化用戶狀態 (改善SSR處理)
  const initializeAuth = async () => {
    if (process.client) {
      const savedToken = localStorage.getItem(TOKEN_KEY)
      const savedUser = localStorage.getItem(USER_KEY)
      
      if (savedToken && savedUser) {
        try {
          token.value = savedToken
          user.value = JSON.parse(savedUser)
          
          // 不要在初始化時立即驗證token，避免頁面刷新時的重定向問題
          // token驗證會在middleware中按需進行
        } catch (error) {
          console.error('Failed to parse saved auth data:', error)
          // 清除無效的儲存數據
          localStorage.removeItem(TOKEN_KEY)
          localStorage.removeItem(USER_KEY)
          user.value = null
          token.value = null
        }
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
      
      const updatedUser = response.data.data.user
      user.value = updatedUser
      
      // 更新 localStorage
      if (process.client) {
        localStorage.setItem(USER_KEY, JSON.stringify(updatedUser))
      }
      
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
  const refreshToken = async () => {
    try {
      const { post } = useApi()
      const response = await post('/auth/refresh')
      
      if (!response.success) {
        throw new Error('Token refresh failed')
      }
      
      const newToken = response.data.data.token
      token.value = newToken
      
      // 更新 localStorage
      if (process.client) {
        localStorage.setItem(TOKEN_KEY, newToken)
      }
      
      return newToken
    } catch (error) {
      console.error('Refresh token error:', error)
      await logout(true)
      throw error
    }
  }

  return {
    // 狀態
    user: readonly(user),
    token: readonly(token),
    isLoggedIn,
    isAdmin,
    isLoading: readonly(isLoading),
    
    // 認證方法
    login,
    logout,
    initializeAuth,
    fetchUser,
    
    // 用戶管理
    updateProfile,
    changePassword,
    refreshToken
  }
})