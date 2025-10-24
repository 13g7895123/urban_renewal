1. 幫我調整一下develop的後端docker，我希望開發階段不要用COPY，用volumn的方式加速開發
2. 目前登入頁出現這個錯誤error caught during app initialization ReferenceError: useAuthStore is not defined
3.

## 問題修復 (2025-10-24)

### 問題 1: 開發環境使用 Volume 加速開發 ✅

**需求:**
調整develop的後端docker，開發階段不要用COPY，用volume的方式加速開發

**已修復:**
1. ✅ 建立 `backend/Dockerfile.dev` - 專門用於開發環境的 Dockerfile
2. ✅ 更新 `docker-compose.dev.yml` - 配置使用 Dockerfile.dev 和 volume 掛載
3. ✅ 修復 `backend/app/Config/Cors.php` - 修正遺漏的逗號語法錯誤

**改善內容:**

**Dockerfile.dev 優化:**
- 只 COPY composer.json 和 composer.lock (不是整個專案)
- 安裝開發依賴 (包含 dev dependencies)
- 應用程式檔案透過 volume 掛載，不使用 COPY

**docker-compose.dev.yml 配置:**
```yaml
volumes:
  - ./backend:/var/www/html          # 掛載整個專案目錄
  - /var/www/html/vendor             # 保護 vendor 目錄不被覆蓋
```

**開發優勢:**
- ✅ 程式碼修改立即生效，無需重建容器
- ✅ 加快開發迭代速度
- ✅ 保留 vendor 目錄，避免重複安裝依賴
- ✅ 開發體驗更流暢

**測試結果:**
- 修改 Cors.php 後立即生效，無需重建
- API 請求正常響應
- Volume 掛載配置正確

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
