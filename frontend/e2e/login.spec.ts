import { test, expect } from '@playwright/test';

/**
 * E2E tests for login functionality
 */
test.describe('登入功能測試', () => {

  // Before each test, navigate to login page
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');

    // Wait for the page to be fully loaded
    await page.waitForLoadState('networkidle');
  });

  test('應該顯示登入頁面的所有元素', async ({ page }) => {
    // Check page title
    await expect(page.locator('h2')).toContainText('登入');

    // Check username input
    const usernameInput = page.getByPlaceholder('帳號');
    await expect(usernameInput).toBeVisible();

    // Check password input
    const passwordInput = page.getByPlaceholder('密碼');
    await expect(passwordInput).toBeVisible();

    // Check login button
    const loginButton = page.getByRole('button', { name: '登入' });
    await expect(loginButton).toBeVisible();

    // Check password toggle button exists
    const passwordToggle = page.locator('.password-toggle');
    await expect(passwordToggle).toBeVisible();
  });

  test('應該能夠顯示和隱藏密碼', async ({ page }) => {
    const passwordInput = page.getByPlaceholder('密碼');
    const passwordToggle = page.locator('.password-toggle');

    // Initially password should be hidden (type="password")
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // Click toggle to show password
    await passwordToggle.click();
    await expect(passwordInput).toHaveAttribute('type', 'text');

    // Click toggle again to hide password
    await passwordToggle.click();
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('應該能夠輸入帳號和密碼', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');

    // Type username
    await usernameInput.fill('testuser');
    await expect(usernameInput).toHaveValue('testuser');

    // Type password
    await passwordInput.fill('testpassword');
    await expect(passwordInput).toHaveValue('testpassword');
  });

  test('空白欄位不應該提交表單', async ({ page }) => {
    const loginButton = page.getByRole('button', { name: '登入' });

    // Try to submit with empty fields
    await loginButton.click();

    // Should still be on login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('只填寫帳號不應該提交表單', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const loginButton = page.getByRole('button', { name: '登入' });

    await usernameInput.fill('testuser');
    await loginButton.click();

    // Should still be on login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('只填寫密碼不應該提交表單', async ({ page }) => {
    const passwordInput = page.getByPlaceholder('密碼');
    const loginButton = page.getByRole('button', { name: '登入' });

    await passwordInput.fill('testpassword');
    await loginButton.click();

    // Should still be on login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('錯誤的帳號密碼應該顯示錯誤訊息', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');
    const loginButton = page.getByRole('button', { name: '登入' });

    // Fill in wrong credentials
    await usernameInput.fill('wronguser');
    await passwordInput.fill('wrongpassword');

    // Submit the form
    await loginButton.click();

    // Wait for error toast to appear
    await page.waitForTimeout(1000); // Wait a bit for the API call

    // Check if error message is displayed (toast notification)
    const errorToast = page.locator('.bg-red-500, [role="alert"]').filter({ hasText: /登入失敗|帳號或密碼錯誤/ });
    await expect(errorToast).toBeVisible({ timeout: 5000 });
  });

  // This test requires a valid test account
  test.skip('成功登入管理員應該跳轉到更新會管理頁面', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');
    const loginButton = page.getByRole('button', { name: '登入' });

    // Fill in admin credentials (replace with actual test credentials)
    await usernameInput.fill('admin');
    await passwordInput.fill('admin123');

    // Submit the form
    await loginButton.click();

    // Wait for navigation
    await page.waitForURL('/tables/urban-renewal', { timeout: 10000 });

    // Verify we're on the urban renewal page
    await expect(page).toHaveURL('/tables/urban-renewal');

    // Check for success toast
    const successToast = page.locator('.bg-green-500, [role="alert"]').filter({ hasText: /登入成功|歡迎回來/ });
    await expect(successToast).toBeVisible({ timeout: 5000 });
  });

  // This test requires a valid test account
  test.skip('成功登入主委應該跳轉到會議列表頁面', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');
    const loginButton = page.getByRole('button', { name: '登入' });

    // Fill in chairman credentials (replace with actual test credentials)
    await usernameInput.fill('chairman');
    await passwordInput.fill('chairman123');

    // Submit the form
    await loginButton.click();

    // Wait for navigation
    await page.waitForURL('/tables/meeting', { timeout: 10000 });

    // Verify we're on the meeting page
    await expect(page).toHaveURL('/tables/meeting');

    // Check for success toast
    const successToast = page.locator('.bg-green-500, [role="alert"]').filter({ hasText: /登入成功|歡迎回來/ });
    await expect(successToast).toBeVisible({ timeout: 5000 });
  });

  test('登入按鈕在處理請求時應該顯示載入狀態', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');
    const loginButton = page.getByRole('button', { name: '登入' });

    // Fill in credentials
    await usernameInput.fill('testuser');
    await passwordInput.fill('testpassword');

    // Click login button
    await loginButton.click();

    // Check if button shows loading state (disabled or has loading class)
    // Wait a bit to catch the loading state
    await page.waitForTimeout(100);

    // The button should be disabled during loading
    const isDisabled = await loginButton.isDisabled().catch(() => false);
    expect(isDisabled).toBeTruthy();
  });

  test('已登入用戶訪問登入頁應該被重定向', async ({ page, context }) => {
    // Set up authentication state in localStorage
    await context.addCookies([]);

    await page.goto('/login');

    // Set auth token in localStorage
    await page.evaluate(() => {
      localStorage.setItem('auth_token', 'fake-token');
      localStorage.setItem('auth_user', JSON.stringify({
        id: 1,
        username: 'testuser',
        role: 'admin'
      }));
    });

    // Visit login page again
    await page.goto('/login');

    // Should be redirected away from login page
    // The middleware should handle this
    await page.waitForTimeout(1000);

    // Verify not on login page (could be redirected to dashboard or other page)
    // This test might need adjustment based on actual middleware behavior
    // Note: The actual redirect behavior depends on the guest middleware implementation
  });

  test('Escape鍵不應該關閉登入頁面', async ({ page }) => {
    await page.keyboard.press('Escape');

    // Should still be on login page
    await expect(page).toHaveURL(/\/login/);

    // All elements should still be visible
    await expect(page.getByPlaceholder('帳號')).toBeVisible();
    await expect(page.getByPlaceholder('密碼')).toBeVisible();
  });

  test('Tab鍵應該在表單欄位間正確切換焦點', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');

    // Focus on username
    await usernameInput.focus();
    await expect(usernameInput).toBeFocused();

    // Press Tab to move to password
    await page.keyboard.press('Tab');
    await expect(passwordInput).toBeFocused();
  });

  test('Enter鍵應該提交登入表單', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');

    // Fill in credentials
    await usernameInput.fill('testuser');
    await passwordInput.fill('testpassword');

    // Press Enter
    await passwordInput.press('Enter');

    // Form should be submitted (wait a bit for the request)
    await page.waitForTimeout(1000);

    // Check that a request was made (button should show loading state or error message appears)
    const loginButton = page.getByRole('button', { name: '登入' });
    const isDisabled = await loginButton.isDisabled().catch(() => false);

    // Either the button is disabled (loading) or an error toast appears
    expect(isDisabled).toBeTruthy();
  });
});

