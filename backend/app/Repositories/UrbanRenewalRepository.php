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
        $builder = $this->urbanRenewalModel;

        if (!empty($filters['company_id'])) {
            $builder->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['search'])) {
            $builder->like('name', $filters['search']);
        }

        $total = $builder->countAllResults(false);
        $data = $builder->orderBy('created_at', 'DESC')
                        ->limit($perPage, ($page - 1) * $perPage)
                        ->findAll();

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
            $this->urbanRenewalModel->update($entity->getId(), $data);
            $id = $entity->getId();
        } else {
            $id = $this->urbanRenewalModel->insert($data);
            $entity->setId($id);
        }

        return $this->findById($id);
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
            'notes' => $entity->getNotes(),
        ];
    }
}
