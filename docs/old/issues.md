# Frontend Audit Issues

## 1. API 基底網址在正式環境指向本機
- **位置**：`frontend/composables/useApi.js`
- **說明**：`getBaseURL()` 將 `config.public.backendHost === 'backend'` 視為開發模式，直接回傳 `http://localhost:${config.public.backendPort}/api`。正式環境容器會將 `NUXT_PUBLIC_BACKEND_HOST` 設為 `backend`，瀏覽器收到的設定仍是 `backend`，因此請求會打到使用者的 `localhost`，導致 API 全部失敗。
- **影響**：正式環境前端無法呼叫任何 API，登入與資料讀取都會失敗。
- **建議**：優先採用 `runtimeConfig.public.apiBaseUrl`，或至少當 host 為 `backend` 時改用 `http://backend:${port}/api`（容器內）或直接由環境變數提供可公開的 API 網址，避免硬寫 `localhost`。

## 2. Tailwind content 設定被清空
- **位置**：`frontend/nuxt.config.ts`
- **說明**：`tailwindcss.config` 內將 `content` 設為空陣列，會覆蓋 `tailwind.config.js` 的掃描路徑。JIT 無法偵測到任何 class，建置後大量 Tailwind 樣式會消失。
- **影響**：佈景在 build/產線環境會失去 Tailwind 樣式，UI 幾乎失真。
- **建議**：移除這段覆寫，或改為延伸既有設定（例如只調整需要的旗標），讓 `tailwind.config.js` 的 `content` 生效。

## 3. Token 刷新回傳資料解析錯誤
- **位置**：`frontend/stores/auth.js`
- **說明**：`refreshAuthToken()` 直接從 `response.data` 解構 `token`；但後端回傳格式為 `{ success: true, data: { token, refresh_token, expires_in } }`。因此 `newToken` 等值會是 `undefined`，刷新流程永遠失敗，隨後觸發強制登出。
- **影響**：使用者閒置一段時間後必定被登出，無法享有自動換發 Token。
- **建議**：與登入邏輯相同，先處理 `response.data.data || response.data`，再取出 `token`、`refresh_token` 與 `expires_in`。

## 4. `useAuth.updateProfile` 方法誤用 HTTP 方法
- **位置**：`frontend/composables/useAuth.js`
- **說明**：`updateProfile()` 呼叫的是 `get('/users/profile')`，僅取得資料沒有送出更新內容，與函式用途不符。
- **影響**：前端若呼叫 `updateProfile` 期待更新資料會失敗，且可能造成錯誤的使用者體驗。
- **建議**：改用 `put('/users/profile', profileData)` 或依後端實作選擇正確的 endpoint 與方法。

## 5. Dev Proxy 指向不存在的容器名稱
- **位置**：`frontend/nuxt.config.ts`
- **說明**：`nitro.devProxy['/api']` target 設為 `http://urban_renewal-backend-1:8000`，與 `docker-compose.dev.yml` 的服務名稱（`urban_renewal_backend_dev`）不符。使用 `nuxt dev` 時 proxy 會連不到後端。
- **影響**：開發環境無法透過 `/api` 代理呼叫後端，需要手動調整。
- **建議**：改為 `http://localhost:8000` 或對應 `docker-compose` 服務的正確主機名稱。

## 6. 強制亮色模式插件過度侵入
- **位置**：`frontend/plugins/force-light-mode.client.js`、`frontend/assets/css/force-light.css`
- **說明**：插件會覆寫 `document.addEventListener`，攔截所有含 `color-mode` 的 listener，並持續以計時器、MutationObserver 強制移除 `.dark`。同時 CSS 直接把 `.dark` 元素整個隱藏。
- **影響**：第三方元件或 Nuxt UI 若依賴 `dark` 類別，邏輯會被破壞；覆寫原生 API 也可能讓其他插件失效。
- **建議**：改用 Nuxt Color Mode 提供的設定或簡單的 class 切換，不要攔截全域事件監聽，也避免把 `.dark` 元素整個隱藏。

## 7. Pinia 持久化重複寫入
- **位置**：`frontend/plugins/pinia-persist.client.js`
- **說明**：專案已載入 `@pinia-plugin-persistedstate/nuxt` 並在 `useAuthStore` 啟用 `persist`，此插件仍手動監聽 `authStore` 並寫入 `sessionStorage`。兩套機制同時運作，造成重複序列化與不必要的 console log。
- **影響**：增加效能負擔與除錯噪音，且手動還原邏輯若與官方插件衝突，容易導致狀態不同步。
- **建議**：移除自訂插件，僅保留官方 persistedstate 設定；如需清理舊格式，可在啟動時做一次性遷移即可。
