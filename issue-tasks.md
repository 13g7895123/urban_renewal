# Frontend Issue Resolution Tasks

## 優先級排序

### 🔴 高優先級（影響正式環境運作）

#### 1. 修正 API 基底網址判斷邏輯
**問題**：正式環境會將 `backend` 主機誤判為開發模式，導致瀏覽器請求打向使用者本機 `localhost`，API 全部失敗。

**修改位置**：`frontend/composables/useApi.js` → `getBaseURL()`

**修改內容**：
- 優先採用 `runtimeConfig.public.apiBaseUrl`
- 當 host 為 `backend` 時，改用環境變數提供的公開 API 網址
- 移除硬編碼 `localhost` 的判斷邏輯

**驗證方式**：
- 開發環境：執行登入與資料請求，確認 API 呼叫成功
- Docker 正式環境：部署後測試完整使用者流程

---

#### 2. 恢復 Tailwind 樣式掃描設定
**問題**：`nuxt.config.ts` 將 Tailwind `content` 設為空陣列，JIT 無法掃描任何檔案，建置後樣式會大量遺失。

**修改位置**：`frontend/nuxt.config.ts` → `tailwindcss.config`

**修改內容**：
- 移除 `content: []` 覆寫設定
- 讓專案既有的 `tailwind.config.js` 生效

**驗證方式**：
```bash
npm run build
# 檢查 .output/public/_nuxt/*.css 是否包含專案使用的 utility classes
```

---

#### 3. 修正 Token 自動刷新資料解析
**問題**：`refreshAuthToken()` 直接從 `response.data` 解構，但後端實際回傳 `response.data.data`，導致 token 永遠為 `undefined`，使用者閒置後必定被登出。

**修改位置**：`frontend/stores/auth.js` → `refreshAuthToken()`

**修改內容**：
```javascript
// 修改前
const { token: newToken, refresh_token: newRefreshToken, expires_in } = response.data

// 修改後（與登入邏輯一致）
const backendData = response.data.data || response.data
const { token: newToken, refresh_token: newRefreshToken, expires_in } = backendData
```

**驗證方式**：
- 縮短 token 到期時間（如改為 5 分鐘）
- 等待自動刷新觸發
- 檢查 Network 面板與 sessionStorage，確認 token 已更新且未登出

---

### 🟡 中優先級（影響開發體驗與部分功能）

#### 4. 修正開發環境 API 代理設定
**問題**：`devProxy` target 指向不存在的容器名稱 `urban_renewal-backend-1`，與 `docker-compose.dev.yml` 定義的 `urban_renewal_backend_dev` 不符。

**修改位置**：`frontend/nuxt.config.ts` → `nitro.devProxy`

**修改內容**：
```typescript
devProxy: {
  '/api': {
    target: 'http://localhost:9228',  // 或使用環境變數 ${BACKEND_PORT}
    changeOrigin: true,
    prependPath: true,
  }
}
```

**驗證方式**：
```bash
npm run dev
# 於瀏覽器 Network 面板確認 /api/* 請求成功轉發
```

---

#### 5. 修正個人資料更新方法
**問題**：`useAuth.updateProfile()` 使用 `GET` 方法，無法實際送出更新資料。

**修改位置**：`frontend/composables/useAuth.js` → `updateProfile()`

**修改內容**：
```javascript
// 修改前
const updateProfile = async (profileData) => {
  return await get('/users/profile')
}

// 修改後
const updateProfile = async (profileData) => {
  return await put('/users/profile', profileData)
}
```

**驗證方式**：
- 於個人資料頁面提交表單
- 確認後端資料庫已更新

---

### 🟢 低優先級（優化與重構）

#### 6. 簡化強制亮色模式實作
**問題**：自訂插件覆寫 `document.addEventListener` 並以 MutationObserver 暴力移除 `.dark`，可能影響第三方元件與 Nuxt UI 邏輯。

**修改位置**：
- `frontend/plugins/force-light-mode.client.js`
- `frontend/assets/css/force-light.css`

**建議方案**：
- 使用 Nuxt Color Mode 官方設定（已於 `nuxt.config.ts` 配置）
- 移除 MutationObserver 與 `addEventListener` 覆寫
- 簡化 CSS 為基礎樣式覆寫，避免完全隱藏 `.dark` 元素

**驗證方式**：
- 瀏覽各頁面與元件
- 確認維持亮色模式且 Nuxt UI 元件行為正常

---

#### 7. 移除重複的 Pinia 持久化邏輯
**問題**：專案已使用 `@pinia-plugin-persistedstate/nuxt`，但自訂 `pinia-persist.client.js` 仍手動監聽並寫入 sessionStorage，造成雙重序列化與除錯噪音。

**修改位置**：`frontend/plugins/pinia-persist.client.js`

**修改內容**：
- 刪除整個插件檔案
- 或保留僅用於舊資料格式清理邏輯（一次性執行）

**驗證方式**：
```bash
# 清除 sessionStorage
sessionStorage.clear()

# 登入後重新整理頁面
# 確認登入狀態持久化且 console 無多餘 log
```

---

## 執行順序建議

1. **第一階段**（確保正式環境可用）：
   - Task 1: API 基底網址
   - Task 2: Tailwind 設定
   - Task 3: Token 刷新

2. **第二階段**（優化開發環境）：
   - Task 4: 開發代理
   - Task 5: 個人資料更新

3. **第三階段**（程式碼清理）：
   - Task 6: 強制亮色模式
   - Task 7: Pinia 持久化

---

## 相關文件

- 問題詳細說明：`issues.md`
- 後端 API 規格：`backend/app/Controllers/Api/AuthController.php`
- 環境變數設定：`.env`、`docker-compose.dev.yml`
