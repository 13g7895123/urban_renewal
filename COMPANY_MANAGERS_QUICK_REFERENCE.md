# 企業管理架構 - 快速參考指南

## 📊 表結構概覽

### users 表
```
企業管理者：user_type='enterprise' AND is_company_manager=1
├─ company_id (必填) → 所屬企業
├─ urban_renewal_id (可選) → 預設工作會
└─ 其他欄位保持不變
```

### companies 表
```
企業表（1對多關係）
├─ id (PK)
├─ name, tax_id, company_phone
├─ max_renewal_count, max_issue_count
└─ 無 urban_renewal_id（已移至 urban_renewals）
```

### urban_renewals 表
```
更新會表
├─ id (PK)
├─ company_id (FK) → 所屬企業
├─ name, chairman_name, chairman_phone
└─ 一個企業可有多個更新會
```

### ✨ company_managers_renewals 表 (新增)
```
企業管理者-更新會關聯表（多對多）
├─ id (PK)
├─ company_id (FK)
├─ manager_id (FK) → users.id
├─ urban_renewal_id (FK)
├─ permission_level: 'full'/'readonly'/'finance'
└─ is_primary: 1/0 (主管理者標記)
```

---

## 🎯 快速使用

### 初始化 Model

```php
use App\Models\CompanyManagerRenewalModel;

$model = new CompanyManagerRenewalModel();
```

### 查詢管理者可訪問的更新會

```php
$renewals = $model->getManagerRenewals($companyId, $managerId);
// 返回該管理者在該企業下的所有授權更新會
```

### 查詢更新會的所有管理者

```php
$managers = $model->getRenewalManagers($companyId, $urbanRenewalId);
// 返回該更新會的所有管理者
```

### 檢查管理者是否有權訪問

```php
if ($model->hasAccess($managerId, $companyId, $renewalId)) {
    // 允許訪問
}
```

### 獲取可訪問的更新會 ID 列表

```php
$renewalIds = $model->getAccessibleRenewalIds($managerId, $companyId);
// 返回 [10, 11, 12] 等 ID 陣列
```

### 授予新權限

```php
$model->grantAccess(
    $companyId,
    $managerId,
    $renewalId,
    'full',    // permission_level: 'full'/'readonly'/'finance'
    false      // is_primary: true/false
);
```

### 撤銷權限

```php
$model->revokeAccess($companyId, $managerId, $renewalId);
```

### 撤銷所有權限

```php
$model->revokeAllAccess($companyId, $managerId);
```

### 檢查是否為主管理者

```php
if ($model->isPrimaryManager($managerId, $companyId, $renewalId)) {
    // 是主管理者
}
```

### 獲取企業的所有管理者

```php
$allManagers = $model->getCompanyManagersWithRenewals($companyId);
// 返回該企業所有管理者及其授權更新會
```

---

## 📋 常見場景

### 場景 1：管理者登入後查詢可訪問的更新會

```php
// 在 LoginController 或 AuthController 中
$user = auth_validate_request();

$model = new CompanyManagerRenewalModel();
$renewals = $model->getManagerRenewals($user['company_id'], $user['id']);

// 前端顯示該管理者可訪問的所有更新會
return response_success('查詢成功', [
    'default_renewal_id' => $user['urban_renewal_id'],
    'accessible_renewals' => $renewals
]);
```

### 場景 2：驗證管理者權限中間件

```php
// 在 Middleware 中
$user = $_SERVER['AUTH_USER'];
$requestedRenewalId = $this->request->getGet('renewal_id');

$model = new CompanyManagerRenewalModel();

if (!$model->hasAccess($user['id'], $user['company_id'], $requestedRenewalId)) {
    return $this->failForbidden('您沒有權限訪問該更新會');
}

// 繼續執行
```

### 場景 3：系統管理員管理权限

```php
// 在 AdminController 中
// 為某個管理者授予新的更新會權限
$success = $model->grantAccess($companyId, $managerId, $newRenewalId, 'full');

if ($success) {
    return response_success('授權成功');
}
```

### 場景 4：查詢權限詳情

```php
// 查詢管理者對某個更新會的具體權限
$permission = $model->where('company_id', $companyId)
                     ->where('manager_id', $managerId)
                     ->where('urban_renewal_id', $renewalId)
                     ->first();

if ($permission) {
    echo "權限等級: " . $permission['permission_level'];
    echo "是否主管理者: " . ($permission['is_primary'] ? '是' : '否');
}
```

---

## 🔍 SQL 查詢範例

### 查詢某管理者在某企業下的所有授權

```sql
SELECT cmr.*, ur.name as renewal_name
FROM company_managers_renewals cmr
JOIN urban_renewals ur ON cmr.urban_renewal_id = ur.id
WHERE cmr.company_id = ? AND cmr.manager_id = ?
ORDER BY cmr.is_primary DESC;
```

### 查詢某企業某更新會的所有管理者

