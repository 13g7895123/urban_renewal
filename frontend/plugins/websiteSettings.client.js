export default defineNuxtPlugin(async () => {
  // Website settings plugin disabled since color-mode is disabled
  // and websiteSettingsStore doesn't exist
  console.log('[Website Settings Plugin] Disabled to prevent Nuxt instance errors')
  
  // Basic page title management without dependencies
  if (process.client) {
    const pageTitles = {
      '/': '都更計票系統首頁',
      '/login': '登入 - 都更計票系統',
      '/signup': '註冊 - 都更計票系統',
      '/tables/urban-renewal': '更新會管理 - 都更計票系統',
      '/tables/meeting': '會議管理 - 都更計票系統',
      '/tables/issue': '投票管理 - 都更計票系統',
      '/pages/user': '使用者基本資料變更 - 都更計票系統'
    }
    
    // Simple title update function
    const updateTitle = () => {
      const currentPath = window.location.pathname
      const pageTitle = pageTitles[currentPath] || '都更計票系統'
      document.title = pageTitle
    }
    
    // Update title on load and navigation
    updateTitle()
    window.addEventListener('popstate', updateTitle)
  }
})