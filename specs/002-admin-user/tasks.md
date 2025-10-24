# Tasks: 管理員與使用者登入情境

**輸入**: 設計文件來自 `/specs/002-admin-user/`
**前置條件**: plan.md, spec.md, research.md, IMPLEMENTATION_STATUS.md

**測試**: 根據專案憲法要求，本功能包含測試任務以達成 80%+ 涵蓋率目標

**組織結構**: 任務按使用者故事分組，以實現每個故事的獨立實作和測試

## 格式: `[ID] [P?] [Story] 描述`
- **[P]**: 可並行執行（不同檔案、無相依性）
- **[Story]**: 此任務屬於哪個使用者故事（例如 US1、US2、US3、US4）
- 描述中包含精確的檔案路徑

## 路徑慣例
- **後端**: `backend/app/`，測試在 `backend/tests/`
- **前端**: `frontend/`，測試在 `frontend/tests/`
- **Docker**: `cron/`，配置在 `docker-compose.yml`

---

## Phase 1: 設定（共享基礎設施）

**目的**: Docker cron 服務設定與測試基礎架構準備

- [X] T001 [P] 建立 Docker cron 容器 Dockerfile 在 `cron/Dockerfile`
- [X] T002 [P] 建立 crontab 配置檔案在 `cron/crontab`（每日 2:00AM 清理會話）
- [X] T003 更新 `docker-compose.yml` 新增 cron 服務配置

---

## Phase 2: 基礎（阻塞性前置條件）

**目的**: 所有使用者故事實作前必須完成的核心基礎設施

**⚠️ 關鍵**: 在此階段完成前無法開始任何使用者故事工作

### 認證事件審計基礎設施（FR-024, FR-025）

- [X] T004 建立 authentication_events 資料表遷移在 `backend/app/Database/Migrations/2025-10-24-000001_CreateAuthenticationEventsTable.php`
- [X] T005 [P] 建立 AuthenticationEventModel 在 `backend/app/Models/AuthenticationEventModel.php`（包含 getFailedLoginsByIP、getUserAuthHistory、deleteOldEvents 方法）
- [X] T006 [P] 建立 audit_helper.php 在 `backend/app/Helpers/audit_helper.php`（實作 log_auth_event 函數）
- [X] T007 執行資料庫遷移以建立 authentication_events 資料表

### RBAC 權限基礎設施（FR-008, FR-014）

- [X] T008 建立 HasRbacPermissions trait 在 `backend/app/Traits/HasRbacPermissions.php`（checkResourceScope、checkRolePermission、getResourceUrbanRenewalId 方法）
- [X] T009 更新 `backend/app/Helpers/auth_helper.php` 新增 auth_check_resource_scope 和 auth_can_access_resource 函數

### 排程任務基礎設施（FR-026）

- [X] T010 建立 SessionCleanup Command 在 `backend/app/Commands/SessionCleanup.php`
- [X] T011 建立 AuthEventCleanup Command 在 `backend/app/Commands/AuthEventCleanup.php`

**Checkpoint**: 基礎設施就緒 - 使用者故事實作現可並行開始

---

## Phase 3: 使用者故事 1 - 管理員登入與系統管理存取權 (優先級：P1) 🎯 MVP

**目標**: 管理員可以登入並存取所有系統功能，不受 urban_renewal_id 限制，包含認證事件審計記錄

**獨立測試**: 使用管理員憑證（帳號：`admin`，密碼：`password`）登入，驗證導向到 `/tables/urban-renewal`，可查看所有都更專案

### 後端實作 - 管理員認證與審計

- [X] T012 [US1] 在 AuthController login 方法新增 log_auth_event 呼叫（成功登入事件）在 `backend/app/Controllers/Api/AuthController.php:72-78`
- [X] T013 [US1] 在 AuthController login 方法新增失敗登入的 log_auth_event 呼叫在 `backend/app/Controllers/Api/AuthController.php:58-62,83-87`
- [X] T014 [US1] 在 AuthController logout 方法新增 log_auth_event 呼叫在 `backend/app/Controllers/Api/AuthController.php:93-103`
- [X] T015 [US1] 在 AuthController refresh 方法新增 log_auth_event 呼叫（token 更新事件）在 `backend/app/Controllers/Api/AuthController.php:112-130`
- [X] T016 [US1] 在 AuthController 在管理員登入時正確設定 urban_renewal_id=null 在 `backend/app/Controllers/Api/AuthController.php:72`

### 測試 - 管理員認證與審計

