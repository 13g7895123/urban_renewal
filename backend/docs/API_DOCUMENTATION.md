# Urban Renewal Management System - API 說明文件

> 更新日期：2025-12-16  
> 版本：1.1.0

## 文件變更紀錄

| 版本 | 日期 | 變更說明 |
|------|------|----------|
| 1.1.0 | 2025-12-16 | 新增詳細回應範例、錯誤處理說明、重新計算權重 API |
| 1.0.0 | 2025-12-06 | 初版發布 |

## 目錄

1. [通用說明](#通用說明)
2. [認證 API (Auth)](#認證-api-auth)
3. [使用者管理 API (Users)](#使用者管理-api-users)
4. [企業管理 API (Companies)](#企業管理-api-companies)
5. [更新會管理 API (Urban Renewals)](#更新會管理-api-urban-renewals)
6. [地號管理 API (Land Plots)](#地號管理-api-land-plots)
7. [共有部分管理 API (Joint Common Areas)](#共有部分管理-api-joint-common-areas)
8. [所有權人管理 API (Property Owners)](#所有權人管理-api-property-owners)
9. [會議管理 API (Meetings)](#會議管理-api-meetings)
10. [會議出席 API (Meeting Attendance)](#會議出席-api-meeting-attendance)
11. [投票議題 API (Voting Topics)](#投票議題-api-voting-topics)
12. [投票記錄 API (Voting)](#投票記錄-api-voting)
13. [通知 API (Notifications)](#通知-api-notifications)
14. [文件管理 API (Documents)](#文件管理-api-documents)
15. [系統設定 API (System Settings)](#系統設定-api-system-settings)
16. [地區資料 API (Locations)](#地區資料-api-locations)
17. [JWT 除錯 API (Admin)](#jwt-除錯-api-admin)
18. [附錄](#附錄)

---

## 通用說明

### Base URL
```
/api
```

### 認證方式
大部分 API 需要 JWT Token 認證，透過以下方式傳遞：
- **Cookie**: `auth_token` (httpOnly cookie，自動設定)
- **Header**: `Authorization: Bearer <token>`

### 回應格式
所有 API 回應格式統一為 JSON：

**成功回應**
```json
{
  "success": true,
  "data": { ... },
  "message": "操作成功"
}
```

**錯誤回應**
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "錯誤訊息",
    "details": { ... }
  }
}
```

### HTTP 狀態碼
| 狀態碼 | 說明 |
|--------|------|
| 200 | 成功 |
| 201 | 建立成功 |
| 400 | 請求參數錯誤 |
| 401 | 未授權 |
| 403 | 權限不足 |
| 404 | 資源不存在 |
| 422 | 驗證失敗 |
| 500 | 伺服器錯誤 |

### 角色權限
| 角色 | 說明 |
|------|------|
| admin | 系統管理員，擁有所有權限 |
| chairman | 主席，管理特定更新會 |
| member | 一般會員 |
| observer | 觀察員 |

---

## 認證 API (Auth)

### 使用者註冊
```
POST /api/auth/register
```

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| account | string | ✓ | 帳號（最大100字元，需唯一） |
| nickname | string | ✓ | 暱稱（最大100字元） |
| password | string | ✓ | 密碼（最少6字元） |
| fullName | string | ✓ | 全名（最大100字元） |
| email | string | ✓ | 電子信箱（需唯一） |
| phone | string | ✓ | 電話（最大20字元） |
| accountType | string | ✓ | 帳號類型：`personal`（個人）或 `business`（企業） |
| businessName | string | 企業必填 | 企業名稱（最大255字元） |
| taxId | string | 企業必填 | 統一編號（最大20字元） |
| businessPhone | string | - | 企業電話 |
| lineId | string | - | LINE ID |
| jobTitle | string | - | 職稱 |

**回應範例**
```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "username": "user001"
  },
  "message": "註冊成功"
}
```

---

### 使用者登入
```
POST /api/auth/login
```

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| username | string | ✓ | 帳號（最大100字元） |
| password | string | ✓ | 密碼（最少6字元） |

**回應範例（成功）**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "role": "admin",
      "full_name": "管理員",
      "nickname": "Admin",
      "email": "admin@example.com",
      "phone": "0912345678",
      "is_company_manager": 0,
      "company_id": null,
      "user_type": "general",
      "is_active": 1,
      "last_login_at": "2025-12-16 10:00:00"
    },
    "expires_in": 86400
  },
  "message": "登入成功"
}
```

**錯誤回應範例（帳密錯誤）**
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "帳號或密碼錯誤"
  }
}
```

**錯誤回應範例（帳號鎖定）**
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "帳號已被鎖定，請稍後再試"
  }
}
```

**注意事項**:
- 連續登入失敗 5 次後，帳號將被鎖定 30 分鐘
- Token 存放於 httpOnly Cookie 中，自動設定
- Token 有效期為 24 小時（86400 秒）

---

### 使用者登出
```
POST /api/auth/logout
```

**請求參數**: 無

**回應範例**
```json
{
  "success": true,
  "message": "登出成功"
}
```

---

### 刷新 Token
```
POST /api/auth/refresh
```

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| refresh_token | string | - | Refresh Token（若未使用 Cookie） |

**回應範例**
```json
{
  "success": true,
  "data": {
    "expires_in": 86400
  },
  "message": "Token 刷新成功"
}
```

---

### 取得當前使用者資訊
```
GET /api/auth/me
```

**請求參數**: 無

**回應範例**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "full_name": "管理員",
    "email": "admin@example.com",
    "role": "admin",
    "is_company_manager": 0,
    "user_type": "general"
  },
  "message": "取得使用者資訊成功"
}
```

---

### 忘記密碼
```
POST /api/auth/forgot-password
```

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| email | string | ✓ | 電子信箱 |

**回應範例**
```json
{
  "success": true,
  "message": "如果該信箱存在，我們已發送重設密碼連結"
}
```

---

### 重設密碼
```
POST /api/auth/reset-password
```

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| token | string | ✓ | 重設令牌 |
| password | string | ✓ | 新密碼（最少6字元） |
| password_confirm | string | ✓ | 確認密碼（需與 password 相同） |

**回應範例**
```json
{
  "success": true,
  "message": "密碼重設成功"
}
```

---

## 使用者管理 API (Users)

### 取得使用者列表
```
GET /api/users
```

**權限**: admin, chairman, 企業管理者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| role | string | - | 篩選角色 |
| user_type | string | - | 篩選類型：`general` 或 `enterprise` |
| company_id | integer | - | 篩選企業 ID |
| is_active | boolean | - | 篩選啟用狀態 |
| search | string | - | 關鍵字搜尋 |

---

### 取得使用者詳情
```
GET /api/users/{id}
```

**權限**: 本人、admin、chairman（同更新會）

---

### 建立使用者
```
POST /api/users
```

**權限**: admin, chairman, 企業管理者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| username | string | ✓ | 帳號（最大100字元，需唯一） |
| email | string | - | 電子信箱（需唯一） |
| password | string | ✓ | 密碼（最少6字元） |
| role | string | ✓ | 角色：`admin`, `chairman`, `member`, `observer` |
| full_name | string | - | 全名（最大100字元） |
| phone | string | - | 電話（最大20字元） |
| nickname | string | - | 暱稱（最大100字元） |
| line_account | string | - | LINE 帳號（最大100字元） |
| position | string | - | 職稱（最大100字元） |
| company_id | integer | - | 企業 ID |
| user_type | string | - | 類型：`general` 或 `enterprise` |
| is_company_manager | integer | - | 是否為企業管理者：`0` 或 `1` |

---

### 更新使用者
```
PUT /api/users/{id}
```

**權限**: 本人、admin、chairman（同更新會）

**請求參數**: 同建立使用者（所有欄位皆為選填）

---

### 刪除使用者
```
DELETE /api/users/{id}
```

**權限**: admin, chairman, 企業管理者

---

### 啟用/停用使用者
```
PATCH /api/users/{id}/toggle-status
```

**權限**: admin, chairman

---

### 重設登入失敗次數
```
PATCH /api/users/{id}/reset-login-attempts
```

**權限**: admin, chairman

---

### 搜尋使用者
```
GET /api/users/search
```

**權限**: admin, chairman

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| keyword | string | ✓ | 搜尋關鍵字 |
| page | integer | - | 頁碼 |
| per_page | integer | - | 每頁筆數 |

---

### 取得角色統計
```
GET /api/users/role-statistics
```

**權限**: admin, chairman

---

### 取得當前使用者資料
```
GET /api/users/profile
```

**權限**: 已登入使用者

---

### 變更密碼
```
POST /api/users/change-password
```

**權限**: 已登入使用者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| current_password | string | ✓ | 當前密碼 |
| new_password | string | ✓ | 新密碼（最少6字元） |
| confirm_password | string | ✓ | 確認密碼（需與 new_password 相同） |

---

### 設定為企業使用者
```
POST /api/users/{id}/set-as-company-user
```

**權限**: admin, 企業管理者

---

### 設定為企業管理者
```
POST /api/users/{id}/set-as-company-manager
```

**權限**: admin, 企業管理者

---

## 企業管理 API (Companies)

### 取得當前企業資料
```
GET /api/companies/me
```

**權限**: 企業管理者

---

### 更新當前企業資料
```
PUT /api/companies/me
```

**權限**: 企業管理者

**請求參數**: 依據企業欄位（company_name, tax_id, address 等）

---

### 取得企業下的更新會列表
```
GET /api/companies/me/renewals
```

**權限**: 企業管理者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |

---

## 更新會管理 API (Urban Renewals)

### 取得更新會列表
```
GET /api/urban-renewals
```

**權限**: admin, 企業管理者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| search | string | - | 搜尋更新會名稱 |

---

### 取得更新會詳情
```
GET /api/urban-renewals/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 建立更新會
```
POST /api/urban-renewals
```

**權限**: admin, 企業管理者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| name | string | ✓ | 更新會名稱 |
| chairman_name | string | - | 主席姓名 |
| chairman_phone | string | - | 主席電話 |
| address | string | - | 地址 |
| representative | string | - | 代表人 |
| company_id | integer | - | 所屬企業 ID |

---

### 更新更新會
```
PUT /api/urban-renewals/{id}
```

**權限**: admin, 企業管理者（同企業）

**請求參數**: 同建立更新會（所有欄位皆為選填）

---

### 刪除更新會
```
DELETE /api/urban-renewals/{id}
```

**權限**: admin

---

### 批量分配管理者
```
POST /api/urban-renewals/batch-assign
```

**權限**: admin, 企業管理者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| assignments | object | ✓ | 分配對照表 `{ "更新會ID": "管理者ID" }` |

---

### 取得企業管理者列表
```
GET /api/urban-renewals/company-managers
```

**權限**: 企業管理者

---

## 地號管理 API (Land Plots)

### 取得更新會的地號列表
```
GET /api/urban-renewals/{id}/land-plots
```

**權限**: admin, 企業管理者（同企業）

---

### 新增地號
```
POST /api/urban-renewals/{id}/land-plots
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| county | string | ✓ | 縣市 |
| district | string | ✓ | 鄉鎮區 |
| section | string | ✓ | 地段 |
| landNumberMain | string | ✓ | 地號主號 |
| landNumberSub | string | - | 地號子號 |
| landArea | decimal | - | 土地面積 |
| isRepresentative | boolean | - | 是否為代表地號 |

---

### 取得地號詳情
```
GET /api/land-plots/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 更新地號
```
PUT /api/land-plots/{id}
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| landArea | decimal | - | 土地面積 |
| isRepresentative | boolean | - | 是否為代表地號 |

---

### 刪除地號
```
DELETE /api/land-plots/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 設定代表地號
```
PUT /api/land-plots/{id}/representative
```

**權限**: admin, 企業管理者

---

## 共有部分管理 API (Joint Common Areas)

### 取得共有部分列表
```
GET /api/urban-renewals/{id}/joint-common-areas
```

**權限**: admin, 企業管理者（同企業）

---

### 新增共有部分
```
POST /api/urban-renewals/{id}/joint-common-areas
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| county | string | ✓ | 縣市 |
| district | string | ✓ | 鄉鎮區 |
| section | string | ✓ | 地段 |
| building_number_main | string | ✓ | 建號主號 |
| building_number_sub | string | - | 建號子號 |
| building_total_area | decimal | - | 建物總面積 |
| corresponding_building_id | integer | - | 對應主建物 ID |
| ownership_numerator | integer | - | 持分分子 |
| ownership_denominator | integer | - | 持分分母 |

---

### 取得共有部分詳情
```
GET /api/joint-common-areas/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 更新共有部分
```
PUT /api/joint-common-areas/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 刪除共有部分
```
DELETE /api/joint-common-areas/{id}
```

**權限**: admin, 企業管理者（同企業）

---

## 所有權人管理 API (Property Owners)

### 取得更新會的所有權人列表
```
GET /api/urban-renewals/{id}/property-owners
```

**權限**: admin, 企業管理者（同企業）

---

### 取得所有權人列表
```
GET /api/property-owners
```

**權限**: admin

---

### 取得所有權人詳情
```
GET /api/property-owners/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 建立所有權人
```
POST /api/property-owners
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| urban_renewal_id | integer | ✓ | 更新會 ID |
| owner_name | string | ✓ | 所有權人名稱 |
| identity_number | string | - | 身分證字號 |
| owner_code | string | - | 所有權人編號 |
| phone1 | string | - | 電話1 |
| phone2 | string | - | 電話2 |
| contact_address | string | - | 聯絡地址 |
| registered_address | string | - | 戶籍地址 |
| exclusion_type | string | - | 排除類型：`法院囑託查封`, `假扣押`, `假處分`, `破產登記`, `未經繼承` |
| notes | string | - | 備註 |
| buildings | array | - | 建物持分資料 |
| lands | array | - | 土地持分資料 |

---

### 更新所有權人
```
PUT /api/property-owners/{id}
```

**權限**: admin, 企業管理者（同企業）

**請求參數**: 同建立所有權人（所有欄位皆為選填）

---

### 刪除所有權人
```
DELETE /api/property-owners/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 匯出所有權人
```
GET /api/urban-renewals/{id}/property-owners/export
```

**權限**: admin, 企業管理者（同企業）

**回應**: Excel 檔案下載

---

### 匯入所有權人
```
POST /api/urban-renewals/{id}/property-owners/import
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| file | file | ✓ | Excel 檔案（.xlsx, .xls） |

---

### 下載匯入範本
```
GET /api/property-owners/template
```

**權限**: 已登入使用者

**回應**: Excel 範本檔案下載

---

### 取得更新會所有建物
```
GET /api/urban-renewals/{id}/property-owners/all-buildings
```

**權限**: admin, 企業管理者（同企業）

---

## 會議管理 API (Meetings)

### 取得會議列表
```
GET /api/meetings
```

**權限**: admin, 企業管理者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| urban_renewal_id | integer | - | 篩選更新會 ID |
| status | string | - | 篩選狀態 |
| search | string | - | 搜尋關鍵字 |

---

### 取得會議詳情
```
GET /api/meetings/{id}
```

**權限**: admin, 企業管理者（同企業）

---

### 建立會議
```
POST /api/meetings
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| urban_renewal_id | integer | ✓ | 更新會 ID |
| meeting_name | string | ✓ | 會議名稱（最大255字元） |
| meeting_type | string | ✓ | 會議類型：`會員大會`, `理事會`, `監事會`, `臨時會議` |
| meeting_date | string | ✓ | 會議日期（格式：Y-m-d） |
| meeting_time | string | ✓ | 會議時間 |
| meeting_location | string | - | 會議地點（最大500字元） |
| observers | array | - | 列席人員列表 |
| exclude_owner_from_count | boolean | - | 排除所有權人不列計 |

---

### 更新會議
```
PUT /api/meetings/{id}
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_name | string | - | 會議名稱（最大255字元） |
| meeting_type | string | - | 會議類型 |
| meeting_date | string | - | 會議日期 |
| meeting_time | string | - | 會議時間 |
| meeting_location | string | - | 會議地點 |
| meeting_status | string | - | 會議狀態：`draft`, `scheduled`, `in_progress`, `completed`, `cancelled` |
| observers | array | - | 列席人員列表 |

---

### 刪除會議
```
DELETE /api/meetings/{id}
```

**權限**: admin, 企業管理者（同企業）

**限制**: 進行中或已完成的會議不能刪除

---

### 取得會議統計
```
GET /api/meetings/{id}/statistics
```

**權限**: admin, 企業管理者（同企業）

---

### 更新會議狀態
```
PATCH /api/meetings/{id}/status
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| status | string | ✓ | 狀態：`draft`, `scheduled`, `in_progress`, `completed`, `cancelled` |

**狀態轉換規則**
| 當前狀態 | 可轉換為 |
|----------|----------|
| draft | scheduled, cancelled |
| scheduled | in_progress, cancelled |
| in_progress | completed, cancelled |
| completed | （不可變更） |
| cancelled | draft |

---

### 匯出會議通知
```
GET /api/meetings/{id}/export-notice
```

**權限**: admin, 企業管理者（同企業）

**回應**: Word 檔案下載

---

### 匯出簽到冊
```
GET /api/meetings/{id}/export-signature-book
```

**權限**: admin, 企業管理者（同企業）

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| anonymous | string | - | 是否匿名（`true` 或 `false`） |

**回應**: Word 檔案下載

---

### 取得合格投票人
```
GET /api/meetings/{id}/eligible-voters
```

**權限**: admin, 企業管理者（同企業）

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 100） |

---

### 重新整理合格投票人
```
POST /api/meetings/{id}/eligible-voters/refresh
```

**權限**: admin, 企業管理者（同企業）

**限制**: 只有草稿或已排程的會議可以重新整理

---

## 會議出席 API (Meeting Attendance)

### 取得會議出席記錄
```
GET /api/meetings/{id}/attendances
```

**權限**: admin, 企業管理者（同企業）

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 50） |
| attendance_type | string | - | 篩選出席類型 |
| search | string | - | 搜尋關鍵字 |

---

### 會員報到
```
POST /api/meetings/{meetingId}/attendances/{ownerId}
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| attendance_type | string | ✓ | 出席類型：`present`（親自）, `proxy`（委託）, `absent`（缺席） |
| proxy_person | string | 委託必填 | 代理人姓名（最大100字元） |
| notes | string | - | 備註（最大500字元） |

---

### 更新出席狀態
```
PUT /api/meetings/{meetingId}/attendances/{ownerId}
```

**權限**: admin, 企業管理者（同企業）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| attendance_type | string | - | 出席類型 |
| proxy_person | string | - | 代理人姓名 |
| notes | string | - | 備註 |
| is_calculated | integer | - | 是否納入計算：`0` 或 `1` |

---

### 取得出席統計
```
GET /api/meetings/{id}/attendances/statistics
```

**權限**: admin, 企業管理者

---

### 匯出報到結果
```
POST /api/meetings/{id}/attendances/export
```

**權限**: admin, 企業管理者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| format | string | - | 匯出格式：`excel`, `pdf`, `json`（預設 excel） |

---

### 批次報到
```
POST /api/meetings/{id}/attendances/batch
```

**權限**: admin, 企業管理者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| attendances | array | ✓ | 報到資料陣列 |
| attendances[].property_owner_id | integer | ✓ | 所有權人 ID |
| attendances[].attendance_type | string | ✓ | 出席類型 |
| attendances[].proxy_person | string | - | 代理人姓名 |

---

## 投票議題 API (Voting Topics)

### 取得投票議題列表
```
GET /api/voting-topics
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| meeting_id | integer | - | 篩選會議 ID |
| status | string | - | 篩選狀態 |
| search | string | - | 搜尋關鍵字 |

---

### 取得投票議題詳情
```
GET /api/voting-topics/{id}
```

**權限**: 已登入使用者（企業管理者限同企業）

---

### 建立投票議題
```
POST /api/voting-topics
```

**權限**: admin, 企業管理者（同企業會議）

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_id | integer | ✓ | 會議 ID |
| topic_number | string | - | 議題編號（最大20字元，自動產生） |
| topic_title | string | ✓ | 議題名稱（最大500字元） |
| topic_name | string | - | 議題名稱（同 topic_title） |
| voting_method | string | - | 投票方式：`simple_majority`, `absolute_majority`, `two_thirds_majority`, `unanimous` |
| property_owners | array | - | 所有權人選項 |
| voting_options | array | - | 投票選項（如同意/不同意） |

---

### 更新投票議題
```
PUT /api/voting-topics/{id}
```

**權限**: admin, 企業管理者（同企業會議）

**限制**: 只有草稿狀態的議題可以修改

**請求參數**: 同建立投票議題（所有欄位皆為選填）

---

### 刪除投票議題
```
DELETE /api/voting-topics/{id}
```

**權限**: admin, 企業管理者（同企業會議）

**限制**: 只有草稿狀態的議題可以刪除

---

### 啟動投票
```
PATCH /api/voting-topics/{id}/start-voting
```

**權限**: admin, chairman

---

### 結束投票
```
PATCH /api/voting-topics/{id}/close-voting
```

**權限**: admin, chairman

---

### 取得投票統計
```
GET /api/voting-topics/statistics
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_id | integer | - | 篩選會議 ID |

---

### 取得即將投票的議題
```
GET /api/voting-topics/upcoming
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| days | integer | - | 天數範圍（預設 7） |

---

## 投票記錄 API (Voting)

### 取得投票記錄
```
GET /api/voting
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| topic_id | integer | ✓ | 議題 ID |
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| choice | string | - | 篩選選擇 |

---

### 投票
```
POST /api/voting/vote
```

**權限**: 已登入使用者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| topic_id | integer | ✓ | 議題 ID |
| property_owner_id | integer | ✓ | 所有權人 ID |
| choice | string | ✓ | 選擇：`agree`, `disagree`, `abstain` |
| voter_name | string | - | 投票人姓名 |
| notes | string | - | 備註 |

---

### 批量投票
```
POST /api/voting/batch-vote
```

**權限**: admin, chairman

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| votes | array | ✓ | 投票資料陣列 |
| votes[].topic_id | integer | ✓ | 議題 ID |
| votes[].property_owner_id | integer | ✓ | 所有權人 ID |
| votes[].choice | string | ✓ | 選擇 |

---

### 查看我的投票
```
GET /api/voting/my-vote/{topicId}
```

**權限**: 已登入使用者

---

### 撤回投票
```
DELETE /api/voting/remove-vote
```

**權限**: 已登入使用者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| topic_id | integer | ✓ | 議題 ID |
| property_owner_id | integer | ✓ | 所有權人 ID |

---

### 取得投票統計
```
GET /api/voting/statistics/{topicId}
```

**權限**: 已登入使用者

---

### 匯出投票記錄
```
GET /api/voting/export/{topicId}
```

**權限**: admin, chairman

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| format | string | - | 格式：`xlsx`, `csv`（預設 xlsx） |

---

### 取得詳細投票記錄
```
GET /api/voting/detailed/{topicId}
```

**權限**: 已登入使用者

**回應範例**
```json
{
  "success": true,
  "data": {
    "topic": {
      "id": 1,
      "topic_title": "更新事業計畫案表決",
      "voting_method": "simple_majority"
    },
    "votes": [
      {
        "property_owner_id": 1,
        "owner_name": "王小明",
        "choice": "agree",
        "area_weight": 0.0523,
        "voted_at": "2025-12-15 14:30:00"
      }
    ]
  },
  "message": "取得詳細投票記錄成功"
}
```

---

### 重新計算投票權重
```
POST /api/voting/recalculate-weights/{topicId}
```

**權限**: admin, chairman, 企業管理者

**說明**: 當所有權人的土地持分資料更新後，可使用此 API 重新計算該議題的投票面積權重。

**請求參數**: 無

**回應範例**
```json
{
  "success": true,
  "data": {
    "topic_id": 1,
    "total_area": 5280.50,
    "updated_votes": 25,
    "recalculated_at": "2025-12-16 10:00:00"
  },
  "message": "投票權重重新計算成功"
}
```

**注意事項**:
- 僅能在投票未結束時重新計算
- 會根據最新的土地持分資料重新計算每位投票者的面積權重
- 計算完成後會自動更新投票統計

---

## 通知 API (Notifications)

### 取得通知列表
```
GET /api/notifications
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| is_read | boolean | - | 篩選已讀狀態 |
| type | string | - | 篩選通知類型 |
| priority | string | - | 篩選優先級 |
| date_from | string | - | 起始日期 |
| date_to | string | - | 結束日期 |

---

### 取得通知詳情
```
GET /api/notifications/{id}
```

**權限**: 本人或全域通知

---

### 建立通知
```
POST /api/notifications
```

**權限**: admin, chairman

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| title | string | ✓ | 標題（最大255字元） |
| message | string | ✓ | 內容 |
| notification_type | string | ✓ | 類型（見下方說明） |
| priority | string | - | 優先級：`low`, `normal`, `high`, `urgent` |
| user_ids | array | - | 收件人 ID 列表（批量通知） |

**通知類型**
| 類型 | 說明 |
|------|------|
| meeting_notice | 會議通知 |
| meeting_reminder | 會議提醒 |
| voting_start | 投票開始 |
| voting_end | 投票結束 |
| voting_reminder | 投票提醒 |
| check_in_reminder | 報到提醒 |
| system_maintenance | 系統維護 |
| user_account | 使用者帳號 |
| document_upload | 文件上傳 |
| report_ready | 報告準備完成 |
| permission_changed | 權限變更 |
| system_alert | 系統警告 |

---

### 標記為已讀
```
PATCH /api/notifications/{id}/mark-read
```

**權限**: 本人

---

### 批量標記為已讀
```
PATCH /api/notifications/mark-multiple-read
```

**權限**: 已登入使用者

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| notification_ids | array | ✓ | 通知 ID 列表 |

---

### 標記所有為已讀
```
PATCH /api/notifications/mark-all-read
```

**權限**: 已登入使用者

---

### 刪除通知
```
DELETE /api/notifications/{id}
```

**權限**: 本人或 admin

---

### 取得未讀通知數量
```
GET /api/notifications/unread-count
```

**權限**: 已登入使用者

---

### 取得通知統計
```
GET /api/notifications/statistics
```

**權限**: 已登入使用者

---

### 建立會議通知
```
POST /api/notifications/create-meeting-notification
```

**權限**: admin, chairman

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_id | integer | ✓ | 會議 ID |
| type | string | ✓ | 類型：`meeting_notice`, `meeting_reminder`, `check_in_reminder` |
| send_email | boolean | - | 是否發送 Email |
| send_sms | boolean | - | 是否發送簡訊 |

---

### 建立投票通知
```
POST /api/notifications/create-voting-notification
```

**權限**: admin, chairman

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| topic_id | integer | ✓ | 議題 ID |
| type | string | ✓ | 類型：`voting_start`, `voting_end`, `voting_reminder` |
| send_email | boolean | - | 是否發送 Email |
| send_sms | boolean | - | 是否發送簡訊 |

---

### 清理過期通知
```
DELETE /api/notifications/clean-expired
```

**權限**: admin

---

### 取得通知類型列表
```
GET /api/notifications/types
```

**權限**: 已登入使用者

---

## 文件管理 API (Documents)

### 取得文件列表
```
GET /api/documents
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| page | integer | - | 頁碼（預設 1） |
| per_page | integer | - | 每頁筆數（預設 10） |
| meeting_id | integer | - | 篩選會議 ID |
| type | string | - | 篩選文件類型 |
| search | string | - | 搜尋關鍵字 |

---

### 取得文件詳情
```
GET /api/documents/{id}
```

**權限**: 已登入使用者（需有存取權限）

---

### 上傳文件
```
POST /api/documents/upload
```

**權限**: admin, chairman, member

**請求參數（multipart/form-data）**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_id | integer | ✓ | 會議 ID |
| document_type | string | ✓ | 文件類型 |
| document_name | string | ✓ | 文件名稱 |
| file | file | ✓ | 檔案 |

---

### 下載文件
```
GET /api/documents/download/{id}
```

**權限**: 已登入使用者（需有存取權限）

---

### 更新文件
```
PUT /api/documents/{id}
```

**權限**: admin, chairman

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| document_name | string | - | 文件名稱 |
| document_type | string | - | 文件類型 |

---

### 刪除文件
```
DELETE /api/documents/{id}
```

**權限**: admin, chairman

---

### 取得文件統計
```
GET /api/documents/statistics
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_id | integer | - | 篩選會議 ID |

---

### 取得最近上傳的文件
```
GET /api/documents/recent
```

**權限**: 已登入使用者

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| limit | integer | - | 數量限制（預設 10） |

---

### 取得文件類型列表
```
GET /api/documents/types
```

**權限**: 已登入使用者

**回應範例**
```json
{
  "success": true,
  "data": {
    "agenda": "議程",
    "minutes": "會議紀錄",
    "attendance": "出席名單",
    "notice": "通知",
    "other": "其他"
  },
  "message": "文件類型列表"
}
```

---

### 取得儲存空間使用情況
```
GET /api/documents/storage-usage
```

**權限**: 已登入使用者

---

### 清理孤立檔案
```
DELETE /api/documents/clean-orphan-files
```

**權限**: admin

---

### 批量上傳文件
```
POST /api/documents/batch-upload
```

**權限**: admin, chairman

**請求參數（multipart/form-data）**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| meeting_id | integer | ✓ | 會議 ID |
| document_type | string | ✓ | 文件類型 |
| files[] | file | ✓ | 檔案（多檔） |

---

## 系統設定 API (System Settings)

### 取得系統設定列表
```
GET /api/system-settings
```

**權限**: admin

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| category | string | - | 篩選分類 |

---

### 取得公開設定
```
GET /api/system-settings/public
```

**權限**: 無需認證

---

### 取得分類設定
```
GET /api/system-settings/category/{category}
```

**權限**: 已登入使用者（非 admin 只能看公開設定）

---

### 取得單一設定值
```
GET /api/system-settings/get/{key}
```

**權限**: 已登入使用者（非 admin 只能看公開設定）

---

### 更新設定值
```
POST /api/system-settings/set
```

**權限**: admin

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| key | string | ✓ | 設定鍵值 |
| value | mixed | ✓ | 設定值 |

---

### 批量更新設定
```
POST /api/system-settings/batch-set
```

**權限**: admin

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| settings | object | ✓ | 設定對照表 `{ "key": "value" }` |

---

### 重設設定為預設值
```
PATCH /api/system-settings/reset/{key}
```

**權限**: admin

---

### 取得設定分類列表
```
GET /api/system-settings/categories
```

**權限**: admin

---

### 清除設定快取
```
DELETE /api/system-settings/clear-cache
```

**權限**: admin

**查詢參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| key | string | - | 特定設定鍵值（不指定則清除全部） |

---

### 驗證設定值
```
POST /api/system-settings/validate
```

**權限**: admin

**請求參數**
| 欄位 | 類型 | 必填 | 說明 |
|------|------|------|------|
| key | string | ✓ | 設定鍵值 |
| value | mixed | ✓ | 設定值 |

---

### 取得系統資訊
```
GET /api/system-settings/system-info
```

**權限**: admin

---

## 地區資料 API (Locations)

### 取得縣市列表
```
GET /api/locations/counties
```

**權限**: 已登入使用者

---

### 取得鄉鎮區列表
```
GET /api/locations/districts/{countyCode}
```

**權限**: 已登入使用者

---

### 取得地段列表
```
GET /api/locations/sections/{countyCode}/{districtCode}
```

**權限**: 已登入使用者

---

### 取得完整地區階層
```
GET /api/locations/hierarchy
```

**權限**: 已登入使用者

---

## JWT 除錯 API (Admin)

### 取得 JWT 除錯記錄列表
```
GET /api/admin/jwt-debug
```

**權限**: admin

---

### 取得 JWT 除錯統計
```
GET /api/admin/jwt-debug/stats
```

**權限**: admin

---

### 取得特定 JWT 除錯記錄
```
GET /api/admin/jwt-debug/{request_id}
```

**權限**: admin

---

### 清理 JWT 除錯記錄
```
POST /api/admin/jwt-debug/cleanup
```

**權限**: admin

---

## 附錄

### 分頁回應格式
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "total_pages": 10
  },
  "message": "操作成功"
}
```

### 常用錯誤代碼
| 代碼 | HTTP 狀態碼 | 說明 |
|------|-------------|------|
| VALIDATION_ERROR | 422 | 資料驗證失敗 |
| UNAUTHORIZED | 401 | 未授權或 Token 過期 |
| FORBIDDEN | 403 | 權限不足 |
| NOT_FOUND | 404 | 資源不存在 |
| BUSINESS_LOGIC_ERROR | 400 | 業務邏輯錯誤 |
| INTERNAL_ERROR | 500 | 伺服器內部錯誤 |
| INVALID_TOKEN | 401 | Token 無效或已過期 |
| EXPORT_ERROR | 500 | 匯出錯誤 |
| FILE_NOT_FOUND | 404 | 檔案不存在 |

### 使用者角色說明

| 角色 | 權限範圍 |
|------|----------|
| admin | 系統管理員，擁有所有權限，可管理所有更新會 |
| chairman | 主席，管理特定更新會的所有事務 |
| member | 一般會員，可參與投票和出席會議 |
| observer | 觀察員，只能查看資料 |

### 使用者類型說明

| 類型 | 說明 |
|------|------|
| general | 一般個人使用者 |
| enterprise | 企業使用者，關聯至特定企業 |

### 會議狀態說明

| 狀態 | 說明 |
|------|------|
| draft | 草稿，可自由編輯 |
| scheduled | 已排程，等待開始 |
| in_progress | 進行中 |
| completed | 已完成，不可修改 |
| cancelled | 已取消，可重新設為草稿 |

### 投票議題狀態說明

| 狀態 | 說明 |
|------|------|
| draft | 草稿，尚未開始投票 |
| voting | 投票中，可接受投票 |
| closed | 已結束，不可再投票 |

### 出席類型說明

| 類型 | 說明 |
|------|------|
| present | 本人親自出席 |
| proxy | 委託代理人出席 |
| absent | 缺席 |

### 投票選項說明

| 選項 | 說明 |
|------|------|
| agree | 同意 |
| disagree | 不同意 |
| abstain | 棄權 |

### 所有權人排除類型說明

| 類型 | 說明 |
|------|------|
| 法院囑託查封 | 遭法院囑託查封 |
| 假扣押 | 遭假扣押 |
| 假處分 | 遭假處分 |
| 破產登記 | 已破產登記 |
| 未經繼承 | 尚未完成繼承程序 |

### API 使用範例 (cURL)

**登入**
```bash
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"your_password"}' \
  -c cookies.txt
```

**取得會議列表（使用 Cookie）**
```bash
curl -X GET "https://your-domain.com/api/meetings?page=1&per_page=10" \
  -b cookies.txt
```

**建立會議**
```bash
curl -X POST https://your-domain.com/api/meetings \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{
    "urban_renewal_id": 1,
    "meeting_name": "第一次會員大會",
    "meeting_type": "會員大會",
    "meeting_date": "2025-12-25",
    "meeting_time": "14:00",
    "meeting_location": "台北市中山區XX路XX號"
  }'
```

### 日期時間格式

| 欄位類型 | 格式 | 範例 |
|----------|------|------|
| 日期 | Y-m-d | 2025-12-16 |
| 時間 | H:i | 14:00 |
| 日期時間 | Y-m-d H:i:s | 2025-12-16 14:00:00 |

### 檔案上傳限制

| 項目 | 限制 |
|------|------|
| 單檔大小上限 | 10 MB |
| 批量上傳數量上限 | 10 個檔案 |
| 允許的檔案類型 | xlsx, xls, pdf, doc, docx, jpg, png |

### CORS 設定

所有 API 端點皆支援 CORS，預設允許以下設定：
- **Access-Control-Allow-Origin**: 依據環境設定
- **Access-Control-Allow-Methods**: GET, POST, PUT, DELETE, PATCH, OPTIONS
- **Access-Control-Allow-Headers**: Content-Type, Authorization
- **Access-Control-Allow-Credentials**: true

### 速率限制（Rate Limiting）

目前未啟用全域速率限制，但建議：
- 一般 API：每分鐘不超過 100 次請求
- 登入 API：每分鐘不超過 10 次請求
- 匯出/匯入 API：每分鐘不超過 5 次請求
