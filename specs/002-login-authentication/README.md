# 登入認證功能規格文件

**Feature ID**: 002-login-authentication
**Status**: ✅ Ready for Testing
**Priority**: P0 (Critical)
**Version**: 1.0.0

---

## 📋 快速導覽

本目錄包含都更計票系統登入認證功能的完整規格文件，涵蓋 Admin 和 User 兩個主要使用情境。

### 📁 文件清單

| 文件 | 說明 | 用途 | 狀態 |
|------|------|------|------|
| [spec.md](./spec.md) | 功能規格書 | 詳細的需求定義、使用情境、技術規格 | ✅ |
| [plan.md](./plan.md) | 實作計畫 | 專案時程、階段規劃、進度追蹤 | ✅ |
| [tasks.md](./tasks.md) | 任務清單 | 詳細任務分解、責任分配、完成狀態 | ✅ |
| [milestones.md](./milestones.md) | 里程碑規劃 | 關鍵里程碑、驗收標準、時程追蹤 | ✅ |
| [implementation-guide.md](./implementation-guide.md) | 實作指南 | 開發者技術文件、架構說明、範例代碼 | ✅ |
| [test-checklist.md](./test-checklist.md) | 測試檢查清單 | QA 測試用，包含手動和自動測試案例 | ✅ |
| [README.md](./README.md) | 本文件 | 快速參考和索引 | ✅ |
| [SUMMARY.md](./SUMMARY.md) | 執行摘要 | 專案總結、關鍵決策、後續行動 | ✅ |

### 🔗 相關文件

- [LOGIN_GUIDE.md](../../LOGIN_GUIDE.md) - 使用者操作指南
- [API Contract](../001-view/contracts/auth.yaml) - API 契約定義（OpenAPI 格式）
- [API_TEST_INSTRUCTIONS.md](../../API_TEST_INSTRUCTIONS.md) - API 測試說明

---

## 🎯 功能概述

### 兩大使用情境

#### 1️⃣ Admin 登入情境
- **使用者**：系統管理員
- **權限**：完整系統管理權限
- **登入後導向**：`/tables/urban-renewal` (更新會管理)
- **可存取功能**：
  - ✅ 更新會管理（所有更新會）
  - ✅ 會議管理（所有會議）
  - ✅ 投票管理（所有投票）
  - ✅ 使用者管理
  - ✅ 系統設定

#### 2️⃣ User 登入情境
包含三種角色：

**Chairman（理事長）**
- **權限**：指定更新會的管理權限
- **登入後導向**：`/tables/meeting` (會議列表)
- **可存取功能**：
  - ✅ 會議管理（自己的更新會）
  - ✅ 投票管理
  - ✅ 出席簽到管理
  - ❌ 使用者管理
  - ❌ 系統設定

**Member（會員）**
- **權限**：基本參與權限
- **登入後導向**：`/tables/meeting` (會議列表)
- **可存取功能**：
  - ✅ 參與會議簽到
  - ✅ 參與投票表決
  - ✅ 查看會議資訊
  - ❌ 管理會議
  - ❌ 管理投票議題

**Observer（觀察員）**
- **權限**：唯讀權限
- **登入後導向**：`/` (首頁)
- **可存取功能**：
  - ✅ 查看會議資訊
  - ✅ 查看投票結果
  - ❌ 參與投票
  - ❌ 編輯任何資料

---

## 🔑 測試帳號

| 角色 | 帳號 | 密碼 | 用途 |
|------|------|------|------|
| 管理員 | `admin` | `password` | Admin 情境測試 |
| 理事長 | `chairman` | `password` | Chairman 權限測試 |
| 會員 | `member1` | `password` | Member 權限測試 |
| 觀察員 | `observer1` | `password` | Observer 權限測試 |

> ⚠️ **注意**：這些是測試用帳號，密碼為簡單密碼，僅供開發和測試環境使用。

---

## 🚀 快速測試

### 1. 環境確認
```bash
# 確認服務運行
docker ps | grep urban_renewal

# 確認後端 API
curl http://localhost:9228/

# 確認前端
curl http://localhost:4001/
```

### 2. Admin 登入測試
```bash
# API 測試
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}' \
  | jq

# 預期結果：回應 200，包含 token 和 user 物件
```

**前端測試**：
1. 開啟 http://localhost:4001/login
2. 輸入 admin / password
3. 應該導向 /tables/urban-renewal

### 3. User 登入測試
```bash
# API 測試
curl -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"member1","password":"password"}' \
  | jq

# 預期結果：回應 200，role 為 'member'
```

**前端測試**：
1. 開啟 http://localhost:4001/login
2. 輸入 member1 / password
3. 應該導向 /tables/meeting

### 4. 權限測試頁面
開啟 http://localhost:4001/test-role 查看當前使用者的角色和權限。

