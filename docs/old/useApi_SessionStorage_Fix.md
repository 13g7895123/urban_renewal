# useApi SessionStorage 修復說明

## 🐛 問題描述

在 `/tables/company-profile` 頁面新增使用者時，系統提示「請重新登入」，但使用者才剛登入。

### 問題發生時機
- 登入成功後
- 訪問企業管理頁面
- 點擊「新增使用者」
- 填寫表單送出
- ❌ **錯誤訊息**：「登入已過期，請重新登入」

---

## 🔍 問題根源

### 衝突點

**前端狀態管理**：使用 `sessionStorage`（透過 Pinia 持久化插件）
```javascript
// stores/auth.js
export const useAuthStore = defineStore('auth', () => {
  // ...
}, {
  persist: {
    storage: sessionStorage,
    paths: ['user', 'token', 'refreshToken', 'tokenExpiresAt']
  }
})
```

**API 請求處理**：使用 `localStorage`（舊的實作）
```javascript
// composables/useApi.js (舊版)
const getAuthToken = () => {
  if (process.client) {
    return localStorage.getItem('auth_token')  // ❌ 錯誤！
  }
  return null
}
```

### 導致結果

1. 使用者登入 → Token 儲存到 **sessionStorage**
2. 呼叫 API → useApi 從 **localStorage** 讀取 Token
3. localStorage 沒有 Token → 請求沒有帶 Authorization Header
4. 後端返回 401 → ❌ 「請重新登入」

---

## ✅ 解決方案

### 修改內容

#### 1. **getAuthToken() 函數**

**修改前**：
```javascript
const getAuthToken = () => {
  if (process.client) {
    return localStorage.getItem('auth_token')
  }
  return null
}
```

**修改後**：
```javascript
const getAuthToken = () => {
  if (process.client) {
    // 優先從 Pinia store 取得 token（使用 sessionStorage 持久化）
    try {
      const authStore = useAuthStore()
      if (authStore.token) {
        return authStore.token
      }
    } catch (error) {
      console.warn('[API] Could not access auth store, falling back to sessionStorage')
    }

    // 回退方案：從 sessionStorage 讀取（Pinia 持久化的資料）
    const persistedAuth = sessionStorage.getItem('auth')
    if (persistedAuth) {
      try {
        const authData = JSON.parse(persistedAuth)
        return authData.token
      } catch (e) {
        console.error('[API] Failed to parse auth from sessionStorage:', e)
      }
    }
  }
  return null
}
```

#### 2. **Token 刷新邏輯**

**修改前**：
```javascript
const refreshToken = localStorage.getItem('auth_refresh_token')
```

**修改後**：
```javascript
// 從 Pinia store 或 sessionStorage 取得 refresh token
let refreshToken = null
try {
  const authStore = useAuthStore()
  refreshToken = authStore.refreshToken
} catch (e) {
  const persistedAuth = sessionStorage.getItem('auth')
  if (persistedAuth) {
    const authData = JSON.parse(persistedAuth)
    refreshToken = authData.refreshToken
  }
}
```

#### 3. **Token 更新後儲存**

**修改前**：
```javascript
localStorage.setItem('auth_token', response.data.token)
localStorage.setItem('auth_refresh_token', response.data.refresh_token)
```

**修改後**：
```javascript
// 更新 Pinia store（會自動持久化到 sessionStorage）
const authStore = useAuthStore()
authStore.token = response.data.token
authStore.refreshToken = response.data.refresh_token
authStore.tokenExpiresAt = new Date(Date.now() + (response.data.expires_in * 1000)).toISOString()
```

#### 4. **清除認證資料**

**修改前**：
```javascript
localStorage.removeItem('auth_token')
localStorage.removeItem('auth_refresh_token')
localStorage.removeItem('auth_token_expires_at')
localStorage.removeItem('auth_user')
```

**修改後**：
```javascript
// 清除 Pinia store（會自動清除 sessionStorage）
const authStore = useAuthStore()
authStore.logout(true) // skipApiCall = true
```

#### 5. **setAuthToken() 和 clearAuthToken()**

**修改前**：
```javascript
const setAuthToken = (token) => {
  if (process.client) {
    localStorage.setItem('auth_token', token)
  }
}

const clearAuthToken = () => {
  if (process.client) {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }
}
```

