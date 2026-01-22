<template>
  <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
    <div class="flex justify-between items-start mb-3">
      <div>
        <h3 class="font-semibold text-lg">{{ title }}</h3>
        <p class="text-sm text-gray-600">
          <span class="font-mono bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ method }}</span>
          <span class="ml-2 font-mono text-sm">{{ endpoint }}</span>
        </p>
        <p v-if="requiresAuth" class="text-xs text-orange-600 mt-1">🔒 需要認證</p>
      </div>
      <button 
        @click="toggleExpand"
        class="text-gray-500 hover:text-gray-700"
      >
        <svg 
          class="w-5 h-5 transition-transform"
          :class="isExpanded ? 'rotate-180' : ''"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <div v-show="isExpanded">
      <!-- Fields -->
      <div v-if="fields.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
        <div v-for="(field, index) in fields" :key="index" class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">
            {{ field.label }}
            <span v-if="field.required" class="text-red-500">*</span>
          </label>
          
          <!-- Text/Number/Email/Password/Date/Time Input -->
          <input 
            v-if="['text', 'number', 'email', 'password', 'date', 'time'].includes(field.type)"
            v-model="formData[field.name]"
            :type="field.type"
            :required="field.required"
            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :placeholder="field.label"
          />
          
          <!-- Textarea -->
          <textarea 
            v-else-if="field.type === 'textarea'"
            v-model="formData[field.name]"
            :required="field.required"
            rows="3"
            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :placeholder="field.label"
          ></textarea>
          
          <!-- Select -->
          <select 
            v-else-if="field.type === 'select'"
            v-model="formData[field.name]"
            :required="field.required"
            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="option in field.options" :key="option" :value="option">
              {{ option || '(空值)' }}
            </option>
          </select>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2">
        <button 
          @click="runTest"
          :disabled="isLoading"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <svg v-if="isLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isLoading ? '執行中...' : '執行測試' }}
        </button>
        
        <button 
          @click="fillTestData"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          填入測試資料
        </button>
        
        <button 
          @click="clearForm"
          class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
        >
          清除
        </button>
        
        <button 
          v-if="testResult"
          @click="copyResult"
          class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700"
        >
          複製結果
        </button>
      </div>

      <!-- Test Result -->
      <div v-if="testResult" class="mt-4">
        <div 
          class="p-4 rounded-lg"
          :class="testResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'"
        >
          <div class="flex items-start gap-2 mb-2">
            <svg 
              v-if="testResult.success"
              class="w-5 h-5 text-green-600 mt-0.5" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg 
              v-else
              class="w-5 h-5 text-red-600 mt-0.5" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <div class="flex-1">
              <h4 class="font-semibold" :class="testResult.success ? 'text-green-800' : 'text-red-800'">
                {{ testResult.success ? '✅ 測試成功' : '❌ 測試失敗' }}
              </h4>
              <p class="text-sm mt-1" :class="testResult.success ? 'text-green-700' : 'text-red-700'">
                狀態碼: {{ testResult.status }}
                <span v-if="testResult.duration" class="ml-2">
                  | 耗時: {{ testResult.duration }}ms
                </span>
              </p>
            </div>
          </div>
          
          <div class="mt-3">
            <button 
              @click="showFullResponse = !showFullResponse"
              class="text-sm font-medium"
              :class="testResult.success ? 'text-green-700 hover:text-green-800' : 'text-red-700 hover:text-red-800'"
            >
              {{ showFullResponse ? '▼ 隱藏' : '▶ 查看' }} 完整回應
            </button>
            
            <div v-show="showFullResponse" class="mt-2">
              <pre class="bg-gray-900 text-green-400 p-3 rounded text-xs overflow-x-auto">{{ formatJson(testResult.data) }}</pre>
            </div>
          </div>

          <div v-if="testResult.error" class="mt-3">
            <p class="text-sm font-semibold text-red-700">錯誤訊息:</p>
            <pre class="bg-red-900 text-red-100 p-3 rounded text-xs mt-1 overflow-x-auto">{{ testResult.error }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  fields: {
    type: Array,
    default: () => []
  },
  method: {
    type: String,
    required: true
  },
  endpoint: {
    type: String,
    required: true
  },
  requiresAuth: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['test'])

