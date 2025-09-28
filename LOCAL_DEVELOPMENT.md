# Local Development Environment Setup

## 概述

此專案提供了一個完整的本地開發環境設定，讓開發者可以在不影響正式站的情況下進行本地開發。

## 環境配置

### 端口分配

| 服務 | 正式環境 | 本地開發環境 |
|------|----------|--------------|
| Frontend | 9128 | 4001 |
| Backend | 9228 | 4002 |
| Database | 9328 | 4306 |
| phpMyAdmin | 9428 | 4003 |

### 資料庫配置

- **正式環境資料庫**: `urban_renewal`
- **本地開發資料庫**: `urban_renewal_dev`
- **本地密碼**: `local_dev_pass`

## 使用方法

### 方法一：使用啟動腳本（推薦）

```bash
./start-dev.sh
```

### 方法二：直接使用 Docker Compose

```bash
docker-compose -f docker-compose.local.yml --env-file .env.local up --build
```

## 檔案結構

```
urban_renewal/
├── .env                     # 正式環境配置（不要修改）
├── .env.local              # 本地開發環境配置
├── docker-compose.yml      # 正式環境 Docker Compose
├── docker-compose.local.yml # 本地開發 Docker Compose
├── backend/.env.local      # 後端本地開發配置
└── start-dev.sh           # 本地開發啟動腳本
```

## 環境變數說明

### .env.local

- `FRONTEND_PORT`: 前端服務端口
- `BACKEND_PORT`: 後端服務端口
- `DB_PORT`: 資料庫端口
- `PHPMYADMIN_PORT`: phpMyAdmin 端口
- `DB_DATABASE`: 本地開發資料庫名稱
- `DB_PASSWORD`: 本地開發資料庫密碼

### backend/.env.local

- 使用本地資料庫連接設定
- 啟用工具列除錯功能
- 設定本地開發日誌級別

## 注意事項

1. **不會影響正式環境**: 所有本地開發設定都使用不同的端口和資料庫
2. **環境隔離**: `.env.local` 文件已加入 `.gitignore`，不會提交到版本控制
3. **資料庫獨立**: 本地開發使用 `urban_renewal_dev` 資料庫，與正式環境完全分離

## 故障排除

### Docker 未運行

```bash
# 確認 Docker 是否正在運行
docker info
```

### 端口衝突

如果遇到端口衝突，可以修改 `.env.local` 中的端口設定：

```bash
# 修改為其他可用端口
FRONTEND_PORT=3011
BACKEND_PORT=3012
PHPMYADMIN_PORT=3013
```

### 資料庫連接問題

確認 `backend/.env.local` 中的資料庫設定與 `.env.local` 一致。

## 停止服務

按 `Ctrl+C` 停止服務，或在另一個終端執行：

```bash
docker-compose -f docker-compose.local.yml down
```