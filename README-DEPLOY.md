# 都更計票系統 - 環境管理指南

## 🚀 快速開始

### 啟動服務

使用統一的 `develop.sh` 腳本管理所有環境：

```bash
# 啟動開發環境（預設）
./develop.sh
./develop.sh dev

# 啟動正式環境
./develop.sh production
```

### 停止服務

```bash
# 停止開發環境
docker compose -f docker/docker-compose.dev.yml down

# 停止正式環境
docker compose -f docker/docker-compose.production.yml down
```

### 查看日誌

```bash
# 開發環境
docker compose -f docker/docker-compose.dev.yml logs -f

# 正式環境
docker compose -f docker/docker-compose.production.yml logs -f
```

---

## 📁 專案結構

```
urban_renewal/
├── docker/                          # Docker 配置目錄
│   ├── .env.production             # 正式環境配置（需手動配置）
│   ├── .env.dev                    # 開發環境配置（需手動配置）
│   ├── .env.example                # 環境配置範例
│   ├── .env                        # 執行時自動生成（gitignore）
│   ├── docker-compose.production.yml
│   └── docker-compose.dev.yml
├── backend/
│   ├── .env                        # 執行時自動生成（gitignore）
│   └── .env.example                # 後端配置範例
├── frontend/
├── develop.sh                      # 統一啟動腳本
└── README-DEPLOY.md               # 本文件
```

---

## ⚙️ 環境配置

### 配置檔案說明

1. **`docker/.env.production`** - 正式環境配置
   - 包含所有正式環境的變數
   - 必須包含 `ENV=production`
   - 需要手動配置資料庫密碼等敏感資訊

2. **`docker/.env.dev`** - 開發環境配置
   - 包含所有開發環境的變數
   - 必須包含 `ENV=development`

3. **`backend/.env`** - 後端配置（自動生成）
   - 由 `develop.sh` 從 `docker/.env` 自動生成
   - 使用 CodeIgniter 4 格式
   - **不要手動編輯此檔案**

### 配置轉換

`develop.sh` 會自動將 Docker 格式轉換為 CodeIgniter 4 格式：

| Docker 格式 | CodeIgniter 4 格式 |
|------------|-------------------|
| `ENV` | `CI_ENVIRONMENT` |
| `DB_HOST` | `database.default.hostname` |
| `DB_DATABASE` | `database.default.database` |
| `DB_USERNAME` | `database.default.username` |
| `DB_PASSWORD` | `database.default.password` |
| `CI_DEBUG` | `CI.debug` |

---

## 🔧 Debug 設定

所有環境的 Debug 等級預設為 **4（全開）**：

```bash
CI_DEBUG=4  # 0=off, 1=error, 2=debug, 3=info, 4=all
```

即使在正式環境也保持全開，以便追蹤問題。

---

## 📝 CI/CD 部署

GitHub Actions 會自動使用新的部署方式：

```yaml
# .github/workflows/deploy-prod.yml
script: |
  cd /home/jarvis/project/bonus/urban_renewal
  git pull origin master
  ./develop.sh production
```

---

## ⚠️ 注意事項

1. **不要直接編輯 `backend/.env`**
   - 此檔案由 `develop.sh` 自動生成
   - 所有配置應在 `docker/.env.production` 或 `docker/.env.dev` 中修改

2. **環境配置檔案必須包含 `ENV` 變數**
   - `docker/.env.production` 必須有 `ENV=production`
   - `docker/.env.dev` 必須有 `ENV=development`

3. **舊的啟動腳本已移除**
   - `start-prod.sh`、`start-dev.sh` 等已刪除
   - 統一使用 `develop.sh`

4. **Docker Compose 檔案位置**
   - 所有 Docker 相關檔案已移到 `docker/` 資料夾
   - 使用相對路徑 `../` 引用專案目錄

---

## 🐛 疑難排解

### 問題：找不到環境配置檔案

```bash
❌ 環境配置檔案不存在: docker/.env.production
```

**解決方式**：
1. 確認 `docker/.env.production` 或 `docker/.env.dev` 存在
2. 可以從 `docker/.env.example` 複製並修改

### 問題：Docker Compose 找不到檔案

```bash
❌ Docker Compose 檔案不存在: docker/docker-compose.production.yml
```

**解決方式**：
1. 確認檔案已移到 `docker/` 資料夾
2. 檔名應為 `docker-compose.production.yml` 或 `docker-compose.dev.yml`

### 問題：後端無法連接資料庫

**解決方式**：
1. 檢查 `docker/.env.production` 中的資料庫配置
2. 確認 `backend/.env` 已正確生成
3. 重新執行 `./develop.sh production`

---

## 📚 相關文件

- [API 規格文件](./docs/API.md)
- [資料庫架構](./docs/DATABASE.md)
- [部署指南](./docs/DEPLOYMENT.md)
