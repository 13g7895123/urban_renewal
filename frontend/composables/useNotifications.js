/**
 * Notifications composable
 * Provides easy-to-use notification functionality with toast-like interface
 */
export const useNotifications = () => {
  const notificationsStore = useNotificationsStore()

  /**
   * Show a notification with different types
   * @param {string} message - The message to display
   * @param {string} type - Type of notification: 'success', 'error', 'warning', 'info'
   * @param {object} options - Additional options
   */
  const showNotification = (message, type = 'info', options = {}) => {
    const notification = {
      title: options.title || getDefaultTitle(type),
      message,
      type: mapTypeToNotificationType(type),
      priority: options.priority || getDefaultPriority(type),
      icon: options.icon || getDefaultIcon(type),
      autoRemove: options.autoRemove !== false,
      ...options
    }

    notificationsStore.addNotification(notification)

    // For console debugging
    if (process.dev) {
      console.log(`[Notification ${type}]:`, message)
    }
  }

  /**
   * Show success notification
   */
  const showSuccess = (message, options = {}) => {
    showNotification(message, 'success', options)
  }

  /**
   * Show error notification
   */
  const showError = (message, options = {}) => {
    showNotification(message, 'error', options)
  }

  /**
   * Show warning notification
   */
  const showWarning = (message, options = {}) => {
    showNotification(message, 'warning', options)
  }

  /**
   * Show info notification
   */
  const showInfo = (message, options = {}) => {
    showNotification(message, 'info', options)
  }

  // Helper functions
  const getDefaultTitle = (type) => {
    const titles = {
      success: '成功',
      error: '錯誤',
      warning: '警告',
      info: '資訊'
    }
    return titles[type] || '通知'
  }

  const mapTypeToNotificationType = (type) => {
    const typeMap = {
      success: 'system',
      error: 'security',
      warning: 'system',
      info: 'report'
    }
    return typeMap[type] || 'report'
  }

  const getDefaultPriority = (type) => {
    const priorities = {
      success: 'medium',
      error: 'high',
      warning: 'medium',
      info: 'low'
    }
    return priorities[type] || 'medium'
  }

  const getDefaultIcon = (type) => {
    const icons = {
      success: 'CheckCircleIcon',
      error: 'ExclamationCircleIcon',
      warning: 'ExclamationTriangleIcon',
      info: 'InformationCircleIcon'
    }
    return icons[type] || 'InformationCircleIcon'
  }

  return {
    showNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    // Expose store methods
    ...notificationsStore
  }
}