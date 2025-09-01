export default defineNuxtPlugin(() => {
  // Force HTML element to have light class instead of dark
  if (process.client) {
    // Disable any color-mode scripts
    window.__NUXT_COLOR_MODE__ = {
      preference: 'light',
      value: 'light',
      getColorScheme: () => 'light',
      addColorScheme: () => {},
      removeColorScheme: () => {}
    }
    
    // Set immediately and aggressively
    const forceLight = () => {
      document.documentElement.classList.remove('dark')
      document.documentElement.classList.add('light')
      document.documentElement.removeAttribute('data-color-mode-forced')
      document.documentElement.setAttribute('data-theme', 'light')
    }
    
    // Force light mode immediately
    forceLight()
    
    // Override any existing color-mode functions
    const originalAddEventListener = document.addEventListener
    document.addEventListener = function(type, listener, ...args) {
      // Block color-mode related event listeners
      if (type === 'DOMContentLoaded' && listener.toString().includes('color-mode')) {
        console.log('[Force Light Mode] Blocked color-mode DOMContentLoaded listener')
        return
      }
      return originalAddEventListener.call(this, type, listener, ...args)
    }
    
    // Ensure it stays light even if something tries to change it
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          const htmlElement = document.documentElement
          if (htmlElement.classList.contains('dark')) {
            htmlElement.classList.remove('dark')
            htmlElement.classList.add('light')
            console.log('[Force Light Mode] Prevented dark mode, forcing light mode')
          } else if (!htmlElement.classList.contains('light')) {
            htmlElement.classList.add('light')
            console.log('[Force Light Mode] Added light class')
          }
        }
      })
    })
    
    // Start observing changes to the HTML element's class attribute
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class', 'data-color-mode-forced', 'data-theme']
    })
    
    // Set a timer to force light mode every 100ms for the first 2 seconds
    let forceCount = 0
    const forceInterval = setInterval(() => {
      forceLight()
      forceCount++
      if (forceCount >= 20) {
        clearInterval(forceInterval)
      }
    }, 100)
    
    console.log('[Force Light Mode] Plugin initialized - HTML forced to light mode aggressively')
  }
})