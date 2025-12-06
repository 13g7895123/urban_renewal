<?php

namespace App\Entities;

/**
 * 投票議題實體
 */
class VotingTopic
{
    private ?int $id = null;
    private int $meetingId;
    private string $topicTitle;
    private ?string $topicDescription = null;
    private string $topicType = 'general';
    private int $sortOrder = 0;
    private bool $isAnonymous = false;
    private string $status = 'pending';
    private ?int $passThreshold = null;
    private ?string $passCondition = null;
    
    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;

    // 投票結果
    private ?int $approveCount = null;
    private ?int $rejectCount = null;
    private ?int $abstainCount = null;
    private ?float $approveLandArea = null;
    private ?float $rejectLandArea = null;
    private ?float $abstainLandArea = null;

    // 有效的議題類型
    public const VALID_TOPIC_TYPES = ['general', 'special', 'election'];
    
    // 有效的狀態
    public const VALID_STATUSES = ['pending', 'voting', 'closed'];

    public function __construct(int $meetingId, string $topicTitle)
    {
        $this->meetingId = $meetingId;
        $this->setTopicTitle($topicTitle);
    }

    // === 業務邏輯方法 ===

    /**
     * 檢查是否可以開始投票
     */
    public function canStartVoting(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * 檢查是否可以結束投票
     */
    public function canCloseVoting(): bool
    {
        return $this->status === 'voting';
    }

    /**
     * 檢查投票是否通過
     */
    public function isPassed(): bool
    {
        if ($this->status !== 'closed' || $this->approveCount === null) {
            return false;
        }

        $totalVotes = $this->approveCount + $this->rejectCount + $this->abstainCount;
        if ($totalVotes === 0) {
            return false;
        }

        $approveRate = ($this->approveCount / $totalVotes) * 100;
        return $approveRate >= ($this->passThreshold ?? 50);
    }

    // === Getters ===
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeetingId(): int
    {
        return $this->meetingId;
    }

    public function getTopicTitle(): string
    {
        return $this->topicTitle;
    }

    public function getTopicDescription(): ?string
    {
        return $this->topicDescription;
    }

    public function getTopicType(): string
    {
        return $this->topicType;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPassThreshold(): ?int
    {
        return $this->passThreshold;
    }

    // === Setters ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setTopicTitle(string $title): self
    {
        $title = trim($title);
        if (empty($title)) {
            throw new \InvalidArgumentException('議題標題不可為空');
        }
        $this->topicTitle = $title;
        return $this;
    }

    public function setTopicDescription(?string $description): self
    {
        $this->topicDescription = $description ? trim($description) : null;
        return $this;
    }

    public function setTopicType(string $type): self
    {
        if (!in_array($type, self::VALID_TOPIC_TYPES)) {
            throw new \InvalidArgumentException('無效的議題類型');
        }
        $this->topicType = $type;
        return $this;
    }

    public function setSortOrder(int $order): self
    {
        $this->sortOrder = $order;
        return $this;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;
        return $this;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('無效的議題狀態');
        }
        $this->status = $status;
        return $this;
    }

    public function setPassThreshold(?int $threshold): self
    {
        $this->passThreshold = $threshold;
        return $this;
    }

    public function setPassCondition(?string $condition): self
    {
        $this->passCondition = $condition;
        return $this;
    }

    public function setVotingResults(array $results): self
    {
        $this->approveCount = $results['approve_count'] ?? null;
        $this->rejectCount = $results['reject_count'] ?? null;
        $this->abstainCount = $results['abstain_count'] ?? null;
        $this->approveLandArea = $results['approve_land_area'] ?? null;
        $this->rejectLandArea = $results['reject_land_area'] ?? null;
        $this->abstainLandArea = $results['abstain_land_area'] ?? null;
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

    /**
     * 轉換為陣列
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'meeting_id' => $this->meetingId,
            'topic_title' => $this->topicTitle,
            'topic_description' => $this->topicDescription,
            'topic_type' => $this->topicType,
            'sort_order' => $this->sortOrder,
            'is_anonymous' => $this->isAnonymous,
            'status' => $this->status,
            'pass_threshold' => $this->passThreshold,
            'pass_condition' => $this->passCondition,
            'approve_count' => $this->approveCount,
            'reject_count' => $this->rejectCount,
            'abstain_count' => $this->abstainCount,
            'approve_land_area' => $this->approveLandArea,
            'reject_land_area' => $this->rejectLandArea,
            'abstain_land_area' => $this->abstainLandArea,
            'is_passed' => $this->isPassed(),
            'can_start_voting' => $this->canStartVoting(),
            'can_close_voting' => $this->canCloseVoting(),
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
            (int)$data['meeting_id'],
            $data['topic_title']
        );
        
        if (isset($data['id'])) $entity->setId((int)$data['id']);
        
        $entity->setTopicDescription($data['topic_description'] ?? null);
        if (isset($data['topic_type'])) $entity->setTopicType($data['topic_type']);
        if (isset($data['sort_order'])) $entity->setSortOrder((int)$data['sort_order']);
        if (isset($data['is_anonymous'])) $entity->setIsAnonymous((bool)$data['is_anonymous']);
        if (isset($data['status'])) $entity->setStatus($data['status']);
        if (isset($data['pass_threshold'])) $entity->setPassThreshold((int)$data['pass_threshold']);
        if (isset($data['pass_condition'])) $entity->setPassCondition($data['pass_condition']);
        
        $entity->setVotingResults($data);
        
        if (isset($data['created_at'])) $entity->setCreatedAt(new \DateTime($data['created_at']));
        if (isset($data['updated_at'])) $entity->setUpdatedAt(new \DateTime($data['updated_at']));
        
        return $entity;
    }
}
