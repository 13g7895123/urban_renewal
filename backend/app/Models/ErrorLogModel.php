<?php

namespace App\Models;

use CodeIgniter\Model;

class ErrorLogModel extends Model
{
    protected $table = 'error_logs';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'severity',
        'message',
        'exception_class',
        'file',
        'line',
        'trace',
        'context',
        'request_method',
        'request_uri',
        'request_body',
        'user_id',
        'ip_address',
        'user_agent',
        'environment',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'notes',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;

    /**
     * 記錄錯誤
     */
    public function logError(array $data): bool
    {
        // 自動設定 created_at
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        // 自動設定環境
        if (!isset($data['environment'])) {
            $data['environment'] = ENVIRONMENT;
        }

        // 遮蔽敏感資訊
        if (isset($data['request_body'])) {
            $data['request_body'] = $this->maskSensitiveData($data['request_body']);
        }

        if (isset($data['context']) && is_array($data['context'])) {
            $data['context'] = $this->maskSensitiveContext($data['context']);
        }

        return $this->insert($data) !== false;
    }

    /**
     * 遮蔽敏感資料
     */
    private function maskSensitiveData($data): string
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            } else {
                return $data;
            }
        }

        if (is_array($data)) {
            $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'api_key', 'credit_card'];
            
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveKeys)) {
                    $data[$key] = '***MASKED***';
                } elseif (is_array($value)) {
                    $data[$key] = $this->maskSensitiveData($value);
                }
            }
        }

        return is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 遮蔽敏感上下文
     */
    private function maskSensitiveContext(array $context): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key'];
        
        foreach ($context as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $context[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $context[$key] = $this->maskSensitiveContext($value);
            }
        }

        return $context;
    }

    /**
     * 取得未解決的錯誤
     */
    public function getUnresolved(int $limit = 100): array
    {
        return $this->where('is_resolved', 0)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 取得嚴重錯誤
     */
    public function getCritical(int $limit = 100): array
    {
        return $this->whereIn('severity', ['emergency', 'alert', 'critical', 'error'])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 取得特定使用者的錯誤
     */
    public function getByUser(int $userId, int $limit = 100): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 取得特定異常類別的錯誤
     */
    public function getByExceptionClass(string $class, int $limit = 100): array
    {
        return $this->where('exception_class', $class)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * 標記為已解決
     */
    public function markAsResolved(int $id, int $resolvedBy = null, string $notes = null): bool
    {
        $data = [
            'is_resolved' => 1,
            'resolved_at' => date('Y-m-d H:i:s'),
        ];

        if ($resolvedBy) {
            $data['resolved_by'] = $resolvedBy;
        }

        if ($notes) {
            $data['notes'] = $notes;
        }

        return $this->update($id, $data);
    }

    /**
     * 批次標記為已解決
     */
    public function markMultipleAsResolved(array $ids, int $resolvedBy = null): bool
    {
        $data = [
            'is_resolved' => 1,
            'resolved_at' => date('Y-m-d H:i:s'),
        ];

        if ($resolvedBy) {
            $data['resolved_by'] = $resolvedBy;
        }

        return $this->whereIn('id', $ids)->set($data)->update();
    }

    /**
     * 清除舊錯誤日誌
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        return $this->where('created_at <', $cutoffDate)
            ->where('is_resolved', 1)
            ->delete();
    }

    /**
     * 取得錯誤統計
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

        $total = $builder->countAllResults(false);
        $unresolved = $builder->where('is_resolved', 0)->countAllResults(false);
        $critical = $builder->whereIn('severity', ['emergency', 'alert', 'critical', 'error'])->countAllResults(false);

        // 按嚴重程度統計
        $bySeverity = $this->db->table($this->table)
            ->select('severity, COUNT(*) as count')
            ->groupBy('severity');

        if ($startDate) {
            $bySeverity->where('created_at >=', $startDate);
        }
        if ($endDate) {
            $bySeverity->where('created_at <=', $endDate);
        }

        $severityStats = $bySeverity->get()->getResultArray();

        return [
            'total_errors' => $total,
            'unresolved_errors' => $unresolved,
            'critical_errors' => $critical,
            'resolved_errors' => $total - $unresolved,
            'by_severity' => $severityStats,
        ];
    }
}
