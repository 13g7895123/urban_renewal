# Tasks: 都更計票系統 - 完整功能實作

**Input**: 設計文件來自 `/home/jarvis/project/bonus/urban_renewal/specs/001-view/`
**Prerequisites**: spec.md (14 個 User Stories), plan.md, data-model.md (18 個資料表), contracts/ (API 規範)

**專案類型**: Web Application (前後端分離)
- **後端**: `/home/jarvis/project/bonus/urban_renewal/backend/app/`
- **前端**: `/home/jarvis/project/bonus/urban_renewal/frontend/`

**重要說明**: 這是一個**現有專案**，許多功能已實作。任務目標是**驗證現有實作並補充缺失**。

---

## Format: `[ID] [P?] [Story] Description`
- **[P]**: 可並行執行（不同檔案，無相依性）
- **[Story]**: 所屬 User Story（US1-US14）
- 包含具體的檔案路徑

---

## Phase 1: Setup (專案初始化)

**目的**: 專案環境配置和基礎結構驗證

- [X] T001 驗證 Docker Compose 配置 (docker-compose.yml) 是否包含所有必要服務
- [X] T002 驗證後端環境變數配置 (backend/.env.example) 是否完整
- [X] T003 [P] 驗證前端環境變數配置 (frontend/.env.example) 是否完整
- [X] T004 [P] 驗證 Port 配置符合規範 (前端:4001, 後端:4002, MySQL:4306, phpMyAdmin:4003)
- [X] T005 驗證資料庫連線設定 (backend/app/Config/Database.php)

**Checkpoint**: 開發環境可正常啟動，所有服務可互相通訊

---

## Phase 2: Foundational (基礎架構 - 阻塞所有 User Stories)

**目的**: 核心基礎設施，必須完成才能實作任何 User Story

**⚠️ 重要**: 所有 User Story 實作必須等待此階段完成

### 資料庫架構 (Database Schema)

- [X] T006 驗證縣市/鄉鎮市區/地段資料表已建立 (counties, districts, sections)
- [X] T007 驗證都市更新會資料表已建立並包含所有必要欄位 (urban_renewals)
- [X] T008 驗證地籍資料表已建立 (land_plots, buildings)
- [X] T009 驗證所有權人資料表已建立 (property_owners)
- [X] T010 驗證所有權關係資料表已建立 (owner_land_ownerships, owner_building_ownerships)
- [X] T011 驗證會議相關資料表已建立 (meetings, meeting_attendances, meeting_documents)
- [X] T012 驗證投票系統資料表已建立 (voting_topics, voting_records)
- [X] T013 驗證使用者認證資料表已建立 (users, user_sessions)
- [X] T014 驗證系統設定和通知資料表已建立 (system_settings, notifications)
- [X] T015 驗證所有外鍵約束和索引已正確建立
- [X] T016 建立資料庫種子資料 (Seeder) 用於開發測試 (backend/app/Database/Seeds/DevelopmentSeeder.php)

### 身份驗證框架 (Authentication Framework)

- [X] T017 [P] 驗證 JWT 認證中介軟體已實作 (backend/app/Filters/JWTAuthFilter.php)
- [X] T018 [P] 驗證 CORS 設定已正確配置 (backend/app/Config/Cors.php)
- [X] T019 [P] 驗證權限檢查中介軟體已實作 (backend/app/Filters/RoleFilter.php)
- [X] T020 驗證 Token 產生和驗證邏輯已實作 (auth_helper.php functions)

### API 路由和中介軟體 (API Routes & Middleware)

- [X] T021 驗證 API 路由定義完整性 (backend/app/Config/Routes.php)
- [X] T022 驗證 API 回應格式標準化 (backend/app/Helpers/ResponseHelper.php)
- [X] T023 [P] 驗證錯誤處理機制 (backend/app/Config/Exceptions.php)
- [X] T024 [P] 驗證日誌記錄配置 (backend/app/Config/Logger.php)

### 基礎模型 (Base Models)

- [X] T025 [P] 驗證 UserModel 包含角色權限管理邏輯 (backend/app/Models/UserModel.php)
- [X] T026 [P] 驗證 UserSessionModel 包含 Session 管理邏輯 (backend/app/Models/UserSessionModel.php)
- [X] T027 驗證所有模型遵循軟刪除機制 (deleted_at 欄位處理)

**Checkpoint**: 基礎設施完成 - User Story 實作現可開始並行進行

---

## Phase 3: US1 - 使用者身份驗證與授權 (Priority: P1) 🎯 MVP 核心

**Goal**: 實現完整的使用者登入、登出、權限驗證功能，確保系統安全性

**Independent Test**: 建立測試帳號，嘗試登入、登出、存取需要權限的 API，驗證 Token 過期處理

### 後端實作

- [X] T028 [P] [US1] 驗證 AuthController 包含所有認證端點 (backend/app/Controllers/Api/AuthController.php)
  - POST /api/auth/login
  - POST /api/auth/logout
  - POST /api/auth/refresh
  - GET /api/auth/me
  - POST /api/auth/forgot-password
  - POST /api/auth/reset-password
