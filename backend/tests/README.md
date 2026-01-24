# 後端測試指南

## 測試架構

```
tests/
├── Support/              # 測試輔助類別
│   ├── ApiTestCase.php  # API 測試基類
│   └── TestSeeder.php   # 測試資料 Seeder
├── Unit/                 # 單元測試
│   ├── Services/        # 服務層測試
│   ├── Models/          # 模型測試
│   └── Helpers/         # 輔助函數測試
└── Feature/              # 整合測試
    └── Api/             # API 端點測試
```

## 執行測試

### 執行所有測試
```bash
cd backend
./vendor/bin/phpunit
```

### 執行特定測試套件
```bash
# 只執行單元測試
./vendor/bin/phpunit --testsuite Unit

# 只執行整合測試
./vendor/bin/phpunit --testsuite Feature
```

### 執行特定測試文件
```bash
./vendor/bin/phpunit tests/Unit/Services/AuthorizationServiceTest.php
```

### 執行特定測試方法
```bash
./vendor/bin/phpunit --filter testIsAdminReturnsTrueForAdminUser
```

### 生成覆蓋率報告
```bash
./vendor/bin/phpunit --coverage-html build/coverage
```

## 測試資料庫配置

在 `phpunit.xml.dist` 中配置測試資料庫：

```xml
<env name="database.tests.hostname" value="localhost"/>
<env name="database.tests.database" value="urban_renewal_test"/>
<env name="database.tests.username" value="root"/>
<env name="database.tests.password" value=""/>
<env name="database.tests.DBDriver" value="MySQLi"/>
<env name="database.tests.DBPrefix" value="test_"/>
```

## 編寫測試

### 單元測試範例

```php
<?php
namespace Tests\Unit\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\YourService;

class YourServiceTest extends CIUnitTestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new YourService();
    }

    public function testSomething()
    {
        $result = $this->service->doSomething();
        $this->assertTrue($result);
    }
}
```

### API 整合測試範例

```php
<?php
namespace Tests\Feature\Api;

use Tests\Support\ApiTestCase;

class YourControllerTest extends ApiTestCase
{
    public function testGetEndpoint()
    {
        $user = $this->createAdminUser();
        
        $result = $this->authenticatedGet('/api/your-endpoint', $user);
        
        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);
    }
}
```

## 測試最佳實踐

1. **測試名稱清晰**：使用描述性的測試方法名稱
2. **獨立性**：每個測試應該獨立執行
3. **可重複性**：測試應該可以重複執行並得到相同結果
4. **快速執行**：單元測試應該快速執行（< 1 秒）
5. **資料清理**：使用 transaction 自動回滾測試資料

## 常用斷言

```php
// 相等性
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);

// 真值
$this->assertTrue($condition);
$this->assertFalse($condition);

// 類型
$this->assertIsInt($value);
$this->assertIsString($value);
$this->assertIsArray($value);

// 空值
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertEmpty($value);
$this->assertNotEmpty($value);

// 陣列
$this->assertArrayHasKey($key, $array);
$this->assertCount($expectedCount, $array);
$this->assertContains($needle, $haystack);

// 例外
$this->expectException(Exception::class);
```

## 輔助方法

### ApiTestCase 提供的方法

- `createTestUser($data = [])` - 建立測試使用者
- `createTestCompany($data = [])` - 建立測試企業
- `createAdminUser()` - 建立管理員
- `createCompanyManager($companyId)` - 建立企業管理者
- `loginAs($user)` - 模擬使用者登入
- `authenticatedGet($path, $user)` - 發送認證 GET 請求
- `authenticatedPost($path, $data, $user)` - 發送認證 POST 請求
- `authenticatedPut($path, $data, $user)` - 發送認證 PUT 請求
- `authenticatedDelete($path, $user)` - 發送認證 DELETE 請求

## 持續整合

測試應該整合到 CI/CD 流程中：

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: ./vendor/bin/phpunit
```

## 疑難排解

### 測試資料庫連接失敗
- 確認測試資料庫已建立
- 檢查 phpunit.xml.dist 中的資料庫配置
- 確認資料庫使用者權限

### 測試失敗但本地正常
- 檢查環境差異（PHP 版本、擴充套件）
- 確認測試資料的獨立性
- 檢查時區設定

### 記憶體不足
- 增加 PHP 記憶體限制：`php -d memory_limit=512M vendor/bin/phpunit`
- 減少平行執行的測試數量

## 相關資源

- [PHPUnit 官方文檔](https://phpunit.de/documentation.html)
- [CodeIgniter 4 測試指南](https://codeigniter.com/user_guide/testing/index.html)
