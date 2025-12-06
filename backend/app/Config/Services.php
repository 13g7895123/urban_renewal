<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\AuthorizationService;
use App\Services\PropertyOwnerService;
use App\Services\MeetingService;
use App\Services\UrbanRenewalService;
use App\Repositories\PropertyOwnerRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\UrbanRenewalRepository;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    // ========================================
    // 核心服務
    // ========================================

    /**
     * 授權服務
     */
    public static function authorizationService(bool $getShared = true): AuthorizationService
    {
        if ($getShared) {
            return static::getSharedInstance('authorizationService');
        }

        return new AuthorizationService();
    }

    // ========================================
    // 所有權人相關
    // ========================================

    /**
     * 所有權人 Repository
     */
    public static function propertyOwnerRepository(bool $getShared = true): PropertyOwnerRepository
    {
        if ($getShared) {
            return static::getSharedInstance('propertyOwnerRepository');
        }

        return new PropertyOwnerRepository();
    }

    /**
     * 所有權人服務
     */
    public static function propertyOwnerService(bool $getShared = true): PropertyOwnerService
    {
        if ($getShared) {
            return static::getSharedInstance('propertyOwnerService');
        }

        return new PropertyOwnerService(
            static::propertyOwnerRepository(),
            static::authorizationService()
        );
    }

    // ========================================
    // 會議相關
    // ========================================

    /**
     * 會議 Repository
     */
    public static function meetingRepository(bool $getShared = true): MeetingRepository
    {
        if ($getShared) {
            return static::getSharedInstance('meetingRepository');
        }

        return new MeetingRepository();
    }

    /**
     * 會議服務
     */
    public static function meetingService(bool $getShared = true): MeetingService
    {
        if ($getShared) {
            return static::getSharedInstance('meetingService');
        }

        return new MeetingService(
            static::meetingRepository(),
            static::authorizationService()
        );
    }

    // ========================================
    // 更新會相關
    // ========================================

    /**
     * 更新會 Repository
     */
    public static function urbanRenewalRepository(bool $getShared = true): UrbanRenewalRepository
    {
        if ($getShared) {
            return static::getSharedInstance('urbanRenewalRepository');
        }

        return new UrbanRenewalRepository();
    }

    /**
     * 更新會服務
     */
    public static function urbanRenewalService(bool $getShared = true): UrbanRenewalService
    {
        if ($getShared) {
            return static::getSharedInstance('urbanRenewalService');
        }

        return new UrbanRenewalService(
            static::urbanRenewalRepository(),
            static::authorizationService()
        );
    }
}
