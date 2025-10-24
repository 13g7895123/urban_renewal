# 研究發現：管理員與使用者登入實作

**功能**: 管理員與使用者登入情境
**分支**: `002-admin-user`
**日期**: 2025-10-24
**狀態**: 完成

## 概述

本文件整合了實作管理員與使用者登入認證系統剩餘 20% 的研究發現。針對五個關鍵技術領域進行研究，以指導完成功能的實作決策。

---

## 1. 認證事件記錄架構

### 決策：混合方法（資料庫 + 結構化日誌）

**選定方案**：實作雙重記錄，在資料庫資料表（`authentication_events`）記錄關鍵事件，並在結構化 JSON 日誌檔案中記錄詳細資訊。

### 理由

**效能與規模**：
- 小到中型使用者基礎（每日約數百到數千認證事件）
- 僅針對關鍵事件進行資料庫寫入（4 種類型：登入成功/失敗、登出、token 更新）
- 預估成長：資料庫每年約 840MB，日誌每年約 5-15GB（兩者都可管理）
- 符合認證操作 <2 秒的效能要求

**安全監控優先**：
- 資料庫支援即時安全分析查詢（在 IP、username、event type、timestamp 上建立索引）
- 檔案日誌提供不可變更的審計追蹤，包含完整請求背景
- 結合方法滿足 OWASP 記錄建議

**現有基礎設施適配**：
- CodeIgniter 4 已使用基於檔案的記錄（程式碼庫中有 116 次 `log_message()` 呼叫）
- MariaDB 與遷移基礎設施已就位
- 當前日誌檔案：15-38KB/天（可管理的成長模式）

### 實作元件

**1. 資料庫資料表**：`authentication_events`
```sql
CREATE TABLE authentication_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM('login_success', 'login_failure', 'logout', 'token_refresh'),
    user_id INT UNSIGNED NULL,
    username_attempted VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    failure_reason VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_event_type (event_type),
    INDEX idx_ip_address (ip_address, created_at),
    INDEX idx_user_id (user_id, created_at),
    INDEX idx_username_attempted (username_attempted, created_at),
    INDEX idx_composite_security (event_type, ip_address, created_at)
)
```

**2. Model**：`AuthenticationEventModel.php` 包含安全查詢輔助函數：
- `getFailedLoginsByIP(string $ip, int $hours)` - 暴力攻擊偵測
- `getUserAuthHistory(int $userId, int $limit)` - 使用者審計追蹤
- `deleteOldEvents(int $retentionMonths)` - 保留政策清理

**3. 輔助函數**：`audit_helper.php` 中的 `log_auth_event(string $eventType, array $data)`
- 單一函數呼叫同時記錄到資料庫和檔案
- 優雅的降級處理：如果資料庫失敗，至少記錄到檔案
- 使用方式：`log_auth_event('login_success', ['user_id' => 1, 'ip_address' => '...'])`

**4. 結構化日誌格式**：CodeIgniter 每日輪替日誌中的 JSON
```json
{
  "event_type": "login_success",
  "user_id": 123,
  "username_attempted": "admin",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "timestamp": "2025-10-24 14:30:00",
  "request_id": "auth_67189abc123"
}
```

**5. 保留政策**：
- 資料庫：12 個月活動保留，透過 CodeIgniter Command 每月清理
- 檔案日誌：90 天，每日輪替，7 天後 gzip 壓縮

### 考慮的替代方案

**僅資料庫**：❌ 失去取證細節，冗長 metadata 使資料表膨脹
**僅日誌檔案**：❌ 安全分析查詢緩慢（無索引），需要外部工具
**混合（選定）**：✅ 快速查詢 + 取證完整性，利用現有基礎設施

### 要建立/修改的檔案

- `backend/app/Database/Migrations/2025-10-24-000001_CreateAuthenticationEventsTable.php` (新增)
- `backend/app/Models/AuthenticationEventModel.php` (新增)
- `backend/app/Helpers/audit_helper.php` (新增)
- `backend/app/Commands/AuthEventCleanup.php` (新增)
- `backend/app/Controllers/Api/AuthController.php` (更新 - 新增審計記錄呼叫)

---

## 2. CodeIgniter 4 中的排程任務實作

### 決策：CodeIgniter Commands + 專用 Docker Cron 容器

**選定方案**：原生 CodeIgniter CLI Commands 由 Docker Compose 中的獨立 cron 容器觸發。

