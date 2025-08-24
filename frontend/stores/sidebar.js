export const useSidebarStore = defineStore('sidebar', () => {
  const collapsed = ref(false)
  const mobileOpen = ref(false)
  const transitioning = ref(false)
  
  const toggleSidebar = async () => {
    if (transitioning.value) return
    
    transitioning.value = true
    collapsed.value = !collapsed.value
    
    // Reset transitioning state after animation completes
    setTimeout(() => {
      transitioning.value = false
    }, 300) // Match CSS transition duration
  }
  
  const toggleMobileSidebar = () => {
    mobileOpen.value = !mobileOpen.value
  }
  
  const closeMobileSidebar = () => {
    mobileOpen.value = false
  }
  
  const setSidebarState = (isCollapsed) => {
    if (collapsed.value !== isCollapsed) {
      toggleSidebar()
    }
  }
  
  return {
    sidebarCollapsed: collapsed,
    sidebarMobileOpen: mobileOpen,
    sidebarTransitioning: transitioning,
    toggleSidebar,
    toggleMobileSidebar,
    closeMobileSidebar,
    setSidebarState
  }
})