<?php

namespace App\Services;

use App\Entities\Meeting;
use App\Repositories\MeetingRepository;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;

/**
 * 會議服務
 * 
 * 處理會議相關的業務邏輯
 */
class MeetingService
{
    private MeetingRepository $repository;
    private AuthorizationService $authService;
    private $eligibleVoterModel;
    private $votingTopicModel;

    public function __construct(
        ?MeetingRepository $repository = null,
        ?AuthorizationService $authService = null
    ) {
        $this->repository = $repository ?? new MeetingRepository();
        $this->authService = $authService ?? new AuthorizationService();
        $this->eligibleVoterModel = model('MeetingEligibleVoterModel');
        $this->votingTopicModel = model('VotingTopicModel');
    }

    /**
     * 取得會議列表（分頁）
     */
    public function getList(?array $user, int $page, int $perPage, array $filters = []): array
    {
        // 非管理員需要過濾資料
        if ($user && !$this->authService->isAdmin($user)) {
            // 如果是企業管理者，只能看到自己企業的會議
            if ($this->authService->isCompanyManager($user)) {
                $renewalIds = $this->authService->getAccessibleRenewalIds($user);
                if ($renewalIds !== null && !empty($renewalIds)) {
                    $filters['urban_renewal_ids'] = $renewalIds;
                } elseif ($renewalIds !== null) {
                    // 沒有可存取的更新會
                    return [
                        'data' => [],
                        'pagination' => [
                            'current_page' => $page,
                            'per_page' => $perPage,
                            'total' => 0,
                            'total_pages' => 0
                        ]
                    ];
                }
            }
            // 非企業管理者：不加過濾條件（與重構前行為一致）
        }

        $result = $this->repository->getPaginated($page, $perPage, $filters);

        return [
            'data' => array_map(fn($entity) => $entity->toArray(), $result['data']),
            'pagination' => $result['pagination']
        ];
    }

    /**
     * 取得特定更新會的會議列表
     */
    public function getByUrbanRenewal(array $user, int $urbanRenewalId, ?string $status = null): array
    {
        $this->authService->assertCanAccessUrbanRenewal($user, $urbanRenewalId);

        $entities = $this->repository->findByUrbanRenewalId($urbanRenewalId, $status);

        return array_map(fn($e) => $e->toArray(), $entities);
    }

