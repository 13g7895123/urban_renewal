## 已完成任務

1. ✅ /tables/urban-renewal/4/property-owners/2/edit幫我加入一顆填入測試資料的按鈕，下方的新增地號與新增建號也要，新增地號的下拉沒有正確顯示text
2. ✅ 幫我建立production的docker-compose與.env，並寫三支sh讓我可以啟用production，包含啟用、停用、建立+啟用
3. ✅ 幫我確認一下，為甚麼建置的時候沒有執行migration，並且縣市、行政區、段小段也沒有資料，請確認是否需要執行seed或是相關功能
4. ✅ 確認一下，/tables/urban-renewal/1/property-owners/create這個頁面的顯示、行政區、段小段的資料是從哪裡來的，請用zh-tw回答
5. ✅ /tables/urban-renewal/4/property-owners/2/edit這一頁的「新增建號」模態框的縣市、行政區、段小段下拉選單，請幫我改為從資料庫為資料源
6. ✅ 登入認證功能規格撰寫（spec.md, test-checklist.md, README.md, SUMMARY.md）
7. ✅ 登入認證功能實作計畫（plan.md, tasks.md, milestones.md, implementation-guide.md）
8. ✅ 安全性分析報告（analyze-02.md）

---

## 🔴 URGENT: 待辦任務（優先級 P0）

### Phase 3.5: 安全性修正（2025-10-24 ~ 10-26）

**背景**：根據 analyze-02.md 安全性分析報告，發現 6 個安全性問題需要立即處理。

9. 🔴 **TASK-099**: 實作 CSRF 保護機制 (P0)
   - 後端：建立 CsrfFilter.php
   - 後端：在 AuthController 產生 CSRF Token
   - 後端：驗證所有 POST/PUT/DELETE 請求的 CSRF Token
   - 前端：useApi.js 加入 X-CSRF-Token header
   - 前端：auth store 儲存和管理 CSRF Token
   - 測試：驗證 CSRF 防護機制
   - 文件：更新 API 文件說明 CSRF 用法
   - **工時**: 2 days
   - **負責**: Backend Team + Frontend Team

10. 🔴 **TASK-100**: 修正 CORS 設定 (P0)
    - 建立 backend/app/Config/Cors.php
    - 從 .env 讀取 ALLOWED_ORIGINS
    - 移除 AuthController.php 中的 CORS header (line 24-26)
    - 建立 CorsFilter middleware
    - 更新 .env.example 加入 ALLOWED_ORIGINS 說明
    - **工時**: 0.5 day
    - **負責**: Backend Team

11. 🔴 **TASK-101**: JWT Secret 強制檢查 (P0)
    - 移除 AuthController.php 的硬編碼 fallback (line 417, 479)
    - JWT_SECRET 為空時拋出異常
    - 更新 .env.example 加入警告說明
    - 建立啟動檢查腳本
    - **工時**: 0.5 day
    - **負責**: Backend Team

### Phase 3.5: 安全性增強（P1，可與測試並行）

12. ⚠️ **TASK-102**: Token 改用 httpOnly Cookie (P1)
    - 後端改用 setcookie() 回傳 Token
    - 設定 httpOnly, secure, samesite flags
    - 前端移除 localStorage 的 Token 讀寫
    - 更新 API 攔截器
    - 更新所有文件說明
    - **工時**: 3 days
    - **負責**: Full-stack Team
    - **備註**: 可選實作

13. ⚠️ **TASK-103**: 強制密碼強度驗證 (P1)
    - 在 UserModel validation rules 加入 passwordStrength
    - 註冊自訂驗證規則
    - 更新密碼重設 API 驗證
    - 更新使用者建立 API 驗證
    - **工時**: 1 day
    - **負責**: Backend Team

14. ⚠️ **TASK-104**: Session 自動清理機制 (P1)
    - 建立 CleanupExpiredSessions Command
    - 設定 Cron Job (`0 2 * * *`)
    - 加入清理日誌
    - 更新部署文件
    - **工時**: 1 day
    - **負責**: DevOps Team

---

## 📚 參考文件

- [analyze-02.md](../analyze-02.md) - 安全性分析報告
- [SECURITY_FIXES_UPDATE.md](../specs/002-login-authentication/SECURITY_FIXES_UPDATE.md) - 安全性修正更新摘要
- [spec.md](../specs/002-login-authentication/spec.md) - 功能規格書
- [plan.md](../specs/002-login-authentication/plan.md) - 實作計畫
- [tasks.md](../specs/002-login-authentication/tasks.md) - 詳細任務清單
- [milestones.md](../specs/002-login-authentication/milestones.md) - 里程碑追蹤
- [tasks-dashboard.md](../specs/002-login-authentication/tasks-dashboard.md) - 任務儀表板
- [TASKS_SUMMARY.md](../TASKS_SUMMARY.md) - 任務總覽