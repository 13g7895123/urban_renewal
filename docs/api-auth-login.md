# API 文件：使用者登入

## 端點資訊

- **端點路徑**: `/api/auth/login`
- **HTTP 方法**: `POST`
- **認證需求**: 無（公開端點）
- **內容類型**: `application/json`
- **版本**: v1.0

## 概述

此 API 端點用於使用者登入都市更新管理系統。成功登入後將返回 JWT 存取令牌（Access Token）和刷新令牌（Refresh Token），用於後續的身份認證。

## 功能特性

- ✅ 使用者名稱與密碼驗證
- ✅ 自動帳號鎖定機制（防暴力破解）
- ✅ JWT Token 產生與管理
- ✅ Session 管理
- ✅ 登入事件記錄與稽核
- ✅ 帳號啟用狀態檢查
- ✅ CORS 跨域支援

## 請求格式

### 請求標頭（Headers）

```
Content-Type: application/json
Accept: application/json
```

### 請求本體（Request Body）

```json
{
  "username": "string",
  "password": "string"
}
```

### 參數說明

| 參數名稱 | 資料類型 | 必填 | 說明 | 限制條件 |
|---------|---------|------|------|---------|
| `username` | string | ✓ | 使用者帳號 | 最大長度 100 字元 |
| `password` | string | ✓ | 使用者密碼 | 最小長度 6 字元 |

### 驗證規則

1. **username（使用者名稱）**
   - 必填欄位
   - 最大長度：100 字元
   - 錯誤訊息：「驗證失敗」

2. **password（密碼）**
   - 必填欄位
   - 最小長度：6 字元
   - 錯誤訊息：「驗證失敗」

## 回應格式

### 成功回應（HTTP 200）

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "user123",
      "email": "user@example.com",
      "full_name": "張三",
      "nickname": "小張",
      "role": "member",
      "user_type": "enterprise",
      "is_company_manager": 1,
      "urban_renewal_id": 5,
      "phone": "0912345678",
      "line_account": "line_id",
      "position": "專案經理",
      "is_active": 1,
      "last_login_at": "2024-11-15 12:30:00",
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-11-15 12:30:00"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6...",
    "expires_in": 86400
  },
  "message": "登入成功"
}
```

### 回應欄位說明

#### user 物件欄位

| 欄位名稱 | 資料類型 | 說明 |
|---------|---------|------|
| `id` | integer | 使用者唯一識別碼 |
| `username` | string | 使用者帳號 |
| `email` | string | 電子郵件地址 |
| `full_name` | string | 使用者全名 |
| `nickname` | string | 暱稱 |
| `role` | string | 角色（admin: 管理員, chairman: 理事長, member: 成員, observer: 觀察員） |
| `user_type` | string | 使用者類型（enterprise: 企業使用者, general: 一般使用者） |
| `is_company_manager` | integer | 是否為企業管理者（0: 否, 1: 是） |
| `urban_renewal_id` | integer\|null | 所屬都市更新會 ID（管理員為 null） |
| `phone` | string | 電話號碼 |
| `line_account` | string\|null | LINE 帳號 |
| `position` | string\|null | 職位 |
| `is_active` | integer | 帳號啟用狀態（0: 停用, 1: 啟用） |
| `last_login_at` | string | 最後登入時間（格式：Y-m-d H:i:s） |
| `created_at` | string | 帳號建立時間 |
| `updated_at` | string | 帳號更新時間 |

**注意**：回應中已移除敏感欄位 `password_hash`、`password_reset_token`、`login_attempts` 等資訊。

#### token 相關欄位

| 欄位名稱 | 資料類型 | 說明 |
|---------|---------|------|
| `token` | string | JWT 存取令牌，用於後續 API 請求的身份認證 |
| `refresh_token` | string | 刷新令牌，用於在存取令牌過期後獲取新令牌 |
| `expires_in` | integer | 存取令牌有效期限（秒），預設為 86400（24 小時） |

#### JWT Token Payload 結構

存取令牌（Access Token）解碼後包含以下資訊：

```json
{
  "iss": "urban-renewal-system",
  "aud": "urban-renewal-users",
  "iat": 1700000000,
  "exp": 1700086400,
  "user_id": 1,
  "username": "user123",
  "role": "member",
  "urban_renewal_id": 5
}
```

| 欄位 | 說明 |
|-----|------|
| `iss` | 發行者（Issuer） |
| `aud` | 接收者（Audience） |
| `iat` | 發行時間（Issued At） |
| `exp` | 過期時間（Expiration Time） |
| `user_id` | 使用者 ID |
| `username` | 使用者名稱 |
| `role` | 使用者角色 |
| `urban_renewal_id` | 都市更新會 ID（管理員為 null，可存取所有資源） |

## 錯誤回應

### 驗證失敗（HTTP 422）

**場景**：請求參數不符合驗證規則

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "驗證失敗",
    "details": {
      "username": "使用者名稱為必填",
      "password": "密碼至少需要6個字元"
    }
  }
}
```

### 認證失敗（HTTP 401）

