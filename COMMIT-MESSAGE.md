refactor(docker): 整合環境管理並統一啟動流程

## 主要變更

### 1. Docker 檔案結構重組
- 建立 `docker/` 資料夾集中管理所有 Docker 相關檔案
- 移動 docker-compose 檔案並統一命名規範
  - `docker-compose.prod.yml` → `docker/docker-compose.production.yml`
  - `docker-compose.dev.yml` → `docker/docker-compose.dev.yml`
- 移動環境配置檔案到 `docker/` 資料夾
  - `.env.production` → `docker/.env.production`
  - `.env.dev` → `docker/.env.dev`
  - `.env.example` → `docker/.env.example`

### 2. 統一啟動腳本
- 新增 `develop.sh` 統一管理所有環境
  - 支援 `./develop.sh production` 啟動正式環境
  - 支援 `./develop.sh dev` 啟動開發環境（預設）
  - 自動從 `docker/.env.$ENV` 生成 `backend/.env`
  - 自動轉換 Docker 格式到 CodeIgniter 4 格式
- 新增 `stop.sh` 統一停止服務

### 3. 環境配置同步機制
- 在所有環境配置中添加 `ENV` 變數標識
  - `docker/.env.production`: `ENV=production`
  - `docker/.env.dev`: `ENV=development`
- 統一 Debug 等級為 4（全開），包含 production 環境
- 建立 `backend/.env.example` 作為後端配置參考

### 4. 清理舊檔案
- 刪除所有舊的啟動和診斷腳本（11 個）
  - start-prod.sh, start-dev.sh
  - cleanup-restart.sh
  - diagnose-api.sh, diagnose-db.sh
  - fix-db-permissions*.sh
  - fix-phpmyadmin.sh
  - reset-db-password*.sh

### 5. CI/CD 更新
- 更新 `.github/workflows/deploy-prod.yml`
- 使用新的 `./develop.sh production` 部署流程

### 6. Git 配置
- 更新 `.gitignore` 排除自動生成的檔案
  - `docker/.env`（執行時生成）
  - `backend/.env`（執行時生成）
- 保留環境配置模板供版本控制

### 7. 文件更新
- 新增 `README-DEPLOY.md` 部署指南
- 新增 `MIGRATION-SUMMARY.md` 遷移總結

## 影響範圍

- ✅ 所有環境的啟動流程統一
- ✅ 環境配置自動同步到後端
- ✅ Docker 檔案結構更清晰
- ✅ CI/CD 流程保持兼容

## 測試建議

1. 測試開發環境啟動：`./develop.sh dev`
2. 檢查 `backend/.env` 是否正確生成
3. 驗證服務正常運行
4. 測試 CI/CD 部署流程

## Breaking Changes

⚠️ 舊的啟動腳本已移除，請使用新的 `develop.sh` 和 `stop.sh`

## 相關文件

- README-DEPLOY.md - 詳細部署指南
- MIGRATION-SUMMARY.md - 完整遷移報告
