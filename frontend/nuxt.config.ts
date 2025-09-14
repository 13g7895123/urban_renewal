export default defineNuxtConfig({
  devtools: { enabled: false },
  // Development server configuration
  devServer: {
    port: 3303,
    host: '0.0.0.0'
  },
  ssr: true,
  modules: [
    '@nuxt/ui',
    '@pinia/nuxt'
  ],
  plugins: [
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
      apiBaseUrl: process.env.BACKEND_API_URL || 'http://localhost:9228',
      backendUrl: process.env.BACKEND_URL || process.env.BACKEND_API_URL || 'http://localhost:9228',
      backendHost: process.env.BACKEND_HOST || 'localhost',
      backendPort: process.env.BACKEND_PORT || '9228'
    }
  },
  nitro: {
    // Only use proxy in development mode
    ...(process.env.NODE_ENV !== 'production' && {
      devProxy: {
        '/api': {
          target: 'http://localhost:9228',
          changeOrigin: true,
          prependPath: true,
        }
      }
    })
  },
  // Disable color-mode completely to prevent HTML class manipulation
  colorMode: false,
  ui: {
    colorMode: false
  },
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
      }
    }
  },
  // Disable problematic nuxt-icon server bundle
  icon: {
    serverBundle: false
  },
  // Alternative: disable icon module entirely if still causing issues
  // icon: false
})