- [X] T029 [US1] 補充登入失敗次數限制邏輯 (AuthController::login)
- [X] T030 [US1] 補充帳號鎖定機制 (UserModel::checkAccountLock)
- [X] T031 [US1] 補充密碼重設功能 (AuthController::forgotPassword, resetPassword)
- [X] T032 [US1] 驗證 JWT Token 刷新機制 (AuthController::refresh)

### 前端實作

- [X] T033 [P] [US1] 驗證登入頁面已實作 (frontend/pages/login.vue)
- [X] T034 [P] [US1] 補充註冊頁面（如需要）(frontend/pages/signup.vue) - Not needed per requirements
- [X] T035 [US1] 建立 useAuth composable (frontend/composables/useAuth.js)
  - login(), logout(), refreshToken(), getCurrentUser()
  - 處理 Token 儲存和自動刷新
- [X] T036 [US1] 建立認證中介軟體 (frontend/middleware/auth.js)
  - 驗證 Token 有效性
  - 自動導向登入頁面
- [X] T037 [US1] 建立角色權限檢查中介軟體 (frontend/middleware/admin.js)
- [X] T038 [US1] 在首頁新增登出按鈕和使用者資訊顯示 (frontend/pages/index.vue)

**Checkpoint**: 使用者可以登入、登出、刷新 Token，系統會檢查權限和 Token 有效性

---

## Phase 4: US2 - 都市更新會管理 (Priority: P1) 🎯 MVP 核心

**Goal**: 實現都市更新會的完整 CRUD 操作，包括基本資料管理

**Independent Test**: 建立一個新的都市更新會，填入完整資料，然後檢視、編輯、刪除該更新會

### 後端實作

- [X] T039 [P] [US2] 驗證 UrbanRenewalModel 已實作 (backend/app/Models/UrbanRenewalModel.php)
- [X] T040 [P] [US2] 驗證 UrbanRenewalController 包含所有 CRUD 端點 (backend/app/Controllers/Api/UrbanRenewalController.php)
  - GET /api/urban-renewals (列表)
  - POST /api/urban-renewals (建立)
  - GET /api/urban-renewals/{id} (詳情)
  - PUT /api/urban-renewals/{id} (更新)
  - DELETE /api/urban-renewals/{id} (刪除)
- [X] T041 [US2] 補充搜尋和篩選功能 (UrbanRenewalController::index)
- [X] T042 [US2] 補充分頁功能 (UrbanRenewalController::index)
- [X] T043 [US2] 補充資料驗證規則 (UrbanRenewalController::validate)

### 前端實作

- [X] T044 [P] [US2] 驗證都市更新會列表頁面已實作 (frontend/pages/tables/urban-renewal/index.vue)
- [X] T045 [P] [US2] 驗證都市更新會基本資料頁面已實作 (frontend/pages/tables/urban-renewal/[id]/basic-info.vue)
- [X] T046 [US2] 建立 useUrbanRenewal composable (frontend/composables/useUrbanRenewal.js)
  - fetchList(), fetchById(), create(), update(), delete()
  - 搜尋和篩選邏輯
- [X] T047 [US2] 建立都市更新會表單元件 (frontend/components/UrbanRenewal/Form.vue)
- [X] T048 [US2] 建立都市更新會卡片元件 (frontend/components/UrbanRenewal/Card.vue)
- [X] T049 [US2] 補充都市更新會新建頁面 (frontend/pages/tables/urban-renewal/create.vue)

**Checkpoint**: 可以完整管理都市更新會的 CRUD 操作，包括搜尋和分頁

---

## Phase 5: US6 - 會議管理 (Priority: P1) 🎯 MVP 核心

**Goal**: 實現會議的完整管理功能，包括會議建立、狀態管理、法定人數設定

**Independent Test**: 建立一個新會議，設定法定人數，然後更新會議狀態並驗證狀態轉換規則

### 後端實作

- [X] T050 [P] [US6] 驗證 MeetingModel 已實作 (backend/app/Models/MeetingModel.php)
- [X] T051 [P] [US6] 驗證 MeetingController 包含所有會議端點 (backend/app/Controllers/Api/MeetingController.php)
  - GET /api/meetings (列表)
  - POST /api/meetings (建立)
  - GET /api/meetings/{id} (詳情)
  - PUT /api/meetings/{id} (更新)
  - DELETE /api/meetings/{id} (刪除)
  - PATCH /api/meetings/{id}/status (狀態更新)
  - GET /api/meetings/{id}/statistics (統計)
- [X] T052 [US6] 補充會議狀態轉換驗證 (MeetingModel::validateStatusTransition)
- [X] T053 [US6] 補充法定人數計算邏輯 (MeetingModel::calculateQuorum)
- [X] T054 [US6] 補充會議時間衝突檢查 (MeetingController::checkConflict)

### 前端實作

- [X] T055 [P] [US6] 驗證會議列表頁面已實作 (frontend/pages/tables/meeting/index.vue)
- [X] T056 [P] [US6] 驗證會議基本資料頁面已實作 (frontend/pages/tables/meeting/[meetingId]/basic-info.vue)
- [X] T057 [US6] 建立 useMeetings composable (frontend/composables/useMeetings.js)
  - fetchList(), fetchById(), create(), update(), delete(), updateStatus()
