<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MeetingModel;
use App\Models\UrbanRenewalModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\HasRbacPermissions;

class MeetingController extends ResourceController
{
    use HasRbacPermissions;

    protected $meetingModel;
    protected $urbanRenewalModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();

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
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $urbanRenewalId = $this->request->getGet('urban_renewal_id');
            $status = $this->request->getGet('status');
            $search = $this->request->getGet('search');

            $filters = [];
            
            // For company managers, filter by their company
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);
                if ($userCompanyId) {
                    // Get all urban renewals for user's company
                    $urbanRenewalModel = model('UrbanRenewalModel');
                    $companyRenewals = $urbanRenewalModel->where('company_id', $userCompanyId)->findAll();
                    $renewalIds = array_column($companyRenewals, 'id');
                    if (!empty($renewalIds)) {
                        $filters['urban_renewal_ids'] = $renewalIds;
                    } else {
                        // 若該企業沒有任何更新會，直接回傳空列表
                        return $this->respond([
                            'success' => true,
                            'data' => [],
                            'pagination' => [
                                'current_page' => (int)$page,
                                'per_page' => (int)$perPage,
                                'total' => 0,
                                'total_pages' => 0
                            ],
                            'message' => '取得會議列表成功'
                        ]);
                    }
                }
            } elseif ($urbanRenewalId) {
                $filters['urban_renewal_id'] = $urbanRenewalId;
            }
            
            if ($status) $filters['meeting_status'] = $status;
            if ($search) $filters['search'] = $search;

            $data = $this->meetingModel->getMeetings($page, $perPage, $filters);
            $pager = $this->meetingModel->pager;

            return $this->respond([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $pager->getCurrentPage(),
                    'per_page' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'total_pages' => $pager->getPageCount()
                ],
                'message' => '取得會議列表成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get meetings error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得會議列表失敗'
                ]
            ], 500);
        }
    }

    /**
     * 取得特定更新會的會議列表
     * GET /api/urban-renewals/{id}/meetings
     */
    public function getByUrbanRenewal($urbanRenewalId)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$urbanRenewalId, $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限存取此更新會的會議資料'
                        ]
                    ], 403);
                }
            }

            // 檢查更新會是否存在
            if (!$this->urbanRenewalModel->find($urbanRenewalId)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '更新會不存在'
                    ]
                ], 404);
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $status = $this->request->getGet('status');

            $data = $this->meetingModel->getMeetingsByUrbanRenewal($urbanRenewalId, $page, $perPage, $status);
            $pager = $this->meetingModel->pager;

            return $this->respond([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $pager->getCurrentPage(),
                    'per_page' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'total_pages' => $pager->getPageCount()
                ],
                'message' => '取得會議列表成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get meetings by urban renewal error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得會議列表失敗'
                ]
            ], 500);
        }
    }

    /**
     * 取得單一會議詳情
     * GET /api/meetings/{id}
     */
    public function show($id = null)
    {
        try {
            $meeting = $this->meetingModel->getMeetingWithDetails($id);

            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限存取此會議'
                        ]
                    ], 403);
                }
            }

            return $this->respond([
                'success' => true,
                'data' => $meeting,
                'message' => '取得會議詳情成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get meeting details error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得會議詳情失敗'
                ]
            ], 500);
        }
    }

    /**
     * 建立新會議
     * POST /api/meetings
     */
    public function create()
    {
        try {
            $rules = [
                'urban_renewal_id' => 'required|integer',
                'meeting_name' => 'required|max_length[255]',
                'meeting_type' => 'required|in_list[會員大會,理事會,監事會,臨時會議]',
                'meeting_date' => 'required|valid_date[Y-m-d]',
                'meeting_time' => 'required',
                'meeting_location' => 'permit_empty|max_length[500]'
            ];

            if (!$this->validate($rules)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '驗證失敗',
                        'details' => $this->validator->getErrors()
                    ]
                ], 422);
            }

            $data = $this->request->getJSON(true);

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$data['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您只能為自己的更新會建立會議'
                        ]
                    ], 403);
                }
            }

            // 檢查更新會是否存在
            if (!$this->urbanRenewalModel->find($data['urban_renewal_id'])) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '指定的更新會不存在'
                    ]
                ], 404);
            }

            // 檢查會議時間衝突
            $conflictMeeting = $this->meetingModel->checkMeetingConflict(
                $data['urban_renewal_id'],
                $data['meeting_date'],
                $data['meeting_time']
            );

            if ($conflictMeeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'BUSINESS_LOGIC_ERROR',
                        'message' => '該時間已有其他會議安排'
                    ]
                ], 422);
            }

            // 設定預設值
            $data['meeting_status'] = 'draft';
            if (!isset($data['attendee_count'])) $data['attendee_count'] = 0;
            if (!isset($data['calculated_total_count'])) $data['calculated_total_count'] = 0;
            $data['observer_count'] = 0;

            $meetingId = $this->meetingModel->insert($data);

            if (!$meetingId) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '會議建立失敗'
                    ]
                ], 500);
            }

            // Save observers if provided
            if (!empty($data['observers']) && is_array($data['observers'])) {
                $observerModel = model('MeetingObserverModel');
                foreach ($data['observers'] as $observer) {
                    if (!empty($observer['name'])) {
                        $observerModel->insert([
                            'meeting_id' => $meetingId,
                            'observer_name' => $observer['name'],
                            'observer_title' => $observer['title'] ?? null,
                            'observer_organization' => $observer['organization'] ?? null,
                            'contact_phone' => $observer['phone'] ?? null,
                            'notes' => $observer['notes'] ?? null
                        ]);
                    }
                }
                
                // Update observer count
                $count = $observerModel->where('meeting_id', $meetingId)->countAllResults();
                $this->meetingModel->update($meetingId, ['observer_count' => $count]);
            }

            // 建立合格投票人快照
            $eligibleVoterModel = model('MeetingEligibleVoterModel');
            $snapshotResult = $eligibleVoterModel->createSnapshot($meetingId, $data['urban_renewal_id']);
            
            if (!$snapshotResult['success']) {
                log_message('warning', 'Meeting voter snapshot creation had errors: ' . json_encode($snapshotResult['errors']));
            }
            
            // 更新會議的納入計算總人數
            // 如果有勾選「排除所有權人不列計」，則減 1
            $calculatedTotalCount = $snapshotResult['snapshot_count'];
            if (!empty($data['exclude_owner_from_count'])) {
                $calculatedTotalCount = max(0, $calculatedTotalCount - 1);
            }
            
            $this->meetingModel->update($meetingId, [
                'calculated_total_count' => $calculatedTotalCount
            ]);

            $meeting = $this->meetingModel->getMeetingWithDetails($meetingId);
            $meeting['voter_snapshot'] = $snapshotResult;

            return $this->respondCreated([
                'success' => true,
                'data' => $meeting,
                'message' => '會議建立成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Create meeting error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '會議建立失敗'
                ]
            ], 500);
        }
    }

    /**
     * 更新會議資料
     * PUT /api/meetings/{id}
     */
    public function update($id = null)
    {
        try {
            $meeting = $this->meetingModel->find($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限修改此會議'
                        ]
                    ], 403);
                }
            }

            $rules = [
                'meeting_name' => 'permit_empty|max_length[255]',
                'meeting_type' => 'permit_empty|in_list[會員大會,理事會,監事會,臨時會議]',
                'meeting_date' => 'permit_empty|valid_date[Y-m-d]',
                'meeting_time' => 'permit_empty',
                'meeting_location' => 'permit_empty|max_length[500]',
                'meeting_status' => 'permit_empty|in_list[draft,scheduled,in_progress,completed,cancelled]'
            ];

            if (!$this->validate($rules)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '驗證失敗',
                        'details' => $this->validator->getErrors()
                    ]
                ], 422);
            }

            $data = $this->request->getJSON(true);

            // 檢查會議時間衝突 (如果更新了日期或時間)
            if (isset($data['meeting_date']) || isset($data['meeting_time'])) {
                $checkDate = $data['meeting_date'] ?? $meeting['meeting_date'];
                $checkTime = $data['meeting_time'] ?? $meeting['meeting_time'];

                $conflictMeeting = $this->meetingModel->checkMeetingConflict(
                    $meeting['urban_renewal_id'],
                    $checkDate,
                    $checkTime,
                    $id
                );

                if ($conflictMeeting) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'BUSINESS_LOGIC_ERROR',
                            'message' => '該時間已有其他會議安排'
                        ]
                    ], 422);
                }
            }

            $success = $this->meetingModel->update($id, $data);

            if (!$success) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '會議更新失敗'
                    ]
                ], 500);
            }

            // Update observers if provided
            if (isset($data['observers']) && is_array($data['observers'])) {
                $observerModel = model('MeetingObserverModel');
                // Delete existing observers for this meeting (soft delete)
                $observerModel->where('meeting_id', $id)->delete();
                
                foreach ($data['observers'] as $observer) {
                    if (!empty($observer['name'])) {
                        $observerModel->insert([
                            'meeting_id' => $id,
                            'observer_name' => $observer['name'],
                            'observer_title' => $observer['title'] ?? null,
                            'observer_organization' => $observer['organization'] ?? null,
                            'contact_phone' => $observer['phone'] ?? null,
                            'notes' => $observer['notes'] ?? null
                        ]);
                    }
                }
                
                // Update observer count
                $count = $observerModel->where('meeting_id', $id)->countAllResults();
                $this->meetingModel->update($id, ['observer_count' => $count]);
            }

            // 如果 exclude_owner_from_count 有變更，重新計算 calculated_total_count
            if (isset($data['exclude_owner_from_count'])) {
                $eligibleVoterModel = model('MeetingEligibleVoterModel');
                $snapshotStats = $eligibleVoterModel->getSnapshotStatistics($id);
                $baseCount = $snapshotStats['total_voters'];
                
                $calculatedTotalCount = $baseCount;
                if ($data['exclude_owner_from_count']) {
                    $calculatedTotalCount = max(0, $baseCount - 1);
                }
                
                $this->meetingModel->update($id, [
                    'calculated_total_count' => $calculatedTotalCount
                ]);
            }

            $updatedMeeting = $this->meetingModel->getMeetingWithDetails($id);

            return $this->respond([
                'success' => true,
                'data' => $updatedMeeting,
                'message' => '會議更新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Update meeting error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '會議更新失敗'
                ]
            ], 500);
        }
    }

    /**
     * 刪除會議
     * DELETE /api/meetings/{id}
     */
    public function delete($id = null)
    {
        try {
            $meeting = $this->meetingModel->find($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限刪除此會議'
                        ]
                    ], 403);
                }
            }

            // 檢查會議狀態，已進行中或已完成的會議不能刪除
            if (in_array($meeting['meeting_status'], ['in_progress', 'completed'])) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'BUSINESS_LOGIC_ERROR',
                        'message' => '進行中或已完成的會議不能刪除'
                    ]
                ], 422);
            }

            // 檢查是否有投票議題
            $votingTopicModel = model('VotingTopicModel');
            $hasVotingTopics = $votingTopicModel->where('meeting_id', $id)->countAllResults() > 0;

            if ($hasVotingTopics) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'BUSINESS_LOGIC_ERROR',
                        'message' => '有投票議題的會議不能刪除，請先刪除相關議題'
                    ]
                ], 422);
            }

            $success = $this->meetingModel->delete($id);

            if (!$success) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '會議刪除失敗'
                    ]
                ], 500);
            }

            return $this->respondDeleted([
                'success' => true,
                'message' => '會議刪除成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Delete meeting error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '會議刪除失敗'
                ]
            ], 500);
        }
    }

    /**
     * 取得會議統計資料
     * GET /api/meetings/{id}/statistics
     */
    public function statistics($id = null)
    {
        try {
            $meeting = $this->meetingModel->find($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            $statistics = $this->meetingModel->getMeetingStatistics($id);

            return $this->respond([
                'success' => true,
                'data' => $statistics,
                'message' => '取得會議統計成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get meeting statistics error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得會議統計失敗'
                ]
            ], 500);
        }
    }

    /**
     * 更新會議狀態
     * PUT /api/meetings/{id}/status
     */
    public function updateStatus($id = null)
    {
        try {
            $meeting = $this->meetingModel->find($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            $rules = [
                'status' => 'required|in_list[draft,scheduled,in_progress,completed,cancelled]'
            ];

            if (!$this->validate($rules)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '驗證失敗',
                        'details' => $this->validator->getErrors()
                    ]
                ], 422);
            }

            $data = $this->request->getJSON(true);
            $newStatus = $data['status'];

            // 檢查狀態轉換是否合法
            $validTransitions = [
                'draft' => ['scheduled', 'cancelled'],
                'scheduled' => ['in_progress', 'cancelled'],
                'in_progress' => ['completed', 'cancelled'],
                'completed' => [], // 已完成的會議不能再變更狀態
                'cancelled' => ['draft'] // 已取消的會議可以重新開始
            ];

            $currentStatus = $meeting['meeting_status'];
            if (!in_array($newStatus, $validTransitions[$currentStatus])) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'BUSINESS_LOGIC_ERROR',
                        'message' => "無法從狀態「{$currentStatus}」變更為「{$newStatus}」"
                    ]
                ], 422);
            }

            $success = $this->meetingModel->update($id, ['meeting_status' => $newStatus]);

            if (!$success) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '會議狀態更新失敗'
                    ]
                ], 500);
            }

            $updatedMeeting = $this->meetingModel->getMeetingWithDetails($id);

            return $this->respond([
                'success' => true,
                'data' => $updatedMeeting,
                'message' => '會議狀態更新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Update meeting status error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '會議狀態更新失敗'
                ]
            ], 500);
        }
    }

    /**
     * 匯出會議通知
     * GET /api/meetings/{id}/export-notice
     */
    public function exportNotice($id = null)
    {
        try {
            log_message('info', '[ExportNotice] Start exporting meeting notice for ID: ' . $id);

            $meeting = $this->meetingModel->getMeetingWithDetails($id);
            if (!$meeting) {
                log_message('warning', '[ExportNotice] Meeting not found for ID: ' . $id);
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            log_message('info', '[ExportNotice] Meeting found: ' . json_encode([
                'id' => $meeting['id'],
                'title' => $meeting['title'] ?? 'N/A',
                'urban_renewal_id' => $meeting['urban_renewal_id'] ?? 'N/A'
            ]));

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            log_message('info', '[ExportNotice] User info: ' . json_encode([
                'user_id' => $user['id'] ?? 'N/A',
                'role' => $user['role'] ?? 'N/A',
                'is_company_manager' => $user['is_company_manager'] ?? 'N/A',
                'urban_renewal_id' => $user['urban_renewal_id'] ?? 'N/A'
            ]));

            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    log_message('warning', '[ExportNotice] Permission denied - user cannot access this renewal');
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限匯出此會議通知'
                        ]
                    ], 403);
                }
            }

            log_message('info', '[ExportNotice] Permission check passed');

            // 使用 WordExportService 匯出
            log_message('info', '[ExportNotice] Calling WordExportService...');
            $wordExportService = new \App\Services\WordExportService();
            $result = $wordExportService->exportMeetingNotice($meeting);

            log_message('info', '[ExportNotice] WordExportService result: ' . json_encode([
                'success' => $result['success'] ?? false,
                'filepath' => $result['filepath'] ?? 'N/A',
                'filename' => $result['filename'] ?? 'N/A',
                'error' => $result['error'] ?? 'N/A'
            ]));

            if (!$result['success']) {
                log_message('error', '[ExportNotice] WordExportService failed: ' . ($result['error'] ?? 'Unknown error'));
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'EXPORT_ERROR',
                        'message' => $result['error']
                    ]
                ], 500);
            }

            // 回傳檔案下載
            $filepath = $result['filepath'];
            $filename = $result['filename'];

            log_message('info', '[ExportNotice] Checking file exists: ' . $filepath);
            if (!file_exists($filepath)) {
                log_message('error', '[ExportNotice] File not found: ' . $filepath);
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'FILE_NOT_FOUND',
                        'message' => '匯出檔案不存在'
                    ]
                ], 404);
            }

            $filesize = filesize($filepath);
            log_message('info', '[ExportNotice] File exists, size: ' . $filesize . ' bytes');

            // 設定檔案下載 header
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
            header('Content-Length: ' . $filesize);
            header('Cache-Control: max-age=0');

            log_message('info', '[ExportNotice] Sending file to client: ' . $filename);

            // 讀取並輸出檔案
            readfile($filepath);

            // 刪除臨時檔案（可選）
            // unlink($filepath);

            log_message('info', '[ExportNotice] Export completed successfully');
            exit;

        } catch (\Exception $e) {
            log_message('error', '[ExportNotice] Exception: ' . $e->getMessage());
            log_message('error', '[ExportNotice] Stack trace: ' . $e->getTraceAsString());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '匯出會議通知失敗'
                ]
            ], 500);
        }
    }

    /**
     * 匯出簽到冊
     * GET /api/meetings/{id}/export-signature-book
     */
    public function exportSignatureBook($id = null)
    {
        try {
            log_message('info', '[ExportSignatureBook] Start exporting signature book for ID: ' . $id);

            $meeting = $this->meetingModel->getMeetingWithDetails($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限匯出此會議簽到冊'
                        ]
                    ], 403);
                }
            }

            $isAnonymous = $this->request->getGet('anonymous') === 'true';
            log_message('info', '[ExportSignatureBook] Anonymous mode: ' . ($isAnonymous ? 'Yes' : 'No'));

            $wordExportService = new \App\Services\WordExportService();
            $result = $wordExportService->exportSignatureBook($meeting, $isAnonymous);

            if (!$result['success']) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'EXPORT_ERROR',
                        'message' => $result['error']
                    ]
                ], 500);
            }

            // 回傳檔案下載
            $filepath = $result['filepath'];
            $filename = $result['filename'];

            if (!file_exists($filepath)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'FILE_NOT_FOUND',
                        'message' => '匯出檔案不存在'
                    ]
                ], 404);
            }

            $filesize = filesize($filepath);

            // 設定檔案下載 header
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
            header('Content-Length: ' . $filesize);
            header('Cache-Control: max-age=0');

            // 讀取並輸出檔案
            readfile($filepath);
            exit;

        } catch (\Exception $e) {
            log_message('error', '[ExportSignatureBook] Exception: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '匯出簽到冊失敗'
                ]
            ], 500);
        }
    }

    /**
     * 取得會議的合格投票人快照
     * GET /api/meetings/{id}/eligible-voters
     */
    public function getEligibleVoters($id = null)
    {
        try {
            $meeting = $this->meetingModel->find($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限存取此會議的投票人名單'
                        ]
                    ], 403);
                }
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 100;

            $eligibleVoterModel = model('MeetingEligibleVoterModel');
            
            // 檢查是否有快照
            if (!$eligibleVoterModel->hasSnapshot($id)) {
                return $this->respond([
                    'success' => true,
                    'data' => [],
                    'statistics' => [
                        'total_voters' => 0,
                        'total_land_area' => 0,
                        'total_building_area' => 0,
                        'snapshot_at' => null
                    ],
                    'has_snapshot' => false,
                    'message' => '此會議尚未建立投票人快照'
                ]);
            }

            $voters = $eligibleVoterModel->getByMeetingIdPaginated($id, $page, $perPage);
            $statistics = $eligibleVoterModel->getSnapshotStatistics($id);
            $pager = $eligibleVoterModel->pager;

            return $this->respond([
                'success' => true,
                'data' => $voters,
                'statistics' => $statistics,
                'has_snapshot' => true,
                'pagination' => [
                    'current_page' => $pager->getCurrentPage(),
                    'per_page' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'total_pages' => $pager->getPageCount()
                ],
                'message' => '取得合格投票人名單成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get eligible voters error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得合格投票人名單失敗'
                ]
            ], 500);
        }
    }

    /**
     * 重新整理會議的合格投票人快照
     * POST /api/meetings/{id}/eligible-voters/refresh
     */
    public function refreshEligibleVoters($id = null)
    {
        try {
            $meeting = $this->meetingModel->find($id);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // Get authenticated user and check permission
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限重新整理此會議的投票人名單'
                        ]
                    ], 403);
                }
            }

            // 檢查會議狀態，只有草稿或已排程的會議可以重新整理快照
            if (!in_array($meeting['meeting_status'], ['draft', 'scheduled'])) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'BUSINESS_LOGIC_ERROR',
                        'message' => '只有草稿或已排程的會議可以重新整理投票人名單'
                    ]
                ], 422);
            }

            $eligibleVoterModel = model('MeetingEligibleVoterModel');
            $result = $eligibleVoterModel->recreateSnapshot($id, $meeting['urban_renewal_id']);

            if (!$result['success']) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => $result['error'] ?? '重新整理投票人名單失敗'
                    ]
                ], 500);
            }

            // 更新會議的納入計算總人數
            $this->meetingModel->update($id, [
                'calculated_total_count' => $result['snapshot_count']
            ]);

            return $this->respond([
                'success' => true,
                'data' => $result,
                'message' => '投票人名單已重新整理'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Refresh eligible voters error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '重新整理投票人名單失敗'
                ]
            ], 500);
        }
    }
}