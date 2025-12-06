<?php

namespace App\Services;

use App\Entities\UrbanRenewal;
use App\Repositories\UrbanRenewalRepository;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;

/**
 * 更新會服務
 */
class UrbanRenewalService
{
    private UrbanRenewalRepository $repository;
    private AuthorizationService $authService;

    public function __construct(
        ?UrbanRenewalRepository $repository = null,
        ?AuthorizationService $authService = null
    ) {
        $this->repository = $repository ?? new UrbanRenewalRepository();
        $this->authService = $authService ?? new AuthorizationService();
    }

    /**
     * 取得更新會列表（分頁）
     */
    public function getList(array $user, int $page, int $perPage, array $filters = []): array
    {
        // 非管理員只能看到自己企業的更新會
        if (!$this->authService->isAdmin($user)) {
            $companyFilter = $this->authService->getRenewalFilterForUser($user);
            $filters = array_merge($filters, $companyFilter);
        }

        $result = $this->repository->getPaginated($page, $perPage, $filters);
        
        return [
            'data' => array_map(fn($entity) => $entity->toArray(), $result['data']),
            'pagination' => $result['pagination']
        ];
    }

    /**
     * 取得更新會詳情
     */
    public function getDetail(array $user, int $id): array
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('更新會不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $id);

        return $entity->toArray();
    }

    /**
     * 建立更新會
     */
    public function create(array $user, array $data): array
    {
        if (empty($data['name'])) {
            throw new ValidationException('更新會名稱為必填項目');
        }

        // 企業管理者只能為自己的企業建立更新會
        if (!$this->authService->isAdmin($user)) {
            $this->authService->assertIsCompanyManager($user);
            $userCompanyId = $this->authService->getUserCompanyId($user);
            
            if (empty($data['company_id'])) {
                $data['company_id'] = $userCompanyId;
            } elseif ((int)$data['company_id'] !== $userCompanyId) {
                throw new ForbiddenException('您只能為自己的企業建立更新會');
            }
        }

        $entity = new UrbanRenewal($data['name']);
        
        if (!empty($data['company_id'])) {
            $entity->setCompanyId((int)$data['company_id']);
        }
        
        $entity->setAddress($data['address'] ?? null);
        $entity->setPhone($data['phone'] ?? null);
        $entity->setFax($data['fax'] ?? null);
        $entity->setEmail($data['email'] ?? null);
        $entity->setContactPerson($data['contact_person'] ?? null);
        $entity->setNotes($data['notes'] ?? null);

        $saved = $this->repository->save($entity);
        
        return $saved->toArray();
    }

    /**
     * 更新更新會
     */
    public function update(array $user, int $id, array $data): array
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('更新會不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $id);

        // 更新屬性
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        if (array_key_exists('address', $data)) {
            $entity->setAddress($data['address']);
        }
        if (array_key_exists('phone', $data)) {
            $entity->setPhone($data['phone']);
        }
        if (array_key_exists('fax', $data)) {
            $entity->setFax($data['fax']);
        }
        if (array_key_exists('email', $data)) {
            $entity->setEmail($data['email']);
        }
        if (array_key_exists('contact_person', $data)) {
            $entity->setContactPerson($data['contact_person']);
        }
        if (array_key_exists('notes', $data)) {
            $entity->setNotes($data['notes']);
        }

        $saved = $this->repository->save($entity);
        
        return $saved->toArray();
    }

    /**
     * 刪除更新會
     */
    public function delete(array $user, int $id): bool
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('更新會不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $id);

        // 檢查是否有關聯資料
        $meetingModel = model('MeetingModel');
        $hasMeetings = $meetingModel->where('urban_renewal_id', $id)->countAllResults() > 0;
        if ($hasMeetings) {
            throw new ValidationException('此更新會有關聯的會議資料，無法刪除');
        }

        return $this->repository->delete($id);
    }

    /**
     * 取得更新會統計
     */
    public function getStatistics(array $user, int $id): array
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('更新會不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $id);

        return $this->repository->calculateStatistics($id);
    }

    /**
     * 取得企業的所有更新會
     */
    public function getByCompany(array $user, int $companyId): array
    {
        // 檢查權限
        if (!$this->authService->isAdmin($user)) {
            $userCompanyId = $this->authService->getUserCompanyId($user);
            if ($userCompanyId !== $companyId) {
                throw new ForbiddenException('您無權存取其他企業的更新會');
            }
        }

        $entities = $this->repository->findByCompanyId($companyId);
        
        return array_map(fn($e) => $e->toArray(), $entities);
    }
}
