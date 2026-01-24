<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 shadow-lg">
      <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-2">🧪 系統功能測試頁面</h1>
        <p class="text-blue-100">簡化版測試介面</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">測試說明</h2>
        <p class="mb-2">這是一個簡化的測試頁面，用於驗證系統功能。</p>
        <p class="text-sm text-gray-600">如果你看到這段文字，表示頁面正常運作。</p>
      </div>

      <!-- 認證測試 -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">🔐 認證功能測試</h2>
        
        <div class="space-y-4">
          <!-- 註冊測試 -->
          <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">1. 註冊新用戶</h3>
            <div class="grid grid-cols-2 gap-3 mb-3">
              <input v-model="registerData.username" placeholder="使用者名稱" class="border rounded px-3 py-2" />
              <input v-model="registerData.email" type="email" placeholder="Email" class="border rounded px-3 py-2" />
              <input v-model="registerData.password" type="password" placeholder="密碼" class="border rounded px-3 py-2" />
              <input v-model="registerData.full_name" placeholder="真實姓名" class="border rounded px-3 py-2" />
            </div>
            <button @click="testRegister" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
              測試註冊
            </button>
            <div v-if="registerResult" class="mt-2 p-2 bg-gray-100 rounded text-sm">
              <pre>{{ registerResult }}</pre>
            </div>
          </div>

          <!-- 登入測試 -->
          <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">2. 登入</h3>
            <div class="grid grid-cols-2 gap-3 mb-3">
              <input v-model="loginData.email" type="email" placeholder="Email" class="border rounded px-3 py-2" />
              <input v-model="loginData.password" type="password" placeholder="密碼" class="border rounded px-3 py-2" />
            </div>
            <button @click="testLogin" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              測試登入
            </button>
            <div v-if="loginResult" class="mt-2 p-2 bg-gray-100 rounded text-sm">
              <pre>{{ loginResult }}</pre>
            </div>
          </div>

          <!-- 取得用戶資訊 -->
          <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">3. 取得當前用戶資訊 (需要登入)</h3>
            <button @click="testGetMe" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
              測試取得用戶資訊
            </button>
            <div v-if="meResult" class="mt-2 p-2 bg-gray-100 rounded text-sm">
              <pre>{{ meResult }}</pre>
            </div>
          </div>
        </div>
      </div>

      <!-- 更新會管理測試 -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">🏢 更新會管理測試</h2>
        
        <div class="space-y-4">
          <!-- 查看更新會列表 -->
          <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">1. 查看更新會列表 (需要登入)</h3>
            <button @click="testGetUrbanRenewals" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              測試取得列表
            </button>
            <div v-if="urbanRenewalsResult" class="mt-2 p-2 bg-gray-100 rounded text-sm max-h-60 overflow-auto">
              <pre>{{ urbanRenewalsResult }}</pre>
            </div>
          </div>

          <!-- 建立更新會 -->
          <div class="border rounded-lg p-4">
            <h3 class="font-semibold mb-2">2. 建立更新會 (需要登入)</h3>
            <div class="grid grid-cols-2 gap-3 mb-3">
              <input v-model="urbanRenewalData.name" placeholder="更新會名稱" class="border rounded px-3 py-2" />
              <input v-model="urbanRenewalData.county" placeholder="縣市" class="border rounded px-3 py-2" />
              <input v-model="urbanRenewalData.district" placeholder="鄉鎮區" class="border rounded px-3 py-2" />
              <input v-model="urbanRenewalData.address" placeholder="地址" class="border rounded px-3 py-2" />
            </div>
            <button @click="testCreateUrbanRenewal" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
              測試建立
            </button>
            <div v-if="createUrbanRenewalResult" class="mt-2 p-2 bg-gray-100 rounded text-sm">
              <pre>{{ createUrbanRenewalResult }}</pre>
            </div>
          </div>
        </div>
      </div>

      <!-- API 基礎位址設定 -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <label class="block font-semibold mb-2">API 基礎位址:</label>
        <input v-model="apiBaseUrl" class="border rounded px-3 py-2 w-full" />
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false,
  middleware: []
})

// API 基礎位址
const apiBaseUrl = ref('http://localhost:8080')

// 註冊資料
const registerData = ref({
  username: '',
  email: '',
  password: '',
  full_name: ''
})
const registerResult = ref(null)

// 登入資料
const loginData = ref({
  email: '',
  password: ''
})
const loginResult = ref(null)

// 用戶資訊
const meResult = ref(null)

// 更新會資料
const urbanRenewalData = ref({
  name: '',
  county: '',
  district: '',
  address: ''
})
const urbanRenewalsResult = ref(null)
const createUrbanRenewalResult = ref(null)

// 註冊測試
const testRegister = async () => {
  try {
    const response = await $fetch(`${apiBaseUrl.value}/api/auth/register`, {
      method: 'POST',
      body: registerData.value
    })
    registerResult.value = JSON.stringify(response, null, 2)
  } catch (error) {
    registerResult.value = `錯誤: ${error.message}`
  }
}

// 登入測試
const testLogin = async () => {
  try {
    const response = await $fetch(`${apiBaseUrl.value}/api/auth/login`, {
      method: 'POST',
      body: loginData.value,
      credentials: 'include'
    })
    loginResult.value = JSON.stringify(response, null, 2)
  } catch (error) {
    loginResult.value = `錯誤: ${error.message}`
  }
}

// 取得用戶資訊測試
const testGetMe = async () => {
  try {
    const response = await $fetch(`${apiBaseUrl.value}/api/auth/me`, {
      method: 'GET',
      credentials: 'include'
    })
    meResult.value = JSON.stringify(response, null, 2)
  } catch (error) {
    meResult.value = `錯誤: ${error.message}`
  }
}

// 取得更新會列表
const testGetUrbanRenewals = async () => {
  try {
    const response = await $fetch(`${apiBaseUrl.value}/api/urban-renewals`, {
      method: 'GET',
      credentials: 'include'
    })
    urbanRenewalsResult.value = JSON.stringify(response, null, 2)
  } catch (error) {
    urbanRenewalsResult.value = `錯誤: ${error.message}`
  }
}

// 建立更新會
const testCreateUrbanRenewal = async () => {
  try {
    const response = await $fetch(`${apiBaseUrl.value}/api/urban-renewals`, {
      method: 'POST',
      body: urbanRenewalData.value,
      credentials: 'include'
    })
    createUrbanRenewalResult.value = JSON.stringify(response, null, 2)
  } catch (error) {
    createUrbanRenewalResult.value = `錯誤: ${error.message}`
  }
}

// 元件掛載時初始化測試資料
onMounted(() => {
  const timestamp = Date.now()
  registerData.value.username = `testuser_${timestamp}`
  registerData.value.email = `test_${timestamp}@example.com`
  registerData.value.password = 'password123'
  registerData.value.full_name = '測試使用者'
  
  loginData.value.email = 'admin@example.com'
  loginData.value.password = 'password123'
  
  urbanRenewalData.value.name = `測試更新會_${timestamp}`
  urbanRenewalData.value.county = '臺北市'
  urbanRenewalData.value.district = '中正區'
  urbanRenewalData.value.address = '測試地址123號'
})
</script>
