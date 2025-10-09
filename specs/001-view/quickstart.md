# 快速開始指南：都更計票系統

**Feature**: 都更計票系統 - 完整功能規範
**Date**: 2025-10-08
**Target Audience**: 開發人員、測試人員、系統管理員

## 目錄

1. [系統概述](#系統概述)
2. [環境需求](#環境需求)
3. [快速安裝](#快速安裝)
4. [開發環境設定](#開發環境設定)
5. [執行系統](#執行系統)
6. [驗證安裝](#驗證安裝)
7. [常見問題](#常見問題)
8. [下一步](#下一步)

## 系統概述

都更計票系統是一個為都市更新組織設計的完整投票和會議管理系統。系統提供：

- 🏢 **都市更新會管理**: 管理多個都更組織及其所有權人
- 📅 **會議管理**: 建立和管理會員大會、理事會等各類會議
- ✋ **會員報到**: 記錄出席、委託出席和列席狀況
- 🗳️ **投票表決**: 支援多種投票方式和即時統計
- 📊 **報表匯出**: Excel 格式的所有權人和投票結果報表
- 🔐 **權限管理**: 基於角色的使用者權限控制

### 技術堆疊

- **前端**: Nuxt 3 + Vue 3 + Nuxt UI + Tailwind CSS
- **後端**: CodeIgniter 4 (PHP) + MySQL
- **容器化**: Docker + Docker Compose

## 環境需求

### 必要軟體

| 軟體 | 版本要求 | 用途 |
|------|---------|------|
| Docker | 20.10+ | 容器化執行環境 |
| Docker Compose | 2.0+ | 多容器管理 |
| Git | 2.30+ | 版本控制 |
| 文字編輯器 | 任意 | 程式碼編輯（推薦 VS Code）|

### 可選軟體

| 軟體 | 版本要求 | 用途 |
|------|---------|------|
| Node.js | 18+ | 前端本機開發（非必要，Docker 已包含）|
| PHP | 7.4+ | 後端本機開發（非必要，Docker 已包含）|
| MySQL Client | 5.7+ | 資料庫查詢工具 |
| Postman | 最新版 | API 測試工具 |

### 硬體需求

- **最低配置**:
  - CPU: 雙核心 2GHz
  - RAM: 4GB
  - 硬碟空間: 10GB

- **建議配置**:
  - CPU: 四核心 2.5GHz+
  - RAM: 8GB+
  - 硬碟空間: 20GB+
  - SSD 固態硬碟

## 快速安裝

### 方法一：使用 Docker Compose（推薦）

這是最快速且最簡單的方式，適合大多數使用者。

#### 1. Clone 專案

```bash
# Clone 專案到本機
git clone https://github.com/your-org/urban-renewal.git
cd urban-renewal

# 切換到功能分支（如果需要）
git checkout 001-view
```

#### 2. 設定環境變數

```bash
# 複製環境變數範本
cp .env.example .env

# 編輯環境變數（使用您習慣的編輯器）
nano .env  # 或 vim .env 或 code .env
```

**重要環境變數**（`.env` 檔案）:

```env
# 資料庫設定
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=urban_renewal
DB_USERNAME=root
DB_PASSWORD=your_secure_password

# JWT 設定
JWT_SECRET=your_jwt_secret_key_here_at_least_32_characters

# 前端設定
FRONTEND_PORT=4001
FRONTEND_API_URL=http://localhost:4002

# 後端設定
BACKEND_PORT=4002
CI_ENVIRONMENT=development

# MySQL 設定
MYSQL_ROOT_PASSWORD=your_secure_password
MYSQL_DATABASE=urban_renewal
MYSQL_PORT=4306

# phpMyAdmin 設定
PMA_PORT=4003
```

#### 3. 啟動服務

```bash
# 使用 Docker Compose 啟動所有服務
docker-compose up -d

# 查看服務狀態
docker-compose ps

# 查看日誌（可選）
docker-compose logs -f
```

#### 4. 初始化資料庫

```bash
# 進入後端容器
docker-compose exec backend bash

# 執行資料庫遷移
php spark migrate

# 執行資料種子（建立初始資料）
php spark db:seed InitialSetup

# 離開容器
exit
```

#### 5. 驗證安裝

開啟瀏覽器訪問以下網址：

- **前端應用**: http://localhost:4001
- **後端 API**: http://localhost:4002/api
- **phpMyAdmin**: http://localhost:4003

### 方法二：本機安裝（進階）

如果您偏好在本機直接執行而不使用 Docker：

#### 1. 安裝後端

```bash
# 進入後端目錄
cd backend

# 安裝 PHP 依賴
composer install

# 設定環境變數
cp .env.example .env
nano .env  # 編輯資料庫連線等設定

# 執行資料庫遷移
php spark migrate

# 執行資料種子
php spark db:seed InitialSetup

# 啟動開發伺服器
php spark serve --host=0.0.0.0 --port=4002
```

#### 2. 安裝前端

```bash
# 開啟新終端機，進入前端目錄
cd frontend

# 安裝 npm 依賴
npm install

# 設定環境變數
cp .env.example .env
nano .env  # 編輯 API URL 等設定

# 啟動開發伺服器
PORT=4001 npm run dev
```

## 開發環境設定

### Port 配置

系統使用以下 Port 避免與常用 Port 衝突：

| 服務 | Port | URL |
|------|------|-----|
| 前端 | 4001 | http://localhost:4001 |
| 後端 API | 4002 | http://localhost:4002 |
| MySQL | 4306 | localhost:4306 |
| phpMyAdmin | 4003 | http://localhost:4003 |
| Redis（選用）| 6379 | localhost:6379 |

### 資料庫設定

#### 使用 phpMyAdmin 管理資料庫

1. 開啟瀏覽器訪問: http://localhost:4003
2. 登入資訊:
   - 伺服器: `mysql`
   - 使用者名稱: `root`
   - 密碼: 您在 `.env` 中設定的 `MYSQL_ROOT_PASSWORD`
3. 選擇 `urban_renewal` 資料庫即可查看和管理資料

#### 使用 MySQL 命令列

```bash
# 連線到 MySQL（Docker）
docker-compose exec mysql mysql -u root -p

# 連線到 MySQL（本機）
mysql -h 127.0.0.1 -P 4306 -u root -p

# 選擇資料庫
USE urban_renewal;

# 查看所有表格
SHOW TABLES;

# 查看使用者
SELECT * FROM users;
```

### IDE 設定建議

#### Visual Studio Code

推薦安裝以下擴充功能：

```json
{
  "recommendations": [
    "Vue.volar",
    "dbaeumer.vscode-eslint",
    "esbenp.prettier-vscode",
    "bradlc.vscode-tailwindcss",
    "bmewburn.vscode-intelephense-client",
    "junstyle.php-cs-fixer"
  ]
}
```

**設定檔** (`.vscode/settings.json`):

```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "[php]": {
    "editor.defaultFormatter": "junstyle.php-cs-fixer"
  },
  "eslint.validate": [
    "javascript",
    "vue"
  ],
  "tailwindCSS.experimental.classRegex": [
    ["ui:\\s*{([^)]*)\\s*}", "[\"'`]([^\"'`]*).*?[\"'`]"]
  ]
}
```

## 執行系統

### 啟動開發環境

```bash
# 啟動所有服務
docker-compose up -d

# 或者使用提供的啟動腳本（如果有）
./start-dev.sh
```

### 停止開發環境

```bash
# 停止所有服務
docker-compose down

# 停止並刪除 volumes（會清除資料庫資料）
docker-compose down -v
```

### 查看日誌

```bash
# 查看所有服務日誌
docker-compose logs -f

# 查看特定服務日誌
docker-compose logs -f frontend
docker-compose logs -f backend
docker-compose logs -f mysql
```

### 重新建置

```bash
# 重新建置並啟動
docker-compose up -d --build

# 清除舊的映像檔和容器
docker-compose down
docker system prune -a
docker-compose up -d --build
```

## 驗證安裝

### 1. 檢查服務狀態

```bash
# 查看所有容器狀態
docker-compose ps

# 應該看到以下服務都在運行：
# - frontend (Up)
# - backend (Up)
# - mysql (Up)
# - phpmyadmin (Up)
```

### 2. 測試前端

開啟瀏覽器訪問: http://localhost:4001

- ✅ 應該看到登入頁面
- ✅ 頁面樣式正常顯示
- ✅ 無 JavaScript 錯誤（開啟瀏覽器開發者工具檢查）

### 3. 測試後端 API

```bash
# 測試 API 健康檢查（如果有）
curl http://localhost:4002/

# 測試登入 API
curl -X POST http://localhost:4002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# 應該收到包含 token 的 JSON 回應
```

### 4. 測試資料庫連線

```bash
# 進入 MySQL 容器
docker-compose exec mysql mysql -u root -p urban_renewal

# 執行測試查詢
SELECT COUNT(*) FROM users;

# 應該看到至少有一筆使用者資料（從種子建立）
```

### 5. 登入系統

1. 開啟瀏覽器訪問: http://localhost:4001/login
2. 使用預設管理員帳號登入:
   - 帳號: `admin`
   - 密碼: `admin123`（請在生產環境中修改）
3. 成功登入後應該看到系統首頁

## 常見問題

### Q1: Port 被占用

**問題**: 執行 `docker-compose up` 時出現 Port 衝突錯誤

**解決方案**:

```bash
# 檢查哪個程序占用 Port
# Linux/Mac
sudo lsof -i :4001
sudo lsof -i :4002

# Windows
netstat -ano | findstr :4001
netstat -ano | findstr :4002

# 方法1: 停止占用 Port 的程序
kill -9 <PID>  # Linux/Mac
taskkill /PID <PID> /F  # Windows

# 方法2: 修改 docker-compose.yml 中的 Port 映射
# 將 4001:3000 改為 5001:3000 等
```

### Q2: 資料庫連線失敗

**問題**: 後端無法連線到 MySQL

**解決方案**:

```bash
# 1. 確認 MySQL 容器正在運行
docker-compose ps mysql

# 2. 檢查 MySQL 日誌
docker-compose logs mysql

# 3. 檢查環境變數設定
cat backend/.env | grep DB_

# 4. 重新啟動 MySQL
docker-compose restart mysql

# 5. 等待 MySQL 完全啟動（約 30 秒）
docker-compose exec mysql mysqladmin ping -h localhost
```

### Q3: 前端無法連線到後端 API

**問題**: 前端顯示網路錯誤或 CORS 錯誤

**解決方案**:

```bash
# 1. 檢查後端是否正在運行
curl http://localhost:4002/

# 2. 檢查 CORS 設定
cat backend/app/Config/Cors.php

# 3. 確認前端環境變數
cat frontend/.env | grep API

# 4. 檢查後端日誌
docker-compose logs backend

# 5. 重新啟動後端
docker-compose restart backend
```

### Q4: npm install 失敗

**問題**: 執行 `npm install` 時出現錯誤

**解決方案**:

```bash
# 1. 清除 npm 快取
npm cache clean --force

# 2. 刪除 node_modules 和 lock 檔案
rm -rf node_modules package-lock.json

# 3. 重新安裝
npm install

# 4. 如果還是失敗，嘗試使用 --legacy-peer-deps
npm install --legacy-peer-deps
```

### Q5: 資料庫遷移失敗

**問題**: 執行 `php spark migrate` 時出現錯誤

**解決方案**:

```bash
# 1. 檢查資料庫連線
php spark db:table users

# 2. 重置資料庫（警告：會刪除所有資料）
php spark migrate:rollback
php spark migrate

# 3. 如果需要完全重建
docker-compose down -v
docker-compose up -d
# 等待 MySQL 啟動
docker-compose exec backend php spark migrate
docker-compose exec backend php spark db:seed InitialSetup
```

### Q6: Docker 容器無法啟動

**問題**: Docker Compose 無法啟動容器

**解決方案**:

```bash
# 1. 檢查 Docker 服務狀態
docker info

# 2. 檢查磁碟空間
df -h

# 3. 清理未使用的 Docker 資源
docker system prune -a --volumes

# 4. 重新啟動 Docker 服務
# Linux
sudo systemctl restart docker

# Mac/Windows
# 重新啟動 Docker Desktop

# 5. 重新建置和啟動
docker-compose down
docker-compose up -d --build
```

## 下一步

恭喜您已成功設定開發環境！接下來您可以：

### 1. 探索系統功能

- 📖 閱讀 [功能規範](./spec.md) 了解系統所有功能
- 🗺️ 查看 [資料模型](./data-model.md) 了解資料庫結構
- 📋 查看 [API 合約](./contracts/) 了解 API 端點定義

### 2. 開始開發

- 查看 [tasks.md](./tasks.md) 了解待辦任務（需先執行 `/speckit.tasks` 命令生成）
- 閱讀 [專案憲章](../../.specify/spec.constitution) 了解開發規範
- 查看 [CLAUDE.md](../../CLAUDE.md) 了解 Claude Code 使用指引

### 3. 測試和除錯

```bash
# 執行後端測試
docker-compose exec backend composer test

# 執行前端測試
docker-compose exec frontend npm test

# 檢視測試覆蓋率報告
docker-compose exec backend composer test -- --coverage
```

### 4. 常用開發命令

#### 後端開發

```bash
# 建立新的控制器
php spark make:controller Api/ExampleController

# 建立新的模型
php spark make:model ExampleModel

# 建立新的資料庫遷移
php spark make:migration CreateExampleTable

# 執行資料庫遷移
php spark migrate

# 回滾資料庫遷移
php spark migrate:rollback

# 建立資料種子
php spark make:seeder ExampleSeeder

# 執行資料種子
php spark db:seed ExampleSeeder
```

#### 前端開發

```bash
# 建立新的頁面（自動建立檔案）
# 只需在 frontend/pages/ 目錄下建立 .vue 檔案即可

# 建立新的元件
# 在 frontend/components/ 目錄下建立 .vue 檔案

# 建立新的 Composable
# 在 frontend/composables/ 目錄下建立 .js 檔案

# 執行 ESLint 檢查
npm run lint

# 自動修復 ESLint 錯誤
npm run lint:fix

# 建置生產版本
npm run build

# 預覽生產版本
npm run preview
```

### 5. 部署準備

當您準備部署到生產環境時：

1. 閱讀 [plan.md](./plan.md) 中的「部署考量」章節
2. 設定生產環境變數（絕不使用預設密碼）
3. 啟用 HTTPS
4. 設定資料庫備份
5. 配置 Redis 快取
6. 設定 Log Rotation

### 6. 取得幫助

遇到問題？以下資源可以幫助您：

- **專案文件**: `specs/001-view/` 目錄下的所有文件
- **API 文件**: `specs/001-view/contracts/` 目錄
- **專案憲章**: `.specify/spec.constitution`
- **程式碼註解**: 所有程式碼都有正體中文註解
- **Git 提交歷史**: 查看過往的變更和解決方案

## 總結

您現在已經：

- ✅ 安裝並設定好完整的開發環境
- ✅ 了解系統架構和 Port 配置
- ✅ 知道如何啟動、停止和除錯系統
- ✅ 掌握常見問題的解決方法
- ✅ 準備好開始開發

歡迎開始您的都更計票系統開發之旅！

---

**文件版本**: 1.0.0
**最後更新**: 2025-10-08
**維護者**: Urban Renewal Project Team

如有問題或建議，請參考專案文件或聯繫開發團隊。
