export const useThemeStore = defineStore('theme', () => {
  const websiteSettingsStore = useWebsiteSettingsStore()
  const { primaryColor } = storeToRefs(websiteSettingsStore)
  
  // Generate color variations from primary color
  const generateColorVariations = (baseColor) => {
    // Simple color manipulation - in production, you might want to use a proper color library
    const hex = baseColor.replace('#', '')
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)
    
    // Generate lighter and darker variations
    const lighten = (amount) => {
      const newR = Math.min(255, Math.floor(r + (255 - r) * amount))
      const newG = Math.min(255, Math.floor(g + (255 - g) * amount))
      const newB = Math.min(255, Math.floor(b + (255 - b) * amount))
      return `#${newR.toString(16).padStart(2, '0')}${newG.toString(16).padStart(2, '0')}${newB.toString(16).padStart(2, '0')}`
    }
    
    const darken = (amount) => {
      const newR = Math.max(0, Math.floor(r * (1 - amount)))
      const newG = Math.max(0, Math.floor(g * (1 - amount)))
      const newB = Math.max(0, Math.floor(b * (1 - amount)))
      return `#${newR.toString(16).padStart(2, '0')}${newG.toString(16).padStart(2, '0')}${newB.toString(16).padStart(2, '0')}`
    }
    
    return {
      50: lighten(0.95),
      100: lighten(0.9),
      200: lighten(0.75),
      300: lighten(0.6),
      400: lighten(0.3),
      500: baseColor,
      600: darken(0.15),
      700: darken(0.3),
      800: darken(0.45),
      900: darken(0.6)
    }
  }
  
  const setPrimaryColor = (color) => {
    // Update through website settings store to sync with API
    websiteSettingsStore.primaryColor = color
    
    // Let websiteSettingsStore handle all the DOM manipulation and saving
    websiteSettingsStore.saveSettings()
  }
  
  // Initialize primary color on client side
  const initializePrimaryColor = () => {
    if (process.client) {
      // Initialize primary color from website settings
      // The websiteSettingsStore will load from API
      setPrimaryColor(primaryColor.value || '#6366f1')
    }
  }
  
  return {
    primaryColor,
    setPrimaryColor,
    initializePrimaryColor
  }
})