    /**
     * 取得會議詳情
     */
    public function getDetail(array $user, int $id): array
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        return $entity->toArray();
    }

    /**
     * 建立會議
     */
    public function create(array $user, array $data): array
    {
        $this->validateRequiredFields($data);
        $this->authService->assertCanAccessUrbanRenewal($user, (int)$data['urban_renewal_id']);

        // 檢查更新會是否存在
        $urbanRenewalModel = model('UrbanRenewalModel');
        if (!$urbanRenewalModel->find($data['urban_renewal_id'])) {
            throw new NotFoundException('指定的更新會不存在');
        }

        // 檢查時間衝突
        if ($this->repository->checkConflict(
            (int)$data['urban_renewal_id'],
            $data['meeting_date'],
            $data['meeting_time']
        )) {
            throw new ValidationException('該時間已有其他會議安排');
        }

        // 建立 Entity
        $entity = new Meeting(
            (int)$data['urban_renewal_id'],
            $data['meeting_name'],
            $data['meeting_type'],
            $data['meeting_date'],
            $data['meeting_time']
        );

        if (!empty($data['meeting_location'])) {
            $entity->setMeetingLocation($data['meeting_location']);
        }

        // 設定出席人數（來自更新會的所有權人數）
        if (isset($data['attendee_count'])) {
            $entity->setAttendeeCount((int)$data['attendee_count']);
        }

        // 設定列席者人數
        if (isset($data['observer_count'])) {
            $entity->setObserverCount((int)$data['observer_count']);
        }

        if (!empty($data['observers'])) {
            $entity->setObservers($data['observers']);
        }

        // 儲存
        $saved = $this->repository->save($entity);


        // 建立合格投票人快照
        $snapshotResult = $this->eligibleVoterModel->createSnapshot(
            $saved->getId(),
            $saved->getUrbanRenewalId()
        );

        // 更新計算總人數
        $calculatedTotalCount = $snapshotResult['snapshot_count'];
        if (!empty($data['exclude_owner_from_count'])) {
            $calculatedTotalCount = max(0, $calculatedTotalCount - 1);
        }

        $meetingModel = model('MeetingModel');
        $meetingModel->update($saved->getId(), [
            'calculated_total_count' => $calculatedTotalCount
        ]);

        // 重新取得完整資料
        $result = $this->repository->findById($saved->getId());
        $resultArray = $result->toArray();
        $resultArray['voter_snapshot'] = $snapshotResult;

        return $resultArray;
    }

    /**
     * 更新會議
     */
    public function update(array $user, int $id, array $data): array
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        // 檢查時間衝突
        if (isset($data['meeting_date']) || isset($data['meeting_time'])) {
            $checkDate = $data['meeting_date'] ?? $entity->getMeetingDate();
            $checkTime = $data['meeting_time'] ?? $entity->getMeetingTime();

            if ($this->repository->checkConflict(
                $entity->getUrbanRenewalId(),
                $checkDate,
                $checkTime,
                $id
            )) {
                throw new ValidationException('該時間已有其他會議安排');
            }
        }

        // 更新屬性
        if (isset($data['meeting_name'])) {
            $entity->setMeetingName($data['meeting_name']);
        }
        if (isset($data['meeting_type'])) {
            $entity->setMeetingType($data['meeting_type']);
        }
        if (isset($data['meeting_date'])) {
            $entity->setMeetingDate($data['meeting_date']);
        }
        if (isset($data['meeting_time'])) {
            $entity->setMeetingTime($data['meeting_time']);
        }
        if (array_key_exists('meeting_location', $data)) {
            $entity->setMeetingLocation($data['meeting_location']);
        }
        if (isset($data['attendee_count'])) {
            $entity->setAttendeeCount((int)$data['attendee_count']);
        }
        if (isset($data['observer_count'])) {
            $entity->setObserverCount((int)$data['observer_count']);
        }
        if (isset($data['observers'])) {
            $entity->setObservers($data['observers']);
        }

        $saved = $this->repository->save($entity);


        // 更新計算總人數
        if (isset($data['exclude_owner_from_count'])) {
            $snapshotStats = $this->eligibleVoterModel->getSnapshotStatistics($id);
            $baseCount = $snapshotStats['total_voters'];

            $calculatedTotalCount = $baseCount;
            if ($data['exclude_owner_from_count']) {
                $calculatedTotalCount = max(0, $baseCount - 1);
            }

            $meetingModel = model('MeetingModel');
            $meetingModel->update($id, ['calculated_total_count' => $calculatedTotalCount]);
        }

        return $this->repository->findById($id)->toArray();
    }

    /**
     * 刪除會議
     */
    public function delete(array $user, int $id): bool
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        // 檢查是否可以刪除
        if (!$entity->canBeDeleted()) {
            throw new ValidationException('進行中或已完成的會議不能刪除');
        }

        // 檢查是否有投票議題
        $hasVotingTopics = $this->votingTopicModel->where('meeting_id', $id)->countAllResults() > 0;
        if ($hasVotingTopics) {
            throw new ValidationException('有投票議題的會議不能刪除，請先刪除相關議題');
        }

        return $this->repository->delete($id);
    }

    /**
     * 更新會議狀態
     */
    public function updateStatus(array $user, int $id, string $newStatus): array
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        // 檢查狀態轉換
        if (!$entity->canTransitionTo($newStatus)) {
            throw new ValidationException(
                "無法從狀態「{$entity->getMeetingStatus()}」變更為「{$newStatus}」"
            );
        }

        $meetingModel = model('MeetingModel');
        $meetingModel->update($id, ['meeting_status' => $newStatus]);

        return $this->repository->findById($id)->toArray();
    }

    /**
     * 取得會議統計
     */
    public function getStatistics(array $user, int $id): array
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        return $this->repository->getStatistics($id);
    }

    /**
     * 取得會議的合格投票人
     */
    public function getEligibleVoters(array $user, int $id, int $page = 1, int $perPage = 100): array
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        // 檢查是否有快照
        if (!$this->eligibleVoterModel->hasSnapshot($id)) {
            return [
                'data' => [],
                'statistics' => [
                    'total_voters' => 0,
                    'total_land_area' => 0,
                    'total_building_area' => 0,
                    'snapshot_at' => null
                ],
                'has_snapshot' => false
            ];
        }

        $voters = $this->eligibleVoterModel->getByMeetingIdPaginated($id, $page, $perPage);
        $statistics = $this->eligibleVoterModel->getSnapshotStatistics($id);
        $pager = $this->eligibleVoterModel->pager;

        return [
            'data' => $voters,
            'statistics' => $statistics,
            'has_snapshot' => true,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'total_pages' => $pager->getPageCount()
            ]
        ];
    }

    /**
     * 重新整理合格投票人快照
     */
    public function refreshEligibleVoters(array $user, int $id): array
    {
        $entity = $this->repository->findById($id);

        if (!$entity) {
            throw new NotFoundException('會議不存在');
        }

        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        if (!$entity->canRefreshVoters()) {
            throw new ValidationException('只有草稿或已排程的會議可以重新整理投票人名單');
        }

        $result = $this->eligibleVoterModel->recreateSnapshot($id, $entity->getUrbanRenewalId());

        if (!$result['success']) {
            throw new \RuntimeException($result['error'] ?? '重新整理投票人名單失敗');
        }

        // 更新會議的計算總人數
        $meetingModel = model('MeetingModel');
        $meetingModel->update($id, ['calculated_total_count' => $result['snapshot_count']]);

        return $result;
    }

    // === 私有方法 ===

    /**
     * 驗證必填欄位
     */
    private function validateRequiredFields(array $data): void
    {
        $errors = [];

        if (empty($data['urban_renewal_id'])) {
            $errors['urban_renewal_id'] = '更新會ID為必填';
        }
        if (empty($data['meeting_name'])) {
            $errors['meeting_name'] = '會議名稱為必填';
        }
        if (empty($data['meeting_type'])) {
            $errors['meeting_type'] = '會議類型為必填';
        }
        if (empty($data['meeting_date'])) {
            $errors['meeting_date'] = '會議日期為必填';
        }
        if (empty($data['meeting_time'])) {
            $errors['meeting_time'] = '會議時間為必填';
        }

        if (!empty($errors)) {
            throw new ValidationException('資料驗證失敗', $errors);
        }

        // 驗證會議類型
        if (!in_array($data['meeting_type'], Meeting::VALID_MEETING_TYPES)) {
            throw new ValidationException('會議類型必須為：' . implode('、', Meeting::VALID_MEETING_TYPES));
        }
    }
}