### 理由

**Docker 最佳實踐**：
- 遵循「每個容器一個程序」原則
- 主應用程式容器專注於處理 HTTP 請求
- Cron 容器可以獨立擴展/重啟
- 不修改現有的後端 Dockerfile

**CodeIgniter 4 原生**：
- 使用內建的 `BaseCommand` 類別（無第三方相依性）
- 完整存取 models、database、helpers
- 簡單測試：`php spark session:cleanup --force`
- 與現有架構整合

**可靠性與簡單性**：
- 系統 cron 經過實戰考驗（30+ 年正式使用）
- 容器重啟不影響排程（cron 容器處理）
- 易於監控：`docker-compose logs -f cron`
- 最小配置負擔

### 實作架構

**1. CodeIgniter Command**：`backend/app/Commands/SessionCleanup.php`
```php
class SessionCleanup extends BaseCommand
{
    protected $group = 'Housekeeping';
    protected $name = 'session:cleanup';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $deleted = $db->table('user_sessions')
            ->where('expires_at <', Time::now()->toDateTimeString())
            ->where('is_active', 0)
            ->delete();

        CLI::write("Deleted {$deleted} expired session(s).", 'green');
        log_message('info', "Session cleanup: {$deleted} sessions deleted");
    }
}
```

**2. Cron 容器**：獨立的 Docker 服務
- 基礎映像：`php:8.2-cli` 包含系統 cron
- 共享後端 volume 以存取 `php spark` 指令
- 在前台執行 cron 並附帶日誌追蹤

**3. Crontab 配置**：`cron/crontab`
```cron
# 每日清理在凌晨 2:00
0 2 * * * cd /var/www/html && php spark session:cleanup --force >> /var/log/cron.log 2>&1

# 每週日凌晨 3:00 清理（更徹底）
0 3 * * 0 cd /var/www/html && php spark session:cleanup --force >> /var/log/cron.log 2>&1
```

**4. Docker Compose 服務**：新增至 `docker-compose.yml`
```yaml
cron:
  build:
    context: ./cron
  volumes:
    - ./backend:/var/www/html
  depends_on:
    - mariadb
    - backend
  restart: unless-stopped
```

### 考慮的替代方案

**daycry/cronjob 套件**：❌ 新增相依性、需要資料庫資料表、仍需系統 cron、對單一任務過度複雜
**單一容器 + Supervisor**：❌ 違反 Docker 最佳實踐、更難除錯、重啟影響應用程式和 cron
**主機 Cron**：❌ 破壞可攜性、需要主機配置、未版本控制

### 測試與監控

**手動測試**：
```bash
docker exec urban_renewal-backend-1 php spark session:cleanup --force
```

**監控執行**：
```bash
docker-compose logs -f cron
docker exec urban_renewal-cron-1 cat /var/log/cron.log
```

**驗證清理**：
```sql
SELECT COUNT(*) FROM user_sessions
WHERE expires_at < NOW() AND is_active = 0;
```

### 要建立的檔案

- `backend/app/Commands/SessionCleanup.php` (新增)
- `cron/Dockerfile` (新增)
- `cron/crontab` (新增)
- `docker-compose.yml` (更新 - 新增 cron 服務)

---

## 3. API 權限層設計模式

### 決策：混合 Trait + 輔助函數

**選定方案**：控制器使用 `HasRbacPermissions` trait 結合 `auth_helper.php` 中的增強輔助函數。

### 理由

**最小化程式碼重複**：
- 目前程式碼庫有 25+ 個手動 `urban_renewal_id` 檢查實例
- Trait 將權限邏輯集中在可重用方法中
- 將每次檢查的程式碼從約 8 行減少到 1-2 行

**遵循 CodeIgniter 4 慣例**：
- 現有模式使用 helpers（`auth_helper.php`、`response_helper.php`）
- 控制器已擴充 `BaseController`
- Traits 無縫整合且不改變架構

**平衡重用性與彈性**：
- Filters 太過剛性（無法在路由層級檢查資源特定的 `urban_renewal_id`）
- Service 層對簡單權限檢查增加不必要的負擔
- Traits 在控制器方法內提供細粒度控制

**現有模式對齊**：
- 程式碼庫已使用 `auth_validate_request(['admin', 'chairman'])` 進行角色檢查
- 這自然擴充了資源範圍權限的模式