- [X] T017 [P] [US1] 建立 AuthenticationEventModelTest 單元測試在 `backend/tests/Unit/Models/AuthenticationEventModelTest.php`（測試 getFailedLoginsByIP、getUserAuthHistory、deleteOldEvents）
- [X] T018 [P] [US1] 更新 AuthControllerTest 新增審計記錄測試在 `backend/tests/app/Controllers/Api/AuthControllerTest.php`（驗證登入成功/失敗/登出/更新都記錄事件）
- [X] T019 [P] [US1] 建立 AdminPermissionsTest 整合測試在 `backend/tests/Feature/RBAC/AdminPermissionsTest.php`（驗收情境 1-5）

**Checkpoint**: 管理員可登入、登出、更新 token，所有認證事件被記錄，管理員擁有無限制存取權

---

## Phase 4: 使用者故事 2 - 一般使用者登入與範圍存取 (優先級：P1)

**目標**: 理事長、會員、觀察員可登入並僅存取其指派的 urban_renewal_id 資源，實施 RBAC 強制執行

**獨立測試**: 使用會員憑證（帳號：`member1`，密碼：`password`）登入，驗證導向到 `/tables/meeting`，僅能查看其 urban_renewal_id 的資料

### 後端實作 - RBAC 強制執行

- [X] T020 [P] [US2] 在 UserController 新增 HasRbacPermissions trait 使用並實作權限檢查在 `backend/app/Controllers/Api/UserController.php`
- [X] T021 [P] [US2] 在 VotingController 新增 HasRbacPermissions trait 使用並實作權限檢查在 `backend/app/Controllers/Api/VotingController.php`
- [X] T022 [P] [US2] 在 MeetingController 新增 HasRbacPermissions trait 使用並實作權限檢查在 `backend/app/Controllers/Api/MeetingController.php`
- [X] T023 [P] [US2] 稽核並更新其他 10+ 個控制器新增 RBAC 檢查在 `backend/app/Controllers/Api/` 目錄（DocumentController、NotificationController 等）

### 測試基礎設施

- [X] T024 [P] [US2] 建立 UserFixture 測試工廠在 `backend/tests/Support/Fixtures/UserFixture.php`（包含所有 4 種角色的測試使用者和 generateToken 方法）
- [X] T025 [P] [US2] 建立 UrbanRenewalFixture 測試工廠在 `backend/tests/Support/Fixtures/UrbanRenewalFixture.php`
- [X] T026 [P] [US2] 建立 MeetingFixture 測試工廠在 `backend/tests/Support/Fixtures/MeetingFixture.php`

### 測試 - RBAC 權限矩陣

- [X] T027 [P] [US2] 建立 ChairmanPermissionsTest 在 `backend/tests/Feature/RBAC/ChairmanPermissionsTest.php`（驗收情境 2、6、7）
- [X] T028 [P] [US2] 建立 MemberPermissionsTest 在 `backend/tests/Feature/RBAC/MemberPermissionsTest.php`（驗收情境 1、4、7）
- [X] T029 [P] [US2] 建立 ObserverPermissionsTest 在 `backend/tests/Feature/RBAC/ObserverPermissionsTest.php`（驗收情境 5）
- [X] T030 [P] [US2] 建立 CrossUrbanRenewalAccessTest 在 `backend/tests/Feature/RBAC/CrossUrbanRenewalAccessTest.php`（驗收情境 3、US4 驗收情境 5）
- [X] T031 [P] [US2] 建立 AuthHelperTest 單元測試在 `backend/tests/Unit/Helpers/AuthHelperTest.php`（測試 auth_check_resource_scope 和 auth_can_access_resource）

**Checkpoint**: 一般使用者登入後僅能存取其 urban_renewal_id 資源，角色權限正確強制執行，所有 RBAC 測試通過

---

## Phase 5: 使用者故事 3 - 會話管理與安全性 (優先級：P2)

**目標**: 實作自動 token 更新、安全登出、會話清理，確保無縫使用者體驗與資料安全

**獨立測試**: 登入後等待接近 24 小時 token 過期，驗證自動更新；或登出並確認 tokens 被清除且會話無效

### 前端實作 - 自動 Token 更新

- [X] T032 [US3] 更新 auth.js Pinia store 新增 tokenExpiresAt 狀態和 decodeToken、isTokenExpiringSoon 輔助函數在 `frontend/stores/auth.js`
- [X] T033 [US3] 更新 auth.js login action 儲存 refresh_token 和 token_expires_at 到 localStorage 在 `frontend/stores/auth.js`
- [X] T034 [US3] 建立 token-refresh.client.js plugin 實作主動排程更新在 `frontend/plugins/token-refresh.client.js`（包含 scheduleTokenRefresh 和 watch）
- [X] T035 [US3] 更新 useApi.js 實作被動 401 攔截器與重試邏輯在 `frontend/composables/useApi.js`（包含 isRefreshing 旗標和 refreshPromise）