- [X] T058 [US6] 建立會議表單元件 (frontend/components/Meeting/Form.vue)
- [X] T059 [US6] 建立會議狀態徽章元件 (frontend/components/Meeting/StatusBadge.vue)
- [X] T060 [US6] 建立法定人數設定元件 (frontend/components/Meeting/QuorumSettings.vue)
- [X] T061 [US6] 補充會議新建頁面 (frontend/pages/tables/meeting/create.vue)

**Checkpoint**: 可以完整管理會議，包括狀態轉換和法定人數設定

---

## Phase 6: US8 - 投票議題管理 (Priority: P1) 🎯 MVP 核心

**Goal**: 實現投票議題的完整管理，包括議題建立、投票方式設定、議題狀態管理

**Independent Test**: 在特定會議下建立投票議題，設定投票方式，開啟和關閉投票

### 後端實作

- [X] T062 [P] [US8] 驗證 VotingTopicModel 已實作 (backend/app/Models/VotingTopicModel.php)
- [X] T063 [P] [US8] 驗證 VotingTopicController 包含所有議題端點 (backend/app/Controllers/Api/VotingTopicController.php)
  - GET /api/voting-topics (列表)
  - POST /api/voting-topics (建立)
  - GET /api/voting-topics/{id} (詳情)
  - PUT /api/voting-topics/{id} (更新)
  - DELETE /api/voting-topics/{id} (刪除)
  - PATCH /api/voting-topics/{id}/start-voting (開始投票)
  - PATCH /api/voting-topics/{id}/close-voting (關閉投票)
- [X] T064 [US8] 補充議題編號唯一性驗證 (VotingTopicController::validateTopicNumber)
- [X] T065 [US8] 補充投票方式驗證 (VotingTopicModel::validateVotingMethod)

### 前端實作

- [X] T066 [P] [US8] 驗證投票議題列表頁面已實作 (frontend/pages/tables/meeting/[meetingId]/voting-topics/index.vue)
- [X] T067 [P] [US8] 驗證投票議題基本資料頁面已實作 (frontend/pages/tables/meeting/[meetingId]/voting-topics/[topicId]/basic-info.vue)
- [X] T068 [US8] 建立 useVotingTopics composable (frontend/composables/useVotingTopics.js)
  - fetchList(), fetchById(), create(), update(), delete(), startVoting(), closeVoting()
- [X] T069 [US8] 建立投票議題表單元件 (frontend/components/VotingTopic/Form.vue)
- [X] T070 [US8] 建立投票方式選擇器元件 (frontend/components/VotingTopic/VotingMethodSelector.vue)
- [X] T071 [US8] 建立議題狀態控制元件 (frontend/components/VotingTopic/StatusControl.vue)
- [X] T072 [US8] 補充新增投票議題頁面 (frontend/pages/tables/meeting/[meetingId]/voting-topics/new/basic-info.vue)

**Checkpoint**: 可以完整管理投票議題，包括開啟和關閉投票

---

## Phase 7: US9 - 投票表決管理 (Priority: P1) 🎯 MVP 核心

**Goal**: 實現投票功能，包括單筆投票、批次投票、投票修改、投票記錄查詢

**Independent Test**: 以不同會員身份對議題投票，驗證投票記錄、權重計算、投票統計

### 後端實作

- [X] T073 [P] [US9] 驗證 VotingRecordModel 已實作 (backend/app/Models/VotingRecordModel.php)
- [X] T074 [P] [US9] 驗證 VotingController 包含所有投票端點 (backend/app/Controllers/Api/VotingController.php)
  - POST /api/voting/vote (單筆投票)
  - POST /api/voting/batch-vote (批次投票)
  - GET /api/voting/my-vote/{topicId} (我的投票)
  - DELETE /api/voting/remove-vote (移除投票)
  - GET /api/voting/statistics/{topicId} (投票統計)
  - GET /api/voting/detailed/{topicId} (詳細記錄)
  - GET /api/voting/export/{topicId} (匯出結果)
- [X] T075 [US9] 實作投票權重計算邏輯 (VotingController::calculateVoteWeight)
  - 基於所有權人的土地面積持分
  - 基於所有權人的建物面積持分
- [X] T076 [US9] 實作投票統計即時更新 (VotingController::updateStatistics)
- [X] T077 [US9] 補充投票資格驗證（已報到才能投票）(VotingController::checkVotingEligibility)
- [X] T078 [US9] 補充投票狀態檢查（議題必須為 active）(VotingController::validateTopicStatus)

### 前端實作

- [X] T079 [P] [US9] 驗證投票頁面已實作 (frontend/pages/tables/meeting/[meetingId]/voting-topics/[topicId]/voting.vue)
- [X] T080 [US9] 建立 useVoting composable (frontend/composables/useVoting.js)
  - vote(), batchVote(), getMyVote(), removeVote(), getStatistics()
- [X] T081 [US9] 建立投票按鈕元件 (frontend/components/Voting/VoteButtons.vue)
  - 同意、不同意、棄權按鈕
  - 顯示已投票狀態
