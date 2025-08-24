export default defineNuxtPlugin(async () => {
  // Initialize website settings on client side
  const websiteSettingsStore = useWebsiteSettingsStore()
  const colorMode = useColorMode()
  
  // Load settings from API/localStorage with await
  await websiteSettingsStore.loadSettings()
  
  // Sync Nuxt color mode with website settings
  if (process.client) {
    const savedThemeMode = localStorage.getItem('website-theme-mode')
    if (savedThemeMode) {
      colorMode.preference = savedThemeMode
    } else if (websiteSettingsStore.themeMode) {
      colorMode.preference = websiteSettingsStore.themeMode
      // Save to Nuxt storage
      localStorage.setItem('website-theme-mode', websiteSettingsStore.themeMode)
    }
    
    // Apply theme settings immediately after loading
    websiteSettingsStore.applyThemeSettings()
    
    // Watch for color mode changes and sync with website settings
    watch(() => colorMode.preference, (newMode) => {
      if (newMode !== websiteSettingsStore.themeMode) {
        websiteSettingsStore.themeMode = newMode
        localStorage.setItem('website-theme-mode', newMode)
        websiteSettingsStore.saveSettings()
        websiteSettingsStore.applyThemeSettings()
      }
    }, { immediate: false })
  }
  
  // Set up page title management
  const router = useRouter()
  
  // Listen for settings changes and update document properties
  if (process.client) {
    window.addEventListener('website-settings-changed', (event) => {
      const settings = event.detail
      
      // Update favicon
      let favicon = document.querySelector('link[rel="icon"]')
      if (!favicon) {
        favicon = document.createElement('link')
        favicon.rel = 'icon'
        document.head.appendChild(favicon)
      }
      favicon.href = settings.faviconUrl
      
      // Force title update based on current route
      nextTick(async () => {
        const { usePageTitle } = await import('~/composables/usePageTitle')
        usePageTitle()
      })
    })
    
    // Handle route-based title updates
    router.afterEach(() => {
      nextTick(() => {
        const pageTitles = {
          '/': '儀表板',
          '/dashboard': '儀表板',
          '/profile': '個人檔案',
          '/settings': '設定',
          '/settings/theme': '主題設定',
          '/settings/website': '網站設定',
          '/settings/ui': '介面設定',
          '/settings/users': '用戶管理',
          '/clients': '業主管理',
          '/clients/create': '新增業主',
          '/projects': '專案管理',
          '/projects/create': '新增專案',
          '/help': '幫助中心'
        }
        
        const currentPath = router.currentRoute.value.path
        const pageTitle = pageTitles[currentPath]
        
        if (pageTitle) {
          websiteSettingsStore.updateDocumentTitle(pageTitle)
        } else {
          websiteSettingsStore.updateDocumentTitle()
        }
      })
    })
  }
})