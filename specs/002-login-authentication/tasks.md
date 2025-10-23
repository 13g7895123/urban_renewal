# Task List: 登入認證功能

**Feature**: 002-login-authentication
**Last Updated**: 2025-10-23 (Updated with security fixes)
**Total Tasks**: 91 (新增 6 個安全性修正任務)
**Completed**: 59 (65%)
**In Progress**: 0 (0%)
**Pending**: 32 (35%)

---

## Task Legend

- ✅ **Completed**: 任務已完成
- ⚠️ **In Progress**: 正在進行中
- ⬜ **Pending**: 尚未開始
- 🔴 **Blocked**: 被阻擋
- ⏸️ **On Hold**: 暫停

**Priority Levels**:
- **P0**: Critical - 必須完成
- **P1**: High - 高優先級
- **P2**: Medium - 中優先級
- **P3**: Low - 低優先級

---

## Phase 1: 需求分析與設計 ✅

### Epic 1.1: 需求收集 ✅
**Owner**: Product Team
**Duration**: 2 days

- [x] **TASK-001** 定義使用者角色和權限 `P0`
  - Completed: 2025-10-08
  - Notes: 定義了 4 種角色：admin, chairman, member, observer

- [x] **TASK-002** 收集 Admin 登入情境需求 `P0`
  - Completed: 2025-10-08
  - Notes: 5 個主要情境已定義

- [x] **TASK-003** 收集 User 登入情境需求 `P0`
  - Completed: 2025-10-08
  - Notes: 包含 chairman, member, observer 子情境

- [x] **TASK-004** 定義安全需求 `P0`
  - Completed: 2025-10-09
  - Notes: JWT, 密碼加密, 帳號鎖定等

### Epic 1.2: 系統設計 ✅
**Owner**: Tech Lead
**Duration**: 3 days

- [x] **TASK-005** 設計資料庫 Schema `P0`
  - Completed: 2025-10-10
  - Deliverable: Migration file created
  - Notes: users, user_sessions, user_permissions 三個表

- [x] **TASK-006** 設計 API 端點 `P0`
  - Completed: 2025-10-10
  - Deliverable: auth.yaml OpenAPI spec
  - Notes: 7 個主要 API 端點

- [x] **TASK-007** 設計 JWT Token 結構 `P0`
  - Completed: 2025-10-10
  - Notes: Payload 包含 user_id, role, urban_renewal_id

- [x] **TASK-008** 設計前端路由和 Middleware `P0`
  - Completed: 2025-10-11
  - Notes: auth middleware 和 role middleware

### Epic 1.3: 文件撰寫 ✅
**Owner**: Tech Writer
**Duration**: 2 days

- [x] **TASK-009** 撰寫功能規格書 (spec.md) `P0`
  - Completed: 2025-10-23
  - Deliverable: spec.md (約 700 行)

- [x] **TASK-010** 撰寫 API 契約文件 `P0`
  - Completed: 2025-10-11
  - Deliverable: auth.yaml

- [x] **TASK-011** 撰寫資料模型文件 `P1`
  - Completed: 2025-10-11
  - Deliverable: data-model.md

---

## Phase 2: 後端開發 ✅

### Epic 2.1: 基礎架構 ✅
**Owner**: Backend Team
**Duration**: 1 day

- [x] **TASK-012** 建立 AuthController `P0`
  - Completed: 2025-10-15
  - File: backend/app/Controllers/Api/AuthController.php
  - Lines: 487

- [x] **TASK-013** 建立 UserModel `P0`
  - Completed: 2025-10-15
  - File: backend/app/Models/UserModel.php
  - Lines: 286

- [x] **TASK-014** 建立 UserSessionModel `P0`
  - Completed: 2025-10-15
  - File: backend/app/Models/UserSessionModel.php

- [x] **TASK-015** 建立 auth_helper.php `P0`
  - Completed: 2025-10-15
  - File: backend/app/Helpers/auth_helper.php

- [x] **TASK-016** 安裝 Firebase JWT Library `P0`
  - Completed: 2025-10-15
  - Command: composer require firebase/php-jwt

### Epic 2.2: 登入登出功能 ✅
**Owner**: Backend Team
**Duration**: 2 days

