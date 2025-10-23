# 安全性修正更新摘要

**Update Date**: 2025-10-23
**Triggered By**: analyze-02.md 分析報告
**Updated Documents**: spec.md, plan.md, tasks.md
**New Tasks Added**: 6 tasks (TASK-099 to TASK-104)

---

## 📋 更新概述

根據 analyze-02.md 分析報告的發現，我們識別出 6 個需要立即處理的安全性問題，並已將這些問題加入到專案規劃文件中。

---

## 🔴 Critical 問題 (P0) - 3 項

### 1. CSRF 保護缺失 - **TASK-099**
**問題**：無 CSRF Token 機制，CORS 設定為 `*`
**風險**：任何網站都可以呼叫 API，存在 CSRF 攻擊風險
**工時**：2 天
**負責**：Backend Team + Frontend Team

**實作項目**：
- [ ] 建立 CSRF middleware (backend/app/Filters/CsrfFilter.php)
- [ ] 在 AuthController 產生 CSRF Token
- [ ] 在 login 回應中回傳 CSRF Token
- [ ] 前端 useApi.js 加入 X-CSRF-Token header
- [ ] 前端 auth store 儲存 CSRF Token
- [ ] 測試 CSRF 防護機制
- [ ] 更新 API 文件說明 CSRF 用法

**影響檔案**：
- `backend/app/Filters/CsrfFilter.php` (新增)
- `backend/app/Controllers/Api/AuthController.php` (修改)
- `frontend/composables/useApi.js` (修改)
- `frontend/stores/auth.js` (修改)

---

### 2. CORS 設定錯誤 - **TASK-100**
**問題**：CORS 設定為 `Access-Control-Allow-Origin: *`
**風險**：允許任何來源存取 API
**工時**：0.5 天
**負責**：Backend Team

**實作項目**：
- [ ] 建立 Config/Cors.php 設定檔
- [ ] 從 .env 讀取 ALLOWED_ORIGINS
- [ ] 移除 AuthController 的 CORS header
- [ ] 在 middleware 中集中處理 CORS
- [ ] 更新 .env.example 加入 ALLOWED_ORIGINS

**影響檔案**：
- `backend/app/Config/Cors.php` (新增)
- `backend/app/Controllers/Api/AuthController.php` (移除 line 24-26)
- `backend/.env.example` (新增 ALLOWED_ORIGINS)
- `backend/app/Filters/CorsFilter.php` (新增)

---

### 3. JWT Secret 硬編碼 - **TASK-101**
**問題**：存在 fallback 值 `urban_renewal_secret_key_2025`
**風險**：若 .env 未設定，使用已知 secret，Token 可被偽造
**工時**：0.5 天
**負責**：Backend Team

**實作項目**：
- [ ] 移除 fallback `urban_renewal_secret_key_2025`
- [ ] JWT_SECRET 為空時拋出異常
- [ ] 更新 .env.example 加入警告說明
- [ ] 在應用程式啟動時檢查 JWT_SECRET
- [ ] 撰寫啟動檢查腳本

**影響檔案**：
- `backend/app/Controllers/Api/AuthController.php` (line 417, 479)
- `backend/.env.example` (加入說明)
- `backend/app/Config/Boot/production.php` (加入檢查)

---

## 🟡 High 問題 (P1) - 3 項

### 4. Token 存在 localStorage - **TASK-102**
**問題**：Token 儲存在 localStorage 有 XSS 風險
**建議**：改用 httpOnly cookie 或加密 Token
**工時**：3 天
**負責**：Full-stack Team
**優先級**：可選實作，與 TASK-099 (CSRF) 擇一優先

**實作項目**：
- [ ] 後端改用 setcookie() 回傳 Token
- [ ] 設定 httpOnly, secure, samesite flags
- [ ] 前端移除 localStorage 的 Token 讀寫
- [ ] 前端改為自動從 Cookie 讀取
- [ ] 更新 API 攔截器
- [ ] 更新所有文件說明

