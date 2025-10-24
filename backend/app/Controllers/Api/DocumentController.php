<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Traits\HasRbacPermissions;

class DocumentController extends ResourceController
{
    use HasRbacPermissions;

    protected $modelName = 'App\Models\MeetingDocumentModel';
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
     * 取得文件列表
     */
    public function index()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $meetingId = $this->request->getGet('meeting_id');
            $type = $this->request->getGet('type');
            $keyword = $this->request->getGet('search');

            if ($keyword) {
                $filters = [
                    'meeting_id' => $meetingId,
                    'document_type' => $type
                ];

                // 如果不是管理員，限制只能查看自己更新會的文件
                if ($user['role'] !== 'admin') {
                    $filters['urban_renewal_id'] = $user['urban_renewal_id'];
                }

                $documents = $this->model->searchDocuments($keyword, $page, $perPage, $filters);
            } elseif ($meetingId) {
                // 檢查會議權限
                $meetingModel = model('MeetingModel');
                $meeting = $meetingModel->find($meetingId);
                if (!$meeting) {
                    return response_error('找不到該會議', 404);
                }

                if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                    return $this->failForbidden('無權限查看此會議文件');
                }

                $documents = $this->model->getDocumentsByMeeting($meetingId, $page, $perPage, $type);
            } else {
                // 取得所有文件（根據權限過濾）
                $builder = $this->model;
                if ($user['role'] !== 'admin') {
                    $builder = $builder->join('meetings', 'meetings.id = meeting_documents.meeting_id', 'inner')
                                     ->where('meetings.urban_renewal_id', $user['urban_renewal_id']);
                }
                $documents = $builder->paginate($perPage, 'default', $page);
            }

            // 格式化檔案大小
            foreach ($documents as &$document) {
                $document['formatted_size'] = $this->model->formatFileSize($document['file_size']);
                $document['type_name'] = $this->model->getDocumentTypeName($document['document_type']);
            }

            return response_success('文件列表', [
                'documents' => $documents,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得文件列表失敗: ' . $e->getMessage());
            return response_error('取得文件列表失敗', 500);
        }
    }

    /**
     * 取得文件詳情
     */
    public function show($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('文件ID為必填', 400);
            }

            $document = $this->model->getDocumentWithDetails($id);
            if (!$document) {
                return response_error('找不到該文件', 404);
            }

            // 檢查存取權限
            $hasAccess = $this->model->checkDocumentAccess($id, $user['id'], $user['role'], $user['urban_renewal_id']);
            if (!$hasAccess) {
                return $this->failForbidden('無權限查看此文件');
            }

            $document['formatted_size'] = $this->model->formatFileSize($document['file_size']);
            $document['type_name'] = $this->model->getDocumentTypeName($document['document_type']);

            return response_success('文件詳情', $document);

        } catch (\Exception $e) {
            log_message('error', '取得文件詳情失敗: ' . $e->getMessage());
            return response_error('取得文件詳情失敗', 500);
        }
    }

    /**
     * 上傳文件
     */
    public function upload()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman', 'member']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $meetingId = $this->request->getPost('meeting_id');
            $documentType = $this->request->getPost('document_type');
            $documentName = $this->request->getPost('document_name');

            // 驗證必填欄位
            if (!$meetingId || !$documentType || !$documentName) {
                return response_error('會議ID、文件類型和文件名稱為必填', 400);
            }

            // 檢查會議權限
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($meetingId);
            if (!$meeting) {
                return response_error('找不到該會議', 404);
            }

            if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限上傳此會議文件');
            }

            // 檢查檔案
            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                return response_error('請選擇有效的檔案', 400);
            }

            $data = [
                'meeting_id' => $meetingId,
                'document_type' => $documentType,
                'document_name' => $documentName,
                'uploaded_by' => $user['id']
            ];

            $documentId = $this->model->uploadDocument($data, $file);
            if (!$documentId) {
                return response_error('檔案上傳失敗', 500, $this->model->errors());
            }

            $document = $this->model->find($documentId);
            $document['formatted_size'] = $this->model->formatFileSize($document['file_size']);

            return response_success('檔案上傳成功', $document, 201);

        } catch (\Exception $e) {
            log_message('error', '上傳文件失敗: ' . $e->getMessage());
            return response_error('上傳文件失敗', 500);
        }
    }

    /**
     * 下載文件
     */
    public function download($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('文件ID為必填', 400);
            }

            // 檢查存取權限
            $hasAccess = $this->model->checkDocumentAccess($id, $user['id'], $user['role'], $user['urban_renewal_id']);
            if (!$hasAccess) {
                return $this->failForbidden('無權限下載此文件');
            }

            $downloadInfo = $this->model->downloadDocument($id);
            if (!$downloadInfo) {
                return response_error('檔案不存在或已損毀', 404);
            }

            return $this->response->download($downloadInfo['file_path'], null, true)
                                  ->setFileName($downloadInfo['file_name']);

        } catch (\Exception $e) {
            log_message('error', '下載文件失敗: ' . $e->getMessage());
            return response_error('下載文件失敗', 500);
        }
    }

    /**
     * 更新文件資訊
     */
    public function update($id = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('文件ID為必填', 400);
            }

            $document = $this->model->find($id);
            if (!$document) {
                return response_error('找不到該文件', 404);
            }

            // 檢查權限
            $hasAccess = $this->model->checkDocumentAccess($id, $user['id'], $user['role'], $user['urban_renewal_id']);
            if (!$hasAccess) {
                return $this->failForbidden('無權限修改此文件');
            }

            $data = $this->request->getJSON(true);

            // 只允許修改特定欄位
            $allowedFields = ['document_name', 'document_type'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            if (empty($updateData)) {
                return response_error('沒有可更新的資料', 400);
            }

            $success = $this->model->update($id, $updateData);
            if (!$success) {
                return response_error('更新文件失敗', 500, $this->model->errors());
            }

            $updatedDocument = $this->model->getDocumentWithDetails($id);
            $updatedDocument['formatted_size'] = $this->model->formatFileSize($updatedDocument['file_size']);

            return response_success('文件更新成功', $updatedDocument);

        } catch (\Exception $e) {
            log_message('error', '更新文件失敗: ' . $e->getMessage());
            return response_error('更新文件失敗', 500);
        }
    }

    /**
     * 刪除文件
     */
    public function delete($id = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('文件ID為必填', 400);
            }

            $document = $this->model->find($id);
            if (!$document) {
                return response_error('找不到該文件', 404);
            }

            // 檢查權限
            $hasAccess = $this->model->checkDocumentAccess($id, $user['id'], $user['role'], $user['urban_renewal_id']);
            if (!$hasAccess) {
                return $this->failForbidden('無權限刪除此文件');
            }

            $success = $this->model->deleteDocument($id);
            if (!$success) {
                return response_error('刪除文件失敗', 500);
            }

            return response_success('文件刪除成功');

        } catch (\Exception $e) {
            log_message('error', '刪除文件失敗: ' . $e->getMessage());
            return response_error('刪除文件失敗', 500);
        }
    }

    /**
     * 取得文件統計
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

            // 檢查會議權限（如果指定了會議ID）
            if ($meetingId) {
                $meetingModel = model('MeetingModel');
                $meeting = $meetingModel->find($meetingId);
                if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                    return $this->failForbidden('無權限查看此會議統計');
                }
            }

            $statistics = $this->model->getDocumentStatistics($meetingId);

            // 格式化統計資料
            foreach ($statistics as &$stat) {
                $stat['formatted_size'] = $this->model->formatFileSize($stat['total_size']);
                $stat['type_name'] = $this->model->getDocumentTypeName($stat['document_type']);
            }

            return response_success('文件統計', $statistics);

        } catch (\Exception $e) {
            log_message('error', '取得文件統計失敗: ' . $e->getMessage());
            return response_error('取得文件統計失敗', 500);
        }
    }

    /**
     * 取得最近上傳的文件
     */
    public function recent()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $limit = $this->request->getGet('limit') ?? 10;
            $urbanRenewalId = $user['role'] === 'admin' ? null : $user['urban_renewal_id'];

            $documents = $this->model->getRecentDocuments($limit, $urbanRenewalId);

            // 格式化資料
            foreach ($documents as &$document) {
                $document['formatted_size'] = $this->model->formatFileSize($document['file_size']);
                $document['type_name'] = $this->model->getDocumentTypeName($document['document_type']);
            }

            return response_success('最近上傳的文件', $documents);

        } catch (\Exception $e) {
            log_message('error', '取得最近文件失敗: ' . $e->getMessage());
            return response_error('取得最近文件失敗', 500);
        }
    }

    /**
     * 取得文件類型列表
     */
    public function types()
    {
        try {
            $types = [
                'agenda' => '議程',
                'minutes' => '會議紀錄',
                'attendance' => '出席名單',
                'notice' => '通知',
                'other' => '其他'
            ];

            return response_success('文件類型列表', $types);

        } catch (\Exception $e) {
            log_message('error', '取得文件類型失敗: ' . $e->getMessage());
            return response_error('取得文件類型失敗', 500);
        }
    }

    /**
     * 取得儲存空間使用情況
     */
    public function storageUsage()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $urbanRenewalId = $user['role'] === 'admin' ? null : $user['urban_renewal_id'];
            $usage = $this->model->getStorageUsage($urbanRenewalId);

            return response_success('儲存空間使用情況', $usage);

        } catch (\Exception $e) {
            log_message('error', '取得儲存空間使用情況失敗: ' . $e->getMessage());
            return response_error('取得儲存空間使用情況失敗', 500);
        }
    }

    /**
     * 清理孤立檔案（管理員功能）
     */
    public function cleanOrphanFiles()
    {
        try {
            // 驗證用戶權限（只有管理員可以執行）
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $cleanedCount = $this->model->cleanOrphanFiles();

            return response_success('孤立檔案清理完成', [
                'cleaned_count' => $cleanedCount
            ]);

        } catch (\Exception $e) {
            log_message('error', '清理孤立檔案失敗: ' . $e->getMessage());
            return response_error('清理孤立檔案失敗', 500);
        }
    }

    /**
     * 批量上傳文件
     */
    public function batchUpload()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $meetingId = $this->request->getPost('meeting_id');
            $documentType = $this->request->getPost('document_type');

            if (!$meetingId || !$documentType) {
                return response_error('會議ID和文件類型為必填', 400);
            }

            // 檢查會議權限
            $meetingModel = model('MeetingModel');
            $meeting = $meetingModel->find($meetingId);
            if (!$meeting) {
                return response_error('找不到該會議', 404);
            }

            if ($user['role'] !== 'admin' && $user['urban_renewal_id'] !== $meeting['urban_renewal_id']) {
                return $this->failForbidden('無權限上傳此會議文件');
            }

            $files = $this->request->getFiles();
            $results = [];
            $successCount = 0;

            foreach ($files['files'] as $file) {
                if ($file->isValid()) {
                    $data = [
                        'meeting_id' => $meetingId,
                        'document_type' => $documentType,
                        'document_name' => pathinfo($file->getName(), PATHINFO_FILENAME),
                        'uploaded_by' => $user['id']
                    ];

                    $documentId = $this->model->uploadDocument($data, $file);
                    if ($documentId) {
                        $successCount++;
                        $results[] = [
                            'file_name' => $file->getName(),
                            'success' => true,
                            'document_id' => $documentId
                        ];
                    } else {
                        $results[] = [
                            'file_name' => $file->getName(),
                            'success' => false,
                            'error' => '上傳失敗'
                        ];
                    }
                }
            }

            return response_success('批量上傳完成', [
                'total' => count($files['files']),
                'success' => $successCount,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            log_message('error', '批量上傳文件失敗: ' . $e->getMessage());
            return response_error('批量上傳文件失敗', 500);
        }
    }
}