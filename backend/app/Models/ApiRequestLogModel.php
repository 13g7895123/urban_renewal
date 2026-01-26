<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiRequestLogModel extends Model
{
    protected $table = 'api_request_logs';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'method',
        'endpoint',
        'request_headers',
        'request_query',
        'request_body',
        'response_status',
        'response_headers',
        'response_body',
        'duration_ms',
        'error_message',
        'user_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false; // 手動管理 created_at
    protected $dateFormat = 'datetime';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true; // 日誌表不需要嚴格驗證

    /**
     * 記錄 API 請求
     */
    public function logRequest(array $data): bool
    {
        // 自動設定 created_at
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        // 遮蔽敏感資訊
        if (isset($data['request_headers'])) {
            $data['request_headers'] = $this->maskSensitiveHeaders($data['request_headers']);
        }

        if (isset($data['request_body'])) {
            $data['request_body'] = $this->maskSensitiveData($data['request_body']);
        }

        return $this->insert($data) !== false;
    }

    /**
     * 遮蔽敏感的 Header 資訊
     */
    private function maskSensitiveHeaders(array $headers): array
    {
        $sensitiveKeys = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $headers[$key] = '***MASKED***';
            }
        }

        return $headers;
    }

    /**
     * 遮蔽敏感的請求資料
     */
    private function maskSensitiveData($data): array
    {
        if (!is_array($data)) {
            return $data;
        }

        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * 取得特定端點的日誌
     */
    public function getByEndpoint(string $endpoint, int $limit = 100): array
    {
        return $this->where('endpoint', $endpoint)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 取得特定使用者的日誌
     */
    public function getByUser(int $userId, int $limit = 100): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 取得錯誤請求日誌（狀態碼 >= 400）
     */
    public function getErrors(int $limit = 100): array
    {
        return $this->where('response_status >=', 400)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 取得慢速請求日誌（執行時間超過指定毫秒）
     */
    public function getSlowRequests(int $minDurationMs = 1000, int $limit = 100): array
    {
        return $this->where('duration_ms >=', $minDurationMs)
            ->orderBy('duration_ms', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 清除舊日誌（超過指定天數）
     */
    public function cleanOldLogs(int $daysToKeep = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        return $this->where('created_at <', $cutoffDate)->delete();
    }

    /**
     * 取得 API 統計資料
     */
    public function getStatistics(string $startDate = null, string $endDate = null): array
    {
        $builder = $this->builder();

        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        return [
            'total_requests' => $builder->countAllResults(false),
            'success_requests' => $builder->where('response_status <', 400)->countAllResults(false),
            'error_requests' => $builder->where('response_status >=', 400)->countAllResults(false),
            'avg_duration_ms' => $builder->selectAvg('duration_ms')->get()->getRowArray()['duration_ms'] ?? 0,
        ];
    }
}
