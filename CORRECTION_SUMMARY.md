# 系統改善修正報告

> 修正日期：2026年1月24日
> 根據：PROJECT_ANALYSIS_REPORT.md

## 修正範圍

根據報告中的建議，本次修正涵蓋以下問題（**不包含問題4：會議合格投票人快照**）：

---

## ✅ 已完成修正

### 1. 問題3：投票權限檢查邏輯混亂 🔴 P0

**修正文件：** [backend/app/Controllers/Api/VotingController.php](backend/app/Controllers/Api/VotingController.php)

**改善內容：**
- 重構投票權限檢查邏輯，使其更清晰
- 明確處理 `property_owner_id` 可能不存在的情況
- 為一般會員增加明確的錯誤訊息
- 統一 admin、chairman、企業管理者的權限檢查流程

**修正前問題：**
```php
// property_owner_id 欄位可能不存在
if (($user['property_owner_id'] ?? null) !== $data['property_owner_id']) {
    return $this->failForbidden('只能為自己投票');
}
```

**修正後：**
```php
// 明確檢查並提供友善錯誤訊息
$userPropertyOwnerId = $user['property_owner_id'] ?? null;

if ($userPropertyOwnerId === null) {
    return $this->failForbidden('您尚未綁定所有權人資料，無法投票');
}

if ($userPropertyOwnerId !== $data['property_owner_id']) {
    return $this->failForbidden('一般會員只能為自己投票');
}
```

---

### 2. 問題2：清理企業與更新會過渡期代碼 🔴 P0

**修正文件：** [backend/app/Services/AuthorizationService.php](backend/app/Services/AuthorizationService.php)

**改善內容：**
- 移除從 `urban_renewal_id` 推導 `company_id` 的過渡期代碼
- 簡化 `getUserCompanyId()` 方法
- 統一使用新架構（一對多關係）

**修正前：**
```php
// 過渡期兼容：從 urban_renewal_id 推導 company_id
if (!empty($user['urban_renewal_id'])) {
    $renewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
    if ($renewal && !empty($renewal['company_id'])) {
        return (int)$renewal['company_id'];
    }
}
```

**修正後：**
```php
// 直接使用 company_id
if (!empty($user['company_id'])) {
    return (int)$user['company_id'];
}
```

---

### 3. 問題5：移除前端 Middleware 過時邏輯 🟡 P1

**修正文件：** [frontend/middleware/company-manager.js](frontend/middleware/company-manager.js)

**改善內容：**
- 移除手動從 localStorage/sessionStorage 恢復 token 的邏輯
- 統一使用 HttpOnly Cookies 機制
- 簡化認證檢查流程

**修正前：**
```javascript
// 從 localStorage 或 sessionStorage 讀取並手動恢復狀態
if (!authStore.token && process.client) {
    const persistedAuth = localStorage.getItem('auth') || sessionStorage.getItem('auth')
    // ... 手動恢復邏輯
}
```

**修正後：**
```javascript
// 直接使用 HttpOnly Cookie 檢查登入狀態
if (!authStore.isLoggedIn) {
    return navigateTo('/login')
}
```

---

### 4. 問題7：統一 CORS 設定 🟡 P1

**新增文件：** [backend/app/Filters/CorsFilter.php](backend/app/Filters/CorsFilter.php)

**修正文件：**
- [backend/app/Config/Filters.php](backend/app/Config/Filters.php)
- [backend/app/Controllers/Api/AuthController.php](backend/app/Controllers/Api/AuthController.php)
- [backend/app/Controllers/Api/JointCommonAreaController.php](backend/app/Controllers/Api/JointCommonAreaController.php)
- [backend/app/Controllers/Api/CompanyController.php](backend/app/Controllers/Api/CompanyController.php)
- [backend/app/Controllers/Api/UrbanRenewalController.php](backend/app/Controllers/Api/UrbanRenewalController.php)
- [backend/app/Controllers/Api/LandPlotController.php](backend/app/Controllers/Api/LandPlotController.php)
- [backend/app/Controllers/Api/MeetingAttendanceController.php](backend/app/Controllers/Api/MeetingAttendanceController.php)
- [backend/app/Controllers/Api/MeetingController.php](backend/app/Controllers/Api/MeetingController.php)
- [backend/app/Controllers/Api/LocationController.php](backend/app/Controllers/Api/LocationController.php)
- [backend/app/Controllers/Api/PropertyOwnerController.php](backend/app/Controllers/Api/PropertyOwnerController.php)

**改善內容：**
- 創建統一的 `CorsFilter` 處理所有 CORS 請求
- 集中管理允許的來源清單
- 移除所有 Controller 中的重複 CORS 設定
- 移除各 Controller 中的 `options()` 方法

