export default defineNuxtPlugin(() => {
  // Simplified force light mode - use Nuxt Color Mode official API
  if (process.client) {
    // Set color mode preference
    window.__NUXT_COLOR_MODE__ = {
      preference: 'light',
      value: 'light',
      getColorScheme: () => 'light',
      addColorScheme: () => {},
      removeColorScheme: () => {}
    }
    
    // Simple enforcement without aggressive overrides
    const forceLight = () => {
      document.documentElement.classList.remove('dark')
      document.documentElement.classList.add('light')
      document.documentElement.setAttribute('data-theme', 'light')
    }
    
    // Apply on initialization
    forceLight()
    
    // Single check after a short delay to ensure it's applied
    setTimeout(forceLight, 100)
    
    console.log('[Force Light Mode] Plugin initialized - using simple light mode enforcement')
  }
})