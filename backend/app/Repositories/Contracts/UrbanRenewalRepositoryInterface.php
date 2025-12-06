<?php

namespace App\Repositories\Contracts;

use App\Entities\UrbanRenewal;

/**
 * 更新會 Repository 介面
 */
interface UrbanRenewalRepositoryInterface
{
    /**
     * 根據 ID 查找
     */
    public function findById(int $id): ?UrbanRenewal;

    /**
     * 根據企業 ID 查找所有更新會
     * 
     * @return UrbanRenewal[]
     */
    public function findByCompanyId(int $companyId): array;

    /**
     * 取得更新會列表（分頁）
     */
    public function getPaginated(int $page, int $perPage, array $filters = []): array;

    /**
     * 儲存更新會
     */
    public function save(UrbanRenewal $entity): UrbanRenewal;

    /**
     * 刪除更新會
     */
    public function delete(int $id): bool;

    /**
     * 計算更新會統計資料
     */
    public function calculateStatistics(int $id): array;
}
