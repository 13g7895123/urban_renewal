<?php

namespace App\Entities;

/**
 * 會議實體
 * 
 * 封裝會議的核心業務資料與行為
 */
class Meeting
{
    private ?int $id = null;
    private int $urbanRenewalId;
    private string $meetingName;
    private string $meetingType;
    private string $meetingDate;
    private string $meetingTime;
    private ?string $meetingLocation = null;
    private int $attendeeCount = 0;
    private int $calculatedTotalCount = 0;
    private int $observerCount = 0;
    private ?int $quorumLandAreaNumerator = null;
    private ?int $quorumLandAreaDenominator = null;
    private ?float $quorumLandArea = null;
    private ?int $quorumBuildingAreaNumerator = null;
    private ?int $quorumBuildingAreaDenominator = null;
    private ?float $quorumBuildingArea = null;
    private ?int $quorumMemberNumerator = null;
    private ?int $quorumMemberDenominator = null;
    private ?int $quorumMemberCount = null;
    private string $meetingStatus = 'draft';
    
    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;

    // 有效的會議類型
    public const VALID_MEETING_TYPES = ['會員大會', '理事會', '監事會', '臨時會議'];

    // 有效的會議狀態
    public const VALID_STATUSES = ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'];

    // 有效的狀態轉換
    private const VALID_TRANSITIONS = [
        'draft' => ['scheduled', 'cancelled'],
        'scheduled' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => ['draft'],
    ];

    // 關聯資料
    private ?string $urbanRenewalName = null;
    private ?int $votingTopicsCount = null;
    private ?int $totalObservers = null;
    private array $observers = [];
    private array $votingTopics = [];

    // === 建構子 ===
    
    public function __construct(int $urbanRenewalId, string $meetingName, string $meetingType, string $meetingDate, string $meetingTime)
    {
        $this->urbanRenewalId = $urbanRenewalId;
        $this->setMeetingName($meetingName);
        $this->setMeetingType($meetingType);
        $this->meetingDate = $meetingDate;
        $this->meetingTime = $meetingTime;
    }

    // === 業務邏輯方法 ===
    
