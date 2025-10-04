/**
 * Alert composable for showing consistent alerts across the application
 */
export const useAlert = () => {
  const { $swal } = useNuxtApp()

  /**
   * Show success alert (auto-closes after 1.5 seconds, no buttons)
   */
  const success = (title, text = '') => {
    return $swal.fire({
      title,
      text,
      icon: 'success',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  }

  /**
   * Show error alert (auto-closes after 1.5 seconds, no buttons)
   */
  const error = (title, text = '') => {
    return $swal.fire({
      title,
      text,
      icon: 'error',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  }

  /**
   * Show warning alert (auto-closes after 1.5 seconds, no buttons)
   */
  const warning = (title, text = '') => {
    return $swal.fire({
      title,
      text,
      icon: 'warning',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  }

  /**
   * Show info alert (auto-closes after 1.5 seconds, no buttons)
   */
  const info = (title, text = '') => {
    return $swal.fire({
      title,
      text,
      icon: 'info',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true
    })
  }

  /**
   * Show confirmation dialog (with buttons, does not auto-close)
   */
  const confirm = (title, text = '', options = {}) => {
    return $swal.fire({
      title,
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: '確定',
      cancelButtonText: '取消',
      ...options
    })
  }

  return {
    success,
    error,
    warning,
    info,
    confirm
  }
}
