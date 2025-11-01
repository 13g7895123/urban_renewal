// Nuxt auto-imports defineAppConfig
// @ts-expect-error - defineAppConfig is auto-imported by Nuxt
export default defineAppConfig({
  ui: {
    modal: {
      overlay: {
        background: 'bg-gray-900'  // 移除透明度，使用純色背景
      }
    }
  }
})
