<?php

namespace App\Repositories\Contracts;

use App\Entities\Meeting;

/**
 * 會議 Repository 介面
 */
interface MeetingRepositoryInterface
{
    /**
     * 根據 ID 查找
     */
    public function findById(int $id): ?Meeting;

    /**
     * 根據更新會 ID 查找所有會議
     * 
     * @return Meeting[]
     */
    public function findByUrbanRenewalId(int $urbanRenewalId, ?string $status = null): array;

    /**
     * 取得會議列表（分頁）
     * 
     * @return array ['data' => Meeting[], 'pagination' => [...]]
     */
    public function getPaginated(int $page, int $perPage, array $filters = []): array;

    /**
     * 儲存會議
     */
    public function save(Meeting $entity): Meeting;

    /**
     * 刪除會議
     */
    public function delete(int $id): bool;

    /**
     * 檢查會議時間衝突
     */
    public function checkConflict(int $urbanRenewalId, string $date, string $time, ?int $excludeId = null): bool;

    /**
     * 取得會議統計
     */
    public function getStatistics(int $id): array;
}
