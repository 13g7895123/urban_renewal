# 實作計畫：管理員與使用者登入情境

**分支**: `002-admin-user` | **日期**: 2025-10-24 | **規格**: [spec.md](./spec.md)
**輸入**: 功能規格來自 `/specs/002-admin-user/spec.md`

**注意**: 此模板由 `/speckit.plan` 指令填寫。執行流程請參閱 `.specify/templates/commands/plan.md`。

## 摘要

實作並完成都更平台的管理員與使用者登入認證系統。本功能提供安全的 JWT 基礎認證，並針對四種使用者類型（管理員、理事長、會員、觀察員）實施角色基礎存取控制 (RBAC)。核心功能（約 80%）已存在，包含登入/登出、會話管理與基礎角色檢查。本計畫專注於完成剩餘缺口：後端 RBAC 強制執行、自動 token 更新、認證事件記錄（審計追蹤）與會話清理自動化。

**主要需求**:
- 管理員使用者：對所有都更專案與系統功能擁有完整無限制存取權
- 一般使用者（理事長/會員/觀察員）：僅能存取指派的 urban_renewal_id，並具有角色特定權限
- 會話管理：24 小時 JWT tokens 搭配 7 天 refresh tokens，自動更新
- 安全性：5 次失敗嘗試後鎖定帳號、記錄所有認證事件
- 營運面：透過排程任務自動清理過期會話

**技術方法**（來自現有實作 + 待填補缺口）:
- 後端：CodeIgniter 4 RESTful API 搭配 JWT 認證 (firebase/php-jwt)
- 前端：Nuxt 3 搭配 Pinia 狀態管理與認證 middleware
- 儲存：MariaDB 用於使用者、會話與新的 authentication_events 審計表
- 測試：後端使用 PHPUnit，前端使用 Vitest
- 新元件：認證事件記錄、排程會話清理任務、API 權限強制執行層

## 技術背景

**語言/版本**: PHP 8.1+ (後端), Node.js/Vue 3 (前端透過 Nuxt 3.13)
**主要相依套件**:
  - 後端: CodeIgniter 4.x, firebase/php-jwt 6.x, PHPUnit 10.5+
  - 前端: Nuxt 3.13, @nuxt/ui 2.18, Pinia 2.2, Vitest 3.2

**儲存**: MariaDB (透過 Docker, 本機 port 4306 / 正式環境 9328)
  - 現有資料表: users, user_sessions, user_permissions, urban_renewals, property_owners
  - 需新增資料表: authentication_events (用於 FR-024, FR-025 的審計記錄)

**測試**:
  - 後端: PHPUnit 10.5 (backend/tests/ 中的單元 + 整合測試)
  - 前端: Vitest 3.2 搭配 @vue/test-utils (frontend/tests/ 中的測試)
  - 契約測試: 根據 OpenAPI 規格驗證 API 端點

**目標平台**:
  - 後端: Linux Docker 容器 (nginx + PHP-FPM)
  - 前端: Node.js 伺服器端渲染 (Nuxt SSR)
  - 資料庫: Docker 中的 MariaDB

**專案類型**: Web 應用程式（獨立的後端 + 前端服務）

**效能目標**:
  - 認證操作在 <2 秒內完成 (SC-010)
  - 錯誤回饋在 1 秒內顯示 (SC-003)
  - 路由阻擋在 500ms 內 (SC-007)
  - 資料庫認證查詢 <100ms (來自假設條件)
  - 有效憑證的登入成功率 99.9% (SC-011)

**限制條件**:
  - JWT tokens: 24 小時過期，HS256 演算法
  - Refresh tokens: 7 天過期
  - 帳號鎖定: 5 次失敗嘗試 → 鎖定 30 分鐘
  - 會話並行: 每位使用者僅保留最新會話
  - 需要繁體中文錯誤訊息
  - 正式環境使用 HTTPS（開發環境使用 HTTP localhost）

**規模/範圍**:
  - 使用者基礎: 小到中型（都更專案利害關係人）
  - 並行使用者: 標準 web 應用程式負載（無特定限制）
  - 會話儲存成長: 透過自動清理緩解 (FR-026)
  - 4 種使用者角色，具有不同權限矩陣
  - 橫跨認證、授權、會話管理的約 26 項功能需求

