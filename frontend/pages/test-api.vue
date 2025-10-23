<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <UContainer>
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">API 連接測試</h1>

        <!-- Environment Info -->
        <UCard class="mb-6">
          <template #header>
            <h2 class="text-xl font-semibold">環境變數配置</h2>
          </template>

          <div class="space-y-3">
            <div class="grid grid-cols-3 gap-4">
              <span class="font-medium">API Base URL:</span>
              <span class="col-span-2 font-mono text-sm bg-gray-100 p-2 rounded">{{ config.public.apiBaseUrl }}</span>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <span class="font-medium">Backend URL:</span>
              <span class="col-span-2 font-mono text-sm bg-gray-100 p-2 rounded">{{ config.public.backendUrl }}</span>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <span class="font-medium">Backend Host:</span>
              <span class="col-span-2 font-mono text-sm bg-gray-100 p-2 rounded">{{ config.public.backendHost }}</span>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <span class="font-medium">Backend Port:</span>
              <span class="col-span-2 font-mono text-sm bg-gray-100 p-2 rounded">{{ config.public.backendPort }}</span>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <span class="font-medium">運行環境:</span>
              <span class="col-span-2 font-mono text-sm bg-gray-100 p-2 rounded">
                {{ isClient ? 'Client-side (瀏覽器)' : 'Server-side (SSR)' }}
              </span>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <span class="font-medium">解析後的 API URL:</span>
              <span class="col-span-2 font-mono text-sm bg-green-100 p-2 rounded font-bold">{{ resolvedApiUrl }}</span>
            </div>
          </div>
        </UCard>

        <!-- Test Controls -->
        <UCard class="mb-6">
          <template #header>
            <h2 class="text-xl font-semibold">API 測試</h2>
          </template>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-2">測試端點:</label>
              <UInput v-model="testEndpoint" placeholder="/urban-renewals" />
            </div>

            <div class="flex gap-4">
              <UButton @click="testApi" :loading="testing" color="primary" size="lg">
                測試 API 連接
              </UButton>
              <UButton @click="testDirectUrl" :loading="testingDirect" color="gray" size="lg">
                直接測試 localhost:9228
              </UButton>
            </div>
          </div>
        </UCard>

        <!-- Test Results -->
        <UCard v-if="testResult || testError" class="mb-6">
          <template #header>
            <h2 class="text-xl font-semibold">測試結果</h2>
          </template>

          <div class="space-y-4">
            <!-- Success Result -->
            <div v-if="testResult" class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-start gap-3 mb-3">
                <Icon name="heroicons:check-circle" class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <h3 class="font-semibold text-green-900 mb-1">連接成功！</h3>
                  <div class="space-y-2 text-sm">
                    <div>
                      <span class="font-medium">完整請求 URL:</span>
                      <div class="font-mono bg-white p-2 rounded mt-1 break-all">{{ testResult.url }}</div>
                    </div>
                    <div>
                      <span class="font-medium">HTTP 狀態:</span>
                      <span class="ml-2 font-mono">{{ testResult.status || 200 }}</span>
                    </div>
                    <div>
                      <span class="font-medium">響應時間:</span>
                      <span class="ml-2 font-mono">{{ testResult.responseTime }}ms</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <div class="font-medium mb-2">響應數據:</div>
                <pre class="bg-white p-4 rounded border border-green-200 overflow-auto max-h-96 text-xs">{{ JSON.stringify(testResult.data, null, 2) }}</pre>
              </div>
            </div>

            <!-- Error Result -->
            <div v-if="testError" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <div class="flex items-start gap-3 mb-3">
                <Icon name="heroicons:x-circle" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <h3 class="font-semibold text-red-900 mb-1">連接失敗！</h3>
                  <div class="space-y-2 text-sm">
                    <div>
                      <span class="font-medium">嘗試的 URL:</span>
                      <div class="font-mono bg-white p-2 rounded mt-1 break-all">{{ testError.url }}</div>
                    </div>
                    <div>
                      <span class="font-medium">錯誤訊息:</span>
                      <div class="font-mono bg-white p-2 rounded mt-1">{{ testError.message }}</div>
                    </div>
                    <div v-if="testError.status">
                      <span class="font-medium">HTTP 狀態:</span>
                      <span class="ml-2 font-mono">{{ testError.status }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="testError.details" class="mt-4">
                <div class="font-medium mb-2">詳細信息:</div>
                <pre class="bg-white p-4 rounded border border-red-200 overflow-auto max-h-96 text-xs">{{ JSON.stringify(testError.details, null, 2) }}</pre>
              </div>
            </div>
          </div>
        </UCard>

        <!-- Browser Console Logs -->
        <UCard>
          <template #header>
            <h2 class="text-xl font-semibold">控制台日誌</h2>
          </template>

          <div class="space-y-2">
            <p class="text-sm text-gray-600 mb-3">
              請打開瀏覽器開發者工具 (F12) 查看 Console 標籤，應該會看到類似以下的日誌:
            </p>
            <div class="bg-gray-900 text-green-400 p-4 rounded font-mono text-xs space-y-1">
              <div>[API] Client-side using localhost URL: http://localhost:9228/api</div>
              <div>[API] Base URL resolved to: http://localhost:9228/api</div>
              <div>[API] GET http://localhost:9228/api/urban-renewals</div>
              <div>[API] Success: http://localhost:9228/api/urban-renewals</div>
            </div>
          </div>
        </UCard>
      </div>
    </UContainer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const config = useRuntimeConfig()
const { get } = useApi()

const isClient = ref(false)
const testEndpoint = ref('/urban-renewals')
const testing = ref(false)
const testingDirect = ref(false)
const testResult = ref(null)
const testError = ref(null)
const resolvedApiUrl = ref('')

onMounted(() => {
  isClient.value = true

  // Get the resolved API URL
  const isDev = config.public.backendHost === 'backend' || config.public.backendHost === 'localhost'
  if (isDev) {
    resolvedApiUrl.value = `http://localhost:${config.public.backendPort || 9228}/api`
  } else if (config.public.apiBaseUrl && !config.public.apiBaseUrl.includes('backend:')) {
    const baseUrl = config.public.apiBaseUrl
    resolvedApiUrl.value = baseUrl.endsWith('/api') ? baseUrl : `${baseUrl}/api`
  } else {
    resolvedApiUrl.value = 'http://localhost:9228/api (fallback)'
  }
})

const testApi = async () => {
  testing.value = true
  testResult.value = null
  testError.value = null

  console.log('=== API Test Started ===')
  console.log('Testing endpoint:', testEndpoint.value)

  const startTime = Date.now()

  try {
    const response = await get(testEndpoint.value)
    const responseTime = Date.now() - startTime

    console.log('=== API Test Success ===')
    console.log('Response:', response)

    if (response.success) {
      testResult.value = {
        url: `${resolvedApiUrl.value}${testEndpoint.value}`,
        data: response.data,
        status: response.status || 200,
        responseTime
      }
    } else {
      testError.value = {
        url: `${resolvedApiUrl.value}${testEndpoint.value}`,
        message: response.error?.message || '未知錯誤',
        status: response.error?.status,
        details: response.error
      }
    }
  } catch (error) {
    console.error('=== API Test Error ===')
    console.error('Error:', error)

    testError.value = {
      url: `${resolvedApiUrl.value}${testEndpoint.value}`,
      message: error.message || '連接失敗',
      status: error.status || error.statusCode,
      details: error
    }
  } finally {
    testing.value = false
  }
}

const testDirectUrl = async () => {
  testingDirect.value = true
  testResult.value = null
  testError.value = null

  const directUrl = `http://localhost:9228/api${testEndpoint.value}`

  console.log('=== Direct URL Test Started ===')
  console.log('Testing URL:', directUrl)

  const startTime = Date.now()

  try {
    const response = await $fetch(directUrl)
    const responseTime = Date.now() - startTime

    console.log('=== Direct URL Test Success ===')
    console.log('Response:', response)

    testResult.value = {
      url: directUrl,
      data: response,
      status: 200,
      responseTime
    }
  } catch (error) {
    console.error('=== Direct URL Test Error ===')
    console.error('Error:', error)

    testError.value = {
      url: directUrl,
      message: error.message || error.data?.message || '連接失敗',
      status: error.status || error.statusCode,
      details: error.data || error
    }
  } finally {
    testingDirect.value = false
  }
}
</script>
