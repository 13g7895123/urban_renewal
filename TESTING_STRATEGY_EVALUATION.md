# 測試策略評估報告

> 評估日期：2026年1月24日  
> 專案：都市更新會管理系統

---

## 目錄

1. [現狀分析](#1-現狀分析)
2. [後端測試策略](#2-後端測試策略)
3. [前端測試策略](#3-前端測試策略)
4. [測試優先級與實施計劃](#4-測試優先級與實施計劃)
5. [資源與成本評估](#5-資源與成本評估)
6. [建議與總結](#6-建議與總結)

---

## 1. 現狀分析

### 1.1 後端現狀

#### ✅ 已具備的基礎設施
- **測試框架：** PHPUnit 10.5+（已配置）
- **CI 配置：** phpunit.xml.dist 完整設置
- **測試基類：** DatabaseTestCase（支援資料庫測試）
- **現有測試：** 1 個單元測試（AuthenticationEventModelTest）

#### 📊 測試覆蓋率現狀
```
目前覆蓋率：< 5%
測試數量：1 個測試類
待測試組件：
  - Controllers: 16 個 API 控制器
  - Models: 29 個模型
  - Services: 6 個服務
  - Filters: 3 個過濾器
  - Helpers: 多個輔助函數
```

#### 🔍 系統架構分析
```
後端架構：
├── Controllers/Api/          # RESTful API 端點
│   ├── AuthController        # 認證相關
│   ├── UserController        # 使用者管理
│   ├── CompanyController     # 企業管理
│   ├── UrbanRenewalController # 更新會管理
│   ├── PropertyOwnerController # 所有權人管理
│   ├── MeetingController     # 會議管理
│   ├── VotingController      # 投票管理
│   └── ... (9 個其他控制器)
├── Services/                 # 業務邏輯層
│   ├── AuthorizationService  # 權限服務
│   ├── MeetingService        # 會議服務
│   ├── UrbanRenewalService   # 更新會服務
│   └── ... (4 個其他服務)
├── Models/                   # 資料模型
│   ├── UserModel            # 使用者
│   ├── CompanyModel         # 企業
│   ├── PropertyOwnerModel   # 所有權人
│   ├── VotingRecordModel    # 投票記錄
│   └── ... (25 個其他模型)
└── Filters/                  # 中介層
    ├── JWTAuthFilter        # JWT 認證
    ├── RoleFilter           # 角色權限
    └── CorsFilter           # CORS 處理
```

---

### 1.2 前端現狀

#### ✅ 已具備的基礎設施
- **單元測試框架：** Vitest 3.2+（已配置）
- **E2E 測試框架：** Playwright 1.56+（已配置）
- **測試環境：** Happy-DOM
- **現有 E2E 測試：** 6 個測試檔案

#### 📊 測試覆蓋率現狀
```
E2E 測試覆蓋：
  ✅ login.spec.ts              # 登入功能
  ✅ signup.spec.ts             # 註冊功能
  ✅ signup-simple.spec.ts      # 簡化註冊
  ✅ signup-optional-fields.spec.ts
  ✅ register-t1101.spec.ts     # T1101 註冊
  ✅ company-profile-t1101.spec.ts

單元測試覆蓋：
  ⚠️ 僅有測試配置，無實際測試檔案
  
組件測試覆蓋：
  ❌ 尚未建立
```

#### 🔍 前端架構分析
```
前端架構：
├── pages/                    # 頁面路由
│   ├── admin/               # 管理後台
│   ├── tables/              # 資料表格
│   └── pages/               # 功能頁面
├── components/               # Vue 元件
│   ├── ui/                  # UI 組件
│   ├── forms/               # 表單組件
│   └── ... (其他組件)
├── composables/              # 組合式函式
│   ├── useAuth.ts          # 認證
│   ├── useApi.ts           # API 調用
│   └── ... (其他 composables)
├── stores/                   # Pinia 狀態管理
│   ├── auth.ts             # 認證狀態
│   ├── company.ts          # 企業狀態
│   └── ... (其他 stores)
└── middleware/               # 路由中介層
    ├── auth.ts             # 認證檢查
    └── company-manager.ts  # 企業管理者檢查
```

---

## 2. 後端測試策略

### 2.1 單元測試 (Unit Tests)

#### 🎯 測試目標
測試獨立的類別、方法、函數，確保各組件在隔離環境下正常運作。

#### 📋 優先測試項目

##### P0 - 關鍵業務邏輯（2-3週）
```php
1. Services 層
   ├── AuthorizationService
   │   ├── isAdmin()
   │   ├── isCompanyManager()
   │   ├── canAccessUrbanRenewal()
   │   └── getUserCompanyId()
   ├── MeetingService
   │   ├── createMeeting()
   │   ├── updateMeetingStatus()
   │   └── calculateQuorum()
   └── UrbanRenewalService
       ├── create()
       ├── update()
       └── validateRenewalData()

2. Models 層（業務邏輯）
   ├── VotingRecordModel
   │   ├── castVote()
   │   ├── calculateWeights()
   │   └── getVotingStatistics()
   ├── MeetingEligibleVoterModel
   │   ├── createSnapshot()
   │   └── refreshSnapshot()
   └── PropertyOwnerModel
       ├── calculateLandAreaWeight()
       └── calculateBuildingAreaWeight()

3. Helpers 函數
   ├── auth_helper
   │   ├── auth_validate_request()
   │   ├── auth_check_company_access()
   │   └── generate_jwt_token()
   └── response_helper
       ├── response_success()
       └── response_error()
```

##### P1 - 資料驗證與模型（3-4週）
```php
1. 所有 Model 的基本 CRUD
   ├── UserModel
   ├── CompanyModel
   ├── PropertyOwnerModel
   ├── LandPlotModel
   └── ... (其他模型)

2. 資料驗證規則
   ├── Validation/LocationRules
   └── 各 Model 的 validationRules

3. Filters
   ├── JWTAuthFilter
   ├── RoleFilter
   └── CorsFilter
```

##### P2 - 輔助功能（4-5週）
```php
1. 匯出服務
   ├── ExcelExportService
   └── WordExportService

2. 資料庫查詢方法
3. 快取機制
4. 日誌記錄
```

---

### 2.2 整合測試 (Integration Tests)

#### 🎯 測試目標
測試多個組件間的互動，特別是 API 端點、資料庫操作、服務層協作。

#### 📋 優先測試項目

##### P0 - 核心 API 流程（3-4週）
```php
1. 認證流程
   ├── POST /api/auth/register
   │   └── 測試：註冊 → 驗證 → 登入
   ├── POST /api/auth/login
   │   └── 測試：登入 → JWT Token → 權限
   ├── POST /api/auth/refresh
   │   └── 測試：Token 刷新機制
   └── POST /api/auth/logout
       └── 測試：登出 → Session 清除

2. 投票核心流程
   ├── POST /api/voting/vote
   │   └── 測試：投票 → 權重計算 → 統計更新
   ├── GET /api/voting/statistics/{topicId}
   │   └── 測試：統計準確性
   └── POST /api/voting/recalculate-weights/{topicId}
       └── 測試：重新計算邏輯

3. 會議管理流程
   ├── POST /api/meetings
   │   └── 測試：建立會議 → 合格投票人快照
   ├── PATCH /api/meetings/{id}/status
   │   └── 測試：狀態轉換邏輯
   └── POST /api/meetings/{id}/eligible-voters/refresh
       └── 測試：快照更新機制
```

##### P1 - 資料管理 API（4-6週）
```php
1. 企業與更新會管理
   ├── GET /api/companies/me
   ├── POST /api/urban-renewals
   ├── PUT /api/urban-renewals/{id}
   └── POST /api/urban-renewals/batch-assign

2. 所有權人管理
   ├── GET /api/property-owners
   ├── POST /api/property-owners
   ├── POST /api/urban-renewals/{id}/property-owners/import
   └── GET /api/urban-renewals/{id}/property-owners/export

3. 使用者管理
   ├── GET /api/users
   ├── POST /api/users
   └── PATCH /api/users/{id}/toggle-status
```

##### P2 - 輔助功能 API（6-8週）
```php
1. 通知系統
2. 文件管理
3. 系統設定
4. 地區資料
```

---

### 2.3 測試實作範例

#### 📝 單元測試範例
```php
<?php
// tests/Unit/Services/AuthorizationServiceTest.php

namespace Tests\Unit\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\AuthorizationService;
use App\Models\UrbanRenewalModel;

class AuthorizationServiceTest extends CIUnitTestCase
{
    protected $authService;
    protected $urbanRenewalModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthorizationService();
        $this->urbanRenewalModel = $this->createMock(UrbanRenewalModel::class);
    }

    public function testIsAdminReturnsTrueForAdminUser()
    {
        $user = ['role' => 'admin'];
        $this->assertTrue($this->authService->isAdmin($user));
    }

    public function testIsAdminReturnsFalseForNonAdminUser()
    {
        $user = ['role' => 'member'];
        $this->assertFalse($this->authService->isAdmin($user));
    }

    public function testIsCompanyManagerReturnsTrueForCompanyManager()
    {
        $user = [
            'role' => 'admin',
            'is_company_manager' => 1
        ];
        $this->assertTrue($this->authService->isCompanyManager($user));
    }

    public function testGetUserCompanyIdReturnsCorrectId()
    {
        $user = ['company_id' => 123];
        $result = $this->authService->getUserCompanyId($user);
        $this->assertEquals(123, $result);
    }

    public function testGetUserCompanyIdReturnsNullForUserWithoutCompany()
    {
        $user = ['role' => 'member'];
        $result = $this->authService->getUserCompanyId($user);
        $this->assertNull($result);
    }

    public function testCanAccessUrbanRenewalReturnsTrueForAdmin()
    {
        $user = ['role' => 'admin'];
        $result = $this->authService->canAccessUrbanRenewal($user, 1);
        $this->assertTrue($result);
    }
}
```

#### 📝 整合測試範例
```php
<?php
// tests/Feature/Api/AuthControllerTest.php

namespace Tests\Feature\Api;

use Tests\DatabaseTestCase;
use App\Models\UserModel;

class AuthControllerTest extends DatabaseTestCase
{
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new UserModel();
    }

    public function testUserCanRegisterWithValidData()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirm' => 'Password123!',
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'member'
        ];

        $result = $this->withHeaders([
            'Content-Type' => 'application/json'
        ])->post('/api/auth/register', $userData);

        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);

        // 驗證使用者已建立
        $user = $this->userModel->where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user['username']);
    }

    public function testUserCanLoginWithCorrectCredentials()
    {
        // 建立測試使用者
        $this->userModel->insert([
            'username' => 'testlogin',
            'email' => 'login@example.com',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
            'role' => 'member',
            'is_active' => 1
        ]);

        $loginData = [
            'username' => 'testlogin',
            'password' => 'Password123!'
        ];

        $result = $this->post('/api/auth/login', $loginData);
        
        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);
        
        // 驗證返回了使用者資料
        $data = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('user', $data['data']);
        $this->assertEquals('testlogin', $data['data']['user']['username']);
    }

    public function testLoginFailsWithIncorrectPassword()
    {
        $this->userModel->insert([
            'username' => 'testfail',
            'email' => 'fail@example.com',
            'password_hash' => password_hash('CorrectPassword', PASSWORD_DEFAULT),
            'role' => 'member',
            'is_active' => 1
        ]);

        $loginData = [
            'username' => 'testfail',
            'password' => 'WrongPassword'
        ];

        $result = $this->post('/api/auth/login', $loginData);
        
        $result->assertStatus(401);
        $result->assertJSONFragment([
            'success' => false
        ]);
    }

    public function testUserCanRefreshToken()
    {
        // 先登入取得 token
        $this->userModel->insert([
            'username' => 'refreshtest',
            'email' => 'refresh@example.com',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
            'role' => 'member',
            'is_active' => 1
        ]);

        $loginResult = $this->post('/api/auth/login', [
            'username' => 'refreshtest',
            'password' => 'Password123!'
        ]);

        // 使用 cookie 進行 refresh
        $refreshResult = $this->withCookies($loginResult->getCookies())
                              ->post('/api/auth/refresh');

        $refreshResult->assertStatus(200);
        $refreshResult->assertJSONFragment([
            'success' => true
        ]);
    }
}
```

#### 📝 模型測試範例
```php
<?php
// tests/Unit/Models/VotingRecordModelTest.php

namespace Tests\Unit\Models;

use Tests\DatabaseTestCase;
use App\Models\VotingRecordModel;
use App\Models\VotingTopicModel;
use App\Models\PropertyOwnerModel;
use App\Models\MeetingModel;

class VotingRecordModelTest extends DatabaseTestCase
{
    protected $votingRecordModel;
    protected $votingTopicModel;
    protected $propertyOwnerModel;
    protected $meetingModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->votingRecordModel = new VotingRecordModel();
        $this->votingTopicModel = new VotingTopicModel();
        $this->propertyOwnerModel = new PropertyOwnerModel();
        $this->meetingModel = new MeetingModel();
    }

    public function testCastVoteCreatesNewRecord()
    {
        // 建立測試資料
        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => 1,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled'
        ]);

        $topicId = $this->votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Topic',
            'voting_method' => 'simple_majority',
            'status' => 'open'
        ]);

        $ownerId = $this->propertyOwnerModel->insert([
            'urban_renewal_id' => 1,
            'name' => 'Test Owner',
            'id_number' => 'A123456789'
        ]);

        // 執行投票
        $result = $this->votingRecordModel->castVote(
            $topicId,
            $ownerId,
            'agree',
            'Test Voter',
            'Test note'
        );

        $this->assertTrue($result);

        // 驗證記錄已建立
        $vote = $this->votingRecordModel
            ->where('voting_topic_id', $topicId)
            ->where('property_owner_id', $ownerId)
            ->first();

        $this->assertNotNull($vote);
        $this->assertEquals('agree', $vote['vote_choice']);
        $this->assertEquals('Test Voter', $vote['voter_name']);
    }

    public function testCalculateWeightsReturnsCorrectValues()
    {
        // 建立測試資料並執行權重計算測試
        // ...
    }

    public function testGetVotingStatisticsReturnsAccurateCount()
    {
        // 測試統計功能
        // ...
    }
}
```

---

## 3. 前端測試策略

### 3.1 單元測試 (Unit Tests)

#### 🎯 測試目標
測試 Composables、Utils、獨立函數的邏輯正確性。

#### 📋 優先測試項目

##### P0 - 核心 Composables（2-3週）
```typescript
1. composables/useAuth.ts
   ├── login()
   ├── logout()
   ├── fetchUser()
   ├── isAuthenticated computed
   └── hasRole()

2. composables/useApi.ts
   ├── apiRequest()
   ├── get()
   ├── post()
   ├── put()
   ├── delete()
   └── 錯誤處理

3. composables/useVoting.ts
   ├── castVote()
   ├── calculateWeights()
   └── getStatistics()
```

##### P1 - Stores 狀態管理（3-4週）
```typescript
1. stores/auth.ts
   ├── actions: login, logout, fetchUser
   ├── getters: isLoggedIn, isAdmin
   └── state mutations

2. stores/company.ts
   ├── actions: fetchCompany, updateCompany
   └── getters: currentCompany

3. stores/meeting.ts
4. stores/voting.ts
```

##### P2 - Utils 工具函數（1-2週）
```typescript
1. utils/validators.ts
2. utils/formatters.ts
3. utils/helpers.ts
```

---

### 3.2 組件測試 (Component Tests)

#### 🎯 測試目標
測試 Vue 組件的渲染、事件處理、props 傳遞。

#### 📋 優先測試項目

##### P0 - 關鍵表單組件（3-4週）
```typescript
1. components/forms/LoginForm.vue
   ├── 輸入驗證
   ├── 提交事件
   └── 錯誤顯示

2. components/forms/VotingForm.vue
   ├── 投票選項選擇
   ├── 權重顯示
   └── 提交確認

3. components/forms/PropertyOwnerForm.vue
   ├── 必填欄位驗證
   ├── 資料格式驗證
   └── 提交處理
```

##### P1 - UI 組件（4-5週）
```typescript
1. components/ui/Modal.vue
2. components/ui/Table.vue
3. components/ui/Pagination.vue
4. components/ui/Alert.vue
5. components/ui/Notification.vue
```

##### P2 - 頁面組件（5-6週）
```typescript
1. components/meeting/MeetingCard.vue
2. components/voting/VotingTopicCard.vue
3. components/company/CompanyProfile.vue
```

---

### 3.3 E2E 測試 (End-to-End Tests)

#### 🎯 測試目標
測試完整的使用者流程，確保系統端到端的功能正常。

#### 📋 優先測試項目

##### P0 - 核心業務流程（已部分完成，需擴充）
```typescript
✅ 已完成：
1. 使用者註冊與登入
2. 基本表單提交

🔜 需要新增：
1. 完整投票流程
   ├── 登入 → 查看會議 → 查看議題 → 投票 → 確認結果

2. 會議管理流程
   ├── 企業管理者登入 → 建立會議 → 設定議題 → 管理出席

3. 所有權人管理流程
   ├── 匯入 Excel → 驗證資料 → 編輯 → 匯出

4. 企業管理流程
   ├── 建立更新會 → 指派成員 → 管理權限
```

##### P1 - 擴展功能流程（6-8週）
```typescript
1. 文件上傳與下載
2. 通知系統
3. 報表匯出
4. 搜尋與篩選
```

---

### 3.4 測試實作範例

#### 📝 Composable 單元測試範例
```typescript
// tests/composables/useAuth.test.ts

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuth } from '~/composables/useAuth'

describe('useAuth composable', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should login successfully with valid credentials', async () => {
    const mockResponse = {
      success: true,
      data: {
        user: {
          id: 1,
          username: 'testuser',
          role: 'admin'
        }
      }
    }

    global.$fetch = vi.fn().mockResolvedValue(mockResponse)

    const { login } = useAuth()
    const result = await login('testuser', 'password123')

    expect(result.success).toBe(true)
    expect(result.data.user.username).toBe('testuser')
    expect($fetch).toHaveBeenCalledWith(
      '/api/auth/login',
      expect.objectContaining({
        method: 'POST'
      })
    )
  })

  it('should handle login failure', async () => {
    const mockError = {
      success: false,
      message: '帳號或密碼錯誤'
    }

    global.$fetch = vi.fn().mockRejectedValue(mockError)

    const { login } = useAuth()
    
    await expect(login('wrong', 'credentials')).rejects.toEqual(mockError)
  })

  it('should check if user is authenticated', () => {
    const { isAuthenticated } = useAuth()
    
    // 初始應該是未認證
    expect(isAuthenticated.value).toBe(false)
  })

  it('should logout and clear user data', async () => {
    global.$fetch = vi.fn().mockResolvedValue({ success: true })

    const { logout } = useAuth()
    await logout()

    expect($fetch).toHaveBeenCalledWith('/api/auth/logout', {
      method: 'POST'
    })
  })
})
```

#### 📝 Store 單元測試範例
```typescript
// tests/stores/auth.test.ts

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '~/stores/auth'

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should set user data on successful login', async () => {
    const mockUser = {
      id: 1,
      username: 'testuser',
      role: 'admin',
      is_company_manager: 1
    }

    global.$fetch = vi.fn().mockResolvedValue({
      success: true,
      data: { user: mockUser }
    })

    const authStore = useAuthStore()
    await authStore.login('testuser', 'password')

    expect(authStore.user).toEqual(mockUser)
    expect(authStore.isLoggedIn).toBe(true)
  })

  it('should correctly identify admin users', () => {
    const authStore = useAuthStore()
    authStore.user = { role: 'admin' }

    expect(authStore.isAdmin).toBe(true)
  })

  it('should correctly identify company managers', () => {
    const authStore = useAuthStore()
    authStore.user = { 
      role: 'admin',
      is_company_manager: 1 
    }

    expect(authStore.isCompanyManager).toBe(true)
  })

  it('should clear user data on logout', async () => {
    const authStore = useAuthStore()
    authStore.user = { id: 1, username: 'test' }

    global.$fetch = vi.fn().mockResolvedValue({ success: true })
    
    await authStore.logout()

    expect(authStore.user).toBeNull()
    expect(authStore.isLoggedIn).toBe(false)
  })
})
```

#### 📝 組件測試範例
```typescript
// tests/components/forms/LoginForm.test.ts

import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import LoginForm from '~/components/forms/LoginForm.vue'

describe('LoginForm Component', () => {
  it('should render login form with all fields', () => {
    const wrapper = mount(LoginForm)

    expect(wrapper.find('input[name="username"]').exists()).toBe(true)
    expect(wrapper.find('input[name="password"]').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
  })

  it('should show validation errors for empty fields', async () => {
    const wrapper = mount(LoginForm)
    
    await wrapper.find('form').trigger('submit')

    expect(wrapper.text()).toContain('請輸入帳號')
    expect(wrapper.text()).toContain('請輸入密碼')
  })

  it('should emit login event with correct data', async () => {
    const wrapper = mount(LoginForm)

    await wrapper.find('input[name="username"]').setValue('testuser')
    await wrapper.find('input[name="password"]').setValue('password123')
    await wrapper.find('form').trigger('submit')

    expect(wrapper.emitted('login')).toBeTruthy()
    expect(wrapper.emitted('login')[0]).toEqual([{
      username: 'testuser',
      password: 'password123'
    }])
  })

  it('should disable submit button during loading', async () => {
    const wrapper = mount(LoginForm, {
      props: { loading: true }
    })

    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeDefined()
  })

  it('should display error message when provided', () => {
    const errorMessage = '帳號或密碼錯誤'
    const wrapper = mount(LoginForm, {
      props: { error: errorMessage }
    })

    expect(wrapper.text()).toContain(errorMessage)
  })
})
```

#### 📝 E2E 測試範例（擴充現有）
```typescript
// e2e/voting-flow.spec.ts

import { test, expect } from '@playwright/test'

test.describe('Voting Flow', () => {
  test.beforeEach(async ({ page }) => {
    // 登入
    await page.goto('/login')
    await page.fill('input[name="username"]', 'testuser')
    await page.fill('input[name="password"]', 'password123')
    await page.click('button[type="submit"]')
    await expect(page).toHaveURL('/admin')
  })

  test('complete voting process', async ({ page }) => {
    // 1. 前往會議列表
    await page.goto('/tables/meeting')
    await expect(page.locator('h1')).toContainText('會議管理')

    // 2. 點擊會議查看詳情
    await page.click('tr:first-child .view-button')
    await expect(page).toHaveURL(/\/meeting\/\d+/)

    // 3. 查看投票議題
    await page.click('.voting-topics-tab')
    await expect(page.locator('.voting-topic-card')).toBeVisible()

    // 4. 進行投票
    await page.click('.vote-button')
    await page.click('input[value="agree"]')
    await page.fill('textarea[name="notes"]', 'Test voting note')
    await page.click('button:has-text("確認投票")')

    // 5. 驗證投票成功
    await expect(page.locator('.success-message')).toContainText('投票成功')
    await expect(page.locator('.my-vote')).toContainText('同意')

    // 6. 查看統計結果
    await page.click('.statistics-button')
    await expect(page.locator('.vote-statistics')).toBeVisible()
    await expect(page.locator('.agree-count')).toContainText('1')
  })

  test('prevent double voting', async ({ page }) => {
    // 先投票一次
    await page.goto('/meeting/1/topic/1')
    await page.click('input[value="agree"]')
    await page.click('button:has-text("確認投票")')
    await expect(page.locator('.success-message')).toBeVisible()

    // 嘗試再次投票
    await page.reload()
    await expect(page.locator('.already-voted-message')).toBeVisible()
    await expect(page.locator('.vote-button')).toBeDisabled()
  })

  test('show voting weights correctly', async ({ page }) => {
    await page.goto('/meeting/1/topic/1')
    
    // 驗證權重資訊顯示
    await expect(page.locator('.land-area-weight')).toBeVisible()
    await expect(page.locator('.building-area-weight')).toBeVisible()
    
    // 驗證權重數值格式
    const landWeight = await page.locator('.land-area-weight').textContent()
    expect(landWeight).toMatch(/\d+\.\d+/)
  })
})
```

---

## 4. 測試優先級與實施計劃

### 4.1 後端測試實施計劃

#### 第一階段（4-6週）- 基礎建設
```
週 1-2：環境設置與基礎架構
├── 設置測試資料庫
├── 建立測試基類與輔助函數
├── 配置 CI/CD 整合
└── 建立測試資料 Seeder

週 3-4：核心業務邏輯測試
├── AuthorizationService 測試 (100%)
├── VotingRecordModel 測試 (100%)
├── MeetingService 測試 (100%)
└── auth_helper 測試 (100%)

週 5-6：關鍵 API 整合測試
├── 認證 API 測試 (100%)
├── 投票 API 測試 (100%)
└── 會議 API 測試 (80%)

目標覆蓋率：核心業務邏輯 80%+
```

#### 第二階段（6-8週）- 擴展覆蓋
```
週 7-10：Model 層測試
├── UserModel (100%)
├── CompanyModel (100%)
├── PropertyOwnerModel (100%)
├── MeetingModel (100%)
└── 其他 Models (60%)

週 11-14：Controller 整合測試
├── UserController
├── CompanyController
├── PropertyOwnerController
├── MeetingAttendanceController
└── 其他 Controllers

目標覆蓋率：整體 60%+
```

#### 第三階段（4-6週）- 完善與優化
```
週 15-18：剩餘組件測試
├── Filters 測試
├── Helpers 測試
├── 匯出服務測試
└── 邊界條件測試

週 19-20：測試優化
├── 效能測試
├── 壓力測試
├── 安全性測試
└── 測試文檔整理

目標覆蓋率：整體 75%+
```

---

### 4.2 前端測試實施計劃

#### 第一階段（3-4週）- 基礎建設
```
週 1-2：環境設置與核心測試
├── 完善 Vitest 配置
├── 建立測試工具函數
├── useAuth composable 測試 (100%)
├── useApi composable 測試 (100%)
└── auth store 測試 (100%)

週 3-4：關鍵組件測試
├── LoginForm 測試 (100%)
├── VotingForm 測試 (100%)
└── 基本 UI 組件測試 (60%)

目標覆蓋率：核心 Composables 80%+
```

#### 第二階段（4-6週）- E2E 擴展
```
週 5-8：完整業務流程 E2E
├── 投票完整流程 ✅
├── 會議管理流程 ✅
├── 所有權人管理流程 ✅
├── 企業管理流程 ✅
└── 文件上傳下載流程 ✅

週 9-10：組件測試擴展
├── 表格組件測試
├── 模態框組件測試
├── 表單驗證測試
└── 通知組件測試

目標覆蓋率：關鍵業務流程 100% E2E
```

#### 第三階段（3-4週）- 完善與優化
```
週 11-13：剩餘測試
├── Stores 測試完善
├── Utils 測試
├── Middleware 測試
└── 邊界條件測試

週 14：測試優化
├── 測試效能優化
├── CI/CD 整合
├── 測試報告自動化
└── 測試文檔

目標覆蓋率：整體 70%+
```

---

## 5. 資源與成本評估

### 5.1 人力資源需求

#### 後端測試
```
角色分配：
├── 資深後端工程師（1人）
│   ├── 架構設計與核心測試
│   ├── 複雜業務邏輯測試
│   └── Code Review
│
└── 後端工程師（1-2人）
    ├── Model 測試
    ├── Controller 測試
    └── 整合測試

預估工時：
├── 第一階段：240-320 小時（1.5-2 個月）
├── 第二階段：320-400 小時（2-2.5 個月）
└── 第三階段：160-240 小時（1-1.5 個月）

總計：720-960 小時（4.5-6 個月）
```

#### 前端測試
```
角色分配：
├── 資深前端工程師（1人）
│   ├── 測試架構設計
│   ├── E2E 測試框架
│   └── Code Review
│
└── 前端工程師（1-2人）
    ├── 組件測試
    ├── Composable 測試
    └── E2E 測試編寫

預估工時：
├── 第一階段：160-200 小時（1-1.25 個月）
├── 第二階段：200-280 小時（1.25-1.75 個月）
└── 第三階段：120-160 小時（0.75-1 個月）

總計：480-640 小時（3-4 個月）
```

---

### 5.2 工具與環境成本

#### 必要工具（免費）
```
後端：
✅ PHPUnit（已安裝）
✅ CodeIgniter 測試工具（已安裝）
✅ MySQL 測試資料庫

前端：
✅ Vitest（已安裝）
✅ Playwright（已安裝）
✅ Vue Test Utils（已安裝）
```

#### 建議增加工具
```
CI/CD：
├── GitHub Actions（免費 Public repo）
└── 或 GitLab CI（如果使用 GitLab）

程式碼品質：
├── SonarQube Community（免費）
├── PHPStan（免費）
└── ESLint + Prettier（已有）

測試覆蓋率報告：
├── Codecov（免費 Public repo）
└── 或內建 PHPUnit/Vitest 覆蓋率報告

預估額外成本：$0-50/月（如需私有 repo CI/CD）
```

---

### 5.3 時程與里程碑

```
總體時程：6-8 個月

里程碑：
├── M1（第 2 個月）
│   ├── 後端核心業務邏輯測試完成
│   ├── 前端核心 Composables 測試完成
│   └── CI/CD 基礎設置完成
│
├── M2（第 4 個月）
│   ├── 後端 API 整合測試完成 60%
│   ├── 前端 E2E 關鍵流程完成
│   └── 測試覆蓋率達 50%
│
├── M3（第 6 個月）
│   ├── 後端測試覆蓋率達 60%
│   ├── 前端測試覆蓋率達 60%
│   └── 所有 P0、P1 測試完成
│
└── M4（第 8 個月）
    ├── 後端測試覆蓋率達 75%
    ├── 前端測試覆蓋率達 70%
    ├── 完整測試文檔
    └── 測試自動化流程建立
```

---

## 6. 建議與總結

### 6.1 立即行動項目（本月內）

#### 🔴 優先級 P0
```
1. 設置測試環境
   ├── 建立測試資料庫
   ├── 配置 phpunit.xml 測試資料庫連線
   └── 設置前端測試 mock 資料

2. 建立測試基礎設施
   ├── 後端：擴展 DatabaseTestCase
   ├── 後端：建立 ApiTestCase 基類
   ├── 前端：建立測試工具函數庫
   └── 建立測試資料 Seeder

3. 編寫第一批關鍵測試
   ├── 後端：AuthorizationService 測試
   ├── 後端：Auth API 整合測試
   ├── 前端：useAuth composable 測試
   └── 前端：投票流程 E2E 測試
```

---

### 6.2 測試策略建議

#### ✅ 推薦做法
```
1. 採用 TDD（測試驅動開發）
   ├── 新功能先寫測試
   ├── 重構時確保測試通過
   └── Bug 修復時先寫失敗測試

2. 建立測試文化
   ├── Code Review 必檢查測試
   ├── PR 必須包含測試
   ├── 定期測試覆蓋率檢視
   └── 測試失敗時禁止 merge

3. 自動化測試流程
   ├── Git push 時觸發單元測試
   ├── PR 時執行完整測試套件
   ├── Deploy 前執行 E2E 測試
   └── 每日產生覆蓋率報告

4. 測試分層清晰
   ├── 70% 單元測試（快速、隔離）
   ├── 20% 整合測試（關鍵流程）
   └── 10% E2E 測試（關鍵業務路徑）
```

#### ⚠️ 注意事項
```
1. 避免過度測試
   ├── 不測試第三方套件
   ├── 不測試框架本身
   └── 專注業務邏輯測試

2. 維護測試品質
   ├── 定期重構測試程式碼
   ├── 移除過時測試
   ├── 避免測試相依性
   └── 保持測試獨立性

3. 效能考量
   ├── 單元測試應<1秒
   ├── 整合測試應<5秒
   ├── E2E 測試應<30秒
   └── 使用 parallel 執行加速

4. 資料庫測試
   ├── 每個測試使用獨立 transaction
   ├── 測試後自動 rollback
   ├── 避免測試間資料污染
   └── 使用 Factory 產生測試資料
```

---

### 6.3 長期目標

```
6 個月目標：
├── 後端測試覆蓋率：75%+
├── 前端測試覆蓋率：70%+
├── 關鍵業務流程：100% E2E
├── CI/CD 完全自動化
└── 測試執行時間 <10 分鐘

1 年目標：
├── 後端測試覆蓋率：85%+
├── 前端測試覆蓋率：80%+
├── 零 critical bugs 上線
├── 自動化效能測試
└── 完整測試文檔與培訓材料
```

---

### 6.4 ROI 評估

#### 投資成本
```
人力成本：
├── 後端測試：720-960 小時
├── 前端測試：480-640 小時
└── 總計：1200-1600 小時（約 7.5-10 人月）

工具成本：
├── CI/CD：$0-50/月
└── 程式碼品質工具：$0（使用免費版）

總投資：約 10 人月 + 基礎設施
```

#### 預期收益
```
短期收益（3-6 個月）：
├── ✅ 減少 60% 的 regression bugs
├── ✅ 提升 40% 的開發信心
├── ✅ 減少 50% 的手動測試時間
└── ✅ 提早發現 80% 的關鍵 bugs

長期收益（6-12 個月）：
├── ✅ 減少 80% 的生產環境 bugs
├── ✅ 加快 50% 的功能開發速度
├── ✅ 降低 70% 的維護成本
├── ✅ 提升團隊程式碼品質意識
└── ✅ 建立可持續的開發流程

ROI：預估 6-12 個月回本
```

---

## 7. 實施檢查清單

### 7.1 後端測試檢查清單

#### 環境設置
- [ ] 建立測試資料庫
- [ ] 配置 phpunit.xml
- [ ] 建立 DatabaseTestCase 基類
- [ ] 建立 ApiTestCase 基類
- [ ] 建立測試 Seeder
- [ ] 設置 CI/CD

#### 單元測試
- [ ] AuthorizationService 測試
- [ ] MeetingService 測試
- [ ] UrbanRenewalService 測試
- [ ] VotingRecordModel 測試
- [ ] UserModel 測試
- [ ] PropertyOwnerModel 測試
- [ ] auth_helper 測試

#### 整合測試
- [ ] Auth API 測試
- [ ] User API 測試
- [ ] Company API 測試
- [ ] Meeting API 測試
- [ ] Voting API 測試
- [ ] PropertyOwner API 測試

---

### 7.2 前端測試檢查清單

#### 環境設置
- [ ] 完善 vitest.config.ts
- [ ] 建立測試工具函數
- [ ] 設置 mock 資料
- [ ] 配置 Playwright
- [ ] 設置 CI/CD

#### 單元測試
- [ ] useAuth composable 測試
- [ ] useApi composable 測試
- [ ] auth store 測試
- [ ] company store 測試
- [ ] voting store 測試
- [ ] validators 測試

#### 組件測試
- [ ] LoginForm 測試
- [ ] VotingForm 測試
- [ ] PropertyOwnerForm 測試
- [ ] Modal 測試
- [ ] Table 測試
- [ ] Alert 測試

#### E2E 測試
- [ ] 投票完整流程
- [ ] 會議管理流程
- [ ] 所有權人管理流程
- [ ] 企業管理流程
- [ ] 文件上傳下載流程

---

## 8. 結論

### 8.1 核心建議

1. **分階段實施**：不要試圖一次完成所有測試，按優先級分階段進行
2. **從核心開始**：優先測試關鍵業務邏輯和高風險區域
3. **自動化優先**：盡早建立 CI/CD 自動化測試流程
4. **文化建設**：將測試融入開發流程，建立測試優先的文化
5. **持續改進**：定期回顧測試覆蓋率和測試品質，持續優化

---

### 8.2 成功關鍵因素

```
技術層面：
├── ✅ 完善的測試基礎設施
├── ✅ 清晰的測試策略
├── ✅ 自動化 CI/CD 流程
└── ✅ 良好的測試工具選擇

團隊層面：
├── ✅ 管理層支持與資源投入
├── ✅ 團隊成員測試技能培訓
├── ✅ Code Review 中重視測試
└── ✅ 建立測試優先文化

流程層面：
├── ✅ 明確的測試標準
├── ✅ PR 必須包含測試
├── ✅ 定期測試覆蓋率檢視
└── ✅ 測試失敗時的處理機制
```

---

### 8.3 預期成果

**6 個月後：**
- 後端測試覆蓋率達 75%
- 前端測試覆蓋率達 70%
- 關鍵業務流程 100% 測試
- 大幅減少生產環境 bugs
- 提升開發速度與信心

**這是一項長期投資，但將為系統的穩定性、可維護性和團隊效率帶來顯著提升。**

---

**報告完成時間：** 2026年1月24日  
**評估人員：** GitHub Copilot  
**下一步行動：** 請審閱本報告，確認測試策略後開始實施第一階段測試