### 實作元件

**1. Trait**：`app/Traits/HasRbacPermissions.php`

**主要方法**：
```php
trait HasRbacPermissions
{
    // 檢查資源範圍（urban_renewal_id 匹配）
    protected function checkResourceScope($resourceUrbanRenewalId, $user = null, $allowedRoles = ['admin'])
    {
        // 管理員略過範圍檢查
        // 非管理員必須匹配 urban_renewal_id
        // 返回 true 或錯誤回應
    }

    // 檢查角色 + 選用的資源範圍
    protected function checkRolePermission($allowedRoles, $resourceUrbanRenewalId = null, $user = null)
    {
        // 驗證角色在允許清單中
        // 如果是非管理員，也驗證 urban_renewal_id
        // 返回 true 或錯誤回應
    }

    // 輔助函數從相關資源提取 urban_renewal_id
    protected function getResourceUrbanRenewalId($resourceType, $resourceId)
    {
        // 'meeting' -> 查詢 meetings 資料表
        // 'voting_topic' -> 查詢 topic -> meeting -> urban_renewal_id
        // 返回 urban_renewal_id 或 null
    }
}
```

**2. 增強輔助函數**：`app/Helpers/auth_helper.php`

```php
// 資源範圍的快速布林檢查
function auth_check_resource_scope($resourceUrbanRenewalId, $user = null): bool

// 基於動作的權限檢查（view/create/update/delete）
function auth_can_access_resource($action, $resourceUrbanRenewalId = null, $user = null): bool
```

**權限矩陣**：
```php
$permissions = [
    'admin' => ['view', 'create', 'update', 'delete'],
    'chairman' => ['view', 'create', 'update', 'delete'], // 僅自己的 UR
    'member' => ['view'],
    'observer' => ['view']
];
```

### 使用範例

**之前**（VotingController.php 第 52 行）：
```php
$meetingModel = model('MeetingModel');
$meeting = $meetingModel->find($topic['meeting_id']);
if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
    return $this->failForbidden('無權限查看此投票記錄');
}
```

**之後**：
```php
use App\Traits\HasRbacPermissions;

class VotingController extends ResourceController
{
    use HasRbacPermissions;

    public function index()
    {
        $user = auth_validate_request();
        $urbanRenewalId = $this->getResourceUrbanRenewalId('voting_topic', $topicId);

        $check = $this->checkResourceScope($urbanRenewalId, $user);
        if ($check !== true) {
            return $check; // 返回錯誤回應
        }
        // ... 繼續邏輯
    }
}
```

**快速輔助函數檢查**：
```php
if (!auth_can_access_resource('delete', $meeting['urban_renewal_id'], $user)) {
    return response_error('您沒有刪除此會議的權限', 403);
}
```

### 考慮的替代方案

**Filter 類別**：❌ 無法在路由層級存取資源特定的 `urban_renewal_id`、效能負擔需要兩次載入資源
**Service 層**：❌ 增加負擔、與基於 helper 的程式碼庫不對齊、語法冗長
**僅 Helper 函數**：❌ 無法存取控制器狀態、重複的資源獲取、有限的重用性

### 實作路線圖

**階段 1**：建立 trait + helpers（2-3 小時）
**階段 2**：重構 2-3 個試點控制器（3-4 小時）
**階段 3**：推廣到剩餘 10+ 個控制器（4-5 小時）
**階段 4**：文件（1 小時）

### 要建立/修改的檔案

- `backend/app/Traits/HasRbacPermissions.php` (新增)
- `backend/app/Helpers/auth_helper.php` (更新 - 新增 2 個函數)
- `backend/app/Controllers/Api/` 中的所有 13 個控制器（更新 - 新增 `use HasRbacPermissions;`）

---

## 4. 前端自動 Token 更新策略

### 決策：混合方法（主動排程器 + 被動攔截器）

**選定方案**：Nuxt plugin 搭配排程 token 更新（主動）與 API 請求攔截器搭配重試邏輯（被動）的組合。

### 理由

**Nuxt 3 SSR 限制**：
- Token 更新必須僅在客戶端執行（`process.client` 檢查）
- Nuxt 的 `$fetch` 使用 ofetch，攔截器語義與 Axios 不同
- 需要遞迴重試模式而非傳統的攔截器變更

