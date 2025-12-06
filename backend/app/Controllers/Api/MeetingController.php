<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\MeetingService;
use App\Services\AuthorizationService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Traits\HasRbacPermissions;

/**
 * 會議 Controller
 * 
 * 使用 Entity + Repository + Service 架構
 */
class MeetingController extends ResourceController
{
    use HasRbacPermissions;

    protected MeetingService $meetingService;
    protected AuthorizationService $authService;
    protected $format = 'json';

    // 保留原有 Model 供匯出功能使用
    protected $meetingModel;
    protected $urbanRenewalModel;

    public function __construct()
    {
        $this->meetingService = service('meetingService');
        $this->authService = service('authorizationService');
        $this->meetingModel = model('MeetingModel');
        $this->urbanRenewalModel = model('UrbanRenewalModel');

        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Expose-Headers: Content-Disposition');
    }

    /**
     * Handle preflight OPTIONS requests
     */
    public function options()
    {
        return $this->response->setStatusCode(200);
    }

    /**
     * 取得會議列表
     * GET /api/meetings
     */
    public function index()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;

            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = (int)($this->request->getGet('per_page') ?? 10);
            
            $filters = [];
            if ($urbanRenewalId = $this->request->getGet('urban_renewal_id')) {
                $filters['urban_renewal_id'] = $urbanRenewalId;
            }
            if ($status = $this->request->getGet('status')) {
                $filters['meeting_status'] = $status;
            }
            if ($search = $this->request->getGet('search')) {
                $filters['search'] = $search;
            }

            $result = $this->meetingService->getList($user, $page, $perPage, $filters);