---

### 5. 密碼強度未強制 - **TASK-103**
**問題**：validatePasswordStrength() 方法存在但未使用
**風險**：使用者可設定弱密碼
**工時**：1 天
**負責**：Backend Team

**實作項目**：
- [ ] 在 UserModel validation rules 加入 passwordStrength
- [ ] 註冊自訂驗證規則
- [ ] 更新密碼重設 API 驗證
- [ ] 更新使用者建立 API 驗證
- [ ] 測試所有密碼設定端點

---

### 6. Session 無清理機制 - **TASK-104**
**問題**：過期 session 會持續累積
**影響**：資料庫表會越來越大
**工時**：1 天
**負責**：DevOps Team

**實作項目**：
- [ ] 建立 CleanupExpiredSessions Command
- [ ] 實作清理邏輯（刪除 30 天前過期的 session）
- [ ] 加入清理日誌
- [ ] 設定 Cron Job 或 Scheduled Task (`0 2 * * *`)
- [ ] 更新部署文件說明排程設定

---

## 📊 更新統計

### 任務數量變化
- **原有任務**：85 tasks
- **新增任務**：6 tasks
- **更新後總計**：91 tasks

### 完成度變化
- **原有完成度**：59/85 (69%)
- **更新後完成度**：59/91 (65%)

### 優先級分佈變化
- **P0 (Critical)**：68 → 71 tasks (+3)
- **P1 (High)**：22 → 25 tasks (+3)
- **P2 (Medium)**：3 tasks (unchanged)
- **P3 (Low)**：5 tasks (unchanged)

### 新增階段
- **Phase 3.5**: 安全性修正 (0/6 tasks, 0%)

---

## 📅 時程影響

### 原時程
- Phase 4 (測試與 QA): 2025-10-24 ~ 2025-11-05

### 更新後時程
- **Phase 3.5 (安全性修正)**: 2025-10-24 ~ 2025-10-26 (3 days)
- **Phase 4 (測試與 QA)**: 2025-10-27 ~ 2025-11-08 (延後 3 天)

### 里程碑影響
- M3 (測試完成): 2025-11-05 → 2025-11-08 (延後 3 天)
- M4 (上線準備): 可能需要相應調整

---

## 🎯 立即行動項目

### 本週必做 (2025-10-24 ~ 2025-10-26)
1. **TASK-099**: 實作 CSRF 保護 (P0) 🔴
2. **TASK-100**: 修正 CORS 設定 (P0) 🔴
3. **TASK-101**: JWT Secret 強制檢查 (P0) 🔴

### 下週執行 (2025-10-27 ~ 2025-11-01)
4. **TASK-103**: 強制密碼強度驗證 (P1) ⚠️
5. **TASK-104**: Session 自動清理機制 (P1) ⚠️
6. **TASK-102**: Token 改用 httpOnly Cookie (P1) ⚠️ (可選)

---

## ✅ 驗收標準

### Phase 3.5 完成標準
- ✅ CSRF 攻擊防護測試通過
- ✅ CORS 只允許指定來源
- ✅ JWT_SECRET 未設定時系統拒絕啟動
- ✅ 所有弱密碼嘗試被拒絕
- ✅ 過期 session 自動清理

### 安全性測試通過標準
- ✅ CSRF 測試：嘗試無 token 請求被拒絕
- ✅ CORS 測試：非允許來源請求被拒絕
- ✅ JWT 測試：無法使用已知 secret 偽造 token
- ✅ 密碼測試：弱密碼（如 "123456"）被拒絕
- ✅ Session 測試：過期 session 自動清理

---

## 📄 更新的文件清單

### 1. spec.md
**更新內容**：
- 更新「安全機制」表格，標註需修正的項目
- 新增「待改進項目」章節，詳細說明 6 個安全性問題
- 按優先級分類（P0, P1, P2, P3）

**修改位置**：
- Line 433-448: 安全機制表格
- Line 493-542: 待改進項目（擴展）

