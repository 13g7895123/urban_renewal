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
     * 匯出簽到冊
     *
     * @param array $meetingData 會議資料
     * @param bool $isAnonymous 是否匿名
     * @return array ['success' => bool, 'filename' => string, 'filepath' => string, 'error' => string]
     */
    public function exportSignatureBook(array $meetingData, bool $isAnonymous = false): array
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
            $templateFile = $this->templatePath . 'signature_book_template.docx';

            if (!file_exists($templateFile)) {
                return [
                    'success' => false,
                    'error' => '範本檔案不存在'
                ];
            }

            // 建立範本處理器
            $templateProcessor = new TemplateProcessor($templateFile);

            // 替換基本變數
            $templateProcessor->setValue('urban_renewal_name', $meetingData['urban_renewal_name'] ?? '');
            $templateProcessor->setValue('meeting_name', $meetingData['meeting_name'] ?? '');
            
            if (isset($meetingData['meeting_date'])) {
                $templateProcessor->setValue('meeting_date', $this->formatDate($meetingData['meeting_date']));
            }
            
            $templateProcessor->setValue('meeting_time', $meetingData['meeting_time'] ?? '');
            $templateProcessor->setValue('meeting_location', $meetingData['meeting_location'] ?? '');

            // 處理簽到列表
            $owners = $this->getOwnersList($meetingData);
            
            // 準備表格資料
            $values = [];
            foreach ($owners as $index => $owner) {
                $name = $owner['name'];
                if ($isAnonymous) {
                    $name = $this->maskName($name);
                }
                
                // 處理建物住址/地號
                $addresses = [];
                
                // 1. 建物住址
                if (isset($owner['buildings']) && is_array($owner['buildings'])) {
                    foreach ($owner['buildings'] as $building) {
                        if (!empty($building['building_address'])) {
                            $addresses[] = $building['building_address'];
                        } elseif (!empty($building['location'])) {
                            // 如果沒有完整地址，使用地段+建號
                            $buildingNum = $building['building_number_main'];
                            if (!empty($building['building_number_sub'])) {
                                $buildingNum .= '-' . $building['building_number_sub'];
                            }
                            $addresses[] = $building['location'] . ' ' . $buildingNum . '建號';
                        }
                    }
                }
                
                // 2. 土地地號
                if (isset($owner['lands']) && is_array($owner['lands'])) {
                    foreach ($owner['lands'] as $land) {
                        $landNum = $land['land_number_main'];
                        if (!empty($land['land_number_sub'])) {
                            $landNum .= '-' . $land['land_number_sub'];
                        }
                        
                        // 組合完整地號資訊
                        $location = '';
                        if (isset($land['county']) && isset($land['district']) && isset($land['section'])) {
                            // 這裡簡化處理，理想情況應該查表取得中文名稱
                            // 但因為 PropertyOwnerModel 已經處理了 buildings 的中文名稱，
                            // lands 的部分可能需要類似處理。
                            // 檢查 PropertyOwnerModel::attachRelatedData 發現 lands 沒有轉中文名稱
                            // 暫時直接使用地號，或者嘗試從 buildings 取得地段資訊（如果有的話）
                            // 或者直接顯示地號
                        }
                        
                        $addresses[] = $landNum . '地號';
                    }
                }
                
                $addressStr = implode('/', array_unique($addresses));

                $values[] = [
                    'index' => sprintf('%03d', $index + 1),
                    'owner_name' => $name,
                    'address' => $addressStr,
                    'signature' => '' // 留白供簽名
                ];
            }

            // 如果有資料，進行複製列操作
            // 假設範本中有 ${owner_name} 變數在表格列中
            if (!empty($values)) {
                try {
                    $templateProcessor->cloneRowAndSetValues('owner_name', $values);
                } catch (\Exception $e) {
                    // 如果範本中沒有 owner_name 變數，可能只是簡單的簽到冊
                    log_message('warning', 'Clone row failed (maybe variable not found): ' . $e->getMessage());
                }
            }

            // 生成檔名
            $date = date('Ymd');
            if (isset($meetingData['meeting_date'])) {
                $date = str_replace('-', '', $meetingData['meeting_date']);
            }

            $filename = $this->sanitizeFilename(
                $meetingData['urban_renewal_name'] . '_' .
                $meetingData['meeting_name'] .
                '簽到冊_' . $date . '.docx'
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
            log_message('error', 'Signature book export error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => '匯出失敗: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 遮罩姓名
     *
     * @param string $name
     * @return string
     */
    private function maskName(string $name): string
    {
        $len = mb_strlen($name, 'UTF-8');
        if ($len <= 1) return $name;
        
        // 留下姓氏，名字用OO代替
        return mb_substr($name, 0, 1, 'UTF-8') . 'OO';
    }

    /**
     * 取得所有權人列表
     *
     * @param array $data
     * @return array
     */
    private function getOwnersList(array $data): array
    {
        if (!isset($data['urban_renewal_id'])) {
            return [];
        }

        $propertyOwnerModel = new \App\Models\PropertyOwnerModel();
        return $propertyOwnerModel
            ->where('urban_renewal_id', $data['urban_renewal_id'])
            ->orderBy('owner_code', 'ASC')
            ->findAll();
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
        // 記錄傳入的所有資料鍵值
        log_message('info', '[TemplateVariables] Input data keys: ' . implode(', ', array_keys($data)));
        log_message('debug', '[TemplateVariables] Full input data: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        // 基本資訊
        $urban_renewal_name = $data['urban_renewal_name'] ?? '';
        $meeting_name = $data['meeting_name'] ?? '';
        $meeting_type = $data['meeting_type'] ?? '';

        log_message('info', '[TemplateVariables] Basic info - urban_renewal_name: "' . $urban_renewal_name .
                   '", meeting_name: "' . $meeting_name . '", meeting_type: "' . $meeting_type . '"');

        $templateProcessor->setValue('urban_renewal_name', $urban_renewal_name);
        $templateProcessor->setValue('meeting_name', $meeting_name);
        $templateProcessor->setValue('meeting_type', $meeting_type);

        // 日期時間處理
        if (isset($data['meeting_date'])) {
            $formatted_date = $this->formatDate($data['meeting_date']);
            log_message('info', '[TemplateVariables] meeting_date: "' . $data['meeting_date'] . '" -> formatted: "' . $formatted_date . '"');
            $templateProcessor->setValue('meeting_date', $formatted_date);
        }
        if (isset($data['meeting_time'])) {
            log_message('info', '[TemplateVariables] meeting_time: "' . $data['meeting_time'] . '"');
            $templateProcessor->setValue('meeting_time', $data['meeting_time']);
        }

        // 地點
        $meeting_location = $data['meeting_location'] ?? '';
        log_message('info', '[TemplateVariables] meeting_location: "' . $meeting_location . '"');
        $templateProcessor->setValue('meeting_location', $meeting_location);

        // 發文字號
        $notice_doc_number = $data['notice_doc_number'] ?? '';
        $notice_word_number = $data['notice_word_number'] ?? '';
        $notice_mid_number = $data['notice_mid_number'] ?? '';
        $notice_end_number = $data['notice_end_number'] ?? '';

        log_message('info', '[TemplateVariables] Notice numbers - doc: "' . $notice_doc_number .
                   '", word: "' . $notice_word_number . '", mid: "' . $notice_mid_number .
                   '", end: "' . $notice_end_number . '"');

        $templateProcessor->setValue('notice_doc_number', $notice_doc_number);
        $templateProcessor->setValue('notice_word_number', $notice_word_number);
        $templateProcessor->setValue('notice_mid_number', $notice_mid_number);
        $templateProcessor->setValue('notice_end_number', $notice_end_number);

        // 聯絡資訊
        $chairman_name = $data['chairman_name'] ?? '';
        $contact_name = $data['contact_name'] ?? '';
        $contact_phone = $data['contact_phone'] ?? '';
        $attachments = $data['attachments'] ?? '';

        log_message('info', '[TemplateVariables] Contact info - chairman: "' . $chairman_name .
                   '", contact_name: "' . $contact_name . '", phone: "' . $contact_phone . '"');

        $templateProcessor->setValue('chairman_name', $chairman_name);
        $templateProcessor->setValue('contact_name', $contact_name);
        $templateProcessor->setValue('contact_phone', $contact_phone);
        $templateProcessor->setValue('attachments', $attachments);

        // 處理發文說明（如果有多筆）
        if (isset($data['descriptions']) && is_array($data['descriptions'])) {
            log_message('info', '[TemplateVariables] descriptions is array with ' . count($data['descriptions']) . ' items');
            $descriptionsText = '';
            foreach ($data['descriptions'] as $index => $desc) {
                $chineseNum = $this->getChineseNumber($index + 1);
                $descriptionsText .= $chineseNum . '、' . $desc . "\n";
            }
            log_message('info', '[TemplateVariables] descriptions text: "' . trim($descriptionsText) . '"');
            $templateProcessor->setValue('descriptions', $descriptionsText);
        } else {
            $descriptions = $data['descriptions'] ?? '';
            log_message('info', '[TemplateVariables] descriptions (string): "' . $descriptions . '"');
            $templateProcessor->setValue('descriptions', $descriptions);
        }

        // 出席者清單
        log_message('info', '[TemplateVariables] Processing attendees list...');
        $attendees = $this->getAttendeesList($data);
        log_message('info', '[TemplateVariables] Final attendees list: "' . $attendees . '"');
        $templateProcessor->setValue('attendees', $attendees);
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
                log_message('warning', '[GetAttendeesList] urban_renewal_id not found in data');
                log_message('debug', '[GetAttendeesList] Available keys: ' . implode(', ', array_keys($data)));
                return '';
            }

            $urban_renewal_id = $data['urban_renewal_id'];
            log_message('info', '[GetAttendeesList] Looking for owners with urban_renewal_id: ' . $urban_renewal_id);

            // 載入 PropertyOwnerModel
            $propertyOwnerModel = new \App\Models\PropertyOwnerModel();

            // 查詢該更新會的所有權人
            $owners = $propertyOwnerModel
                ->where('urban_renewal_id', $urban_renewal_id)
                ->orderBy('owner_code', 'ASC')
                ->findAll();

            log_message('info', '[GetAttendeesList] Found ' . count($owners) . ' owners');

            if (empty($owners)) {
                log_message('warning', '[GetAttendeesList] No owners found for urban_renewal_id: ' . $urban_renewal_id);
                return '';
            }

            // 提取姓名並用頓號連接
            $names = array_column($owners, 'name');
            log_message('debug', '[GetAttendeesList] Owner names: ' . json_encode($names, JSON_UNESCAPED_UNICODE));

            $attendeesList = implode('、', $names);
            log_message('info', '[GetAttendeesList] Final attendees list: "' . $attendeesList . '"');

            return $attendeesList;

        } catch (\Exception $e) {
            log_message('error', '[GetAttendeesList] Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            log_message('debug', '[GetAttendeesList] Stack trace: ' . $e->getTraceAsString());
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
