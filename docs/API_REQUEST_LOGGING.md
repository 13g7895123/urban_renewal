# API 請求日誌系統 - 使用說明

## 概述

此系統提供完整的 API 請求/回應記錄功能，用於除錯和監控。

## 資料表架構

### 資料表名稱：`api_request_logs`

| 欄位名稱 | 資料型別 | 說明 |
|---------|---------|------|
| id | BIGINT | 主鍵 |
| method | ENUM | HTTP 方法 (GET, POST, PUT, PATCH, DELETE, OPTIONS) |
| endpoint | VARCHAR(500) | API 路徑 |
| request_headers | JSON | 請求標頭（敏感資訊已遮蔽） |
| request_query | JSON | 查詢參數 |
| request_body | JSON | 請求內容（密碼等敏感資訊已遮蔽） |
| response_status | INT | HTTP 狀態碼 (200, 404, 500 等) |
| response_headers | JSON | 回應標頭 |
| response_body | LONGTEXT | 回應內容（JSON 字串） |
| duration_ms | INT | 執行時間（毫秒） |
| error_message | TEXT | 錯誤訊息 |
| user_id | INT | 發送請求的使用者 ID |
| ip_address | VARCHAR(45) | 請求來源 IP 位址 |
| user_agent | VARCHAR(500) | 瀏覽器 User Agent |
| created_at | DATETIME | 建立時間 |

### 索引

- 主鍵：`id`
- 單一索引：`method`, `endpoint`, `response_status`, `user_id`, `ip_address`, `created_at`
- 複合索引：`(endpoint, method)`, `(user_id, created_at)`

## 安裝步驟

### 1. 執行 Migration

```bash
# 如果 Docker 容器正在運行
docker compose -f docker/docker-compose.dev.yml exec backend php spark migrate

# 或者在本機 (需要安裝 PHP)
cd backend
php spark migrate
```

### 2. 設定 Filter（可選）

如果要自動記錄所有 API 請求，在 `app/Config/Filters.php` 中加入：

```php
public array $globals = [
    'before' => [
        // ... 其他 filters
    ],
    'after' => [
        'apiRequestLog' => ['except' => [
            'api/request-logs*', // 避免記錄日誌查詢本身
        ]],
        // ... 其他 filters
    ],
];

public array $aliases = [
    // ... 其他 aliases
    'apiRequestLog' => \App\Filters\ApiRequestLogFilter::class,
];
```

### 3. 設定路由（可選）

在 `app/Config/Routes.php` 中加入日誌查詢路由：

```php
$routes->group('api', function ($routes) {
    // ... 其他路由
    
    $routes->group('request-logs', ['filter' => 'auth'], function ($routes) {
        $routes->get('/', 'Api\ApiRequestLogController::index');
        $routes->get('errors', 'Api\ApiRequestLogController::errors');
        $routes->get('slow', 'Api\ApiRequestLogController::slow');
        $routes->get('statistics', 'Api\ApiRequestLogController::statistics');
        $routes->get('(:num)', 'Api\ApiRequestLogController::show/$1');
        $routes->delete('clean', 'Api\ApiRequestLogController::clean');
    });
});
```

## 使用方式

### 手動記錄日誌

```php
use App\Models\ApiRequestLogModel;

$logModel = new ApiRequestLogModel();
$logModel->logRequest([
    'method' => 'POST',
    'endpoint' => '/api/property-owners',
    'request_body' => $requestData,
    'response_status' => 500,
    'response_body' => json_encode(['error' => 'Failed to create']),
    'duration_ms' => 250,
    'error_message' => 'Database connection failed',
    'user_id' => $userId,
    'ip_address' => $request->getIPAddress(),
]);
```

### 查詢日誌 API

#### 1. 取得日誌列表

```bash
GET /api/request-logs?page=1&per_page=50&endpoint=/api/property-owners&method=POST
```

查詢參數：
- `page`: 頁碼（預設 1）
- `per_page`: 每頁筆數（預設 50）
- `endpoint`: 篩選端點（模糊比對）
- `method`: 篩選 HTTP 方法
- `user_id`: 篩選使用者
- `status`: 篩選狀態碼
- `start_date`: 開始日期
- `end_date`: 結束日期