### 後端實作 - 會話清理

- [X] T036 [US3] 驗證 SessionCleanup Command 正確刪除過期會話（手動測試 `php spark session:cleanup --force`）
- [X] T037 [US3] 驗證 Docker cron 容器正確執行排程任務（檢查 `docker-compose logs -f cron`）

### 測試 - 會話管理

- [X] T038 [P] [US3] 建立 useApi.spec.js 測試在 `frontend/tests/composables/useApi.spec.js`（測試自動更新邏輯、401 重試、並發請求處理）
- [X] T039 [P] [US3] 更新 role.spec.js 增強測試在 `frontend/tests/middleware/role.spec.js`（更多角色情境）

**Checkpoint**: Token 在過期前自動更新（95% 成功率）、401 錯誤自動重試、登出正確清除 tokens、過期會話自動清理

---

## Phase 6: 使用者故事 4 - 角色基礎存取控制與路由保護 (優先級：P2)

**目標**: 強化前端和後端的角色基礎路由保護，防止權限提升和未授權存取

**獨立測試**: 以不同角色登入（admin、chairman、member、observer），驗證只能存取允許的路由，未授權嘗試導向到 `/unauthorized`

### 後端實作 - API 端點權限驗證

- [X] T040 [US4] 在 VotingController 驗證觀察員無法提交投票（後端驗證）在 `backend/app/Controllers/Api/VotingController.php`
- [X] T041 [US4] 在 MeetingController 驗證會員無法建立會議（後端驗證）在 `backend/app/Controllers/Api/MeetingController.php`
- [X] T042 [US4] 在 UserController 驗證理事長無法管理使用者（缺少 system_admin 權限）在 `backend/app/Controllers/Api/UserController.php`

### 測試 - 路由保護與權限拒絕

- [X] T043 [P] [US4] 建立 RolePermissionTest 整合測試在 `backend/tests/app/Controllers/Api/RolePermissionTest.php`（驗收情境 1-5：路由保護、API 拒絕、403/401 回應）
- [X] T044 [P] [US4] 更新現有控制器測試驗證 RBAC 強制執行在 `backend/tests/Feature/Controllers/`（UserControllerTest、MeetingControllerTest、VotingControllerTest）

**Checkpoint**: 所有角色僅能存取其允許的路由和 API 端點，未授權嘗試被正確拒絕並返回 403/401，前後端路由保護一致

---

## Phase 7: 精煉與跨領域關注

**目的**: 跨多個使用者故事的改進與最終驗證

### 程式碼品質與測試涵蓋率

- [X] T045 [P] 執行 PHPUnit 涵蓋率報告 `XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html build/coverage`
- [X] T046 [P] 驗證涵蓋率達標：Auth/RBAC 95%、Controllers 85%、Helpers 90%、Models 80%、整體 80%+
- [X] T047 [P] 補充缺失的單元測試以達成涵蓋率目標在 `backend/tests/Unit/`
- [X] T048 程式碼重構：提取重複的權限檢查邏輯到 trait 方法

### 安全性強化

- [X] T049 [P] 驗證所有敏感欄位已從 API 回應移除（password_hash、password_reset_token、login_attempts）
- [X] T050 [P] 驗證所有錯誤訊息使用繁體中文
- [X] T051 安全性審查：檢查所有 API 端點的 JWT 驗證與 RBAC 強制執行

### 文件與驗證

- [X] T052 [P] 更新 IMPLEMENTATION_STATUS.md 標記所有功能為完成
- [X] T053 [P] 建立測試執行文件記錄如何執行所有測試套件
- [X] T054 執行完整測試套件驗證所有使用者故事

---

## 相依性與執行順序

### 階段相依性

- **設定 (Phase 1)**: 無相依性 - 可立即開始
- **基礎 (Phase 2)**: 依賴設定完成 - 阻塞所有使用者故事
- **使用者故事 (Phase 3-6)**: 全部依賴基礎階段完成
  - 使用者故事可並行進行（如有人力）
  - 或按優先順序依序進行（P1 → P1 → P2 → P2）
- **精煉 (Phase 7)**: 依賴所有期望的使用者故事完成

### 使用者故事相依性

- **使用者故事 1 (P1)**: 基礎階段後可開始 - 無其他故事相依性（獨立）
- **使用者故事 2 (P1)**: 基礎階段後可開始 - 無其他故事相依性（獨立，但使用 US1 建立的審計基礎設施）
- **使用者故事 3 (P2)**: 基礎階段後可開始 - 無其他故事相依性（獨立）
- **使用者故事 4 (P2)**: 基礎階段後可開始 - 與 US2 共享 RBAC 基礎設施但獨立可測

