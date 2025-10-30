# E2E 測試實作說明

## 概述

本文件說明登入功能的 E2E（端對端）測試實作詳情。

## 技術選型

### Playwright

我們選擇 Playwright 作為 E2E 測試框架，原因如下：

1. **現代化且功能強大**：支援所有主流瀏覽器（Chromium、Firefox、WebKit）
2. **優秀的開發體驗**：提供 UI 模式、除錯工具、Trace Viewer 等
3. **自動等待機制**：內建智能等待，減少 flaky tests
4. **強大的選擇器**：支援角色、文字、placeholder 等語義化選擇器
5. **官方支援 TypeScript**：完整的類型定義

## 專案架構

```
frontend/
├── e2e/                          # E2E 測試目錄
│   ├── login.spec.ts            # 登入功能測試（18個測試案例）
│   └── README.md                # E2E 測試使用說明
├── playwright.config.ts         # Playwright 配置檔
├── package.json                 # 新增 e2e 測試腳本
└── tests/                       # 單元測試目錄（原有）
    └── composables/
```

## 安裝的套件

```json
{
  "devDependencies": {
    "@playwright/test": "^1.56.1",
    "@playwright/experimental-ct-vue": "^1.56.1",
    "@types/node": "^24.9.1"
  }
}
```

## 新增的 npm 腳本

```json
{
  "scripts": {
    "test:e2e": "playwright test",                    // 執行所有 E2E 測試
    "test:e2e:ui": "playwright test --ui",           // UI 模式（推薦開發使用）
    "test:e2e:headed": "playwright test --headed",   // 有頭模式（可見瀏覽器）
    "test:e2e:debug": "playwright test --debug",     // 除錯模式
    "test:e2e:report": "playwright show-report"      // 查看測試報告
  }
}
```

## 測試涵蓋範圍

### 1. 登入功能測試（13個測試案例）

#### 基本功能
- ✅ 應該顯示登入頁面的所有元素
- ✅ 應該能夠顯示和隱藏密碼
- ✅ 應該能夠輸入帳號和密碼

#### 驗證邏輯
- ✅ 空白欄位不應該提交表單
- ✅ 只填寫帳號不應該提交表單
- ✅ 只填寫密碼不應該提交表單
- ✅ 錯誤的帳號密碼應該顯示錯誤訊息

#### 使用者流程
- ⏭️ 成功登入管理員應該跳轉到更新會管理頁面（需要測試帳號）
- ⏭️ 成功登入主委應該跳轉到會議列表頁面（需要測試帳號）
- ✅ 登入按鈕在處理請求時應該顯示載入狀態
- ✅ 已登入用戶訪問登入頁應該被重定向

#### 鍵盤操作
- ✅ Escape鍵不應該關閉登入頁面
- ✅ Tab鍵應該在表單欄位間正確切換焦點
- ✅ Enter鍵應該提交登入表單

### 2. 登入安全性測試（3個測試案例）

- ✅ 密碼欄位應該有type="password"屬性
- ✅ 密碼不應該被自動填入
- ✅ 表單應該不支援自動完成（如果設定）

### 3. 登入無障礙測試（3個測試案例）

- ✅ 輸入欄位應該有適當的placeholder
- ✅ 登入按鈕應該可以被鍵盤存取
- ✅ 圖示應該有適當的視覺呈現

## 配置說明

### Playwright 配置 (playwright.config.ts)

```typescript
export default defineConfig({
  testDir: './e2e',                    // 測試目錄
  fullyParallel: true,                 // 平行執行測試
  retries: process.env.CI ? 2 : 0,    // CI 環境重試2次
  reporter: 'html',                    // HTML 報告
  use: {
    baseURL: 'http://localhost:4001', // 基礎 URL（與 nuxt.config.ts 一致）
    trace: 'on-first-retry',          // 重試時記錄 trace
    screenshot: 'only-on-failure',     // 失敗時截圖
    video: 'retain-on-failure',       // 失敗時保留錄影
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } }
  ],
  webServer: {
    command: 'npm run dev',            // 自動啟動開發伺服器
    url: 'http://localhost:4001',      // 與 nuxt.config.ts 的 devServer.port 一致
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  },
});
```

**重要**：端口必須與 `nuxt.config.ts` 中的 `devServer.port` 配置一致（本專案使用 4001）。

## 測試程式碼範例

### 測試結構

```typescript
test.describe('登入功能測試', () => {
  // 每個測試前都先導航到登入頁
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
  });

  test('應該顯示登入頁面的所有元素', async ({ page }) => {
    // 檢查標題
    await expect(page.locator('h2')).toContainText('登入');

    // 檢查輸入欄位
    await expect(page.getByPlaceholder('帳號')).toBeVisible();
    await expect(page.getByPlaceholder('密碼')).toBeVisible();

    // 檢查登入按鈕
    await expect(page.getByRole('button', { name: '登入' })).toBeVisible();
  });
});
```

### 使用語義化選擇器

```typescript
// ✅ 推薦：使用角色和可見文字
page.getByRole('button', { name: '登入' })

// ✅ 推薦：使用 placeholder
page.getByPlaceholder('帳號')

// ✅ 推薦：使用文字內容
page.getByText('登入成功')

// ⚠️ 避免：CSS 選擇器（容易因 UI 改變而失效）
page.locator('.login-button')
```

