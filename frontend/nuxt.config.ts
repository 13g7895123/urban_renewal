export default defineNuxtConfig({
  compatibilityDate: '2025-10-06',
  devtools: { enabled: true },
  // Development server configuration
  devServer: {
    port: parseInt(process.env.FRONTEND_PORT || '4001'),
    host: 'localhost'
  },
  ssr: false, // 改為 SPA 模式
  modules: [
    '@nuxt/ui',
    '@pinia/nuxt',
    '@pinia-plugin-persistedstate/nuxt'
  ],
  piniaPersistedstate: {
    storage: 'sessionStorage'
  },
  ui: {
    global: true,
    icons: ['heroicons'],
    safelistColors: ['primary']
  },
  colorMode: {
    preference: 'light',
    fallback: 'light',
    classSuffix: ''
  },
  tailwindcss: {
    viewer: false,
    quiet: true
  },
  plugins: [
    '~/plugins/clear-invalid-auth.client.js', // 先清理無效數據
    '~/plugins/pinia-persist.client.js',       // 手動處理 Pinia 持久化
    '~/plugins/auth.client.js',                // 再初始化認證
    '~/plugins/token-refresh.client.js',        // 設定 token 自動刷新
    '~/plugins/apexcharts.client.js',
    '~/plugins/force-light-mode.client.js',
    '~/plugins/sweetalert.client.js'
  ],
  // Transpile ApexCharts for better compatibility
  build: {
    transpile: ['vue3-apexcharts']
  },
  css: ['~/assets/css/main.css', '~/assets/css/force-light.css'],
  runtimeConfig: {
    // Private keys (only available on server-side)
    // Public keys (exposed to client-side)
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || process.env.BACKEND_API_URL,
      backendUrl: process.env.NUXT_PUBLIC_BACKEND_URL || process.env.BACKEND_URL || process.env.BACKEND_API_URL,
      backendHost: process.env.NUXT_PUBLIC_BACKEND_HOST || process.env.BACKEND_HOST,
      backendPort: process.env.NUXT_PUBLIC_BACKEND_PORT || process.env.BACKEND_PORT
    }
  },
  nitro: {
    devProxy: {
      '/api': {
        target: `http://localhost:${process.env.BACKEND_PORT || 9228}`,
        changeOrigin: true,
        prependPath: true,
      }
    }
  },
  // Color mode configuration is handled by ui config above
  // Build optimization - ensure apexcharts can be imported correctly
  vite: {
    optimizeDeps: {
      include: ['apexcharts', 'vue3-apexcharts'],
      exclude: []
    },
    server: {
      allowedHosts: [
        'project.local'
      ],
    },
    build: {
      rollupOptions: {
        external: [],
        output: {
          globals: {}
        }
      },
      cssCodeSplit: false
    },
    define: {
      'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development')
    }
  },
  // Bypass Tailwind config validation
  hooks: {
    'build:before': () => {
      process.env.TAILWIND_DISABLE_TOUCH = '1'
    }
  },
  // Configure icon server bundle to fix 404 API errors
  icon: {
    serverBundle: {
      collections: ['heroicons']
    }
  }
})