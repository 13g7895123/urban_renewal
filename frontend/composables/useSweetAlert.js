import Swal from 'sweetalert2'

export const useSweetAlert = () => {
  // Success notification
  const showSuccess = (title = '成功', text = '操作已成功完成') => {
    return Swal.fire({
      icon: 'success',
      title,
      text,
      timer: 2000,
      timerProgressBar: true,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      customClass: {
        popup: 'colored-toast'
      }
    })
  }

  // Error notification
  const showError = (title = '錯誤', text = '操作失敗，請稍後再試') => {
    return Swal.fire({
      icon: 'error',
      title,
      text,
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      customClass: {
        popup: 'colored-toast'
      }
    })
  }

  // Warning notification
  const showWarning = (title = '警告', text = '請注意此操作') => {
    return Swal.fire({
      icon: 'warning',
      title,
      text,
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      customClass: {
        popup: 'colored-toast'
      }
    })
  }

  // Info notification
  const showInfo = (title = '提示', text = '訊息通知') => {
    return Swal.fire({
      icon: 'info',
      title,
      text,
      timer: 2500,
      timerProgressBar: true,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      customClass: {
        popup: 'colored-toast'
      }
    })
  }

  // Confirmation dialog
  const showConfirm = (
    title = '確認操作',
    text = '您確定要執行此操作嗎？',
    confirmButtonText = '確認',
    cancelButtonText = '取消'
  ) => {
    return Swal.fire({
      icon: 'question',
      title,
      text,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      reverseButtons: true
    })
  }

  // Delete confirmation
  const showDeleteConfirm = (
    title = '確認刪除',
    text = '此操作無法復原！',
    confirmButtonText = '確認刪除',
    cancelButtonText = '取消'
  ) => {
    return Swal.fire({
      icon: 'warning',
      title,
      text,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      reverseButtons: true
    })
  }

  // Loading notification
  const showLoading = (title = '處理中...', text = '請稍候') => {
    return Swal.fire({
      title,
      text,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading()
      }
    })
  }

  // Close any open Swal
  const close = () => {
    Swal.close()
  }

  // Custom alert with more options
  const showCustom = (options) => {
    const defaultOptions = {
      timer: 2000,
      timerProgressBar: true,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      customClass: {
        popup: 'colored-toast'
      }
    }
    
    return Swal.fire({
      ...defaultOptions,
      ...options
    })
  }

  // Form input dialog
  const showInput = (
    title = '輸入',
    inputPlaceholder = '請輸入內容',
    inputType = 'text'
  ) => {
    return Swal.fire({
      title,
      input: inputType,
      inputPlaceholder,
      showCancelButton: true,
      confirmButtonText: '確認',
      cancelButtonText: '取消',
      inputValidator: (value) => {
        if (!value) {
          return '請輸入內容！'
        }
      }
    })
  }

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirm,
    showDeleteConfirm,
    showLoading,
    showCustom,
    showInput,
    close
  }
}