### 智能等待

```typescript
// Playwright 自動等待元素可見
await expect(page.getByText('登入成功')).toBeVisible();

// 自訂 timeout
await expect(page.getByText('載入中...')).toBeVisible({ timeout: 5000 });

// 等待網路閒置
await page.waitForLoadState('networkidle');
```

## 如何執行測試

### 開發模式（推薦）

```bash
# 使用 UI 模式，可視化測試執行過程
npm run test:e2e:ui
```

### 無頭模式（CI/CD）

```bash
# 在背景執行所有測試
npm run test:e2e
```

### 有頭模式（除錯）

```bash
# 顯示瀏覽器視窗
npm run test:e2e:headed
```

### 除錯特定測試

```bash
# 除錯模式，可逐步執行
npm run test:e2e:debug

# 只執行登入測試
npx playwright test e2e/login.spec.ts

# 只執行特定測試案例
npx playwright test -g "應該顯示登入頁面的所有元素"
```

## 測試報告

測試執行後會自動產生 HTML 報告：

```bash
npm run test:e2e:report
```

報告包含：
- 測試結果統計
- 失敗測試的截圖
- 失敗測試的錄影
- Trace 檔案（可用 Trace Viewer 查看）

## 待完成的工作

### 1. 建立測試帳號

目前有兩個測試被標記為 `test.skip()`，因為需要有效的測試帳號：

```typescript
test.skip('成功登入管理員應該跳轉到更新會管理頁面', ...)
test.skip('成功登入主委應該跳轉到會議列表頁面', ...)
```

建議：
- 在測試資料庫中建立專用的測試帳號
- 帳號：`test_admin`、`test_chairman`、`test_member`
- 更新測試程式碼中的帳號密碼
- 移除 `test.skip()`

### 2. 擴充測試範圍

建議新增的測試：
- 登出功能測試
- 會議列表頁面測試
- 投票功能測試
- 簽到功能測試
- Excel 匯出功能測試

### 3. CI/CD 整合

在 GitHub Actions 或其他 CI 工具中執行 E2E 測試：

```yaml
# .github/workflows/e2e.yml
name: E2E Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - run: npm ci
      - run: npx playwright install --with-deps
      - run: npm run test:e2e
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: playwright-report/
```

## 最佳實踐

### 1. 使用 beforeEach 減少重複程式碼

```typescript
test.beforeEach(async ({ page }) => {
  await page.goto('/login');
  await page.waitForLoadState('networkidle');
});
```

### 2. 測試應該獨立且可重複執行

每個測試都應該能夠獨立執行，不依賴其他測試的狀態。

### 3. 使用有意義的測試描述

```typescript
// ✅ 好：清楚描述測試的期望行為
test('錯誤的帳號密碼應該顯示錯誤訊息', ...)

// ❌ 不好：描述不清楚
test('測試登入', ...)
```

### 4. 適當的斷言

```typescript
// ✅ 好：明確的斷言
await expect(page.getByText('登入成功')).toBeVisible();

// ❌ 不好：過於寬鬆的斷言
await expect(page.locator('div')).toBeVisible();
```

## 疑難排解

### 問題：Timed out waiting from config.webServer（最常見）

這是配置端口錯誤導致的問題。

**解決方案**：
1. 確認 `nuxt.config.ts` 中的端口（本專案使用 4001）
2. 確保 `playwright.config.ts` 中的 `baseURL` 和 `webServer.url` 都指向同一端口
3. 檢查端口是否被佔用：`lsof -i :4001`
4. 手動測試開發伺服器：`npm run dev` 然後訪問 `http://localhost:4001`

### 問題：API 請求失敗

**原因**：登入測試需要後端 API

**解決方案**：
1. 確保後端服務正在運行（Docker: `docker-compose up -d`）
2. 檢查 `nuxt.config.ts` 中的 API 代理設定
3. 驗證後端健康狀態：`curl http://localhost:8000/api/health`

### 問題：測試找不到元素

**解決方案**：
1. 檢查選擇器是否正確
2. 增加等待時間：`await expect(element).toBeVisible({ timeout: 10000 })`
3. 使用 UI 模式查看實際的 DOM 結構

### 問題：測試在 CI 中失敗但本地通過

**解決方案**：
1. 檢查是否有時間相關的問題（使用適當的等待）
2. 確保 CI 環境有足夠的資源
3. 增加 timeout 設定

### 問題：開發伺服器無法啟動

**解決方案**：
1. 檢查 port 是否已被佔用
2. 檢查 `playwright.config.ts` 中的 `webServer` 設定
3. 手動啟動開發伺服器再執行測試

## 參考資源

- [Playwright 官方文件](https://playwright.dev/)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Nuxt Testing Guide](https://nuxt.com/docs/getting-started/testing)

## 總結

我們成功為登入功能建立了完整的 E2E 測試框架，涵蓋了：
- ✅ 18 個測試案例（16 個已啟用，2 個等待測試帳號）
- ✅ 功能、安全性、無障礙三大測試面向
- ✅ 完整的測試文件和說明
- ✅ 簡單易用的測試腳本
- ✅ 強大的除錯和報告工具

這個測試框架為未來擴充其他頁面的 E2E 測試奠定了良好的基礎。
