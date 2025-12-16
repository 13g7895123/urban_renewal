<?php

namespace App\Repositories;

use App\Entities\Meeting;
use App\Repositories\Contracts\MeetingRepositoryInterface;

/**
 * 會議 Repository 實作
 */
class MeetingRepository implements MeetingRepositoryInterface
{
    private $meetingModel;
    private $urbanRenewalModel;
    private $observerModel;
    private $votingTopicModel;

    public function __construct()
    {
        $this->meetingModel = model('MeetingModel');
        $this->urbanRenewalModel = model('UrbanRenewalModel');
        $this->observerModel = model('MeetingObserverModel');
        $this->votingTopicModel = model('VotingTopicModel');
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Meeting
    {
        $data = $this->meetingModel->getMeetingWithDetails($id);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    /**
     * @inheritDoc
     */
    public function findByUrbanRenewalId(int $urbanRenewalId, ?string $status = null): array
    {
        $builder = $this->meetingModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->where('deleted_at', null);

        if ($status) {
            $builder->where('meeting_status', $status);
        }

        $records = $builder->orderBy('meeting_date', 'DESC')->findAll();

        return array_map(fn($data) => $this->hydrate($data), $records);
    }

    /**
     * @inheritDoc
     */
    public function getPaginated(int $page, int $perPage, array $filters = []): array
    {
        $data = $this->meetingModel->getMeetings($page, $perPage, $filters);
        $pager = $this->meetingModel->pager;

        return [
            'data' => array_map(fn($item) => $this->hydrate($item), $data),
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'total_pages' => $pager->getPageCount()
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function save(Meeting $entity): Meeting
    {
        $data = $this->dehydrate($entity);

        if ($entity->getId()) {
            $result = $this->meetingModel->update($entity->getId(), $data);
            if ($result === false) {
                $errors = $this->meetingModel->errors();
                throw new \RuntimeException('更新會議失敗: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }
            $id = $entity->getId();
        } else {
            $id = $this->meetingModel->insert($data);
            if ($id === false) {
                $errors = $this->meetingModel->errors();
                throw new \RuntimeException('新增會議失敗: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }
            $entity->setId($id);
        }

        // 處理觀察員
        if (!empty($entity->getObservers())) {
            $this->syncObservers($id, $entity->getObservers());
        }

        $saved = $this->findById($id);
        if ($saved === null) {
            throw new \RuntimeException('無法取得已儲存的會議資料');
        }

        return $saved;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        return $this->meetingModel->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function checkConflict(int $urbanRenewalId, string $date, string $time, ?int $excludeId = null): bool
    {
        $conflict = $this->meetingModel->checkMeetingConflict($urbanRenewalId, $date, $time, $excludeId);
        return $conflict !== null;
    }

    /**
     * @inheritDoc
     */
    public function getStatistics(int $id): array
    {
        return $this->meetingModel->getMeetingStatistics($id);
    }

    // === 私有方法 ===

    /**
     * 將資料庫記錄轉換為 Entity
     */
    private function hydrate(array $data): Meeting
    {
        return Meeting::fromArray($data);
    }

    /**
     * 將 Entity 轉換為資料庫格式
     */
    private function dehydrate(Meeting $entity): array
    {
        return [
            'urban_renewal_id' => $entity->getUrbanRenewalId(),
            'meeting_name' => $entity->getMeetingName(),
            'meeting_type' => $entity->getMeetingType(),
            'meeting_date' => $entity->getMeetingDate(),
            'meeting_time' => $entity->getMeetingTime(),
            'meeting_location' => $entity->getMeetingLocation(),
            'attendee_count' => $entity->getAttendeeCount(),
            'calculated_total_count' => $entity->getCalculatedTotalCount(),
            'observer_count' => $entity->getObserverCount(),
            'meeting_status' => $entity->getMeetingStatus(),
        ];
    }

    /**
     * 同步觀察員
     */
    private function syncObservers(int $meetingId, array $observers): void
    {
        // 刪除舊的觀察員
        $this->observerModel->where('meeting_id', $meetingId)->delete();

        // 新增新的觀察員
        foreach ($observers as $observer) {
            if (!empty($observer['name'])) {
                $this->observerModel->insert([
                    'meeting_id' => $meetingId,
                    'observer_name' => $observer['name'],
                    'observer_title' => $observer['title'] ?? null,
                    'observer_organization' => $observer['organization'] ?? null,
                    'contact_phone' => $observer['phone'] ?? null,
                    'notes' => $observer['notes'] ?? null
                ]);
            }
        }

        // 更新觀察員數量
        $count = $this->observerModel->where('meeting_id', $meetingId)->countAllResults();
        $this->meetingModel->update($meetingId, ['observer_count' => $count]);
    }
}
