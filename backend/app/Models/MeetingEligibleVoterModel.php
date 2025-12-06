<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 會議合格投票人快照 Model
 * 
 * 用於記錄會議建立當下的合格投票人名單，
 * 確保投票時以會議建立時的名單為準，不受後續產權異動影響。
 */
class MeetingEligibleVoterModel extends Model
{
    protected $table = 'meeting_eligible_voters';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'meeting_id',
        'property_owner_id',
        'owner_name',
        'owner_code',
        'id_number',
        'land_area_weight',
        'building_area_weight',
        'snapshot_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'meeting_id' => 'required|integer',
        'property_owner_id' => 'required|integer',
        'owner_name' => 'required|max_length[100]',
        'owner_code' => 'permit_empty|max_length[20]',
        'id_number' => 'permit_empty|max_length[20]',
        'land_area_weight' => 'permit_empty|decimal',
        'building_area_weight' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'meeting_id' => [
            'required' => '會議ID為必填',
            'integer' => '會議ID必須為數字'
        ],
        'property_owner_id' => [
            'required' => '所有權人ID為必填',
            'integer' => '所有權人ID必須為數字'
        ],
        'owner_name' => [
            'required' => '所有權人姓名為必填',
            'max_length' => '所有權人姓名不可超過100字元'
        ]
    ];

    /**
     * 為會議建立合格投票人快照
     * 
     * @param int $meetingId 會議ID
     * @param int $urbanRenewalId 都市更新會ID
     * @return array 建立結果
     */
    public function createSnapshot(int $meetingId, int $urbanRenewalId): array
    {
        $propertyOwnerModel = model('PropertyOwnerModel');
        
        // 取得該更新會所有「納入計算」的所有權人（排除 exclusion_type 不為空者）
        $eligibleOwners = $propertyOwnerModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->where('exclusion_type IS NULL')
            ->findAll();

        $snapshotTime = date('Y-m-d H:i:s');
        $successCount = 0;
        $errors = [];

        foreach ($eligibleOwners as $owner) {
            // 計算該所有權人的土地與建物面積權重
            $areas = $propertyOwnerModel->calculateTotalAreas($owner['id']);

            $data = [
                'meeting_id' => $meetingId,
                'property_owner_id' => $owner['id'],
                'owner_name' => $owner['name'],
                'owner_code' => $owner['owner_code'] ?? null,
                'id_number' => $owner['id_number'] ?? null,
                'land_area_weight' => $areas['total_land_area'] ?? 0,
                'building_area_weight' => $areas['total_building_area'] ?? 0,
                'snapshot_at' => $snapshotTime
            ];

            try {
                $this->insert($data);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'property_owner_id' => $owner['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => empty($errors),
            'total_eligible' => count($eligibleOwners),
            'snapshot_count' => $successCount,
            'errors' => $errors,
            'snapshot_at' => $snapshotTime
        ];
    }

    /**
     * 取得會議的合格投票人快照列表
     * 
     * @param int $meetingId 會議ID
     * @return array
     */
    public function getByMeetingId(int $meetingId): array
    {
        return $this->where('meeting_id', $meetingId)
                    ->orderBy('owner_code', 'ASC')
                    ->findAll();
    }

    /**
     * 取得會議的合格投票人快照（含分頁）
     * 
     * @param int $meetingId 會議ID
     * @param int $page 頁碼
     * @param int $perPage 每頁筆數
     * @return array
     */
    public function getByMeetingIdPaginated(int $meetingId, int $page = 1, int $perPage = 50): array
    {
        return $this->where('meeting_id', $meetingId)
                    ->orderBy('owner_code', 'ASC')
                    ->paginate($perPage, 'default', $page);
    }

    /**
     * 檢查會議是否已有快照
     * 
     * @param int $meetingId 會議ID
     * @return bool
     */
    public function hasSnapshot(int $meetingId): bool
    {
        return $this->where('meeting_id', $meetingId)->countAllResults() > 0;
    }

    /**
     * 取得會議快照的統計資料
     * 
     * @param int $meetingId 會議ID
     * @return array
     */
    public function getSnapshotStatistics(int $meetingId): array
    {
        $result = $this->select('
                COUNT(*) as total_voters,
                SUM(land_area_weight) as total_land_area,
                SUM(building_area_weight) as total_building_area,
                MIN(snapshot_at) as snapshot_at
            ')
            ->where('meeting_id', $meetingId)
            ->first();

        return [
            'total_voters' => (int)($result['total_voters'] ?? 0),
            'total_land_area' => (float)($result['total_land_area'] ?? 0),
            'total_building_area' => (float)($result['total_building_area'] ?? 0),
            'snapshot_at' => $result['snapshot_at'] ?? null
        ];
    }

    /**
     * 刪除會議的快照
     * 
     * @param int $meetingId 會議ID
     * @return bool
     */
    public function deleteByMeetingId(int $meetingId): bool
    {
        return $this->where('meeting_id', $meetingId)->delete();
    }

    /**
     * 檢查特定所有權人是否在快照中
     * 
     * @param int $meetingId 會議ID
     * @param int $propertyOwnerId 所有權人ID
     * @return array|null
     */
    public function getVoterSnapshot(int $meetingId, int $propertyOwnerId): ?array
    {
        return $this->where('meeting_id', $meetingId)
                    ->where('property_owner_id', $propertyOwnerId)
                    ->first();
    }

    /**
     * 重新建立會議快照（先刪除舊的再建立新的）
     * 
     * @param int $meetingId 會議ID
     * @param int $urbanRenewalId 都市更新會ID
     * @return array
     */
    public function recreateSnapshot(int $meetingId, int $urbanRenewalId): array
    {
        // 開始交易
        $db = \Config\Database::connect();
        $db->transStart();

        // 刪除舊快照
        $this->deleteByMeetingId($meetingId);

        // 建立新快照
        $result = $this->createSnapshot($meetingId, $urbanRenewalId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return [
                'success' => false,
                'error' => '快照建立失敗，交易已回滾'
            ];
        }

        return $result;
    }
}
