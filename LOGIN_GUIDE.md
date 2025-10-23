# 登入功能說明文件

## 測試帳號

本系統預留了兩個主要角色的測試帳號：

### 1. 管理員 (Admin)
- **帳號**: `admin`
- **密碼**: `password`
- **角色**: admin
- **權限**: 完整系統管理權限

### 2. 一般用戶 (User/Member)
- **帳號**: `member1`
- **密碼**: `password`
- **角色**: member
- **權限**: 一般會員權限

### 其他可用帳號
- **理事長**: `chairman` / `password` (role: chairman)
- **觀察員**: `observer1` / `password` (role: observer)

## 登入流程

### 前端登入頁面
- URL: `http://localhost:4001/login`
- 功能：
  - 使用者名稱/密碼輸入
  - 密碼顯示/隱藏切換
  - 登入按鈕（含載入狀態）
  - 登入成功後重定向至 `/tables/urban-renewal`

### 後端 API
- **登入 API**: `POST /api/auth/login`
- **請求格式**:
  ```json
  {
    "username": "admin",
    "password": "password"
  }
  ```
- **回應格式**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": "1",
        "username": "admin",
        "email": "admin@example.com",
        "role": "admin",
        "full_name": "系統管理員",
        ...
      },
      "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
      "refresh_token": "d6d6ab933d64cb1e568...",
      "expires_in": 86400
    },
    "message": "登入成功"
  }
  ```

## API 測試範例

### 測試 Admin 登入
```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

### 測試 Member 登入
```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"member1","password":"password"}'
```

### 測試認證狀態
```bash
# 使用登入後取得的 token
curl -X GET http://localhost:9228/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 認證機制

### Token 管理
- **Token 類型**: JWT (JSON Web Token)
- **有效期限**: 24 小時 (86400 秒)
- **Refresh Token**: 7 天有效期
- **儲存位置**:
  - 前端: localStorage (`auth_token`, `auth_user`)
  - 後端: `user_sessions` 資料表

### 安全機制
1. **密碼加密**: 使用 PHP `password_hash()` (bcrypt)
2. **登入失敗處理**:
   - 記錄失敗次數
   - 5 次失敗後鎖定帳號 30 分鐘
3. **Token 驗證**: JWT 簽名驗證
4. **Session 管理**: 每次登入清除舊 session

## 前端認證流程

### 登入流程 (frontend/pages/login.vue)
1. 使用者輸入帳號密碼
2. 呼叫 `useAuth().login()`
3. 儲存 token 和使用者資料到 localStorage
4. 設定 API 請求 header (Authorization: Bearer token)
5. 重定向到 `/tables/urban-renewal`

### 認證 Middleware (frontend/middleware/auth.js)
- 檢查 localStorage 中的 token
- 驗證 token 有效性
- 未登入時重定向到 `/auth/login`

### Auth Store (frontend/stores/auth.js)
提供的方法：
- `login()`: 登入
- `logout()`: 登出
- `fetchUser()`: 取得當前使用者資料
- `initializeAuth()`: 初始化認證狀態
- `isLoggedIn`: 計算屬性，是否已登入
- `isAdmin`: 計算屬性，是否為管理員

## 角色權限

### 角色定義
1. **admin** (管理員)
   - 系統完整管理權限
   - 可管理所有更新會
   - 可管理使用者

2. **chairman** (理事長)
   - 更新會管理權限
   - 會議管理
   - 投票管理

3. **member** (會員)
   - 查看更新會資訊
   - 參與投票
   - 查看會議資訊

4. **observer** (觀察員)
   - 僅查看權限
   - 無投票權

## 資料庫結構

### users 表
- `id`: 使用者 ID
- `username`: 使用者名稱 (唯一)
- `email`: 電子信箱 (唯一)
- `password_hash`: 密碼雜湊值
- `role`: 角色 (admin/chairman/member/observer)
- `urban_renewal_id`: 所屬更新會 ID
- `property_owner_id`: 關聯所有權人 ID
- `full_name`: 真實姓名
- `is_active`: 是否啟用
- `last_login_at`: 最後登入時間
- `login_attempts`: 登入失敗次數
- `locked_until`: 帳號鎖定至

### user_sessions 表
- `id`: Session ID
- `user_id`: 使用者 ID
- `session_token`: JWT Token
- `refresh_token`: Refresh Token
- `expires_at`: Token 過期時間
- `refresh_expires_at`: Refresh Token 過期時間
- `ip_address`: 登入 IP
- `user_agent`: 瀏覽器資訊
- `is_active`: Session 是否有效

## 已完成功能

✅ 後端登入 API (CodeIgniter 4)
✅ JWT Token 生成與驗證
✅ Refresh Token 機制
✅ 登入失敗處理與帳號鎖定
✅ 前端登入頁面 (Nuxt 3 + Nuxt UI)
✅ Auth Store (Pinia)
✅ Auth Middleware
✅ Token 儲存與管理
✅ 測試帳號 Seeder
✅ API 測試驗證
✅ 角色權限 Composable (useRole)
✅ 角色權限 Middleware
✅ 登入後根據角色重定向
✅ 無權限訪問頁面

## 角色權限控制

### useRole Composable
提供以下功能（位於 `frontend/composables/useRole.js`）：

**角色檢查方法：**
- `hasRole(roles)`: 檢查是否有指定角色
- `isAdmin`: 是否為管理員
- `isChairman`: 是否為理事長
- `isMember`: 是否為會員
- `isObserver`: 是否為觀察員

**權限檢查方法：**
- `canManageUrbanRenewal`: 可管理更新會（admin、chairman）
- `canManageMeetings`: 可管理會議（admin、chairman）
- `canVote`: 可投票（chairman、member）
- `canViewResults`: 可查看結果（所有登入用戶）
- `canManageUsers`: 可管理用戶（僅 admin）
- `canManageSettings`: 可管理系統設定（僅 admin）
- `canAccessUrbanRenewal(id)`: 可訪問特定更新會

**使用範例：**
```vue
<script setup>
const { isAdmin, canVote, getRoleDisplayName } = useRole()
</script>

