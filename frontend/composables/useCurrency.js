/**
 * Currency formatting utilities
 */

/**
 * Format number as Taiwan Dollar with thousand separators
 * @param {number|string} amount - The amount to format
 * @param {Object} options - Formatting options
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, options = {}) {
  const {
    currency = 'NT$',
    minimumFractionDigits = 0,
    maximumFractionDigits = 0,
    showZero = true
  } = options

  // Handle null, undefined, or empty values
  if (amount === null || amount === undefined || amount === '') {
    return showZero ? `${currency}0` : `${currency}--`
  }

  // Convert to number
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : Number(amount)
  
  // Handle NaN
  if (isNaN(numericAmount)) {
    return showZero ? `${currency}0` : `${currency}--`
  }

  // Format with thousand separators using toLocaleString
  const formatted = numericAmount.toLocaleString('en-US', {
    minimumFractionDigits,
    maximumFractionDigits
  })

  return `${currency}${formatted}`
}

/**
 * Format number as Taiwan Dollar with thousand separators (composable version)
 * @returns {Object} Object containing formatting functions
 */
export const useCurrency = () => {
  /**
   * Format currency with default NT$ symbol
   */
  const formatTWD = (amount) => {
    return formatCurrency(amount, { currency: 'NT$' })
  }

  /**
   * Format currency with decimals
   */
  const formatTWDWithDecimals = (amount) => {
    return formatCurrency(amount, { 
      currency: 'NT$', 
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }

  /**
   * Format currency without symbol (just the number with separators)
   */
  const formatNumber = (amount) => {
    return formatCurrency(amount, { currency: '' })
  }

  /**
   * Format currency for display in charts
   */
  const formatChartCurrency = (amount) => {
    return formatCurrency(amount, { currency: 'NT$' })
  }

  return {
    formatTWD,
    formatTWDWithDecimals,
    formatNumber,
    formatChartCurrency,
    formatCurrency
  }
}