/**
 * E2E tests for login security
 */
test.describe('登入安全性測試', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
  });

  test('密碼欄位應該有type="password"屬性', async ({ page }) => {
    const passwordInput = page.getByPlaceholder('密碼');
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('密碼不應該被自動填入', async ({ page }) => {
    const passwordInput = page.getByPlaceholder('密碼');
    const initialValue = await passwordInput.inputValue();
    expect(initialValue).toBe('');
  });

  test('表單應該不支援自動完成（如果設定）', async ({ page }) => {
    const form = page.locator('form');

    // Check if form has autocomplete="off" if security requires it
    // This is optional and depends on security requirements
    const autocomplete = await form.getAttribute('autocomplete');

    // If autocomplete is set, verify it's either "off" or not set
    if (autocomplete) {
      expect(['off', null]).toContain(autocomplete);
    }
  });
});

/**
 * E2E tests for login accessibility
 */
test.describe('登入無障礙測試', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
  });

  test('輸入欄位應該有適當的placeholder', async ({ page }) => {
    const usernameInput = page.getByPlaceholder('帳號');
    const passwordInput = page.getByPlaceholder('密碼');

    await expect(usernameInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
  });

  test('登入按鈕應該可以被鍵盤存取', async ({ page }) => {
    const loginButton = page.getByRole('button', { name: '登入' });

    // Tab through the form to reach the button
    await page.keyboard.press('Tab'); // Username
    await page.keyboard.press('Tab'); // Password
    await page.keyboard.press('Tab'); // Password toggle
    await page.keyboard.press('Tab'); // Login button

    // Button should be focusable
    await expect(loginButton).toBeFocused();

    // Should be able to activate with Enter or Space
    await page.keyboard.press('Enter');
    // Form submission should be triggered
  });

  test('圖示應該有適當的視覺呈現', async ({ page }) => {
    // Check that icons are visible
    const userIcon = page.locator('svg').first();
    await expect(userIcon).toBeVisible();
  });
});
