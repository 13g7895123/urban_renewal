export const useTheme = () => {
  const colorMode = useColorMode()
  const themeStore = useThemeStore()
  const websiteSettingsStore = useWebsiteSettingsStore()

  // Computed properties for reactive theme state
  const isDark = computed(() => colorMode.value === 'dark')
  const isLight = computed(() => colorMode.value === 'light')
  const isSystem = computed(() => colorMode.preference === 'system')

  // Theme toggle function
  const toggleTheme = () => {
    // Check if dark mode is enabled in website settings
    const websiteSettingsStore = useWebsiteSettingsStore()
    if (!websiteSettingsStore.enableDarkMode) {
      return // Don't allow theme toggle if disabled
    }
    
    const newMode = colorMode.value === 'dark' ? 'light' : 'dark'
    setTheme(newMode)
  }

  // Set specific theme
  const setTheme = (mode) => {
    if (!websiteSettingsStore.enableDarkMode && mode === 'dark') {
      return // Don't allow setting dark mode if disabled
    }
    
    // Update Nuxt color mode first
    colorMode.preference = mode
    
    // Update website settings store and save to both API and localStorage
    websiteSettingsStore.themeMode = mode
    
    // Apply theme immediately with proper synchronization
    if (process.client) {
      localStorage.setItem('website-theme-mode', mode)
      
      // Add smooth transition
      document.documentElement.classList.add('theme-transition')
      
      // Apply theme immediately using nextTick for proper timing
      nextTick(() => {
        const html = document.documentElement
        const shouldBeDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
        
        // Remove both classes first to ensure clean state
        html.classList.remove('dark', 'light')
        
        // Apply the correct class
        if (shouldBeDark) {
          html.classList.add('dark')
        } else {
          html.classList.add('light')
        }
        
        // Force style recalculation
        document.body.offsetHeight
        
        // Remove transition class after transition completes
        setTimeout(() => {
          document.documentElement.classList.remove('theme-transition')
          
          // Apply full theme settings including primary color
          websiteSettingsStore.applyThemeSettings()
          
          // Save settings after visual changes are applied
          websiteSettingsStore.saveSettings()
        }, 300)
      })
    } else {
      // Server-side: just save the settings
      websiteSettingsStore.saveSettings()
    }
  }

  // Set primary color with immediate visual feedback
  const setPrimaryColor = (color) => {
    themeStore.setPrimaryColor(color)
  }

  // Initialize theme on first load
  const initializeTheme = () => {
    if (process.client) {
      // Load from multiple sources with priority order:
      // 1. Nuxt color mode storage (most recent user choice)
      // 2. Website settings store
      // 3. Default to system
      const savedThemeMode = localStorage.getItem('website-theme-mode')
      const preferredMode = savedThemeMode || websiteSettingsStore.themeMode || 'system'
      
      // Set the theme mode if different
      if (preferredMode !== colorMode.preference) {
        colorMode.preference = preferredMode
      }
      
      // Update website settings store to match
      if (websiteSettingsStore.themeMode !== preferredMode) {
        websiteSettingsStore.themeMode = preferredMode
      }
      
      // Initialize primary color
      themeStore.initializePrimaryColor()
      
      // Ensure theme class is properly set immediately
      nextTick(() => {
        const html = document.documentElement
        const currentTheme = colorMode.value
        const shouldBeDark = currentTheme === 'dark' || (currentTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
        
        // Remove both classes first
        html.classList.remove('dark', 'light')
        
        // Add appropriate class based on actual computed theme
        if (shouldBeDark) {
          html.classList.add('dark')
        } else {
          html.classList.add('light')
        }
        
        // Apply website settings theme (this also handles primary color)
        websiteSettingsStore.applyThemeSettings()
        
        // Force style recalculation to ensure all styles are applied
        document.body.offsetHeight
      })
    }
  }

  // Watch for system theme changes
  if (process.client) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addListener(() => {
      if (colorMode.preference === 'system') {
        // Force a re-evaluation
        nextTick(() => {
          document.body.offsetHeight
        })
      }
    })
  }

  return {
    // State
    isDark,
    isLight,
    isSystem,
    colorMode,
    primaryColor: computed(() => themeStore.primaryColor),
    
    // Methods
    toggleTheme,
    setTheme,
    setPrimaryColor,
    initializeTheme
  }
}