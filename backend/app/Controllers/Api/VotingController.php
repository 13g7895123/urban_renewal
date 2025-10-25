<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Traits\HasRbacPermissions;

class VotingController extends ResourceController
{
    use HasRbacPermissions;

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

            // 取得詳細投票記錄
            $records = $this->model->getDetailedVotingRecords($topicId);
            $statistics = $this->model->getVotingStatistics($topicId);

            // 準備匯出資料
            $data = [
                'topic' => $topic,
                'meeting' => $meeting,
                'records' => $records,
                'statistics' => $statistics
            ];

            // 使用 XLSX 格式匯出
            $format = $this->request->getGet('format') ?? 'xlsx';

            if ($format === 'xlsx') {
                $filePath = $this->generateExcelReport($data, $topicId);

                return $this->response
                    ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
                    ->setBody(file_get_contents($filePath));
            }

            // 相容舊版 CSV 格式（如需要）
            if ($format === 'csv') {
                $csvData = $this->model->exportVotingRecords($topicId, 'csv');
                return $this->response
                    ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                    ->setHeader('Content-Disposition', 'attachment; filename="voting_records_' . $topicId . '.csv"')
                    ->setBody("\xEF\xBB\xBF" . $csvData);
            }

            return response_success('投票記錄匯出', $records);

        } catch (\Exception $e) {
            log_message('error', '匯出投票記錄失敗: ' . $e->getMessage());
            return response_error('匯出投票記錄失敗: ' . $e->getMessage(), 500);
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

    /**
     * 產生 Excel 報表
     */
    private function generateExcelReport($data, $topicId)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 設定文件屬性
        $spreadsheet->getProperties()
            ->setCreator('都市更新會管理系統')
            ->setTitle('投票記錄報表')
            ->setSubject('投票記錄')
            ->setDescription('投票議題記錄匯出');

        // 設定標題
        $sheet->setCellValue('A1', '投票記錄報表');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 議題資訊
        $row = 3;
        $sheet->setCellValue('A' . $row, '議題名稱：' . ($data['topic']['title'] ?? ''));
        $row++;
        $sheet->setCellValue('A' . $row, '議題說明：' . ($data['topic']['description'] ?? ''));
        $row++;
        $sheet->setCellValue('A' . $row, '會議名稱：' . ($data['meeting']['name'] ?? ''));
        $row += 2;

        // 統計資訊
        $sheet->setCellValue('A' . $row, '投票統計');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        $statistics = $data['statistics'];
        $sheet->setCellValue('A' . $row, '選項');
        $sheet->setCellValue('B' . $row, '票數');
        $sheet->setCellValue('C' . $row, '土地面積權重');
        $sheet->setCellValue('D' . $row, '建物面積權重');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $row++;

        // 同意票
        $sheet->setCellValue('A' . $row, '同意');
        $sheet->setCellValue('B' . $row, $statistics['agree_votes'] ?? 0);
        $sheet->setCellValue('C' . $row, number_format($statistics['agree_land_area'] ?? 0, 2));
        $sheet->setCellValue('D' . $row, number_format($statistics['agree_building_area'] ?? 0, 2));
        $row++;

        // 不同意票
        $sheet->setCellValue('A' . $row, '不同意');
        $sheet->setCellValue('B' . $row, $statistics['disagree_votes'] ?? 0);
        $sheet->setCellValue('C' . $row, number_format($statistics['disagree_land_area'] ?? 0, 2));
        $sheet->setCellValue('D' . $row, number_format($statistics['disagree_building_area'] ?? 0, 2));
        $row++;

        // 棄權票
        $sheet->setCellValue('A' . $row, '棄權');
        $sheet->setCellValue('B' . $row, $statistics['abstain_votes'] ?? 0);
        $sheet->setCellValue('C' . $row, number_format($statistics['abstain_land_area'] ?? 0, 2));
        $sheet->setCellValue('D' . $row, number_format($statistics['abstain_building_area'] ?? 0, 2));
        $row++;

        // 總計
        $sheet->setCellValue('A' . $row, '總計');
        $sheet->setCellValue('B' . $row, $statistics['total_votes'] ?? 0);
        $sheet->setCellValue('C' . $row, number_format($statistics['total_land_area'] ?? 0, 2));
        $sheet->setCellValue('D' . $row, number_format($statistics['total_building_area'] ?? 0, 2));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // 詳細投票記錄表頭
        $sheet->setCellValue('A' . $row, '詳細投票記錄');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        $headers = ['投票時間', '所有權人姓名', '身分證字號', '投票選擇', '土地面積權重', '建物面積權重', '備註'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // 設定表頭樣式
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');

        // 設定表頭邊框
        $headerRow = $row;
        $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $row++;

        // 填入投票記錄資料
        $records = $data['records'];
        foreach ($records as $record) {
            $voteChoiceText = '';
            switch ($record['vote_choice']) {
                case 'agree':
                    $voteChoiceText = '同意';
                    break;
                case 'disagree':
                    $voteChoiceText = '不同意';
                    break;
                case 'abstain':
                    $voteChoiceText = '棄權';
                    break;
                default:
                    $voteChoiceText = $record['vote_choice'];
            }

            $sheet->setCellValue('A' . $row, $record['vote_time']);
            $sheet->setCellValue('B' . $row, $record['owner_name']);
            $sheet->setCellValue('C' . $row, $record['id_number']);
            $sheet->setCellValue('D' . $row, $voteChoiceText);
            $sheet->setCellValue('E' . $row, number_format($record['land_area_weight'] ?? 0, 2));
            $sheet->setCellValue('F' . $row, number_format($record['building_area_weight'] ?? 0, 2));
            $sheet->setCellValue('G' . $row, $record['notes'] ?? '');

            // 設定資料邊框
            $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $row++;
        }

        // 自動調整欄寬
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 儲存檔案
        $filename = '投票記錄_' . $topicId . '_' . date('YmdHis') . '.xlsx';
        $filepath = WRITEPATH . 'exports/' . $filename;

        // 確保目錄存在
        if (!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0755, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }
}