**優勢：**
- 單一職責原則：CORS 邏輯集中在 Filter 中
- 易於維護：修改 CORS 設定只需更新一個文件
- 安全性提升：統一的白名單管理，支援 credentials

---

### 5. 問題6：改善投票權重計算精度 🟡 P2

**新增文件：** [backend/app/Database/Migrations/2026-01-24-000001_ImproveVotingWeightPrecision.php](backend/app/Database/Migrations/2026-01-24-000001_ImproveVotingWeightPrecision.php)

**改善內容：**
- 將 `land_area_weight` 和 `building_area_weight` 的精度從 `DECIMAL(12,2)` 提升到 `DECIMAL(20,10)`
- 影響表：`voting_records`、`meeting_eligible_voters`
- 支援更精確的面積持分計算，減少浮點數誤差

**執行方式：**
```bash
cd backend
php spark migrate
```

**說明：**
- 原精度 `DECIMAL(12,2)` 只支援 2 位小數，面積計算可能產生誤差
- 新精度 `DECIMAL(20,10)` 支援 10 位小數，滿足複雜持分比例計算
- 建議後續使用 BC Math 函式庫進行精確計算

---

### 6. 問題9：清理備份檔案 🟢 P3

**清理文件：**
- `backend/app/Database/Migrations/2025-11-08-000001_RemoveDeletedAtFromPropertyOwnersTable.php.bak`
- `backend/app/Database/Migrations/2025-11-08-000002_RemoveDeletedAtFromOwnershipTables.php.bak`

**修正文件：** [.gitignore](.gitignore)

**改善內容：**
- 刪除所有 `.bak` 備份檔案
- 在 `.gitignore` 中新增 `*.bak` 規則
- 避免未來備份檔案被意外提交

---

### 7. 問題8：處理測試頁面 🟢 P3

**移動文件：**

**前端測試頁面** → `frontend/pages/dev-tests/`
- `test-all-features.vue`
- `test-api.vue`
- `test-features.vue`
- `test-role.vue`
- `test-session.vue`
- `test-simple.vue`

**前端測試腳本** → `frontend/dev-tests/`
- `test-session-storage.js`
- `test-session-storage.mjs`

**新增文件：**
- [frontend/pages/dev-tests/README.md](frontend/pages/dev-tests/README.md)

**改善內容：**
- 將測試頁面組織到專用目錄
- 提供使用說明文檔
- 明確標示這些頁面僅供開發使用

**訪問路徑：**
- `/dev-tests/test-all-features`
- `/dev-tests/test-api`
- 等等...

---

## ⚠️ 未修正項目

### 問題4：會議合格投票人快照包含所有所有權人 🟡 P1

**狀態：** 根據使用者要求，此問題不進行修正

**原因：** 使用者明確指示「會議合格投票人快照包含所有所有權人，這個問題不要修正」

**相關文件：** `backend/app/Models/MeetingEligibleVoterModel.php`

---

## 📋 修正總結

### 統計數據

- **修正文件數量：** 16 個文件
- **新增文件數量：** 3 個文件
- **移動文件數量：** 8 個文件
- **刪除文件數量：** 2 個文件

### 優先級分佈

- 🔴 P0（立即）：2 項修正完成
- 🟡 P1（2週內）：2 項修正完成，1 項未修正（按使用者要求）
- 🟡 P2（1個月內）：1 項修正完成
- 🟢 P3（隨時）：2 項修正完成

### 影響範圍

1. **後端：** 權限邏輯、CORS 設定、資料庫精度
2. **前端：** 認證中介層、測試頁面組織
3. **專案管理：** 備份檔案清理、.gitignore 優化

---

## 🚀 後續建議

### 短期（已完成）
- ✅ 投票權限邏輯修正
- ✅ CORS 設定統一化
- ✅ 過渡期代碼清理

### 中期（建議）
1. 執行資料庫遷移以應用新的精度設定
2. 在 VotingRecordModel 中使用 BC Math 進行面積計算
3. 為測試頁面添加環境變數控制（生產環境隱藏）

### 長期（建議）
1. 建立自動化測試涵蓋投票權限邏輯
2. 監控 CORS Filter 的性能影響
3. 定期清理技術債和 TODO 註解

---

## 📝 注意事項

### 資料庫遷移
執行以下命令應用投票權重精度改善：
```bash
cd backend
php spark migrate
```

### CORS 設定
新的 CorsFilter 已在 `Config/Filters.php` 中註冊為全域 filter，會自動處理所有請求的 CORS headers。

### 測試頁面
測試頁面已移至 `frontend/pages/dev-tests/`，在生產環境建議通過 middleware 限制訪問。

---

**修正完成時間：** 2026年1月24日
**修正人員：** GitHub Copilot
**審核狀態：** 待審核
