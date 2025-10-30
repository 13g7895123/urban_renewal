# E2E 測試快速開始

## 前置需求

1. **Node.js** - 已安裝（專案已有）
2. **後端服務** - 必須正在運行（用於 API 請求）
3. **Playwright** - 已安裝（執行 `npm install` 會自動安裝）

## 5 分鐘快速開始

### 步驟 1: 確保後端服務運行

```bash
# 如果使用 Docker
cd /home/jarvis/project/bonus/urban_renewal
docker-compose up -d

# 或使用啟動腳本
./start-dev.sh

# 驗證後端運行
curl http://localhost:8000/api/health
```

### 步驟 2: 安裝 Playwright 瀏覽器（首次執行）

```bash
cd frontend
npx playwright install chromium
```

### 步驟 3: 執行測試

```bash
# 使用 UI 模式（推薦！可視化介面）
npm run test:e2e:ui
```

這會開啟 Playwright UI，你可以：
- 👀 看到所有測試案例
- ▶️ 點擊任一測試執行
- 🔍 查看每個步驟的執行過程
- 🐛 即時除錯

### 步驟 4: 瀏覽測試結果

測試執行後會自動顯示結果，包括：
- ✅ 通過的測試（綠色）
- ❌ 失敗的測試（紅色，會有截圖和錄影）
- ⏭️ 跳過的測試（黃色）

## 其他執行方式

### 無頭模式（快速執行）
```bash
npm run test:e2e
```
適合：CI/CD 環境或快速驗證

### 有頭模式（看到瀏覽器）
```bash
npm run test:e2e:headed
```
適合：想看到實際瀏覽器操作過程

### 除錯模式
```bash
npm run test:e2e:debug
```
適合：測試失敗時逐步除錯

### 查看上次測試報告
```bash
npm run test:e2e:report
```

## 常見問題快速解決

### ❌ "Timed out waiting from config.webServer"

**原因**：開發伺服器無法啟動

**解決**：
```bash
# 1. 檢查端口 4001 是否被佔用
lsof -i :4001

# 2. 如果被佔用，停止該程序或使用其他端口
kill -9 <PID>

# 3. 手動測試開發伺服器
npm run dev
# 開啟瀏覽器訪問 http://localhost:4001
```

### ❌ "Connection refused" 或 API 錯誤

**原因**：後端服務未運行

**解決**：
```bash
# 啟動後端服務
cd /home/jarvis/project/bonus/urban_renewal
docker-compose up -d

# 或
./start-dev.sh

# 驗證後端
curl http://localhost:8000/api/health
```

### ❌ 測試找不到元素

**原因**：頁面載入太慢或元素選擇器錯誤

**解決**：
1. 使用 UI 模式檢查實際 DOM 結構
2. 增加等待時間：
```typescript
await expect(element).toBeVisible({ timeout: 10000 });
```

## 測試涵蓋範圍

目前已實作的測試：

✅ **登入功能測試**（13個）
- 頁面元素顯示
- 密碼顯示/隱藏
- 輸入驗證
- 錯誤處理
- 鍵盤操作

✅ **安全性測試**（3個）
- 密碼欄位類型
- 自動填入防護
- 自動完成設定

✅ **無障礙測試**（3個）
- Placeholder
- 鍵盤存取
- 視覺元素

⏭️ **跳過的測試**（2個）
- 需要測試帳號才能啟用

**總計**：18 個測試案例

## 下一步

### 啟用跳過的測試

1. 在測試資料庫建立測試帳號：
   - 管理員：`test_admin` / `admin123`
   - 主委：`test_chairman` / `chairman123`

2. 編輯 `e2e/login.spec.ts`：
   - 找到 `test.skip(...)` 的測試
   - 更新帳號密碼
   - 移除 `.skip`

### 新增更多測試

參考現有測試結構，可以新增：
- 登出功能測試
- 會議列表測試
- 投票功能測試
- 簽到功能測試

## 更多資源

- [完整文件](./README.md) - 詳細的使用說明
- [實作說明](../../docs/E2E_TESTING_IMPLEMENTATION.md) - 技術細節
- [Playwright 官方文件](https://playwright.dev/) - 官方資源

## 需要協助？

如果遇到問題：
1. 查看 [README.md](./README.md) 的「常見問題」章節
2. 使用 UI 模式檢查測試執行過程
3. 查看測試報告中的截圖和錄影
4. 使用 Trace Viewer 回放測試過程

祝測試愉快！ 🎉
