# 登入 API 格式說明文件

## 快速確認

✅ **前後端完全一致，沒有問題！**

---

## 完整技術說明

### 1. 前端實現

#### 登入頁面 (login.vue)
```vue
<template>
  <UInput v-model="username" placeholder="帳號" />
  <UInput v-model="password" type="password" placeholder="密碼" />
  <UButton @click="handleLogin">登入</UButton>
</template>

<script setup>
const handleLogin = async () => {
  const authStore = useAuthStore()
  await authStore.login({
    username: username.value,
    password: password.value
  })
}
</script>
```

#### 認證 Store (auth.js)
```javascript
const login = async (credentials) => {
  const loginData = {
    username: credentials.username,  // ← 欄位名稱
    password: credentials.password   // ← 欄位名稱
  }
  
  const { post } = useApi()
  const response = await post('/auth/login', loginData)
  // 處理回應...
}
```

#### API Composable (useApi.js)
```javascript
const post = async (endpoint, body = {}) => {
  return await apiRequest(endpoint, {
    method: 'POST',           // ← HTTP 方法
    body                      // ← 自動序列化為 JSON
  })
}

const apiRequest = async (endpoint, options = {}) => {
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',  // ← JSON 格式
      'Accept': 'application/json'
    },
    ...options
  }
  
  return await $fetch(endpoint, defaultOptions)
}
```

### 2. 後端實現

#### 控制器 (AuthController.php)
```php
public function login()
{
    // 驗證規則
    $rules = [
        'username' => 'required|max_length[100]',  // ← 欄位名稱
        'password' => 'required|min_length[6]'     // ← 欄位名稱
    ];

    // 解析 JSON 請求
    $username = $this->request->getJSON()->username;  // ← 讀取 JSON
    $password = $this->request->getJSON()->password;  // ← 讀取 JSON

    // 驗證用戶...
    // 產生 token...
    
    return $this->respond([
        'success' => true,
        'data' => [
            'user' => $user,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'expires_in' => 86400
        ]
    ]);
}
```

---

## HTTP 請求詳細解析

### 實際發送的 HTTP 請求

```http
POST /api/auth/login HTTP/1.1
Host: localhost:9228
Content-Type: application/json
Accept: application/json
Content-Length: 45

{"username":"admin","password":"password"}
```

### 請求組成部分

| 部分 | 值 | 說明 |
|-----|---|------|
| Method | `POST` | HTTP 方法 |
| URL | `/api/auth/login` | API 端點 |
| Content-Type | `application/json` | 請求格式為 JSON |
| Accept | `application/json` | 期望回應格式為 JSON |
| Body | `{"username":"admin","password":"password"}` | JSON 格式的請求資料 |

### 後端回應

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "username": "admin",
      "email": "admin@example.com",
      "role": "admin",
      "full_name": "系統管理員"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "4e073b6e0a124c7af06...",
    "expires_in": 86400
  },
  "message": "登入成功"
}
```

---

## 為什麼使用 POST + JSON？

### POST 方法的優點

#### ✅ 安全性
```
錯誤方式 (GET):
GET /auth/login?username=admin&password=123456

問題:
- 密碼在 URL 中可見
- 瀏覽器歷史記錄會保存
- 伺服器日誌會記錄
- 代理伺服器可能快取
```

```
正確方式 (POST):
POST /auth/login
Body: {"username":"admin","password":"password"}

優點:
- 密碼在請求 body 中
- 不會出現在 URL
- 不會被日誌記錄
- 使用 HTTPS 時完全加密
```

#### ✅ RESTful 標準
```
POST   /auth/login     → 創建 session (登入)
POST   /auth/logout    → 終止 session (登出)
POST   /auth/refresh   → 刷新 token
GET    /auth/me        → 獲取當前用戶資訊
```

#### ✅ JSON 格式優點
```javascript
// 支援複雜資料結構
{
  "username": "admin",
  "password": "password",
  "remember_me": true,
  "device": {
    "type": "desktop",
    "browser": "Chrome"
  }
}

// 前後端都容易解析
// 支援多語言
// 標準化格式
```

---

## 測試方法

### 1. 使用 curl 測試

```bash
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "admin",
    "password": "password"
  }'
```

### 2. 使用 Postman 測試

```
Method: POST
URL: http://localhost:9228/api/auth/login
Headers:
  Content-Type: application/json
  Accept: application/json
Body (raw JSON):
{
  "username": "admin",
  "password": "password"
}
```

### 3. 瀏覽器開發者工具

```
1. 打開開發者工具 (F12)
2. 切換到 Network 標籤
3. 在登入頁面輸入帳密並登入
4. 查看 "login" 請求
5. 確認 Request Method: POST
6. 確認 Request Headers: Content-Type: application/json
7. 確認 Request Payload: {"username":"...","password":"..."}
```

---

## 常見問題 Q&A

### Q1: 為什麼不用 GET？
**A:** GET 方法會將參數放在 URL 中，密碼會暴露，不安全。

### Q2: 為什麼不用 application/x-www-form-urlencoded？
**A:** 可以用，但 JSON 格式更現代、更靈活、更易於處理複雜資料。

### Q3: POST 和 JSON 是什麼關係？
**A:** 
- POST 是 HTTP 方法（怎麼傳）
- JSON 是資料格式（傳什麼）
- 可以組合使用：POST + JSON

### Q4: 後端怎麼知道是 JSON？
**A:** 透過 `Content-Type: application/json` header 告訴後端。

### Q5: 前端需要手動設定 header 嗎？
**A:** 不需要，`useApi()` 已經自動設定。

---

## 技術棧對照

| 層級 | 技術 | 負責內容 |
|-----|------|---------|
| 前端 UI | Vue 3 + Nuxt UI | 登入表單界面 |
| 前端狀態 | Pinia Store | 管理認證狀態 |
| 前端 API | Composable | 發送 HTTP 請求 |
| HTTP | POST + JSON | 資料傳輸 |
| 後端 | CodeIgniter 4 | 接收並處理請求 |
| 認證 | JWT | Token 生成 |
| 資料庫 | MariaDB | 用戶資料儲存 |

---

## 結論

✅ **系統完全正常**
- 前端使用 POST 方法 + JSON 格式
- 後端期望 POST 方法 + JSON 格式
- 欄位名稱完全匹配
- 符合 RESTful 標準
- 符合安全最佳實踐

**無需任何修改！**

---

最後更新: 2025-10-24
文件版本: 1.0.0
