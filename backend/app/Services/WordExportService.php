<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;

/**
 * Word 文件匯出共用服務
 *
 * 提供統一的 Word 文件匯出功能，處理會議通知等文件
 */
class WordExportService
{
    /**
     * @var string 範本目錄路徑
     */
    private $templatePath;

    /**
     * @var string 匯出目錄路徑
     */
    private $exportPath;

    /**
     * 建構函式
     */
    public function __construct()
    {
        $this->templatePath = WRITEPATH . 'templates/';
        $this->exportPath = WRITEPATH . 'exports/';

        // 確保目錄存在
        if (!is_dir($this->templatePath)) {
            mkdir($this->templatePath, 0755, true);
        }
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath, 0755, true);
        }
    }

    /**
     * 匯出會議通知
     *
     * @param array $meetingData 會議資料
     * @return array ['success' => bool, 'filename' => string, 'filepath' => string, 'error' => string]
     */
    public function exportMeetingNotice(array $meetingData): array
    {
        try {
            // 驗證必要欄位
            $requiredFields = ['urban_renewal_name', 'meeting_name', 'meeting_date'];
            foreach ($requiredFields as $field) {
                if (!isset($meetingData[$field]) || empty($meetingData[$field])) {
                    return [
                        'success' => false,
                        'error' => "缺少必要欄位: {$field}"
                    ];
                }
            }

            // 範本檔案路徑
            $templateFile = $this->templatePath . 'meeting_notice_template.docx';

            if (!file_exists($templateFile)) {
                return [
                    'success' => false,
                    'error' => '範本檔案不存在'
                ];
            }

            // 建立範本處理器
            $templateProcessor = new TemplateProcessor($templateFile);

            // 替換變數
            $this->replaceMeetingNoticeVariables($templateProcessor, $meetingData);

            // 生成檔名：[更新會名稱]_[會議名稱]會議通知_YYYYmmdd.docx
            $date = date('Ymd');
            if (isset($meetingData['meeting_date'])) {
                $date = str_replace('-', '', $meetingData['meeting_date']);
            }

            $filename = $this->sanitizeFilename(
                $meetingData['urban_renewal_name'] . '_' .
                $meetingData['meeting_name'] .
                '會議通知_' . $date . '.docx'
            );

            // 儲存檔案
            $filepath = $this->exportPath . $filename;
            $templateProcessor->saveAs($filepath);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];

        } catch (\Exception $e) {
            log_message('error', 'Word export error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => '匯出失敗: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 替換會議通知範本變數
     *
     * @param TemplateProcessor $templateProcessor
     * @param array $data
     * @return void
     */
    private function replaceMeetingNoticeVariables(TemplateProcessor $templateProcessor, array $data): void
    {
        // 基本資訊
        $templateProcessor->setValue('urban_renewal_name', $data['urban_renewal_name'] ?? '');
        $templateProcessor->setValue('meeting_name', $data['meeting_name'] ?? '');
        $templateProcessor->setValue('meeting_type', $data['meeting_type'] ?? '');

        // 日期時間處理
        if (isset($data['meeting_date'])) {
            $templateProcessor->setValue('meeting_date', $this->formatDate($data['meeting_date']));
        }
        if (isset($data['meeting_time'])) {
            $templateProcessor->setValue('meeting_time', $data['meeting_time']);
        }

        // 地點
        $templateProcessor->setValue('meeting_location', $data['meeting_location'] ?? '');

        // 發文字號
        $templateProcessor->setValue('notice_doc_number', $data['notice_doc_number'] ?? '');
        $templateProcessor->setValue('notice_word_number', $data['notice_word_number'] ?? '');
        $templateProcessor->setValue('notice_mid_number', $data['notice_mid_number'] ?? '');
        $templateProcessor->setValue('notice_end_number', $data['notice_end_number'] ?? '');

        // 聯絡資訊
        $templateProcessor->setValue('chairman_name', $data['chairman_name'] ?? '');
        $templateProcessor->setValue('contact_name', $data['contact_name'] ?? '');
        $templateProcessor->setValue('contact_phone', $data['contact_phone'] ?? '');
        $templateProcessor->setValue('attachments', $data['attachments'] ?? '');

        // 處理發文說明（如果有多筆）
        if (isset($data['descriptions']) && is_array($data['descriptions'])) {
            $descriptionsText = '';
            foreach ($data['descriptions'] as $index => $desc) {
                $chineseNum = $this->getChineseNumber($index + 1);
                $descriptionsText .= $chineseNum . '、' . $desc . "\n";
            }
            $templateProcessor->setValue('descriptions', $descriptionsText);
        } else {
            $templateProcessor->setValue('descriptions', $data['descriptions'] ?? '');
        }

        // 出席者清單
        $templateProcessor->setValue('attendees', $this->getAttendeesList($data));
    }

    /**
     * 取得出席者清單
     *
     * @param array $data 會議資料
     * @return string 出席者名單（頓號分隔）
     */
    private function getAttendeesList(array $data): string
    {
        try {
            // 檢查是否有 urban_renewal_id
            if (!isset($data['urban_renewal_id'])) {
                return '';
            }

            // 載入 PropertyOwnerModel
            $propertyOwnerModel = new \App\Models\PropertyOwnerModel();

            // 查詢該更新會的所有權人
            $owners = $propertyOwnerModel
                ->where('urban_renewal_id', $data['urban_renewal_id'])
                ->where('deleted_at', null)
                ->orderBy('owner_code', 'ASC')
                ->findAll();

            // 提取姓名並用頓號連接
            $names = array_column($owners, 'name');
            return implode('、', $names);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get attendees list: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * 格式化日期
     *
     * @param string $date 日期字串 (YYYY-MM-DD)
     * @return string 格式化後的日期
     */
    private function formatDate(string $date): string
    {
        try {
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                return $date;
            }
            return date('Y年m月d日', $timestamp);
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * 取得中文數字
     *
     * @param int $num
     * @return string
     */
    private function getChineseNumber(int $num): string
    {
        $chineseNumbers = ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
        if ($num <= 10) {
            return $chineseNumbers[$num];
        }
        return (string)$num;
    }

    /**
     * 清理檔名中的非法字元
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // 移除或替換非法字元
        $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
        return $filename;
    }

    /**
     * 刪除匯出檔案
     *
     * @param string $filename
     * @return bool
     */
    public function deleteExportFile(string $filename): bool
    {
        $filepath = $this->exportPath . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * 清理舊的匯出檔案（超過指定天數）
     *
     * @param int $days 保留天數
     * @return int 刪除的檔案數量
     */
    public function cleanOldExports(int $days = 7): int
    {
        $count = 0;
        $cutoffTime = time() - ($days * 24 * 60 * 60);

        $files = glob($this->exportPath . '*.docx');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