- [X] T082 [US9] 建立批次投票元件 (frontend/components/Voting/BatchVote.vue)
- [X] T083 [US9] 建立投票統計顯示元件 (frontend/components/Voting/Statistics.vue)
  - 票數統計
  - 面積統計
  - 通過狀態
- [X] T084 [US9] 建立投票權重說明元件 (frontend/components/Voting/WeightExplanation.vue)

**Checkpoint**: 會員可以對議題投票，系統正確計算權重和統計結果

---

## Phase 8: US3 - 地籍資料管理 (Priority: P2)

**Goal**: 實現土地和建物資料的完整管理，包括階層式地區選擇

**Independent Test**: 在特定更新會下新增土地和建物資料，使用階層式下拉選單選擇縣市/鄉鎮市區/地段

### 後端實作

- [ ] T085 [P] [US3] 驗證 LandPlotModel 已實作 (backend/app/Models/LandPlotModel.php)
- [ ] T086 [P] [US3] 驗證 BuildingModel 已實作 (backend/app/Models/BuildingModel.php)
- [ ] T087 [P] [US3] 驗證 CountyModel, DistrictModel, SectionModel 已實作
- [ ] T088 [P] [US3] 驗證 LandPlotController 包含所有土地端點 (backend/app/Controllers/Api/LandPlotController.php)
  - GET /api/urban-renewals/{id}/land-plots
  - POST /api/urban-renewals/{id}/land-plots
  - GET /api/land-plots/{id}
  - PUT /api/land-plots/{id}
  - DELETE /api/land-plots/{id}
- [ ] T089 [P] [US3] 驗證 LocationController 包含地區階層端點 (backend/app/Controllers/Api/LocationController.php)
  - GET /api/locations/counties
  - GET /api/locations/districts/{countyCode}
  - GET /api/locations/sections/{countyCode}/{districtCode}
- [ ] T090 [US3] 補充建物資料端點（類似土地）(BuildingController.php - 如不存在則新建)
- [ ] T091 [US3] 補充地籍資料驗證規則

### 前端實作

- [ ] T092 [P] [US3] 建立土地資料管理頁面 (frontend/pages/tables/urban-renewal/[id]/land-plots/index.vue)
- [ ] T093 [P] [US3] 建立建物資料管理頁面 (frontend/pages/tables/urban-renewal/[id]/buildings/index.vue)
- [ ] T094 [US3] 建立 useLandPlots composable (frontend/composables/useLandPlots.js)
- [ ] T095 [US3] 建立 useBuildings composable (frontend/composables/useBuildings.js)
- [ ] T096 [US3] 建立 useLocations composable (frontend/composables/useLocations.js)
  - fetchCounties(), fetchDistricts(), fetchSections()
- [ ] T097 [US3] 建立階層式地區選擇器元件 (frontend/components/Location/CascadeSelector.vue)
  - 縣市 → 鄉鎮市區 → 地段
  - 級聯更新
- [ ] T098 [US3] 建立土地表單元件 (frontend/components/LandPlot/Form.vue)
- [ ] T099 [US3] 建立建物表單元件 (frontend/components/Building/Form.vue)

**Checkpoint**: 可以管理土地和建物資料，使用階層式選擇器選擇地區

---

## Phase 9: US4 - 所有權人管理 (Priority: P2)

**Goal**: 實現所有權人資料管理，包括批次匯入匯出功能

**Independent Test**: 新增所有權人、匯入 Excel 檔案、匯出所有權人清單

### 後端實作

- [ ] T100 [P] [US4] 驗證 PropertyOwnerModel 已實作 (backend/app/Models/PropertyOwnerModel.php)
- [ ] T101 [P] [US4] 驗證 PropertyOwnerController 包含所有端點 (backend/app/Controllers/Api/PropertyOwnerController.php)
  - GET /api/urban-renewals/{id}/property-owners
  - POST /api/urban-renewals/{id}/property-owners
  - GET /api/property-owners/{id}
  - PUT /api/property-owners/{id}
  - DELETE /api/property-owners/{id}
  - POST /api/urban-renewals/{id}/property-owners/import
  - GET /api/urban-renewals/{id}/property-owners/export
  - GET /api/property-owners/template
- [ ] T102 [US4] 補充所有權人編號自動產生邏輯 (PropertyOwnerModel::generateOwnerCode)
- [ ] T103 [US4] 實作 Excel 匯入功能（使用 PHPSpreadsheet）(PropertyOwnerController::import)
- [ ] T104 [US4] 實作 Excel 匯出功能（使用 PHPSpreadsheet）(PropertyOwnerController::export)
- [ ] T105 [US4] 建立匯入範本產生功能 (PropertyOwnerController::template)

### 前端實作

- [ ] T106 [P] [US4] 驗證所有權人列表頁面已實作 (frontend/pages/tables/urban-renewal/[id]/property-owners/index.vue)
- [ ] T107 [P] [US4] 驗證所有權人詳細頁面已實作 (frontend/pages/tables/urban-renewal/[id]/property-owners/[ownerId]/edit.vue)
- [ ] T108 [US4] 建立 usePropertyOwners composable (frontend/composables/usePropertyOwners.js)
  - fetchList(), create(), update(), delete(), import(), export(), downloadTemplate()
