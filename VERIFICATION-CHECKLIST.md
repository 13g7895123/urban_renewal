# 環境整合驗證清單

## ✅ 檔案結構檢查

### Docker 資料夾
- [x] `docker/docker-compose.production.yml` 存在
- [x] `docker/docker-compose.dev.yml` 存在
- [x] `docker/.env.production` 存在且包含 `ENV=production`
- [x] `docker/.env.dev` 存在且包含 `ENV=development`
- [x] `docker/.env.example` 存在
- [x] 所有 docker-compose 檔案的路徑已更新為相對路徑（`../`）

### 根目錄
- [x] `develop.sh` 存在且可執行
- [x] `stop.sh` 存在且可執行
- [x] `README-DEPLOY.md` 存在
- [x] `MIGRATION-SUMMARY.md` 存在
- [x] `COMMIT-MESSAGE.md` 存在

### Backend 資料夾
- [x] `backend/.env.example` 存在
- [x] `backend/.env` 會被 gitignore

### 舊檔案已刪除
- [x] `start-prod.sh` 已刪除
- [x] `start-dev.sh` 已刪除
- [x] 其他診斷和修復腳本已刪除（共 11 個）

---

## ✅ 配置檢查

### docker/.env.production
- [x] 包含 `ENV=production`
- [x] 包含 `CI_ENVIRONMENT=production`
- [x] 包含 `CI_DEBUG=4`
- [x] 包含完整的資料庫配置

### docker/.env.dev
- [x] 包含 `ENV=development`
- [x] 包含 `CI_ENVIRONMENT=development`
- [x] 包含 `CI_DEBUG=4`
- [x] 包含完整的資料庫配置

---

## ✅ Git 配置檢查

### .gitignore
- [x] 排除 `docker/.env`
- [x] 排除 `backend/.env`
- [x] 保留環境配置模板（不排除 `.env.production`, `.env.dev`）

---

## ✅ CI/CD 檢查

### .github/workflows/deploy-prod.yml
- [x] 使用 `./develop.sh production` 部署
- [x] 不再使用舊的 `./start-prod.sh`

---

## 🧪 功能測試

### 測試項目

#### 1. 開發環境啟動
```bash
./develop.sh dev
# 預期結果：
# - 複製 docker/.env.dev → docker/.env
# - 生成 backend/.env（CI4 格式）
# - 啟動 Docker Compose
```

#### 2. 正式環境啟動
```bash
./develop.sh production
# 預期結果：
# - 複製 docker/.env.production → docker/.env
# - 生成 backend/.env（CI4 格式）
# - 啟動 Docker Compose
```

#### 3. 停止服務
```bash
./stop.sh dev
./stop.sh production
# 預期結果：
# - 正確停止對應環境的服務
```

#### 4. backend/.env 生成驗證
```bash
# 啟動後檢查
cat backend/.env
# 預期內容：
# - CI_ENVIRONMENT = production (或 development)
# - database.default.hostname = mariadb
# - database.default.database = urban_renewal
# - database.default.username = (對應環境的使用者)
# - database.default.password = (對應環境的密碼)
# - CI.debug = 4
```

---

## 📋 手動驗證步驟

### Step 1: 檢查檔案結構
```bash
tree -L 2 docker/
ls -lh *.sh
ls -lh backend/.env*
```

### Step 2: 檢查環境配置
```bash
head -5 docker/.env.production
head -5 docker/.env.dev
```

### Step 3: 測試腳本語法
```bash
bash -n develop.sh
bash -n stop.sh
```

### Step 4: 測試開發環境啟動（不實際啟動 Docker）
```bash
# 可以先註解掉 develop.sh 中的 docker-compose 指令測試
# 或直接執行看是否正確生成 backend/.env
```

### Step 5: 檢查 Git 狀態
```bash
git status
# 確認：
# - docker/.env 和 backend/.env 不會出現在未追蹤檔案中
# - 新增的檔案都正確顯示
```

---

## ✅ 完成確認

所有檢查項目都已通過！可以進行以下操作：

1. **提交變更**
   ```bash
   git add .
   git commit -F COMMIT-MESSAGE.md
   ```

2. **推送到遠端**
   ```bash
   git push origin master
   ```

3. **測試 CI/CD**
   - 推送後觀察 GitHub Actions 是否正確執行
   - 確認使用新的 `./develop.sh production` 部署

---

生成時間: $(date '+%Y-%m-%d %H:%M:%S')