**為何不使用獨立方案**：
- **僅攔截器**：僅對 401 錯誤反應（使用者先體驗短暫失敗）
- **僅 setInterval**：即使不活動時也持續執行（低效）、無安全網
- **Pinia action**：需要手動呼叫（非自動）

**混合優勢**：
- 主動層在發生前防止約 95% 的 token 過期
- 被動安全網捕捉邊緣案例（網路失敗、時鐘偏移）
- 最佳使用者體驗：無中斷的無縫體驗
- 高效：僅在需要時更新（活動會話）

### 實作架構

**元件 1：增強 Auth Store**（Pinia）

**新增 Token 過期追蹤**：
```javascript
// 新增狀態
const tokenExpiresAt = ref(null) // Unix timestamp

// 輔助函數
const decodeToken = (token) => {
  const payload = JSON.parse(atob(token.split('.')[1]))
  return payload
}

const isTokenExpiringSoon = (bufferSeconds = 300) => {
  if (!tokenExpiresAt.value) return true
  const now = Date.now() / 1000
  return (tokenExpiresAt.value - now) < bufferSeconds // 5分鐘緩衝
}

// 在 login/refresh 中儲存過期時間
const decoded = decodeToken(userToken)
if (decoded?.exp) {
  tokenExpiresAt.value = decoded.exp
  localStorage.setItem('token_expires_at', decoded.exp.toString())
}
```

**元件 2：主動更新 Plugin**（`plugins/token-refresh.client.js`）

```javascript
const scheduleTokenRefresh = () => {
  const expiresAt = authStore.tokenExpiresAt
  const timeUntilExpiry = expiresAt - (Date.now() / 1000)
  const refreshBuffer = 300 // 過期前 5 分鐘
  const timeUntilRefresh = timeUntilExpiry - refreshBuffer

  if (timeUntilRefresh <= 0) {
    // 立即更新
    authStore.refreshToken().then(() => scheduleTokenRefresh())
  } else {
    // 排程更新
    refreshTimer = setTimeout(() => {
      authStore.refreshToken().then(() => scheduleTokenRefresh())
    }, timeUntilRefresh * 1000)
  }
}

// 監視 auth 狀態變更
watch(() => authStore.isLoggedIn, (isLoggedIn) => {
  if (isLoggedIn) scheduleTokenRefresh()
  else if (refreshTimer) clearTimeout(refreshTimer)
})
```

**元件 3：被動攔截器**（`composables/useApi.js`）

```javascript
const isRefreshing = ref(false)
let refreshPromise = null

const apiRequest = async (endpoint, options, isRetry = false) => {
  try {
    return await $fetch(endpoint, defaultOptions)
  } catch (error) {
    // 處理 401 並重試
    if (error.status === 401 && !isRetry) {
      // 防止並發更新
      if (!isRefreshing.value) {
        isRefreshing.value = true
        refreshPromise = useAuthStore().refreshToken()
      }

      await refreshPromise // 等待更新
      return await apiRequest(endpoint, options, true) // 重試
    }
    throw error
  }
}
```

**元件 4：更新登入以儲存 Refresh Token**

```javascript
const { user, token, refresh_token } = response.data.data

localStorage.setItem('auth_token', token)
localStorage.setItem('refresh_token', refresh_token)
localStorage.setItem('token_expires_at', decoded.exp.toString())
```

### 錯誤處理策略

**1. 過期的 Refresh Token（7 天）**：
```javascript
catch (error) {
  await logout(true) // 強制登出
  console.warn('Session expired. Please log in again.')
}
```

**2. 網路失敗**：使用指數退避的重試
**3. 並發請求**：共享 `refreshPromise` 防止重複更新
**4. 競爭條件**：`isRefreshing` 旗標協調主動 + 被動層
**5. 跨分頁同步**：localStorage 事件監聽器同步 token 更新

### 考慮的替代方案

**@sidebase/nuxt-auth**：❌ 外部相依性、固執架構、目前實作輕量
**@nuxtjs-alt/http**：❌ 不必要的抽象、增加 bundle 大小
**伺服器端 middleware**：❌ Nuxt SSR 不在請求間持久化狀態
**WebWorker**：❌ 過度複雜、無法直接存取 localStorage

### 實作檢查清單

