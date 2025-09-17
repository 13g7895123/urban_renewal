<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'notification_type',
        'priority',
        'title',
        'message',
        'related_type',
        'related_id',
        'action_url',
        'action_text',
        'metadata',
        'is_read',
        'read_at',
        'is_global',
        'urban_renewal_id',
        'expires_at',
        'send_email',
        'email_sent_at',
        'send_sms',
        'sms_sent_at',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'notification_type' => 'required|in_list[meeting_notice,meeting_reminder,voting_start,voting_end,voting_reminder,check_in_reminder,system_maintenance,user_account,document_upload,report_ready,permission_changed,system_alert]',
        'priority' => 'required|in_list[low,normal,high,urgent]',
        'title' => 'required|max_length[255]',
        'message' => 'required'
    ];

    protected $validationMessages = [
        'notification_type' => [
            'required' => '通知類型為必填',
            'in_list' => '通知類型無效'
        ],
        'priority' => [
            'required' => '優先級為必填',
            'in_list' => '優先級必須為：low, normal, high, urgent'
        ],
        'title' => [
            'required' => '標題為必填',
            'max_length' => '標題不可超過255字元'
        ],
        'message' => [
            'required' => '訊息內容為必填'
        ]
    ];

    /**
     * 取得使用者通知列表
     */
    public function getUserNotifications($userId, $page = 1, $perPage = 10, $filters = [])
    {
        $builder = $this->where('user_id', $userId)
                       ->orWhere('is_global', 1)
                       ->where('deleted_at', null);

        // 過期篩選
        $builder->groupStart()
               ->where('expires_at IS NULL')
               ->orWhere('expires_at >', date('Y-m-d H:i:s'))
               ->groupEnd();

        // 篩選條件
        if (!empty($filters['is_read'])) {
            $builder->where('is_read', $filters['is_read']);
        }

        if (!empty($filters['notification_type'])) {
            $builder->where('notification_type', $filters['notification_type']);
        }

        if (!empty($filters['priority'])) {
            $builder->where('priority', $filters['priority']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        return $builder->orderBy('is_read', 'ASC')
                       ->orderBy('priority', 'DESC')
                       ->orderBy('created_at', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得未讀通知數量
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                   ->where('is_read', 0)
                   ->where('deleted_at', null)
                   ->groupStart()
                   ->where('expires_at IS NULL')
                   ->orWhere('expires_at >', date('Y-m-d H:i:s'))
                   ->groupEnd()
                   ->countAllResults();
    }

    /**
     * 建立通知
     */
    public function createNotification($data)
    {
        // 處理元資料
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }

        // 設定預設值
        $data = array_merge([
            'priority' => 'normal',
            'is_read' => 0,
            'is_global' => 0,
            'send_email' => 0,
            'send_sms' => 0
        ], $data);

        return $this->insert($data);
    }

    /**
     * 批量建立通知
     */
    public function createBulkNotifications($userIds, $notificationData)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notification = array_merge($notificationData, ['user_id' => $userId]);
            $notifications[] = $notification;
        }

        return $this->insertBatch($notifications);
    }

    /**
     * 標記為已讀
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $builder = $this->where('id', $notificationId);

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->set([
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ])->update();
    }

    /**
     * 批量標記為已讀
     */
    public function markMultipleAsRead($notificationIds, $userId = null)
    {
        $builder = $this->whereIn('id', $notificationIds);

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->set([
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ])->update();
    }

    /**
     * 標記所有為已讀
     */
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                   ->where('is_read', 0)
                   ->set([
                       'is_read' => 1,
                       'read_at' => date('Y-m-d H:i:s')
                   ])->update();
    }

    /**
     * 建立會議通知
     */
    public function createMeetingNotification($meetingId, $type = 'meeting_notice', $additionalData = [])
    {
        $meetingModel = model('MeetingModel');
        $meeting = $meetingModel->find($meetingId);

        if (!$meeting) {
            return false;
        }

        // 取得該更新會的所有使用者
        $userModel = model('UserModel');
        $users = $userModel->where('urban_renewal_id', $meeting['urban_renewal_id'])
                          ->where('is_active', 1)
                          ->findAll();

        $userIds = array_column($users, 'id');

        $notificationData = array_merge([
            'notification_type' => $type,
            'priority' => 'normal',
            'title' => $this->getMeetingNotificationTitle($type, $meeting),
            'message' => $this->getMeetingNotificationMessage($type, $meeting),
            'related_type' => 'meeting',
            'related_id' => $meetingId,
            'action_url' => '/meetings/' . $meetingId,
            'action_text' => '查看會議',
            'urban_renewal_id' => $meeting['urban_renewal_id'],
            'metadata' => json_encode([
                'meeting_name' => $meeting['meeting_name'],
                'meeting_date' => $meeting['meeting_date'],
                'meeting_time' => $meeting['meeting_time']
            ])
        ], $additionalData);

        return $this->createBulkNotifications($userIds, $notificationData);
    }

    /**
     * 建立投票通知
     */
    public function createVotingNotification($topicId, $type = 'voting_start', $additionalData = [])
    {
        $votingTopicModel = model('VotingTopicModel');
        $topic = $votingTopicModel->find($topicId);

        if (!$topic) {
            return false;
        }

        $meetingModel = model('MeetingModel');
        $meeting = $meetingModel->find($topic['meeting_id']);

        if (!$meeting) {
            return false;
        }

        // 取得該更新會的所有使用者
        $userModel = model('UserModel');
        $users = $userModel->where('urban_renewal_id', $meeting['urban_renewal_id'])
                          ->where('is_active', 1)
                          ->findAll();

        $userIds = array_column($users, 'id');

        $notificationData = array_merge([
            'notification_type' => $type,
            'priority' => 'high',
            'title' => $this->getVotingNotificationTitle($type, $topic),
            'message' => $this->getVotingNotificationMessage($type, $topic),
            'related_type' => 'voting_topic',
            'related_id' => $topicId,
            'action_url' => '/voting/' . $topicId,
            'action_text' => '參與投票',
            'urban_renewal_id' => $meeting['urban_renewal_id'],
            'metadata' => json_encode([
                'topic_title' => $topic['topic_title'],
                'topic_number' => $topic['topic_number'],
                'meeting_name' => $meeting['meeting_name']
            ])
        ], $additionalData);

        return $this->createBulkNotifications($userIds, $notificationData);
    }

    /**
     * 清理過期通知
     */
    public function cleanExpiredNotifications()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->where('expires_at IS NOT NULL')
                   ->delete();
    }

    /**
     * 取得通知統計
     */
    public function getNotificationStatistics($userId = null, $urbanRenewalId = null)
    {
        $builder = $this->select('notification_type, priority, is_read, COUNT(*) as count')
                       ->where('deleted_at', null);

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        if ($urbanRenewalId) {
            $builder->where('urban_renewal_id', $urbanRenewalId);
        }

        return $builder->groupBy(['notification_type', 'priority', 'is_read'])
                       ->findAll();
    }

    /**
     * 取得會議通知標題
     */
    private function getMeetingNotificationTitle($type, $meeting)
    {
        $titles = [
            'meeting_notice' => '會議通知：' . $meeting['meeting_name'],
            'meeting_reminder' => '會議提醒：' . $meeting['meeting_name'],
            'check_in_reminder' => '報到提醒：' . $meeting['meeting_name']
        ];

        return $titles[$type] ?? '會議通知';
    }

    /**
     * 取得會議通知訊息
     */
    private function getMeetingNotificationMessage($type, $meeting)
    {
        $messages = [
            'meeting_notice' => sprintf(
                '您有一場會議即將舉行：%s，時間：%s %s，地點：%s',
                $meeting['meeting_name'],
                $meeting['meeting_date'],
                $meeting['meeting_time'],
                $meeting['meeting_location']
            ),
            'meeting_reminder' => sprintf(
                '提醒您，會議「%s」將於今日 %s 在 %s 舉行，請準時參加。',
                $meeting['meeting_name'],
                $meeting['meeting_time'],
                $meeting['meeting_location']
            ),
            'check_in_reminder' => sprintf(
                '會議「%s」現在開放報到，請盡快完成報到程序。',
                $meeting['meeting_name']
            )
        ];

        return $messages[$type] ?? '會議相關通知';
    }

    /**
     * 取得投票通知標題
     */
    private function getVotingNotificationTitle($type, $topic)
    {
        $titles = [
            'voting_start' => '投票開始：' . $topic['topic_title'],
            'voting_end' => '投票結束：' . $topic['topic_title'],
            'voting_reminder' => '投票提醒：' . $topic['topic_title']
        ];

        return $titles[$type] ?? '投票通知';
    }

    /**
     * 取得投票通知訊息
     */
    private function getVotingNotificationMessage($type, $topic)
    {
        $messages = [
            'voting_start' => sprintf(
                '議題「%s」現已開放投票，請盡快完成您的投票。',
                $topic['topic_title']
            ),
            'voting_end' => sprintf(
                '議題「%s」投票已結束，結果為：%s',
                $topic['topic_title'],
                $this->translateVotingResult($topic['voting_result'])
            ),
            'voting_reminder' => sprintf(
                '提醒您，議題「%s」的投票即將截止，請盡快完成投票。',
                $topic['topic_title']
            )
        ];

        return $messages[$type] ?? '投票相關通知';
    }

    /**
     * 翻譯投票結果
     */
    private function translateVotingResult($result)
    {
        $translations = [
            'passed' => '通過',
            'failed' => '未通過',
            'pending' => '待定',
            'withdrawn' => '撤回'
        ];

        return $translations[$result] ?? $result;
    }
}