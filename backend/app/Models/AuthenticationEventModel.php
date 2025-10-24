<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthenticationEventModel extends Model
{
    protected $table = 'authentication_events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'event_type',
        'user_id',
        'username_attempted',
        'ip_address',
        'user_agent',
        'failure_reason',
        'event_metadata',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    protected $validationRules = [
        'event_type' => 'required|in_list[login_success,login_failure,logout,token_refresh]',
        'ip_address' => 'permit_empty|max_length[45]',
        'user_agent' => 'permit_empty',
        'username_attempted' => 'permit_empty|max_length[100]',
        'failure_reason' => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * 取得特定 IP 位址的失敗登入記錄
     *
     * @param string $ipAddress IP 位址
     * @param int $minutes 時間範圍（分鐘）
     * @return array
     */
    public function getFailedLoginsByIP(string $ipAddress, int $minutes = 30): array
    {
        return $this->where('ip_address', $ipAddress)
            ->where('event_type', 'login_failure')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")))
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * 取得使用者的認證歷史記錄
     *
     * @param int $userId 使用者 ID
     * @param int $limit 記錄數量限制
     * @return array
     */
    public function getUserAuthHistory(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 刪除舊的認證事件（用於清理）
     *
     * @param int $days 保留天數
     * @return int 刪除的記錄數
     */
    public function deleteOldEvents(int $days = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $this->where('created_at <', $cutoffDate)->delete();

        return $this->db->affectedRows();
    }

    /**
     * 取得特定時間範圍內的事件統計
     *
     * @param string $eventType 事件類型（可選）
     * @param int $hours 時間範圍（小時）
     * @return array
     */
    public function getEventStats(string $eventType = null, int $hours = 24): array
    {
        $builder = $this->builder();
        $builder->select('event_type, COUNT(*) as count')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$hours} hours")))
            ->groupBy('event_type');

        if ($eventType) {
            $builder->where('event_type', $eventType);
        }

        return $builder->get()->getResultArray();
    }
}
