<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class JwtDebugController extends BaseController
{
    /**
     * 取得 JWT 除錯日誌列表
     */
    public function index()
    {
        // 只有管理員可以查看
        helper('auth');
        $user = auth_validate_request(['admin'], false); // 關閉除錯以避免循環
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => '未授權的請求'
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('jwt_debug_logs');

        // 篩選條件
        $filters = $this->request->getGet();
        
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['stage'])) {
            $builder->where('stage', $filters['stage']);
        }
        
        if (!empty($filters['stage_status'])) {
            $builder->where('stage_status', $filters['stage_status']);
        }
        
        if (!empty($filters['ip_address'])) {
            $builder->where('ip_address', $filters['ip_address']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        // 排序（預設最新的在前）
        $builder->orderBy('created_at', 'DESC');

        // 分頁
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 50;
        $offset = ($page - 1) * $perPage;

        $total = $builder->countAllResults(false);
        $logs = $builder->limit($perPage, $offset)->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $logs,
            'pagination' => [
                'total' => $total,
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * 取得特定請求的詳細日誌
     */
    public function show($requestId)
    {
        helper('auth');
        $user = auth_validate_request(['admin'], false);
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => '未授權的請求'
            ]);
        }

        $db = \Config\Database::connect();
        $log = $db->table('jwt_debug_logs')
            ->where('request_id', $requestId)
            ->get()
            ->getRowArray();

        if (!$log) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => '找不到該筆日誌'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $log
        ]);
    }

    /**
     * 取得統計資訊
     */
    public function stats()
    {
        helper('auth');
        $user = auth_validate_request(['admin'], false);
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => '未授權的請求'
            ]);
        }

        $db = \Config\Database::connect();
        
        // 過去24小時的統計
        $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        $stats = [
            'total_requests' => $db->table('jwt_debug_logs')
                ->where('created_at >=', $since)
                ->countAllResults(),
            
            'success_rate' => $this->_calculateSuccessRate($since),
            
            'failure_by_stage' => $this->_getFailureByStage($since),
            
            'top_errors' => $this->_getTopErrors($since),
            
            'requests_by_hour' => $this->_getRequestsByHour($since),
            
            'top_users' => $this->_getTopUsers($since)
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    private function _calculateSuccessRate($since)
    {
        $db = \Config\Database::connect();
        
        $total = $db->table('jwt_debug_logs')
            ->where('created_at >=', $since)
            ->countAllResults();
        
        if ($total === 0) return 0;
        
        $success = $db->table('jwt_debug_logs')
            ->where('created_at >=', $since)
            ->where('stage', 'success')
            ->where('stage_status', 'pass')
            ->countAllResults();
        
        return round(($success / $total) * 100, 2);
    }

    private function _getFailureByStage($since)
    {
        $db = \Config\Database::connect();
        
        return $db->table('jwt_debug_logs')
            ->select('stage, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->whereIn('stage_status', ['fail', 'error'])
            ->groupBy('stage')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function _getTopErrors($since)
    {
        $db = \Config\Database::connect();
        
        return $db->table('jwt_debug_logs')
            ->select('error_type, error_message, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->where('error_type IS NOT NULL')
            ->groupBy('error_type, error_message')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
    }

    private function _getRequestsByHour($since)
    {
        $db = \Config\Database::connect();
        
        return $db->table('jwt_debug_logs')
            ->select("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, COUNT(*) as count, 
                     SUM(CASE WHEN stage = 'success' AND stage_status = 'pass' THEN 1 ELSE 0 END) as success_count")
            ->where('created_at >=', $since)
            ->groupBy('hour')
            ->orderBy('hour', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function _getTopUsers($since)
    {
        $db = \Config\Database::connect();
        
        return $db->table('jwt_debug_logs')
            ->select('user_id, COUNT(*) as request_count, 
                     SUM(CASE WHEN stage = \'success\' AND stage_status = \'pass\' THEN 1 ELSE 0 END) as success_count')
            ->where('created_at >=', $since)
            ->where('user_id IS NOT NULL')
            ->groupBy('user_id')
            ->orderBy('request_count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
    }

    /**
     * 清理舊的除錯日誌
     */
    public function cleanup()
    {
        helper('auth');
        $user = auth_validate_request(['admin'], false);
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => '未授權的請求'
            ]);
        }

        $days = $this->request->getPost('days') ?? 30;
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $db = \Config\Database::connect();
        $deleted = $db->table('jwt_debug_logs')
            ->where('created_at <', $cutoffDate)
            ->delete();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "已刪除 {$deleted} 筆舊日誌",
            'deleted_count' => $deleted
        ]);
    }
}
