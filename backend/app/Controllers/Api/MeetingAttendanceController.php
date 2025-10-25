<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MeetingAttendanceModel;
use App\Models\MeetingModel;
use App\Models\PropertyOwnerModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\HasRbacPermissions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MeetingAttendanceController extends BaseController
{
    use HasRbacPermissions;

    protected $attendanceModel;
    protected $meetingModel;
    protected $propertyOwnerModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->attendanceModel = new MeetingAttendanceModel();
        $this->meetingModel = new MeetingModel();
        $this->propertyOwnerModel = new PropertyOwnerModel();

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
     * 取得會議出席記錄
     * GET /api/meetings/{id}/attendances
     */
    public function index($meetingId = null)
    {
        try {
            // 檢查會議是否存在
            $meeting = $this->meetingModel->find($meetingId);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 50; // 報到系統通常需要較多資料
            $attendanceType = $this->request->getGet('attendance_type');
            $search = $this->request->getGet('search');

            $filters = [];
            if ($attendanceType) $filters['attendance_type'] = $attendanceType;
            if ($search) $filters['search'] = $search;

            $data = $this->attendanceModel->getMeetingAttendances($meetingId, $page, $perPage, $filters);
            $pager = $this->attendanceModel->pager;

            return $this->respond([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $pager->getCurrentPage(),
                    'per_page' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'total_pages' => $pager->getPageCount()
                ],
                'message' => '取得出席記錄成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get meeting attendances error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得出席記錄失敗'
                ]
            ], 500);
        }
    }

    /**
     * 會員報到
     * POST /api/meetings/{meetingId}/attendances/{ownerId}
     */
    public function checkIn($meetingId = null, $ownerId = null)
    {
        try {
            // 檢查會議是否存在
            $meeting = $this->meetingModel->find($meetingId);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            // 檢查所有權人是否存在且屬於該更新會
            $propertyOwner = $this->propertyOwnerModel->where('id', $ownerId)
                                                     ->where('urban_renewal_id', $meeting['urban_renewal_id'])
                                                     ->first();

            if (!$propertyOwner) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '所有權人不存在或不屬於該更新會'
                    ]
                ], 404);
            }

            $rules = [
                'attendance_type' => 'required|in_list[present,proxy,absent]',
                'proxy_person' => 'permit_empty|max_length[100]',
                'notes' => 'permit_empty|max_length[500]'
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
            $attendanceType = $data['attendance_type'];

            // 檢查委託出席是否有代理人姓名
            if ($attendanceType === 'proxy' && empty($data['proxy_person'])) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '委託出席需要填寫代理人姓名'
                    ]
                ], 422);
            }

            // 檢查是否已經報到
            $existingAttendance = $this->attendanceModel->where('meeting_id', $meetingId)
                                                       ->where('property_owner_id', $ownerId)
                                                       ->first();

            $attendanceData = [
                'meeting_id' => $meetingId,
                'property_owner_id' => $ownerId,
                'attendance_type' => $attendanceType,
                'proxy_person' => $data['proxy_person'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_calculated' => $propertyOwner['exclusion_type'] ? 0 : 1 // 有排除計算類型的不納入計算
            ];

            if ($attendanceType === 'present' || $attendanceType === 'proxy') {
                $attendanceData['check_in_time'] = date('Y-m-d H:i:s');
            }

            if ($existingAttendance) {
                // 更新現有記錄
                $success = $this->attendanceModel->update($existingAttendance['id'], $attendanceData);
                $attendanceId = $existingAttendance['id'];
            } else {
                // 建立新記錄
                $attendanceId = $this->attendanceModel->insert($attendanceData);
                $success = $attendanceId !== false;
            }

            if (!$success) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '報到處理失敗'
                    ]
                ], 500);
            }

            // 更新會議出席統計
            $this->meetingModel->updateAttendanceStatistics($meetingId);

            // 取得更新後的出席記錄
            $attendance = $this->attendanceModel->getAttendanceWithOwnerInfo($attendanceId);

            return $this->respond([
                'success' => true,
                'data' => $attendance,
                'message' => '報到成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Check in error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '報到處理失敗'
                ]
            ], 500);
        }
    }

    /**
     * 更新出席狀態
     * PUT /api/meetings/{meetingId}/attendances/{ownerId}
     */
    public function update($meetingId = null, $ownerId = null)
    {
        try {
            // 檢查出席記錄是否存在
            $attendance = $this->attendanceModel->where('meeting_id', $meetingId)
                                                ->where('property_owner_id', $ownerId)
                                                ->first();

            if (!$attendance) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '出席記錄不存在'
                    ]
                ], 404);
            }

            $rules = [
                'attendance_type' => 'permit_empty|in_list[present,proxy,absent]',
                'proxy_person' => 'permit_empty|max_length[100]',
                'notes' => 'permit_empty|max_length[500]',
                'is_calculated' => 'permit_empty|in_list[0,1]'
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

            // 處理出席時間
            if (isset($data['attendance_type'])) {
                if ($data['attendance_type'] === 'present' || $data['attendance_type'] === 'proxy') {
                    if (!$attendance['check_in_time']) {
                        $data['check_in_time'] = date('Y-m-d H:i:s');
                    }
                } else {
                    $data['check_in_time'] = null;
                }
            }

            // 檢查委託出席是否有代理人姓名
            if (isset($data['attendance_type']) && $data['attendance_type'] === 'proxy' && empty($data['proxy_person'])) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '委託出席需要填寫代理人姓名'
                    ]
                ], 422);
            }

            $success = $this->attendanceModel->update($attendance['id'], $data);

            if (!$success) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '出席狀態更新失敗'
                    ]
                ], 500);
            }

            // 更新會議出席統計
            $this->meetingModel->updateAttendanceStatistics($meetingId);

            // 取得更新後的出席記錄
            $updatedAttendance = $this->attendanceModel->getAttendanceWithOwnerInfo($attendance['id']);

            return $this->respond([
                'success' => true,
                'data' => $updatedAttendance,
                'message' => '出席狀態更新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Update attendance error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '出席狀態更新失敗'
                ]
            ], 500);
        }
    }

    /**
     * 取得出席統計
     * GET /api/meetings/{id}/attendances/statistics
     */
    public function statistics($meetingId = null)
    {
        try {
            // 檢查會議是否存在
            $meeting = $this->meetingModel->find($meetingId);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            $statistics = $this->attendanceModel->getDetailedAttendanceStatistics($meetingId);

            return $this->respond([
                'success' => true,
                'data' => $statistics,
                'message' => '取得出席統計成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get attendance statistics error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得出席統計失敗'
                ]
            ], 500);
        }
    }

    /**
     * 匯出報到結果
     * POST /api/meetings/{id}/attendances/export
     */
    public function export($meetingId = null)
    {
        try {
            // 檢查會議是否存在
            $meeting = $this->meetingModel->getMeetingWithDetails($meetingId);
            if (!$meeting) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => '會議不存在'
                    ]
                ], 404);
            }

            $format = $this->request->getJSON()->format ?? 'excel';

            // 取得完整出席資料
            $attendances = $this->attendanceModel->getMeetingAttendances($meetingId, 1, 1000); // 匯出不分頁
            $statistics = $this->attendanceModel->getDetailedAttendanceStatistics($meetingId);

            $exportData = [
                'meeting' => $meeting,
                'attendances' => $attendances,
                'statistics' => $statistics,
                'export_time' => date('Y-m-d H:i:s'),
                'format' => $format
            ];

            switch ($format) {
                case 'excel':
                    $filePath = $this->generateExcelReport($exportData);
                    break;
                case 'pdf':
                    $filePath = $this->generatePdfReport($exportData);
                    break;
                case 'json':
                    return $this->respond([
                        'success' => true,
                        'data' => $exportData,
                        'message' => '匯出資料成功'
                    ]);
                default:
                    return $this->fail([
                        'success' => false,
                        'error' => [
                            'code' => 'VALIDATION_ERROR',
                            'message' => '不支援的匯出格式'
                        ]
                    ], 422);
            }

            if (!$filePath || !file_exists($filePath)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '檔案產生失敗'
                    ]
                ], 500);
            }

            // 直接回傳檔案供下載
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->setHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
                ->setHeader('Cache-Control', 'max-age=0')
                ->setBody(file_get_contents($filePath))
                ->send();

        } catch (\Exception $e) {
            log_message('error', 'Export attendance error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '匯出處理失敗'
                ]
            ], 500);
        }
    }

    /**
     * 批次報到
     * POST /api/meetings/{meetingId}/attendances/batch
     */
    public function batchCheckIn($meetingId = null)
    {
        try {
            // 檢查會議是否存在
            $meeting = $this->meetingModel->find($meetingId);
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
                'attendances' => 'required|is_array',
                'attendances.*.property_owner_id' => 'required|integer',
                'attendances.*.attendance_type' => 'required|in_list[present,proxy,absent]',
                'attendances.*.proxy_person' => 'permit_empty|max_length[100]'
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
            $attendances = $data['attendances'];

            $successCount = 0;
            $errors = [];

            foreach ($attendances as $index => $attendanceData) {
                try {
                    // 檢查所有權人是否屬於該更新會
                    $propertyOwner = $this->propertyOwnerModel->where('id', $attendanceData['property_owner_id'])
                                                             ->where('urban_renewal_id', $meeting['urban_renewal_id'])
                                                             ->first();

                    if (!$propertyOwner) {
                        $errors[] = [
                            'index' => $index,
                            'property_owner_id' => $attendanceData['property_owner_id'],
                            'error' => '所有權人不存在或不屬於該更新會'
                        ];
                        continue;
                    }

                    $processData = [
                        'meeting_id' => $meetingId,
                        'property_owner_id' => $attendanceData['property_owner_id'],
                        'attendance_type' => $attendanceData['attendance_type'],
                        'proxy_person' => $attendanceData['proxy_person'] ?? null,
                        'notes' => $attendanceData['notes'] ?? null,
                        'is_calculated' => $propertyOwner['exclusion_type'] ? 0 : 1
                    ];

                    if ($attendanceData['attendance_type'] === 'present' || $attendanceData['attendance_type'] === 'proxy') {
                        $processData['check_in_time'] = date('Y-m-d H:i:s');
                    }

                    // 檢查是否已存在記錄
                    $existingAttendance = $this->attendanceModel->where('meeting_id', $meetingId)
                                                               ->where('property_owner_id', $attendanceData['property_owner_id'])
                                                               ->first();

                    if ($existingAttendance) {
                        $this->attendanceModel->update($existingAttendance['id'], $processData);
                    } else {
                        $this->attendanceModel->insert($processData);
                    }

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'property_owner_id' => $attendanceData['property_owner_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            // 更新會議出席統計
            $this->meetingModel->updateAttendanceStatistics($meetingId);

            return $this->respond([
                'success' => true,
                'data' => [
                    'total_processed' => count($attendances),
                    'success_count' => $successCount,
                    'error_count' => count($errors),
                    'errors' => $errors
                ],
                'message' => "批次報到完成，成功 {$successCount} 筆"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Batch check in error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '批次報到處理失敗'
                ]
            ], 500);
        }
    }

    /**
     * 產生 Excel 報表
     */
    private function generateExcelReport($data)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // 設定文件屬性
            $spreadsheet->getProperties()
                ->setCreator('都更計票系統')
                ->setTitle('會議簽到結果')
                ->setSubject('會議簽到結果匯出')
                ->setDescription('會議簽到結果匯出報表');
            
            $meeting = $data['meeting'];
            $attendances = $data['attendances'];
            $statistics = $data['statistics'];
            
            // 標題行
            $sheet->setCellValue('A1', '會議簽到結果');
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // 會議資訊
            $row = 3;
            $sheet->setCellValue('A' . $row, '會議名稱：');
            $sheet->setCellValue('B' . $row, $meeting['name'] ?? '');
            $sheet->mergeCells('B' . $row . ':D' . $row);
            $row++;
            
            $sheet->setCellValue('A' . $row, '更新會：');
            $sheet->setCellValue('B' . $row, $meeting['urban_renewal_name'] ?? '');
            $sheet->mergeCells('B' . $row . ':D' . $row);
            $row++;
            
            $sheet->setCellValue('A' . $row, '會議時間：');
            $meetingDateTime = ($meeting['meeting_date'] ?? '') . ' ' . ($meeting['meeting_time'] ?? '');
            $sheet->setCellValue('B' . $row, trim($meetingDateTime));
            $sheet->mergeCells('B' . $row . ':D' . $row);
            $row++;
            
            $sheet->setCellValue('A' . $row, '匯出時間：');
            $sheet->setCellValue('B' . $row, $data['export_time']);
            $sheet->mergeCells('B' . $row . ':D' . $row);
            $row += 2;
            
            // 統計資訊
            if ($statistics) {
                $sheet->setCellValue('A' . $row, '出席統計');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
                $row++;
                
                $sheet->setCellValue('A' . $row, '親自出席：');
                $sheet->setCellValue('B' . $row, $statistics['present_count'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, '委託出席：');
                $sheet->setCellValue('B' . $row, $statistics['proxy_count'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, '未出席：');
                $sheet->setCellValue('B' . $row, $statistics['absent_count'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, '總人數：');
                $sheet->setCellValue('B' . $row, $statistics['total_count'] ?? 0);
                $row += 2;
            }
            
            // 簽到明細表頭
            $sheet->setCellValue('A' . $row, '簽到明細');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $row++;
            
            $headerRow = $row;
            $headers = ['編號', '所有權人姓名', '出席狀態', '委託代理人', '報到時間'];
            $columns = ['A', 'B', 'C', 'D', 'E'];
            
            foreach ($columns as $index => $col) {
                $sheet->setCellValue($col . $headerRow, $headers[$index]);
                $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
                $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($col . $headerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }
            
            // 設定邊框
            $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            $row++;
            
            // 簽到資料
            $dataStartRow = $row;
            foreach ($attendances as $index => $attendance) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $attendance['owner_name'] ?? '');
                
                // 出席狀態
                $statusText = '';
                switch ($attendance['attendance_type'] ?? '') {
                    case 'present':
                        $statusText = '親自出席';
                        break;
                    case 'proxy':
                        $statusText = '委託出席';
                        break;
                    case 'absent':
                        $statusText = '未出席';
                        break;
                    default:
                        $statusText = '未報到';
                }
                $sheet->setCellValue('C' . $row, $statusText);
                
                $sheet->setCellValue('D' . $row, $attendance['proxy_person'] ?? '');
                $sheet->setCellValue('E' . $row, $attendance['check_in_time'] ?? '');
                
                // 設定對齊
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $row++;
            }
            
            // 設定資料區域邊框
            if ($row > $dataStartRow) {
                $sheet->getStyle('A' . $dataStartRow . ':E' . ($row - 1))->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }
            
            // 自動調整欄寬
            foreach ($columns as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // 確保目錄存在
            $exportDir = WRITEPATH . 'exports';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }
            
            // 產生檔案名稱（避免亂碼，使用英文和日期）
            $filename = 'attendance_' . ($meeting['id'] ?? 'unknown') . '_' . date('YmdHis') . '.xlsx';
            $filePath = $exportDir . '/' . $filename;
            
            // 寫入檔案
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            // 清理記憶體
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            return $filePath;
            
        } catch (\Exception $e) {
            log_message('error', 'Generate Excel report error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 產生 PDF 報表
     */
    private function generatePdfReport($data)
    {
        // TODO: 實作 PDF 匯出功能
        // 這裡需要整合 TCPDF 或類似的套件
        return false;
    }
}