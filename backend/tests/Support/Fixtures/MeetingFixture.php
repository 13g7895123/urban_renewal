<?php

namespace Tests\Support\Fixtures;

use App\Models\MeetingModel;

/**
 * Meeting Test Fixture
 * 提供測試用會議資料
 */
class MeetingFixture
{
    protected $meetingModel;
    protected $createdMeetings = [];

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
    }

    /**
     * 建立基本會議
     */
    public function create(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        $timestamp = time();
        $random = rand(1000, 9999);

        $data = array_merge([
            'urban_renewal_id' => $urbanRenewalId,
            'meeting_type' => 'general',
            'title' => "測試會議_{$timestamp}_{$random}",
            'description' => '這是一個測試用的會議',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'location' => '台北市中正區會議室',
            'status' => 'scheduled',
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);

        $id = $this->meetingModel->insert($data);
        $meeting = $this->meetingModel->find($id);
        $this->createdMeetings[] = $id;

        return $meeting;
    }

    /**
     * 建立一般會議
     */
    public function createGeneral(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'meeting_type' => 'general',
            'title' => '一般會議_' . time()
        ], $overrides));
    }

    /**
     * 建立臨時會議
     */
    public function createSpecial(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'meeting_type' => 'special',
            'title' => '臨時會議_' . time()
        ], $overrides));
    }

    /**
     * 建立年度會議
     */
    public function createAnnual(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'meeting_type' => 'annual',
            'title' => '年度會議_' . time()
        ], $overrides));
    }

    /**
     * 建立已排程的會議
     */
    public function createScheduled(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'status' => 'scheduled',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'title' => '已排程會議_' . time()
        ], $overrides));
    }

    /**
     * 建立進行中的會議
     */
    public function createInProgress(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'status' => 'in_progress',
            'scheduled_at' => date('Y-m-d H:i:s'),
            'title' => '進行中會議_' . time()
        ], $overrides));
    }

    /**
     * 建立已完成的會議
     */
    public function createCompleted(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'status' => 'completed',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'title' => '已完成會議_' . time()
        ], $overrides));
    }

    /**
     * 建立已取消的會議
     */
    public function createCancelled(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'status' => 'cancelled',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'title' => '已取消會議_' . time()
        ], $overrides));
    }

    /**
     * 建立多個不同狀態的會議
     */
    public function createMultipleWithStatuses(int $urbanRenewalId, int $createdBy): array
    {
        return [
            'scheduled' => $this->createScheduled($urbanRenewalId, $createdBy),
            'in_progress' => $this->createInProgress($urbanRenewalId, $createdBy),
            'completed' => $this->createCompleted($urbanRenewalId, $createdBy),
            'cancelled' => $this->createCancelled($urbanRenewalId, $createdBy)
        ];
    }

    /**
     * 建立多個不同類型的會議
     */
    public function createMultipleWithTypes(int $urbanRenewalId, int $createdBy): array
    {
        return [
            'general' => $this->createGeneral($urbanRenewalId, $createdBy),
            'special' => $this->createSpecial($urbanRenewalId, $createdBy),
            'annual' => $this->createAnnual($urbanRenewalId, $createdBy)
        ];
    }

    /**
     * 建立指定數量的會議
     */
    public function createMany(int $count, int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        $meetings = [];

        for ($i = 0; $i < $count; $i++) {
            $meetings[] = $this->create($urbanRenewalId, $createdBy, array_merge([
                'title' => "測試會議_{$i}_" . time(),
                'scheduled_at' => date('Y-m-d H:i:s', strtotime("+{$i} days"))
            ], $overrides));
        }

        return $meetings;
    }

    /**
     * 建立跨更新地區測試場景
     * 用於測試資源範圍存取控制
     */
    public function createCrossUrbanRenewalScenario(array $urbanRenewalIds, int $createdBy): array
    {
        $meetings = [];

        foreach ($urbanRenewalIds as $index => $urbanRenewalId) {
            $meetings["urban_renewal_{$urbanRenewalId}"] = [
                'meeting_1' => $this->create($urbanRenewalId, $createdBy, [
                    'title' => "更新地區{$urbanRenewalId}_會議1"
                ]),
                'meeting_2' => $this->create($urbanRenewalId, $createdBy, [
                    'title' => "更新地區{$urbanRenewalId}_會議2"
                ])
            ];
        }

        return $meetings;
    }

    /**
     * 建立包含詳細資訊的會議
     */
    public function createWithDetails(int $urbanRenewalId, int $createdBy, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'meeting_type' => 'general',
            'title' => '詳細資訊會議_' . time(),
            'description' => '這是一個包含完整詳細資訊的會議，用於測試各項會議功能。會議將討論重要議題並進行投票表決。',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => '台北市中正區重慶南路一段122號3樓會議室',
            'attendee_count' => 50,
            'quorum_required' => 26 // 過半數
        ], $overrides));
    }

    /**
     * 建立過去的會議（用於歷史記錄測試）
     */
    public function createPast(int $urbanRenewalId, int $createdBy, int $daysAgo = 7, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'status' => 'completed',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")),
            'title' => "{$daysAgo}天前會議_" . time()
        ], $overrides));
    }

    /**
     * 建立未來的會議（用於排程測試）
     */
    public function createFuture(int $urbanRenewalId, int $createdBy, int $daysAhead = 7, array $overrides = []): array
    {
        return $this->create($urbanRenewalId, $createdBy, array_merge([
            'status' => 'scheduled',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime("+{$daysAhead} days")),
            'title' => "{$daysAhead}天後會議_" . time()
        ], $overrides));
    }

    /**
     * 取得已建立的會議 ID 列表
     */
    public function getCreatedIds(): array
    {
        return $this->createdMeetings;
    }

    /**
     * 清理所有建立的測試會議
     */
    public function cleanup(): void
    {
        foreach ($this->createdMeetings as $id) {
            $this->meetingModel->delete($id);
        }
        $this->createdMeetings = [];
    }

    /**
     * Destructor - 自動清理
     */
    public function __destruct()
    {
        $this->cleanup();
    }
}
