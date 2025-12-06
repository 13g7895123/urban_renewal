<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Traits\HasRbacPermissions;

class VotingTopicController extends ResourceController
{
    use HasRbacPermissions;

    protected $modelName = 'App\Models\VotingTopicModel';
    protected $format = 'json';

    public function __construct()
    {
        $this->loadHelpers();
    }

    private function loadHelpers()
    {
        helper(['auth', 'response']);
    }

    /**
     * 取得投票議題列表
     */
    public function index()
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $meetingId = $this->request->getGet('meeting_id');
            $status = $this->request->getGet('status');
            $keyword = $this->request->getGet('search');

            // For company managers, need to filter by their company
            if (!$isAdmin && $isCompanyManager) {
                // Get user's company_id
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);
                
                if ($userCompanyId) {
                    // Get all urban renewals for user's company
                    $urbanRenewalModel = model('UrbanRenewalModel');
                    $companyRenewals = $urbanRenewalModel->where('company_id', $userCompanyId)->findAll();
                    $renewalIds = array_column($companyRenewals, 'id');
                    
                    // Get all meetings for these renewals
                    $meetingModel = model('MeetingModel');
                    if (!empty($renewalIds)) {
                        $allowedMeetings = $meetingModel->whereIn('urban_renewal_id', $renewalIds)->findAll();
                    } else {
                        $allowedMeetings = [];
                    }
                    $allowedMeetingIds = array_column($allowedMeetings, 'id');

                    // If specific meetingId requested, verify permission
                    if ($meetingId && !in_array($meetingId, $allowedMeetingIds)) {
                        return $this->fail([
                            'success' => false,
                            'error' => [
                                'code' => 'FORBIDDEN',
                                'message' => '您沒有權限存取此會議的投票議題'
                            ]
                        ], 403);
                    }

                    // Filter topics by allowed meetings
                    if ($keyword) {
                        $filters = [
                            'meeting_id' => $meetingId,
                            'voting_status' => $status,
                            'allowed_meeting_ids' => $allowedMeetingIds
                        ];
                        $topics = $this->model->searchTopics($keyword, $page, $perPage, $filters);
                    } elseif ($meetingId) {
                        $topics = $this->model->getTopicsByMeeting($meetingId, $page, $perPage, $status);
                    } else {
                        // Get all topics for allowed meetings
                        if (!empty($allowedMeetingIds)) {
                            $this->model->whereIn('meeting_id', $allowedMeetingIds);
                        } else {
                            $this->model->where('1', '0'); // No results
                        }
                        $topics = $this->model->paginate($perPage, 'default', $page);
                    }
                } else {
                    // No company association, return empty
                    $topics = [];
                }
            } else {
                // Admin can see all
                if ($keyword) {
                    $filters = [
                        'meeting_id' => $meetingId,
                        'voting_status' => $status
                    ];
                    $topics = $this->model->searchTopics($keyword, $page, $perPage, $filters);
                } elseif ($meetingId) {
                    $topics = $this->model->getTopicsByMeeting($meetingId, $page, $perPage, $status);
                } else {
                    $topics = $this->model->paginate($perPage, 'default', $page);
                }
            }

            return response_success('投票議題列表', [
                'topics' => $topics,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得投票議題列表失敗: ' . $e->getMessage());
            return response_error('取得投票議題列表失敗', 500);
        }
    }

    /**
     * 取得投票議題詳情
     */
    public function show($id = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('議題ID為必填', 400);
            }

            $topic = $this->model->getTopicWithDetails($id);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            // Check permission - need to get meeting's urban_renewal_id
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$isAdmin && $isCompanyManager) {
                $meetingModel = model('MeetingModel');
                $meeting = $meetingModel->find($topic['meeting_id']);
                
                helper('auth');
                if (!$meeting || !auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限查看此投票議題'
                        ]
                    ], 403);
                }
            }

            return response_success('投票議題詳情', $topic);

        } catch (\Exception $e) {
            log_message('error', '取得投票議題詳情失敗: ' . $e->getMessage());
            return response_error('取得投票議題詳情失敗', 500);
        }
    }

    /**
     * 建立投票議題
     */
    public function create()
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            $data = $this->request->getJSON(true);

            // Map frontend fields to backend fields
            if (isset($data['topic_name']) && !isset($data['topic_title'])) {
                $data['topic_title'] = $data['topic_name'];
            }

            // Auto-generate topic number if not provided
            if (empty($data['topic_number'])) {
                $count = $this->model->where('meeting_id', $data['meeting_id'])->countAllResults();
                $data['topic_number'] = 'Topic-' . ($count + 1);
            }

            // Set default voting method if not provided
            if (empty($data['voting_method'])) {
                $data['voting_method'] = 'simple_majority';
            }

            // 驗證必填欄位
            $validation = \Config\Services::validation();
            $validation->setRules([
                'meeting_id' => 'required|integer',
                'topic_number' => 'required|max_length[20]',
                'topic_title' => 'required|max_length[500]',
                'voting_method' => 'permit_empty|in_list[simple_majority,absolute_majority,two_thirds_majority,unanimous]'
            ]);

            if (!$validation->run($data)) {
                return response_error('資料驗證失敗', 400, $validation->getErrors());
            }

            // 檢查會議是否存在且用戶有權限
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($data['meeting_id']);
            if (!$meeting) {
                return response_error('找不到該會議', 404);
            }

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限在此會議建立投票議題'
                        ]
                    ], 403);
                }
            }

            // 檢查議題編號是否重複
            $existingTopic = $this->model->checkTopicNumberExists($data['meeting_id'], $data['topic_number']);
            if ($existingTopic) {
                return response_error('議題編號已存在', 400);
            }

            // 設定預設值
            $data = array_merge([
                'voting_status' => 'draft',
                'voting_result' => 'pending',
                'total_votes' => 0,
                'agree_votes' => 0,
                'disagree_votes' => 0,
                'abstain_votes' => 0,
                'total_land_area' => 0,
                'agree_land_area' => 0,
                'disagree_land_area' => 0,
                'abstain_land_area' => 0,
                'total_building_area' => 0,
                'agree_building_area' => 0,
                'disagree_building_area' => 0,
                'abstain_building_area' => 0
            ], $data);

            $topicId = $this->model->insert($data);
            if (!$topicId) {
                return response_error('建立投票議題失敗', 500, $this->model->errors());
            }

            // Handle property owners (voting options)
            if (isset($data['property_owners']) && is_array($data['property_owners'])) {
                $votingOptionModel = model('VotingOptionModel');
                foreach ($data['property_owners'] as $index => $owner) {
                    if (!empty($owner['name'])) {
                        $votingOptionModel->insert([
                            'voting_topic_id' => $topicId,
                            'option_name' => $owner['name'],
                            'is_pinned' => isset($owner['is_pinned']) ? $owner['is_pinned'] : 0,
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            // Handle voting choices (同意/不同意 options)
            $votingChoiceModel = model('VotingChoiceModel');
            if (isset($data['voting_options']) && is_array($data['voting_options']) && count($data['voting_options']) > 0) {
                foreach ($data['voting_options'] as $index => $option) {
                    if (!empty($option['name'])) {
                        $votingChoiceModel->insert([
                            'voting_topic_id' => $topicId,
                            'choice_name' => $option['name'],
                            'sort_order' => $index
                        ]);
                    }
                }
            } else {
                // Create default choices if not provided
                $votingChoiceModel->createDefaultChoices($topicId);
            }

            $topic = $this->model->getTopicWithDetails($topicId);
            return response_success('投票議題建立成功', $topic, 201);

        } catch (\Exception $e) {
            log_message('error', '建立投票議題失敗: ' . $e->getMessage());
            return response_error('建立投票議題失敗', 500);
        }
    }

    /**
     * 更新投票議題
     */
    public function update($id = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return response_error('議題ID為必填', 400);
            }

            $topic = $this->model->find($id);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            // Check permission for company managers
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限修改此投票議題'
                        ]
                    ], 403);
                }
            }

            // 檢查議題狀態（只有草稿狀態可以修改）
            if ($topic['voting_status'] !== 'draft') {
                return response_error('只有草稿狀態的議題可以修改', 400);
            }

            $data = $this->request->getJSON(true);

            // Map frontend fields to backend fields
            if (isset($data['topic_name']) && !isset($data['topic_title'])) {
                $data['topic_title'] = $data['topic_name'];
            }

            // 如果要修改議題編號，檢查是否重複
            if (isset($data['topic_number']) && $data['topic_number'] !== $topic['topic_number']) {
                $existingTopic = $this->model->checkTopicNumberExists($topic['meeting_id'], $data['topic_number'], $id);
                if ($existingTopic) {
                    return response_error('議題編號已存在', 400);
                }
            }

            // 移除不允許修改的統計欄位
            unset($data['total_votes'], $data['agree_votes'], $data['disagree_votes'], $data['abstain_votes']);
            unset($data['total_land_area'], $data['agree_land_area'], $data['disagree_land_area'], $data['abstain_land_area']);
            unset($data['total_building_area'], $data['agree_building_area'], $data['disagree_building_area'], $data['abstain_building_area']);

            $success = $this->model->update($id, $data);
            if (!$success) {
                return response_error('更新投票議題失敗', 500, $this->model->errors());
            }

            // Handle property owners (voting options)
            if (isset($data['property_owners']) && is_array($data['property_owners'])) {
                $votingOptionModel = model('VotingOptionModel');
                
                // Delete existing options
                $votingOptionModel->where('voting_topic_id', $id)->delete();
                
                // Insert new options
                foreach ($data['property_owners'] as $index => $owner) {
                    if (!empty($owner['name'])) {
                        $votingOptionModel->insert([
                            'voting_topic_id' => $id,
                            'option_name' => $owner['name'],
                            'is_pinned' => isset($owner['is_pinned']) ? $owner['is_pinned'] : 0,
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            // Handle voting choices (同意/不同意 options)
            if (isset($data['voting_options']) && is_array($data['voting_options'])) {
                $votingChoiceModel = model('VotingChoiceModel');
                
                // Delete existing choices
                $votingChoiceModel->where('voting_topic_id', $id)->delete();
                
                // Insert new choices
                foreach ($data['voting_options'] as $index => $option) {
                    if (!empty($option['name'])) {
                        $votingChoiceModel->insert([
                            'voting_topic_id' => $id,
                            'choice_name' => $option['name'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            $topic = $this->model->getTopicWithDetails($id);
            return response_success('投票議題更新成功', $topic);

        } catch (\Exception $e) {
            log_message('error', '更新投票議題失敗: ' . $e->getMessage());
            return response_error('更新投票議題失敗', 500);
        }
    }

    /**
     * 刪除投票議題
     */
    public function delete($id = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return response_error('議題ID為必填', 400);
            }

            $topic = $this->model->find($id);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            // Check permission for company managers
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            
            if (!$isAdmin && $isCompanyManager) {
                helper('auth');
                if (!auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'FORBIDDEN',
                            'message' => '您沒有權限刪除此投票議題'
                        ]
                    ], 403);
                }
            }

            // 檢查議題狀態（只有草稿狀態可以刪除）
            if ($topic['voting_status'] !== 'draft') {
                return response_error('只有草稿狀態的議題可以刪除', 400);
            }

            $success = $this->model->delete($id);
            if (!$success) {
                return response_error('刪除投票議題失敗', 500);
            }

            return response_success('投票議題刪除成功');

        } catch (\Exception $e) {
            log_message('error', '刪除投票議題失敗: ' . $e->getMessage());
            return response_error('刪除投票議題失敗', 500);
        }
    }

    /**
     * 啟動投票
     */
    public function startVoting($id = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('議題ID為必填', 400);
            }

            $topic = $this->model->find($id);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            // 檢查用戶權限
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            helper('auth');
            if ($user['role'] !== 'admin' && !auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                return $this->failForbidden('無權限啟動此議題投票');
            }

            $success = $this->model->startVoting($id);
            if (!$success) {
                return response_error('啟動投票失敗', 400);
            }

            $topic = $this->model->find($id);
            return response_success('投票已啟動', $topic);

        } catch (\Exception $e) {
            log_message('error', '啟動投票失敗: ' . $e->getMessage());
            return response_error('啟動投票失敗', 500);
        }
    }

    /**
     * 結束投票
     */
    public function closeVoting($id = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('議題ID為必填', 400);
            }

            $topic = $this->model->find($id);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            // 檢查用戶權限
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            helper('auth');
            if ($user['role'] !== 'admin' && !auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                return $this->failForbidden('無權限結束此議題投票');
            }

            $success = $this->model->closeVoting($id);
            if (!$success) {
                return response_error('結束投票失敗', 400);
            }

            $topic = $this->model->getTopicWithDetails($id);
            return response_success('投票已結束', $topic);

        } catch (\Exception $e) {
            log_message('error', '結束投票失敗: ' . $e->getMessage());
            return response_error('結束投票失敗', 500);
        }
    }

    /**
     * 取得投票統計
     */
    public function statistics()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $meetingId = $this->request->getGet('meeting_id');

            // 如果不是admin，則限制只能查看自己企業的統計
            if ($user['role'] !== 'admin' && $meetingId) {
                $meetingModel = model('MeetingModel');
                $meeting = $meetingModel->find($meetingId);
                helper('auth');
                if (!$meeting || !auth_check_company_access((int)$meeting['urban_renewal_id'], $user)) {
                    return $this->failForbidden('無權限查看此統計');
                }
            }

            $statistics = $this->model->getVotingStatistics($meetingId);

            return response_success('投票統計', [
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得投票統計失敗: ' . $e->getMessage());
            return response_error('取得投票統計失敗', 500);
        }
    }

    /**
     * 取得即將投票的議題
     */
    public function upcoming()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $days = $this->request->getGet('days') ?? 7;
            $topics = $this->model->getUpcomingTopics($days);

            // 如果不是admin，過濾只顯示自己企業的議題
            if ($user['role'] !== 'admin') {
                helper('auth');
                $topics = array_filter($topics, function($topic) use ($user) {
                    $meetingModel = model('MeetingModel');
                    $meeting = $meetingModel->find($topic['meeting_id']);
                    return $meeting && auth_check_company_access((int)$meeting['urban_renewal_id'], $user);
                });
            }

            return response_success('即將投票的議題', [
                'topics' => array_values($topics)
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得即將投票議題失敗: ' . $e->getMessage());
            return response_error('取得即將投票議題失敗', 500);
        }
    }
}