**修改後**：
```javascript
const setAuthToken = (token) => {
  if (process.client) {
    try {
      const authStore = useAuthStore()
      authStore.token = token
    } catch (error) {
      console.error('[API] Failed to set auth token in store:', error)
    }
  }
}

const clearAuthToken = () => {
  if (process.client) {
    try {
      const authStore = useAuthStore()
      authStore.logout(true)
    } catch (error) {
      console.error('[API] Failed to clear auth token from store:', error)
      sessionStorage.removeItem('auth')
    }
  }
}
```

---

## 🎯 修改策略

### 雙層讀取機制

1. **優先**：從 Pinia Store 讀取（記憶體中，最快）
2. **回退**：從 sessionStorage 讀取（持久化資料）

### 為什麼需要回退方案？

- Pinia Store 可能尚未初始化
- 避免循環依賴問題
- 提供更健壯的錯誤處理

---

## 📊 修改檔案清單

### 前端
- ✅ `frontend/composables/useApi.js`

### 文件
- ✅ `docs/prompts.md`（標記第 4 點完成）
- ✅ `docs/useApi_SessionStorage_Fix.md`（本文件）

---

## 🧪 測試步驟

### 測試 1：基本 API 請求
```
1. 登入系統
2. 開啟瀏覽器開發者工具（F12）
3. 切換到 Network 分頁
4. 執行任何 API 請求（如載入使用者列表）
5. 檢查 Request Headers
   ✅ 應該包含：Authorization: Bearer {token}
```

### 測試 2：新增使用者
```
1. 登入系統
2. 訪問 /tables/company-profile
3. 點擊「新增使用者」
4. 填寫表單
5. 送出
   ✅ 應該成功新增
   ✅ 不應該出現「請重新登入」錯誤
```

### 測試 3：Token 刷新
```
1. 登入系統
2. 等待 Token 即將過期（或手動修改 tokenExpiresAt）
3. 執行 API 請求
4. 檢查 Console
   ✅ 應該看到「Token refreshed successfully」
   ✅ sessionStorage 中的 token 應該更新
```

### 測試 4：關閉分頁重開
```
1. 登入系統
2. 關閉分頁
3. 重新開啟網站
4. ✅ 應該需要重新登入（sessionStorage 已清除）
```

---

## 🔍 除錯方法

### 檢查 Token 來源

在瀏覽器 Console 執行：
```javascript
// 檢查 Pinia Store
const authStore = useAuthStore()
console.log('Store Token:', authStore.token)

// 檢查 sessionStorage
const persistedAuth = sessionStorage.getItem('auth')
console.log('sessionStorage:', JSON.parse(persistedAuth))

// 檢查 localStorage（應該是空的）
console.log('localStorage auth_token:', localStorage.getItem('auth_token'))
```

### 檢查 API 請求 Header

在瀏覽器 Console 執行：
```javascript
const { getAuthHeaders } = useApi()
console.log('Auth Headers:', getAuthHeaders())
```

**預期輸出**：
```javascript
{
  Authorization: "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

---

## ⚠️ 注意事項

### 1. **避免循環依賴**

useApi 中使用 useAuthStore 可能導致循環依賴，因此：
- 使用 try-catch 處理
- 提供 sessionStorage 回退方案

### 2. **Store 未初始化**

在某些情況下（如首次載入），Pinia Store 可能尚未初始化，因此：
- 優先嘗試從 Store 讀取
- 失敗時從 sessionStorage 讀取

### 3. **向後相容**

雖然已移除 localStorage，但保留了 sessionStorage 回退機制：
- 確保在 Store 不可用時仍能正常運作
- 提供更好的錯誤處理

---

## 📚 相關文件

- [SessionStorage 遷移指南](./SessionStorage_Migration_Guide.md)
- [會員系統架構說明](./會員系統架構說明.md)
- [Pinia Persisted State 文件](https://prazdevs.github.io/pinia-plugin-persistedstate/)

---

## ✅ 總結

### 修復前
```
登入 → Token 存到 sessionStorage
新增使用者 → useApi 從 localStorage 讀取
→ 找不到 Token → ❌ 請重新登入
```

### 修復後
```
登入 → Token 存到 sessionStorage（透過 Pinia）
新增使用者 → useApi 從 Pinia Store / sessionStorage 讀取
→ 找到 Token → ✅ 請求成功
```

現在所有 API 請求都能正確帶上 Authorization Token，不會再出現「請重新登入」的錯誤！