- [x] **TASK-017** 實作登入 API (POST /api/auth/login) `P0`
  - Completed: 2025-10-16
  - Location: AuthController.php:41
  - Tests: Manual API test passed

- [x] **TASK-018** 實作密碼驗證邏輯 `P0`
  - Completed: 2025-10-16
  - Location: AuthController.php:71
  - Notes: Using password_verify()

- [x] **TASK-019** 實作 JWT Token 生成 `P0`
  - Completed: 2025-10-16
  - Location: AuthController.php:404
  - Notes: HMAC-SHA256 algorithm

- [x] **TASK-020** 實作 Refresh Token 生成 `P0`
  - Completed: 2025-10-16
  - Location: AuthController.php:424
  - Notes: 32 bytes random token

- [x] **TASK-021** 實作 Session 儲存 `P0`
  - Completed: 2025-10-16
  - Location: AuthController.php:432
  - Notes: 儲存到 user_sessions 表

- [x] **TASK-022** 實作登出 API (POST /api/auth/logout) `P0`
  - Completed: 2025-10-16
  - Location: AuthController.php:126
  - Notes: 標記 session 為失效

### Epic 2.3: 安全機制 ✅
**Owner**: Backend Team
**Duration**: 2 days

- [x] **TASK-023** 實作登入失敗計數 `P0`
  - Completed: 2025-10-17
  - Location: AuthController.php:73
  - Notes: 記錄到 users.login_attempts

- [x] **TASK-024** 實作帳號鎖定機制 `P0`
  - Completed: 2025-10-17
  - Location: AuthController.php:77-81
  - Notes: 5 次失敗鎖定 30 分鐘

- [x] **TASK-025** 檢查帳號鎖定狀態 `P0`
  - Completed: 2025-10-17
  - Location: AuthController.php:66-68
  - Notes: 檢查 locked_until 時間

- [x] **TASK-026** 實作密碼重設 Token 生成 `P1`
  - Completed: 2025-10-17
  - Location: AuthController.php:305
  - Notes: 32 bytes random, 1 hour expiry

- [x] **TASK-027** 加入 CORS 保護 `P0`
  - Completed: 2025-10-17
  - Location: AuthController.php:24-26
  - Notes: 在 constructor 設定 headers

### Epic 2.4: 進階功能 ✅
**Owner**: Backend Team
**Duration**: 1 day

- [x] **TASK-028** 實作 Token 刷新 API `P1`
  - Completed: 2025-10-18
  - Location: AuthController.php:165

- [x] **TASK-029** 實作取得使用者資訊 API `P0`
  - Completed: 2025-10-18
  - Location: AuthController.php:236

- [x] **TASK-030** 實作忘記密碼 API `P1`
  - Completed: 2025-10-18
  - Location: AuthController.php:275
  - Notes: 郵件發送待實作

- [x] **TASK-031** 實作重設密碼 API `P1`
  - Completed: 2025-10-18
  - Location: AuthController.php:337

### Epic 2.5: 資料庫 ✅
**Owner**: Backend Team / DBA
**Duration**: 1 day

- [x] **TASK-032** 建立 Migration 檔案 `P0`
  - Completed: 2025-10-15
  - File: 2025-01-01-000010_CreateUserAuthenticationTables.php

- [x] **TASK-033** 執行 Migration `P0`
  - Completed: 2025-10-15
  - Command: php spark migrate

- [x] **TASK-034** 建立測試資料 Seeder `P0`
  - Completed: 2025-10-15
  - File: UserSeeder.php
  - Notes: 4 個測試帳號

- [x] **TASK-035** 執行 Seeder `P0`
  - Completed: 2025-10-15
  - Command: php spark db:seed UserSeeder

---

## Phase 3: 前端開發 ✅

### Epic 3.1: 登入頁面 ✅
**Owner**: Frontend Team
**Duration**: 1 day

- [x] **TASK-036** 建立 login.vue 頁面 `P0`
  - Completed: 2025-10-20
  - File: frontend/pages/login.vue
  - Lines: 203

- [x] **TASK-037** 實作登入表單 `P0`
  - Completed: 2025-10-20
  - Notes: 使用 Nuxt UI components

- [x] **TASK-038** 實作表單驗證 `P0`
  - Completed: 2025-10-20
  - Notes: 必填驗證

