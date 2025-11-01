import { test, expect } from '@playwright/test';

/**
 * 註冊特定帳號：t1101
 * 公司：測試公司001
 */

test.describe('註冊帳號 t1101', () => {

  test('註冊企業帳號 t1101', async ({ page }) => {
    // 帳號資訊
    const accountInfo = {
      account: 't1101',
      nickname: '測試管理員',
      password: 'Test1101!',
      fullName: '測試負責人',
      email: 't1101@test.com',
      phone: '0912345678',
      lineId: 'linet1101',
      jobTitle: '總經理',
      businessName: '測試公司001',
      taxId: '12345678',
      businessPhone: '02-12345678'
    };

    console.log('='.repeat(50));
    console.log('開始註冊帳號：', accountInfo.account);
    console.log('公司名稱：', accountInfo.businessName);
    console.log('='.repeat(50));

    // 訪問註冊頁面
    await page.goto('/signup');
    await page.waitForLoadState('networkidle');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);

    console.log('✓ 已載入註冊頁面');

    // 選擇企業帳號
    const businessBtn = page.locator('button').filter({ hasText: '企業帳號' }).first();
    await businessBtn.waitFor({ state: 'visible', timeout: 15000 });
    await businessBtn.click();
    console.log('✓ 已選擇企業帳號類型');

    // 點擊下一步
    await page.waitForTimeout(500);
    const nextBtn = page.locator('button').filter({ hasText: '下一步' });
    await nextBtn.waitFor({ state: 'visible', timeout: 10000 });
    await nextBtn.click();
    console.log('✓ 進入資料填寫頁面');

    // 等待表單載入
    await page.waitForTimeout(1000);

    // 填寫基本資料
    await page.getByPlaceholder('帳號', { exact: true }).fill(accountInfo.account);
    await page.getByPlaceholder('暱稱').fill(accountInfo.nickname);
    await page.getByPlaceholder('密碼').first().fill(accountInfo.password);
    await page.getByPlaceholder('確認密碼').fill(accountInfo.password);
    await page.getByPlaceholder('姓名').fill(accountInfo.fullName);
    await page.getByPlaceholder('信箱').fill(accountInfo.email);
    await page.getByPlaceholder('手機號碼').fill(accountInfo.phone);
    await page.getByPlaceholder('Line帳號').fill(accountInfo.lineId);
    await page.getByPlaceholder('職稱').fill(accountInfo.jobTitle);
    console.log('✓ 已填寫基本資料');

    // 填寫企業資料
    await page.getByPlaceholder('企業名稱').fill(accountInfo.businessName);
    await page.getByPlaceholder('統一編號').fill(accountInfo.taxId);
    await page.getByPlaceholder('企業電話').fill(accountInfo.businessPhone);
    console.log('✓ 已填寫企業資料');

    // 點擊註冊按鈕
    await page.waitForTimeout(500);
    const registerBtn = page.locator('.register-btn');
    await registerBtn.click();
    console.log('✓ 已提交註冊申請');

    // 等待註冊完成
    await page.waitForTimeout(5000);

    // 檢查註冊結果
    const pageContent = await page.locator('body').textContent();
    const success = pageContent?.includes('註冊完成') || pageContent?.includes('前往登入');

    console.log('='.repeat(50));
    if (success) {
      console.log('✅ 註冊成功！');
      console.log('');
      console.log('帳號資訊：');
      console.log('  帳號：', accountInfo.account);
      console.log('  密碼：', accountInfo.password);
      console.log('  暱稱：', accountInfo.nickname);
      console.log('  公司：', accountInfo.businessName);
      console.log('  信箱：', accountInfo.email);
      console.log('  姓名：', accountInfo.fullName);
      console.log('  職稱：', accountInfo.jobTitle);
      console.log('');
      expect(success).toBe(true);
    } else {
      console.log('❌ 註冊失敗');
      console.log('錯誤訊息：', pageContent?.substring(0, 500));
      throw new Error('註冊失敗');
    }
    console.log('='.repeat(50));
  });
});
