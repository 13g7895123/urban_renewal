<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class NotificationController extends ResourceController
{
    protected $modelName = 'App\Models\NotificationModel';
    protected $format = 'json';

    public function __construct()
    {
        $this->loadHelpers();
    }

    private function loadHelpers()
    {
        helper(['auth', 'response']);
    }

    /**
     * 取得使用者通知列表
     */
    public function index()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $filters = [
                'is_read' => $this->request->getGet('is_read'),
                'notification_type' => $this->request->getGet('type'),
                'priority' => $this->request->getGet('priority'),
                'date_from' => $this->request->getGet('date_from'),
                'date_to' => $this->request->getGet('date_to')
            ];

            $notifications = $this->model->getUserNotifications($user['id'], $page, $perPage, $filters);

            // 解析元資料
            foreach ($notifications as &$notification) {
                if ($notification['metadata']) {
                    $notification['metadata'] = json_decode($notification['metadata'], true);
                }
            }

            return response_success('通知列表', [
                'notifications' => $notifications,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得通知列表失敗: ' . $e->getMessage());
            return response_error('取得通知列表失敗', 500);
        }
    }

    /**
     * 取得通知詳情
     */
    public function show($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('通知ID為必填', 400);
            }

            $notification = $this->model->find($id);
            if (!$notification) {
                return response_error('找不到該通知', 404);
            }

            // 檢查權限（只能查看自己的通知或全域通知）
            if ($notification['user_id'] !== $user['id'] && !$notification['is_global']) {
                return $this->failForbidden('無權限查看此通知');
            }

            // 解析元資料
            if ($notification['metadata']) {
                $notification['metadata'] = json_decode($notification['metadata'], true);
            }

            // 自動標記為已讀
            if (!$notification['is_read'] && $notification['user_id'] === $user['id']) {
                $this->model->markAsRead($id, $user['id']);
                $notification['is_read'] = 1;
                $notification['read_at'] = date('Y-m-d H:i:s');
            }

            return response_success('通知詳情', $notification);

        } catch (\Exception $e) {
            log_message('error', '取得通知詳情失敗: ' . $e->getMessage());
            return response_error('取得通知詳情失敗', 500);
        }
    }

    /**
     * 建立通知（管理員功能）
     */
    public function create()
    {
        try {
            // 驗證用戶權限（只有管理員和主席可以建立通知）
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            $validation = \Config\Services::validation();
            $validation->setRules([
                'title' => 'required|max_length[255]',
                'message' => 'required',
                'notification_type' => 'required|in_list[meeting_notice,meeting_reminder,voting_start,voting_end,voting_reminder,check_in_reminder,system_maintenance,user_account,document_upload,report_ready,permission_changed,system_alert]',
                'priority' => 'permit_empty|in_list[low,normal,high,urgent]'
            ]);

            if (!$validation->run($data)) {
                return response_error('資料驗證失敗', 400, $validation->getErrors());
            }

            // 設定建立者
            $data['created_by'] = $user['id'];

            // 主席只能為自己的更新會建立通知
            if ($user['role'] === 'chairman') {
                $data['urban_renewal_id'] = $user['urban_renewal_id'];
            }

            // 處理收件人
            if (isset($data['user_ids']) && is_array($data['user_ids'])) {
                // 批量建立通知
                $userIds = $data['user_ids'];
                unset($data['user_ids']);

                $notificationId = $this->model->createBulkNotifications($userIds, $data);
            } else {
                // 單一通知或全域通知
                $notificationId = $this->model->createNotification($data);
            }

            if (!$notificationId) {
                return response_error('建立通知失敗', 500, $this->model->errors());
            }

            return response_success('通知建立成功', ['id' => $notificationId], 201);

        } catch (\Exception $e) {
            log_message('error', '建立通知失敗: ' . $e->getMessage());
            return response_error('建立通知失敗', 500);
        }
    }

    /**
     * 標記為已讀
     */
    public function markRead($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('通知ID為必填', 400);
            }

            $notification = $this->model->find($id);
            if (!$notification) {
                return response_error('找不到該通知', 404);
            }

            // 檢查權限
            if ($notification['user_id'] !== $user['id']) {
                return $this->failForbidden('無權限操作此通知');
            }

            $success = $this->model->markAsRead($id, $user['id']);
            if (!$success) {
                return response_error('標記失敗', 500);
            }

            return response_success('已標記為已讀');

        } catch (\Exception $e) {
            log_message('error', '標記通知失敗: ' . $e->getMessage());
            return response_error('標記通知失敗', 500);
        }
    }

    /**
     * 批量標記為已讀
     */
    public function markMultipleRead()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['notification_ids']) || !is_array($data['notification_ids'])) {
                return response_error('通知ID列表為必填', 400);
            }

            $success = $this->model->markMultipleAsRead($data['notification_ids'], $user['id']);
            if (!$success) {
                return response_error('批量標記失敗', 500);
            }

            return response_success('已批量標記為已讀');

        } catch (\Exception $e) {
            log_message('error', '批量標記通知失敗: ' . $e->getMessage());
            return response_error('批量標記通知失敗', 500);
        }
    }

    /**
     * 標記所有為已讀
     */
    public function markAllRead()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $success = $this->model->markAllAsRead($user['id']);
            if (!$success) {
                return response_error('標記所有通知失敗', 500);
            }

            return response_success('已標記所有通知為已讀');

        } catch (\Exception $e) {
            log_message('error', '標記所有通知失敗: ' . $e->getMessage());
            return response_error('標記所有通知失敗', 500);
        }
    }

    /**
     * 刪除通知
     */
    public function delete($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('通知ID為必填', 400);
            }

            $notification = $this->model->find($id);
            if (!$notification) {
                return response_error('找不到該通知', 404);
            }

            // 檢查權限（只能刪除自己的通知或管理員可以刪除所有通知）
            if ($notification['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
                return $this->failForbidden('無權限刪除此通知');
            }

            $success = $this->model->delete($id);
            if (!$success) {
                return response_error('刪除通知失敗', 500);
            }

            return response_success('通知刪除成功');

        } catch (\Exception $e) {
            log_message('error', '刪除通知失敗: ' . $e->getMessage());
            return response_error('刪除通知失敗', 500);
        }
    }

    /**
     * 取得未讀通知數量
     */
    public function unreadCount()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $count = $this->model->getUnreadCount($user['id']);

            return response_success('未讀通知數量', [
                'unread_count' => $count
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得未讀通知數量失敗: ' . $e->getMessage());
            return response_error('取得未讀通知數量失敗', 500);
        }
    }

    /**
     * 取得通知統計
     */
    public function statistics()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $userId = $user['role'] === 'admin' ? null : $user['id'];
            $urbanRenewalId = $user['role'] === 'admin' ? null : $user['urban_renewal_id'];

            $statistics = $this->model->getNotificationStatistics($userId, $urbanRenewalId);

            return response_success('通知統計', $statistics);

        } catch (\Exception $e) {
            log_message('error', '取得通知統計失敗: ' . $e->getMessage());
            return response_error('取得通知統計失敗', 500);
        }
    }

    /**
     * 建立會議通知
     */
    public function createMeetingNotification()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['meeting_id']) || !isset($data['type'])) {
                return response_error('會議ID和通知類型為必填', 400);
            }

            $validTypes = ['meeting_notice', 'meeting_reminder', 'check_in_reminder'];
            if (!in_array($data['type'], $validTypes)) {
                return response_error('無效的通知類型', 400);
            }

            // 檢查會議權限
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($data['meeting_id']);
            if (!$meeting) {
                return response_error('找不到該會議', 404);
            }

            if ($user['role'] === 'chairman' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限為此會議建立通知');
            }

            $additionalData = [
                'created_by' => $user['id'],
                'send_email' => $data['send_email'] ?? 0,
                'send_sms' => $data['send_sms'] ?? 0
            ];

            $success = $this->model->createMeetingNotification($data['meeting_id'], $data['type'], $additionalData);
            if (!$success) {
                return response_error('建立會議通知失敗', 500);
            }

            return response_success('會議通知建立成功');

        } catch (\Exception $e) {
            log_message('error', '建立會議通知失敗: ' . $e->getMessage());
            return response_error('建立會議通知失敗', 500);
        }
    }

    /**
     * 建立投票通知
     */
    public function createVotingNotification()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['topic_id']) || !isset($data['type'])) {
                return response_error('議題ID和通知類型為必填', 400);
            }

            $validTypes = ['voting_start', 'voting_end', 'voting_reminder'];
            if (!in_array($data['type'], $validTypes)) {
                return response_error('無效的通知類型', 400);
            }

            // 檢查議題權限
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($data['topic_id']);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            if ($user['role'] === 'chairman' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限為此議題建立通知');
            }

            $additionalData = [
                'created_by' => $user['id'],
                'send_email' => $data['send_email'] ?? 0,
                'send_sms' => $data['send_sms'] ?? 0
            ];

            $success = $this->model->createVotingNotification($data['topic_id'], $data['type'], $additionalData);
            if (!$success) {
                return response_error('建立投票通知失敗', 500);
            }

            return response_success('投票通知建立成功');

        } catch (\Exception $e) {
            log_message('error', '建立投票通知失敗: ' . $e->getMessage());
            return response_error('建立投票通知失敗', 500);
        }
    }

    /**
     * 清理過期通知
     */
    public function cleanExpired()
    {
        try {
            // 驗證用戶權限（只有管理員可以清理）
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $count = $this->model->cleanExpiredNotifications();

            return response_success('過期通知清理完成', [
                'cleaned_count' => $count
            ]);

        } catch (\Exception $e) {
            log_message('error', '清理過期通知失敗: ' . $e->getMessage());
            return response_error('清理過期通知失敗', 500);
        }
    }

    /**
     * 取得通知類型列表
     */
    public function types()
    {
        try {
            $types = [
                'meeting_notice' => '會議通知',
                'meeting_reminder' => '會議提醒',
                'voting_start' => '投票開始',
                'voting_end' => '投票結束',
                'voting_reminder' => '投票提醒',
                'check_in_reminder' => '報到提醒',
                'system_maintenance' => '系統維護',
                'user_account' => '使用者帳號',
                'document_upload' => '文件上傳',
                'report_ready' => '報告準備完成',
                'permission_changed' => '權限變更',
                'system_alert' => '系統警告'
            ];

            return response_success('通知類型列表', $types);

        } catch (\Exception $e) {
            log_message('error', '取得通知類型失敗: ' . $e->getMessage());
            return response_error('取得通知類型失敗', 500);
        }
    }
}