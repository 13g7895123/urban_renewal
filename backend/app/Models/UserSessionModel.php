<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'session_token',
        'refresh_token',
        'expires_at',
        'refresh_expires_at',
        'ip_address',
        'user_agent',
        'device_info',
        'is_active',
        'created_at',
        'last_activity_at'
    ];

    protected $useTimestamps = false; // 手動控制時間戳
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'session_token' => 'required|max_length[255]',
        'expires_at' => 'required'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => '使用者ID為必填',
            'integer' => '使用者ID必須為數字'
        ],
        'session_token' => [
            'required' => 'Session token為必填',
            'max_length' => 'Session token長度不可超過255字元'
        ],
        'expires_at' => [
            'required' => '過期時間為必填'
        ]
    ];

    /**
     * 清理過期的 sessions
     */
    public function cleanExpiredSessions()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->orWhere('refresh_expires_at <', date('Y-m-d H:i:s'))
                   ->set(['is_active' => 0])
                   ->update();
    }

    /**
     * 取得活躍的使用者 session
     */
    public function getActiveSession($token)
    {
        return $this->where('session_token', $token)
                   ->where('is_active', 1)
                   ->where('expires_at >', date('Y-m-d H:i:s'))
                   ->first();
    }

    /**
     * 更新 session 活動時間
     */
    public function updateActivity($sessionId)
    {
        return $this->update($sessionId, [
            'last_activity_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 取得使用者的所有活躍 sessions
     */
    public function getUserActiveSessions($userId)
    {
        return $this->where('user_id', $userId)
                   ->where('is_active', 1)
                   ->where('expires_at >', date('Y-m-d H:i:s'))
                   ->findAll();
    }

    /**
     * 登出使用者的所有 sessions
     */
    public function logoutAllUserSessions($userId)
    {
        return $this->where('user_id', $userId)
                   ->set(['is_active' => 0])
                   ->update();
    }
}