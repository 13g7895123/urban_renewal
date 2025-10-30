# E2E 測試說明

本專案使用 [Playwright](https://playwright.dev/) 進行端對端 (E2E) 測試。

## 測試架構

```
frontend/
├── e2e/                      # E2E 測試目錄
│   ├── login.spec.ts        # 登入功能測試
│   └── README.md            # 本文件
├── playwright.config.ts     # Playwright 配置
└── package.json             # 測試腳本定義
```

## 安裝

Playwright 已經作為開發依賴安裝：

```bash
npm install
```

如果需要安裝瀏覽器，執行：

```bash
npx playwright install
```

## 執行測試

### 基本指令

```bash
# 執行所有 E2E 測試（無頭模式）
npm run test:e2e

# 使用 UI 模式執行測試（推薦）
npm run test:e2e:ui

# 使用有頭模式執行測試（可看到瀏覽器）
npm run test:e2e:headed

# 除錯模式執行測試
npm run test:e2e:debug

# 查看測試報告
npm run test:e2e:report
```

### 進階指令

```bash
# 只執行特定測試檔案
npx playwright test e2e/login.spec.ts

# 只執行特定測試案例
npx playwright test -g "應該顯示登入頁面的所有元素"

# 在特定瀏覽器執行測試
npx playwright test --project=chromium

# 執行測試並產生報告
npx playwright test --reporter=html
```

## 測試檔案說明

### login.spec.ts

登入功能的 E2E 測試，包含以下測試群組：

#### 1. 登入功能測試
- ✅ 顯示登入頁面的所有元素
- ✅ 密碼顯示/隱藏切換
- ✅ 輸入帳號和密碼
- ✅ 空白欄位驗證
- ✅ 錯誤的帳號密碼顯示錯誤訊息
- ✅ 成功登入後的跳轉（需要測試帳號）
- ✅ 登入按鈕載入狀態
- ✅ 鍵盤操作（Tab、Enter）

#### 2. 登入安全性測試
- ✅ 密碼欄位屬性檢查
- ✅ 密碼不自動填入
- ✅ 表單自動完成設定

#### 3. 登入無障礙測試
- ✅ 輸入欄位 placeholder
- ✅ 鍵盤存取性
- ✅ 圖示視覺呈現

## 測試最佳實踐

### 1. 使用有意義的測試描述

```typescript
test('應該顯示登入頁面的所有元素', async ({ page }) => {
  // 測試程式碼
});
```

### 2. 使用 Page Object Model

對於複雜的頁面，建議使用 Page Object Pattern：

```typescript
// pages/login.page.ts
export class LoginPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/login');
  }

  async login(username: string, password: string) {
    await this.page.getByPlaceholder('帳號').fill(username);
    await this.page.getByPlaceholder('密碼').fill(password);
    await this.page.getByRole('button', { name: '登入' }).click();
  }
}
```

### 3. 使用適當的等待策略

```typescript
// 等待網路閒置
await page.waitForLoadState('networkidle');

// 等待特定元素
await expect(page.getByText('登入成功')).toBeVisible();

// 避免使用固定延遲
// ❌ 不好
await page.waitForTimeout(5000);

// ✅ 好
await expect(element).toBeVisible({ timeout: 5000 });
```

### 4. 清理測試資料

```typescript
test.afterEach(async ({ page }) => {
  // 登出或清理 localStorage
  await page.evaluate(() => localStorage.clear());
});
```

## 跳過的測試

某些測試被標記為 `test.skip()`，因為它們需要實際的測試帳號：

```typescript
test.skip('成功登入管理員應該跳轉到更新會管理頁面', async ({ page }) => {
  // 這個測試需要有效的測試帳號
});
```

要啟用這些測試，請：

1. 在測試資料庫中建立測試帳號
2. 更新測試中的帳號密碼
3. 移除 `test.skip()`

## CI/CD 整合

在 CI/CD 環境中執行測試：

```yaml
# .github/workflows/e2e-tests.yml 範例
name: E2E Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '20'
      - name: Install dependencies
        run: npm ci
      - name: Install Playwright browsers
        run: npx playwright install --with-deps
      - name: Run E2E tests
        run: npm run test:e2e
      - name: Upload test report
        uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: playwright-report/
```

## 除錯技巧

### 1. 使用 UI 模式

```bash
npm run test:e2e:ui
```

UI 模式提供視覺化介面，可以逐步執行測試、查看 DOM、網路請求等。

### 2. 使用除錯模式

```bash
npm run test:e2e:debug
```

除錯模式會暫停測試執行，讓你可以逐步執行並檢查狀態。

### 3. 截圖和錄影

測試失敗時會自動截圖和錄影，檔案儲存在 `test-results/` 目錄。

### 4. 使用 Trace Viewer

```bash
npx playwright show-trace test-results/path/to/trace.zip
```

Trace Viewer 可以回放測試執行過程，查看每個步驟的 DOM、網路、Console 等。

## 常見問題

### Q: 測試無法啟動開發伺服器（Timed out waiting from config.webServer）

這是最常見的問題，通常是因為端口配置不正確。

**原因**：
- Nuxt 開發伺服器配置在 `nuxt.config.ts` 中使用 port 4001
- 但 Playwright 配置可能指向錯誤的端口

**解決方案**：

1. 確認 Nuxt 開發伺服器端口（查看 `nuxt.config.ts`）：
```typescript
devServer: {
  port: parseInt(process.env.FRONTEND_PORT || '4001'),
  host: 'localhost'
}
```

2. 確保 `playwright.config.ts` 中的端口一致：
```typescript
webServer: {
  command: 'npm run dev',
  url: 'http://localhost:4001',  // 必須與 nuxt.config.ts 一致
  reuseExistingServer: !process.env.CI,
  timeout: 120 * 1000,
}
```

3. 如果端口被佔用，可以：
   - 停止佔用該端口的程序
   - 或修改 `nuxt.config.ts` 使用不同端口
   - 記得同步更新 `playwright.config.ts`

4. 手動測試開發伺服器是否正常：
```bash
npm run dev
# 開啟瀏覽器訪問 http://localhost:4001
```

### Q: 需要後端 API 嗎？

A: **是的**，登入測試需要後端 API 正常運作。

本專案的前端會透過代理將 `/api` 請求轉發到後端：
```typescript
// nuxt.config.ts
nitro: {
  devProxy: {
    '/api': {
      target: 'http://urban_renewal-backend-1:8000',
      changeOrigin: true,
      prependPath: true,
    }
  }
}
```

**解決方案**：
1. 確保後端服務正在運行
2. 如果使用 Docker，確保容器名稱正確（`urban_renewal-backend-1`）
3. 或者啟動本地後端開發伺服器

**只想測試前端 UI**？
如果只想測試前端 UI（不測試實際登入），可以：
- 移除測試中需要 API 的部分（如錯誤訊息驗證）
- 或使用 mock API 回應

### Q: 測試找不到元素

A: 使用 Playwright 提供的定位器：

```typescript
// ✅ 推薦：使用角色和文字
page.getByRole('button', { name: '登入' })

// ✅ 推薦：使用 placeholder
page.getByPlaceholder('帳號')

// ⚠️ 避免：使用 CSS 選擇器（容易改變）
page.locator('.login-button')
```

### Q: 測試在 CI 中失敗但本地通過

A: 可能是時間問題，增加 timeout 或使用更可靠的等待策略：

```typescript
await expect(element).toBeVisible({ timeout: 10000 });
```

## 參考資源

- [Playwright 官方文件](https://playwright.dev/)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Playwright API Reference](https://playwright.dev/docs/api/class-playwright)
- [Testing Best Practices](https://playwright.dev/docs/best-practices)

## 貢獻

歡迎提交新的測試案例！請確保：

1. 測試描述清晰
2. 遵循現有的測試風格
3. 測試通過後再提交
4. 更新此 README 如果有新的測試類型
