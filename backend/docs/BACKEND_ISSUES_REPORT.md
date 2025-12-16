# 後端欄位缺失與命名不一致問題報告

> 建立日期：2025-12-16
> 狀態：待修復

## 1. 問題概述

在比對 API 文件、前端代碼與後端實體（Entity）後，發現「更新會管理 (Urban Renewal)」模組存在嚴重的欄位不一致與缺失問題。這將導致前端傳送的資料（如理事長姓名、代表人）無法正確儲存至資料庫。

## 2. 詳細問題列表

### 2.1 理事長姓名 (Chairman Name)
- **API 文件要求**：`chairman_name`
- **前端需求**：`chairman_name` (Update API), `chairmanName` (Create API)
- **後端現況**：
  - Entity (`App\Entities\UrbanRenewal`) 中不存在 `chairmanName` 或 `chairman_name` 屬性。
  - 只有 `contactPerson` (對應 `contact_person`)。
- **影響**：前端傳送的 `chairman_name` 無法自動映射至 `contact_person`，導致資料遺失。
- **建議**：
  - 修改 `UrbanRenewalService`，在 `create` 與 `update` 方法中明確將 `chairman_name` 映射至 `contactPerson`。
  - 或者在資料庫與 Entity 中新增 `chairman_name` 欄位（更佳，因為理事長與聯絡人可能是不同人）。

### 2.2 代表人 (Representative)
- **API 文件定義**：`representative` (選填)
- **前端傳送**：`representative` (Update API)
- **後端現況**：
  - Entity (`App\Entities\UrbanRenewal`) 中**完全缺失**此屬性。
  - `UrbanRenewalService` 中亦無處理此欄位的邏輯。
- **影響**：前端傳送的代表人資料將被忽略。
- **建議**：
  - 資料庫 `urban_renewals` 表需新增 `representative` 欄位。
  - Entity 需新增 `$representative` 屬性與 Getter/Setter。
  - Service 需增加對應的儲存邏輯。

### 2.3 欄位對照表

| 欄位名稱 (中文) | API 文件 / 前端期望 | 後端 Entity 現況 | 狀態 |
|----------------|-------------------|------------------|------|
| 理事長姓名 | `chairman_name` | `contactPerson` (可能) | ⚠️ 命名不符 / 用途混淆 |
| 理事長電話 | `chairman_phone` | `phone` (可能) | ⚠️ 命名不符 / 用途混淆 |
| 代表人 | `representative` | (缺失) | ❌ 欄位缺失 |
| 更新會名稱 | `name` | `name` | ✅ 一致 |
| 地址 | `address` | `address` | ✅ 一致 |

## 3. 修復建議步驟

1.  **資料庫遷移 (Migration)**：
    -   新增 `chairman_name`, `chairman_phone`, `representative` 欄位（若希望與 `contact_person` 區隔）。
    -   或是確認 `contact_person` 是否即為理事長，若是，則需在 Service 層做欄位名稱轉換。

2.  **後端程式碼調整**：
    -   更新 `App\Entities\UrbanRenewal` 加入缺失屬性。
    -   更新 `App\Repositories\UrbanRenewalRepository` 的 `hydrate`/`dehydrate` 方法。
    -   更新 `App\Services\UrbanRenewalService` 的 `create`/`update` 方法以接收新欄位。

3.  **前端程式碼調整**（本次即將執行）：
    -   將 `index.vue` 中的 `chairmanName` 修正為 `chairman_name` 以符合 API 文件標準。
