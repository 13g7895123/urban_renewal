<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ErrorLogModel;

/**
 * 錯誤日誌 Controller
 */
class ErrorLogController extends ResourceController
{
    protected ErrorLogModel $errorLogModel;

    public function __construct()
    {
        $this->errorLogModel = new ErrorLogModel();
    }

    /**
     * 取得錯誤日誌列表
     * GET /api/error-logs
     */
    public function index(): ResponseInterface
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 50;
            $severity = $this->request->getGet('severity');
            $isResolved = $this->request->getGet('is_resolved');
            $exceptionClass = $this->request->getGet('exception_class');
            $userId = $this->request->getGet('user_id');
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');

            $builder = $this->errorLogModel->builder();

            // 套用篩選
            if ($severity) {
                $builder->where('severity', $severity);
            }
            if ($isResolved !== null) {
                $builder->where('is_resolved', (int)$isResolved);
            }
            if ($exceptionClass) {
                $builder->like('exception_class', $exceptionClass);
            }
            if ($userId) {
                $builder->where('user_id', $userId);
            }
            if ($startDate) {
                $builder->where('created_at >=', $startDate);
            }
            if ($endDate) {
                $builder->where('created_at <=', $endDate);
            }

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
            log_message('error', 'Error fetching error logs: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve error logs'
            ], 500);
        }
    }

    /**
     * 取得單一錯誤詳情
     * GET /api/error-logs/{id}
     */
    public function show($id = null): ResponseInterface
    {
        try {
            $log = $this->errorLogModel->find($id);

            if (!$log) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Error log not found'
                ], 404);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $log,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching error log: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve error log'
            ], 500);
        }
    }

    /**
     * 取得未解決的錯誤
     * GET /api/error-logs/unresolved
     */
    public function unresolved(): ResponseInterface
    {
        try {
            $limit = $this->request->getGet('limit') ?? 100;
            $logs = $this->errorLogModel->getUnresolved($limit);

            return $this->respond([
                'status' => 'success',
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching unresolved errors: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve unresolved errors'
            ], 500);
        }
    }

    /**
     * 取得嚴重錯誤
     * GET /api/error-logs/critical
     */
    public function critical(): ResponseInterface
    {
        try {
            $limit = $this->request->getGet('limit') ?? 100;
            $logs = $this->errorLogModel->getCritical($limit);

            return $this->respond([
                'status' => 'success',
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching critical errors: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve critical errors'
            ], 500);
        }
    }

    /**
     * 取得統計資料
     * GET /api/error-logs/statistics
     */
    public function statistics(): ResponseInterface
    {
        try {
            $startDate = $this->request->getGet('start_date');
            $endDate = $this->request->getGet('end_date');

            $stats = $this->errorLogModel->getStatistics($startDate, $endDate);

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
     * 標記為已解決
     * PATCH /api/error-logs/{id}/resolve
     */
    public function resolve($id = null): ResponseInterface
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $userId = $user['id'] ?? null;
            
            $notes = $this->request->getJSON(true)['notes'] ?? null;

            $result = $this->errorLogModel->markAsResolved($id, $userId, $notes);

            if ($result) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Error marked as resolved',
                ]);
            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to mark error as resolved'
                ], 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error marking as resolved: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to mark error as resolved'
            ], 500);
        }
    }

    /**
     * 批次標記為已解決
     * POST /api/error-logs/resolve-multiple
     */
    public function resolveMultiple(): ResponseInterface
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $userId = $user['id'] ?? null;
            
            $data = $this->request->getJSON(true);
            $ids = $data['ids'] ?? [];

            if (empty($ids)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No IDs provided'
                ], 400);
            }

            $result = $this->errorLogModel->markMultipleAsResolved($ids, $userId);

            if ($result) {
                return $this->respond([
                    'status' => 'success',
                    'message' => count($ids) . ' errors marked as resolved',
                ]);
            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to mark errors as resolved'
                ], 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error marking multiple as resolved: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to mark errors as resolved'
            ], 500);
        }
    }

    /**
     * 清除舊錯誤
     * DELETE /api/error-logs/clean
     */
    public function clean(): ResponseInterface
    {
        try {
            $days = $this->request->getGet('days') ?? 90;
            $deletedCount = $this->errorLogModel->cleanOldLogs($days);

            return $this->respond([
                'status' => 'success',
                'message' => "Deleted {$deletedCount} old error logs",
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