### 2. plan.md
**更新內容**：
- 新增 Phase 3.5: 安全性修正階段（2025-10-24 ~ 2025-10-26）
- 詳細列出 6 個修正任務的實作計畫
- 更新 Phase 4 開始時間（延後 3 天）

**修改位置**：
- Line 181-277: 新增 Phase 3.5
- Line 279-282: 更新 Phase 4 時程

### 3. tasks.md
**更新內容**：
- 更新標題統計（91 tasks, 59 completed, 32 pending）
- 新增 Phase 3.5 章節，包含 6 個新任務（TASK-099 到 TASK-104）
- 更新 Summary Statistics
- 更新 Critical Path，將安全性修正任務列為最優先

**修改位置**：
- Line 4-8: 更新統計數字
- Line 351-496: 新增 Phase 3.5 任務
- Line 728-757: 更新統計區塊
- Line 761-773: 更新 Critical Path

---

## 🔍 文件一致性驗證

### ✅ 任務數量一致
- spec.md: 列出 11 個待改進項目（包含 6 個安全性修正）
- plan.md: Phase 3.5 包含 6 個安全性修正任務
- tasks.md: 新增 6 個任務（TASK-099 到 TASK-104）

### ✅ 優先級一致
所有文件中的優先級標示一致：
- P0: TASK-099, TASK-100, TASK-101
- P1: TASK-102, TASK-103, TASK-104

### ✅ 工時估算一致
- P0 tasks: 3 days total (2 + 0.5 + 0.5)
- P1 tasks: 5 days total (3 + 1 + 1)
- Total: 8 days (可並行執行部分任務)

### ✅ 時程一致
- Phase 3.5: 2025-10-24 ~ 2025-10-26 (所有文件一致)
- Phase 4 延後: 2025-10-27 開始 (所有文件一致)

---

## 📞 後續行動

### 團隊溝通
1. [ ] 召開緊急會議，說明安全性修正的必要性
2. [ ] 分配 TASK-099 到 TASK-104 給相關團隊
3. [ ] 確認資源是否足夠（Backend, Frontend, DevOps）
4. [ ] 更新專案管理系統（Jira/Trello）

### 技術準備
1. [ ] 審查 analyze-02.md 報告的詳細建議
2. [ ] 準備開發環境進行安全性測試
3. [ ] 準備 CSRF 和 CORS 測試案例
4. [ ] 設定 code review 流程

### 文件追蹤
1. [ ] 定期更新 tasks.md 的完成狀態
2. [ ] 每日更新 tasks-dashboard.md
3. [ ] Phase 3.5 完成後更新 milestones.md
4. [ ] 在 TASKS_SUMMARY.md 中反映最新進度

---

## 🎯 成功指標

### 短期目標 (本週)
- ✅ 完成 3 個 P0 安全性修正
- ✅ 通過基本安全性測試
- ✅ 所有修正有測試覆蓋

### 中期目標 (下週)
- ✅ 完成 3 個 P1 安全性增強
- ✅ 通過完整安全性掃描
- ✅ 開始 Phase 4 測試工作

### 長期目標
- ✅ 達到 80%+ 測試覆蓋率
- ✅ 通過第三方安全審計
- ✅ 如期完成 M3 里程碑（延後 3 天可接受）

---

**Document Status**: ✅ Complete
**Last Updated**: 2025-10-23
**Next Review**: 2025-10-27 (Phase 3.5 完成後)

---

## 📚 相關文件

- [analyze-02.md](../../analyze-02.md) - 原始分析報告
- [spec.md](./spec.md) - 功能規格書（已更新）
- [plan.md](./plan.md) - 實作計畫（已更新）
- [tasks.md](./tasks.md) - 任務清單（已更新）
- [tasks-dashboard.md](./tasks-dashboard.md) - 需要更新
- [milestones.md](./milestones.md) - 需要更新
- [TASKS_SUMMARY.md](../../TASKS_SUMMARY.md) - 需要更新
