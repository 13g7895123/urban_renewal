# 開發工作流程說明

## 開發環境配置 (Development Environment)

### 後端開發 - Volume 掛載模式

開發環境使用 **volume 掛載** 方式，代碼修改立即生效，無需重建容器。

#### 架構說明

**Dockerfile.dev** (開發專用)
- 只複製 composer 相關檔案
- 安裝所有依賴 (包含開發依賴)
- 應用程式碼透過 volume 掛載

**docker-compose.dev.yml** 配置
```yaml
volumes:
  - ./backend:/var/www/html          # 完整專案目錄掛載
  - /var/www/html/vendor             # 保護 vendor 目錄
```

#### 開發優勢

✅ **即時更新** - 程式碼修改後立即生效
✅ **快速迭代** - 無需重建容器
✅ **保留依賴** - vendor 目錄獨立保存
✅ **流暢體驗** - 開發效率大幅提升

#### 使用方式

```bash
# 啟動開發環境
./start-dev.sh

# 修改程式碼
vim backend/app/Controllers/Api/AuthController.php

# 立即測試 (無需重建)
curl http://localhost:9228/api/auth/login

# 停止環境
./stop-dev.sh
```

#### 何時需要重建？

僅在以下情況需要重建容器：
- 修改 Dockerfile.dev
- 更新 composer.json (新增/移除套件)
- 修改 PHP 擴展需求

重建指令：
```bash
docker compose -f docker-compose.dev.yml build backend
docker compose -f docker-compose.dev.yml up -d
```

---

## 正式環境配置 (Production Environment)

正式環境使用 **COPY 模式**，確保部署的穩定性和一致性。

**Dockerfile** (正式環境)
- 完整複製所有程式碼
- 只安裝生產依賴
- 優化效能設定

---

## 測試帳號

開發環境預設包含以下測試帳號：

| 角色 | 帳號 | 密碼 | 權限 |
|-----|------|------|------|
| 管理員 | admin | password | admin |
| 主任委員 | chairman | password | chairman |
| 地主成員 | member1 | password | member |
| 觀察員 | observer1 | password | observer |

---

## 常見問題

### Q: 修改程式碼後沒有生效？
A: 檢查是否在正確的目錄修改，確保修改的是 `./backend` 目錄下的檔案

### Q: 需要安裝新套件怎麼辦？
A: 
1. 修改 `backend/composer.json`
2. 執行 `docker exec urban_renewal_backend_dev composer install`
3. 或重建容器

### Q: Vendor 目錄被覆蓋了？
A: Volume 配置已保護 vendor 目錄，不會被覆蓋

---

更新時間: 2025-10-24
