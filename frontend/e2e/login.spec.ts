import { test, expect } from '@playwright/test';

test.describe('登入功能測試', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    console.log('當前完整路徑:', page.url());
  });

  test('應該顯示登入頁面的所有元素', async ({ page }) => {
    await expect(page.locator('h2')).toContainText('登入');
    await expect(page.getByPlaceholder('帳號')).toBeVisible();
    await expect(page.getByPlaceholder('密碼')).toBeVisible();
    await expect(page.getByRole('button', { name: '登入' })).toBeVisible();
  });

  test('應該能夠輸入帳號和密碼', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');

    await usernameInput.fill('testuser');
    await expect(usernameInput).toHaveValue('testuser');

    await passwordInput.fill('testpassword');
    await expect(passwordInput).toHaveValue('testpassword');
  });

  test('空白欄位不應該提交表單', async ({ page }) => {
    await page.getByRole('button', { name: '登入' }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('錯誤的帳號密碼應該顯示錯誤訊息', async ({ page }) => {
    await page.getByPlaceholder('帳號').fill('wronguser');
    await page.getByPlaceholder('密碼').fill('wrongpassword');
    await page.getByRole('button', { name: '登入' }).click();
    
    await page.waitForTimeout(1000);
    const errorToast = page.locator('.bg-red-500, [role="alert"]').filter({ hasText: /登入失敗|帳號或密碼錯誤/ });
    await expect(errorToast).toBeVisible({ timeout: 5000 });
  });
});
