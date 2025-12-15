import { test, expect } from '@playwright/test';

/**
 * 註冊功能測試 - 驗證選填欄位
 * 確認 Line ID 和職稱為選填欄位，不填寫也能正常註冊
 */

test.describe('註冊功能 - 選填欄位測試', () => {

    test.beforeEach(async ({ page }) => {
        // 訪問註冊頁面
        await page.goto('/signup');

        // 等待網路請求完成
        await page.waitForLoadState('networkidle');

        // 等待頁面完全載入
        await page.waitForLoadState('domcontentloaded');

        // 額外等待確保 Nuxt hydration 完成
        await page.waitForTimeout(2000);

        console.log('當前完整路徑:', page.url());
    });

    test('應該顯示 Line ID 和職稱欄位標示為選填', async ({ page }) => {
        // 選擇個人帳號
        const personalBtn = page.locator('button').filter({ hasText: '個人帳號' }).first();
        await personalBtn.waitFor({ state: 'visible', timeout: 15000 });
        await personalBtn.click();

        // 點擊下一步進入表單頁面
        await page.waitForTimeout(500);
        const nextBtn = page.locator('button').filter({ hasText: '下一步' });
        await nextBtn.waitFor({ state: 'visible', timeout: 10000 });
        await nextBtn.click();

        // 等待表單載入
        await page.waitForTimeout(1000);

        // 驗證 Line ID 欄位的 placeholder 包含「選填」
        const lineIdInput = page.getByPlaceholder('Line ID (選填)');
        await expect(lineIdInput).toBeVisible({ timeout: 10000 });
        console.log('Line ID 欄位已標示為選填');

        // 驗證職稱欄位的 placeholder 包含「選填」
        const jobTitleInput = page.getByPlaceholder('職稱 (選填)');
        await expect(jobTitleInput).toBeVisible({ timeout: 10000 });
        console.log('職稱欄位已標示為選填');
    });

    test('個人帳號 - 不填寫 Line ID 和職稱應能成功註冊', async ({ page }) => {
        // 生成唯一的測試資料
        const timestamp = Date.now();
        const testUser = {
            account: `opttest${timestamp}`,
            nickname: `選填測試${timestamp}`,
            password: 'Test123456',
            fullName: '選填測試使用者',
            email: `opttest${timestamp}@example.com`,
            phone: '0912345678',
            companyName: '測試公司'
            // 故意不填寫 lineId 和 jobTitle
        };

        console.log('開始測試：不填寫選填欄位的註冊，帳號:', testUser.account);

        // 選擇個人帳號
        const personalBtn = page.locator('button').filter({ hasText: '個人帳號' }).first();
        await personalBtn.waitFor({ state: 'visible', timeout: 15000 });
        await personalBtn.click();
        console.log('已選擇個人帳號');

        // 點擊下一步
        await page.waitForTimeout(500);
        const nextBtn = page.locator('button').filter({ hasText: '下一步' });
        await nextBtn.waitFor({ state: 'visible', timeout: 10000 });
        await nextBtn.click();
        console.log('已進入填寫資料頁面');

        // 等待表單載入
        await page.waitForTimeout(1000);

        // 只填寫必填欄位，不填寫 Line ID 和職稱
        await page.getByPlaceholder('帳號', { exact: true }).fill(testUser.account);
        await page.getByPlaceholder('暱稱').fill(testUser.nickname);
        await page.getByPlaceholder('密碼').first().fill(testUser.password);
        await page.getByPlaceholder('確認密碼').fill(testUser.password);
        await page.getByPlaceholder('姓名').fill(testUser.fullName);
        await page.getByPlaceholder('信箱').fill(testUser.email);
        await page.getByPlaceholder('手機號碼').fill(testUser.phone);
        await page.getByPlaceholder('公司名稱').fill(testUser.companyName);

        // 確認 Line ID 和職稱欄位保持空白
        const lineIdInput = page.getByPlaceholder('Line ID (選填)');
        const jobTitleInput = page.getByPlaceholder('職稱 (選填)');
        await expect(lineIdInput).toHaveValue('');
        await expect(jobTitleInput).toHaveValue('');
        console.log('已填寫必填欄位，Line ID 和職稱保持空白');

        // 點擊註冊按鈕
        await page.waitForTimeout(500);
        const registerBtn = page.locator('.register-btn');
        await registerBtn.click();
        console.log('已提交註冊');

        // 等待註冊完成
        await page.waitForTimeout(5000);

        // 檢查是否成功（檢查是否有「註冊完成」或「前往登入」）
        const pageContent = await page.locator('body').textContent();
        const success = pageContent?.includes('註冊完成') || pageContent?.includes('前往登入');

        console.log('註冊結果:', success ? '成功' : '失敗');

        if (success) {
            // 驗證註冊成功
            await expect(page.locator('body')).toContainText('註冊完成');
            console.log('✅ 不填寫選填欄位的註冊測試通過');
        } else {
            // 如果失敗，檢查是否有錯誤訊息
            console.log('頁面內容:', pageContent?.substring(0, 500));

            // 確認錯誤不是因為 Line ID 或職稱未填寫
            expect(pageContent).not.toContain('Line');
            expect(pageContent).not.toContain('職稱');
        }

        expect(success).toBe(true);
    });

    test('個人帳號 - 填寫所有欄位（包含選填）應能成功註冊', async ({ page }) => {
        // 生成唯一的測試資料
        const timestamp = Date.now();
        const testUser = {
            account: `fulltest${timestamp}`,
            nickname: `完整測試${timestamp}`,
            password: 'Test123456',
            fullName: '完整測試使用者',
            email: `fulltest${timestamp}@example.com`,
            phone: '0912345678',
            companyName: '測試公司',
            lineId: `lineid${timestamp}`,
            jobTitle: '資深工程師'
        };

        console.log('開始測試：填寫所有欄位的註冊，帳號:', testUser.account);

        // 選擇個人帳號
        const personalBtn = page.locator('button').filter({ hasText: '個人帳號' }).first();
        await personalBtn.waitFor({ state: 'visible', timeout: 15000 });
        await personalBtn.click();

        // 點擊下一步
        await page.waitForTimeout(500);
        const nextBtn = page.locator('button').filter({ hasText: '下一步' });
        await nextBtn.waitFor({ state: 'visible', timeout: 10000 });
        await nextBtn.click();

        // 等待表單載入
        await page.waitForTimeout(1000);

        // 填寫所有欄位，包含選填欄位
        await page.getByPlaceholder('帳號', { exact: true }).fill(testUser.account);
        await page.getByPlaceholder('暱稱').fill(testUser.nickname);
        await page.getByPlaceholder('密碼').first().fill(testUser.password);
        await page.getByPlaceholder('確認密碼').fill(testUser.password);
        await page.getByPlaceholder('姓名').fill(testUser.fullName);
        await page.getByPlaceholder('信箱').fill(testUser.email);
        await page.getByPlaceholder('手機號碼').fill(testUser.phone);
        await page.getByPlaceholder('Line ID (選填)').fill(testUser.lineId);
        await page.getByPlaceholder('公司名稱').fill(testUser.companyName);
        await page.getByPlaceholder('職稱 (選填)').fill(testUser.jobTitle);

        console.log('已填寫所有欄位（包含選填）');

        // 點擊註冊按鈕
        await page.waitForTimeout(500);
        const registerBtn = page.locator('.register-btn');
        await registerBtn.click();
        console.log('已提交註冊');

        // 等待註冊完成
        await page.waitForTimeout(5000);

        // 檢查是否成功
        const pageContent = await page.locator('body').textContent();
        const success = pageContent?.includes('註冊完成') || pageContent?.includes('前往登入');

        console.log('註冊結果:', success ? '成功' : '失敗');

        if (success) {
            await expect(page.locator('body')).toContainText('註冊完成');
            console.log('✅ 填寫所有欄位的註冊測試通過');
        } else {
            console.log('頁面內容:', pageContent?.substring(0, 500));
        }

        expect(success).toBe(true);
    });

    test('企業帳號 - 不填寫 Line ID 和職稱應能成功註冊', async ({ page }) => {
        // 生成唯一的測試資料
        const timestamp = Date.now();
        const testBiz = {
            account: `bizopt${timestamp}`,
            nickname: `企業選填${timestamp}`,
            password: 'Test123456',
            fullName: '企業負責人',
            email: `bizopt${timestamp}@example.com`,
            phone: '0987654321',
            companyName: '測試公司',
            businessName: `企業${timestamp}`,
            taxId: `${timestamp}`.slice(-8),
            businessPhone: '02-12345678'
            // 故意不填寫 lineId 和 jobTitle
        };

        console.log('開始測試企業帳號：不填寫選填欄位，帳號:', testBiz.account);

        // 選擇企業帳號
        const businessBtn = page.locator('button').filter({ hasText: '企業帳號' }).first();
        await businessBtn.waitFor({ state: 'visible', timeout: 15000 });
        await businessBtn.click();
        console.log('已選擇企業帳號');

        // 點擊下一步
        await page.waitForTimeout(500);
        const nextBtn = page.locator('button').filter({ hasText: '下一步' });
        await nextBtn.click();
        console.log('已進入填寫資料頁面');

        // 等待表單載入
        await page.waitForTimeout(1000);

        // 填寫基本必填資料（不填寫 Line ID 和職稱）
        await page.getByPlaceholder('帳號', { exact: true }).fill(testBiz.account);
        await page.getByPlaceholder('暱稱').fill(testBiz.nickname);
        await page.getByPlaceholder('密碼').first().fill(testBiz.password);
        await page.getByPlaceholder('確認密碼').fill(testBiz.password);
        await page.getByPlaceholder('姓名').fill(testBiz.fullName);
        await page.getByPlaceholder('信箱').fill(testBiz.email);
        await page.getByPlaceholder('手機號碼').fill(testBiz.phone);
        await page.getByPlaceholder('公司名稱').fill(testBiz.companyName);

        // 填寫企業資料
        await page.getByPlaceholder('企業名稱').fill(testBiz.businessName);
        await page.getByPlaceholder('統一編號').fill(testBiz.taxId);
        await page.getByPlaceholder('企業電話').fill(testBiz.businessPhone);

        // 確認 Line ID 和職稱欄位保持空白
        const lineIdInput = page.getByPlaceholder('Line ID (選填)');
        const jobTitleInput = page.getByPlaceholder('職稱 (選填)');
        await expect(lineIdInput).toHaveValue('');
        await expect(jobTitleInput).toHaveValue('');
        console.log('已填寫必填欄位，Line ID 和職稱保持空白');

        // 點擊註冊
        await page.waitForTimeout(500);
        const registerBtn = page.locator('.register-btn');
        await registerBtn.click();
        console.log('已提交註冊');

        // 等待註冊完成
        await page.waitForTimeout(5000);

        // 檢查結果
        const pageContent = await page.locator('body').textContent();
        const success = pageContent?.includes('註冊完成') || pageContent?.includes('前往登入');

        console.log('企業帳號註冊結果:', success ? '成功' : '失敗');

        if (success) {
            await expect(page.locator('body')).toContainText('註冊完成');
            console.log('✅ 企業帳號不填寫選填欄位的註冊測試通過');
        } else {
            console.log('失敗頁面內容:', pageContent?.substring(0, 500));
        }

        expect(success).toBe(true);
    });

    test('密碼顯示/隱藏切換功能測試', async ({ page }) => {
        // 選擇個人帳號
        const personalBtn = page.locator('button').filter({ hasText: '個人帳號' }).first();
        await personalBtn.waitFor({ state: 'visible', timeout: 15000 });
        await personalBtn.click();

        // 點擊下一步進入表單頁面
        await page.waitForTimeout(500);
        const nextBtn = page.locator('button').filter({ hasText: '下一步' });
        await nextBtn.waitFor({ state: 'visible', timeout: 10000 });
        await nextBtn.click();

        // 等待表單載入
        await page.waitForTimeout(1000);

        // 填入密碼
        const passwordInput = page.getByPlaceholder('密碼').first();
        await passwordInput.fill('TestPassword123');

        // 驗證初始狀態密碼是隱藏的 (type="password")
        await expect(passwordInput).toHaveAttribute('type', 'password');
        console.log('初始狀態：密碼已隱藏');

        // 找到密碼欄位旁的眼睛按鈕並點擊
        const passwordToggle = page.locator('.form-field').filter({ has: page.getByPlaceholder('密碼').first() }).locator('button').first();
        await passwordToggle.click();
        await page.waitForTimeout(300);

        // 驗證密碼現在是可見的 (type="text")
        await expect(passwordInput).toHaveAttribute('type', 'text');
        console.log('點擊後：密碼已顯示');

        // 再次點擊應該隱藏密碼
        await passwordToggle.click();
        await page.waitForTimeout(300);

        // 驗證密碼又變回隱藏狀態
        await expect(passwordInput).toHaveAttribute('type', 'password');
        console.log('再次點擊：密碼已隱藏');

        console.log('✅ 密碼顯示/隱藏切換功能測試通過');
    });
});