- [x] **TASK-039** 實作密碼顯示/隱藏功能 `P1`
  - Completed: 2025-10-20
  - Notes: 使用 eye icon toggle

- [x] **TASK-040** 實作載入狀態 `P1`
  - Completed: 2025-10-20
  - Notes: Button loading state

- [x] **TASK-041** 實作錯誤訊息顯示 `P0`
  - Completed: 2025-10-20
  - Notes: Toast notifications

- [x] **TASK-042** 實作根據角色重定向 `P0`
  - Completed: 2025-10-23
  - Location: login.vue:108-119
  - Notes: Admin→urban-renewal, Others→meeting

### Epic 3.2: 認證機制 ✅
**Owner**: Frontend Team
**Duration**: 2 days

- [x] **TASK-043** 建立 Auth Pinia Store `P0`
  - Completed: 2025-10-21
  - File: frontend/stores/auth.js
  - Lines: 222

- [x] **TASK-044** 實作登入方法 `P0`
  - Completed: 2025-10-21
  - Location: auth.js:14-51

- [x] **TASK-045** 實作登出方法 `P0`
  - Completed: 2025-10-21
  - Location: auth.js:55-79

- [x] **TASK-046** 實作 Token 儲存管理 `P0`
  - Completed: 2025-10-21
  - Notes: 使用 localStorage

- [x] **TASK-047** 實作初始化認證狀態 `P0`
  - Completed: 2025-10-21
  - Location: auth.js:111-133

- [x] **TASK-048** 建立 useAuth composable `P0`
  - Completed: 2025-10-21
  - File: frontend/composables/useAuth.js
  - Lines: 105

- [x] **TASK-049** 建立 useApi composable `P0`
  - Completed: 2025-10-21
  - File: frontend/composables/useApi.js
  - Notes: API 請求封裝，自動加入 token

- [x] **TASK-050** 建立 auth middleware `P0`
  - Completed: 2025-10-21
  - File: frontend/middleware/auth.js
  - Lines: 28

### Epic 3.3: 權限控制 ✅
**Owner**: Frontend Team
**Duration**: 1 day

- [x] **TASK-051** 建立 useRole composable `P0`
  - Completed: 2025-10-23
  - File: frontend/composables/useRole.js
  - Lines: 155

- [x] **TASK-052** 實作角色檢查方法 `P0`
  - Completed: 2025-10-23
  - Notes: hasRole, isAdmin, isChairman, etc.

- [x] **TASK-053** 實作權限檢查方法 `P0`
  - Completed: 2025-10-23
  - Notes: canManageUrbanRenewal, canVote, etc.

- [x] **TASK-054** 建立 role middleware `P0`
  - Completed: 2025-10-23
  - File: frontend/middleware/role.js
  - Lines: 41

- [x] **TASK-055** 建立 unauthorized 頁面 `P0`
  - Completed: 2025-10-23
  - File: frontend/pages/unauthorized.vue
  - Lines: 55

### Epic 3.4: 測試頁面 ✅
**Owner**: Frontend Team
**Duration**: 0.5 day

- [x] **TASK-056** 建立 test-role.vue 頁面 `P1`
  - Completed: 2025-10-23
  - File: frontend/pages/test-role.vue
  - Lines: 273

- [x] **TASK-057** 顯示使用者資訊 `P1`
  - Completed: 2025-10-23
  - Notes: 顯示 username, role, email等

- [x] **TASK-058** 顯示角色檢查結果 `P1`
  - Completed: 2025-10-23
  - Notes: 4 種角色的檢查圖示

- [x] **TASK-059** 顯示權限檢查結果 `P1`
  - Completed: 2025-10-23
  - Notes: 5 種權限的檢查結果

---

## Phase 3.5: 安全性修正 🔴

**Status**: 🔴 **URGENT** - Critical Security Fixes
**Triggered By**: analyze-02.md 分析報告
**Duration**: 3-5 days
**Priority**: P0 (Critical) 和 P1 (High)

### Epic 3.5.1: Critical 安全性修正 (P0) 🔴
**Owner**: Backend Team + Frontend Team
**Duration**: 2.5 days

