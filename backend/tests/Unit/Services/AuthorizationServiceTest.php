<?php

namespace Tests\Unit\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\AuthorizationService;
use App\Models\UrbanRenewalModel;

class AuthorizationServiceTest extends CIUnitTestCase
{
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthorizationService();
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

    public function testIsAdminReturnsFalseForNullUser()
    {
        $this->assertFalse($this->authService->isAdmin(null));
    }

    public function testIsCompanyManagerReturnsTrueForCompanyManager()
    {
        $user = [
            'role' => 'admin',
            'is_company_manager' => 1
        ];
        $this->assertTrue($this->authService->isCompanyManager($user));
    }

    public function testIsCompanyManagerReturnsTrueForStringOne()
    {
        $user = [
            'role' => 'admin',
            'is_company_manager' => '1'
        ];
        $this->assertTrue($this->authService->isCompanyManager($user));
    }

    public function testIsCompanyManagerReturnsTrueForBooleanTrue()
    {
        $user = [
            'role' => 'admin',
            'is_company_manager' => true
        ];
        $this->assertTrue($this->authService->isCompanyManager($user));
    }

    public function testIsCompanyManagerReturnsFalseForNonManager()
    {
        $user = [
            'role' => 'member',
            'is_company_manager' => 0
        ];
        $this->assertFalse($this->authService->isCompanyManager($user));
    }

    public function testIsCompanyManagerReturnsFalseForNullUser()
    {
        $this->assertFalse($this->authService->isCompanyManager(null));
    }

    public function testGetUserCompanyIdReturnsCorrectId()
    {
        $user = ['company_id' => 123];
        $result = $this->authService->getUserCompanyId($user);
        $this->assertEquals(123, $result);
    }

    public function testGetUserCompanyIdReturnsIntegerType()
    {
        $user = ['company_id' => '456'];
        $result = $this->authService->getUserCompanyId($user);
        $this->assertIsInt($result);
        $this->assertEquals(456, $result);
    }

    public function testGetUserCompanyIdReturnsNullForUserWithoutCompany()
    {
        $user = ['role' => 'member'];
        $result = $this->authService->getUserCompanyId($user);
        $this->assertNull($result);
    }

    public function testGetUserCompanyIdReturnsNullForNullUser()
    {
        $result = $this->authService->getUserCompanyId(null);
        $this->assertNull($result);
    }

    public function testCanAccessUrbanRenewalReturnsTrueForAdmin()
    {
        $user = ['role' => 'admin'];
        $result = $this->authService->canAccessUrbanRenewal($user, 1);
        $this->assertTrue($result);
    }

    public function testCanAccessUrbanRenewalReturnsFalseForNullUser()
    {
        $result = $this->authService->canAccessUrbanRenewal(null, 1);
        $this->assertFalse($result);
    }

    public function testCanAccessUrbanRenewalReturnsFalseForNonManager()
    {
        $user = ['role' => 'member'];
        $result = $this->authService->canAccessUrbanRenewal($user, 1);
        $this->assertFalse($result);
    }

    public function testGetAccessibleRenewalIdsReturnsNullForAdmin()
    {
        $user = ['role' => 'admin'];
        $result = $this->authService->getAccessibleRenewalIds($user);
        $this->assertNull($result); // null 表示可存取所有
    }

    public function testGetAccessibleRenewalIdsReturnsEmptyArrayForNullUser()
    {
        $result = $this->authService->getAccessibleRenewalIds(null);
        $this->assertEquals([], $result);
    }

    public function testGetAccessibleRenewalIdsReturnsEmptyArrayForUserWithoutCompany()
    {
        $user = ['role' => 'member'];
        $result = $this->authService->getAccessibleRenewalIds($user);
        $this->assertEquals([], $result);
    }

    public function testAssertAuthenticatedThrowsExceptionForNullUser()
    {
        $this->expectException(\App\Exceptions\UnauthorizedException::class);
        $this->authService->assertAuthenticated(null);
    }

    public function testAssertIsAdminThrowsExceptionForNonAdmin()
    {
        $this->expectException(\App\Exceptions\ForbiddenException::class);
        $user = ['role' => 'member'];
        $this->authService->assertIsAdmin($user);
    }

    public function testAssertIsCompanyManagerThrowsExceptionForNonManager()
    {
        $this->expectException(\App\Exceptions\ForbiddenException::class);
        $user = ['role' => 'member'];
        $this->authService->assertIsCompanyManager($user);
    }

    public function testAssertIsCompanyManagerPassesForAdmin()
    {
        $user = ['role' => 'admin'];
        // 不應該拋出異常
        $this->authService->assertIsCompanyManager($user);
        $this->assertTrue(true);
    }

    public function testGetRenewalFilterForUserReturnsEmptyForAdmin()
    {
        $user = ['role' => 'admin'];
        $result = $this->authService->getRenewalFilterForUser($user);
        $this->assertEquals([], $result);
    }

    public function testGetRenewalFilterForUserReturnsCompanyIdFilter()
    {
        $user = ['company_id' => 123];
        $result = $this->authService->getRenewalFilterForUser($user);
        $this->assertEquals(['company_id' => 123], $result);
    }

    public function testGetRenewalFilterForUserReturnsImpossibleFilterForNoCompany()
    {
        $user = ['role' => 'member'];
        $result = $this->authService->getRenewalFilterForUser($user);
        $this->assertEquals(['company_id' => -1], $result);
    }
}
