<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MeetingAttendanceModel;
use App\Models\MeetingModel;
use App\Models\PropertyOwnerModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\HasRbacPermissions;

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

            if (!$filePath) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INTERNAL_ERROR',
                        'message' => '檔案產生失敗'
                    ]
                ], 500);
            }

            return $this->respond([
                'success' => true,
                'data' => [
                    'download_url' => base_url('api/downloads/' . basename($filePath)),
                    'filename' => basename($filePath),
                    'format' => $format
                ],
                'message' => '匯出檔案產生成功'
            ]);

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
        // TODO: 實作 Excel 匯出功能
        // 這裡需要整合 PhpSpreadsheet 或類似的套件
        return false;
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