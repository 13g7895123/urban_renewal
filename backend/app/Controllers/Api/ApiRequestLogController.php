<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ApiRequestLogModel;

/**
 * API 請求日誌 Controller
 * 
 * 提供查詢和管理 API 日誌的功能
 */
class ApiRequestLogController extends ResourceController
{
    protected ApiRequestLogModel $logModel;

    public function __construct()
    {
        $this->logModel = new ApiRequestLogModel();
    }

    /**
     * 取得日誌列表
     * GET /api/request-logs
     */
    public function index(): ResponseInterface
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 50;
            $endpoint = $this->request->getGet('endpoint');
            $method = $this->request->getGet('method');
            $userId = $this->request->getGet('user_id');
            $status = $this->request->getGet('status');
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');

            $builder = $this->logModel->builder();

            // 套用篩選條件
            if ($endpoint) {
                $builder->like('endpoint', $endpoint);
            }
            if ($method) {
                $builder->where('method', strtoupper($method));
            }
            if ($userId) {
                $builder->where('user_id', $userId);
            }
            if ($status) {
                $builder->where('response_status', $status);
            }
            if ($startDate) {
                $builder->where('created_at >=', $startDate);
            }
            if ($endDate) {
                $builder->where('created_at <=', $endDate);
            }

            // 分頁
            $total = $builder->countAllResults(false);
            $logs = $builder->orderBy('created_at', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()
                ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $logs,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                ],
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching API logs: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve API logs'
            ], 500);
        }
    }

    /**
     * 取得單一日誌詳情
     * GET /api/request-logs/{id}
     */
    public function show($id = null): ResponseInterface
    {
        try {
            $log = $this->logModel->find($id);

            if (!$log) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Log not found'
                ], 404);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $log,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching API log: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve API log'
            ], 500);
        }
    }

    /**
     * 取得錯誤日誌
     * GET /api/request-logs/errors
     */
    public function errors(): ResponseInterface
    {
        try {
            $limit = $this->request->getGet('limit') ?? 100;
            $logs = $this->logModel->getErrors($limit);

            return $this->respond([
                'status' => 'success',
                'data' => $logs,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching error logs: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve error logs'
            ], 500);
        }
    }

    /**
     * 取得慢速請求日誌
     * GET /api/request-logs/slow
     */
    public function slow(): ResponseInterface
    {
        try {
            $minDuration = $this->request->getGet('min_duration') ?? 1000;
            $limit = $this->request->getGet('limit') ?? 100;
            $logs = $this->logModel->getSlowRequests($minDuration, $limit);

            return $this->respond([
                'status' => 'success',
                'data' => $logs,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching slow request logs: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve slow request logs'
            ], 500);
        }
    }

    /**
     * 取得統計資料
     * GET /api/request-logs/statistics
     */
    public function statistics(): ResponseInterface
    {
        try {
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');

            $stats = $this->logModel->getStatistics($startDate, $endDate);

            return $this->respond([
                'status' => 'success',
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching statistics: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * 清除舊日誌
     * DELETE /api/request-logs/clean
     */
    public function clean(): ResponseInterface
    {
        try {
            $daysToKeep = $this->request->getGet('days') ?? 30;
            $deletedCount = $this->logModel->cleanOldLogs($daysToKeep);

            return $this->respond([
                'status' => 'success',
                'message' => "Deleted {$deletedCount} old log entries",
                'deleted_count' => $deletedCount,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error cleaning logs: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to clean logs'
            ], 500);
        }
    }
}
