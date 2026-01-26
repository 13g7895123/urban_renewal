# OPcache 管理 API 使用說明

## 已新增的 API 端點

### 1. 檢查 OPcache 狀態
```bash
GET /api/admin/opcache/status
```

**回應範例：**
```json
{
  "enabled": true,
  "opcache_enabled": true,
  "cache_full": false,
  "memory_usage": {
    "used_memory": "45.23 MB",
    "free_memory": "82.77 MB",
    "wasted_memory": "0 B",
    "current_wasted_percentage": "0%"
  },
  "statistics": {
    "num_cached_scripts": 1234,
    "opcache_hit_rate": "99.87%",
    "hits": 123456,
    "misses": 123
  },
  "configuration": {
    "validate_timestamps": true,
    "revalidate_freq": 2
  }
}
```

### 2. 清除所有 OPcache
```bash
POST /api/admin/opcache/reset
```

**回應：**
```json
{
  "success": true,
  "message": "OPcache 已成功清除",
  "timestamp": "2026-01-26 14:30:00"
}
```

### 3. 清除系統關鍵檔案的 cache
```bash
POST /api/admin/opcache/reset-system
```

這會清除以下檔案的 cache：
- app/Config/Filters.php
- app/Config/Events.php
- app/Config/Routes.php
- app/Filters/ApiRequestLogFilter.php
- app/Libraries/ErrorLogger.php
- app/Models/ApiRequestLogModel.php
- app/Models/ErrorLogModel.php

**回應：**
```json
{
  "success": true,
  "message": "系統檔案 cache 已清除",
  "files": {
    "app/Config/Filters.php": "cleared",
    "app/Config/Events.php": "cleared",
    "app/Filters/ApiRequestLogFilter.php": "cleared"
  },
  "timestamp": "2026-01-26 14:30:00"
}
```

### 4. 清除特定檔案的 cache
```bash
POST /api/admin/opcache/invalidate
Content-Type: application/json

{
  "file": "app/Controllers/Api/PropertyOwnerController.php"
}
```

### 5. 查看已快取的腳本（前 50 個最常用）
```bash
GET /api/admin/opcache/scripts
```

## 使用流程

### 推送程式碼後檢查 OPcache

1. **檢查狀態：**
   ```bash
   curl -X GET "https://your-domain.com/api/admin/opcache/status" \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

2. **如果日誌系統沒有資料，清除系統檔案 cache：**
   ```bash
   curl -X POST "https://your-domain.com/api/admin/opcache/reset-system" \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

3. **或者清除所有 cache（更徹底）：**
   ```bash
   curl -X POST "https://your-domain.com/api/admin/opcache/reset" \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

4. **再次測試 API，檢查日誌是否開始記錄**

## 已修復的問題

### ✅ 問題2：Duplicate entry 錯誤

**修改內容：**

1. **PropertyOwnerRepository::syncLands()**
   - 停用 Model callbacks（避免在事務中觸發額外操作）
   - 去重處理：確保同一個 land_plot_id 只插入一次
   - 使用 insertBatch 批次插入（效能更好）
   - 在事務完成後才手動更新總面積

2. **PropertyOwnerRepository::syncBuildings()**
   - 相同的邏輯處理建物關聯

3. **確認軟刪除狀態**
   - OwnerLandOwnershipModel: `useSoftDeletes = false` ✓
   - OwnerBuildingOwnershipModel: `useSoftDeletes = false` ✓

**現在的流程：**
```
1. 開始事務
2. 保存 PropertyOwner
3. syncLands() 執行：
   - 停用 callbacks
   - 刪除舊關聯（硬刪除）
   - 去重新資料
   - 批次插入
   - 啟用 callbacks
   - 在 try-catch 中更新總面積（不影響主流程）
4. syncBuildings() 執行（同上）
5. 提交事務
```

## 測試建議

1. **推送程式碼到 VPS**
2. **呼叫 `/api/admin/opcache/reset-system` 清除快取**
3. **觸發 property owner 建立 API**
4. **檢查日誌：**
   - 應該不再出現 "Duplicate entry" 錯誤
   - 如果有重複資料，會在日誌中看到 warning 訊息
   - api_request_logs 和 error_logs 應該開始有資料

5. **查看統計：**
   ```bash
   GET /api/error-logs/statistics
   GET /api/request-logs/statistics
   ```
