# 依賴檢查工具使用指南

## 快速開始

### 方式一：在本地直接執行檢查腳本
```bash
# 進入 backend 目錄
cd backend

# 執行檢查
bash check-dependencies.sh
```

### 方式二：在 Docker 容器內執行檢查
```bash
# 檢查 backend 容器的依賴
docker exec urban_renewal_backend_dev bash check-dependencies.sh

# 或使用 docker compose
docker compose -f docker-compose.dev.yml exec backend bash check-dependencies.sh
```

## 檢查內容

檢查腳本會驗證以下內容：

### 1️⃣ PHP 版本
- PHP 8.2.x 版本檢查

### 2️⃣ 系統指令
- `git` - 版本控制
- `curl` - HTTP 工具
- `zip/unzip` - 壓縮工具
- `mysql` - 資料庫客戶端
- `composer` - PHP 依賴管理

### 3️⃣ PHP 必要擴展
- `pdo_mysql` - PDO MySQL 驅動
- `mysqli` - MySQLi 驅動
- `mbstring` - 多字節字符串
- `exif` - 圖片信息
- `pcntl` - 進程控制
- `bcmath` - 高精度數學
- `gd` - 圖片處理
- `intl` - 國際化
- `zip` - ZIP 壓縮

### 4️⃣ PHP 建議擴展
- `curl` - HTTP 客戶端
- `openssl` - SSL/TLS 加密
- `dom` - DOM 操作
- `json` - JSON 處理
- `xml` - XML 處理

### 5️⃣ Composer 主要套件
- `codeigniter4/framework` - CodeIgniter 4 框架
- `firebase/php-jwt` - JWT 驗證
- `phpoffice/phpspreadsheet` - Excel 檔案處理
- `phpoffice/phpword` - Word 文件生成
- `phpoffice/math` - 數學公式支援

### 6️⃣ 目錄結構
檢查以下目錄是否存在：
- `vendor/` - Composer 依賴目錄
- `vendor/bin/` - 執行檔目錄
- `app/` - 應用程式代碼
- `public/` - 公開文件
- `writable/` - 可寫目錄
  - `writable/cache/` - 緩存
  - `writable/logs/` - 日誌
  - `writable/session/` - 會話
  - `writable/uploads/` - 上傳文件

### 7️⃣ 檔案權限
- `writable` 目錄是否可寫
- `.env` 檔案是否存在

### 8️⃣ 特定服務檢查
驗證關鍵類是否可用：
- `PhpOffice\PhpWord\TemplateProcessor` - Word 文件生成
- Composer autoloader - 自動加載器
- `Firebase\JWT\JWT` - JWT 驗證

### 9️⃣ 資料庫連線
測試資料庫是否能正常連線

## 結果解釋

### ✓ 通過 (綠色)
表示該依賴已正確安裝

### ✗ 失敗 (紅色)
表示該依賴缺失或未正確安裝，需要修復

### ⚠ 警告 (黃色)
表示該依賴為可選項，但建議安裝

## 常見問題解決

### 1. Composer 套件顯示未安裝

如果檢查顯示 `phpoffice/phpword` 未安裝，但 TemplateProcessor 可用，這是正常的。

**原因**：Docker volume 掛載導致本地 vendor 目錄覆蓋了容器內的完整安裝。

**解決方案**：
```bash
# 在容器內重新安裝
docker exec urban_renewal_backend_dev composer install

# 或重啟容器（會自動執行 docker-entrypoint.sh）
docker compose -f docker-compose.dev.yml restart backend
```

### 2. PHP 擴展缺失

如果某個 PHP 擴展缺失，需要修改 Dockerfile.dev 或重新建置映像：

```bash
# 重新建置 backend 映像
docker compose -f docker-compose.dev.yml build --no-cache backend

# 重啟容器
docker compose -f docker-compose.dev.yml up backend
```

### 3. 資料庫連線失敗

檢查以下事項：
- 資料庫容器是否運行中：`docker ps | grep mariadb`
- 環境變數是否設定正確：檢查 `.env` 檔案
- 資料庫認證信息是否正確

### 4. 目錄不存在或無法寫入

執行以下命令修復權限：
```bash
# 修復 writable 目錄權限
docker exec urban_renewal_backend_dev chmod -R 777 writable
```

## 退出碼 (Exit Code)

- `0` - 所有依賴檢查通過
- `1` - 發現缺失的依賴

## 腳本位置

```
backend/check-dependencies.sh
```

## 修改和維護

如需添加更多檢查項目，編輯 `check-dependencies.sh` 檔案：

1. 添加新的檢查函數
2. 在相應的檢查區段調用該函數
3. 確保計數器 `((PASSED++))` 或 `((FAILED++))` 正確更新

## 自動化使用

可在 CI/CD 流程中使用此腳本：

```bash
#!/bin/bash
# 在 Docker 容器啟動後執行檢查
docker exec urban_renewal_backend_dev bash check-dependencies.sh

# 檢查退出碼
if [ $? -ne 0 ]; then
    echo "依賴檢查失敗！"
    exit 1
fi

echo "所有依賴檢查通過"
```

## 更新依賴

如需更新 Composer 套件：

```bash
# 在容器內更新特定套件
docker exec urban_renewal_backend_dev composer update phpoffice/phpword

# 或更新所有套件
docker exec urban_renewal_backend_dev composer update

# 重新檢查
docker exec urban_renewal_backend_dev bash check-dependencies.sh
```

---

**最後檢查時間**: 2025-11-09
**狀態**: ✓ 所有關鍵依賴已安裝
