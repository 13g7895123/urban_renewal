# 都更計票系統 - 部署指南
# Urban Renewal Voting System - Deployment Guide

## 📋 目錄結構

```
.
├── docker-compose.prod.yml   # 正式環境配置 (Production)
├── docker-compose.dev.yml    # 開發環境配置 (Development)
├── start-prod.sh             # 正式環境啟動腳本
├── stop-prod.sh              # 正式環境停止腳本
├── start-dev.sh              # 開發環境啟動腳本
├── stop-dev.sh               # 開發環境停止腳本
├── .env.example              # 環境變數範例檔案
└── .env                      # 環境變數配置檔案 (需自行建立)
```

## 🚀 快速開始

### 1. 初始設定

```bash
# 複製環境變數範例檔案
cp .env.example .env

# 編輯 .env 檔案，設定您的 Port 配置
# 預設 Port：
#   - Frontend: 4001
#   - Backend: 4002
#   - Database: 4306
#   - phpMyAdmin: 4003
```

### 2. 選擇環境模式

#### 🏢 Production (正式環境)

**適用情境**：完整部署前後端服務

**包含服務**：
- ✅ Frontend (Nuxt.js)
- ✅ Backend (CodeIgniter 4)
- ✅ MariaDB Database
- ✅ phpMyAdmin
- ✅ Cron Jobs

**啟動方式**：
```bash
./start-prod.sh
```

**存取位置**：
- 前端網站: http://localhost:4001
- 後端 API: http://localhost:4002/api
- phpMyAdmin: http://localhost:4003
- 資料庫: localhost:4306

**停止方式**：
```bash
./stop-prod.sh
```

---

#### 💻 Development (開發環境)

**適用情境**：僅部署後端，前端使用 npm run dev

**包含服務**：
- ❌ Frontend (需手動啟動)
- ✅ Backend (CodeIgniter 4)
- ✅ MariaDB Database
- ✅ phpMyAdmin
- ✅ Cron Jobs

**啟動方式**：
```bash
# 1. 啟動後端服務
./start-dev.sh

# 2. 在另一個終端視窗啟動前端
cd frontend
npm install
npm run dev
```

**存取位置**：
- 前端開發: http://localhost:3000 (npm run dev)
- 後端 API: http://localhost:4002/api
- phpMyAdmin: http://localhost:4003
- 資料庫: localhost:4306

**停止方式**：
```bash
# 停止後端服務
./stop-dev.sh

# 前端 Ctrl+C 停止
```

## ⚙️ 環境變數說明

編輯 `.env` 檔案來調整配置：

```bash
# Port 配置
FRONTEND_PORT=4001          # 前端 Port (僅 Production 使用)
BACKEND_PORT=4002           # 後端 Port
DB_PORT=4306                # 資料庫 Port
PHPMYADMIN_PORT=4003        # phpMyAdmin Port

# 後端 URL
BACKEND_API_URL=http://localhost:4002/api
BACKEND_URL=http://localhost:4002

# 資料庫配置
DB_HOST=mariadb
DB_DATABASE=urban_renewal
DB_USERNAME=root
DB_PASSWORD=urban_renewal_pass
```

## 📝 常用指令

### 查看服務狀態

```bash
# Production
docker compose -f docker-compose.prod.yml ps

# Development
docker compose -f docker-compose.dev.yml ps
```

### 查看服務日誌

```bash
# Production - 所有服務
docker compose -f docker-compose.prod.yml logs -f

# Production - 特定服務
docker compose -f docker-compose.prod.yml logs -f backend

# Development - 所有服務
docker compose -f docker-compose.dev.yml logs -f

# Development - 特定服務
docker compose -f docker-compose.dev.yml logs -f backend
```

### 重新建立服務

```bash
# Production
docker compose -f docker-compose.prod.yml up -d --build

# Development
docker compose -f docker-compose.dev.yml up -d --build
```

### 完全清除 (包含資料庫)

```bash
# Production
docker compose -f docker-compose.prod.yml down -v

# Development
docker compose -f docker-compose.dev.yml down -v
```

## 🔧 故障排除

### Port 被佔用

如果遇到 Port 被佔用的錯誤，請：

1. 檢查哪個 Port 被佔用：
   ```bash
   # Linux/Mac
   lsof -i :4002

   # Windows
   netstat -ano | findstr :4002
   ```

2. 修改 `.env` 檔案中的 Port 設定

### 容器無法啟動

```bash
# 檢查 Docker 狀態
docker info

# 清除舊容器
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.dev.yml down

# 重新啟動
./start-prod.sh  # 或 ./start-dev.sh
```

### 資料庫連線失敗

1. 確認資料庫容器正在運行：
   ```bash
   docker ps | grep mariadb
   ```

2. 檢查資料庫日誌：
   ```bash
   docker compose -f docker-compose.prod.yml logs mariadb
   ```

3. 確認 `.env` 中的資料庫密碼正確

## 📦 容器命名規則

### Production
- `urban_renewal_frontend_prod` - 前端容器
- `urban_renewal_backend_prod` - 後端容器
- `urban_renewal_db_prod` - 資料庫容器
- `urban_renewal_phpmyadmin_prod` - phpMyAdmin 容器
- `urban_renewal_cron_prod` - Cron 任務容器

### Development
- `urban_renewal_backend_dev` - 後端容器
- `urban_renewal_db_dev` - 資料庫容器
- `urban_renewal_phpmyadmin_dev` - phpMyAdmin 容器
- `urban_renewal_cron_dev` - Cron 任務容器

## 🎯 最佳實踐

1. **Development 環境**：
   - 使用 `docker-compose.dev.yml`
   - 前端使用 `npm run dev` 獲得熱重載
   - 後端程式碼變更會自動同步到容器

2. **Production 環境**：
   - 使用 `docker-compose.prod.yml`
   - 完整部署所有服務
   - 定期備份資料庫

3. **安全性**：
   - 不要將 `.env` 檔案提交到 Git
   - 使用強密碼設定資料庫
   - 正式環境請修改預設密碼

## 📞 支援

如有問題，請查看：
- [專案文件](./docs/)
- [API 文件](./backend/README.md)
- [前端文件](./frontend/README.md)