## 準則檢查

*關卡：必須在階段 0 研究前通過。階段 1 設計後重新檢查。*

**準則狀態**: 已找到專案特定準則檔案 (`.specify/memory/constitution.md`)。根據都更計票系統開發準則進行檢查。

### 準則符合性檢查

✅ **I. 繁體中文優先**: 本文件已使用繁體中文撰寫，符合準則要求
✅ **II. 安全優先開發**: JWT 認證、bcrypt 密碼雜湊、審計記錄已納入計畫
✅ **III. 測試覆蓋率要求**: PHPUnit + Vitest 測試框架已就位，目標 80%+ 覆蓋率
✅ **IV. API 優先架構**: 後端 API 與前端展示層分離
✅ **V. 程式碼品質與可維護性**: 使用 Traits 和 Composables 提升程式碼重用性

**無需解釋的違規情況。**

## 專案結構

### 文件（本功能）

```
specs/002-admin-user/
├── spec.md                      # 功能規格 ✅ 完成
├── IMPLEMENTATION_STATUS.md     # 目前狀態分析 ✅ 完成
├── checklists/
│   └── requirements.md          # 規格品質驗證 ✅ 完成
├── plan.md                      # 本檔案（階段 0-1 /speckit.plan 輸出）
├── research.md                  # 階段 0 輸出 ✅ 完成
├── data-model.md                # 階段 1 輸出（待產生）
├── quickstart.md                # 階段 1 輸出（待產生）
├── contracts/                   # 階段 1 輸出（待產生）
│   ├── auth.openapi.yaml        # 認證 API 契約
│   └── events.openapi.yaml      # 審計事件 API 契約（新增）
└── tasks.md                     # 階段 2 輸出（/speckit.tasks - 非由 /speckit.plan 建立）
```

### 原始碼（儲存庫根目錄）

```
backend/                         # CodeIgniter 4 REST API
├── app/
│   ├── Controllers/Api/
│   │   ├── AuthController.php              # ✅ 已存在 - 登入/登出/更新
│   │   ├── UserController.php              # ⚠️ 更新 - 新增權限檢查
│   │   ├── VotingController.php            # ⚠️ 更新 - 新增角色驗證
│   │   ├── MeetingController.php           # ⚠️ 更新 - 新增 urban_renewal_id 範圍限制
│   │   └── [其他控制器]                     # ⚠️ 稽核 - 驗證 RBAC 強制執行
│   ├── Models/
│   │   ├── UserModel.php                   # ✅ 已存在
│   │   ├── UserSessionModel.php            # ✅ 已存在
│   │   └── AuthenticationEventModel.php    # ❌ 新增 - 用於審計記錄
│   ├── Filters/
│   │   ├── JWTAuthFilter.php               # ✅ 已存在 - Token 驗證
│   │   └── RolePermissionFilter.php        # ❌ 新增 - 角色/資源權限檢查
│   ├── Database/
│   │   ├── Migrations/
│   │   │   ├── 2025-01-01-000010_CreateUserAuthenticationTables.php  # ✅ 已存在
│   │   │   └── 2025-10-24-000001_CreateAuthenticationEventsTable.php # ❌ 新增
│   │   └── Seeds/
│   │       └── UserSeeder.php              # ✅ 已存在 - 測試帳號
│   ├── Helpers/
│   │   ├── auth_helper.php                 # ✅ 已存在
│   │   └── audit_helper.php                # ❌ 新增 - 記錄工具
│   └── Commands/
│       └── SessionCleanup.php              # ❌ 新增 - FR-026 的排程任務
└── tests/
    ├── app/Controllers/Api/
    │   ├── AuthControllerTest.php          # ⚠️ 增強 - 新增審計記錄測試
    │   └── RolePermissionTest.php          # ❌ 新增 - RBAC 整合測試
    └── unit/
        └── AuthenticationEventModelTest.php # ❌ 新增

frontend/                        # Nuxt 3 SSR 應用程式
├── pages/
│   ├── login.vue                           # ✅ 已存在
│   └── unauthorized.vue                    # ✅ 已存在
├── stores/
│   └── auth.js                             # ✅ 已存在（Pinia store）
├── composables/
│   ├── useAuth.js                          # ✅ 已存在
│   ├── useApi.js                           # ⚠️ 更新 - 新增自動更新攔截器
│   └── useRole.js                          # ✅ 已存在
├── middleware/
│   ├── auth.js                             # ✅ 已存在
│   └── role.js                             # ✅ 已存在
├── plugins/
│   └── auth.client.js                      # ✅ 已存在
└── tests/
    ├── composables/
    │   └── useApi.spec.js                  # ❌ 新增 - 測試自動更新邏輯
    └── middleware/
        └── role.spec.js                    # ⚠️ 增強 - 更多角色情境
```

