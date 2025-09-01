export const usePageTitle = (title) => {
  const route = useRoute()
  
  // Set page title directly without store dependency
  const setPageTitle = (newTitle) => {
    if (process.client) {
      const baseTitle = '都更計票系統'
      document.title = newTitle ? `${newTitle} - ${baseTitle}` : baseTitle
    }
  }
  
  // Auto-update title when route changes
  const updateTitleFromRoute = () => {
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
    
    const currentPageTitle = pageTitles[route.path]
    if (currentPageTitle) {
      setPageTitle(currentPageTitle)
    }
  }
  
  // Set initial title if provided
  if (title) {
    setPageTitle(title)
  } else {
    updateTitleFromRoute()
  }
  
  // Watch for route changes
  watch(() => route.path, () => {
    if (!title) { // Only auto-update if no specific title was set
      updateTitleFromRoute()
    }
  })
  
  return {
    setPageTitle
  }
}