<template>
  <div v-if="isAdmin">
    <!-- 只有管理員能看到的內容 -->
  </div>

  <UButton v-if="canVote" @click="submitVote">
    投票
  </UButton>

  <p>當前角色：{{ getRoleDisplayName() }}</p>
</template>
```

### Role Middleware
在頁面中使用角色權限保護（位於 `frontend/middleware/role.js`）：

```vue
<script setup>
// 只允許管理員訪問
definePageMeta({
  middleware: ['auth', 'role'],
  role: 'admin'
})

// 允許多個角色訪問
definePageMeta({
  middleware: ['auth', 'role'],
  role: ['admin', 'chairman']
})
</script>
```

### 登入後重定向規則
- **Admin**: `/tables/urban-renewal` （更新會管理）
- **Chairman/Member**: `/tables/meeting` （會議列表）
- **其他角色**: `/` （首頁）

## 待完善功能

⚠️ 需要在所有需要認證的頁面加上 middleware: ['auth'] 或 ['auth', 'role']
⚠️ 密碼重設功能（郵件發送）
⚠️ 個人資料修改頁面
⚠️ 密碼變更功能
⚠️ Session 自動續約機制

## 開發環境

- **後端**: http://localhost:9228
- **前端**: http://localhost:4001
- **資料庫**: MariaDB (localhost:9328)
- **phpMyAdmin**: http://localhost:9428

## 注意事項

1. 測試帳號密碼都是 `password`，請勿在正式環境使用
2. JWT Secret Key 設定在 `.env` 檔案中
3. Token 過期後需要使用 refresh token 更新或重新登入
4. 所有需要認證的頁面都應該加上 `definePageMeta({ middleware: ['auth'] })`
