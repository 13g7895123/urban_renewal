# 錯誤記錄系統使用指南

## 概述

系統已經建立了完整的錯誤記錄功能，可以自動捕捉並記錄所有系統錯誤到資料庫。

## 安裝步驟

### 1. 執行資料庫遷移

首先需要建立必要的資料表：

```bash
# 在 Docker 容器內執行
docker exec <backend-container-name> php spark migrate

# 或者進入容器後執行
docker exec -it <backend-container-name> bash
php spark migrate
```

這會建立兩個新表：
- `api_request_logs` - 記錄所有 API 請求和回應
- `error_logs` - 記錄所有系統錯誤和異常

### 2. 驗證安裝

檢查表格是否已建立：

```bash
docker exec <backend-container-name> php spark db:table api_request_logs
docker exec <backend-container-name> php spark db:table error_logs
```

## 功能說明

### 自動錯誤記錄

系統已經在 `app/Config/Events.php` 中註冊了全域錯誤處理器，會自動記錄：

1. **未捕捉的異常 (Uncaught Exceptions)**
   - 包括完整的堆疊追蹤
   - 自動判定嚴重程度
   - 記錄請求上下文

2. **PHP 錯誤 (Errors)**
   - E_ERROR, E_WARNING, E_NOTICE 等
   - 轉換為適當的嚴重等級

3. **致命錯誤 (Fatal Errors)**
   - 使用 shutdown function 捕捉
   - 記錄為 CRITICAL 等級

### 手動記錄錯誤

在程式碼中可以手動記錄特定錯誤：

```php
use App\Libraries\ErrorLogger;

$errorLogger = new ErrorLogger();

// 記錄異常
try {
    // 你的程式碼
} catch (\Exception $e) {
    $errorLogger->logException($e);
    // 或指定嚴重程度
    $errorLogger->logException($e, 'critical');
}

// 記錄一般錯誤
$errorLogger->logError(
    'error',           // 嚴重程度: debug, info, notice, warning, error, critical, alert, emergency
    'Error message',   // 錯誤訊息
    ['key' => 'value'] // 額外資料 (選填)
);

// 記錄驗證錯誤
$errorLogger->logValidationError(['field' => 'error message']);

// 記錄資料庫錯誤
$errorLogger->logDatabaseError($exception);
```

### 嚴重程度等級

系統使用標準的 PSR-3 日誌等級：

- `debug` - 詳細的除錯訊息
- `info` - 一般資訊性訊息
- `notice` - 正常但重要的事件
- `warning` - 警告訊息
- `error` - 執行時錯誤，不需要立即處理
- `critical` - 嚴重情況（如組件不可用、未預期的異常）
- `alert` - 必須立即採取行動
- `emergency` - 系統不可用

## API 端點

### 查詢錯誤記錄

```bash
# 取得所有錯誤記錄（分頁）
GET /api/error-logs
Query: page, limit, severity, start_date, end_date, is_resolved, search

# 取得單一錯誤詳情
GET /api/error-logs/{id}

# 取得未解決的錯誤
GET /api/error-logs/unresolved

# 取得嚴重錯誤
GET /api/error-logs/critical

# 取得統計資料
GET /api/error-logs/statistics
Query: days (預設 7)
```

### 管理錯誤記錄

```bash
# 標記錯誤為已解決
PATCH /api/error-logs/{id}/resolve
Body: {
  "resolution_notes": "修復說明"
}

# 批次解決多個錯誤
POST /api/error-logs/resolve-multiple
Body: {
  "ids": [1, 2, 3],
  "resolution_notes": "修復說明"
}

# 清理舊的已解決錯誤
DELETE /api/error-logs/clean
Query: days (預設 30，清理 N 天前的已解決錯誤)
```

## 使用範例

### 1. 在 Controller 中記錄錯誤

```php
namespace App\Controllers\Api;

use App\Libraries\ErrorLogger;

class PropertyOwnerController extends BaseController
{
    protected $errorLogger;

    public function __construct()
    {
        $this->errorLogger = new ErrorLogger();
    }

    public function create()
    {
        try {
            // 你的業務邏輯
            $data = $this->request->getJSON(true);
            $result = $this->propertyOwnerService->create($data);
            
            return $this->respond($result);
        } catch (\Exception $e) {
            // 自動記錄到資料庫
            $this->errorLogger->logException($e);
            
            return $this->fail($e->getMessage(), 500);
        }
    }
}
```

### 2. 查看錯誤統計

```bash
curl -X GET "http://localhost/api/error-logs/statistics?days=7" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

回應範例：
```json
{
    "total_errors": 45,
    "unresolved_count": 12,
    "resolved_count": 33,
    "critical_count": 3,
    "by_severity": {
        "critical": 3,
        "error": 30,
        "warning": 12
    },
    "recent_errors": [...],
    "most_common_errors": [...]
}
```

### 3. 查看特定錯誤的重複次數

系統會自動計算相同錯誤出現的次數（基於錯誤訊息的 hash 值）：

```bash
curl -X GET "http://localhost/api/error-logs?search=Duplicate%20entry" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. 監控關鍵錯誤