- [ ] **TASK-099** 實作 CSRF 保護機制 `P0` 🔴
  - Status: Pending
  - Priority: P0 (Critical)
  - Duration: 2 days
  - Owner: Backend Team + Frontend Team
  - Subtasks:
    - [ ] 建立 CSRF middleware (backend/app/Filters/CsrfFilter.php)
    - [ ] 在 AuthController 產生 CSRF Token
    - [ ] 在 login 回應中回傳 CSRF Token
    - [ ] 前端 useApi.js 加入 X-CSRF-Token header
    - [ ] 前端 auth store 儲存 CSRF Token
    - [ ] 測試 CSRF 防護機制
    - [ ] 更新 API 文件說明 CSRF 用法
  - Files to modify:
    - `backend/app/Filters/CsrfFilter.php` (新增)
    - `backend/app/Controllers/Api/AuthController.php` (修改)
    - `frontend/composables/useApi.js` (修改)
    - `frontend/stores/auth.js` (修改)
  - Acceptance Criteria:
    - ✅ 無 CSRF Token 的請求被拒絕
    - ✅ 錯誤的 CSRF Token 被拒絕
    - ✅ 正確的 CSRF Token 通過驗證
  - Related Issues: analyze-02.md P0-1

- [ ] **TASK-100** 修正 CORS 設定 `P0` 🔴
  - Status: Pending
  - Priority: P0 (Critical)
  - Duration: 0.5 day
  - Owner: Backend Team
  - Subtasks:
    - [ ] 建立 Config/Cors.php 設定檔
    - [ ] 從 .env 讀取 ALLOWED_ORIGINS
    - [ ] 移除 AuthController 的 CORS header
    - [ ] 在 middleware 中集中處理 CORS
    - [ ] 更新 .env.example 加入 ALLOWED_ORIGINS
  - Files to modify:
    - `backend/app/Config/Cors.php` (新增)
    - `backend/app/Controllers/Api/AuthController.php` (移除 line 24-26)
    - `backend/.env.example` (新增 ALLOWED_ORIGINS)
    - `backend/app/Filters/CorsFilter.php` (新增)
  - Acceptance Criteria:
    - ✅ 只允許 .env 中指定的來源
    - ✅ 非允許來源的請求被拒絕
    - ✅ OPTIONS preflight 請求正確處理
  - Related Issues: analyze-02.md P0-1

- [ ] **TASK-101** JWT Secret 強制檢查 `P0` 🔴
  - Status: Pending
  - Priority: P0 (Critical)
  - Duration: 0.5 day
  - Owner: Backend Team
  - Subtasks:
    - [ ] 移除 fallback `urban_renewal_secret_key_2025`
    - [ ] JWT_SECRET 為空時拋出異常
    - [ ] 更新 .env.example 加入警告說明
    - [ ] 在應用程式啟動時檢查 JWT_SECRET
    - [ ] 撰寫啟動檢查腳本
  - Files to modify:
    - `backend/app/Controllers/Api/AuthController.php` (line 417, 479)
    - `backend/.env.example` (加入說明)
    - `backend/app/Config/Boot/production.php` (加入檢查)
  - Acceptance Criteria:
    - ✅ JWT_SECRET 未設定時系統拒絕啟動
    - ✅ 不存在任何硬編碼的 secret
  - Related Issues: analyze-02.md P0-2

### Epic 3.5.2: High 安全性增強 (P1) ⚠️
**Owner**: Full-stack Team + DevOps
**Duration**: 5 days (可與測試並行)

- [ ] **TASK-102** Token 改用 httpOnly Cookie `P1` ⚠️
  - Status: Pending
  - Priority: P1 (High)
  - Duration: 3 days
  - Owner: Full-stack Team
  - Note: 可選實作，與 TASK-099 (CSRF) 擇一優先
  - Subtasks:
    - [ ] 後端改用 setcookie() 回傳 Token
    - [ ] 設定 httpOnly, secure, samesite flags
    - [ ] 前端移除 localStorage 的 Token 讀寫
    - [ ] 前端改為自動從 Cookie 讀取
    - [ ] 更新 API 攔截器
    - [ ] 更新所有文件說明
  - Files to modify:
    - `backend/app/Controllers/Api/AuthController.php` (login, refresh 方法)
    - `frontend/stores/auth.js` (移除 token localStorage)
    - `frontend/composables/useApi.js` (改為從 cookie 讀取)
  - Acceptance Criteria:
    - ✅ Token 存在 httpOnly cookie 中
    - ✅ JavaScript 無法讀取 Token
    - ✅ XSS 攻擊無法竊取 Token
  - Related Issues: analyze-02.md P1-4

