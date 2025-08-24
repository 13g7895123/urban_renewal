import { vi } from 'vitest'

// Mock $fetch globally
global.$fetch = vi.fn()

// Mock navigateTo globally  
global.navigateTo = vi.fn()

// Mock useRoute
global.useRoute = vi.fn(() => ({
  query: {}
}))

// Mock useRuntimeConfig
global.useRuntimeConfig = vi.fn(() => ({
  public: {
    apiBaseUrl: 'http://localhost:8000/api'
  }
}))

// Mock useApi globally to fix import issues
global.useApi = vi.fn(() => ({
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
  delete: vi.fn(),
  apiRequest: vi.fn(),
  getAuthToken: vi.fn(),
  getAuthHeaders: vi.fn(),
  setAuthToken: vi.fn(),
  clearAuthToken: vi.fn()
}))

// Mock localStorage
Object.defineProperty(window, 'localStorage', {
  value: {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
  },
  writable: true
})

// Mock process
global.process = {
  client: true,
  dev: false,
  env: {
    NODE_ENV: 'test'
  }
}