#### 2. 取得單一日誌詳情

```bash
GET /api/request-logs/123
```

#### 3. 取得錯誤日誌

```bash
GET /api/request-logs/errors?limit=100
```

#### 4. 取得慢速請求

```bash
GET /api/request-logs/slow?min_duration=1000&limit=100
```

#### 5. 取得統計資料

```bash
GET /api/request-logs/statistics?start_date=2026-01-01&end_date=2026-01-31
```

回應範例：
```json
{
  "status": "success",
  "data": {
    "total_requests": 1523,
    "success_requests": 1420,
    "error_requests": 103,
    "avg_duration_ms": 245.5
  }
}
```

#### 6. 清除舊日誌

```bash
DELETE /api/request-logs/clean?days=30
```

### 在程式中查詢日誌

```php
use App\Models\ApiRequestLogModel;

$logModel = new ApiRequestLogModel();

// 取得特定端點的日誌
$logs = $logModel->getByEndpoint('/api/property-owners', 100);

// 取得特定使用者的日誌
$logs = $logModel->getByUser(123, 100);

// 取得錯誤日誌
$errors = $logModel->getErrors(100);

// 取得慢速請求
$slowRequests = $logModel->getSlowRequests(1000, 100);

// 取得統計資料
$stats = $logModel->getStatistics('2026-01-01', '2026-01-31');

// 清除 30 天前的日誌
$deletedCount = $logModel->cleanOldLogs(30);
```

## 除錯您的問題

針對您的 `/api/property-owners` 錯誤，可以：

### 1. 查詢最近的錯誤日誌

```bash
GET /api/request-logs/errors?endpoint=/api/property-owners&limit=10
```

### 2. 查看完整的請求/回應內容

```bash
GET /api/request-logs/{id}
```

這會顯示：
- 完整的請求 payload
- 完整的回應內容
- 錯誤訊息
- 執行時間

### 3. 直接查詢資料庫

```sql
-- 查看最近的錯誤
SELECT 
    id,
    method,
    endpoint,
    request_body,
    response_status,
    response_body,
    error_message,
    duration_ms,
    created_at
FROM api_request_logs
WHERE endpoint LIKE '%property-owners%'
    AND response_status >= 400
ORDER BY created_at DESC
LIMIT 10;
```

## 注意事項

### 安全性
- 敏感欄位（password, token, authorization header 等）會自動遮蔽
- 建議限制日誌查詢 API 僅管理員可存取

### 效能
- 使用 `register_shutdown_function` 非同步記錄，不影響回應速度
- 回應內容超過 1MB 會自動截斷
- 建議定期清理舊日誌（建議保留 30 天）

### 維護
建立 Cron Job 定期清理日誌：

```bash
# 每天凌晨 2 點清理 30 天前的日誌
0 2 * * * cd /path/to/backend && php spark db:clean-logs
```

或建立 Command：

```php
// app/Commands/CleanApiLogs.php
<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use App\Models\ApiRequestLogModel;

class CleanApiLogs extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:clean-logs';
    protected $description = 'Clean old API request logs';

    public function run(array $params)
    {
        $days = $params[0] ?? 30;
        $logModel = new ApiRequestLogModel();
        $deletedCount = $logModel->cleanOldLogs($days);
        
        $this->write("Deleted {$deletedCount} log entries older than {$days} days.", 'green');
    }
}
```

## 疑難排解

如果日誌沒有記錄，請檢查：

1. Migration 是否成功執行
2. Filter 是否正確設定
3. 資料庫連線是否正常
4. 檢查 `writable/logs` 中是否有相關錯誤訊息

## 資料庫索引建議

如果日誌量很大，可以考慮額外建立索引：

```sql
-- 針對時間範圍查詢
CREATE INDEX idx_created_at_status ON api_request_logs(created_at, response_status);

-- 針對端點效能分析
CREATE INDEX idx_endpoint_duration ON api_request_logs(endpoint, duration_ms);
```
