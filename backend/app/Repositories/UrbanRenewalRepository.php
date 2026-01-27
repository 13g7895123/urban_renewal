<?php

namespace App\Repositories;

use App\Entities\UrbanRenewal;
use App\Repositories\Contracts\UrbanRenewalRepositoryInterface;

/**
 * 更新會 Repository 實作
 */
class UrbanRenewalRepository implements UrbanRenewalRepositoryInterface
{
    private $urbanRenewalModel;
    private $propertyOwnerModel;
    private $meetingModel;

    public function __construct()
    {
        $this->urbanRenewalModel = model('UrbanRenewalModel');
        $this->propertyOwnerModel = model('PropertyOwnerModel');
        $this->meetingModel = model('MeetingModel');
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?UrbanRenewal
    {
        $data = $this->urbanRenewalModel->find($id);

        if (!$data) {
            return null;
        }

        return $this->hydrateWithRelations($data);
    }

    /**
     * @inheritDoc
     */
    public function findByCompanyId(int $companyId): array
    {
        $records = $this->urbanRenewalModel
            ->where('company_id', $companyId)
            ->findAll();

        return array_map(fn($data) => $this->hydrateWithRelations($data), $records);
    }

    /**
     * @inheritDoc
     */
    public function getPaginated(int $page, int $perPage, array $filters = []): array
    {
        // Create a fresh builder instance for count
        $countBuilder = $this->urbanRenewalModel->builder();
        
        if (!empty($filters['company_id'])) {
            $countBuilder->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['search'])) {
            $countBuilder->like('name', $filters['search']);
        }
        
        // Add deleted_at filter for soft deletes
        $countBuilder->where('deleted_at', null);

        $total = $countBuilder->countAllResults();

        // Create a new builder instance for the actual query
        $builder = $this->urbanRenewalModel->builder();
        $builder->select('urban_renewals.*');
        
        // Add LEFT JOIN to get assigned admin name
        $builder->select('users.full_name as assigned_admin_name, users.username as assigned_admin_username');
        $builder->join('users', 'users.id = urban_renewals.assigned_admin_id', 'left');
        
        if (!empty($filters['company_id'])) {
            $builder->where('urban_renewals.company_id', $filters['company_id']);
        }

        if (!empty($filters['search'])) {
            $builder->like('urban_renewals.name', $filters['search']);
        }
        
        // Add deleted_at filter for soft deletes
        $builder->where('urban_renewals.deleted_at', null);

        $data = $builder->orderBy('urban_renewals.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return [
            'data' => array_map(fn($item) => $this->hydrateWithRelations($item), $data),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function save(UrbanRenewal $entity): UrbanRenewal
    {
        $data = $this->dehydrate($entity);

        if ($entity->getId()) {
            $result = $this->urbanRenewalModel->update($entity->getId(), $data);
            if ($result === false) {
                $errors = $this->urbanRenewalModel->errors();
                log_message('error', 'UrbanRenewal update failed: ' . json_encode($errors));
                throw new \RuntimeException('更新更新會失敗: ' . implode(', ', $errors));
            }
            $id = $entity->getId();
        } else {
            $result = $this->urbanRenewalModel->insert($data);
            if ($result === false) {
                // Log validation errors if insert fails
                $errors = $this->urbanRenewalModel->errors();
                log_message('error', 'UrbanRenewal insert failed: ' . json_encode($errors));
                throw new \RuntimeException('新增更新會失敗: ' . implode(', ', $errors));
            }
            $id = $result;
            $entity->setId($id);
        }

        $saved = $this->findById($id);
        if ($saved === null) {
            throw new \RuntimeException('無法取得已儲存的更新會資料');
        }

        return $saved;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        return $this->urbanRenewalModel->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function calculateStatistics(int $id): array
    {
        return $this->urbanRenewalModel->calculateTotalLandArea($id);
    }

    // === 私有方法 ===

    /**
     * 將資料庫記錄轉換為 Entity（含關聯資料）
     */
    private function hydrateWithRelations(array $data): UrbanRenewal
    {
        $entity = UrbanRenewal::fromArray($data);

        // 載入所有權人數量
        $propertyOwnerCount = $this->propertyOwnerModel
            ->where('urban_renewal_id', $data['id'])
            ->countAllResults();
        $entity->setPropertyOwnerCount($propertyOwnerCount);

        // 載入會議數量
        $meetingCount = $this->meetingModel
            ->where('urban_renewal_id', $data['id'])
            ->where('deleted_at', null)
            ->countAllResults();
        $entity->setMeetingCount($meetingCount);

        // 載入企業名稱
        if (!empty($data['company_id'])) {
            $companyModel = model('CompanyModel');
            $company = $companyModel->find($data['company_id']);
            if ($company) {
                $entity->setCompanyName($company['name']);
            }
        }
        
        // 設定 assigned admin 名稱（如果已經在 JOIN 中獲取）
        if (!empty($data['assigned_admin_name'])) {
            $entity->setAssignedAdminName($data['assigned_admin_name']);
        } elseif (!empty($data['assigned_admin_id'])) {
            // 如果沒有 JOIN，手動查詢
            $userModel = model('UserModel');
            $admin = $userModel->find($data['assigned_admin_id']);
            if ($admin) {
                $entity->setAssignedAdminName($admin['full_name'] ?? $admin['username']);
            }
        }

        return $entity;
    }

    /**
     * 將 Entity 轉換為資料庫格式
     */
    private function dehydrate(UrbanRenewal $entity): array
    {
        return [
            'company_id' => $entity->getCompanyId(),
            'name' => $entity->getName(),
            'address' => $entity->getAddress(),
            'phone' => $entity->getPhone(),
            'fax' => $entity->getFax(),
            'email' => $entity->getEmail(),
            'contact_person' => $entity->getContactPerson(),
            'chairman_name' => $entity->getChairmanName(),
            'chairman_phone' => $entity->getChairmanPhone(),
            'representative' => $entity->getRepresentative(),
            'notes' => $entity->getNotes(),
            'assigned_admin_id' => $entity->getAssignedAdminId(),
        ];
    }
}