    /**
     * 檢查狀態轉換是否有效
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->meetingStatus] ?? []);
    }

    /**
     * 執行狀態轉換
     */
    public function transitionTo(string $newStatus): void
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "無法從狀態「{$this->meetingStatus}」變更為「{$newStatus}」"
            );
        }
        $this->meetingStatus = $newStatus;
    }

    /**
     * 檢查是否可以刪除
     */
    public function canBeDeleted(): bool
    {
        return !in_array($this->meetingStatus, ['in_progress', 'completed']);
    }

    /**
     * 檢查是否可以編輯
     */
    public function canBeEdited(): bool
    {
        return in_array($this->meetingStatus, ['draft', 'scheduled']);
    }

    /**
     * 檢查是否可以刷新投票人名單
     */
    public function canRefreshVoters(): bool
    {
        return in_array($this->meetingStatus, ['draft', 'scheduled']);
    }

    /**
     * 檢查會議是否進行中
     */
    public function isInProgress(): bool
    {
        return $this->meetingStatus === 'in_progress';
    }

    /**
     * 檢查會議是否已完成
     */
    public function isCompleted(): bool
    {
        return $this->meetingStatus === 'completed';
    }

    /**
     * 取得會議的完整日期時間
     */
    public function getDateTime(): \DateTime
    {
        return new \DateTime($this->meetingDate . ' ' . $this->meetingTime);
    }

    // === Getters ===
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrbanRenewalId(): int
    {
        return $this->urbanRenewalId;
    }

    public function getMeetingName(): string
    {
        return $this->meetingName;
    }

    public function getMeetingType(): string
    {
        return $this->meetingType;
    }

    public function getMeetingDate(): string
    {
        return $this->meetingDate;
    }

    public function getMeetingTime(): string
    {
        return $this->meetingTime;
    }

    public function getMeetingLocation(): ?string
    {
        return $this->meetingLocation;
    }

    public function getAttendeeCount(): int
    {
        return $this->attendeeCount;
    }

    public function getCalculatedTotalCount(): int
    {
        return $this->calculatedTotalCount;
    }

    public function getObserverCount(): int
    {
        return $this->observerCount;
    }

    public function getMeetingStatus(): string
    {
        return $this->meetingStatus;
    }

    public function getUrbanRenewalName(): ?string
    {
        return $this->urbanRenewalName;
    }

    public function getVotingTopicsCount(): ?int
    {
        return $this->votingTopicsCount;
    }

    public function getObservers(): array
    {
        return $this->observers;
    }

    public function getVotingTopics(): array
    {
        return $this->votingTopics;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // === Setters ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setMeetingName(string $name): self
    {
        $name = trim($name);
        if (empty($name)) {
            throw new \InvalidArgumentException('會議名稱不可為空');
        }
        if (mb_strlen($name) > 255) {
            throw new \InvalidArgumentException('會議名稱不可超過 255 字元');
        }
        $this->meetingName = $name;
        return $this;
    }

    public function setMeetingType(string $type): self
    {
        if (!in_array($type, self::VALID_MEETING_TYPES)) {
            throw new \InvalidArgumentException(
                '會議類型必須為：' . implode('、', self::VALID_MEETING_TYPES)
            );
        }
        $this->meetingType = $type;
        return $this;
    }

    public function setMeetingDate(string $date): self
    {
        $this->meetingDate = $date;
        return $this;
    }

    public function setMeetingTime(string $time): self
    {
        $this->meetingTime = $time;
        return $this;
    }

    public function setMeetingLocation(?string $location): self
    {
        $this->meetingLocation = $location ? trim($location) : null;
        return $this;
    }

    public function setAttendeeCount(int $count): self
    {
        $this->attendeeCount = max(0, $count);
        return $this;
    }

    public function setCalculatedTotalCount(int $count): self
    {
        $this->calculatedTotalCount = max(0, $count);
        return $this;
    }

    public function setObserverCount(int $count): self
    {
        $this->observerCount = max(0, $count);
        return $this;
    }

    public function setMeetingStatus(string $status): self
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException(
                '會議狀態必須為：' . implode(', ', self::VALID_STATUSES)
            );
        }
        $this->meetingStatus = $status;
        return $this;
    }

    public function setUrbanRenewalName(?string $name): self
    {
        $this->urbanRenewalName = $name;
        return $this;
    }

    public function setVotingTopicsCount(?int $count): self
    {
        $this->votingTopicsCount = $count;
        return $this;
    }

    public function setObservers(array $observers): self
    {
        $this->observers = $observers;
        return $this;
    }

    public function setVotingTopics(array $topics): self
    {
        $this->votingTopics = $topics;
        return $this;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setQuorumData(array $data): self
    {
        $this->quorumLandAreaNumerator = $data['quorum_land_area_numerator'] ?? null;
        $this->quorumLandAreaDenominator = $data['quorum_land_area_denominator'] ?? null;
        $this->quorumLandArea = $data['quorum_land_area'] ?? null;
        $this->quorumBuildingAreaNumerator = $data['quorum_building_area_numerator'] ?? null;
        $this->quorumBuildingAreaDenominator = $data['quorum_building_area_denominator'] ?? null;
        $this->quorumBuildingArea = $data['quorum_building_area'] ?? null;
        $this->quorumMemberNumerator = $data['quorum_member_numerator'] ?? null;
        $this->quorumMemberDenominator = $data['quorum_member_denominator'] ?? null;
        $this->quorumMemberCount = $data['quorum_member_count'] ?? null;
        return $this;
    }

    // === 序列化 ===
    
    /**
     * 轉換為陣列（供 API 回應使用）
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'urban_renewal_id' => $this->urbanRenewalId,
            'urban_renewal_name' => $this->urbanRenewalName,
            'meeting_name' => $this->meetingName,
            'meeting_type' => $this->meetingType,
            'meeting_date' => $this->meetingDate,
            'meeting_time' => $this->meetingTime,
            'meeting_location' => $this->meetingLocation,
            'attendee_count' => $this->attendeeCount,
            'calculated_total_count' => $this->calculatedTotalCount,
            'observer_count' => $this->observerCount,
            'quorum_land_area_numerator' => $this->quorumLandAreaNumerator,
            'quorum_land_area_denominator' => $this->quorumLandAreaDenominator,
            'quorum_land_area' => $this->quorumLandArea,
            'quorum_building_area_numerator' => $this->quorumBuildingAreaNumerator,
            'quorum_building_area_denominator' => $this->quorumBuildingAreaDenominator,
            'quorum_building_area' => $this->quorumBuildingArea,
            'quorum_member_numerator' => $this->quorumMemberNumerator,
            'quorum_member_denominator' => $this->quorumMemberDenominator,
            'quorum_member_count' => $this->quorumMemberCount,
            'meeting_status' => $this->meetingStatus,
            'voting_topics_count' => $this->votingTopicsCount,
            'total_observers' => $this->totalObservers ?? $this->observerCount,
            'observers' => $this->observers,
            'voting_topics' => $this->votingTopics,
            'can_be_edited' => $this->canBeEdited(),
            'can_be_deleted' => $this->canBeDeleted(),
            'can_refresh_voters' => $this->canRefreshVoters(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 從陣列建立實體
     */
    public static function fromArray(array $data): self
    {
        $entity = new self(
            (int)$data['urban_renewal_id'],
            $data['meeting_name'],
            $data['meeting_type'],
            $data['meeting_date'],
            $data['meeting_time']
        );
        
        if (isset($data['id'])) {
            $entity->setId((int)$data['id']);
        }
        
        $entity->setMeetingLocation($data['meeting_location'] ?? null);
        $entity->setAttendeeCount((int)($data['attendee_count'] ?? 0));
        $entity->setCalculatedTotalCount((int)($data['calculated_total_count'] ?? 0));
        $entity->setObserverCount((int)($data['observer_count'] ?? 0));
        
        if (isset($data['meeting_status'])) {
            $entity->setMeetingStatus($data['meeting_status']);
        }
        
        if (isset($data['urban_renewal_name'])) {
            $entity->setUrbanRenewalName($data['urban_renewal_name']);
        }
        
        if (isset($data['voting_topics_count'])) {
            $entity->setVotingTopicsCount((int)$data['voting_topics_count']);
        }
        
        $entity->setQuorumData($data);
        
        if (isset($data['observers'])) {
            $entity->setObservers($data['observers']);
        }
        
        if (isset($data['voting_topics'])) {
            $entity->setVotingTopics($data['voting_topics']);
        }
        
        if (isset($data['created_at'])) {
            $entity->setCreatedAt(new \DateTime($data['created_at']));
        }
        if (isset($data['updated_at'])) {
            $entity->setUpdatedAt(new \DateTime($data['updated_at']));
        }
        
        return $entity;
    }
}
