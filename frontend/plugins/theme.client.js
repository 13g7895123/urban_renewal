export default defineNuxtPlugin(() => {
  // Theme plugin disabled since color-mode is disabled
  // This prevents "Nuxt instance is unavailable!" errors
  console.log('[Theme Plugin] Color-mode disabled, skipping theme initialization')
})