- [ ] **TASK-103** 強制密碼強度驗證 `P1` ⚠️
  - Status: Pending
  - Priority: P1 (High)
  - Duration: 1 day
  - Owner: Backend Team
  - Subtasks:
    - [ ] 在 UserModel validation rules 加入 passwordStrength
    - [ ] 註冊自訂驗證規則
    - [ ] 更新密碼重設 API 驗證
    - [ ] 更新使用者建立 API 驗證
    - [ ] 測試所有密碼設定端點
  - Files to modify:
    - `backend/app/Models/UserModel.php` (line 40-46)
    - `backend/app/Validation/CustomRules.php` (新增)
    - `backend/app/Controllers/Api/AuthController.php` (resetPassword 方法)
  - Acceptance Criteria:
    - ✅ 弱密碼被拒絕（如 "123456"）
    - ✅ 強密碼通過驗證
    - ✅ 錯誤訊息清楚說明密碼要求
  - Related Issues: analyze-02.md P1-5

- [ ] **TASK-104** Session 自動清理機制 `P1` ⚠️
  - Status: Pending
  - Priority: P1 (High)
  - Duration: 1 day
  - Owner: DevOps Team
  - Subtasks:
    - [ ] 建立 CleanupExpiredSessions Command
    - [ ] 實作清理邏輯（刪除 30 天前過期的 session）
    - [ ] 加入清理日誌
    - [ ] 設定 Cron Job 或 Scheduled Task
    - [ ] 更新部署文件說明排程設定
  - Files to create:
    - `backend/app/Commands/CleanupExpiredSessions.php` (新增)
    - `backend/app/Config/Cron.php` (新增排程設定)
  - Cron Schedule: `0 2 * * *` (每天凌晨 2:00 執行)
  - Acceptance Criteria:
    - ✅ 過期 session 自動清理
    - ✅ 清理操作有日誌記錄
    - ✅ Cron Job 正常運作
  - Related Issues: analyze-02.md P1-6

---

## Phase 4: 測試與 QA ⚠️

**Updated**: 測試階段延後 3 天，等待 Phase 3.5 Critical 修正完成
**New Start Date**: 2025-10-27

### Epic 4.1: 單元測試 ⬜
**Owner**: QA Team
**Duration**: 3 days
**Depends On**: Phase 2, Phase 3

- [ ] **TASK-060** 設定測試環境 `P0`
  - Status: Pending
  - Notes: PHPUnit for backend, Vitest for frontend

- [ ] **TASK-061** AuthController 單元測試 `P0`
  - Status: Pending
  - Target Coverage: 80%
  - Tests:
    - [ ] test_login_success()
    - [ ] test_login_failure()
    - [ ] test_account_locked()
    - [ ] test_logout()
    - [ ] test_token_generation()

- [ ] **TASK-062** UserModel 單元測試 `P0`
  - Status: Pending
  - Tests:
    - [ ] test_create_user()
    - [ ] test_password_hashing()
    - [ ] test_login_attempts()

- [ ] **TASK-063** Auth Store 單元測試 `P0`
  - Status: Pending
  - Tests:
    - [ ] test_login_action()
    - [ ] test_logout_action()
    - [ ] test_initialize_auth()

- [ ] **TASK-064** useRole composable 單元測試 `P0`
  - Status: Pending
  - Tests:
    - [ ] test_role_checks()
    - [ ] test_permission_checks()

### Epic 4.2: 整合測試 ⬜
**Owner**: QA Team
**Duration**: 3 days

- [ ] **TASK-065** API 整合測試 `P0`
  - Status: Pending
  - Tools: Postman / Newman
  - Test Cases: 20+

- [ ] **TASK-066** 前後端整合測試 `P0`
  - Status: Pending
  - Tools: Cypress / Playwright
  - Test Cases: 15+