- [ ] T109 [US4] 建立所有權人表單元件 (frontend/components/PropertyOwner/Form.vue)
- [ ] T110 [US4] 建立 Excel 匯入元件 (frontend/components/PropertyOwner/ImportExcel.vue)
  - 檔案上傳
  - 匯入進度顯示
  - 錯誤報告
- [ ] T111 [US4] 建立所有權人卡片元件 (frontend/components/PropertyOwner/Card.vue)
- [ ] T112 [US4] 補充所有權人新建頁面 (frontend/pages/tables/urban-renewal/[id]/property-owners/create.vue)

**Checkpoint**: 可以管理所有權人資料，支援批次匯入匯出

---

## Phase 10: US7 - 會員報到管理 (Priority: P2)

**Goal**: 實現會員報到功能，包括單筆報到、批次報到、即時顯示

**Independent Test**: 為特定會議建立報到記錄，檢視報到統計，使用大螢幕顯示頁面

### 後端實作

- [ ] T113 [P] [US7] 驗證 MeetingAttendanceModel 已實作（或使用 meeting_attendances 資料表）
- [ ] T114 [P] [US7] 驗證 MeetingAttendanceController 包含所有報到端點 (backend/app/Controllers/Api/MeetingAttendanceController.php)
  - POST /api/meeting-attendance/check-in
  - POST /api/meeting-attendance/batch-check-in
  - PATCH /api/meeting-attendance/{id}/update-status
  - GET /api/meeting-attendance/{meetingId}/summary
  - GET /api/meeting-attendance/{meetingId}/statistics
  - GET /api/meeting-attendance/{meetingId}/export
- [ ] T115 [US7] 補充報到統計計算邏輯 (MeetingAttendanceController::calculateStatistics)
  - 出席人數
  - 納入計算總人數
  - 列席總人數
- [ ] T116 [US7] 補充重複報到檢查 (MeetingAttendanceController::checkDuplicate)

### 前端實作

- [ ] T117 [P] [US7] 驗證會員報到頁面已實作 (frontend/pages/tables/meeting/[meetingId]/member-checkin.vue)
- [ ] T118 [P] [US7] 驗證報到顯示頁面已實作（大螢幕）(frontend/pages/tables/meeting/[meetingId]/checkin-display.vue)
- [ ] T119 [US7] 建立 useAttendance composable (frontend/composables/useAttendance.js)
  - checkIn(), batchCheckIn(), updateStatus(), getSummary(), getStatistics()
- [ ] T120 [US7] 建立報到表單元件 (frontend/components/Attendance/CheckInForm.vue)
  - 出席類型選擇
  - 代理人輸入
- [ ] T121 [US7] 建立批次報到元件 (frontend/components/Attendance/BatchCheckIn.vue)
- [ ] T122 [US7] 建立報到統計元件 (frontend/components/Attendance/Statistics.vue)
- [ ] T123 [US7] 建立即時報到顯示元件（大螢幕）(frontend/components/Attendance/LiveDisplay.vue)
  - 使用 SSE 或 WebSocket 實作即時更新

**Checkpoint**: 可以進行會員報到，查看統計，大螢幕即時顯示

---

## Phase 11: US10 - 投票結果統計與報表 (Priority: P2)

**Goal**: 實現投票結果的詳細統計和報表匯出功能

**Independent Test**: 檢視特定議題的投票統計，匯出報表（Excel 或 PDF）

### 後端實作

- [ ] T124 [US10] 補充投票結果判定邏輯 (VotingController::determineResult)
  - 簡單多數: 同意票數 > 不同意票數
  - 絕對多數: 同意票數 > 應出席數 ÷ 2
  - 三分之二多數: 同意票數 ≥ 應出席數 × 2/3
  - 全體一致: 同意票數 = 應出席數
- [ ] T125 [US10] 實作投票結果匯出功能（Excel）(VotingController::exportExcel)
- [ ] T126 [US10] 實作投票結果匯出功能（PDF）(VotingController::exportPDF)
- [ ] T127 [US10] 補充詳細投票記錄查詢（包含個別投票資料）(VotingController::detailed)

### 前端實作

- [ ] T128 [P] [US10] 驗證投票結果頁面已實作 (frontend/pages/tables/meeting/[meetingId]/voting-topics/[topicId]/results.vue)
- [ ] T129 [US10] 建立投票結果統計圖表元件 (frontend/components/Voting/ResultChart.vue)
  - 圓餅圖顯示票數分佈
  - 長條圖顯示面積分佈
- [ ] T130 [US10] 建立投票結果表格元件 (frontend/components/Voting/ResultTable.vue)
  - 顯示個別投票記錄
  - 支援排序和篩選
- [ ] T131 [US10] 建立報表匯出按鈕元件 (frontend/components/Voting/ExportButtons.vue)
  - Excel 匯出
  - PDF 匯出
- [ ] T132 [US10] 建立通過狀態顯示元件 (frontend/components/Voting/PassStatus.vue)
  - 根據投票方式顯示是否通過

**Checkpoint**: 可以查看完整投票統計和匯出報表

---

## Phase 12: US14 - 使用者管理 (Priority: P2)

