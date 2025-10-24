<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\UrbanRenewalModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\HasRbacPermissions;

class MeetingController extends BaseController
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
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $urbanRenewalId = $this->request->getGet('urban_renewal_id');
            $status = $this->request->getGet('status');
            $search = $this->request->getGet('search');

            $filters = [];
            if ($urbanRenewalId) $filters['urban_renewal_id'] = $urbanRenewalId;
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
            $data['attendee_count'] = 0;
            $data['calculated_total_count'] = 0;
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

            $meeting = $this->meetingModel->getMeetingWithDetails($meetingId);

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
}