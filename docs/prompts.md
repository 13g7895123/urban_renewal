1. 可否幫我調整一下啟用的sh，啟用前先確認容器是否有在運作，如果有運作則先關閉後再啟用，這樣只要留一支sh即可，develop與production都一樣
2. 目前登入頁出現這個錯誤error caught during app initialization ReferenceError: useAuthStore is not defined
3.

## 問題修復 (2025-10-24)

### 問題 1: 啟動腳本改善 ✅

**需求:**
調整啟用的sh，啟用前先確認容器是否有在運作，如果有運作則先關閉後再啟用，這樣只要留一支sh即可，develop與production都一樣

**已修復:**
1. ✅ 更新 `start-dev.sh` - 新增容器狀態檢查，如果發現開發環境容器正在運行會先自動停止
2. ✅ 更新 `start-prod.sh` - 新增容器狀態檢查，如果發現正式環境容器正在運行會先自動停止

**改善內容:**
- 腳本執行前會自動檢查是否有相同環境的容器正在運行
- 如果有運行中的容器，會先執行 `docker compose down` 停止
- 停止完成後才啟動新的容器
- 使用者不需要手動執行 stop 腳本，直接執行 start 腳本即可

**使用方式:**
```bash
# 開發環境 - 會自動停止現有容器後重新啟動
./start-dev.sh

# 正式環境 - 會自動停止現有容器後重新啟動
./start-prod.sh
```

### 問題 2: useAuthStore is not defined ✅

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
