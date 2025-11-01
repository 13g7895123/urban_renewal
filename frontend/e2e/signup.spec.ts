import { test, expect } from '@playwright/test';

test.describe('註冊功能測試', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/signup');
    await page.waitForLoadState('networkidle');
    console.log('當前完整路徑:', page.url());
  });

  test('應該顯示註冊頁面的所有元素', async ({ page }) => {
    // 檢查標題（在 navbar 的 h1 中）
    await expect(page.locator('h1')).toContainText('註冊');

    // 等待頁面完全載入
    await page.waitForSelector('.step-text', { timeout: 10000 });

    // 檢查步驟進度條
    await expect(page.locator('.step-text').first()).toContainText('選擇帳號類型');
    await expect(page.locator('.step-text').nth(1)).toContainText('填入資料');
    await expect(page.locator('.step-text').nth(2)).toContainText('完成');

    // 檢查帳號類型選擇按鈕
    await expect(page.locator('text=個人帳號')).toBeVisible();
    await expect(page.locator('text=企業帳號')).toBeVisible();

    // 檢查下一步按鈕（應該是停用狀態）
    await expect(page.getByRole('button', { name: '下一步' })).toBeDisabled();
  });

  test('個人帳號成功註冊流程', async ({ page }) => {
    // 生成唯一的測試資料
    const timestamp = Date.now();
    const testUser = {
      account: `testperson${timestamp}`,
      nickname: `測試人員${timestamp}`,
      password: 'Test123456',
      fullName: '測試使用者',
      email: `testperson${timestamp}@example.com`,
      phone: '0912345678',
      lineId: `line${timestamp}`,
      jobTitle: '測試工程師'
    };

    // 等待頁面載入
    await page.waitForSelector('.account-btn', { timeout: 10000 });

    // 步驟 1: 選擇個人帳號（點擊按鈕本身，而不是透過 role）
    await page.locator('.personal-btn').click();

    // 驗證選擇狀態
    await expect(page.locator('.personal-btn')).toHaveClass(/active/);

    // 點擊下一步
    await page.locator('button:has-text("下一步")').click();

    // 步驟 2: 填寫註冊資料
    await expect(page.getByPlaceholder('帳號', { exact: true })).toBeVisible();

    await page.getByPlaceholder('帳號', { exact: true }).fill(testUser.account);
    await page.getByPlaceholder('暱稱').fill(testUser.nickname);
    await page.getByPlaceholder('密碼').first().fill(testUser.password);
    await page.getByPlaceholder('確認密碼').fill(testUser.password);
    await page.getByPlaceholder('姓名').fill(testUser.fullName);
    await page.getByPlaceholder('信箱').fill(testUser.email);
    await page.getByPlaceholder('手機號碼').fill(testUser.phone);
    await page.getByPlaceholder('Line帳號').fill(testUser.lineId);
    await page.getByPlaceholder('職稱').fill(testUser.jobTitle);

    // 驗證輸入值
    await expect(page.getByPlaceholder('帳號', { exact: true })).toHaveValue(testUser.account);
    await expect(page.getByPlaceholder('暱稱')).toHaveValue(testUser.nickname);

    // 點擊註冊按鈕（使用 class 選擇器以避免與 navbar 衝突）
    await page.locator('.register-btn').click();

    // 等待註冊完成（可能需要調整超時時間）
    await page.waitForTimeout(3000);

    // 步驟 3: 驗證註冊完成頁面
    await expect(page.locator('text=註冊完成')).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('button', { name: '前往登入' })).toBeVisible();

    // 點擊前往登入
    await page.getByRole('button', { name: '前往登入' }).click();

    // 驗證導航到登入頁面
    await expect(page).toHaveURL(/\/login/, { timeout: 5000 });
  });

  test('企業帳號成功註冊流程', async ({ page }) => {
    // 生成唯一的測試資料
    const timestamp = Date.now();
    const testBusiness = {
      account: `testbiz${timestamp}`,
      nickname: `企業管理員${timestamp}`,
      password: 'Test123456',
      fullName: '企業負責人',
      email: `testbiz${timestamp}@example.com`,
      phone: '0987654321',
      lineId: `bizline${timestamp}`,
      jobTitle: '執行長',
      businessName: `測試企業${timestamp}`,
      taxId: `${timestamp}`.slice(-8),
      businessPhone: '02-12345678'
    };

    // 等待頁面載入
    await page.waitForSelector('.account-btn', { timeout: 10000 });

    // 步驟 1: 選擇企業帳號
    await page.locator('.business-btn').click();

    // 驗證選擇狀態
    await expect(page.locator('.business-btn')).toHaveClass(/active/);

    // 點擊下一步
    await page.locator('button:has-text("下一步")').click();

    // 步驟 2: 填寫註冊資料
    await expect(page.getByPlaceholder('帳號', { exact: true })).toBeVisible();

    await page.getByPlaceholder('帳號', { exact: true }).fill(testBusiness.account);
    await page.getByPlaceholder('暱稱').fill(testBusiness.nickname);
    await page.getByPlaceholder('密碼').first().fill(testBusiness.password);
    await page.getByPlaceholder('確認密碼').fill(testBusiness.password);
    await page.getByPlaceholder('姓名').fill(testBusiness.fullName);
    await page.getByPlaceholder('信箱').fill(testBusiness.email);
    await page.getByPlaceholder('手機號碼').fill(testBusiness.phone);
    await page.getByPlaceholder('Line帳號').fill(testBusiness.lineId);
    await page.getByPlaceholder('職稱').fill(testBusiness.jobTitle);

    // 企業專屬欄位
    await page.getByPlaceholder('企業名稱').fill(testBusiness.businessName);
    await page.getByPlaceholder('統一編號').fill(testBusiness.taxId);
    await page.getByPlaceholder('企業電話').fill(testBusiness.businessPhone);

    // 驗證企業欄位輸入值
    await expect(page.getByPlaceholder('企業名稱')).toHaveValue(testBusiness.businessName);
    await expect(page.getByPlaceholder('統一編號')).toHaveValue(testBusiness.taxId);

    // 點擊註冊按鈕（使用 class 選擇器以避免與 navbar 衝突）
    await page.locator('.register-btn').click();

    // 等待註冊完成
    await page.waitForTimeout(3000);

    // 步驟 3: 驗證註冊完成頁面
    await expect(page.locator('text=註冊完成')).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('button', { name: '前往登入' })).toBeVisible();

    // 點擊前往登入
    await page.getByRole('button', { name: '前往登入' }).click();

    // 驗證導航到登入頁面
    await expect(page).toHaveURL(/\/login/, { timeout: 5000 });
  });

  test('測試返回上一步功能', async ({ page }) => {
    // 等待頁面載入
    await page.waitForSelector('.account-btn', { timeout: 10000 });

    // 選擇個人帳號並進入下一步
    await page.locator('.personal-btn').click();
    await page.locator('button:has-text("下一步")').click();

    // 驗證已進入步驟 2
    await expect(page.getByPlaceholder('帳號', { exact: true })).toBeVisible();

    // 點擊回上一頁
    await page.locator('button:has-text("回上一頁")').click();

    // 驗證返回步驟 1
    await expect(page.locator('.personal-btn')).toBeVisible();
    await expect(page.locator('.business-btn')).toBeVisible();
  });

  test('驗證步驟切換時進度條的狀態變化', async ({ page }) => {
    // 等待頁面載入
    await page.waitForSelector('.step-item', { timeout: 10000 });

    // 步驟 1 應該是 active
    const step1 = page.locator('.step-item').first();
    await expect(step1).toHaveClass(/active/);

    // 選擇帳號類型並前進
    await page.locator('.personal-btn').click();
    await page.locator('button:has-text("下一步")').click();

    // 步驟 1 應該變為 completed，步驟 2 應該是 active
    await expect(page.locator('.step-item').first()).toHaveClass(/completed/);
    await expect(page.locator('.step-item').nth(1)).toHaveClass(/active/);
  });
});
