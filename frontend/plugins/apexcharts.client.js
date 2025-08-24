/**
 * ApexCharts plugin for client-side only
 */
export default defineNuxtPlugin(async (nuxtApp) => {
  // Only run on client side
  if (process.client) {
    try {
      console.log('Loading ApexCharts plugin...')
      
      // Dynamic import ApexCharts components
      const { default: VueApexCharts } = await import('vue3-apexcharts')
      
      // Register the component globally
      nuxtApp.vueApp.component('apexchart', VueApexCharts)
      
      console.log('ApexCharts loaded and registered successfully')
      
      // Make ApexCharts globally available
      return {
        provide: {
          apexCharts: VueApexCharts
        }
      }
    } catch (error) {
      console.error('Critical error in ApexCharts plugin:', error)
      
      return {
        provide: {
          apexCharts: null
        }
      }
    }
  }
  
  // Server-side: provide null
  return {
    provide: {
      apexCharts: null
    }
  }
})