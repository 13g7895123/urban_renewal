<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class VotingController extends ResourceController
{
    protected $modelName = 'App\Models\VotingRecordModel';
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
     * 取得投票記錄列表
     */
    public function index()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $topicId = $this->request->getGet('topic_id');
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $choice = $this->request->getGet('choice');

            if (!$topicId) {
                return response_error('議題ID為必填', 400);
            }

            // 檢查用戶權限
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($topicId);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限查看此投票記錄');
            }

            $records = $this->model->getVotingRecords($topicId, $page, $perPage, $choice);

            return response_success('投票記錄列表', [
                'records' => $records,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得投票記錄列表失敗: ' . $e->getMessage());
            return response_error('取得投票記錄列表失敗', 500);
        }
    }

    /**
     * 投票
     */
    public function vote()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            $validation = \Config\Services::validation();
            $validation->setRules([
                'topic_id' => 'required|integer',
                'property_owner_id' => 'required|integer',
                'choice' => 'required|in_list[agree,disagree,abstain]'
            ]);

            if (!$validation->run($data)) {
                return response_error('資料驗證失敗', 400, $validation->getErrors());
            }

            // 檢查投票議題是否存在且可投票
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($data['topic_id']);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            if ($topic['voting_status'] !== 'active') {
                return response_error('該議題目前不開放投票', 400);
            }

            // 檢查所有權人是否存在
            $propertyOwnerModel = model('PropertyOwnerModel');
            $owner = $propertyOwnerModel->find($data['property_owner_id']);
            if (!$owner) {
                return response_error('找不到該所有權人', 404);
            }

            // 檢查用戶權限（只能為自己投票或代理投票）
            if ($user['role'] === 'member') {
                if ($user['property_owner_id'] !== $data['property_owner_id']) {
                    return $this->failForbidden('只能為自己投票');
                }
            } elseif ($user['role'] !== 'admin' && $user['role'] !== 'chairman') {
                // 檢查是否為同一個更新會
                $meetingModel = model('MeetingModel');
                $meeting = $meetingModel->find($topic['meeting_id']);
                if ($user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                    return $this->failForbidden('無權限在此議題投票');
                }
            }

            // 執行投票
            $success = $this->model->castVote(
                $data['topic_id'],
                $data['property_owner_id'],
                $data['choice'],
                $data['voter_name'] ?? null,
                $data['notes'] ?? null
            );

            if (!$success) {
                return response_error('投票失敗', 500, $this->model->errors());
            }

            // 更新議題統計
            $votingTopicModel->calculateVotingResult($data['topic_id']);

            // 取得投票記錄
            $voteRecord = $this->model->getUserVote($data['topic_id'], $data['property_owner_id']);

            return response_success('投票成功', $voteRecord);

        } catch (\Exception $e) {
            log_message('error', '投票失敗: ' . $e->getMessage());
            return response_error('投票失敗', 500);
        }
    }

    /**
     * 批量投票
     */
    public function batchVote()
    {
        try {
            // 驗證用戶權限（只有主席和管理員可以批量投票）
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['votes']) || !is_array($data['votes'])) {
                return response_error('投票資料格式錯誤', 400);
            }

            $results = $this->model->batchCastVotes($data['votes']);

            // 更新相關議題的統計
            $topicIds = array_unique(array_column($data['votes'], 'topic_id'));
            $votingTopicModel = model('VotingTopicModel');
            foreach ($topicIds as $topicId) {
                $votingTopicModel->calculateVotingResult($topicId);
            }

            return response_success('批量投票完成', $results);

        } catch (\Exception $e) {
            log_message('error', '批量投票失敗: ' . $e->getMessage());
            return response_error('批量投票失敗', 500);
        }
    }

    /**
     * 查看我的投票
     */
    public function myVote($topicId = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$topicId) {
                return response_error('議題ID為必填', 400);
            }

            if (!$user['property_owner_id']) {
                return response_error('您尚未綁定所有權人資料', 400);
            }

            $vote = $this->model->getUserVote($topicId, $user['property_owner_id']);

            return response_success('我的投票', [
                'vote' => $vote,
                'has_voted' => $vote !== null
            ]);

        } catch (\Exception $e) {
            log_message('error', '查看我的投票失敗: ' . $e->getMessage());
            return response_error('查看我的投票失敗', 500);
        }
    }

    /**
     * 撤回投票
     */
    public function removeVote()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['topic_id']) || !isset($data['property_owner_id'])) {
                return response_error('議題ID和所有權人ID為必填', 400);
            }

            // 檢查投票議題狀態
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($data['topic_id']);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            if ($topic['voting_status'] !== 'active') {
                return response_error('該議題目前不開放撤回投票', 400);
            }

            // 檢查權限（只能撤回自己的投票或代理投票）
            if ($user['role'] === 'member' && $user['property_owner_id'] !== $data['property_owner_id']) {
                return $this->failForbidden('只能撤回自己的投票');
            }

            // 檢查是否已投票
            $hasVoted = $this->model->hasVoted($data['topic_id'], $data['property_owner_id']);
            if (!$hasVoted) {
                return response_error('尚未投票，無法撤回', 400);
            }

            $success = $this->model->removeVote($data['topic_id'], $data['property_owner_id']);
            if (!$success) {
                return response_error('撤回投票失敗', 500);
            }

            // 更新議題統計
            $votingTopicModel->calculateVotingResult($data['topic_id']);

            return response_success('投票已撤回');

        } catch (\Exception $e) {
            log_message('error', '撤回投票失敗: ' . $e->getMessage());
            return response_error('撤回投票失敗', 500);
        }
    }

    /**
     * 取得投票統計
     */
    public function statistics($topicId = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$topicId) {
                return response_error('議題ID為必填', 400);
            }

            // 檢查用戶權限
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($topicId);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限查看此投票統計');
            }

            $statistics = $this->model->getVotingStatistics($topicId);
            $participation = $this->model->getParticipationStatistics($topicId);

            return response_success('投票統計', [
                'statistics' => $statistics,
                'participation' => $participation
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得投票統計失敗: ' . $e->getMessage());
            return response_error('取得投票統計失敗', 500);
        }
    }

    /**
     * 匯出投票記錄
     */
    public function export($topicId = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$topicId) {
                return response_error('議題ID為必填', 400);
            }

            // 檢查用戶權限
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($topicId);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限匯出此投票記錄');
            }

            $format = $this->request->getGet('format') ?? 'csv';
            $data = $this->model->exportVotingRecords($topicId, $format);

            if ($format === 'csv') {
                return $this->response
                    ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                    ->setHeader('Content-Disposition', 'attachment; filename="voting_records_' . $topicId . '.csv"')
                    ->setBody("\xEF\xBB\xBF" . $data); // 加上BOM以支援中文
            }

            return response_success('投票記錄匯出', $data);

        } catch (\Exception $e) {
            log_message('error', '匯出投票記錄失敗: ' . $e->getMessage());
            return response_error('匯出投票記錄失敗', 500);
        }
    }

    /**
     * 取得詳細投票記錄
     */
    public function detailed($topicId = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$topicId) {
                return response_error('議題ID為必填', 400);
            }

            // 檢查用戶權限
            $votingTopicModel = model('VotingTopicModel');
            $topic = $votingTopicModel->find($topicId);
            if (!$topic) {
                return response_error('找不到該投票議題', 404);
            }

            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($topic['meeting_id']);
            if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限查看此投票記錄');
            }

            $records = $this->model->getDetailedVotingRecords($topicId);

            return response_success('詳細投票記錄', [
                'topic' => $topic,
                'records' => $records
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得詳細投票記錄失敗: ' . $e->getMessage());
            return response_error('取得詳細投票記錄失敗', 500);
        }
    }
}