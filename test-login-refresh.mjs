#!/usr/bin/env node
import pkg from './frontend/node_modules/playwright/index.js';
const { chromium } = pkg;

const testLoginRefresh = async () => {
  const browser = await chromium.launch({
    headless: true,
    args: ['--disable-blink-features=AutomationControlled']
  });

  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    console.log('🔍 開始測試登入和重新整理功能...\n');

    // 1. 訪問登入頁面
    console.log('1. 訪問登入頁面...');
    await page.goto('http://localhost:4001/login', { waitUntil: 'networkidle' });
    console.log('✓ 登入頁面載入完成\n');

    // 2. 填寫登入表單
    console.log('2. 填寫登入表單...');
    await page.waitForSelector('input[type="text"]', { timeout: 10000 });

    // 使用更通用的選擇器
    const usernameInput = await page.locator('input[type="text"]').first();
    const passwordInput = await page.locator('input[type="password"]').first();

    await usernameInput.fill('admin');
    await passwordInput.fill('password');
    console.log('✓ 登入資訊已填寫\n');

    // 3. 點擊登入按鈕
    console.log('3. 點擊登入按鈕...');
    await page.click('button[type="submit"]');

    // 等待導航離開登入頁面 (可能導向到首頁或其他頁面)
    await page.waitForURL((url) => !url.toString().includes('/login'), {
      timeout: 10000,
      waitUntil: 'networkidle'
    });

    const afterLoginUrl = page.url();
    console.log(`✓ 成功登入並導航到: ${afterLoginUrl}\n`);

    // 4. 檢查 sessionStorage
    console.log('4. 檢查 sessionStorage 中的認證資訊...');
    const sessionData = await page.evaluate(() => {
      const authData = sessionStorage.getItem('auth');
      return authData ? JSON.parse(authData) : null;
    });

    if (sessionData && sessionData.token) {
      console.log('✓ sessionStorage 中找到認證資訊');
      console.log(`  - 用戶: ${sessionData.user?.username || 'N/A'}`);
      console.log(`  - Token: ${sessionData.token.substring(0, 20)}...`);
      console.log(`  - 有 Refresh Token: ${!!sessionData.refreshToken}\n`);
    } else {
      console.log('❌ sessionStorage 中沒有找到認證資訊\n');
      throw new Error('登入後 sessionStorage 中沒有認證資訊');
    }

    // 5. 重新整理頁面
    console.log('5. 重新整理頁面...');
    await page.reload({ waitUntil: 'networkidle' });
    console.log('✓ 頁面已重新整理\n');

    // 6. 檢查是否保持登入狀態
    console.log('6. 檢查是否保持登入狀態...');

    // 等待頁面載入完成
    await page.waitForTimeout(2000);

    // 檢查當前 URL
    const currentUrl = page.url();
    console.log(`  - 當前 URL: ${currentUrl}`);

    // 檢查 sessionStorage
    const sessionDataAfterRefresh = await page.evaluate(() => {
      const authData = sessionStorage.getItem('auth');
      return authData ? JSON.parse(authData) : null;
    });

    if (sessionDataAfterRefresh && sessionDataAfterRefresh.token) {
      console.log('✓ 重新整理後 sessionStorage 仍有認證資訊');
      console.log(`  - 用戶: ${sessionDataAfterRefresh.user?.username || 'N/A'}`);
      console.log(`  - Token 仍存在: ${!!sessionDataAfterRefresh.token}\n`);
    } else {
      console.log('❌ 重新整理後 sessionStorage 中的認證資訊遺失\n');
    }

    // 檢查是否被重導向到登入頁面
    if (currentUrl.includes('/login')) {
      console.log('❌ 重新整理後被重導向到登入頁面 - 登入狀態遺失！');
      throw new Error('重新整理後登入狀態遺失');
    } else {
      console.log('✅ 重新整理後保持登入狀態 - 測試通過！');
    }

    // 7. 檢查頁面內容
    console.log('\n7. 檢查頁面內容...');

    // 檢查是否有用戶名顯示
    const userInfo = await page.evaluate(() => {
      const authStore = window.$nuxt?.$pinia?.use?.('auth');
      if (authStore) {
        return {
          user: authStore.user,
          isLoggedIn: authStore.isLoggedIn,
          token: !!authStore.token
        };
      }
      return null;
    });

    if (userInfo) {
      console.log('✓ Pinia store 狀態:');
      console.log(`  - 登入狀態: ${userInfo.isLoggedIn}`);
      console.log(`  - 用戶資訊: ${userInfo.user?.username || 'N/A'}`);
      console.log(`  - Token 存在: ${userInfo.token}`);
    }

  } catch (error) {
    console.error('\n❌ 測試過程中發生錯誤：');
    console.error(error.message);
    process.exit(1);
  } finally {
    await browser.close();
  }

  console.log('\n🎉 所有測試通過！登入和重新整理功能正常運作。');
};

// 執行測試
testLoginRefresh().catch(console.error);