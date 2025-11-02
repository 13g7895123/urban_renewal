#!/usr/bin/env node
import pkg from './frontend/node_modules/playwright/index.js';
const { chromium } = pkg;

const testSessionPage = async () => {
  const browser = await chromium.launch({
    headless: false, // 改為 false 方便觀察
    args: ['--disable-blink-features=AutomationControlled']
  });

  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    console.log('🔍 開始測試 SessionStorage 頁面...\n');

    // 1. 訪問測試頁面
    console.log('1. 訪問測試頁面...');
    await page.goto('http://localhost:4001/test-session', { waitUntil: 'networkidle' });
    console.log('✓ 測試頁面載入完成\n');

    // 2. 等待頁面穩定
    await page.waitForTimeout(2000);

    // 3. 檢查初始狀態
    console.log('2. 檢查初始狀態...');
    const initialSessionData = await page.evaluate(() => {
      return sessionStorage.getItem('auth');
    });
    console.log('初始 sessionStorage:', initialSessionData ? '有資料' : '空的');

    // 4. 點擊測試登入按鈕
    console.log('\n3. 點擊測試登入按鈕...');
    await page.click('button:has-text("測試登入")');

    // 等待登入完成
    await page.waitForTimeout(3000);

    // 5. 再次檢查 sessionStorage
    console.log('\n4. 檢查登入後的 sessionStorage...');
    const afterLoginData = await page.evaluate(() => {
      return sessionStorage.getItem('auth');
    });

    if (afterLoginData) {
      const parsed = JSON.parse(afterLoginData);
      console.log('✓ SessionStorage 已寫入');
      console.log('  - 有 user:', !!parsed.user);
      console.log('  - 有 token:', !!parsed.token);
      console.log('  - 有 refreshToken:', !!parsed.refreshToken);
      console.log('  - 有 tokenExpiresAt:', !!parsed.tokenExpiresAt);
    } else {
      console.log('❌ SessionStorage 仍然是空的');
    }

    // 6. 檢查 Pinia store 狀態
    console.log('\n5. 檢查 Pinia Store 狀態...');
    const storeState = await page.evaluate(() => {
      const authStore = window.$nuxt?.$pinia?.use?.('auth');
      if (authStore) {
        return {
          user: authStore.user,
          hasToken: !!authStore.token,
          isLoggedIn: authStore.isLoggedIn
        };
      }
      return null;
    });

    if (storeState) {
      console.log('Pinia Store 狀態:');
      console.log('  - 用戶:', storeState.user?.username || 'N/A');
      console.log('  - Token 存在:', storeState.hasToken);
      console.log('  - 登入狀態:', storeState.isLoggedIn);
    }

    console.log('\n瀏覽器將保持開啟，請手動檢查...');
    console.log('按 Ctrl+C 結束程式');

    // 保持瀏覽器開啟
    await new Promise(() => {});

  } catch (error) {
    console.error('\n❌ 測試過程中發生錯誤：');
    console.error(error.message);
    await browser.close();
    process.exit(1);
  }
};

// 執行測試
testSessionPage().catch(console.error);