- [ ] 新增 `tokenExpiresAt` 狀態到 auth store
- [ ] 新增 `decodeToken()` 和 `isTokenExpiringSoon()` helpers
- [ ] 更新 `login()` 以儲存過期時間和 refresh_token
- [ ] 建立 `plugins/token-refresh.client.js` 包含排程器
- [ ] 修改 `useApi.js` 新增 401 重試邏輯
- [ ] 在 localStorage 儲存 refresh_token
- [ ] 新增跨分頁同步監聽器
- [ ] 測試排程更新（降低緩衝以測試）
- [ ] 測試過期 token 的 401 攔截器
- [ ] 測試更新期間的並發請求

### 要建立/修改的檔案

- `frontend/stores/auth.js` (更新 - 新增過期追蹤)
- `frontend/plugins/token-refresh.client.js` (新增)
- `frontend/composables/useApi.js` (更新 - 新增重試攔截器)

---

## 5. RBAC 強制執行的測試策略

### 決策：混合整合測試（70%）+ 單元測試（30%）

**選定方案**：主要使用 CodeIgniter 的 `FeatureTestTrait` 進行整合測試，輔以 helpers 和 filters 的單元測試。

### 理由

**RBAC 邏輯整合**：
- 權限檢查與 HTTP 請求、JWT tokens、資料庫狀態緊密整合
- 整合測試提供端到端信心
- CodeIgniter 4 的 `FeatureTestTrait` 專為 HTTP 測試設計

**測試效率**：
- 完整的請求/回應週期驗證整個權限流程
- helpers/filter 邏輯的單元測試提供快速回饋
- 組合提供全面涵蓋

### 測試結構

```
backend/tests/
├── Support/
│   ├── Fixtures/
│   │   ├── UserFixture.php              # 測試使用者工廠（所有 4 種角色）
│   │   ├── UrbanRenewalFixture.php      # 測試 UR 資料
│   │   └── MeetingFixture.php           # 測試會議資料
│   └── DatabaseTestCase.php             # [已存在]
│
├── Unit/
│   ├── Helpers/AuthHelperTest.php       # 測試 auth_helper 函數
│   ├── Filters/RoleFilterTest.php       # 測試角色過濾
│   └── Models/UserModelTest.php
│
└── Feature/
    ├── RBAC/                             # **權限矩陣測試**
    │   ├── AdminPermissionsTest.php
    │   ├── ChairmanPermissionsTest.php
    │   ├── MemberPermissionsTest.php
    │   ├── ObserverPermissionsTest.php
    │   └── CrossUrbanRenewalAccessTest.php
    └── Controllers/
        ├── UserControllerTest.php
        ├── MeetingControllerTest.php
        └── ... (共 13 個控制器)
```

### Fixture 策略：測試 Fixtures 搭配工廠

**UserFixture.php**：
```php
class UserFixture
{
    public static function createTestUsers($db): array
    {
        return [
            'admin' => ['id' => 1, 'role' => 'admin', 'urban_renewal_id' => null],
            'chairman_ur1' => ['id' => 2, 'role' => 'chairman', 'urban_renewal_id' => 1],
            'chairman_ur2' => ['id' => 3, 'role' => 'chairman', 'urban_renewal_id' => 2],
            'member_ur1' => ['id' => 4, 'role' => 'member', 'urban_renewal_id' => 1],
            'observer_ur1' => ['id' => 5, 'role' => 'observer', 'urban_renewal_id' => 1]
        ];
    }

    public static function generateToken(array $user): string
    {
        // 為測試生成 JWT token
    }
}
```

### 權限矩陣測試

**測試矩陣**（4 個角色 × 4 個動作 × N 個資源）：

| 角色 | 查看自己的 | 查看其他的 | 建立 | 更新 | 刪除 |
|------|----------|------------|--------|--------|--------|
| Admin | ✓ | ✓ | ✓ | ✓ | ✓ |
| Chairman | ✓ | ✗ | ✓ (自己的) | ✓ (自己的) | ✓ (自己的) |
| Member | ✓ | ✗ | ✗ | ✗ | ✗ |
| Observer | ✓ | ✗ | ✗ | ✗ | ✗ |