**Goal**: 實現系統使用者帳號管理，包括角色權限設定

**Independent Test**: 建立新使用者、修改角色、停用帳號、重置登入嘗試次數

### 後端實作

- [ ] T133 [P] [US14] 驗證 UserController 包含所有使用者管理端點 (backend/app/Controllers/Api/UserController.php)
  - GET /api/users
  - GET /api/users/{id}
  - POST /api/users
  - PUT /api/users/{id}
  - DELETE /api/users/{id}
  - PATCH /api/users/{id}/toggle-status
  - PATCH /api/users/{id}/reset-login-attempts
- [ ] T134 [US14] 補充使用者角色統計 (UserController::getRoleStatistics)
- [ ] T135 [US14] 補充密碼變更功能（使用者自己修改）(UserController::changePassword)

### 前端實作

- [ ] T136 [P] [US14] 建立使用者管理頁面 (frontend/pages/admin/users/index.vue)
- [ ] T137 [P] [US14] 驗證使用者資料變更頁面已實作 (frontend/pages/pages/user.vue)
- [ ] T138 [US14] 建立 useUsers composable (frontend/composables/useUsers.js)
  - fetchList(), create(), update(), delete(), toggleStatus(), resetLoginAttempts()
- [ ] T139 [US14] 建立使用者表單元件 (frontend/components/User/Form.vue)
- [ ] T140 [US14] 建立角色選擇器元件 (frontend/components/User/RoleSelector.vue)
- [ ] T141 [US14] 建立使用者狀態控制元件 (frontend/components/User/StatusControl.vue)

**Checkpoint**: 可以完整管理系統使用者和權限

---

## Phase 13: US5 - 所有權關係管理 (Priority: P3)

**Goal**: 實現所有權人與地籍的持分關係管理

**Independent Test**: 為所有權人建立土地和建物的持分關係，驗證持分比例計算

### 後端實作

- [ ] T142 [P] [US5] 驗證 OwnerLandOwnershipModel 已實作 (backend/app/Models/OwnerLandOwnershipModel.php)
- [ ] T143 [P] [US5] 驗證 OwnerBuildingOwnershipModel 已實作 (backend/app/Models/OwnerBuildingOwnershipModel.php)
- [ ] T144 [US5] 建立所有權關係管理端點 (OwnershipController.php - 新建)
  - POST /api/ownerships/land (建立土地持分)
  - POST /api/ownerships/building (建立建物持分)
  - PUT /api/ownerships/land/{id} (更新土地持分)
  - PUT /api/ownerships/building/{id} (更新建物持分)
  - DELETE /api/ownerships/land/{id} (刪除土地持分)
  - DELETE /api/ownerships/building/{id} (刪除建物持分)
- [ ] T145 [US5] 補充持分比例驗證（分子 ≤ 分母）

### 前端實作

- [ ] T146 [US5] 建立所有權關係管理頁面 (frontend/pages/tables/urban-renewal/[id]/ownerships/index.vue)
- [ ] T147 [US5] 建立 useOwnerships composable (frontend/composables/useOwnerships.js)
- [ ] T148 [US5] 建立持分關係表單元件 (frontend/components/Ownership/Form.vue)
  - 選擇所有權人
  - 選擇土地/建物
  - 輸入持分比例（分子/分母）
- [ ] T149 [US5] 建立持分清單顯示元件 (frontend/components/Ownership/List.vue)

**Checkpoint**: 可以管理所有權關係和持分比例

---

## Phase 14: US11 - 系統設定管理 (Priority: P3)

**Goal**: 實現系統全域設定管理功能

**Independent Test**: 修改系統名稱、標誌、預設值，清除快取

### 後端實作

- [ ] T150 [P] [US11] 驗證 SystemSettingModel 已實作 (backend/app/Models/SystemSettingModel.php)
- [ ] T151 [P] [US11] 驗證 SystemSettingsController 包含所有設定端點 (backend/app/Controllers/Api/SystemSettingsController.php)
  - GET /api/system-settings
  - GET /api/system-settings/public
  - GET /api/system-settings/category/{category}
  - GET /api/system-settings/get/{key}
  - POST /api/system-settings/set
  - POST /api/system-settings/batch-set
  - PATCH /api/system-settings/reset/{key}
  - DELETE /api/system-settings/clear-cache
- [ ] T152 [US11] 補充系統資訊查詢功能（版本、資料庫狀態、儲存空間）(SystemSettingsController::getSystemInfo)

### 前端實作

- [ ] T153 [US11] 建立系統設定頁面 (frontend/pages/admin/settings/index.vue)
- [ ] T154 [US11] 建立 useSystemSettings composable (frontend/composables/useSystemSettings.js)
- [ ] T155 [US11] 建立設定表單元件 (frontend/components/SystemSettings/Form.vue)
- [ ] T156 [US11] 建立系統資訊顯示元件 (frontend/components/SystemSettings/SystemInfo.vue)

**Checkpoint**: 可以管理系統設定和查看系統資訊

---

## Phase 15: US12 - 通知系統 (Priority: P3)

**Goal**: 實現系統通知功能（站內通知）

