export default defineNuxtPlugin(() => {
  // This plugin only runs on client side due to .client.js suffix
  const { initializeTheme } = useTheme()
  
  // Initialize theme system with error handling
  try {
    initializeTheme()
  } catch (error) {
    console.warn('Theme initialization failed:', error)
  }
})