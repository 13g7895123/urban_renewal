# 認證與授權指南

## 概述

本系統實現了完整的基於角色的訪問控制 (RBAC)，包含：
- 用戶登入/登出
- 基於角色的頁面訪問控制
- 動態側邊欄選單
- 用戶資訊顯示

---

## 用戶角色

系統支援四種用戶角色：

| 角色 | 代碼 | 中文名稱 | 權限等級 |
|------|------|---------|---------|
| Admin | `admin` | 系統管理員 | 最高 |
| Chairman | `chairman` | 主任委員 | 高 |
| Member | `member` | 地主成員 | 中 |
| Observer | `observer` | 觀察員 | 低 |

---

## 登入流程

### 1. 登入頁面
路徑: `/login`

### 2. 登入後跳轉

根據用戶角色自動跳轉到對應頁面：

```javascript
// Admin (管理員)
→ /tables/urban-renewal  // 更新會管理

// Chairman (主任委員) / Member (地主成員)
→ /tables/meeting  // 會議管理

// 其他角色
→ /  // 首頁
```

### 3. 認證狀態管理

使用 Pinia store (`useAuthStore`) 管理認證狀態：
- `user` - 用戶資訊
- `token` - JWT 令牌
- `refreshToken` - 刷新令牌
- `isLoggedIn` - 登入狀態

---

## 頁面權限控制

### Middleware 配置

#### Auth Middleware
所有內頁都應添加 `auth` middleware：

```vue
<script setup>
definePageMeta({
  middleware: 'auth'
})
</script>
```

#### Role Middleware
需要特定角色才能訪問的頁面：

```vue
<script setup>
definePageMeta({
  middleware: ['auth', 'role'],
  role: 'admin'  // 單一角色
})
</script>
```

或允許多個角色：

```vue
<script setup>
definePageMeta({
  middleware: ['auth', 'role'],
  role: ['admin', 'chairman', 'member']  // 多個角色
})
</script>
```

### 頁面權限矩陣

| 頁面 | Admin | Chairman | Member | Observer |
|------|-------|----------|--------|----------|
| 首頁 (/) | ✅ | ✅ | ✅ | ✅ |
| 更新會管理 | ✅ | ❌ | ❌ | ❌ |
| 會議管理 | ✅ | ✅ | ✅ | ❌ |
| 投票管理 | ✅ | ✅ | ✅ | ❌ |
| 商城 | ✅ | ✅ | ✅ | ❌ |
| 購買紀錄 | ✅ | ✅ | ✅ | ❌ |
| 使用者資料 | ✅ | ✅ | ✅ | ✅ |
| 企業管理 | ✅ | ❌ | ❌ | ❌ |

---

## 側邊欄選單

### 動態選單顯示

側邊欄選單會根據用戶角色自動過濾：

```javascript
// layouts/main.vue
const menuItems = [
  {
    path: '/',
    icon: 'heroicons:home',
    label: '首頁',
    roles: ['admin', 'chairman', 'member', 'observer']
  },
  {
    path: '/tables/urban-renewal',
    icon: 'heroicons:building-office-2',
    label: '更新會管理',
    roles: ['admin']  // 只有 admin 可見
  },
  // ...
]
```

### 用戶資訊顯示

側邊欄頂部顯示：
- 用戶頭像 (圖標)
- 用戶姓名 (`full_name` 或 `username`)
- 角色標籤 (中文顯示)

---

## 登出功能

### 使用方式

點擊右上角「登出」按鈕：

```javascript
const handleLogout = async () => {
  await authStore.logout()
  // 自動跳轉到 /login
}
```

### 登出流程

1. 調用後端 API `/auth/logout`
2. 清除本地認證狀態
3. 清除 localStorage
4. 跳轉到登入頁面

---

## 測試帳號

開發環境提供以下測試帳號：

| 角色 | 帳號 | 密碼 | 用途 |
|------|------|------|------|
| Admin | `admin` | `password` | 測試管理員功能 |
| Chairman | `chairman` | `password` | 測試主任委員功能 |
| Member | `member1` | `password` | 測試地主成員功能 |
| Observer | `observer1` | `password` | 測試觀察員功能 |

---

## 無權限處理

當用戶嘗試訪問無權限頁面時：

1. Role middleware 攔截請求
2. 重定向到 `/unauthorized`
3. 顯示友善的錯誤訊息
4. 提供返回上一頁或首頁的按鈕

---

## API 整合

### 登入 API

```javascript
// POST /api/auth/login
{
  "username": "admin",
  "password": "password"
}

// Response
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "...",
    "refresh_token": "...",
    "expires_in": 86400
  }
}
```

### 驗證 Token

```javascript
// GET /api/auth/me
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "data": {
    "id": "1",
    "username": "admin",
    "role": "admin",
    "full_name": "系統管理員"
  }
}
```

### 登出 API

```javascript
// POST /api/auth/logout
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "message": "登出成功"
}
```

---

## 開發指南

### 添加新頁面

1. 創建頁面組件
2. 添加 middleware 保護
3. 設定角色要求（如需要）
4. 更新側邊欄選單配置

範例：

```vue
<!-- pages/new-feature.vue -->
<template>
  <NuxtLayout name="main">
    <template #title>新功能</template>
    <!-- 頁面內容 -->
  </NuxtLayout>
</template>

<script setup>
definePageMeta({
  middleware: ['auth', 'role'],
  role: ['admin', 'chairman'],
  layout: false
})
</script>
```

### 添加選單項目

更新 `layouts/main.vue` 的 `menuItems` 陣列：

```javascript
{
  path: '/new-feature',
  icon: 'heroicons:star',
  label: '新功能',
  roles: ['admin', 'chairman']
}
```

---

## 安全性考量

1. **Token 儲存** - JWT token 存儲在 localStorage
2. **Token 驗證** - 每次頁面切換時驗證 token 有效性
3. **角色檢查** - 前後端雙重檢查用戶角色
4. **自動登出** - Token 過期時自動登出
5. **HTTPS** - 生產環境應使用 HTTPS

---

## 常見問題

### Q: 如何添加新角色？
A: 
1. 更新後端 UserModel 的角色列表
2. 更新前端 `layouts/main.vue` 的 `roleMap`
3. 配置新角色的選單權限

### Q: 如何自定義登入跳轉？
A: 修改 `pages/login.vue` 的 `handleLogin` 函數

### Q: 如何處理 Token 過期？
A: authStore 自動處理，過期時會重定向到登入頁

### Q: 可以同時支援多種認證方式嗎？
A: 可以，在 authStore 中添加新的登入方法即可

---

更新時間: 2025-10-24
版本: 1.0.0
