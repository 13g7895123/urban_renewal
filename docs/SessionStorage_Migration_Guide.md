# SessionStorage 遷移指南

## 📋 變更摘要

本次更新將專案從 **SSR (Server-Side Rendering)** 模式改為 **SPA (Single Page Application)** 模式，並將認證資料儲存方式從 **localStorage** 改為 **sessionStorage**。

### 更新日期
2025-11-01

---

## 🎯 主要變更

### 1. **關閉 SSR**
- **檔案**：`frontend/nuxt.config.ts`
- **變更**：`ssr: false`
- **影響**：專案現在是純客戶端渲染（SPA）

### 2. **使用 sessionStorage**
- **替代**：localStorage → sessionStorage
- **工具**：使用 `@pinia-plugin-persistedstate/nuxt` 自動持久化

### 3. **簡化 Auth Store**
- **移除**：所有手動的 localStorage 操作
- **移除**：TOKEN_KEY、REFRESH_TOKEN_KEY 等常數
- **簡化**：initializeAuth() 方法（不再需要手動讀取 localStorage）
- **新增**：persist 配置（自動持久化到 sessionStorage）

### 4. **簡化 Middleware**
- **移除**：所有 `process.server` 檢查
- **移除**：手動呼叫 `initializeAuth()`
- **原因**：SPA 模式下都在客戶端執行，持久化插件會自動恢復狀態

---

## 🔄 localStorage vs sessionStorage

| 特性 | localStorage | sessionStorage |
|------|--------------|----------------|
| **資料保存** | 永久（直到手動清除） | 關閉分頁即清除 |
| **跨分頁共享** | ✅ 可以 | ❌ 不可以 |
| **容量** | ~5-10MB | ~5-10MB |
| **安全性** | ⚠️ XSS 風險 | ⚠️ XSS 風險 |
| **適用場景** | 長期保存使用者偏好 | 單次 Session 認證 |

---

## ✨ 使用 sessionStorage 的優點

### 1. **更高的安全性**
```
關閉分頁 → 自動清除 Token → 降低被盜用風險
```

### 2. **強制重新登入**
```
每次重新開啟瀏覽器 → 需要重新登入 → 確保是本人操作
```

### 3. **避免過期 Token**
```
sessionStorage 在分頁關閉時自動清除 → 不會有過期 Token 殘留
```

### 4. **多帳號支援**
```
不同分頁可以登入不同帳號 → 互不干擾
```

---

## ⚠️ 注意事項

### 1. **使用者體驗變化**

#### ❌ 關閉分頁後：
- **localStorage**：重新開啟仍保持登入
- **sessionStorage**：需要重新登入

#### ❌ 開新分頁：
- **localStorage**：自動同步登入狀態
- **sessionStorage**：需要重新登入

### 2. **重新整理頁面**
- ✅ **重新整理**：登入狀態**會保留**（同一個分頁）
- ❌ **關閉分頁**：登入狀態**會清除**

### 3. **多分頁情境**
```
分頁 A：登入帳號 user1
分頁 B：可以登入帳號 user2
→ 兩個分頁互不影響
```

---

## 🔧 如何測試

### 測試 1：登入與重新整理
```
1. 登入系統
2. 重新整理頁面（F5）
3. ✅ 應該保持登入狀態
```

### 測試 2：關閉分頁
```
1. 登入系統
2. 關閉分頁
3. 重新開啟網站
4. ✅ 應該需要重新登入
```

### 測試 3：多分頁
```
1. 分頁 A：登入 admin
2. 開新分頁 B：登入 chairman
3. ✅ 兩個分頁應該獨立運作
```

### 測試 4：企業管理員權限
```
1. 以 is_company_manager = 1 的帳號登入
2. ✅ 應該看到「企業管理」選單
3. 點擊進入企業管理頁面
4. ✅ 應該可以正常使用
```

---

## 📦 依賴套件

### 新增套件
```json
{
  "@pinia-plugin-persistedstate/nuxt": "^1.2.1"
}
```

### 安裝指令
```bash
npm install @pinia-plugin-persistedstate/nuxt
```

---

## 🗂️ 修改檔案清單

### 設定檔
- ✅ `frontend/nuxt.config.ts`

### Stores
- ✅ `frontend/stores/auth.js`

### Middleware
- ✅ `frontend/middleware/auth.js`
- ✅ `frontend/middleware/admin.js`
- ✅ `frontend/middleware/guest.js`
- ✅ `frontend/middleware/company-manager.js`

---

## 🚀 部署步驟

### 1. 清除舊的 localStorage
```javascript
// 在瀏覽器 Console 執行
localStorage.clear()
```

### 2. 重新啟動開發伺服器
```bash
cd frontend
npm run dev
```

### 3. 清除瀏覽器快取
```
Ctrl + Shift + Delete
→ 清除快取和 Cookie
```

### 4. 重新登入測試
```
1. 訪問 http://localhost:9128
2. 登入系統
3. 檢查 sessionStorage（F12 → Application → Session Storage）
4. 應該看到 auth store 的資料
```

---

## 🔍 除錯工具

### 查看 sessionStorage
```javascript
// 在瀏覽器 Console 執行
console.log(sessionStorage)
```

### 查看 Auth Store 資料
```javascript
// F12 → Application → Session Storage → http://localhost:9128
// 找到 "auth" 這個 key
```

### 手動清除 sessionStorage
```javascript
sessionStorage.clear()
location.reload()
```

---

## ❓ 常見問題

### Q1: 為什麼關閉分頁後需要重新登入？
**A**: 這是 sessionStorage 的設計特性，為了提高安全性。如果您希望保持登入，可以改回使用 localStorage。

### Q2: 如何改回 localStorage？
**A**: 修改 `nuxt.config.ts`:
```javascript
piniaPersistedstate: {
  storage: 'localStorage' // 改為 localStorage
}
```

### Q3: 為什麼重新整理頁面後仍然保持登入？
**A**: sessionStorage 只在關閉分頁時清除，重新整理頁面不會清除。

### Q4: 多個分頁可以同時登入不同帳號嗎？
**A**: 可以！sessionStorage 是獨立於每個分頁的。

### Q5: 舊的 localStorage 資料怎麼辦？
**A**: 需要手動清除：
```javascript
localStorage.removeItem('auth_token')
localStorage.removeItem('auth_refresh_token')
localStorage.removeItem('auth_token_expires_at')
localStorage.removeItem('auth_user')
```

---

## 📚 相關文件

- [Nuxt 3 SPA Mode](https://nuxt.com/docs/guide/concepts/rendering#client-side-rendering)
- [Pinia Persisted State](https://prazdevs.github.io/pinia-plugin-persistedstate/)
- [SessionStorage MDN](https://developer.mozilla.org/zh-TW/docs/Web/API/Window/sessionStorage)

---

## 🎉 總結

✅ **關閉 SSR**：專案改為 SPA 模式
✅ **使用 sessionStorage**：提高安全性
✅ **自動持久化**：使用 Pinia 插件
✅ **簡化程式碼**：移除手動 localStorage 操作
✅ **企業管理員支援**：is_company_manager 欄位自動儲存

現在使用者資料會自動儲存到 sessionStorage，關閉分頁後會自動清除，提高系統安全性！