定期查詢未解決的嚴重錯誤：

```bash
# 取得所有未解決的嚴重錯誤
curl -X GET "http://localhost/api/error-logs/critical?is_resolved=0" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 資料表結構

### error_logs 表

| 欄位 | 類型 | 說明 |
|------|------|------|
| id | INT | 主鍵 |
| severity | ENUM | 嚴重程度 |
| message | TEXT | 錯誤訊息 |
| exception_class | VARCHAR | 異常類別名稱 |
| file | VARCHAR | 發生錯誤的檔案 |
| line | INT | 發生錯誤的行號 |
| trace | TEXT | 堆疊追蹤 |
| context | JSON | 額外資料 |
| request_method | VARCHAR | HTTP 方法 |
| request_uri | VARCHAR | 請求 URI |
| request_ip | VARCHAR | 請求 IP |
| request_user_agent | TEXT | User Agent |
| request_data | JSON | 請求資料（敏感資料已遮罩） |
| user_id | INT | 使用者 ID |
| error_hash | VARCHAR | 錯誤識別碼（用於分組相同錯誤） |
| occurrence_count | INT | 發生次數 |
| first_occurrence | DATETIME | 首次發生時間 |
| is_resolved | BOOLEAN | 是否已解決 |
| resolved_at | DATETIME | 解決時間 |
| resolved_by | INT | 解決者 ID |
| resolution_notes | TEXT | 解決說明 |
| created_at | DATETIME | 建立時間 |

### api_request_logs 表

| 欄位 | 類型 | 說明 |
|------|------|------|
| id | INT | 主鍵 |
| method | VARCHAR | HTTP 方法 |
| endpoint | VARCHAR | API 端點 |
| request_headers | JSON | 請求標頭 |
| request_body | JSON | 請求本體 |
| response_status | INT | 回應狀態碼 |
| response_body | JSON | 回應本體 |
| response_time | FLOAT | 回應時間（秒） |
| ip_address | VARCHAR | IP 位址 |
| user_agent | TEXT | User Agent |
| user_id | INT | 使用者 ID |
| created_at | DATETIME | 建立時間 |

## 安全性考量

### 敏感資料遮罩

系統會自動遮罩以下敏感資料：
- `password`
- `token`
- `secret`
- `api_key`
- `credit_card`
- `ssn`

遮罩後顯示為 `[MASKED]`。

### 權限控制

所有錯誤記錄 API 端點都應該限制為管理員才能存取。請在 `app/Config/Filters.php` 中確認已正確設定 JWT 認證和角色檢查。

## 維護建議

### 定期清理

建議定期清理已解決的舊錯誤：

```bash
# 清理 30 天前的已解決錯誤
curl -X DELETE "http://localhost/api/error-logs/clean?days=30" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 監控告警

可以設定定時任務（cron job）來監控嚴重錯誤：

```bash
# 在 crontab 中加入
*/5 * * * * curl -X GET "http://localhost/api/error-logs/critical?is_resolved=0" | mail -s "Critical Errors" admin@example.com
```

### 效能考量

- 錯誤記錄是同步寫入資料庫，可能影響效能
- 如果流量很大，考慮改用佇列系統（Queue）進行非同步寫入
- 定期清理舊記錄以維持查詢效率
- 為常用查詢欄位建立索引（已在 migration 中設定）

## 疑難排解

### 錯誤沒有被記錄

1. 檢查資料表是否已建立：
   ```bash
   php spark db:table error_logs
   ```

2. 檢查 Events.php 是否正確載入：
   ```php
   // 在任何地方加入
   log_message('debug', 'Error logger registered');
   ```

3. 檢查資料庫連線是否正常

### 記錄太多錯誤

如果某個錯誤重複出現，系統會自動增加 `occurrence_count`，不會建立重複記錄。

如果需要忽略特定錯誤，可以在 `ErrorLogger.php` 中加入過濾邏輯。

## 整合建議

### 與通知系統整合

可以在記錄嚴重錯誤時發送通知：

```php
// 在 ErrorLogger.php 的 logError() 方法中
if ($severity === 'critical' || $severity === 'alert' || $severity === 'emergency') {
    // 發送通知給管理員
    $this->sendNotification($data);
}
```

### 與監控系統整合

可以將錯誤資料匯出到監控系統（如 Sentry、New Relic）：

```php
// 在 ErrorLogger.php 中
if (getenv('SENTRY_DSN')) {
    \Sentry\captureException($exception);
}
```

## 總結

這個錯誤記錄系統提供：

✅ 自動捕捉所有未處理的異常  
✅ 記錄完整的請求上下文  
✅ 敏感資料自動遮罩  
✅ 重複錯誤自動分組  
✅ 完整的 REST API 查詢介面  
✅ 錯誤解決狀態追蹤  
✅ 統計分析功能  

現在當你遇到像 "Failed to create property owner" 這類錯誤時，所有資訊都會自動記錄到資料庫中，方便你進行除錯和分析。