```sql
SELECT cmr.*, u.username, u.email
FROM company_managers_renewals cmr
JOIN users u ON cmr.manager_id = u.id
WHERE cmr.company_id = ? AND cmr.urban_renewal_id = ?
ORDER BY cmr.is_primary DESC;
```

### 查詢某管理者可訪問的所有更新會

```sql
SELECT DISTINCT ur.*
FROM urban_renewals ur
JOIN company_managers_renewals cmr ON ur.id = cmr.urban_renewal_id
WHERE cmr.company_id = ? AND cmr.manager_id = ?;
```

### 檢查管理者對某更新會的權限

```sql
SELECT permission_level, is_primary
FROM company_managers_renewals
WHERE company_id = ? AND manager_id = ? AND urban_renewal_id = ?;
```

### 查詢企業下所有管理者的授權統計

```sql
SELECT 
    u.id, u.username, u.full_name,
    COUNT(cmr.id) as renewal_count,
    GROUP_CONCAT(ur.name) as renewals
FROM users u
JOIN company_managers_renewals cmr ON u.id = cmr.manager_id
JOIN urban_renewals ur ON cmr.urban_renewal_id = ur.id
WHERE cmr.company_id = ?
GROUP BY u.id;
```

---

## ⚙️ 複合索引

| 索引名 | 欄位組合 | 用途 |
|--------|---------|------|
| `unique_manager_renewal` | (company_id, manager_id, urban_renewal_id) | 唯一性約束 + 查詢特定權限 |
| `idx_company_manager` | (company_id, manager_id) | 查詢管理者的授權 |
| `idx_company_renewal` | (company_id, urban_renewal_id) | 查詢更新會的管理者 |
| `idx_manager` | (manager_id) | 查詢管理者的所有授權 |

---

## 🔐 外鍵約束

```
company_managers_renewals
├─ company_id → companies.id [CASCADE DELETE]
├─ manager_id → users.id [CASCADE DELETE]
└─ urban_renewal_id → urban_renewals.id [CASCADE DELETE]
```

級聯刪除規則：
- 刪除企業 → 清理該企業的所有授權記錄
- 刪除管理者 → 清理該管理者的所有授權
- 刪除更新會 → 清理該更新會的所有授權

---

## 📊 資料遷移

### 遷移執行

```bash
docker exec urban_renewal_backend_dev php spark migrate
```

### 遷移詳情

- 遷移1 (120000): 建立 `company_managers_renewals` 表
- 遷移2 (120001): 將現有企業管理者初始化到新表

### 驗證遷移

```bash
docker exec urban_renewal_backend_dev php spark migrate:status
```

預期輸出應顯示：
```
| App | 2025-11-15-120000 | CreateCompanyManagersRenewalsTable      | ✓ |
| App | 2025-11-15-120001 | InitializeCompanyManagersRenewalsData   | ✓ |
```

---

## 🔄 權限等級說明

| 等級 | 代碼 | 說明 |
|------|------|------|
| 完全 | `'full'` | 可執行所有操作 |
| 唯讀 | `'readonly'` | 僅可查看資訊 |
| 財務 | `'finance'` | 僅可操作財務相關功能 |

---

## ✅ 檢查清單

使用新架構時的檢查項目：

- [ ] 確認管理者已關聯到 `companies` 表
- [ ] 在查詢時使用 `CompanyManagerRenewalModel`
- [ ] 驗證 `permission_level` 權限等級
- [ ] 檢查 `is_primary` 標記（是否主管理者）
- [ ] 保留 `users.urban_renewal_id` 作為預設工作會
- [ ] 後端路由中驗證 `company_managers_renewals` 權限
- [ ] 前端顯示管理者可訪問的所有更新會

---

## 🆘 常見問題

**Q: 管理者仍可使用舊的 `urban_renewal_id` 嗎?**  
A: 可以，`urban_renewal_id` 被保留作為「預設工作會」。登入時若設置了此值，會預設進入該會。

**Q: 舊代碼需要改嗎?**  
A: 不需要。新表純粹是擴展，可保留舊代碼運行。推薦逐步遷移到新邏輯。

**Q: 如何查詢管理者的完整授權?**  
A: 使用 `$model->getManagerRenewals($companyId, $managerId);`

**Q: 如何為管理者添加新的更新會?**  
A: 使用 `$model->grantAccess($companyId, $managerId, $newRenewalId, 'full');`

**Q: 級聯刪除會自動清理授權嗎?**  
A: 是的，設置了 CASCADE 規則，刪除關聯方時會自動清理。

---

## 📚 相關文檔

- `COMPANY_MANAGERS_RENEWALS_ARCHITECTURE.md` - 完整架構文檔
- `IMPLEMENTATION_SCHEMA_COMPLETE.md` - 實施完成報告
- `RELATIONSHIP_ARCHITECTURE.md` - 舊的一對一關係說明（供參考）

---

**最後更新**: 2025-11-15  
**版本**: v2.0  
**狀態**: Ready for Production ✅
