<?php

namespace App\Controllers\Api\Admin;

use CodeIgniter\RESTful\ResourceController;

class RegistrationLogController extends ResourceController
{
    protected $format = 'json';
    protected $registrationLogModel;

    public function __construct()
    {
        $this->registrationLogModel = model('RegistrationLogModel');
        helper(['auth', 'response']);
    }

    /**
     * 取得註冊日誌列表
     * GET /api/admin/registration-logs
     */
    public function index()
    {
        try {
            // 驗證管理員權限
            $user = auth_validate_request();
            if (!$user || $user['role'] !== 'admin') {
                return response_error('您沒有權限存取此功能', 403);
            }

            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = (int)($this->request->getGet('per_page') ?? 20);

            $filters = [];

            if ($status = $this->request->getGet('status')) {
                $filters['status'] = $status;
            }

            if ($dateFrom = $this->request->getGet('date_from')) {
                $filters['date_from'] = $dateFrom;
            }

            if ($dateTo = $this->request->getGet('date_to')) {
                $filters['date_to'] = $dateTo;
            }

            if ($ipAddress = $this->request->getGet('ip_address')) {
                $filters['ip_address'] = $ipAddress;
            }

            $result = $this->registrationLogModel->getLogList($page, $perPage, $filters);

            return $this->respond([
                'status' => 'success',
                'data' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                    'total_pages' => $result['total_pages'],
                ],
                'message' => '取得註冊日誌列表成功'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get registration logs error: ' . $e->getMessage());
            return response_error('取得註冊日誌列表失敗', 500);
        }
    }

    /**
     * 取得單筆註冊日誌詳情
     * GET /api/admin/registration-logs/:id
     */
    public function show($id = null)
    {
        try {
            // 驗證管理員權限
            $user = auth_validate_request();
            if (!$user || $user['role'] !== 'admin') {
                return response_error('您沒有權限存取此功能', 403);
            }

            if (!$id) {
                return response_error('日誌 ID 為必填', 400);
            }

            $log = $this->registrationLogModel->getLogDetail((int)$id);

            if (!$log) {
                return response_error('找不到該日誌', 404);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $log,
                'message' => '取得註冊日誌詳情成功'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get registration log detail error: ' . $e->getMessage());
            return response_error('取得註冊日誌詳情失敗', 500);
        }
    }

    /**
     * 刪除單筆註冊日誌
     * DELETE /api/admin/registration-logs/:id
     */
    public function delete($id = null)
    {
        try {
            // 驗證管理員權限
            $user = auth_validate_request();
            if (!$user || $user['role'] !== 'admin') {
                return response_error('您沒有權限存取此功能', 403);
            }

            if (!$id) {
                return response_error('日誌 ID 為必填', 400);
            }

            $log = $this->registrationLogModel->find($id);

            if (!$log) {
                return response_error('找不到該日誌', 404);
            }

            $this->registrationLogModel->delete($id);

            return $this->respond([
                'status' => 'success',
                'message' => '刪除註冊日誌成功'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Delete registration log error: ' . $e->getMessage());
            return response_error('刪除註冊日誌失敗', 500);
        }
    }

    /**
     * 取得統計資料
     * GET /api/admin/registration-logs/statistics
     */
    public function statistics()
    {
        try {
            // 驗證管理員權限
            $user = auth_validate_request();
            if (!$user || $user['role'] !== 'admin') {
                return response_error('您沒有權限存取此功能', 403);
            }

            $builder = $this->registrationLogModel->builder();

            // 總數
            $total = $builder->countAllResults(false);

            // 成功數
            $successCount = $this->registrationLogModel
                ->where('response_status', 'success')
                ->countAllResults(false);

            // 失敗數
            $errorCount = $this->registrationLogModel
                ->where('response_status', 'error')
                ->countAllResults(false);

            // 今日數
            $todayCount = $this->registrationLogModel
                ->where('created_at >=', date('Y-m-d') . ' 00:00:00')
                ->countAllResults();

            return $this->respond([
                'status' => 'success',
                'data' => [
                    'total' => $total,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'today_count' => $todayCount,
                ],
                'message' => '取得統計資料成功'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get registration log statistics error: ' . $e->getMessage());
            return response_error('取得統計資料失敗', 500);
        }
    }
}
