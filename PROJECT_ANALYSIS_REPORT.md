# 都市更新會管理系統 - 完整功能分析報告

> 撰寫日期：2026年1月23日

---

## 目錄

1. [系統概述](#1-系統概述)
2. [完整功能清單](#2-完整功能清單)
3. [系統架構分析](#3-系統架構分析)
4. [不合理之處與改善建議](#4-不合理之處與改善建議)
5. [總結](#5-總結)

---

## 1. 系統概述

### 1.1 系統目的

此系統是一個**都市更新會管理系統**（Urban Renewal Management System），主要用於：
- 管理都市更新會的運作
- 處理會員大會/理事會/監事會的會議流程
- 管理所有權人資料、地號、建號
- 執行投票計票功能（含土地/建物面積權重計算）
- 企業管理與成員指派

### 1.2 技術架構

| 層級 | 技術 |
|------|------|
| **後端** | PHP 8.1+ / CodeIgniter 4 |
| **前端** | Nuxt 3 / Vue 3 / TailwindCSS |
| **資料庫** | MySQL |
| **認證** | JWT Token + HttpOnly Cookies |
| **容器化** | Docker / Docker Compose |

### 1.3 使用者類型

| 類型 | 說明 |
|------|------|
| **一般使用者 (general)** | 角色：admin, chairman, member, observer |
| **企業使用者 (enterprise)** | 角色同上，但可設定為企業管理者 (is_company_manager) |

---

## 2. 完整功能清單

### 2.1 認證與授權模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 使用者註冊 | `POST /api/auth/register` | 支援個人/企業帳號、邀請碼註冊 |
| 使用者登入 | `POST /api/auth/login` | JWT Token 認證，支援帳號鎖定機制 |
| 使用者登出 | `POST /api/auth/logout` | 清除 Token |
| Token 刷新 | `POST /api/auth/refresh` | 刷新 JWT Token |
| 取得當前使用者 | `GET /api/auth/me` | 驗證 Token 有效性 |
| 忘記密碼 | `POST /api/auth/forgot-password` | 發送重設連結 |
| 重設密碼 | `POST /api/auth/reset-password` | 重設密碼 |

### 2.2 使用者管理模組

| 功能 | API 端點 | 權限 |
|------|----------|------|
| 使用者列表 | `GET /api/users` | Admin |
| 使用者詳情 | `GET /api/users/{id}` | Admin |
| 新增使用者 | `POST /api/users` | Admin |
| 更新使用者 | `PUT /api/users/{id}` | Admin |
| 刪除使用者 | `DELETE /api/users/{id}` | Admin |
| 切換狀態 | `PATCH /api/users/{id}/toggle-status` | Admin |
| 重設登入嘗試 | `PATCH /api/users/{id}/reset-login-attempts` | Admin |
| 設為企業使用者 | `POST /api/users/{id}/set-as-company-user` | Admin |
| 設為企業管理者 | `POST /api/users/{id}/set-as-company-manager` | Admin |
| 搜尋使用者 | `GET /api/users/search` | Admin |
| 角色統計 | `GET /api/users/role-statistics` | Admin |
| 個人資料 | `GET /api/users/profile` | 已登入 |
| 更新個人資料 | `PUT /api/users/profile` | 已登入 |
| 變更密碼 | `POST /api/users/change-password` | 已登入 |

### 2.3 企業管理模組

| 功能 | API 端點 | 權限 |
|------|----------|------|
| 取得企業資料 | `GET /api/companies/me` | 企業管理者 |
| 更新企業資料 | `PUT /api/companies/me` | 企業管理者 |
| 取得更新會列表 | `GET /api/companies/me/renewals` | 企業管理者 |
| 取得待審核使用者 | `GET /api/companies/me/pending-users` | 企業管理者 |
| 審核使用者 | `POST /api/companies/me/approve-user/{id}` | 企業管理者 |
| 取得邀請碼 | `GET /api/companies/me/invite-code` | 企業管理者 |
| 產生新邀請碼 | `POST /api/companies/me/generate-invite-code` | 企業管理者 |
| 取得更新會成員 | `GET /api/companies/me/renewals/{id}/members` | 企業管理者 |
| 指派成員到更新會 | `POST /api/companies/me/renewals/{id}/assign` | 企業管理者 |
| 取消成員指派 | `DELETE /api/companies/me/renewals/{id}/members/{userId}` | 企業管理者 |
| 取得可用成員 | `GET /api/companies/me/available-members` | 企業管理者 |

### 2.4 都市更新會管理模組

| 功能 | API 端點 | 權限 |
|------|----------|------|
| 更新會列表 | `GET /api/urban-renewals` | 企業管理者/Admin |
| 更新會詳情 | `GET /api/urban-renewals/{id}` | 企業管理者/Admin |
| 新增更新會 | `POST /api/urban-renewals` | 企業管理者/Admin |
| 更新更新會 | `PUT /api/urban-renewals/{id}` | 企業管理者/Admin |
| 刪除更新會 | `DELETE /api/urban-renewals/{id}` | 企業管理者/Admin |
| 批次指派 | `POST /api/urban-renewals/batch-assign` | Admin |
| 取得企業管理者 | `GET /api/urban-renewals/company-managers` | Admin |

### 2.5 地號管理模組 (Land Plots)

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 地號列表 | `GET /api/urban-renewals/{id}/land-plots` | 依更新會篩選 |
| 新增地號 | `POST /api/urban-renewals/{id}/land-plots` | 建立新地號 |
| 地號詳情 | `GET /api/land-plots/{id}` | 單筆查詢 |
| 更新地號 | `PUT /api/land-plots/{id}` | 編輯地號 |
| 刪除地號 | `DELETE /api/land-plots/{id}` | 軟刪除 |
| 設定代表人 | `PUT /api/land-plots/{id}/representative` | 設定代表地號 |

### 2.6 建號管理模組 (Buildings)

建號資料主要透過**所有權人模組**進行關聯管理，包含：
- 縣市/行政區/段小段
- 建號母號/子號
- 建物面積
- 建物地址

### 2.7 共同區域管理模組 (Joint Common Areas)

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 共同區域列表 | `GET /api/urban-renewals/{id}/joint-common-areas` | 依更新會篩選 |
| 新增共同區域 | `POST /api/urban-renewals/{id}/joint-common-areas` | 建立新共同區域 |
| 共同區域詳情 | `GET /api/joint-common-areas/{id}` | 單筆查詢 |
| 更新共同區域 | `PUT /api/joint-common-areas/{id}` | 編輯 |
| 刪除共同區域 | `DELETE /api/joint-common-areas/{id}` | 軟刪除 |

### 2.8 所有權人管理模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 所有權人列表 | `GET /api/urban-renewals/{id}/property-owners` | 依更新會篩選 |
| 所有權人詳情 | `GET /api/property-owners/{id}` | 含土地/建物持分 |
| 新增所有權人 | `POST /api/property-owners` | 建立新所有權人 |
| 更新所有權人 | `PUT /api/property-owners/{id}` | 編輯 |
| 刪除所有權人 | `DELETE /api/property-owners/{id}` | 刪除 |
| 匯入所有權人 | `POST /api/urban-renewals/{id}/property-owners/import` | Excel 批次匯入 |
| 匯出所有權人 | `GET /api/urban-renewals/{id}/property-owners/export` | Excel 匯出 |
| 下載範本 | `GET /api/property-owners/template` | 下載匯入範本 |
| 全部建物 | `GET /api/urban-renewals/{id}/property-owners/all-buildings` | 查看所有建物 |

**所有權人欄位：**
- 基本資料：姓名、身分證字號、編號
- 聯絡方式：電話1、電話2、聯絡地址、戶籍地址
- 排除類型：法院囑託查封、假扣押、假處分、破產登記、未經繼承
- 關聯資料：土地持分、建物持分

### 2.9 會議管理模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 會議列表 | `GET /api/meetings` | 支援分頁/篩選 |
| 會議詳情 | `GET /api/meetings/{id}` | 單筆查詢 |
| 新增會議 | `POST /api/meetings` | 建立新會議 |
| 更新會議 | `PUT /api/meetings/{id}` | 編輯會議 |
| 刪除會議 | `DELETE /api/meetings/{id}` | 軟刪除 |
| 搜尋會議 | `GET /api/meetings/search` | 關鍵字搜尋 |
| 即將到來的會議 | `GET /api/meetings/upcoming` | 未來會議 |
| 會議狀態統計 | `GET /api/meetings/status-statistics` | 狀態統計 |
| 會議統計 | `GET /api/meetings/{id}/statistics` | 單一會議統計 |
| 更新會議狀態 | `PATCH /api/meetings/{id}/status` | 變更狀態 |
| 匯出會議通知書 | `GET /api/meetings/{id}/export-notice` | PDF 匯出 |
| 匯出簽到簿 | `GET /api/meetings/{id}/export-signature-book` | PDF 匯出 |
| 合格投票人列表 | `GET /api/meetings/{id}/eligible-voters` | 快照名單 |
| 重新整理快照 | `POST /api/meetings/{id}/eligible-voters/refresh` | 更新快照 |

**會議類型：**
- 會員大會
- 理事會
- 監事會
- 臨時會議

**會議狀態：**
- draft（草稿）
- scheduled（已排程）
- in_progress（進行中）
- completed（已完成）
- cancelled（已取消）

### 2.10 出席管理模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 出席記錄列表 | `GET /api/meetings/{id}/attendances` | 依會議查詢 |
| 會員報到 | `POST /api/meetings/{meetingId}/attendances/{ownerId}` | 簽到 |
| 更新出席狀態 | `PUT /api/meetings/{meetingId}/attendances/{ownerId}` | 更新 |
| 出席統計 | `GET /api/meetings/{id}/attendances/statistics` | 統計資料 |
| 匯出出席記錄 | `POST /api/meetings/{id}/attendances/export` | 匯出報表 |
| 批次報到 | `POST /api/meetings/{id}/attendances/batch` | 批次簽到 |

**出席類型：**
- present（親自出席）
- proxy（委託出席）
- absent（未出席）

### 2.11 投票議題管理模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 議題列表 | `GET /api/voting-topics` | 支援分頁 |
| 議題詳情 | `GET /api/voting-topics/{id}` | 單筆查詢 |
| 新增議題 | `POST /api/voting-topics` | 建立議題 |
| 更新議題 | `PUT /api/voting-topics/{id}` | 編輯議題 |
| 刪除議題 | `DELETE /api/voting-topics/{id}` | 軟刪除 |
| 開始投票 | `PATCH /api/voting-topics/{id}/start-voting` | 啟動投票 |
| 結束投票 | `PATCH /api/voting-topics/{id}/close-voting` | 關閉投票 |
| 議題統計 | `GET /api/voting-topics/statistics` | 統計資料 |
| 即將投票的議題 | `GET /api/voting-topics/upcoming` | 未來議題 |

**投票方式：**
- simple_majority（簡單多數）
- absolute_majority（絕對多數）
- two_thirds_majority（三分之二多數）
- unanimous（全數同意）

**投票結果：**
- pending（待決）
- passed（通過）
- failed（未通過）
- withdrawn（撤回）

### 2.12 投票記錄模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 投票記錄列表 | `GET /api/voting` | 查詢投票記錄 |
| 投票 | `POST /api/voting/vote` | 執行投票 |
| 批次投票 | `POST /api/voting/batch-vote` | 多議題投票 |
| 我的投票 | `GET /api/voting/my-vote/{topicId}` | 查詢個人投票 |
| 撤回投票 | `DELETE /api/voting/remove-vote` | 取消投票 |
| 投票統計 | `GET /api/voting/statistics/{topicId}` | 統計資料 |
| 匯出投票結果 | `GET /api/voting/export/{topicId}` | 匯出報表 |
| 詳細投票記錄 | `GET /api/voting/detailed/{topicId}` | 所有人投票情況 |
| 重新計算權重 | `POST /api/voting/recalculate-weights/{topicId}` | 更新面積權重 |

**投票選項：**
- agree（同意）
- disagree（不同意）
- abstain（棄權）

**權重計算：**
- 人數權重
- 土地面積權重
- 建物面積權重

### 2.13 通知管理模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 通知列表 | `GET /api/notifications` | 支援分頁 |
| 通知詳情 | `GET /api/notifications/{id}` | 單筆查詢 |
| 新增通知 | `POST /api/notifications` | 建立通知 |
| 標記已讀 | `PATCH /api/notifications/{id}/mark-read` | 單筆標記 |
| 批次標記已讀 | `PATCH /api/notifications/mark-multiple-read` | 多筆標記 |
| 全部標記已讀 | `PATCH /api/notifications/mark-all-read` | 全部標記 |
| 刪除通知 | `DELETE /api/notifications/{id}` | 刪除通知 |
| 未讀數量 | `GET /api/notifications/unread-count` | 統計未讀 |
| 通知統計 | `GET /api/notifications/statistics` | 統計資料 |
| 建立會議通知 | `POST /api/notifications/create-meeting-notification` | 會議通知 |
| 建立投票通知 | `POST /api/notifications/create-voting-notification` | 投票通知 |
| 清理過期通知 | `DELETE /api/notifications/clean-expired` | 清理 |
| 通知類型 | `GET /api/notifications/types` | 類型列表 |

### 2.14 文件管理模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 文件列表 | `GET /api/documents` | 支援分頁 |
| 文件詳情 | `GET /api/documents/{id}` | 單筆查詢 |
| 上傳文件 | `POST /api/documents/upload` | 上傳檔案 |
| 下載文件 | `GET /api/documents/download/{id}` | 下載檔案 |
| 更新文件 | `PUT /api/documents/{id}` | 編輯資訊 |
| 刪除文件 | `DELETE /api/documents/{id}` | 刪除檔案 |
| 文件統計 | `GET /api/documents/statistics` | 統計資料 |
| 最近文件 | `GET /api/documents/recent` | 最近上傳 |
| 文件類型 | `GET /api/documents/types` | 類型列表 |
| 儲存空間使用量 | `GET /api/documents/storage-usage` | 容量監控 |
| 清理孤立檔案 | `DELETE /api/documents/clean-orphan-files` | 清理未關聯 |
| 批次上傳 | `POST /api/documents/batch-upload` | 多檔上傳 |

### 2.15 系統設定模組

| 功能 | API 端點 | 權限 |
|------|----------|------|
| 設定列表 | `GET /api/system-settings` | Admin |
| 公開設定 | `GET /api/system-settings/public` | 公開 |
| 取得分類設定 | `GET /api/system-settings/category/{category}` | Admin |
| 取得單一設定 | `GET /api/system-settings/get/{key}` | Admin |
| 設定參數 | `POST /api/system-settings/set` | Admin |
| 批次設定 | `POST /api/system-settings/batch-set` | Admin |
| 重設設定 | `PATCH /api/system-settings/reset/{key}` | Admin |
| 設定分類列表 | `GET /api/system-settings/categories` | Admin |
| 清除快取 | `DELETE /api/system-settings/clear-cache` | Admin |
| 驗證設定 | `POST /api/system-settings/validate` | Admin |
| 系統資訊 | `GET /api/system-settings/system-info` | Admin |

### 2.16 地區資料模組

| 功能 | API 端點 | 說明 |
|------|----------|------|
| 縣市列表 | `GET /api/locations/counties` | 取得所有縣市 |
| 鄉鎮區列表 | `GET /api/locations/districts/{countyCode}` | 依縣市篩選 |
| 段列表 | `GET /api/locations/sections/{countyCode}/{districtCode}` | 依縣市/區篩選 |
| 階層資料 | `GET /api/locations/hierarchy` | 完整三級結構 |

### 2.17 除錯工具模組 (Admin Only)

| 功能 | API 端點 | 說明 |
|------|----------|------|
| JWT 除錯記錄 | `GET /api/admin/jwt-debug` | 查看驗證記錄 |
| JWT 統計 | `GET /api/admin/jwt-debug/stats` | 統計資料 |
| JWT 詳情 | `GET /api/admin/jwt-debug/{request_id}` | 單筆記錄 |
| 清理除錯記錄 | `POST /api/admin/jwt-debug/cleanup` | 清理舊記錄 |
| 註冊記錄 | `GET /api/admin/registration-logs` | 註冊日誌 |
| 註冊統計 | `GET /api/admin/registration-logs/statistics` | 統計資料 |

### 2.18 商城模組（前端）

| 功能 | 頁面路徑 | 說明 |
|------|----------|------|
| 購物頁面 | `/pages/shopping` | 瀏覽商品（可公開存取） |
| 購物車 | 內嵌功能 | SessionStorage 儲存 |
| 結帳頁面 | `/checkout` | 需登入 |
| 購買紀錄 | `/tables/order` | 需登入 |

**商品類型：**
- 增開更新會：NT$3,000
- 增加議題：NT$1,000

---

## 3. 系統架構分析

### 3.1 後端架構

```
app/
├── Controllers/Api/      # RESTful API 控制器
├── Models/               # 資料模型
├── Services/             # 業務邏輯服務層
├── Repositories/         # 資料存取層
├── Entities/             # 實體類別
├── Filters/              # 中介層（認證過濾器）
├── Helpers/              # 輔助函式
├── Exceptions/           # 自訂例外
├── Validation/           # 驗證規則
└── Views/                # 匯出模板
```

### 3.2 前端架構

```
frontend/
├── pages/                # 頁面路由
│   ├── admin/           # 管理後台
│   ├── tables/          # 資料表格頁面
│   └── pages/           # 功能頁面
├── components/           # Vue 元件
├── composables/          # 組合式函式
├── stores/               # Pinia 狀態管理
├── middleware/           # 路由中介層
└── layouts/              # 版面配置
```

### 3.3 資料庫關係

```
companies (企業)
    └── 1:N → urban_renewals (更新會)
                  └── 1:N → land_plots (地號)
                  └── 1:N → buildings (建號)
                  └── 1:N → joint_common_areas (共同區域)
                  └── 1:N → property_owners (所有權人)
                  │              └── N:M → owner_building_ownerships
                  │              └── N:M → owner_land_ownerships
                  └── 1:N → meetings (會議)
                               └── 1:N → meeting_attendances (出席)
                               └── 1:N → meeting_eligible_voters (合格投票人快照)
                               └── 1:N → voting_topics (投票議題)
                                            └── 1:N → voting_records (投票記錄)
```

---

## 4. 不合理之處與改善建議

### 4.1 🔴 嚴重問題

#### 問題 1：商城功能未完整實作

**現況：**
- 前端購物頁面 (`/pages/shopping`) 已實作 UI
- 購物車使用 SessionStorage 暫存
- 結帳頁面 (`/checkout`) 有基本框架
- **但後端完全沒有訂單相關的 API 和資料表**

**不合理之處：**
```javascript
// frontend/pages/tables/order.vue
// TODO: 從 API 取得訂單資料
// 目前使用模擬資料
const orders = ref([])  // 空陣列
```

**建議改善：**
1. 新增 `orders` 資料表
2. 新增 `order_items` 資料表
3. 實作訂單相關 API（建立/查詢/更新）
4. 整合金流服務（如藍新、綠界）

---

#### 問題 2：企業與更新會關係文檔與實作不一致

**文檔描述（RELATIONSHIP_ARCHITECTURE.md）：**
```
企業與更新會是「一對一」關係
每個都市更新會對應一個企業
```

**實際實作（COMPANY_MANAGERS_RENEWALS_ARCHITECTURE.md）：**
```
系統已從「一對一」升級為「一對多」關係
一個企業可管理多個更新會
```

**不合理之處：**
- 兩份文檔存在矛盾描述
- 可能導致新開發者混淆
- 部分程式碼仍使用舊架構（如 `urban_renewal_id` 兼容處理）

**建議改善：**
1. 統一文檔描述，標註已廢棄的內容
2. 移除過渡期相容程式碼
3. 清理 `AuthorizationService` 中的舊邏輯：
```php
// 過渡期兼容：從 urban_renewal_id 推導 company_id
if (!empty($user['urban_renewal_id'])) {
    // 這段程式碼應該被移除
}
```

---

#### 問題 3：投票權限檢查邏輯混亂

**現況（VotingController.php）：**
```php
// admin、chairman、企業管理者可以代理投票
if (!$isAdmin && !$isChairman && !$isCompanyManager) {
    // 一般會員只能為自己投票
    if ($user['role'] === 'member') {
        if (($user['property_owner_id'] ?? null) !== $data['property_owner_id']) {
            return $this->failForbidden('只能為自己投票');
        }
    }
}
```

**不合理之處：**
1. `property_owner_id` 欄位在 `users` 表中是可選的，未必每個使用者都有
2. 使用者可能對應多個所有權人（共同持有）
3. Chairman 代理投票的邏輯沒有限制範圍

**建議改善：**
1. 建立 `user_property_owner_mappings` 關聯表
2. 明確定義代理投票的權限邊界
3. 新增代理投票紀錄欄位（記錄誰代替誰投票）

---

### 4.2 🟡 中度問題

#### 問題 4：會議合格投票人快照包含所有所有權人

**現況（MeetingEligibleVoterModel.php）：**
```php
public function createSnapshot(int $meetingId, int $urbanRenewalId): array
{
    // 取得該更新會所有的所有權人（不論 exclusion_type）
    $eligibleOwners = $propertyOwnerModel
        ->where('urban_renewal_id', $urbanRenewalId)
        ->findAll();
    // ...
}
```

**不合理之處：**
- 快照包含了有 `exclusion_type` 的所有權人（法院查封、假扣押等）
- 這些所有權人理論上不應具有投票權
- 與欄位設計目的矛盾

**建議改善：**
```php
$eligibleOwners = $propertyOwnerModel
    ->where('urban_renewal_id', $urbanRenewalId)
    ->where('exclusion_type IS NULL')  // 排除被限制的所有權人
    ->findAll();
```

---

#### 問題 5：前端 Middleware 狀態恢復邏輯重複

**現況（company-manager.js）：**
```javascript
// 如果 store 沒有 token，嘗試從 localStorage 或 sessionStorage 讀取
if (!authStore.token && process.client) {
    const persistedAuth = localStorage.getItem('auth') || sessionStorage.getItem('auth')
    // ... 手動恢復狀態
}
```

**不合理之處：**
1. 認證系統已改用 HttpOnly Cookies，前端不應存取 token
2. 此邏輯與 `auth.js` store 的設計矛盾
3. `authStore.token` 應該不存在，因為已移除前端 token 存儲

**建議改善：**
1. 移除 `company-manager.js` 中過時的 token 恢復邏輯
2. 統一使用 `authStore.fetchUser()` 驗證登入狀態
3. 清理 localStorage/sessionStorage 中的舊資料

---

#### 問題 6：投票權重計算可能造成精度問題

**現況：**
```php
protected $allowedFields = [
    'land_area_weight',      // decimal
    'building_area_weight',  // decimal
];
```

**不合理之處：**
- 使用 `decimal` 類型存儲面積，但未指定精度
- 面積計算涉及持分比例（分子/分母），可能產生無限小數
- 可能導致投票結果統計誤差

**建議改善：**
1. 明確定義 `DECIMAL(20,10)` 精度
2. 使用 BC Math 函式進行精確計算
3. 在投票結果判定時設定合理的容差範圍

---

#### 問題 7：CORS 設定不一致

**現況（AuthController.php）：**
```php
private function setCorsHeaders()
{
    $allowedOrigins = [
        'https://urban.l',
        'http://localhost:9128',
        'http://localhost:3000'
    ];
    // ...
}
```

**其他 Controller：**
```php
header('Access-Control-Allow-Origin: *');
```

**不合理之處：**
- 不同 Controller 使用不同的 CORS 設定
- 部分使用 `*`（不安全），部分使用白名單
- Cookie 認證需要 `credentials: true`，與 `*` 不相容

**建議改善：**
1. 統一在 Filter 層處理 CORS
2. 建立 `CorsFilter` 集中管理
3. 移除各 Controller 中的重複設定

---

### 4.3 🟢 輕度問題

#### 問題 8：測試頁面殘留在正式環境

**現況：**
```
frontend/pages/
├── test-all-features.vue
├── test-api.vue
├── test-features.vue
├── test-role.vue
├── test-session.vue
├── test-simple.vue
```

**建議改善：**
1. 將測試頁面移至 `tests/` 目錄
2. 或使用環境變數控制顯示
3. 在 production 環境中隱藏路由

---

#### 問題 9：備份檔案殘留

**現況：**
```
backend/app/Database/Migrations/
├── 2025-11-08-000001_RemoveDeletedAtFromPropertyOwnersTable.php.bak
├── 2025-11-08-000002_RemoveDeletedAtFromOwnershipTables.php.bak
```

**建議改善：**
1. 刪除 `.bak` 備份檔案
2. 使用 Git 管理歷史版本
3. 在 `.gitignore` 中排除 `*.bak`

---

#### 問題 10：註解中的 TODO 過多

**現況：**
```javascript
// TODO: Implement cart functionality
// TODO: 從 API 取得訂單資料
// TODO: Replace with actual API call when backend is ready
```

**建議改善：**
1. 建立 Issue 追蹤待辦事項
2. 標註優先級和負責人
3. 定期清理已過時的 TODO

---

### 4.4 📋 改善優先級總表

| 優先級 | 問題 | 影響範圍 | 建議修復時間 |
|--------|------|----------|--------------|
| 🔴 P0 | 商城後端未實作 | 核心功能缺失 | 立即 |
| 🔴 P0 | 投票權限邏輯混亂 | 資料正確性 | 立即 |
| 🔴 P0 | 文檔與實作不一致 | 開發維護 | 1週內 |
| 🟡 P1 | 合格投票人排除邏輯 | 業務正確性 | 2週內 |
| 🟡 P1 | 前端 Middleware 過時邏輯 | 技術債 | 2週內 |
| 🟡 P1 | CORS 設定不一致 | 安全性 | 2週內 |
| 🟡 P2 | 投票權重精度問題 | 資料精確度 | 1個月內 |
| 🟢 P3 | 測試頁面殘留 | 專案整潔 | 隨時 |
| 🟢 P3 | 備份檔案殘留 | 專案整潔 | 隨時 |
| 🟢 P3 | TODO 過多 | 程式碼品質 | 持續 |

---

## 5. 總結

### 5.1 系統優點

1. **完整的都市更新會管理功能**：從所有權人管理到投票計票，功能完整
2. **權重投票機制**：支援人數、土地面積、建物面積三維度計算
3. **企業管理架構**：支援多更新會、成員審核、權限指派
4. **良好的 API 設計**：RESTful 風格，文檔完整
5. **會議快照機制**：確保投票時名單不受後續異動影響
6. **完整的匯入匯出**：支援 Excel 批次處理

### 5.2 待改善項目

1. **商城模組未完成**：前端已有 UI，但後端完全未實作
2. **架構演進造成的技術債**：一對一到一對多的過渡期程式碼
3. **權限控制細節**：代理投票、成員角色的邊界定義
4. **資料正確性**：排除類型所有權人的處理邏輯
5. **程式碼品質**：CORS 設定、Middleware 邏輯需要統一

### 5.3 建議下一步行動

1. **立即**：修復投票合格人排除邏輯
2. **短期**：完成商城後端實作
3. **中期**：清理技術債、統一 CORS 設定
4. **長期**：建立自動化測試、持續改善程式碼品質

---

*報告完成*
