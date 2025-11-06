// Pinia persistence is now handled by @pinia-plugin-persistedstate/nuxt
// This plugin is kept only for backward compatibility and migration
export default defineNuxtPlugin(() => {
  // Official plugin handles persistence automatically
  // No manual intervention needed
  console.log('[Pinia Persist] Using official @pinia-plugin-persistedstate/nuxt')
})