- [ ] **TASK-067** 資料庫整合測試 `P0`
  - Status: Pending
  - Notes: 驗證資料正確寫入

### Epic 4.3: 功能測試 ⚠️
**Owner**: QA Team
**Duration**: 4 days

- [ ] **TASK-068** Admin 情境測試 `P0`
  - Status: Pending
  - Reference: test-checklist.md Section 1
  - Test Cases: 5

- [ ] **TASK-069** User 情境測試 `P0`
  - Status: Pending
  - Reference: test-checklist.md Section 2
  - Test Cases: 5

- [ ] **TASK-070** 角色權限測試 `P0`
  - Status: Pending
  - Test Cases: 4 roles × 5 permissions

- [ ] **TASK-071** 跨瀏覽器測試 `P1`
  - Status: Pending
  - Browsers: Chrome, Firefox, Safari, Edge

### Epic 4.4: 安全測試 ⚠️
**Owner**: Security Team
**Duration**: 2 days

- [ ] **TASK-072** SQL Injection 測試 `P0`
  - Status: Pending
  - Tools: SQLMap

- [ ] **TASK-073** XSS 攻擊測試 `P0`
  - Status: Pending
  - Tools: XSS Strike

- [ ] **TASK-074** CSRF 攻擊測試 `P1`
  - Status: Pending

- [ ] **TASK-075** 暴力破解測試 `P0`
  - Status: Pending
  - Notes: 驗證帳號鎖定機制

- [ ] **TASK-076** Token 安全測試 `P0`
  - Status: Pending
  - Tests:
    - [ ] Token 竊取測試
    - [ ] Token 重放攻擊
    - [ ] Token 偽造測試

### Epic 4.5: 效能測試 ⚠️
**Owner**: Performance Team
**Duration**: 2 days

- [ ] **TASK-077** 登入 API 效能測試 `P1`
  - Status: Pending
  - Tool: Apache JMeter / k6
  - Target: < 500ms (95th percentile)
  - Load: 100 concurrent users

- [ ] **TASK-078** Token 驗證效能測試 `P1`
  - Status: Pending
  - Target: < 100ms
  - Load: 500 concurrent requests

- [ ] **TASK-079** 資料庫查詢優化 `P1`
  - Status: Pending
  - Notes: 檢查 slow queries

---

## Phase 5: Bug 修復與優化 ⬜

### Epic 5.1: Bug 修復 ⬜
**Owner**: Development Team
**Duration**: 1 week

- [ ] **TASK-080** 建立 Bug 追蹤系統 `P0`
  - Status: Pending
  - Tool: GitHub Issues / Jira

- [ ] **TASK-081** 修復 P0 級別 Bugs `P0`
  - Status: Pending
  - Target: 100% fixed

- [ ] **TASK-082** 修復 P1 級別 Bugs `P1`
  - Status: Pending
  - Target: 95% fixed

- [ ] **TASK-083** 回歸測試 `P0`
  - Status: Pending
  - Notes: 重新執行所有測試案例

---

## Phase 6: 文件與部署 ⚠️

### Epic 6.1: 文件完善 ⚠️
**Owner**: Tech Writer
**Duration**: 2 days

- [x] **TASK-084** 撰寫使用者操作指南 `P0`
  - Completed: 2025-10-23
  - Deliverable: LOGIN_GUIDE.md

- [x] **TASK-085** 撰寫 API 測試說明 `P1`
  - Completed: 2025-10-23
  - Deliverable: API_TEST_INSTRUCTIONS.md

- [x] **TASK-086** 撰寫測試檢查清單 `P0`
  - Completed: 2025-10-23
  - Deliverable: test-checklist.md

- [x] **TASK-087** 撰寫實作計畫 `P0`
  - Completed: 2025-10-23
  - Deliverable: plan.md

- [ ] **TASK-088** 撰寫部署指南 `P0`
  - Status: Pending
  - Deliverable: deployment-guide.md

- [ ] **TASK-089** 發布 API 文件 `P1`
  - Status: Pending
  - Tool: Swagger UI / ReDoc

### Epic 6.2: 部署準備 ⬜
**Owner**: DevOps Team
**Duration**: 2 days

- [ ] **TASK-090** 環境變數設定檢查 `P0`
  - Status: Pending
  - Files: .env, .env.production

