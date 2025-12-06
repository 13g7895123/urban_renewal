<?php

namespace App\Repositories\Contracts;

use App\Entities\PropertyOwner;

/**
 * 所有權人 Repository 介面
 */
interface PropertyOwnerRepositoryInterface
{
    /**
     * 根據 ID 查找
     */
    public function findById(int $id): ?PropertyOwner;

    /**
     * 根據更新會 ID 查找所有所有權人
     * 
     * @param int $urbanRenewalId
     * @return PropertyOwner[]
     */
    public function findByUrbanRenewalId(int $urbanRenewalId): array;

    /**
     * 查找合格投票人（未被排除）
     * 
     * @param int $urbanRenewalId
     * @return PropertyOwner[]
     */
    public function findEligibleVoters(int $urbanRenewalId): array;

    /**
     * 儲存（新增或更新）
     */
    public function save(PropertyOwner $entity): PropertyOwner;

    /**
     * 刪除
     */
    public function delete(int $id): bool;

    /**
     * 計算更新會的成員數
     */
    public function countByUrbanRenewalId(int $urbanRenewalId): int;

    /**
     * 根據編號查找
     */
    public function findByOwnerCode(string $ownerCode, int $urbanRenewalId): ?PropertyOwner;

    /**
     * 根據身分證字號查找
     */
    public function findByIdNumber(string $idNumber, int $urbanRenewalId): ?PropertyOwner;

    /**
     * 取得下一個自動編號
     */
    public function getNextOwnerCode(int $urbanRenewalId): string;
}
