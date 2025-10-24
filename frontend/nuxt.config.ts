export default defineNuxtConfig({
  compatibilityDate: '2025-10-06',
  devtools: { enabled: false },
  // Development server configuration
  devServer: {
    port: parseInt(process.env.FRONTEND_PORT || '4001'),
    host: 'localhost'
  },
  ssr: true,
  modules: [
    '@nuxt/ui',
    '@pinia/nuxt'
  ],
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
    quiet: true,
    config: {
      content: [],
      important: false
    }
  },
  plugins: [
    '~/plugins/auth.client.js',
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
        target: 'http://urban_renewal-backend-1:8000',
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