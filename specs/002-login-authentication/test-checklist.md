# 登入認證功能測試檢查清單

**Feature**: 002-login-authentication
**Last Updated**: 2025-10-23
**Tester**: _____________
**Test Environment**: Development (localhost)

## 測試環境準備

### 前置條件
- [ ] Docker 容器運行正常
  - [ ] Backend: http://localhost:9228 可訪問
  - [ ] Frontend: http://localhost:4001 可訪問
  - [ ] Database: localhost:9328 連線正常
- [ ] 資料庫已執行 migrations
- [ ] 測試帳號已建立（執行 UserSeeder）
- [ ] 瀏覽器清除 localStorage 和 cookies

### 測試帳號確認
- [ ] admin / password (role: admin)
- [ ] chairman / password (role: chairman)
- [ ] member1 / password (role: member)
- [ ] observer1 / password (role: observer)

---

## Story 1: Admin 登入情境測試

### ✅ Test Case 1.1: Admin 正常登入

**測試步驟：**
1. [ ] 開啟瀏覽器到 http://localhost:4001/login
2. [ ] 在「帳號」欄位輸入 `admin`
3. [ ] 在「密碼」欄位輸入 `password`
4. [ ] 點擊「登入」按鈕

**預期結果：**
- [ ] 顯示綠色 toast 訊息：「登入成功，歡迎回來，系統管理員！」
- [ ] 瀏覽器自動導向 `/tables/urban-renewal`
- [ ] 網址列顯示 `http://localhost:4001/tables/urban-renewal`
- [ ] 頁面標題顯示「更新會管理」
- [ ] 可以看到「新建更新會」和「分配更新會」按鈕
- [ ] 導航選單顯示管理員可用功能

**後端驗證：**
- [ ] 檢查 localStorage 中有 `auth_token`
- [ ] 檢查 localStorage 中有 `auth_user`
- [ ] user 物件包含正確欄位（id, username, role, full_name...）
- [ ] user.role === 'admin'
- [ ] token 為有效的 JWT 格式

**資料庫驗證：**
```sql
-- 執行以下 SQL 確認
SELECT username, role, last_login_at, login_attempts, locked_until
FROM users WHERE username = 'admin';
```
- [ ] last_login_at 已更新為當前時間
- [ ] login_attempts 為 0

**API 請求驗證（開發者工具 Network）：**
- [ ] POST /api/auth/login 回應 200
- [ ] 回應 body 包含 success: true
- [ ] 回應 body 包含 data.token
- [ ] 回應 body 包含 data.refresh_token
- [ ] 回應 body 包含 data.user

---

### ❌ Test Case 1.2: Admin 使用錯誤密碼登入

**測試步驟：**
1. [ ] 確保已登出（localStorage 已清空）
2. [ ] 開啟 http://localhost:4001/login
3. [ ] 輸入帳號 `admin`
4. [ ] 輸入錯誤密碼 `wrong_password`
5. [ ] 點擊「登入」按鈕

**預期結果：**
- [ ] 顯示紅色 toast 錯誤訊息：「登入失敗 - 帳號或密碼錯誤」
- [ ] 停留在登入頁面
- [ ] localStorage 中沒有 token
- [ ] 帳號和密碼欄位清空或保持原值

**後端驗證：**
- [ ] POST /api/auth/login 回應 401
- [ ] 回應 body 包含 success: false
- [ ] 回應 body 包含 error.message

**資料庫驗證：**
```sql
SELECT username, login_attempts FROM users WHERE username = 'admin';
```
- [ ] login_attempts 增加 1

---

### 🔒 Test Case 1.3: Admin 帳號被鎖定

**測試準備：**
```sql
-- 手動設定帳號鎖定
UPDATE users
SET login_attempts = 5,
    locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
WHERE username = 'admin';
```

**測試步驟：**
1. [ ] 執行上述 SQL 鎖定帳號
2. [ ] 確認 locked_until 時間在未來
3. [ ] 嘗試使用正確密碼登入 `admin` / `password`

**預期結果：**
- [ ] 顯示紅色錯誤訊息：「帳號已被鎖定，請稍後再試」
- [ ] 無法登入
- [ ] localStorage 沒有 token

