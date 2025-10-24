1. 目前登入按鈕送出後是有問題的，幫我確認一下是哪邊的問題，並確認一下seeder中是否至少有兩個身分的帳號存在
2. 目前登入頁出現這個錯誤error caught during app initialization ReferenceError: useAuthStore is not defined

## 問題修復 (2025-10-24)

### 問題 2: useAuthStore is not defined

**問題分析:**
1. auth.client.js 插件沒有在 nuxt.config.ts 中明確註冊
2. auth store 中的 API 響應路徑不正確 (使用了 `response.data.data` 而不是 `response.data`)

**已修復:**
1. ✅ 在 nuxt.config.ts 的 plugins 陣列中添加了 `~/plugins/auth.client.js`
2. ✅ 修正了 auth.js store 中的響應路徑：
   - login: `response.data.data` → `response.data`
   - fetchUser: `response.data.data.user` → `response.data`
   - updateProfile: `response.data.data.user` → `response.data`
   - refreshAuthToken: `response.data.data` → `response.data`

**測試用帳號 (從 UserSeeder.php):**
- Admin: username=`admin`, password=`password`, role=`admin`
- Chairman: username=`chairman`, password=`password`, role=`chairman`
- Member: username=`member1`, password=`password`, role=`member`
- Observer: username=`observer1`, password=`password`, role=`observer`

**後端 API 響應格式確認:**
- `/api/auth/login`: `{ success: true, data: { user, token, refresh_token, expires_in } }`
- `/api/auth/me`: `{ success: true, data: user }`
- `/api/auth/refresh`: `{ success: true, data: { token, refresh_token, expires_in } }`

前端已重啟，登入功能應該正常運作。