### 每個使用者故事內

- 測試基礎設施 before 整合測試
- 後端實作 before 測試執行
- Models before Services before Controllers
- 核心實作 before 整合
- 故事完成 before 移至下個優先級

### 並行機會

- Phase 1 所有標記 [P] 的任務可並行執行
- Phase 2 所有標記 [P] 的任務可並行執行（在階段 2 內）
- Phase 2 完成後，所有使用者故事可並行開始（如團隊容量允許）
- 每個使用者故事內所有標記 [P] 的測試可並行執行
- 每個使用者故事內所有標記 [P] 的 Models 可並行執行
- 不同使用者故事可由不同團隊成員並行開發

---

## 並行範例：使用者故事 2

```bash
# 同時啟動測試基礎設施建立：
Task: "建立 UserFixture 測試工廠在 backend/tests/Support/Fixtures/UserFixture.php"
Task: "建立 UrbanRenewalFixture 測試工廠在 backend/tests/Support/Fixtures/UrbanRenewalFixture.php"
Task: "建立 MeetingFixture 測試工廠在 backend/tests/Support/Fixtures/MeetingFixture.php"

# 同時啟動所有 RBAC 權限矩陣測試：
Task: "建立 ChairmanPermissionsTest 在 backend/tests/Feature/RBAC/ChairmanPermissionsTest.php"
Task: "建立 MemberPermissionsTest 在 backend/tests/Feature/RBAC/MemberPermissionsTest.php"
Task: "建立 ObserverPermissionsTest 在 backend/tests/Feature/RBAC/ObserverPermissionsTest.php"
Task: "建立 CrossUrbanRenewalAccessTest 在 backend/tests/Feature/RBAC/CrossUrbanRenewalAccessTest.php"

# 同時啟動所有控制器的 RBAC 更新：
Task: "在 UserController 新增 HasRbacPermissions trait"
Task: "在 VotingController 新增 HasRbacPermissions trait"
Task: "在 MeetingController 新增 HasRbacPermissions trait"
```

---

## 實作策略

### MVP 優先（僅使用者故事 1）

1. 完成 Phase 1: 設定
2. 完成 Phase 2: 基礎（關鍵 - 阻塞所有故事）
3. 完成 Phase 3: 使用者故事 1
4. **停止並驗證**: 獨立測試使用者故事 1
5. 如就緒則部署/展示

### 增量交付

1. 完成設定 + 基礎 → 基礎就緒
2. 新增使用者故事 1 → 獨立測試 → 部署/展示（MVP！）
3. 新增使用者故事 2 → 獨立測試 → 部署/展示
4. 新增使用者故事 3 → 獨立測試 → 部署/展示
5. 新增使用者故事 4 → 獨立測試 → 部署/展示
6. 每個故事增加價值而不破壞先前故事

### 並行團隊策略

多位開發者時：

1. 團隊共同完成設定 + 基礎
2. 基礎完成後：
   - 開發者 A: 使用者故事 1（管理員登入與審計）
   - 開發者 B: 使用者故事 2（RBAC 強制執行）
   - 開發者 C: 使用者故事 3（自動 token 更新）
   - 開發者 D: 使用者故事 4（路由保護）
3. 故事獨立完成並整合

---

## 備註

- [P] 任務 = 不同檔案、無相依性
- [Story] 標籤將任務映射到特定使用者故事以便追溯
- 每個使用者故事應獨立完成並可測試
- 實作前驗證測試失敗（TDD）
- 每個任務或邏輯群組後 commit
- 在任何 checkpoint 停止以獨立驗證故事
- 避免：模糊任務、同檔案衝突、破壞獨立性的跨故事相依性

---

## 任務摘要

- **總任務數**: 54
- **設定階段**: 3 任務
- **基礎階段**: 8 任務（阻塞所有使用者故事）
- **使用者故事 1 (P1)**: 8 任務（管理員登入與審計）
- **使用者故事 2 (P1)**: 12 任務（RBAC 強制執行）
- **使用者故事 3 (P2)**: 8 任務（會話管理）
- **使用者故事 4 (P2)**: 5 任務（路由保護）
- **精煉階段**: 10 任務
- **並行機會**: 36 個任務標記 [P] 可並行執行

**建議 MVP 範圍**: Phase 1-2（基礎設施）+ Phase 3（使用者故事 1：管理員登入與審計）= 19 任務

**測試涵蓋目標**:
- 認證/授權: 95% 行涵蓋率
- 控制器: 85% 行涵蓋率
- Helpers: 90% 行涵蓋率
- Models: 80% 行涵蓋率
- 整體專案: 80% 最低涵蓋率