**結構決策**: Web 應用程式架構，獨立的後端（CodeIgniter 4 REST API）與前端（Nuxt 3 SSR）。Docker Compose 協調服務（後端、前端、資料庫、phpMyAdmin）。此結構符合規格分析期間發現的現有程式碼庫。

**圖例**:
- ✅ 已存在: 檔案已實作
- ⚠️ 更新/增強: 檔案存在但需根據規格缺口進行修改
- ❌ 新增: 必須建立檔案以完成功能

## 複雜度追蹤

*無需解釋的違規 - 準則檢查已通過標準實踐。*

## 階段 0：研究與未知問題解決

### 已識別的研究問題

根據規格與技術背景，以下領域需要研究以確保最佳實踐實作：

1. **認證事件記錄架構**
   - **問題**: CodeIgniter 4 中高流量認證事件記錄的最佳方法為何？
   - **背景**: FR-024/FR-025 要求記錄所有認證事件（登入、登出、token 更新、失敗）及 IP/user-agent
   - **評估選項**: 資料庫資料表 vs. 結構化日誌檔案 vs. 混合方法
   - **決策標準**: 效能影響、安全分析查詢效率、儲存成長管理

2. **CodeIgniter 4 中的排程任務實作**
   - **問題**: 如何在 CodeIgniter 4 環境中實作會話清理排程任務 (FR-026)？
   - **背景**: 需要每日/每週清理過期會話；Docker 容器化部署
   - **評估選項**: CodeIgniter Commands + cron、Symfony Scheduler、Laravel 風格的任務排程套件
   - **決策標準**: 可靠性、配置簡易性、Docker 相容性

3. **API 權限層設計模式**
   - **問題**: 在 CodeIgniter 控制器中強制執行角色基礎 + 資源範圍權限的最佳模式為何？
   - **背景**: 需要在所有 API 端點驗證角色權限「與」urban_renewal_id 範圍
   - **評估選項**: Filter 基礎（現有 JWTAuthFilter 模式）、Trait 基礎輔助、Middleware 鏈、Service 層
   - **決策標準**: 程式碼重用性、可維護性、控制器程式碼變更最小化

4. **前端自動 Token 更新策略**
   - **問題**: Nuxt 3 中在 JWT 過期前自動更新的最可靠模式為何？
   - **背景**: FR-022 要求無縫 token 更新；tokens 24 小時過期，refresh tokens 7 天過期
   - **評估選項**: Axios 攔截器（請求/回應）、Nuxt plugin middleware、Pinia action 搭配 setInterval、組合方法
   - **決策標準**: 可靠性（無遺漏更新）、效能（不過度頻繁更新）、錯誤處理（過期的 refresh token）

5. **RBAC 強制執行的測試策略**
   - **問題**: 如何全面測試跨多個控制器與情境的角色基礎存取控制？
   - **背景**: 需要整合測試來驗證 FR-008、FR-014、FR-015、FR-016、FR-017（角色權限）
   - **評估選項**: PHPUnit 整合測試結構、測試資料 fixtures 方法、權限矩陣驗證
   - **決策標準**: 測試覆蓋率完整性、可維護性、執行速度

### 研究任務（待執行）

將派發研究任務以收集最佳實踐並做出明智的技術決策。結果將整合至 `research.md`。

---

**下一步**: 透過專門代理執行階段 0 研究以解決這些問題，然後繼續進行階段 1 設計產物（data-model.md、contracts/、quickstart.md）。
