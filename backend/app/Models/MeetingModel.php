<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingModel extends Model
{
    protected $table = 'meetings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'urban_renewal_id',
        'meeting_name',
        'meeting_type',
        'meeting_date',
        'meeting_time',
        'meeting_location',
        'attendee_count',
        'calculated_total_count',
        'observer_count',
        'quorum_land_area_numerator',
        'quorum_land_area_denominator',
        'quorum_land_area',
        'quorum_building_area_numerator',
        'quorum_building_area_denominator',
        'quorum_building_area',
        'quorum_member_numerator',
        'quorum_member_denominator',
        'quorum_member_count',
        'meeting_status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'urban_renewal_id' => 'required|integer',
        'meeting_name' => 'required|max_length[255]',
        'meeting_type' => 'required|in_list[會員大會,理事會,監事會,臨時會議]',
        'meeting_date' => 'required|valid_date[Y-m-d]',
        'meeting_time' => 'required',
        'meeting_status' => 'permit_empty|in_list[draft,scheduled,in_progress,completed,cancelled]'
    ];

    protected $validationMessages = [
        'urban_renewal_id' => [
            'required' => '更新會ID為必填',
            'integer' => '更新會ID必須為數字'
        ],
        'meeting_name' => [
            'required' => '會議名稱為必填',
            'max_length' => '會議名稱不可超過255字元'
        ],
        'meeting_type' => [
            'required' => '會議類型為必填',
            'in_list' => '會議類型必須為：會員大會、理事會、監事會、臨時會議'
        ],
        'meeting_date' => [
            'required' => '會議日期為必填',
            'valid_date' => '請輸入有效的日期格式 (YYYY-MM-DD)'
        ],
        'meeting_time' => [
            'required' => '會議時間為必填'
        ],
        'meeting_status' => [
            'in_list' => '會議狀態必須為：draft, scheduled, in_progress, completed, cancelled'
        ]
    ];

    /**
     * 取得會議列表 (分頁)
     */
    public function getMeetings($page = 1, $perPage = 10, $filters = [])
    {
        $builder = $this->select('meetings.*, urban_renewals.name as urban_renewal_name')
                       ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                       ->where('meetings.deleted_at', null);

        // 篩選條件
        if (!empty($filters['urban_renewal_id'])) {
            $builder->where('meetings.urban_renewal_id', $filters['urban_renewal_id']);
        }

        if (!empty($filters['urban_renewal_ids'])) {
            $builder->whereIn('meetings.urban_renewal_id', $filters['urban_renewal_ids']);
        }

        if (!empty($filters['meeting_status'])) {
            $builder->where('meetings.meeting_status', $filters['meeting_status']);
        }

        if (!empty($filters['meeting_type'])) {
            $builder->where('meetings.meeting_type', $filters['meeting_type']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                   ->like('meetings.meeting_name', $filters['search'])
                   ->orLike('meetings.meeting_location', $filters['search'])
                   ->orLike('urban_renewals.name', $filters['search'])
                   ->groupEnd();
        }

        // 日期範圍篩選
        if (!empty($filters['date_from'])) {
            $builder->where('meetings.meeting_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('meetings.meeting_date <=', $filters['date_to']);
        }

        return $builder->orderBy('meetings.meeting_date', 'DESC')
                       ->orderBy('meetings.meeting_time', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得特定更新會的會議列表
     */
    public function getMeetingsByUrbanRenewal($urbanRenewalId, $page = 1, $perPage = 10, $status = null)
    {
        $builder = $this->select('meetings.*, urban_renewals.name as urban_renewal_name')
                       ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                       ->where('meetings.urban_renewal_id', $urbanRenewalId)
                       ->where('meetings.deleted_at', null);

        if ($status) {
            $builder->where('meetings.meeting_status', $status);
        }

        return $builder->orderBy('meetings.meeting_date', 'DESC')
                       ->orderBy('meetings.meeting_time', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得會議詳情（包含相關資料）
     */
    public function getMeetingWithDetails($meetingId)
    {
        $meeting = $this->select('meetings.*, urban_renewals.name as urban_renewal_name, urban_renewals.chairman_name, urban_renewals.chairman_phone')
                       ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                       ->where('meetings.id', $meetingId)
                       ->where('meetings.deleted_at', null)
                       ->first();

        if (!$meeting) {
            return null;
        }

        // 取得出席統計
        try {
            $attendanceModel = model('MeetingAttendanceModel');
            $meeting['attendance_summary'] = $attendanceModel->getDetailedAttendanceStatistics($meetingId);
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get attendance summary: ' . $e->getMessage());
            $meeting['attendance_summary'] = null;
        }

        // 取得投票議題數量
        try {
            $votingTopicModel = model('VotingTopicModel');
            $meeting['voting_topics_count'] = $votingTopicModel->where('meeting_id', $meetingId)->countAllResults();
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get voting topics count: ' . $e->getMessage());
            $meeting['voting_topics_count'] = 0;
        }

        // 取得列席者數量
        try {
            // MeetingObserverModel may not exist, handle gracefully
            if (class_exists('App\Models\MeetingObserverModel')) {
                $observerModel = model('MeetingObserverModel');
                $meeting['observers_count'] = $observerModel->where('meeting_id', $meetingId)->countAllResults();
            } else {
                $meeting['observers_count'] = 0;
            }
        } catch (\Exception $e) {
            log_message('warning', 'Failed to get observers count: ' . $e->getMessage());
            $meeting['observers_count'] = 0;
        }

        return $meeting;
    }

    /**
     * 檢查會議時間衝突
     */
    public function checkMeetingConflict($urbanRenewalId, $meetingDate, $meetingTime, $excludeId = null)
    {
        $builder = $this->where('urban_renewal_id', $urbanRenewalId)
                       ->where('meeting_date', $meetingDate)
                       ->where('meeting_time', $meetingTime)
                       ->where('meeting_status !=', 'cancelled')
                       ->where('deleted_at', null);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first();
    }

    /**
     * 取得會議統計資料
     */
    public function getMeetingStatistics($meetingId)
    {
        $meeting = $this->find($meetingId);
        if (!$meeting) {
            return null;
        }

        $statistics = [
            'meeting_info' => $meeting,
            'attendance' => [],
            'voting_topics' => [],
            'quorum_status' => []
        ];

        // 出席統計
        $attendanceModel = model('MeetingAttendanceModel');
        $statistics['attendance'] = $attendanceModel->getDetailedAttendanceStatistics($meetingId);

        // 投票議題統計
        $votingTopicModel = model('VotingTopicModel');
        $statistics['voting_topics'] = $votingTopicModel->getTopicsByMeeting($meetingId);

        // 成會門檻檢查
        $statistics['quorum_status'] = $this->checkQuorumStatus($meetingId);

        return $statistics;
    }

    /**
     * 檢查成會門檻
     */
    public function checkQuorumStatus($meetingId)
    {
        $meeting = $this->find($meetingId);
        if (!$meeting) {
            return null;
        }

        $attendanceModel = model('MeetingAttendanceModel');
        $attendance = $attendanceModel->getAttendanceSummary($meetingId);

        // 取得總數據（所有應出席的所有權人）
        $propertyOwnerModel = model('PropertyOwnerModel');
        $totalStats = $propertyOwnerModel->getUrbanRenewalStatistics($meeting['urban_renewal_id']);

        $quorumStatus = [
            'land_area' => [
                'required' => $meeting['quorum_land_area'],
                'current' => $attendance['total_land_area'],
                'percentage' => $totalStats['total_land_area'] > 0 ?
                              ($attendance['total_land_area'] / $totalStats['total_land_area']) * 100 : 0,
                'threshold' => $meeting['quorum_land_area_numerator'] / $meeting['quorum_land_area_denominator'] * 100,
                'met' => false
            ],
            'building_area' => [
                'required' => $meeting['quorum_building_area'],
                'current' => $attendance['total_building_area'],
                'percentage' => $totalStats['total_building_area'] > 0 ?
                              ($attendance['total_building_area'] / $totalStats['total_building_area']) * 100 : 0,
                'threshold' => $meeting['quorum_building_area_numerator'] / $meeting['quorum_building_area_denominator'] * 100,
                'met' => false
            ],
            'member_count' => [
                'required' => $meeting['quorum_member_count'],
                'current' => $attendance['present_count'] + $attendance['proxy_count'],
                'percentage' => $totalStats['total_owners'] > 0 ?
                              (($attendance['present_count'] + $attendance['proxy_count']) / $totalStats['total_owners']) * 100 : 0,
                'threshold' => $meeting['quorum_member_numerator'] / $meeting['quorum_member_denominator'] * 100,
                'met' => false
            ]
        ];

        // 檢查是否達到門檻
        $quorumStatus['land_area']['met'] = $quorumStatus['land_area']['percentage'] >= $quorumStatus['land_area']['threshold'];
        $quorumStatus['building_area']['met'] = $quorumStatus['building_area']['percentage'] >= $quorumStatus['building_area']['threshold'];
        $quorumStatus['member_count']['met'] = $quorumStatus['member_count']['percentage'] >= $quorumStatus['member_count']['threshold'];

        // 整體成會狀態
        $quorumStatus['overall_met'] = $quorumStatus['land_area']['met'] &&
                                      $quorumStatus['building_area']['met'] &&
                                      $quorumStatus['member_count']['met'];

        return $quorumStatus;
    }

    /**
     * 搜尋會議
     */
    public function searchMeetings($keyword, $page = 1, $perPage = 10)
    {
        return $this->select('meetings.*, urban_renewals.name as urban_renewal_name')
                   ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                   ->where('meetings.deleted_at', null)
                   ->groupStart()
                   ->like('meetings.meeting_name', $keyword)
                   ->orLike('meetings.meeting_location', $keyword)
                   ->orLike('urban_renewals.name', $keyword)
                   ->groupEnd()
                   ->orderBy('meetings.meeting_date', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得會議狀態統計
     */
    public function getMeetingStatusStatistics($urbanRenewalId = null)
    {
        $builder = $this->select('meeting_status, COUNT(*) as count')
                       ->where('deleted_at', null);

        if ($urbanRenewalId) {
            $builder->where('urban_renewal_id', $urbanRenewalId);
        }

        return $builder->groupBy('meeting_status')->findAll();
    }

    /**
     * 取得即將舉行的會議
     */
    public function getUpcomingMeetings($urbanRenewalId = null, $days = 30)
    {
        $builder = $this->select('meetings.*, urban_renewals.name as urban_renewal_name')
                       ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                       ->where('meetings.deleted_at', null)
                       ->where('meetings.meeting_date >=', date('Y-m-d'))
                       ->where('meetings.meeting_date <=', date('Y-m-d', strtotime("+{$days} days")))
                       ->whereIn('meetings.meeting_status', ['scheduled', 'draft']);

        if ($urbanRenewalId) {
            $builder->where('meetings.urban_renewal_id', $urbanRenewalId);
        }

        return $builder->orderBy('meetings.meeting_date', 'ASC')
                       ->orderBy('meetings.meeting_time', 'ASC')
                       ->findAll();
    }

    /**
     * 取得會議類型統計
     */
    public function getMeetingTypeStatistics($urbanRenewalId = null)
    {
        $builder = $this->select('meeting_type, COUNT(*) as count')
                       ->where('deleted_at', null);

        if ($urbanRenewalId) {
            $builder->where('urban_renewal_id', $urbanRenewalId);
        }

        return $builder->groupBy('meeting_type')->findAll();
    }

    /**
     * 更新會議出席統計
     */
    public function updateAttendanceStatistics($meetingId)
    {
        $attendanceModel = model('MeetingAttendanceModel');
        $summary = $attendanceModel->getAttendanceSummary($meetingId);

        return $this->update($meetingId, [
            'attendee_count' => $summary['present_count'] + $summary['proxy_count'],
            'calculated_total_count' => $summary['calculated_count'],
            'observer_count' => $summary['observer_count'] ?? 0
        ]);
    }
}