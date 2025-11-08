# 更新會分配功能說明文件

## 目錄
1. [功能概述](#功能概述)
2. [動機與目的](#動機與目的)
3. [業務規則](#業務規則)
4. [使用說明](#使用說明)
5. [技術架構](#技術架構)
6. [資料結構](#資料結構)
7. [API 說明](#api-說明)
8. [注意事項](#注意事項)

---

## 功能概述

「更新會分配功能」允許系統管理員和企業管理者將不同的更新會項目分配給特定的企業管理者進行管理。此功能提供了集中化的批次分配介面，讓管理員可以一次性為多個更新會指定負責的管理者。

### 核心功能
- **批次分配**：可同時為多個更新會分配管理者
- **即時顯示**：列表頁面即時顯示每個更新會的歸屬管理者
- **彈性調整**：支援重新分配或取消分配
- **權限控管**：系統管理員可分配所有更新會，企業管理者只能分配自己所屬的更新會

---

## 動機與目的

### 業務需求背景

#### 1. 管理職責明確化
在都更計票系統中，一個系統可能管理多個不同的更新會項目。每個更新會都有其獨特的成員、土地、建物等資料。為了提高管理效率，需要將不同的更新會明確分配給特定的企業管理者負責。

#### 2. 減少管理混亂
如果沒有明確的分配機制，多個管理者可能同時處理同一個更新會，導致：
- 資料重複維護
- 職責不清
- 溝通成本增加
- 決策衝突

#### 3. 提升工作效率
通過明確的分配：
- 每個管理者清楚知道自己負責的更新會
- 可以專注於特定項目
- 減少不必要的切換成本
- 便於追蹤責任歸屬

#### 4. 支援組織架構
對於大型都更顧問公司：
- 可能有多位企業管理者
- 每位管理者負責不同區域或類型的更新會
- 需要系統化的分配機制來反映組織架構

---

## 業務規則

### 1. 權限規則

#### 1.1 分配權限
- **執行者**：
  - 系統管理員（`role: 'admin'`）：可以分配所有更新會
  - 企業管理者（`is_company_manager: 1`）：只能分配自己所屬的更新會（`urban_renewal_id` 對應的更新會）
- **對象**：僅可分配給啟用中且屬於該更新會的企業管理者（`is_company_manager: 1`、`is_active: 1` 且 `urban_renewal_id` 與更新會 ID 相同）
- **限制**：一般使用者無法執行分配操作

#### 1.2 權限驗證流程
```
使用者發起分配請求
    ↓
系統驗證使用者身份
    ↓
檢查是否為系統管理員或企業管理者
    ├─ 是系統管理員 → 可分配所有更新會
    ├─ 是企業管理者 → 檢查更新會是否屬於該管理者
    │   ├─ 是 → 繼續執行
    │   └─ 否 → 返回 403 Forbidden
    └─ 都不是 → 返回 403 Forbidden
```

### 2. 分配規則

#### 2.1 分配狀態
- **未分配**：`assigned_admin_id` 為 `NULL`，顯示「未分配」
- **已分配**：`assigned_admin_id` 有值，顯示管理者姓名
- **重新分配**：可以將已分配的更新會分配給其他管理者
- **取消分配**：可以將已分配的更新會設為未分配

#### 2.2 分配限制
- 一個更新會同時只能分配給一位企業管理者
- 可以選擇不分配（設為未分配狀態）
- 分配的管理者必須是啟用狀態

#### 2.3 資料完整性
- 如果分配的管理者被刪除，更新會的 `assigned_admin_id` 自動設為 `NULL`（`ON DELETE SET NULL`）
- 如果管理者 ID 更新，更新會的關聯也會同步更新（`ON UPDATE CASCADE`）

### 3. 批次處理規則

#### 3.1 批次分配
- 可以一次為多個更新會進行分配
- 每個更新會可以分配給不同的管理者
- 使用交易（Transaction）確保資料一致性
- 如果任何一筆分配失敗，整個批次回滾

#### 3.2 驗證流程
```
接收分配資料
    ↓
驗證資料格式
    ↓
逐筆驗證管理者存在性
    ↓
逐筆驗證管理者資格
    ├─ 是否為企業管理者
    └─ 是否為啟用狀態
    ↓
開始交易
    ↓
執行批次分配
    ↓
提交交易或回滾
```

---

## 使用說明

### 1. 存取分配功能

#### 1.1 進入頁面
1. 登入系統（需具備系統管理員權限）
2. 前往「更新會管理」頁面（`/tables/urban-renewal`）
3. 點擊右上角的「分配更新會」按鈕

#### 1.2 權限要求
- **必須**：系統管理員權限（`role: 'admin'`）
- **中介層**：`company-manager` middleware
- 非管理員使用者點擊後會收到權限不足的錯誤訊息

### 2. 執行分配操作

#### 2.1 分配介面說明
分配對話框包含以下資訊：
- **更新會名稱**：顯示更新會的名稱
- **所有權人數**：顯示該更新會的所有權人總數
- **當前歸屬**：顯示目前分配的管理者（如有）
- **分配管理者**：下拉選單，可選擇要分配的企業管理者

#### 2.2 分配步驟
1. 在分配對話框中，瀏覽所有更新會列表
2. 對於需要分配的更新會，點擊「分配管理者」下拉選單
3. 從選單中選擇要分配的企業管理者
4. 選項格式：`姓名 (email)`
5. 可以選擇「未分配」來取消現有分配
6. 完成所有選擇後，點擊「確認分配」按鈕
7. 系統執行分配並顯示結果

#### 2.3 分配結果
- **成功**：顯示成功訊息，自動關閉對話框，列表刷新顯示新的分配狀態
- **失敗**：顯示錯誤訊息，對話框保持開啟，可修正後重試

### 3. 查看分配狀態

#### 3.1 列表顯示
在更新會管理列表頁面，新增「歸屬管理者」欄位：
- **已分配**：顯示綠色標籤，包含管理者姓名和使用者圖示
- **未分配**：顯示灰色文字「未分配」和減號圖示

#### 3.2 狀態圖示
- ✓ 使用者圖示（綠色）：已分配給企業管理者
- ⊝ 減號圖示（灰色）：尚未分配

---

## 技術架構

### 1. 系統架構圖

```
┌─────────────────────────────────────────────────────────┐
│                     前端 (Nuxt 3)                        │
├─────────────────────────────────────────────────────────┤
│  pages/tables/urban-renewal/index.vue                   │
│    ├─ 更新會列表頁面                                     │
│    ├─ 顯示分配狀態                                       │
│    └─ 觸發分配對話框                                     │
│                                                          │
│  components/UrbanRenewal/AssignAdminModal.vue           │
│    ├─ 分配對話框元件                                     │
│    ├─ 顯示更新會列表                                     │
│    ├─ 提供管理者選擇                                     │
│    └─ 提交分配資料                                       │
└─────────────────────────────────────────────────────────┘
                            ↕ HTTP/JSON
┌─────────────────────────────────────────────────────────┐
│                   後端 (CodeIgniter 4)                   │
├─────────────────────────────────────────────────────────┤
│  Controllers/Api/UrbanRenewalController.php             │
│    ├─ batchAssign()      批次分配 API                   │
│    └─ getCompanyManagers() 取得企業管理者列表           │
│                                                          │
│  Models/UrbanRenewalModel.php                           │
│    ├─ batchAssignAdmin()  批次分配邏輯                   │
│    └─ getUrbanRenewalsWithAdmin() 查詢含管理者資訊       │
│                                                          │
│  Models/UserModel.php                                    │
│    └─ 查詢企業管理者                                     │
└─────────────────────────────────────────────────────────┘
                            ↕ SQL
┌─────────────────────────────────────────────────────────┐
│                    資料庫 (MariaDB)                      │
├─────────────────────────────────────────────────────────┤
│  urban_renewals 表                                       │
│    ├─ assigned_admin_id (新增欄位)                      │
│    └─ 外鍵關聯到 users.id                                │
│                                                          │
│  users 表                                                │
│    ├─ is_company_manager                                │
│    └─ is_active                                          │
└─────────────────────────────────────────────────────────┘
```

### 2. 資料流程

#### 2.1 開啟分配對話框
```
1. 使用者點擊「分配更新會」按鈕
2. 前端觸發 openAssignAdminModal()
3. 呼叫 API: GET /api/urban-renewals/company-managers
4. 後端查詢企業管理者列表
5. 返回管理者資料
6. 前端顯示分配對話框
7. 使用者看到更新會列表和管理者選項
```

#### 2.2 執行分配
```
1. 使用者選擇管理者並點擊「確認分配」
2. 前端觸發 handleAssignSubmit(assignments)
3. 呼叫 API: POST /api/urban-renewals/batch-assign
   Body: {
     assignments: {
       urbanRenewalId1: adminId1,
       urbanRenewalId2: adminId2,
       ...
     }
   }
4. 後端驗證權限
5. 後端驗證每個管理者
6. 開始資料庫交易
7. 逐筆更新 urban_renewals.assigned_admin_id
8. 提交交易
9. 返回成功訊息
10. 前端刷新列表
11. 顯示更新後的分配狀態
```

---

## 資料結構

### 1. 資料庫架構

#### 1.1 urban_renewals 表新增欄位

```sql
CREATE TABLE `urban_renewals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `area` DECIMAL(10,2) NOT NULL COMMENT '土地面積(平方公尺)',
  `member_count` INT(11) UNSIGNED NOT NULL COMMENT '所有權人數',
  `chairman_name` VARCHAR(100) NOT NULL COMMENT '理事長姓名',
  `chairman_phone` VARCHAR(20) NOT NULL COMMENT '理事長電話',
  `assigned_admin_id` INT(11) UNSIGNED NULL COMMENT '分配的企業管理者ID',  -- 新增
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assigned_admin_id` (`assigned_admin_id`),
  CONSTRAINT `fk_urban_renewals_assigned_admin`
    FOREIGN KEY (`assigned_admin_id`)
    REFERENCES `users`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 1.2 欄位說明

| 欄位名 | 類型 | 允許 NULL | 預設值 | 說明 |
|--------|------|-----------|--------|------|
| `assigned_admin_id` | INT(11) UNSIGNED | YES | NULL | 分配的企業管理者 ID，關聯到 users.id |

#### 1.3 索引

- **idx_assigned_admin_id**: 加速查詢特定管理者負責的更新會
- **fk_urban_renewals_assigned_admin**: 外鍵約束，確保參考完整性

#### 1.4 外鍵行為

- **ON DELETE SET NULL**: 當管理者被刪除時，將 `assigned_admin_id` 設為 NULL
- **ON UPDATE CASCADE**: 當管理者 ID 更新時，同步更新 `assigned_admin_id`

### 2. Model 結構

#### 2.1 UrbanRenewalModel 新增欄位

```php
protected $allowedFields = [
    // ... 其他欄位
    'assigned_admin_id'  // 新增
];
```

#### 2.2 新增方法

**batchAssignAdmin()**
```php
/**
 * 批次分配管理者到多個更新會
 *
 * @param array $assignments 分配資料 ['urban_renewal_id' => admin_id]
 * @return bool 分配是否成功
 */
public function batchAssignAdmin($assignments)
```

**getUrbanRenewalsWithAdmin()**
```php
/**
 * 取得包含管理者資訊的更新會列表
 *
 * @param int $page 頁碼
 * @param int $perPage 每頁筆數
 * @param int|null $urbanRenewalId 過濾特定更新會
 * @return array 更新會列表（含管理者姓名、email）
 */
public function getUrbanRenewalsWithAdmin($page = 1, $perPage = 10, $urbanRenewalId = null)
```

---

## API 說明

### 1. 取得企業管理者列表

#### 1.1 基本資訊

- **路徑**: `GET /api/urban-renewals/company-managers`
- **權限**: 需要認證（所有已登入使用者）
- **用途**: 取得可分配的企業管理者列表

#### 1.2 請求範例

```http
GET /api/urban-renewals/company-managers HTTP/1.1
Host: example.com
Authorization: Bearer {token}
```

#### 1.3 回應格式

**成功回應 (200 OK)**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "username": "manager1",
      "email": "manager1@example.com",
      "full_name": "王大明",
      "is_company_manager": 1,
      "is_active": 1
    },
    {
      "id": 2,
      "username": "manager2",
      "email": "manager2@example.com",
      "full_name": "李小華",
      "is_company_manager": 1,
      "is_active": 1
    }
  ]
}
```

**錯誤回應 (401 Unauthorized)**
```json
{
  "status": "error",
  "message": "未授權訪問"
}
```

### 2. 批次分配更新會

#### 2.1 基本資訊

- **路徑**: `POST /api/urban-renewals/batch-assign`
- **權限**:
  - 系統管理員（role: admin）：可分配所有更新會
  - 企業管理者（is_company_manager: 1）：只能分配自己所屬的更新會
- **用途**: 批次分配更新會給企業管理者

#### 2.2 請求範例

```http
POST /api/urban-renewals/batch-assign HTTP/1.1
Host: example.com
Authorization: Bearer {token}
Content-Type: application/json

{
  "assignments": {
    "1": 10,      // 更新會 ID 1 分配給管理者 ID 10
    "2": 10,      // 更新會 ID 2 分配給管理者 ID 10
    "3": 15,      // 更新會 ID 3 分配給管理者 ID 15
    "4": null     // 更新會 ID 4 取消分配
  }
}
```

#### 2.3 請求參數

| 參數 | 類型 | 必填 | 說明 |
|------|------|------|------|
| `assignments` | Object | 是 | 分配對應表，key 為更新會 ID，value 為管理者 ID（null 表示取消分配）|

#### 2.4 回應格式

**成功回應 (200 OK)**
```json
{
  "status": "success",
  "message": "分配成功"
}
```

**錯誤回應**

1. **未授權 (401 Unauthorized)**
```json
{
  "status": "error",
  "message": "未授權訪問"
}
```

2. **權限不足 (403 Forbidden)**

一般使用者執行分配：
```json
{
  "status": "error",
  "message": "權限不足，只有系統管理員或企業管理者可以分配更新會"
}
```

企業管理者嘗試分配非自己所屬的更新會：
```json
{
  "status": "error",
  "message": "權限不足，您只能分配自己所屬的更新會"
}
```

3. **資料格式錯誤 (400 Bad Request)**
```json
{
  "status": "error",
  "message": "請提供有效的分配資料"
}
```

4. **管理者不存在 (400 Bad Request)**
```json
{
  "status": "error",
  "message": "管理者 ID 10 不存在"
}
```

5. **非企業管理者 (400 Bad Request)**
```json
{
  "status": "error",
  "message": "使用者 王大明 不是企業管理者"
}
```

6. **伺服器錯誤 (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "分配失敗：[錯誤詳情]"
}
```

#### 2.5 驗證規則

1. **格式驗證**
   - `assignments` 必須是物件
   - key 必須是有效的更新會 ID
   - value 必須是有效的管理者 ID 或 null

2. **管理者驗證**
   - 管理者必須存在於 users 表中
   - `is_company_manager` 必須為 1
   - 可選驗證：`is_active` 必須為 1

3. **權限驗證**
   - 執行者必須具備系統管理員或企業管理者權限
   - 如果是企業管理者，只能分配 `user.urban_renewal_id` 對應的更新會
   - 如果是系統管理員，可以分配所有更新會

---

## 注意事項

### 1. 權限管理

#### 1.1 執行權限
- ⚠️ **重要**：系統管理員和企業管理者可以執行分配操作
- **系統管理員**：可以分配所有更新會給任何企業管理者
- **企業管理者**：只能分配自己所屬的更新會（`user.urban_renewal_id`）
- 確保不要將系統管理員權限濫發給不需要的使用者
- 定期審查具備系統管理員權限的帳號
- 企業管理者只能在其管轄範圍內進行分配操作

#### 1.2 資料安全
- 所有 API 都需要通過認證
- 分配操作會記錄在資料庫日誌中（透過 `updated_at` 欄位）
- 建議定期備份資料庫

### 2. 資料完整性

#### 2.1 外鍵約束
- 不要直接刪除 users 表中的記錄，應使用停用功能（`is_active = 0`）
- 如果確實需要刪除管理者，確保相關的更新會已重新分配

#### 2.2 批次操作
- 批次分配使用交易機制，確保全部成功或全部失敗
- 如果批次中有任何錯誤，整個操作都會回滾
- 避免在尖峰時段執行大量批次分配

### 3. 效能考量

#### 3.1 查詢最佳化
- `assigned_admin_id` 已建立索引，查詢效能良好
- 列表頁面使用 LEFT JOIN 查詢管理者資訊，單次查詢取得所有資料

#### 3.2 批次大小
- 建議單次批次分配不超過 100 筆
- 如有大量更新會需要分配，考慮分批進行

### 4. 使用者體驗

#### 4.1 即時反饋
- 分配成功後自動關閉對話框並刷新列表
- 錯誤訊息清楚指出問題所在

#### 4.2 視覺提示
- 已分配的更新會使用綠色標籤顯示
- 未分配的使用灰色文字
- 圖示輔助視覺辨識

### 5. 未來擴展

#### 5.1 可能的功能增強
- 分配歷史記錄（追蹤誰在何時分配給誰）
- 分配變更通知（email 或系統通知）
- 批量匯入分配（CSV 或 Excel）
- 管理者工作量統計（顯示每位管理者負責的更新會數量）
- 自動分配建議（基於地區或負載平衡）

#### 5.2 資料架構建議
如需實作分配歷史，建議新增 `urban_renewal_assignments` 表：
```sql
CREATE TABLE `urban_renewal_assignments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `urban_renewal_id` INT(11) UNSIGNED NOT NULL,
  `admin_id` INT(11) UNSIGNED NULL,
  `assigned_by` INT(11) UNSIGNED NOT NULL COMMENT '分配操作執行者',
  `assigned_at` DATETIME NOT NULL,
  `action` ENUM('assign', 'unassign', 'reassign') NOT NULL,
  PRIMARY KEY (`id`)
);
```

### 6. 故障排除

#### 6.1 常見問題

**問題：分配失敗顯示「權限不足」**
- 確認執行者是否具備系統管理員權限（role: admin）
- 檢查 JWT Token 是否正確且未過期

**問題：找不到企業管理者**
- 確認使用者的 `is_company_manager` 欄位為 1
- 確認使用者的 `is_active` 欄位為 1
- 檢查資料庫連線是否正常

**問題：分配後列表沒有更新**
- 檢查瀏覽器 Console 是否有錯誤
- 確認 API 回應是否成功
- 嘗試手動重新整理頁面

#### 6.2 除錯步驟
1. 檢查瀏覽器開發者工具 Network 標籤
2. 查看 API 請求和回應
3. 檢查後端日誌（CodeIgniter logs）
4. 查詢資料庫驗證資料是否正確更新

---

## 附錄

### A. 相關檔案清單

#### 後端檔案
- `backend/app/Database/Migrations/2025-11-08-050000_AddAssignedAdminIdToUrbanRenewalsTable.php` - 資料庫遷移檔案
- `backend/app/Models/UrbanRenewalModel.php` - 更新會 Model（已擴充）
- `backend/app/Controllers/Api/UrbanRenewalController.php` - API Controller（新增方法）
- `backend/app/Config/Routes.php` - API 路由定義

#### 前端檔案
- `frontend/components/UrbanRenewal/AssignAdminModal.vue` - 分配對話框元件
- `frontend/pages/tables/urban-renewal/index.vue` - 更新會列表頁面（已擴充）

### B. 資料庫遷移命令

```bash
# 執行遷移
docker exec urban_renewal_backend_dev php spark migrate

# 檢查遷移狀態
docker exec urban_renewal_backend_dev php spark migrate:status

# 回滾遷移（如需）
docker exec urban_renewal_backend_dev php spark migrate:rollback
```

### C. 測試建議

#### C.1 單元測試
建議為以下方法編寫單元測試：
- `UrbanRenewalModel::batchAssignAdmin()`
- `UrbanRenewalModel::getUrbanRenewalsWithAdmin()`
- `UrbanRenewalController::batchAssign()`
- `UrbanRenewalController::getCompanyManagers()`

#### C.2 整合測試
建議測試以下情境：
- 系統管理員成功分配更新會
- 非系統管理員嘗試分配（應失敗）
- 分配給不存在的管理者（應失敗）
- 分配給非企業管理者（應失敗）
- 批次分配部分失敗的回滾機制
- 取消分配（設為 NULL）
- 重新分配已分配的更新會

---

## 修訂歷史

| 版本 | 日期 | 修訂內容 | 作者 |
|------|------|----------|------|
| 1.0 | 2025-11-08 | 初版完成 | 系統 |

---

**文件結束**