**Independent Test**: 建立通知、標記為已讀、查看未讀通知數量

### 後端實作

- [ ] T157 [P] [US12] 驗證 NotificationModel 已實作 (backend/app/Models/NotificationModel.php)
- [ ] T158 [P] [US12] 驗證 NotificationController 包含所有通知端點 (backend/app/Controllers/Api/NotificationController.php)
  - GET /api/notifications
  - GET /api/notifications/{id}
  - POST /api/notifications
  - PATCH /api/notifications/{id}/mark-read
  - PATCH /api/notifications/mark-all-read
  - DELETE /api/notifications/{id}
  - GET /api/notifications/unread-count
  - GET /api/notifications/types
- [ ] T159 [US12] 實作自動通知觸發邏輯
  - 會議建立時發送通知
  - 投票開始時發送通知
  - 投票結束時發送通知

### 前端實作

- [ ] T160 [US12] 建立通知列表頁面 (frontend/pages/notifications/index.vue)
- [ ] T161 [US12] 建立 useNotifications composable (frontend/composables/useNotifications.js)
- [ ] T162 [US12] 建立通知鈴鐺元件（顯示未讀數量）(frontend/components/Notification/Bell.vue)
- [ ] T163 [US12] 建立通知列表元件 (frontend/components/Notification/List.vue)
- [ ] T164 [US12] 建立通知卡片元件 (frontend/components/Notification/Card.vue)

**Checkpoint**: 可以查看和管理通知

---

## Phase 16: US13 - 文件管理 (Priority: P3)

**Goal**: 實現會議文件的上傳、下載、刪除功能

**Independent Test**: 上傳文件、下載文件、刪除文件、查看文件統計

### 後端實作

- [ ] T165 [P] [US13] 驗證 MeetingDocumentModel 已實作 (backend/app/Models/MeetingDocumentModel.php)
- [ ] T166 [P] [US13] 驗證 DocumentController 包含所有文件端點 (backend/app/Controllers/Api/DocumentController.php)
  - GET /api/documents
  - GET /api/documents/{id}
  - POST /api/documents/upload
  - POST /api/documents/batch-upload
  - GET /api/documents/download/{id}
  - PUT /api/documents/{id}
  - DELETE /api/documents/{id}
  - GET /api/documents/statistics
- [ ] T167 [US13] 補充檔案上傳驗證（大小、類型）(DocumentController::validateUpload)
- [ ] T168 [US13] 補充檔案統計功能（總文件數、檔案類型分佈、儲存空間）(DocumentController::statistics)

### 前端實作

- [ ] T169 [US13] 建立文件管理頁面 (frontend/pages/tables/meeting/[meetingId]/documents/index.vue)
- [ ] T170 [US13] 建立 useDocuments composable (frontend/composables/useDocuments.js)
- [ ] T171 [US13] 建立檔案上傳元件 (frontend/components/Document/Upload.vue)
- [ ] T172 [US13] 建立文件列表元件 (frontend/components/Document/List.vue)
- [ ] T173 [US13] 建立文件統計元件 (frontend/components/Document/Statistics.vue)

**Checkpoint**: 可以管理會議文件

---

## Phase 17: Polish & Cross-Cutting Concerns

**目的**: 優化和完善系統，確保所有功能整合良好

### 文件和指南

- [ ] T174 [P] 更新 README.md（中文）包含專案說明、安裝步驟、啟動指令
- [ ] T175 [P] 建立 API 文件（基於 OpenAPI 規範）(docs/API.md)
- [ ] T176 [P] 建立 CHANGELOG.md 記錄版本變更
- [ ] T177 [P] 驗證 quickstart.md 正確性（如有）

### 程式碼品質

- [ ] T178 [P] 執行 ESLint 檢查並修正前端程式碼
- [ ] T179 [P] 執行 PHP CodeSniffer 檢查並修正後端程式碼
- [ ] T180 程式碼重構和優化（移除重複程式碼）
- [ ] T181 補充程式碼註解（正體中文）

### 效能優化

- [ ] T182 [P] 檢查資料庫查詢效能，補充必要索引
- [ ] T183 [P] 前端 Bundle 大小優化
- [ ] T184 實作 API 回應快取機制（使用 Redis）
- [ ] T185 前端路由懶載入優化

### 安全性強化

- [ ] T186 [P] 檢查 CORS 設定是否正確
- [ ] T187 [P] 檢查 SQL 注入防護
- [ ] T188 [P] 檢查 XSS 防護
- [ ] T189 [P] 檢查 CSRF 防護
- [ ] T190 檢查敏感資料加密

### 錯誤處理

- [ ] T191 [P] 統一前端錯誤訊息顯示（使用 Toast 或 Modal）
- [ ] T192 [P] 統一後端錯誤回應格式
- [ ] T193 補充友善的錯誤頁面（404、500 等）

### 使用者體驗

- [ ] T194 [P] 檢查所有頁面的響應式設計
- [ ] T195 [P] 補充載入狀態指示器
- [ ] T196 [P] 補充表單驗證提示
- [ ] T197 補充確認對話框（刪除操作等）
- [ ] T198 補充麵包屑導覽

### 測試和驗證