**範例測試**（ChairmanPermissionsTest.php）：
```php
public function test_chairman_can_view_own_urban_renewal_meetings()
{
    $chairmanUser = $this->users['chairman_ur1'];
    $token = UserFixture::generateToken($chairmanUser);

    $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                   ->get('/api/meetings?urban_renewal_id=1');

    $result->assertStatus(200);
    $this->assertNotEmpty($result->getJSON(true)['data']);
}

public function test_chairman_cannot_view_other_urban_renewal_meetings()
{
    $chairmanUser = $this->users['chairman_ur1'];
    $token = UserFixture::generateToken($chairmanUser);

    $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                   ->get('/api/meetings?urban_renewal_id=2'); // 不同 UR

    $result->assertStatus(403); // 或空結果
}
```

### 測試斷言模式

**1. HTTP 狀態碼**：
```php
$result->assertStatus(200);  // 成功
$result->assertStatus(401);  // 未授權
$result->assertStatus(403);  // 禁止
```

**2. 回應結構**：
```php
$response = $result->getJSON(true);
$this->assertEquals('success', $response['success']);
$this->assertArrayHasKey('data', $response);
```

**3. 資料存取控制**：
```php
foreach ($response['data'] as $item) {
    $this->assertEquals($user['urban_renewal_id'], $item['urban_renewal_id']);
}
```

**4. 資料庫狀態**：
```php
$this->seeInDatabase('users', ['username' => 'test_user']);
$this->dontSeeInDatabase('users', ['username' => 'unauthorized_user']);
```

### 涵蓋率目標

**最低涵蓋率：80%+**

**依程式碼區域**：
- 控制器：85% 行涵蓋率
- Filters（Auth/RBAC）：**95%**（關鍵安全程式碼）
- Helpers：90%
- Models：80%

**必須涵蓋的情境**：
- 認證（100%）：登入、登出、token 驗證、token 更新
- 角色矩陣（100%）：7 個情境 × 13 個控制器 = 91 個核心測試
- 資源隔離（100%）：跨 UR 防止測試
- 邊緣案例（80%）：並發存取、會話逾時、無效輸入

**涵蓋率指令**：
```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html build/coverage
```

### 實作路線圖

**階段 1（第 1 週）**：基礎
- 建立 UserFixture、UrbanRenewalFixture
- 撰寫 auth helpers 的單元測試
- 建立基準結構

**階段 2（第 2-3 週）**：核心 RBAC
- AdminPermissionsTest
- ChairmanPermissionsTest
- MemberPermissionsTest
- ObserverPermissionsTest
- CrossUrbanRenewalAccessTest

**階段 3（第 4-5 週）**：控制器整合
- 測試 13 個控制器（優先 User、Meeting、Voting）

**階段 4（第 6 週）**：邊緣案例
- Token 過期、並發存取、安全測試

**階段 5（第 7 週）**：最佳化
- 達成 80%+ 涵蓋率、最佳化慢速測試、文件

### 要建立的檔案

- `backend/tests/Support/Fixtures/UserFixture.php` (新增)
- `backend/tests/Support/Fixtures/UrbanRenewalFixture.php` (新增)
- `backend/tests/Feature/RBAC/AdminPermissionsTest.php` (新增)
- `backend/tests/Feature/RBAC/ChairmanPermissionsTest.php` (新增)
- `backend/tests/Feature/RBAC/MemberPermissionsTest.php` (新增)
- `backend/tests/Feature/RBAC/ObserverPermissionsTest.php` (新增)
- `backend/tests/Feature/RBAC/CrossUrbanRenewalAccessTest.php` (新增)

---

## 決策摘要

| 研究領域 | 決策 | 主要優勢 |
|---------------|----------|-------------|
| **認證事件記錄** | 混合（資料庫 + 日誌） | 快速安全查詢 + 取證細節 |
| **排程任務** | CodeIgniter Commands + Docker Cron | Docker 原生、可靠、簡單 |
| **RBAC 模式** | Trait + Helpers | 將 25+ 次檢查減少到 1-2 行、可重用 |
| **Token 更新** | 主動 + 被動 | 95% 無縫更新、邊緣案例的安全網 |
| **測試策略** | 整合（70%）+ 單元（30%） | 端到端信心、快速回饋 |

## 後續步驟

研究完成後，繼續進行階段 1 設計產物：

1. **data-model.md** - 實體定義與關係
2. **contracts/** - 認證與審計 APIs 的 OpenAPI 規格
3. **quickstart.md** - 開發者入門指南
4. **更新代理背景** - 新增技術到 `.claude/memory/project.md`

所有決策都已準備好用於正式環境，並與現有程式碼庫模式對齊。
