<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingAttendanceModel extends Model
{
    protected $table = 'meeting_attendances';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'meeting_id',
        'property_owner_id',
        'attendance_type',
        'proxy_person',
        'check_in_time',
        'is_calculated',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'meeting_id' => 'required|integer',
        'property_owner_id' => 'required|integer',
        'attendance_type' => 'required|in_list[present,proxy,absent]',
        'proxy_person' => 'permit_empty|max_length[100]',
        'is_calculated' => 'permit_empty|in_list[0,1]',
        'notes' => 'permit_empty'
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
        'attendance_type' => [
            'required' => '出席類型為必填',
            'in_list' => '出席類型必須為：present (親自出席)、proxy (委託出席)、absent (未出席)'
        ],
        'proxy_person' => [
            'max_length' => '代理人姓名不可超過100字元'
        ]
    ];

    /**
     * 取得會議出席記錄列表 (分頁)
     */
    public function getMeetingAttendances($meetingId, $page = 1, $perPage = 50, $filters = [])
    {
        $builder = $this->db->table($this->table . ' ma');
        $builder->select('ma.*, po.name as owner_name, po.owner_code, po.exclusion_type');
        $builder->join('property_owners po', 'po.id = ma.property_owner_id', 'left');
        $builder->where('ma.meeting_id', $meetingId);

        // 應用篩選條件
        if (!empty($filters['attendance_type'])) {
            $builder->where('ma.attendance_type', $filters['attendance_type']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart();
            $builder->like('po.name', $filters['search']);
            $builder->orLike('po.owner_code', $filters['search']);
            $builder->orLike('ma.proxy_person', $filters['search']);
            $builder->groupEnd();
        }

        $builder->orderBy('po.owner_code', 'ASC');

        // 計算總數
        $total = $builder->countAllResults(false);

        // 分頁
        $offset = ($page - 1) * $perPage;
        $builder->limit($perPage, $offset);

        $results = $builder->get()->getResultArray();

        // 設定分頁資訊
        $this->pager = \Config\Services::pager();
        $this->pager->store('default', $page, $perPage, $total);

        return $results;
    }

    /**
     * 取得單筆出席記錄並包含所有權人資訊
     */
    public function getAttendanceWithOwnerInfo($attendanceId)
    {
        $builder = $this->db->table($this->table . ' ma');
        $builder->select('ma.*, po.name as owner_name, po.owner_code, po.exclusion_type');
        $builder->join('property_owners po', 'po.id = ma.property_owner_id', 'left');
        $builder->where('ma.id', $attendanceId);

        return $builder->get()->getRowArray();
    }

    /**
     * 取得會議出席統計
     */
    public function getDetailedAttendanceStatistics($meetingId)
    {
        $builder = $this->db->table($this->table);
        $builder->select('
            COUNT(*) as total_count,
            SUM(CASE WHEN attendance_type = "present" THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN attendance_type = "proxy" THEN 1 ELSE 0 END) as proxy_count,
            SUM(CASE WHEN attendance_type = "absent" THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN attendance_type IN ("present", "proxy") THEN 1 ELSE 0 END) as attended_count,
            SUM(CASE WHEN is_calculated = 1 THEN 1 ELSE 0 END) as calculated_count,
            SUM(CASE WHEN is_calculated = 1 AND attendance_type IN ("present", "proxy") THEN 1 ELSE 0 END) as calculated_attended_count
        ');
        $builder->where('meeting_id', $meetingId);

        return $builder->get()->getRowArray();
    }

    /**
     * 取得會議出席摘要 (getDetailedAttendanceStatistics 的別名)
     */
    public function getAttendanceSummary($meetingId)
    {
        return $this->getDetailedAttendanceStatistics($meetingId);
    }

    /**
     * 取得會議的所有出席記錄（不分頁，用於匯出）
     */
    public function getAllMeetingAttendances($meetingId)
    {
        $builder = $this->db->table($this->table . ' ma');
        $builder->select('ma.*, po.name as owner_name, po.owner_code, po.exclusion_type');
        $builder->join('property_owners po', 'po.id = ma.property_owner_id', 'left');
        $builder->where('ma.meeting_id', $meetingId);
        $builder->orderBy('po.owner_code', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * 檢查出席記錄是否存在
     */
    public function checkAttendanceExists($meetingId, $propertyOwnerId)
    {
        return $this->where('meeting_id', $meetingId)
                    ->where('property_owner_id', $propertyOwnerId)
                    ->first();
    }

    /**
     * 批次建立或更新出席記錄
     */
    public function upsertAttendance($data)
    {
        $existing = $this->checkAttendanceExists($data['meeting_id'], $data['property_owner_id']);

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * 取得會議出席面積統計 (包含人數與面積)
     */
    public function getAreaStatistics($meetingId)
    {
        // 1. 取得應出席總數 (從 meeting_eligible_voters)
        $voterBuilder = $this->db->table('meeting_eligible_voters');
        $voterBuilder->select('
            COUNT(*) as total_count,
            SUM(land_area_weight) as total_land_area,
            SUM(building_area_weight) as total_building_area
        ');
        $voterBuilder->where('meeting_id', $meetingId);
        $totalStats = $voterBuilder->get()->getRowArray();

        // 2. 取得出席統計 (從 meeting_attendances join meeting_eligible_voters)
        $attendanceBuilder = $this->db->table('meeting_attendances ma');
        $attendanceBuilder->select('
            SUM(CASE WHEN ma.attendance_type = "present" THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN ma.attendance_type = "proxy" THEN 1 ELSE 0 END) as proxy_count,
            SUM(CASE WHEN ma.attendance_type = "present" THEN mev.land_area_weight ELSE 0 END) as present_land_area,
            SUM(CASE WHEN ma.attendance_type = "proxy" THEN mev.land_area_weight ELSE 0 END) as proxy_land_area,
            SUM(CASE WHEN ma.attendance_type = "present" THEN mev.building_area_weight ELSE 0 END) as present_building_area,
            SUM(CASE WHEN ma.attendance_type = "proxy" THEN mev.building_area_weight ELSE 0 END) as proxy_building_area
        ');
        $attendanceBuilder->join('meeting_eligible_voters mev', 'mev.meeting_id = ma.meeting_id AND mev.property_owner_id = ma.property_owner_id', 'left');
        $attendanceBuilder->where('ma.meeting_id', $meetingId);
        // 排除不納入計算的
        $attendanceBuilder->where('ma.is_calculated', 1);
        
        $attendanceStats = $attendanceBuilder->get()->getRowArray();

        // 合併結果
        $result = array_merge(
            $totalStats ?? ['total_count' => 0, 'total_land_area' => 0, 'total_building_area' => 0],
            $attendanceStats ?? ['present_count' => 0, 'proxy_count' => 0, 'present_land_area' => 0, 'proxy_land_area' => 0, 'present_building_area' => 0, 'proxy_building_area' => 0]
        );

        // 確保數值為數字類型
        foreach ($result as $key => $value) {
            $result[$key] = (float)$value;
        }

        return $result;
    }

    /**
     * 取得會議的出席率
     */
    public function getAttendanceRate($meetingId)
    {
        $stats = $this->getDetailedAttendanceStatistics($meetingId);
        
        if ($stats['total_count'] == 0) {
            return 0;
        }

        return round(($stats['attended_count'] / $stats['total_count']) * 100, 2);
    }

    /**
     * 取得會議的計算出席率（排除不納入計算的）
     */
    public function getCalculatedAttendanceRate($meetingId)
    {
        $stats = $this->getDetailedAttendanceStatistics($meetingId);
        
        if ($stats['calculated_count'] == 0) {
            return 0;
        }

        return round(($stats['calculated_attended_count'] / $stats['calculated_count']) * 100, 2);
    }
}