**清理：**
```sql
-- 解鎖帳號供後續測試
UPDATE users
SET login_attempts = 0,
    locked_until = NULL
WHERE username = 'admin';
```
- [ ] 執行清理 SQL

---

### ⏱️ Test Case 1.4: Admin Token 過期處理

**測試準備：**
此測試需要修改 token 或等待 24 小時，建議使用手動方式：
1. [ ] 正常登入 admin 帳號
2. [ ] 在 localStorage 中手動修改 token 為過期的 JWT
   ```javascript
   // 在瀏覽器 Console 執行
   localStorage.setItem('auth_token', 'expired_token_here')
   ```

**測試步驟：**
1. [ ] 嘗試存取需要認證的頁面（如 /tables/urban-renewal）
2. [ ] 或嘗試呼叫需要認證的 API

**預期結果：**
- [ ] 系統偵測 token 無效
- [ ] 自動重定向到 /login 頁面
- [ ] localStorage 中的 token 被清除
- [ ] 顯示訊息提示需要重新登入

---

### 🎯 Test Case 1.5: Admin 查看完整管理功能

**測試步驟：**
1. [ ] 使用 admin 帳號登入
2. [ ] 導航到首頁 /
3. [ ] 檢查所有功能卡片是否顯示

**預期結果 - 首頁顯示以下功能卡片：**
- [ ] 更新會管理 (/tables/urban-renewal)
- [ ] 會議管理 (/tables/meeting)
- [ ] 投票管理 (/tables/issue)
- [ ] 使用者基本資料變更 (/pages/user)

**預期結果 - 權限檢查：**
- [ ] 可以存取 /tables/urban-renewal（更新會管理）
- [ ] 可以存取 /tables/meeting（會議管理）
- [ ] 可以存取 /tables/issue（投票管理）
- [ ] 可以看到「新建」、「編輯」、「刪除」按鈕

**開發者工具檢查：**
```javascript
// 在 Console 執行
const { isAdmin, canManageUrbanRenewal, canManageUsers } = useRole()
console.log('isAdmin:', isAdmin.value)
console.log('canManageUrbanRenewal:', canManageUrbanRenewal.value)
console.log('canManageUsers:', canManageUsers.value)
```
- [ ] isAdmin === true
- [ ] canManageUrbanRenewal === true
- [ ] canManageUsers === true

---

## Story 2: User 登入情境測試

### ✅ Test Case 2.1: Member 正常登入

**測試步驟：**
1. [ ] 確保已登出
2. [ ] 開啟 http://localhost:4001/login
3. [ ] 輸入帳號 `member1`
4. [ ] 輸入密碼 `password`
5. [ ] 點擊「登入」按鈕

**預期結果：**
- [ ] 顯示綠色 toast 訊息：「登入成功，歡迎回來，地主成員1！」
- [ ] 瀏覽器自動導向 `/tables/meeting`
- [ ] 可以看到會議列表
- [ ] 導航選單只顯示會員可用功能

**後端驗證：**
- [ ] localStorage 中有 auth_token
- [ ] user.role === 'member'
- [ ] user.username === 'member1'
- [ ] user.full_name === '地主成員1'

**權限檢查：**
```javascript
const { isMember, canVote, canManageUsers } = useRole()
console.log('isMember:', isMember.value)
console.log('canVote:', canVote.value)
console.log('canManageUsers:', canManageUsers.value)
```
- [ ] isMember === true
- [ ] canVote === true
- [ ] canManageUsers === false

---

### 👔 Test Case 2.2: Chairman 登入並管理會議

**測試步驟：**
1. [ ] 登出當前使用者
2. [ ] 使用 `chairman` / `password` 登入

**預期結果：**
- [ ] 成功登入並導向 `/tables/meeting`
- [ ] 顯示「新建會議」按鈕
- [ ] 可以看到會議管理功能
- [ ] user.role === 'chairman'

**權限檢查：**
```javascript
const { isChairman, canManageMeetings, canManageUsers } = useRole()
```
- [ ] isChairman === true
- [ ] canManageMeetings === true
- [ ] canManageUsers === false

