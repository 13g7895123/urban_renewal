<?php

namespace App\Repositories\Contracts;

use App\Entities\User;

/**
 * 使用者 Repository 介面
 */
interface UserRepositoryInterface
{
    /**
     * 根據 ID 查找
     */
    public function findById(int $id): ?User;

    /**
     * 根據使用者名稱查找
     */
    public function findByUsername(string $username): ?User;

    /**
     * 根據 Email 查找
     */
    public function findByEmail(string $email): ?User;

    /**
     * 根據企業 ID 查找所有使用者
     * 
     * @return User[]
     */
    public function findByCompanyId(int $companyId): array;

    /**
     * 取得使用者列表（分頁）
     */
    public function getPaginated(int $page, int $perPage, array $filters = []): array;

    /**
     * 儲存使用者
     */
    public function save(User $entity): User;

    /**
     * 刪除使用者
     */
    public function delete(int $id): bool;

    /**
     * 更新密碼
     */
    public function updatePassword(int $id, string $hashedPassword): bool;

    /**
     * 更新最後登入時間
     */
    public function updateLastLogin(int $id): bool;
}
