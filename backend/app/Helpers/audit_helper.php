<?php

use App\Models\AuthenticationEventModel;

if (!function_exists('log_auth_event')) {
    /**
     * 記錄認證事件到審計日誌
     *
     * @param string $eventType 事件類型 (login_success, login_failure, logout, token_refresh)
     * @param int|null $userId 使用者 ID（成功認證時）
     * @param string|null $usernameAttempted 嘗試的使用者名稱（失敗時）
     * @param string|null $failureReason 失敗原因
     * @param array $metadata 額外元資料
     * @return bool 是否成功記錄
     */
    function log_auth_event(
        string $eventType,
        ?int $userId = null,
        ?string $usernameAttempted = null,
        ?string $failureReason = null,
        array $metadata = []
    ): bool {
        try {
            $model = new AuthenticationEventModel();

            $request = \Config\Services::request();

            // 取得 IP 位址
            $ipAddress = $request->getIPAddress();

            // 取得 User Agent
            $userAgent = $request->getUserAgent()->getAgentString();

            $eventData = [
                'event_type' => $eventType,
                'user_id' => $userId,
                'username_attempted' => $usernameAttempted,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'failure_reason' => $failureReason,
                'event_metadata' => !empty($metadata) ? json_encode($metadata) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            return $model->insert($eventData) !== false;
        } catch (\Exception $e) {
            // 記錄錯誤但不中斷主要流程
            log_message('error', '審計日誌記錄失敗: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_recent_auth_events')) {
    /**
     * 取得最近的認證事件
     *
     * @param int $limit 記錄數量
     * @param string|null $eventType 過濾事件類型
     * @return array
     */
    function get_recent_auth_events(int $limit = 100, ?string $eventType = null): array
    {
        $model = new AuthenticationEventModel();
        $builder = $model->builder();

        $builder->orderBy('created_at', 'DESC')
            ->limit($limit);

        if ($eventType) {
            $builder->where('event_type', $eventType);
        }

        return $builder->get()->getResultArray();
    }
}

if (!function_exists('get_user_login_history')) {
    /**
     * 取得使用者的登入歷史
     *
     * @param int $userId 使用者 ID
     * @param int $limit 記錄數量
     * @return array
     */
    function get_user_login_history(int $userId, int $limit = 20): array
    {
        $model = new AuthenticationEventModel();
        return $model->getUserAuthHistory($userId, $limit);
    }
}

if (!function_exists('check_suspicious_activity')) {
    /**
     * 檢查可疑活動（例如：大量失敗嘗試）
     *
     * @param string $ipAddress IP 位址
     * @param int $threshold 閾值
     * @param int $minutes 時間範圍（分鐘）
     * @return bool 是否有可疑活動
     */
    function check_suspicious_activity(string $ipAddress, int $threshold = 5, int $minutes = 30): bool
    {
        $model = new AuthenticationEventModel();
        $failedAttempts = $model->getFailedLoginsByIP($ipAddress, $minutes);

        return count($failedAttempts) >= $threshold;
    }
}
