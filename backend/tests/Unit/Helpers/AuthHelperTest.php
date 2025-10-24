<?php

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Fixtures\UserFixture;
use Tests\Support\Fixtures\UrbanRenewalFixture;

/**
 * Auth Helper 單元測試
 * 測試 auth_check_resource_scope 和 auth_can_access_resource
 */
class AuthHelperTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = false;
    protected $migrateOnce = false;
    protected $refresh     = false;
    protected $namespace   = null;

    protected $userFixture;
    protected $urbanRenewalFixture;

    protected function setUp(): void
    {
        parent::setUp();

        // Load auth helper
        helper('auth');

        $this->userFixture = new UserFixture();
        $this->urbanRenewalFixture = new UrbanRenewalFixture();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fixtures will auto-cleanup via __destruct
    }

    /**
     * 測試 auth_check_resource_scope: 管理員可以存取任何資源
     */
    public function testAdminCanAccessAnyResource()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $admin = $this->userFixture->createAdmin();

        // Admin should access any resource
        $result = auth_check_resource_scope($urbanRenewal['id'], $admin);

        $this->assertTrue($result);
    }

    /**
     * 測試 auth_check_resource_scope: 一般使用者可以存取其指派的 urban_renewal_id
     */
    public function testUserCanAccessAssignedUrbanRenewal()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $member = $this->userFixture->createMember($urbanRenewal['id']);

        // Member should access their assigned urban_renewal_id
        $result = auth_check_resource_scope($urbanRenewal['id'], $member);

        $this->assertTrue($result);
    }

    /**
     * 測試 auth_check_resource_scope: 一般使用者無法存取其他 urban_renewal_id
     */
    public function testUserCannotAccessOtherUrbanRenewal()
    {
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();
        $member = $this->userFixture->createMember($urbanRenewal1['id']);

        // Member should NOT access other urban_renewal_id
        $result = auth_check_resource_scope($urbanRenewal2['id'], $member);

        $this->assertFalse($result);
    }

    /**
     * 測試 auth_check_resource_scope: 使用者的 urban_renewal_id 為 null 時無法存取資源
     */
    public function testUserWithNullUrbanRenewalIdCannotAccessResource()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();

        // Create a user with null urban_renewal_id (but not admin role)
        $user = $this->userFixture->createUserByRole('member', $urbanRenewal['id'], [
            'urban_renewal_id' => null,
            'role' => 'member'
        ]);

        // User with null urban_renewal_id (non-admin) should NOT access
        $result = auth_check_resource_scope($urbanRenewal['id'], $user);

        $this->assertFalse($result);
    }

    /**
     * 測試 auth_check_resource_scope: 理事長可以存取其指派的 urban_renewal_id
     */
    public function testChairmanCanAccessAssignedUrbanRenewal()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $chairman = $this->userFixture->createChairman($urbanRenewal['id']);

        // Chairman should access their assigned urban_renewal_id
        $result = auth_check_resource_scope($urbanRenewal['id'], $chairman);

        $this->assertTrue($result);
    }

    /**
     * 測試 auth_check_resource_scope: 理事長無法存取其他 urban_renewal_id
     */
    public function testChairmanCannotAccessOtherUrbanRenewal()
    {
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();
        $chairman = $this->userFixture->createChairman($urbanRenewal1['id']);

        // Chairman should NOT access other urban_renewal_id
        $result = auth_check_resource_scope($urbanRenewal2['id'], $chairman);

        $this->assertFalse($result);
    }

    /**
     * 測試 auth_check_resource_scope: 觀察員可以存取其指派的 urban_renewal_id
     */
    public function testObserverCanAccessAssignedUrbanRenewal()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $observer = $this->userFixture->createObserver($urbanRenewal['id']);

        // Observer should access their assigned urban_renewal_id
        $result = auth_check_resource_scope($urbanRenewal['id'], $observer);

        $this->assertTrue($result);
    }

    /**
     * 測試 auth_can_access_resource: 結合權限和範圍檢查
     */
    public function testCanAccessResourceWithPermissionAndScope()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $chairman = $this->userFixture->createChairman($urbanRenewal['id']);

        // Chairman has 'manage_meetings' permission for their urban_renewal_id
        $result = auth_can_access_resource('manage_meetings', $urbanRenewal['id'], $chairman);

        $this->assertTrue($result);
    }

    /**
     * 測試 auth_can_access_resource: 沒有權限時被拒絕
     */
    public function testCanAccessResourceWithoutPermission()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $observer = $this->userFixture->createObserver($urbanRenewal['id']);

        // Observer does NOT have 'manage_meetings' permission
        $result = auth_can_access_resource('manage_meetings', $urbanRenewal['id'], $observer);

        $this->assertFalse($result);
    }

    /**
     * 測試 auth_can_access_resource: 範圍不符時被拒絕
     */
    public function testCanAccessResourceWithWrongScope()
    {
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();
        $chairman = $this->userFixture->createChairman($urbanRenewal1['id']);

        // Chairman has permission but wrong urban_renewal_id
        $result = auth_can_access_resource('manage_meetings', $urbanRenewal2['id'], $chairman);

        $this->assertFalse($result);
    }

    /**
     * 測試 auth_can_access_resource: 管理員擁有所有權限
     */
    public function testAdminHasAllPermissions()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();
        $admin = $this->userFixture->createAdmin();

        // Admin has all permissions for any urban_renewal_id
        $result = auth_can_access_resource('manage_meetings', $urbanRenewal['id'], $admin);

        $this->assertTrue($result);
    }

    /**
     * 測試 auth_can_access_resource: resourceUrbanRenewalId 為 null 時只檢查權限
     */
    public function testCanAccessResourceWithoutScope()
    {
        $admin = $this->userFixture->createAdmin();

        // When resourceUrbanRenewalId is null, only check permission
        $result = auth_can_access_resource('view_system_settings', null, $admin);

        $this->assertTrue($result);
    }

    /**
     * 測試多種角色的資源範圍存取
     */
    public function testMultipleRolesResourceScopeAccess()
    {
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        $admin = $this->userFixture->createAdmin();
        $chairman1 = $this->userFixture->createChairman($urbanRenewal1['id']);
        $member1 = $this->userFixture->createMember($urbanRenewal1['id']);
        $observer1 = $this->userFixture->createObserver($urbanRenewal1['id']);

        // All can access urbanRenewal1
        $this->assertTrue(auth_check_resource_scope($urbanRenewal1['id'], $admin));
        $this->assertTrue(auth_check_resource_scope($urbanRenewal1['id'], $chairman1));
        $this->assertTrue(auth_check_resource_scope($urbanRenewal1['id'], $member1));
        $this->assertTrue(auth_check_resource_scope($urbanRenewal1['id'], $observer1));

        // Only admin can access urbanRenewal2
        $this->assertTrue(auth_check_resource_scope($urbanRenewal2['id'], $admin));
        $this->assertFalse(auth_check_resource_scope($urbanRenewal2['id'], $chairman1));
        $this->assertFalse(auth_check_resource_scope($urbanRenewal2['id'], $member1));
        $this->assertFalse(auth_check_resource_scope($urbanRenewal2['id'], $observer1));
    }

    /**
     * 測試相同 urban_renewal_id 但不同角色的權限
     */
    public function testDifferentRolesSameUrbanRenewal()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();

        $chairman = $this->userFixture->createChairman($urbanRenewal['id']);
        $member = $this->userFixture->createMember($urbanRenewal['id']);
        $observer = $this->userFixture->createObserver($urbanRenewal['id']);

        // All have same urban_renewal_id, so resource scope check passes
        $this->assertTrue(auth_check_resource_scope($urbanRenewal['id'], $chairman));
        $this->assertTrue(auth_check_resource_scope($urbanRenewal['id'], $member));
        $this->assertTrue(auth_check_resource_scope($urbanRenewal['id'], $observer));

        // But permissions differ
        $this->assertTrue(auth_can_access_resource('manage_meetings', $urbanRenewal['id'], $chairman));
        $this->assertFalse(auth_can_access_resource('manage_meetings', $urbanRenewal['id'], $member));
        $this->assertFalse(auth_can_access_resource('manage_meetings', $urbanRenewal['id'], $observer));
    }

    /**
     * 測試無效使用者時返回 false
     */
    public function testInvalidUserReturnsFalse()
    {
        $urbanRenewal = $this->urbanRenewalFixture->createActive();

        // Null user should return false
        $result = auth_check_resource_scope($urbanRenewal['id'], null);
        $this->assertFalse($result);

        // Empty user array should return false
        $result2 = auth_check_resource_scope($urbanRenewal['id'], []);
        $this->assertFalse($result2);
    }

    /**
     * 測試邊界情況：urban_renewal_id = 0
     */
    public function testEdgeCaseZeroUrbanRenewalId()
    {
        $member = $this->userFixture->createUserByRole('member', 1, [
            'urban_renewal_id' => 0
        ]);

        // urban_renewal_id = 0 should be treated as a valid ID
        $result = auth_check_resource_scope(0, $member);
        $this->assertTrue($result);

        // Different ID should fail
        $result2 = auth_check_resource_scope(1, $member);
        $this->assertFalse($result2);
    }
}
