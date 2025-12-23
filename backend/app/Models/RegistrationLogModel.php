<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistrationLogModel extends Model
{
    protected $table = 'registration_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'request_data',
        'response_status',
        'response_code',
        'response_message',
        'error_details',
        'ip_address',
        'user_agent',
        'created_user_id',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * 記錄註冊請求
     *
     * @param array $requestData 請求資料
     * @param string $ipAddress IP 位址
     * @param string $userAgent User Agent
     * @return int|false 日誌 ID 或 false
     */
    public function logRequest(array $requestData, string $ipAddress, string $userAgent)
    {
        // 遮蔽敏感資料
        $sanitizedData = $this->sanitizeRequestData($requestData);

        $logData = [
            'request_data' => json_encode($sanitizedData, JSON_UNESCAPED_UNICODE),
            'response_status' => 'error', // 預設為 error，成功時會更新
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent, 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($logData);
    }

    /**
     * 更新日誌為成功狀態
     *
     * @param int $logId 日誌 ID
     * @param int $userId 建立的使用者 ID
     * @param string $message 回應訊息
     * @return bool
     */
    public function markAsSuccess(int $logId, int $userId, string $message = '註冊成功'): bool
    {
        return $this->update($logId, [
            'response_status' => 'success',
            'response_code' => 201,
            'response_message' => $message,
            'created_user_id' => $userId,
        ]);
    }

    /**
     * 更新日誌為失敗狀態
     *
     * @param int $logId 日誌 ID
     * @param int $responseCode HTTP 狀態碼
     * @param string $message 錯誤訊息
     * @param array|null $errorDetails 錯誤詳情
     * @return bool
     */
    public function markAsError(int $logId, int $responseCode, string $message, ?array $errorDetails = null): bool
    {
        $updateData = [
            'response_status' => 'error',
            'response_code' => $responseCode,
            'response_message' => $message,
        ];

        if ($errorDetails !== null) {
            $updateData['error_details'] = json_encode($errorDetails, JSON_UNESCAPED_UNICODE);
        }

        return $this->update($logId, $updateData);
    }

    /**
     * 遮蔽敏感資料
     *
     * @param array $data 原始資料
     * @return array 遮蔽後的資料
     */
    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirm', 'passwordConfirm'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '******';
            }
        }

        return $data;
    }

    /**
     * 取得日誌列表（分頁）
     *
     * @param int $page 頁碼
     * @param int $perPage 每頁筆數
     * @param array $filters 篩選條件
     * @return array
     */
    public function getLogList(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $builder = $this->builder();

        // 篩選條件
        if (!empty($filters['status'])) {
            $builder->where('response_status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['ip_address'])) {
            $builder->like('ip_address', $filters['ip_address']);
        }

        // 計算總數
        $total = $builder->countAllResults(false);

        // 取得資料
        $data = $builder
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        // 解析 JSON 欄位
        foreach ($data as &$row) {
            $row['request_data'] = json_decode($row['request_data'], true);
            $row['error_details'] = $row['error_details'] ? json_decode($row['error_details'], true) : null;
        }

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * 取得單筆日誌詳情
     *
     * @param int $id 日誌 ID
     * @return array|null
     */
    public function getLogDetail(int $id): ?array
    {
        $log = $this->find($id);

        if (!$log) {
            return null;
        }

        $log['request_data'] = json_decode($log['request_data'], true);
        $log['error_details'] = $log['error_details'] ? json_decode($log['error_details'], true) : null;

        return $log;
    }
}
