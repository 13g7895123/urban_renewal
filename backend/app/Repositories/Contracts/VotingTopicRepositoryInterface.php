<?php

namespace App\Repositories\Contracts;

use App\Entities\VotingTopic;

/**
 * 投票議題 Repository 介面
 */
interface VotingTopicRepositoryInterface
{
    /**
     * 根據 ID 查找
     */
    public function findById(int $id): ?VotingTopic;

    /**
     * 根據會議 ID 查找所有議題
     * 
     * @return VotingTopic[]
     */
    public function findByMeetingId(int $meetingId): array;

    /**
     * 儲存議題
     */
    public function save(VotingTopic $entity): VotingTopic;

    /**
     * 刪除議題
     */
    public function delete(int $id): bool;

    /**
     * 更新議題狀態
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * 更新投票結果
     */
    public function updateVotingResults(int $id, array $results): bool;
}