#### 場景 1：帳號或密碼錯誤

```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "帳號或密碼錯誤"
  }
}
```

#### 場景 2：帳號已被鎖定

```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "帳號已被鎖定，請稍後再試"
  }
}
```

**說明**：連續登入失敗 5 次後，帳號將被鎖定 30 分鐘。

### 伺服器錯誤（HTTP 500）

```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "登入處理失敗"
  }
}
```

## 安全性機制

### 1. 帳號鎖定機制

為防止暴力破解攻擊，系統實施以下安全措施：

- **失敗次數限制**：連續登入失敗達 5 次
- **鎖定時間**：帳號將被鎖定 30 分鐘（1800 秒）
- **自動解鎖**：鎖定時間過後自動解除
- **計數器重置**：登入成功後，失敗次數自動歸零

### 2. 密碼安全

- 密碼使用 `PASSWORD_DEFAULT` 演算法進行雜湊加密（bcrypt）
- 密碼驗證使用 PHP 的 `password_verify()` 函式
- 密碼雜湊值不會在任何 API 回應中返回

### 3. Token 安全

- **演算法**：JWT 使用 HS256 (HMAC-SHA256) 演算法簽名
- **存取令牌有效期**：24 小時（86400 秒）
- **刷新令牌有效期**：7 天（604800 秒）
- **Session 管理**：每次登入會使舊的 Session 失效，確保單一活躍 Session

### 4. 帳號狀態檢查

- 只有 `is_active = 1` 的帳號才能登入
- 停用的帳號會收到「帳號或密碼錯誤」訊息（避免洩露帳號存在資訊）

### 5. 稽核記錄

系統會記錄以下登入事件：

- **成功登入**：記錄使用者 ID、角色、都市更新會 ID
- **失敗登入**：記錄失敗原因（使用者不存在、密碼錯誤、帳號鎖定）
- **事件類型**：login_success, login_failure
- **失敗原因代碼**：invalid_credentials, invalid_password, account_locked

## Session 管理

登入成功後，系統會自動建立 User Session，包含以下資訊：

```json
{
  "user_id": 1,
  "session_token": "JWT存取令牌",
  "refresh_token": "刷新令牌",
  "expires_at": "2024-11-16 12:30:00",
  "refresh_expires_at": "2024-11-22 12:30:00",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "is_active": 1,
  "created_at": "2024-11-15 12:30:00",
  "last_activity_at": "2024-11-15 12:30:00"
}
```

### Session 行為

- 每次登入會將該使用者的舊 Sessions 設為非活躍狀態（`is_active = 0`）
- 確保每個使用者同時只有一個活躍的 Session
- Session 資訊包含 IP 位址和 User Agent，用於安全稽核

## 使用範例

### cURL 範例

```bash
curl -X POST https://api.example.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "user123",
    "password": "mypassword123"
  }'
```

### JavaScript (Fetch API) 範例

```javascript
async function login(username, password) {
  try {
    const response = await fetch('https://api.example.com/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        username: username,
        password: password
      })
    });

    const data = await response.json();

    if (data.success) {
      // 儲存 Token
      localStorage.setItem('access_token', data.data.token);
      localStorage.setItem('refresh_token', data.data.refresh_token);
      
      console.log('登入成功！', data.data.user);
      return data.data;
    } else {
      console.error('登入失敗：', data.error.message);
      throw new Error(data.error.message);
    }
  } catch (error) {
    console.error('請求錯誤：', error);
    throw error;
  }
}

// 使用範例
login('user123', 'mypassword123')
  .then(data => {
    console.log('使用者資料：', data.user);
    console.log('Token：', data.token);
  })
  .catch(error => {
    console.error('登入失敗：', error);
  });
```

### Vue 3 Composable 範例

```javascript
// composables/useAuth.js
export const useAuth = () => {
  const login = async (username, password) => {
    try {
      const { data, error } = await useFetch('/api/auth/login', {
        method: 'POST',
        body: {
          username,
          password
        }
      });

      if (error.value) {
        throw new Error(error.value.message || '登入失敗');
      }

      if (data.value?.success) {
        // 儲存使用者資訊和 Token
        const token = useCookie('auth_token');
        const refreshToken = useCookie('refresh_token');
        
        token.value = data.value.data.token;
        refreshToken.value = data.value.data.refresh_token;

        return {
          success: true,
          user: data.value.data.user
        };
      }
    } catch (err) {
      console.error('登入錯誤：', err);
      throw err;
    }
  };

  return {
    login
  };
};
```

### Axios 範例