const isExpanded = ref(false)
const isLoading = ref(false)
const testResult = ref(null)
const showFullResponse = ref(false)

// Initialize form data with default values - use onMounted to avoid hydration issues
const formData = reactive({})

onMounted(() => {
  props.fields.forEach(field => {
    formData[field.name] = field.value || ''
  })
})

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}

const fillTestData = () => {
  props.fields.forEach(field => {
    formData[field.name] = field.value || ''
  })
}

const clearForm = () => {
  Object.keys(formData).forEach(key => {
    formData[key] = ''
  })
  testResult.value = null
}

const runTest = async () => {
  isLoading.value = true
  testResult.value = null
  const startTime = Date.now()

  try {
    // Build endpoint with path parameters
    let finalEndpoint = props.endpoint
    Object.keys(formData).forEach(key => {
      const placeholder = `{${key}}`
      if (finalEndpoint.includes(placeholder)) {
        finalEndpoint = finalEndpoint.replace(placeholder, formData[key])
      }
    })

    // Build request body (exclude path parameters)
    const requestBody = {}
    Object.keys(formData).forEach(key => {
      if (!props.endpoint.includes(`{${key}}`)) {
        let value = formData[key]
        
        // Try to parse JSON strings
        if (typeof value === 'string' && (value.startsWith('[') || value.startsWith('{'))) {
          try {
            value = JSON.parse(value)
          } catch (e) {
            // Keep as string if not valid JSON
          }
        }
        
        // Convert comma-separated strings to arrays for specific fields
        if (key === 'renewal_ids' || key === 'ids' || key === 'permissions') {
          if (typeof value === 'string' && value.includes(',')) {
            value = value.split(',').map(v => v.trim())
          }
        }
        
        // Convert boolean strings
        if (value === 'true') value = true
        if (value === 'false') value = false
        
        // Only include non-empty values
        if (value !== '' && value !== null && value !== undefined) {
          requestBody[key] = value
        }
      }
    })

    // Get API base URL from parent
    const apiBaseUrl = 'http://localhost:8080'
    const url = `${apiBaseUrl}${finalEndpoint}`

    // Prepare fetch options
    const options = {
      method: props.method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }

    // Add body for POST, PUT, PATCH
    if (['POST', 'PUT', 'PATCH'].includes(props.method) && Object.keys(requestBody).length > 0) {
      options.body = JSON.stringify(requestBody)
    }

    // Add query parameters for GET
    if (props.method === 'GET' && Object.keys(requestBody).length > 0) {
      const queryParams = new URLSearchParams()
      Object.keys(requestBody).forEach(key => {
        if (Array.isArray(requestBody[key])) {
          requestBody[key].forEach(val => queryParams.append(key + '[]', val))
        } else {
          queryParams.append(key, requestBody[key])
        }
      })
      const queryString = queryParams.toString()
      if (queryString) {
        options.url = `${url}?${queryString}`
      } else {
        options.url = url
      }
    } else {
      options.url = url
    }

    console.log('Testing API:', {
      url: options.url || url,
      method: props.method,
      body: requestBody
    })

    // Execute request
    const response = await fetch(options.url || url, options)
    const duration = Date.now() - startTime
    
    let data
    const contentType = response.headers.get('content-type')
    if (contentType && contentType.includes('application/json')) {
      data = await response.json()
    } else {
      data = await response.text()
    }

    testResult.value = {
      success: response.ok,
      status: response.status,
      duration,
      data,
      error: response.ok ? null : (data.message || data.error || '請求失敗')
    }

  } catch (error) {
    const duration = Date.now() - startTime
    testResult.value = {
      success: false,
      status: 0,
      duration,
      data: null,
      error: error.message
    }
  } finally {
    isLoading.value = false
  }
}

const copyResult = () => {
  const text = JSON.stringify(testResult.value.data, null, 2)
  navigator.clipboard.writeText(text).then(() => {
    alert('結果已複製到剪貼簿！')
  })
}

const formatJson = (data) => {
  if (typeof data === 'string') {
    try {
      return JSON.stringify(JSON.parse(data), null, 2)
    } catch (e) {
      return data
    }
  }
  return JSON.stringify(data, null, 2)
}
</script>