            return $this->respond([
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'message' => '取得會議列表成功'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()]], 401);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (\Exception $e) {
            log_message('error', 'Get meetings error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '取得會議列表失敗']], 500);
        }
    }

    /**
     * 取得特定更新會的會議列表
     * GET /api/urban-renewals/{id}/meetings
     */
    public function getByUrbanRenewal($urbanRenewalId)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $status = $this->request->getGet('status');

            // 檢查更新會是否存在
            if (!$this->urbanRenewalModel->find($urbanRenewalId)) {
                return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => '更新會不存在']], 404);
            }

            $data = $this->meetingService->getByUrbanRenewal($user, (int)$urbanRenewalId, $status);

            return $this->respond([
                'success' => true,
                'data' => $data,
                'message' => '取得會議列表成功'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()]], 401);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (\Exception $e) {
            log_message('error', 'Get meetings by urban renewal error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '取得會議列表失敗']], 500);
        }
    }

    /**
     * 取得單一會議詳情
     * GET /api/meetings/{id}
     */
    public function show($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;

            $data = $this->meetingService->getDetail($user, (int)$id);

            return $this->respond([
                'success' => true,
                'data' => $data,
                'message' => '取得會議詳情成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (UnauthorizedException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()]], 401);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (\Exception $e) {
            log_message('error', 'Get meeting details error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '取得會議詳情失敗']], 500);
        }
    }

    /**
     * 建立新會議
     * POST /api/meetings
     */
    public function create()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $data = $this->request->getJSON(true);

            $result = $this->meetingService->create($user, $data);

            return $this->respondCreated([
                'success' => true,
                'data' => $result,
                'message' => '會議建立成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (UnauthorizedException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()]], 401);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (ValidationException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage(), 'details' => $e->getErrors()]], 422);
        } catch (\InvalidArgumentException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage()]], 422);
        } catch (\Exception $e) {
            log_message('error', 'Create meeting error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '會議建立失敗']], 500);
        }
    }

    /**
     * 更新會議資料
     * PUT /api/meetings/{id}
     */
    public function update($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $data = $this->request->getJSON(true);

            $result = $this->meetingService->update($user, (int)$id, $data);

            return $this->respond([
                'success' => true,
                'data' => $result,
                'message' => '會議更新成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (UnauthorizedException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()]], 401);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (ValidationException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage(), 'details' => $e->getErrors()]], 422);
        } catch (\Exception $e) {
            log_message('error', 'Update meeting error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '會議更新失敗']], 500);
        }
    }

    /**
     * 刪除會議
     * DELETE /api/meetings/{id}
     */
    public function delete($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;

            $this->meetingService->delete($user, (int)$id);

            return $this->respondDeleted([
                'success' => true,
                'message' => '會議刪除成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (UnauthorizedException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'message' => $e->getMessage()]], 401);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (ValidationException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'BUSINESS_LOGIC_ERROR', 'message' => $e->getMessage()]], 422);
        } catch (\Exception $e) {
            log_message('error', 'Delete meeting error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '會議刪除失敗']], 500);
        }
    }

    /**
     * 取得會議統計資料
     * GET /api/meetings/{id}/statistics
     */
    public function statistics($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;

            $data = $this->meetingService->getStatistics($user, (int)$id);

            return $this->respond([
                'success' => true,
                'data' => $data,
                'message' => '取得會議統計成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (\Exception $e) {
            log_message('error', 'Get meeting statistics error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '取得會議統計失敗']], 500);
        }
    }

    /**
     * 更新會議狀態
     * PUT /api/meetings/{id}/status
     */
    public function updateStatus($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $data = $this->request->getJSON(true);

            if (empty($data['status'])) {
                return $this->fail(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => '狀態為必填']], 422);
            }

            $result = $this->meetingService->updateStatus($user, (int)$id, $data['status']);

            return $this->respond([
                'success' => true,
                'data' => $result,
                'message' => '會議狀態更新成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (ValidationException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'BUSINESS_LOGIC_ERROR', 'message' => $e->getMessage()]], 422);
        } catch (\Exception $e) {
            log_message('error', 'Update meeting status error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '會議狀態更新失敗']], 500);
        }
    }

    /**
     * 取得會議的合格投票人快照
     * GET /api/meetings/{id}/eligible-voters
     */
    public function getEligibleVoters($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = (int)($this->request->getGet('per_page') ?? 100);

            $result = $this->meetingService->getEligibleVoters($user, (int)$id, $page, $perPage);

            return $this->respond([
                'success' => true,
                'data' => $result['data'],
                'statistics' => $result['statistics'],
                'has_snapshot' => $result['has_snapshot'],
                'pagination' => $result['pagination'] ?? null,
                'message' => $result['has_snapshot'] ? '取得合格投票人名單成功' : '此會議尚未建立投票人快照'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (\Exception $e) {
            log_message('error', 'Get eligible voters error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '取得合格投票人名單失敗']], 500);
        }
    }

    /**
     * 重新整理會議的合格投票人快照
     * POST /api/meetings/{id}/eligible-voters/refresh
     */
    public function refreshEligibleVoters($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;

            $result = $this->meetingService->refreshEligibleVoters($user, (int)$id);

            return $this->respond([
                'success' => true,
                'data' => $result,
                'message' => '投票人名單已重新整理'
            ]);

        } catch (NotFoundException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => $e->getMessage()]], 404);
        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (ValidationException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'BUSINESS_LOGIC_ERROR', 'message' => $e->getMessage()]], 422);
        } catch (\Exception $e) {
            log_message('error', 'Refresh eligible voters error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '重新整理投票人名單失敗']], 500);
        }
    }

    // =====================================================
    // 以下為匯出功能，保留原有實作
    // =====================================================

    /**
     * 匯出會議通知
     * GET /api/meetings/{id}/export-notice
     */
    public function exportNotice($id = null)
    {
        try {
            $meeting = $this->meetingModel->getMeetingWithDetails($id);
            if (!$meeting) {
                return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => '會議不存在']], 404);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            $this->authService->assertCanAccessUrbanRenewal($user, (int)$meeting['urban_renewal_id']);

            $wordExportService = new \App\Services\WordExportService();
            $result = $wordExportService->exportMeetingNotice($meeting);

            if (!$result['success']) {
                return $this->fail(['success' => false, 'error' => ['code' => 'EXPORT_ERROR', 'message' => $result['error']]], 500);
            }

            $filepath = $result['filepath'];
            $filename = $result['filename'];

            if (!file_exists($filepath)) {
                return $this->fail(['success' => false, 'error' => ['code' => 'FILE_NOT_FOUND', 'message' => '匯出檔案不存在']], 404);
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: max-age=0');

            readfile($filepath);
            exit;

        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (\Exception $e) {
            log_message('error', 'Export meeting notice error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '匯出會議通知失敗']], 500);
        }
    }

    /**
     * 匯出簽到冊
     * GET /api/meetings/{id}/export-signature-book
     */
    public function exportSignatureBook($id = null)
    {
        try {
            $meeting = $this->meetingModel->getMeetingWithDetails($id);
            if (!$meeting) {
                return $this->fail(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => '會議不存在']], 404);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            $this->authService->assertCanAccessUrbanRenewal($user, (int)$meeting['urban_renewal_id']);

            $isAnonymous = $this->request->getGet('anonymous') === 'true';

            $wordExportService = new \App\Services\WordExportService();
            $result = $wordExportService->exportSignatureBook($meeting, $isAnonymous);

            if (!$result['success']) {
                return $this->fail(['success' => false, 'error' => ['code' => 'EXPORT_ERROR', 'message' => $result['error']]], 500);
            }

            $filepath = $result['filepath'];
            $filename = $result['filename'];

            if (!file_exists($filepath)) {
                return $this->fail(['success' => false, 'error' => ['code' => 'FILE_NOT_FOUND', 'message' => '匯出檔案不存在']], 404);
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: max-age=0');

            readfile($filepath);
            exit;

        } catch (ForbiddenException $e) {
            return $this->fail(['success' => false, 'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()]], 403);
        } catch (\Exception $e) {
            log_message('error', 'Export signature book error: ' . $e->getMessage());
            return $this->fail(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => '匯出簽到冊失敗']], 500);
        }
    }
}