**權限邊界測試：**
- [ ] 嘗試存取 /pages/user（使用者管理）
- [ ] 應該被重定向到 /unauthorized 或首頁
- [ ] 顯示無權限訊息

---

### 👁️ Test Case 2.3: Observer 登入唯讀模式

**測試步驟：**
1. [ ] 使用 `observer1` / `password` 登入

**預期結果：**
- [ ] 成功登入並導向首頁 `/`
- [ ] user.role === 'observer'
- [ ] 可以查看會議資訊
- [ ] 可以查看投票結果

**權限檢查：**
```javascript
const { isObserver, canVote, canManageMeetings } = useRole()
```
- [ ] isObserver === true
- [ ] canVote === false
- [ ] canManageMeetings === false

**UI 檢查：**
- [ ] 看不到「投票」按鈕
- [ ] 看不到「編輯」按鈕
- [ ] 看不到「新建」按鈕
- [ ] 所有功能僅供查看

---

### 🚫 Test Case 2.4: User 權限邊界測試

**測試步驟：**
1. [ ] 使用 `member1` 登入
2. [ ] 登入成功後，直接在網址列輸入 `/tables/urban-renewal`
3. [ ] 按 Enter 嘗試存取

**預期結果：**
- [ ] 無法存取該頁面
- [ ] 被重定向到 `/unauthorized` 頁面
- [ ] 顯示「無權限訪問」訊息
- [ ] 顯示「返回上一頁」和「返回首頁」按鈕

**額外測試：**
使用不同角色測試以下路由：

| 路由 | Admin | Chairman | Member | Observer |
|------|-------|----------|--------|----------|
| /tables/urban-renewal | [ ] ✅ | [ ] ❌ | [ ] ❌ | [ ] ❌ |
| /tables/meeting | [ ] ✅ | [ ] ✅ | [ ] ✅ | [ ] ✅ |
| /pages/user | [ ] ✅ | [ ] ❌ | [ ] ❌ | [ ] ❌ |

---

### 🚪 Test Case 2.5: User 登出流程

**測試步驟：**
1. [ ] 使用任何帳號登入（建議 member1）
2. [ ] 確認已成功登入（可看到使用者資訊）
3. [ ] 點擊「登出」按鈕（或導航選單中的登出選項）

**預期結果：**
- [ ] 顯示確認登出訊息（如有）
- [ ] 系統調用 /api/auth/logout API
- [ ] localStorage 中的 auth_token 被清除
- [ ] localStorage 中的 auth_user 被清除
- [ ] 自動重定向到 /login 頁面

**後端驗證：**
- [ ] POST /api/auth/logout 回應 200
- [ ] 回應包含 success: true

**資料庫驗證：**
```sql
SELECT * FROM user_sessions
WHERE user_id = (SELECT id FROM users WHERE username = 'member1')
ORDER BY created_at DESC LIMIT 1;
```
- [ ] is_active 被設為 0

**Token 失效驗證：**
1. [ ] 複製登出前的 token
2. [ ] 使用該 token 嘗試調用 API：
   ```bash
   curl -X GET http://localhost:9228/api/auth/me \
     -H "Authorization: Bearer YOUR_OLD_TOKEN"
   ```
3. [ ] 應該回應 401 Unauthorized

---

## 角色權限測試頁面

### Test Case: 角色權限檢查頁面

**測試步驟：**
1. [ ] 使用不同角色登入
2. [ ] 訪問 http://localhost:4001/test-role
3. [ ] 檢查權限顯示是否正確

**測試每個角色：**

#### Admin 帳號測試
- [ ] 角色檢查顯示：✅ 管理員
- [ ] 權限檢查：
  - [ ] ✅ 管理更新會
  - [ ] ✅ 管理會議
  - [ ] ✅ 投票
  - [ ] ✅ 管理用戶
  - [ ] ✅ 管理系統設定

#### Chairman 帳號測試
- [ ] 角色檢查顯示：✅ 理事長
- [ ] 權限檢查：
  - [ ] ✅ 管理更新會
  - [ ] ✅ 管理會議
  - [ ] ✅ 投票
  - [ ] ❌ 管理用戶
  - [ ] ❌ 管理系統設定