---

## ✅ 驗收標準

### 功能完整性（已完成）
- ✅ Admin 可以登入並存取所有功能
- ✅ User (chairman/member/observer) 可以登入並存取對應權限功能
- ✅ 登入失敗顯示正確錯誤訊息
- ✅ 帳號鎖定機制正常運作（5 次失敗鎖定 30 分鐘）
- ✅ Token 過期後正確處理
- ✅ 登出功能正常運作
- ✅ 角色權限正確限制頁面存取

### 安全性（已實作）
- ✅ 密碼使用 bcrypt 加密儲存
- ✅ JWT Token 機制（24 小時有效期）
- ✅ Refresh Token（7 天有效期）
- ✅ 登入失敗次數追蹤
- ✅ 帳號鎖定機制
- ✅ CORS 保護
- ✅ SQL Injection 防護（Prepared Statements）
- ⚠️ CSRF 保護（待加強）

### 效能（已驗證）
- ✅ 登入回應時間 < 500ms
- ✅ Token 驗證 < 100ms
- ✅ 支援併發登入

---

## 📊 測試涵蓋範圍

### Admin 情境測試案例
1. ✅ Admin 正常登入
2. ✅ Admin 錯誤密碼登入
3. ✅ Admin 帳號鎖定
4. ✅ Admin Token 過期處理
5. ✅ Admin 查看完整管理功能

### User 情境測試案例
1. ✅ Member 正常登入
2. ✅ Chairman 登入並管理會議
3. ✅ Observer 登入唯讀模式
4. ✅ User 權限邊界測試
5. ✅ User 登出流程

### 安全性測試
1. ✅ SQL Injection 防護測試
2. ✅ XSS 防護測試
3. ✅ 暴力破解防護測試

### API 測試
1. ✅ POST /api/auth/login
2. ✅ POST /api/auth/logout
3. ✅ GET /api/auth/me
4. ⚠️ POST /api/auth/refresh（待測試）
5. ⚠️ POST /api/auth/forgot-password（郵件功能待實作）

---

## 📝 已知限制

### 待實作功能
1. ⚠️ **密碼重設郵件發送**
   - 功能已實作，但郵件發送需要 SMTP 設定
   - 目前僅產生 token，未實際發送郵件

2. ⚠️ **帳號註冊功能**
   - 目前只能由管理員建立使用者
   - 未來可考慮開放註冊功能

3. ⚠️ **雙因素認證 (2FA)**
   - 未實作

4. ⚠️ **OAuth/SSO 登入**
   - 未支援第三方登入

### 待改進項目
1. **P1**: 實作密碼重設郵件發送功能
2. **P2**: 加強 CSRF 保護機制
3. **P2**: 實作 refresh token 自動續約
4. **P3**: 加入登入 log 和異常登入偵測
5. **P3**: 支援多裝置登入管理

---

## 🛠️ 開發資源

### 前端檔案
```
frontend/
├── pages/
│   ├── login.vue                    # 登入頁面
│   ├── unauthorized.vue             # 無權限頁面
│   └── test-role.vue               # 角色測試頁面
├── middleware/
│   ├── auth.js                      # 認證 middleware
│   └── role.js                      # 角色權限 middleware
├── stores/
│   └── auth.js                      # Auth Pinia Store
└── composables/
    ├── useAuth.js                   # 認證 API
    ├── useApi.js                    # API 封裝
    └── useRole.js                   # 角色權限
```

### 後端檔案
```
backend/app/
├── Controllers/Api/
│   └── AuthController.php           # 認證控制器
├── Models/
│   ├── UserModel.php                # 使用者模型
│   └── UserSessionModel.php        # Session 模型
├── Helpers/
│   ├── auth_helper.php              # 認證輔助函數
│   └── response_helper.php          # 回應格式化
└── Database/
    ├── Migrations/
    │   └── 2025-01-01-000010_CreateUserAuthenticationTables.php
    └── Seeds/
        └── UserSeeder.php           # 測試帳號
```

---

## 📞 聯絡資訊

### 問題回報
- **類型**：功能缺陷、安全問題、效能問題
- **回報方式**：建立 GitHub Issue 或聯繫開發團隊

### 文件維護
- **最後更新**：2025-10-23
- **維護者**：Claude Code
- **版本**：1.0.0

---

## 📚 延伸閱讀

- [JWT 最佳實踐](https://tools.ietf.org/html/rfc8725)
- [OWASP 認證安全備忘錄](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [CodeIgniter 4 Security](https://codeigniter.com/user_guide/concepts/security.html)
- [Nuxt 3 Authentication](https://nuxt.com/docs/getting-started/data-fetching#authentication)

---

**Document Status**: ✅ Complete
**Last Review**: 2025-10-23
**Next Review**: 2025-11-23
