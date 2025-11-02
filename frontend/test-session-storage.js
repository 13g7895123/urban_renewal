#!/usr/bin/env node

/**
 * 測試腳本：檢查登入後 sessionStorage 的實際內容
 * 使用 Playwright 來模擬瀏覽器環境
 */

const { chromium } = require('playwright');

async function testSessionStorage() {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    console.log('🔍 開始測試 sessionStorage...\n');

    // 1. 訪問登入頁面
    console.log('1. 訪問登入頁面...');
    await page.goto('http://localhost:4001/login');
    await page.waitForLoadState('networkidle');
    console.log('✓ 登入頁面載入完成\n');

    // 2. 填寫登入表單（使用測試帳號）
    console.log('2. 填寫登入表單...');
    await page.fill('input[placeholder="帳號"]', 'admin');
    await page.fill('input[placeholder="密碼"]', 'admin');
    console.log('✓ 表單填寫完成\n');

    // 3. 提交登入
    console.log('3. 提交登入...');
    await page.click('button[type="submit"]');

    // 等待導航完成（登入成功後會重定向）
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000); // 額外等待確保狀態已保存
    console.log('✓ 登入請求已提交\n');

    // 4. 檢查 sessionStorage 內容
    console.log('4. 檢查 sessionStorage 內容...');
    const sessionStorageContent = await page.evaluate(() => {
      const storage = {};
      for (let i = 0; i < sessionStorage.length; i++) {
        const key = sessionStorage.key(i);
        const value = sessionStorage.getItem(key);
        storage[key] = value;
      }
      return storage;
    });

    console.log('\n📦 sessionStorage 內容：');
    console.log('================================');
    Object.keys(sessionStorageContent).forEach(key => {
      console.log(`\n鍵名: ${key}`);
      console.log(`值: ${sessionStorageContent[key]}`);

      // 嘗試解析 JSON
      try {
        const parsed = JSON.parse(sessionStorageContent[key]);
        console.log('解析後的 JSON:');
        console.log(JSON.stringify(parsed, null, 2));
      } catch (e) {
        console.log('(不是 JSON 格式)');
      }
    });
    console.log('================================\n');

    // 5. 檢查 Pinia store 狀態
    console.log('5. 檢查 Pinia store 狀態...');
    const authStoreState = await page.evaluate(() => {
      // 嘗試從 window 獲取 Pinia store
      if (window.__NUXT__?.pinia) {
        return window.__NUXT__.pinia;
      }
      return null;
    });

    if (authStoreState) {
      console.log('\n🗂️ Pinia Store 狀態：');
      console.log(JSON.stringify(authStoreState, null, 2));
    } else {
      console.log('⚠️ 無法從 window 獲取 Pinia 狀態');
    }

    // 6. 測試重新整理後的狀態
    console.log('\n6. 測試重新整理...');
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);

    const sessionStorageAfterReload = await page.evaluate(() => {
      const storage = {};
      for (let i = 0; i < sessionStorage.length; i++) {
        const key = sessionStorage.key(i);
        const value = sessionStorage.getItem(key);
        storage[key] = value;
      }
      return storage;
    });

    console.log('\n📦 重新整理後的 sessionStorage 內容：');
    console.log('================================');
    Object.keys(sessionStorageAfterReload).forEach(key => {
      console.log(`\n鍵名: ${key}`);
      console.log(`值: ${sessionStorageAfterReload[key]}`);
    });
    console.log('================================\n');

    // 檢查是否被重定向到登入頁
    const currentUrl = page.url();
    console.log(`\n當前 URL: ${currentUrl}`);
    if (currentUrl.includes('/login')) {
      console.log('❌ 重新整理後被登出了！');
    } else {
      console.log('✓ 重新整理後仍然保持登入狀態');
    }

    // 保持瀏覽器開啟以便檢查
    console.log('\n\n按 Ctrl+C 結束測試...');
    await page.waitForTimeout(60000);

  } catch (error) {
    console.error('\n❌ 測試過程中發生錯誤：');
    console.error(error);
  } finally {
    await browser.close();
  }
}

// 執行測試
testSessionStorage().catch(console.error);