#### Member 帳號測試
- [ ] 角色檢查顯示：✅ 會員
- [ ] 權限檢查：
  - [ ] ❌ 管理更新會
  - [ ] ❌ 管理會議
  - [ ] ✅ 投票
  - [ ] ❌ 管理用戶
  - [ ] ❌ 管理系統設定

#### Observer 帳號測試
- [ ] 角色檢查顯示：✅ 觀察員
- [ ] 權限檢查：
  - [ ] ❌ 管理更新會
  - [ ] ❌ 管理會議
  - [ ] ❌ 投票
  - [ ] ❌ 管理用戶
  - [ ] ❌ 管理系統設定

---

## API 測試（使用 curl）

### API Test 1: Admin 登入
```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' \
  | jq
```
- [ ] 回應 200
- [ ] success === true
- [ ] 包含 token
- [ ] 包含 user 物件

### API Test 2: 錯誤密碼
```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"wrong"}' \
  | jq
```
- [ ] 回應 401
- [ ] success === false
- [ ] 包含 error 物件

### API Test 3: 取得使用者資訊
```bash
# 先登入取得 token
TOKEN=$(curl -s -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' \
  | jq -r '.data.token')

# 使用 token 取得使用者資訊
curl -X GET http://localhost:9228/api/auth/me \
  -H "Authorization: Bearer $TOKEN" \
  | jq
```
- [ ] 回應 200
- [ ] 包含使用者完整資訊

### API Test 4: 登出
```bash
curl -X POST http://localhost:9228/api/auth/logout \
  -H "Authorization: Bearer $TOKEN" \
  | jq
```
- [ ] 回應 200
- [ ] success === true

---

## 安全性測試

### Security Test 1: SQL Injection
```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin'\'' OR '\''1'\''='\''1","password":"anything"}' \
  | jq
```
- [ ] 應該回應 401（不應該繞過驗證）
- [ ] 沒有資料庫錯誤訊息洩漏

### Security Test 2: XSS 嘗試
```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"<script>alert('\''XSS'\'')</script>","password":"test"}' \
  | jq
```
- [ ] 輸入被正確過濾或編碼
- [ ] 不會執行惡意腳本

### Security Test 3: 暴力破解防護
連續嘗試錯誤密碼 5 次：
```bash
for i in {1..5}; do
  curl -X POST http://localhost:9228/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{"username":"admin","password":"wrong'$i'"}' \
    | jq '.error.message'
  sleep 1
done

# 第 6 次嘗試
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' \
  | jq
```
- [ ] 第 5 次失敗後，帳號被鎖定
- [ ] 第 6 次即使密碼正確也無法登入
- [ ] 顯示帳號鎖定訊息

---

## 效能測試

### Performance Test 1: 登入回應時間
使用 curl 測量回應時間：
```bash
time curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' \
  -o /dev/null -s -w '%{time_total}\n'
```
- [ ] 回應時間 < 500ms（95th percentile）
- [ ] 記錄實際時間：_____ ms

### Performance Test 2: Token 驗證效能
```bash
TOKEN=$(curl -s -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' \
  | jq -r '.data.token')

time curl -X GET http://localhost:9228/api/auth/me \
  -H "Authorization: Bearer $TOKEN" \
  -o /dev/null -s -w '%{time_total}\n'
```
- [ ] Token 驗證 < 100ms
- [ ] 記錄實際時間：_____ ms

---

## 測試總結

### 測試完成度
- 總測試項目數：_____
- 通過項目數：_____
- 失敗項目數：_____
- 跳過項目數：_____
- 通過率：_____%

### 發現的問題
1. _________________________________
2. _________________________________
3. _________________________________

### 建議改進事項
1. _________________________________
2. _________________________________
3. _________________________________

### 測試結論
- [ ] ✅ 通過所有測試，可以發布
- [ ] ⚠️ 有小問題，但不影響發布
- [ ] ❌ 有重大問題，需要修復後重新測試

---

**測試人員簽名**：_____________
**測試日期**：_____________
**測試環境**：Development
**備註**：_________________________________