- [ ] T199 執行完整功能測試（所有 User Stories）
- [ ] T200 瀏覽器相容性測試（Chrome、Firefox、Safari、Edge）
- [ ] T201 效能測試（100 位使用者同時在線）
- [ ] T202 安全性測試

**Final Checkpoint**: 系統完整、穩定、安全、效能良好

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: 無相依性 - 可立即開始
- **Phase 2 (Foundational)**: 依賴 Phase 1 完成 - **阻塞所有 User Stories**
- **Phase 3-7 (P1 User Stories)**: 依賴 Phase 2 完成，彼此可並行
  - US1 (身份驗證) - MVP 核心
  - US2 (都市更新會管理) - MVP 核心
  - US6 (會議管理) - MVP 核心
  - US8 (投票議題管理) - MVP 核心
  - US9 (投票表決管理) - MVP 核心
- **Phase 8-12 (P2 User Stories)**: 依賴 Phase 2 完成，建議在 P1 完成後進行
  - US3 (地籍資料管理)
  - US4 (所有權人管理)
  - US7 (會員報到管理)
  - US10 (投票結果統計)
  - US14 (使用者管理)
- **Phase 13-16 (P3 User Stories)**: 依賴 Phase 2 完成，優先級最低
  - US5 (所有權關係管理)
  - US11 (系統設定管理)
  - US12 (通知系統)
  - US13 (文件管理)
- **Phase 17 (Polish)**: 依賴所有希望交付的 User Stories 完成

### User Story Dependencies

- **US1 (身份驗證)**: 其他所有 User Stories 的前置條件
- **US2 (都市更新會)**: US3, US4, US6 的前置條件
- **US4 (所有權人)**: US5, US7, US9 的前置條件
- **US6 (會議)**: US7, US8 的前置條件
- **US8 (投票議題)**: US9, US10 的前置條件

### Parallel Opportunities

- Phase 2 中標記 [P] 的任務可以並行執行
- Phase 3-7 的 P1 User Stories 完成 Phase 2 後可並行開發（如團隊人力允許）
- 每個 Phase 內標記 [P] 的任務可以並行執行
- 前端和後端可以並行開發（基於 API 合約）

---

## Implementation Strategy

### MVP First (最小可行產品)

**目標**: 快速交付核心投票功能

1. 完成 Phase 1: Setup
2. 完成 Phase 2: Foundational（關鍵！）
3. 完成 Phase 3: US1 (身份驗證)
4. 完成 Phase 4: US2 (都市更新會管理)
5. 完成 Phase 5: US6 (會議管理)
6. 完成 Phase 6: US8 (投票議題管理)
7. 完成 Phase 7: US9 (投票表決管理)
8. **驗證**: 測試完整投票流程
9. **部署/展示**: MVP 可交付

### Incremental Delivery (漸進式交付)

1. **Foundation Ready**: Setup + Foundational → 基礎環境就緒
2. **MVP Release**: 加入 P1 User Stories → 核心投票功能可用
3. **Feature Enhancement**: 加入 P2 User Stories → 完善資料管理
4. **Full Feature**: 加入 P3 User Stories → 完整功能系統
5. **Production Ready**: Polish → 可正式上線

### Parallel Team Strategy (並行團隊策略)

如有多位開發者：

1. **共同完成**: Phase 1 (Setup) + Phase 2 (Foundational)
2. **並行開發** (Phase 2 完成後):
   - Developer A: US1 (身份驗證) + US2 (都市更新會)
   - Developer B: US6 (會議管理) + US8 (投票議題)
   - Developer C: US9 (投票表決) + US10 (投票統計)
3. **整合測試**: 確保各 User Story 獨立運作
4. **繼續擴展**: 依優先級加入其他 User Stories

---

## Notes

### 重要提醒

- **現有專案**: 許多功能已實作，任務重點是「驗證和補充」而非「從零開始」
- **[P] 標記**: 不同檔案，無相依性，可並行執行
- **[Story] 標記**: 追溯到特定 User Story，便於管理
- **獨立性**: 每個 User Story 應該可以獨立完成和測試
- **檢查點**: 在每個 Phase 的 Checkpoint 停下來驗證功能
- **提交頻率**: 每完成一個任務或一組相關任務就提交

### 技術規範遵循

- **正體中文**: 所有註解、文件、UI 文字使用正體中文
- **JavaScript ES6+**: 不使用 TypeScript
- **Nuxt UI**: 禁止使用 Vuetify
- **Heroicons**: 圖示系統
- **綠色漸層**: 主色調 (#2FA633 到 #72BB29)
- **CodeIgniter 4**: 後端框架，遵循 MVC 架構
- **RESTful API**: 統一使用 `/api` 前綴

### 避免事項

- 模糊的任務描述
- 相同檔案的衝突（同時修改）
- 破壞 User Story 獨立性的跨 Story 相依
- 未驗證就繼續下一階段
- 忽略軟刪除機制
- 忽略資料驗證和錯誤處理

---

**任務清單建立完成**
**總任務數**: 202 個任務
**User Stories 涵蓋**: 14 個完整 User Stories
**優先級分佈**: P1 (5個) → P2 (5個) → P3 (4個)