```javascript
import axios from 'axios';

const loginUser = async (username, password) => {
  try {
    const response = await axios.post('https://api.example.com/api/auth/login', {
      username: username,
      password: password
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    if (response.data.success) {
      // 儲存 Token 到 localStorage 或 Cookie
      localStorage.setItem('access_token', response.data.data.token);
      localStorage.setItem('refresh_token', response.data.data.refresh_token);
      
      // 設定 Axios 預設 Authorization Header
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`;
      
      return response.data.data;
    }
  } catch (error) {
    if (error.response) {
      // 伺服器回應錯誤
      console.error('登入失敗：', error.response.data.error.message);
      throw new Error(error.response.data.error.message);
    } else if (error.request) {
      // 請求已發送但沒有收到回應
      console.error('無法連線到伺服器');
      throw new Error('無法連線到伺服器');
    } else {
      // 其他錯誤
      console.error('錯誤：', error.message);
      throw error;
    }
  }
};
```

## 後續 API 請求認證

登入成功後，使用返回的 `token` 進行後續 API 請求認證：

### 請求標頭格式

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 範例

```bash
curl -X GET https://api.example.com/api/auth/me \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." \
  -H "Accept: application/json"
```

```javascript
// JavaScript Fetch
fetch('https://api.example.com/api/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

// Axios
axios.get('https://api.example.com/api/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

## Token 刷新

當存取令牌過期時，使用刷新令牌獲取新的存取令牌：

**端點**: `POST /api/auth/refresh`

**請求**:
```json
{
  "refresh_token": "your_refresh_token_here"
}
```

**回應**:
```json
{
  "success": true,
  "data": {
    "token": "new_access_token",
    "refresh_token": "new_refresh_token",
    "expires_in": 86400
  },
  "message": "Token 刷新成功"
}
```

詳細資訊請參考 [Token 刷新 API 文件](/api/auth/refresh)。

## 相關 API 端點

- **GET** `/api/auth/me` - 取得當前使用者資訊
- **POST** `/api/auth/logout` - 登出
- **POST** `/api/auth/refresh` - 刷新 Token
- **POST** `/api/auth/register` - 註冊新使用者
- **POST** `/api/auth/forgot-password` - 忘記密碼
- **POST** `/api/auth/reset-password` - 重設密碼

## 注意事項

### 開發建議

1. **Token 儲存**
   - 瀏覽器端：建議使用 `httpOnly` Cookie 儲存 Token，避免 XSS 攻擊
   - 行動應用：可使用安全的本地儲存（Keychain、SharedPreferences）
   - 避免在 localStorage 中儲存敏感 Token（容易受到 XSS 攻擊）

2. **錯誤處理**
   - 實作完整的錯誤處理機制
   - 針對不同錯誤代碼提供使用者友善的提示訊息
   - 記錄客戶端錯誤日誌以便除錯

3. **安全性最佳實踐**
   - 始終使用 HTTPS 傳輸
   - 不要在 URL 參數中傳遞密碼
   - 在前端加入密碼強度檢查
   - 實作 CAPTCHA 防止自動化攻擊

4. **Token 管理**
   - 定期檢查 Token 是否即將過期，提前刷新
   - 登出時清除所有儲存的 Token
   - 實作 Token 過期後的自動重新導向登入頁面

### 系統管理員設定

1. **環境變數設定**
   - `JWT_SECRET_KEY`：JWT 簽名金鑰（生產環境務必更換）
   - `JWT_ISSUER`：JWT 發行者識別
   - `JWT_AUDIENCE`：JWT 接收者識別
   - `JWT_EXPIRY`：存取令牌有效期限（秒）
   - `JWT_REFRESH_EXPIRY`：刷新令牌有效期限（秒）

2. **資料庫索引**
   - 確保 `users.username` 欄位有索引
   - 確保 `users.email` 欄位有索引
   - 確保 `user_sessions.session_token` 欄位有索引

3. **效能監控**
   - 監控登入 API 的回應時間
   - 追蹤失敗登入次數，偵測異常活動
   - 定期清理過期的 Session 記錄

### 常見問題

**Q1: Token 過期後該怎麼辦？**

A: 使用 `refresh_token` 呼叫 `/api/auth/refresh` 端點獲取新的 Token。如果 Refresh Token 也過期，需要重新登入。

**Q2: 為什麼登入失敗次數達到上限後帳號會被鎖定？**

A: 這是為了防止暴力破解攻擊。等待 30 分鐘後帳號會自動解鎖，或聯繫系統管理員手動解鎖。

**Q3: 可以同時在多個裝置上登入嗎？**

A: 目前系統設計為單一活躍 Session，新的登入會使舊的 Session 失效。若需要多裝置同時登入，需要調整 Session 管理邏輯。

**Q4: 忘記密碼怎麼辦？**

A: 使用 `/api/auth/forgot-password` 端點請求密碼重設連結，系統會發送重設郵件到註冊的電子信箱。

**Q5: 如何查看我的登入歷史記錄？**

A: 系統會記錄所有認證事件到 `authentication_events` 表，管理員可以透過後台查詢。一般使用者可以聯繫管理員查詢。

## 變更歷史

| 版本 | 日期 | 變更內容 |
|------|------|---------|
| v1.0 | 2024-11-15 | 初始版本發布 |

## 聯絡資訊

如有任何問題或建議，請聯繫：

- **技術支援**：support@example.com
- **API 文件**：https://api.example.com/docs
- **問題回報**：https://github.com/your-repo/issues
