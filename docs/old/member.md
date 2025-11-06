# 會員系統驗證機制文件

## 目錄
- [概述](#概述)
- [前端驗證機制](#前端驗證機制)
- [後端驗證機制](#後端驗證機制)
- [Token 機制](#token-機制)
- [Session 管理](#session-管理)
- [安全措施](#安全措施)
- [API 端點](#api-端點)
- [使用範例](#使用範例)

---

## 概述

本系統採用 **JWT (JSON Web Token)** 作為主要的認證機制，配合 **Refresh Token** 實現長效登入。前端使用 Nuxt 3 框架搭配 Pinia 狀態管理，後端使用 CodeIgniter 4 框架。

### 架構特點
- JWT-based 認證系統
- Access Token (24小時) + Refresh Token (7天)
- 基於角色的權限控制 (RBAC)
- 自動 Token 刷新機制
- 帳號安全保護（登入失敗鎖定）
- 完整的認證事件日誌

---

## 前端驗證機制

### 1. 認證狀態管理 (stores/auth.js)

使用 Pinia store 管理全域認證狀態：

**狀態變數：**
- `user`: 當前使用者資訊
- `token`: JWT access token
- `refreshToken`: Refresh token
- `tokenExpiresAt`: Token 過期時間
- `isLoggedIn`: 是否已登入（computed）
- `isAdmin`: 是否為管理員（computed）

**主要方法：**
- `login(credentials)`: 使用者登入
  - 發送登入請求到 `/api/auth/login`
  - 儲存 token 和使用者資訊到 localStorage
  - 計算 token 過期時間

- `logout(skipApiCall)`: 使用者登出
  - 呼叫 `/api/auth/logout` 使 session 失效
  - 清除 localStorage 中的認證資訊
  - 重定向到登入頁面

- `fetchUser()`: 獲取當前使用者資訊
  - 呼叫 `/api/auth/me`
  - 更新 user 狀態

- `refreshAuthToken()`: 刷新 access token
  - 使用 refresh_token 呼叫 `/api/auth/refresh`
  - 更新 token 和過期時間
  - Token 刷新失敗時自動登出

- `initializeAuth()`: 初始化認證狀態
  - 從 localStorage 恢復認證資訊
  - 僅在客戶端執行

**檔案位置：** `frontend/stores/auth.js`

---

### 2. API 請求處理 (composables/useApi.js)

提供統一的 API 請求接口，自動處理認證和錯誤：

**核心功能：**
- 自動附加 `Authorization: Bearer <token>` header
- 401 錯誤時自動刷新 token
- Token 刷新失敗後清除認證狀態並重定向
- 統一的錯誤處理和格式化

**請求方法：**
- `get(endpoint, params)`
- `post(endpoint, body)`
- `put(endpoint, body)`
- `patch(endpoint, body)`
- `delete(endpoint)`

**自動 Token 刷新流程：**
```javascript
1. API 請求返回 401 錯誤
2. 檢查是否有 refresh_token
3. 呼叫 /auth/refresh 取得新 token
4. 使用新 token 重試原請求
5. 刷新失敗則清除認證並重定向到登入頁
```

**檔案位置：** `frontend/composables/useApi.js`

---

### 3. 認證中介層 (middleware/auth.js)

路由級別的認證保護：

**檢查流程：**
```javascript
1. 初始化認證狀態（從 localStorage）
2. 檢查 isLoggedIn 狀態
3. 未登入 → 重定向到 /login
4. 已登入但無使用者資料 → 呼叫 fetchUser()
5. fetchUser 失敗 → 重定向到 /login
```

**使用方式：**
```javascript
// 在頁面中啟用認證保護
definePageMeta({
  middleware: ['auth']
})
```

**檔案位置：** `frontend/middleware/auth.js`

---

### 4. 角色權限中介層 (middleware/role.js)

檢查使用者是否擁有特定角色權限：

**支援功能：**
- 單一角色檢查
- 多角色檢查（陣列）
- 權限不足時重定向到 `/unauthorized`

**使用方式：**
```javascript
// 單一角色
definePageMeta({
  middleware: ['auth', 'role'],
  role: 'admin'
})

// 多個角色
definePageMeta({
  middleware: ['auth', 'role'],
  role: ['admin', 'chairman']
})
```

**檔案位置：** `frontend/middleware/role.js`

---

### 5. 認證插件 (plugins/auth.client.js)

應用啟動時自動初始化認證狀態：

**執行時機：** 僅在客戶端執行

**功能：**
- 從 localStorage 恢復認證狀態
- 清除無效的認證資料
- 提供全域 authStore 訪問

**檔案位置：** `frontend/plugins/auth.client.js`

---

## 後端驗證機制

### 1. 認證控制器 (AuthController.php)

處理所有認證相關的 API 請求：

#### API 端點：

**POST /api/auth/login** - 使用者登入
- 接收參數：`username`, `password`
- 驗證使用者帳號密碼
- 檢查帳號鎖定狀態
- 產生 JWT token 和 refresh token
- 儲存 session
- 記錄登入事件
- 返回：使用者資訊、token、refresh_token、expires_in

**POST /api/auth/logout** - 使用者登出
- 從 header 取得 token
- 使 session 失效（is_active = 0）
- 記錄登出事件

**POST /api/auth/refresh** - 刷新 token
- 接收參數：`refresh_token`
- 驗證 refresh token 是否有效
- 產生新的 access token 和 refresh token
- 更新 session
- 返回：新的 token、refresh_token、expires_in

**GET /api/auth/me** - 獲取當前使用者資訊
- 從 token 解析使用者 ID
- 返回使用者資訊（移除敏感欄位）

**POST /api/auth/forgot-password** - 忘記密碼
- 接收參數：`email`
- 產生重設令牌
- 發送重設密碼郵件（TODO）

**POST /api/auth/reset-password** - 重設密碼
- 接收參數：`token`, `password`, `password_confirm`
- 驗證重設令牌
- 更新密碼並清除令牌

**檔案位置：** `backend/app/Controllers/Api/AuthController.php`

---

### 2. JWT 認證過濾器 (JWTAuthFilter.php)

自動驗證所有需要認證的 API 請求：

**驗證流程：**
```php
1. 載入 auth helper
2. 呼叫 auth_validate_request() 驗證 token
3. Token 無效 → 返回 401 錯誤
4. Token 有效 → 將使用者資訊附加到 $request->user
```

**應用範圍：** 在 `app/Config/Filters.php` 中配置需要認證的路由

**檔案位置：** `backend/app/Filters/JWTAuthFilter.php`

---

### 3. 角色過濾器 (RoleFilter.php)

檢查使用者是否擁有指定角色：

**使用方式：**
```php
// 在 Routes.php 中設定
$routes->group('admin', ['filter' => 'role:admin'], function($routes) {
    // admin 專用路由
});

// 或多個角色
$routes->group('api', ['filter' => 'role:admin,chairman'], function($routes) {
    // admin 或 chairman 可訪問
});
```

**檔案位置：** `backend/app/Filters/RoleFilter.php`

---

### 4. 認證輔助函數 (auth_helper.php)

提供豐富的認證和權限檢查函數：

#### 核心函數：

**auth_validate_request($requiredRoles = [])**
- 驗證請求中的 JWT token
- 檢查 token 是否過期
- 驗證使用者是否啟用
- 可選的角色權限檢查
- 返回：使用者資料或 false

**auth_generate_token($user, $sessionId = null)**
- 產生 JWT access token
- Payload 包含：user_id, role, urban_renewal_id, property_owner_id
- 有效期：24 小時

**auth_generate_refresh_token($user)**
- 產生 refresh token
- 有效期：7 天

**auth_get_current_user()**
- 取得當前認證的使用者
- 等同於 auth_validate_request()

**auth_check_permission($permission, $user = null)**
- 檢查使用者是否擁有特定權限
- Admin 擁有所有權限

**auth_check_resource_scope($resourceUrbanRenewalId, $user = null)**
- 檢查使用者是否可存取特定 urban_renewal_id 的資源
- Admin 可存取所有資源
- 一般使用者只能存取其指派的 urban_renewal_id

#### 企業管理相關函數：

**auth_is_company_manager($user = null)**
- 檢查是否為企業管理者

**auth_is_company_user($user = null)**
- 檢查是否為企業使用者

**auth_is_general_user($user = null)**
- 檢查是否為一般使用者

**auth_can_manage_company($urbanRenewalId, $user = null)**
- 檢查是否可管理指定企業
- Admin 可管理所有企業
- 企業管理者只能管理自己的企業

**auth_can_manage_user($targetUser, $user = null)**
- 檢查是否可管理指定使用者
- 不能管理自己
- Admin 可管理所有使用者
- 企業管理者只能管理同企業的使用者

**檔案位置：** `backend/app/Helpers/auth_helper.php`

---

## Token 機制

### JWT Token 結構

**Access Token Payload:**
```json
{
  "iss": "urban-renewal-system",
  "aud": "urban-renewal-users",
  "iat": 1698765432,
  "exp": 1698851832,
  "user_id": 1,
  "username": "admin",
  "role": "admin",
  "urban_renewal_id": null
}
```

**Refresh Token Payload:**
```json
{
  "iss": "urban-renewal-system",
  "aud": "urban-renewal-users",
  "iat": 1698765432,
  "exp": 1699370232,
  "user_id": 1,
  "type": "refresh"
}
```

### Token 生命週期

| Token 類型 | 有效期 | 用途 |
|-----------|-------|------|
| Access Token | 24 小時 | API 請求認證 |
| Refresh Token | 7 天 | 刷新 access token |

### Token 儲存

**前端：**
- `localStorage.auth_token` - Access token
- `localStorage.auth_refresh_token` - Refresh token
- `localStorage.auth_token_expires_at` - Token 過期時間
- `localStorage.auth_user` - 使用者資訊

**後端：**
- `user_sessions` 資料表
- 記錄 session_token, refresh_token, expires_at 等

---

## Session 管理

### UserSessionModel

儲存使用者的登入 session：

**資料表欄位：**
- `id`: Session ID
- `user_id`: 使用者 ID
- `session_token`: JWT token（加密儲存）
- `refresh_token`: Refresh token
- `expires_at`: Token 過期時間
- `refresh_expires_at`: Refresh token 過期時間
- `ip_address`: 登入 IP
- `user_agent`: 瀏覽器資訊
- `is_active`: 是否啟用
- `created_at`: 建立時間
- `last_activity_at`: 最後活動時間

### Session 處理邏輯

**登入時：**
1. 清除該使用者的所有舊 session（設為 is_active = 0）
2. 建立新 session 記錄
3. 儲存 token、refresh_token、IP、User-Agent

**登出時：**
1. 將對應的 session 設為 is_active = 0

**Token 刷新時：**
1. 驗證 refresh_token 是否有效且未過期
2. 產生新的 token 和 refresh_token
3. 更新 session 記錄
4. 更新 last_activity_at

---

## 安全措施

### 1. 密碼安全

- 使用 PHP `password_hash()` 加密密碼
- 演算法：PASSWORD_DEFAULT (bcrypt)
- 不儲存明文密碼
- 密碼最小長度：6 字元

### 2. 登入失敗保護

**機制：**
- 追蹤登入失敗次數（`users.login_attempts`）
- 失敗 5 次後鎖定帳號 30 分鐘（`users.locked_until`）
- 登入成功後重置失敗次數

**流程：**
```
1. 使用者登入失敗 → login_attempts + 1
2. login_attempts >= 5 → 設定 locked_until
3. 檢查 locked_until 是否已過期
4. 登入成功 → 重置 login_attempts, locked_until
```

### 3. 認證事件記錄

記錄所有重要的認證活動：

**事件類型：**
- `login_success`: 登入成功
- `login_failure`: 登入失敗
- `logout`: 登出
- `token_refresh`: Token 刷新

**記錄內容：**
- 使用者 ID
- 事件類型
- 失敗原因（如：invalid_credentials, account_locked）
- 相關資料（JSON）
- IP 位址
- User-Agent
- 時間戳記

**資料表：** `authentication_events`

### 4. Token 安全

- JWT 使用密鑰簽章（HS256）
- 密鑰儲存在環境變數 `JWT_SECRET`
- Token 包含過期時間（exp claim）
- Refresh token 使用隨機生成的 64 字元字串

### 5. CORS 設定

- 允許的來源：配置於後端
- 允許的方法：GET, POST, PUT, DELETE, OPTIONS
- 允許的 Headers：Content-Type, Authorization, X-Requested-With

---

## API 端點

### 認證相關

| 方法 | 端點 | 說明 | 需要認證 |
|------|------|------|---------|
| POST | `/api/auth/login` | 使用者登入 | 否 |
| POST | `/api/auth/logout` | 使用者登出 | 是 |
| POST | `/api/auth/refresh` | 刷新 token | 否 |
| GET | `/api/auth/me` | 獲取當前使用者資訊 | 是 |
| POST | `/api/auth/forgot-password` | 忘記密碼 | 否 |
| POST | `/api/auth/reset-password` | 重設密碼 | 否 |

### 認證 Header 格式

```http
Authorization: Bearer <JWT_TOKEN>
```

---

## 使用範例

### 前端範例

#### 1. 使用者登入

```vue
<script setup>
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const credentials = ref({
  username: '',
  password: ''
})

const handleLogin = async () => {
  try {
    await authStore.login(credentials.value)
    // 登入成功，自動導向首頁或指定頁面
    navigateTo('/')
  } catch (error) {
    console.error('登入失敗:', error.message)
  }
}
</script>
```

#### 2. 使用者登出

```vue
<script setup>
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  // 自動重定向到登入頁
}
</script>
```

#### 3. 受保護的頁面

```vue
<script setup>
definePageMeta({
  middleware: ['auth']
})

const authStore = useAuthStore()
</script>

<template>
  <div>
    <h1>歡迎, {{ authStore.user?.name }}</h1>
  </div>
</template>
```

#### 4. 角色限制頁面

```vue
<script setup>
definePageMeta({
  middleware: ['auth', 'role'],
  role: 'admin'  // 僅管理員可訪問
})
</script>

<template>
  <div>
    <h1>管理員專區</h1>
  </div>
</template>
```

#### 5. API 請求

```vue
<script setup>
const { get, post } = useApi()

// GET 請求
const fetchData = async () => {
  const response = await get('/users')
  if (response.success) {
    console.log(response.data)
  }
}

// POST 請求
const createUser = async (userData) => {
  const response = await post('/users', userData)
  if (response.success) {
    console.log('使用者建立成功')
  } else {
    console.error('錯誤:', response.error.message)
  }
}
</script>
```

---

### 後端範例

#### 1. 在控制器中獲取當前使用者

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class MyController extends ResourceController
{
    public function index()
    {
        // 取得當前認證的使用者
        $user = auth_get_current_user();

        if (!$user) {
            return response_error('未授權', 401);
        }

        return response_success(['user' => $user]);
    }
}
```

#### 2. 檢查權限

```php
public function delete($id)
{
    $user = auth_get_current_user();

    // 檢查是否為管理員
    if (!auth_check_permission('delete_user', $user)) {
        return response_error('權限不足', 403);
    }

    // 執行刪除
    $this->userModel->delete($id);
    return response_success('刪除成功');
}
```

#### 3. 檢查資源範圍

```php
public function show($id)
{
    $resource = $this->model->find($id);

    // 檢查使用者是否可存取此資源
    if (!auth_check_resource_scope($resource['urban_renewal_id'])) {
        return response_error('無權存取此資源', 403);
    }

    return response_success($resource);
}
```

#### 4. 使用過濾器保護路由

```php
// app/Config/Filters.php

public $filters = [
    'jwtauth' => [
        'before' => [
            'api/users/*',
            'api/meetings/*',
            'api/votings/*'
        ]
    ],
    'role' => [
        'before' => [
            'api/admin/*' => ['role:admin'],
            'api/manager/*' => ['role:admin,manager']
        ]
    ]
];
```

#### 5. 手動驗證請求

```php
public function customAction()
{
    // 驗證是否為管理員或主委
    $user = auth_validate_request(['admin', 'chairman']);

    if (!$user) {
        return response_error('需要管理員或主委權限', 403);
    }

    // 執行操作
    return response_success('操作成功');
}
```

---

## 常見問題

### Q1: Token 過期後會發生什麼？

**A:** 當 access token 過期時：
1. API 請求返回 401 錯誤
2. 前端 `useApi` 自動使用 refresh token 刷新
3. 刷新成功後，重試原請求
4. 刷新失敗，清除認證並重定向到登入頁

### Q2: 如何檢查使用者是否已登入？

**前端：**
```javascript
const authStore = useAuthStore()
if (authStore.isLoggedIn) {
  // 已登入
}
```

**後端：**
```php
$user = auth_get_current_user();
if ($user) {
  // 已登入
}
```

### Q3: 如何限制頁面只有管理員可訪問？

```vue
<script setup>
definePageMeta({
  middleware: ['auth', 'role'],
  role: 'admin'
})
</script>
```

### Q4: 如何在後端取得當前使用者的 urban_renewal_id？

```php
$user = auth_get_current_user();
$urbanRenewalId = $user['urban_renewal_id'] ?? null;

// 或從 JWT token 直接取得
$token = get_token_from_header();
$decoded = JWT::decode($token, new Key($jwtKey, 'HS256'));
$urbanRenewalId = $decoded->urban_renewal_id;
```

### Q5: 如何手動刷新 token？

**前端：**
```javascript
const authStore = useAuthStore()
await authStore.refreshAuthToken()
```

**後端：**
發送 POST 請求到 `/api/auth/refresh`，附帶 `refresh_token`

---

## 相關檔案索引

### 前端
- `frontend/stores/auth.js` - 認證狀態管理
- `frontend/composables/useAuth.js` - 認證 API composable
- `frontend/composables/useApi.js` - API 請求處理
- `frontend/middleware/auth.js` - 認證中介層
- `frontend/middleware/role.js` - 角色權限中介層
- `frontend/plugins/auth.client.js` - 認證初始化插件

### 後端
- `backend/app/Controllers/Api/AuthController.php` - 認證控制器
- `backend/app/Filters/JWTAuthFilter.php` - JWT 認證過濾器
- `backend/app/Filters/RoleFilter.php` - 角色過濾器
- `backend/app/Helpers/auth_helper.php` - 認證輔助函數
- `backend/app/Models/UserModel.php` - 使用者模型
- `backend/app/Models/UserSessionModel.php` - Session 模型
- `backend/app/Models/AuthenticationEventModel.php` - 認證事件模型

---

## 版本資訊

- **文件版本：** 1.0.0
- **最後更新：** 2025-11-01
- **適用系統版本：** Urban Renewal System v1.0