- [ ] **TASK-091** Docker 配置檢查 `P0`
  - Status: Pending
  - Files: docker-compose.yml

- [ ] **TASK-092** CI/CD Pipeline 設定 `P1`
  - Status: Pending
  - Tool: GitHub Actions / GitLab CI

- [ ] **TASK-093** 監控設定 `P1`
  - Status: Pending
  - Tool: Prometheus / Grafana

- [ ] **TASK-094** 日誌設定 `P1`
  - Status: Pending
  - Tool: ELK Stack / CloudWatch

### Epic 6.3: UAT ⬜
**Owner**: Product Team
**Duration**: 1 week

- [ ] **TASK-095** 準備 UAT 環境 `P0`
  - Status: Pending

- [ ] **TASK-096** 準備 UAT 測試案例 `P0`
  - Status: Pending

- [ ] **TASK-097** 執行 UAT `P0`
  - Status: Pending

- [ ] **TASK-098** 收集使用者反饋 `P0`
  - Status: Pending

---

## Summary Statistics

### By Phase
- Phase 1 (需求分析與設計): 11/11 (100%) ✅
- Phase 2 (後端開發): 24/24 (100%) ✅
- Phase 3 (前端開發): 24/24 (100%) ✅
- **Phase 3.5 (安全性修正)**: 0/6 (0%) 🔴 **URGENT**
- Phase 4 (測試與 QA): 0/20 (0%) ⚠️
- Phase 5 (Bug 修復): 0/4 (0%) ⬜
- Phase 6 (文件與部署): 4/15 (27%) ⚠️

**Total**: 59/98 (60% complete)

### By Priority
- P0 (Critical): 42/71 (59%) - **新增 3 個 P0 安全性任務**
- P1 (High): 14/25 (56%) - **新增 3 個 P1 安全性任務**
- P2 (Medium): 0/3 (0%)
- P3 (Low): 3/5 (60%)

### By Status
- ✅ Completed: 59 (65%)
- ⚠️ In Progress: 0 (0%)
- ⬜ Pending: 32 (35%) - **新增 6 個安全性修正任務**
- 🔴 Blocked: 0 (0%)

### 🔴 New Security Fix Tasks (Phase 3.5)
- TASK-099: CSRF 保護機制 (P0) 🔴
- TASK-100: CORS 設定修正 (P0) 🔴
- TASK-101: JWT Secret 強制檢查 (P0) 🔴
- TASK-102: Token 改用 httpOnly Cookie (P1)
- TASK-103: 強制密碼強度驗證 (P1)
- TASK-104: Session 自動清理機制 (P1)

---

## Critical Path

The following tasks are on the critical path and must be completed for go-live:

### 🔴 Phase 3.5: Security Fixes (MUST DO FIRST)
1. **TASK-099** 實作 CSRF 保護機制 (P0) 🔴 ⬜
2. **TASK-100** 修正 CORS 設定 (P0) 🔴 ⬜
3. **TASK-101** JWT Secret 強制檢查 (P0) 🔴 ⬜

### Phase 4: Testing (After Security Fixes)
4. **TASK-068** Admin 情境測試 (P0) ⬜
5. **TASK-069** User 情境測試 (P0) ⬜
3. **TASK-070** 角色權限測試 (P0) ⬜
4. **TASK-072** SQL Injection 測試 (P0) ⬜
5. **TASK-073** XSS 攻擊測試 (P0) ⬜
6. **TASK-081** 修復 P0 級別 Bugs (P0) ⬜
7. **TASK-088** 撰寫部署指南 (P0) ⬜
8. **TASK-090** 環境變數設定檢查 (P0) ⬜
9. **TASK-097** 執行 UAT (P0) ⬜

---

## Next Actions

### This Week
1. 執行功能測試（TASK-068, 069, 070）
2. 執行安全測試（TASK-072, 073, 075, 076）
3. 執行效能測試（TASK-077, 078）

### Next Week
1. 修復發現的 Bugs
2. 執行回歸測試
3. 完成部署文件

### Next Month
1. UAT 測試
2. 正式環境部署
3. 監控和優化

---

**Task List Owner**: Project Manager
**Last Updated**: 2025-10-23
**Next Review**: 2025-10-30
