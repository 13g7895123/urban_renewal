/**
 * Plugin to clear invalid authentication data from localStorage
 * This runs once when the app initializes on the client side
 */
export default defineNuxtPlugin(() => {
  if (process.client) {
    // Check and clean invalid localStorage data
    const authKeys = [
      'auth_user',
      'auth_token',
      'auth_refresh_token',
      'auth_token_expires_at'
    ]

    authKeys.forEach(key => {
      const value = localStorage.getItem(key)

      // Remove if value is literally 'undefined' or 'null' strings
      if (value === 'undefined' || value === 'null' || value === '') {
        console.log(`[Auth Cleanup] Removing invalid ${key} from localStorage`)
        localStorage.removeItem(key)
      }

      // For auth_user, also check if it's valid JSON
      if (key === 'auth_user' && value) {
        try {
          JSON.parse(value)
        } catch (e) {
          console.log(`[Auth Cleanup] Removing invalid JSON for ${key} from localStorage`)
          localStorage.removeItem(key)
        }
      }
    })
  }
})