<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingDocumentModel extends Model
{
    protected $table = 'meeting_documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'meeting_id',
        'document_type',
        'document_name',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'meeting_id' => 'required|integer',
        'document_type' => 'required|in_list[agenda,minutes,attendance,notice,other]',
        'document_name' => 'required|max_length[255]',
        'file_path' => 'required',
        'file_name' => 'required|max_length[255]',
        'file_size' => 'required|integer',
        'mime_type' => 'permit_empty|max_length[100]'
    ];

    protected $validationMessages = [
        'meeting_id' => [
            'required' => '會議ID為必填',
            'integer' => '會議ID必須為數字'
        ],
        'document_type' => [
            'required' => '文件類型為必填',
            'in_list' => '文件類型必須為：agenda, minutes, attendance, notice, other'
        ],
        'document_name' => [
            'required' => '文件名稱為必填',
            'max_length' => '文件名稱不可超過255字元'
        ],
        'file_path' => [
            'required' => '檔案路徑為必填'
        ],
        'file_name' => [
            'required' => '檔案名稱為必填',
            'max_length' => '檔案名稱不可超過255字元'
        ],
        'file_size' => [
            'required' => '檔案大小為必填',
            'integer' => '檔案大小必須為數字'
        ],
        'mime_type' => [
            'max_length' => 'MIME類型不可超過100字元'
        ]
    ];

    /**
     * 取得會議文件列表
     */
    public function getDocumentsByMeeting($meetingId, $page = 1, $perPage = 10, $type = null)
    {
        $builder = $this->select('meeting_documents.*, users.full_name as uploader_name')
                       ->join('users', 'users.id = meeting_documents.uploaded_by', 'left')
                       ->where('meeting_documents.meeting_id', $meetingId)
                       ->where('meeting_documents.deleted_at', null);

        if ($type) {
            $builder->where('meeting_documents.document_type', $type);
        }

        return $builder->orderBy('meeting_documents.document_type', 'ASC')
                       ->orderBy('meeting_documents.created_at', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得文件詳情
     */
    public function getDocumentWithDetails($documentId)
    {
        return $this->select('meeting_documents.*,
                            meetings.meeting_name, meetings.meeting_date,
                            users.full_name as uploader_name')
                   ->join('meetings', 'meetings.id = meeting_documents.meeting_id', 'left')
                   ->join('users', 'users.id = meeting_documents.uploaded_by', 'left')
                   ->where('meeting_documents.id', $documentId)
                   ->where('meeting_documents.deleted_at', null)
                   ->first();
    }

    /**
     * 上傳文件
     */
    public function uploadDocument($data, $file)
    {
        // 驗證檔案
        if (!$file->isValid()) {
            return false;
        }

        // 檢查檔案類型
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];

        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return false;
        }

        // 檢查檔案大小（最大20MB）
        if ($file->getSize() > 20 * 1024 * 1024) {
            return false;
        }

        // 產生檔案名稱
        $fileName = $this->generateFileName($file);
        $uploadPath = WRITEPATH . 'uploads/documents/' . date('Y/m/');

        // 建立資料夾
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // 移動檔案
        if (!$file->move($uploadPath, $fileName)) {
            return false;
        }

        // 儲存資料
        $documentData = array_merge($data, [
            'file_path' => $uploadPath . $fileName,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);

        return $this->insert($documentData);
    }

    /**
     * 下載文件
     */
    public function downloadDocument($documentId)
    {
        $document = $this->find($documentId);
        if (!$document || !file_exists($document['file_path'])) {
            return false;
        }

        return [
            'file_path' => $document['file_path'],
            'file_name' => $document['document_name'] . '.' . pathinfo($document['file_name'], PATHINFO_EXTENSION),
            'mime_type' => $document['mime_type']
        ];
    }

    /**
     * 刪除文件
     */
    public function deleteDocument($documentId)
    {
        $document = $this->find($documentId);
        if (!$document) {
            return false;
        }

        // 刪除實體檔案
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }

        // 軟刪除資料庫記錄
        return $this->delete($documentId);
    }

    /**
     * 取得文件統計
     */
    public function getDocumentStatistics($meetingId = null)
    {
        $builder = $this->select('document_type, COUNT(*) as count, SUM(file_size) as total_size')
                       ->where('deleted_at', null);

        if ($meetingId) {
            $builder->where('meeting_id', $meetingId);
        }

        return $builder->groupBy('document_type')->findAll();
    }

    /**
     * 搜尋文件
     */
    public function searchDocuments($keyword, $page = 1, $perPage = 10, $filters = [])
    {
        $builder = $this->select('meeting_documents.*,
                                meetings.meeting_name, meetings.meeting_date,
                                users.full_name as uploader_name')
                       ->join('meetings', 'meetings.id = meeting_documents.meeting_id', 'left')
                       ->join('users', 'users.id = meeting_documents.uploaded_by', 'left')
                       ->where('meeting_documents.deleted_at', null);

        if (!empty($keyword)) {
            $builder->groupStart()
                   ->like('meeting_documents.document_name', $keyword)
                   ->orLike('meeting_documents.file_name', $keyword)
                   ->orLike('meetings.meeting_name', $keyword)
                   ->groupEnd();
        }

        // 篩選條件
        if (!empty($filters['meeting_id'])) {
            $builder->where('meeting_documents.meeting_id', $filters['meeting_id']);
        }

        if (!empty($filters['document_type'])) {
            $builder->where('meeting_documents.document_type', $filters['document_type']);
        }

        if (!empty($filters['uploaded_by'])) {
            $builder->where('meeting_documents.uploaded_by', $filters['uploaded_by']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('meeting_documents.created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('meeting_documents.created_at <=', $filters['date_to']);
        }

        return $builder->orderBy('meeting_documents.created_at', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得最近上傳的文件
     */
    public function getRecentDocuments($limit = 10, $urbanRenewalId = null)
    {
        $builder = $this->select('meeting_documents.*,
                                meetings.meeting_name, meetings.meeting_date,
                                users.full_name as uploader_name')
                       ->join('meetings', 'meetings.id = meeting_documents.meeting_id', 'left')
                       ->join('users', 'users.id = meeting_documents.uploaded_by', 'left')
                       ->where('meeting_documents.deleted_at', null);

        if ($urbanRenewalId) {
            $builder->where('meetings.urban_renewal_id', $urbanRenewalId);
        }

        return $builder->orderBy('meeting_documents.created_at', 'DESC')
                       ->limit($limit)
                       ->findAll();
    }

    /**
     * 檢查文件權限
     */
    public function checkDocumentAccess($documentId, $userId, $userRole, $urbanRenewalId = null)
    {
        $document = $this->getDocumentWithDetails($documentId);
        if (!$document) {
            return false;
        }

        // 管理員可以存取所有文件
        if ($userRole === 'admin') {
            return true;
        }

        // 檢查是否為同一個更新會
        $meetingModel = model('MeetingModel');
        $meeting = $meetingModel->find($document['meeting_id']);

        if (!$meeting || ($urbanRenewalId && $meeting['urban_renewal_id'] !== $urbanRenewalId)) {
            return false;
        }

        return true;
    }

    /**
     * 產生檔案名稱
     */
    private function generateFileName($file)
    {
        $extension = $file->getExtension();
        return uniqid('doc_') . '_' . time() . '.' . $extension;
    }

    /**
     * 格式化檔案大小
     */
    public function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 取得文件類型名稱
     */
    public function getDocumentTypeName($type)
    {
        $types = [
            'agenda' => '議程',
            'minutes' => '會議紀錄',
            'attendance' => '出席名單',
            'notice' => '通知',
            'other' => '其他'
        ];

        return $types[$type] ?? $type;
    }

    /**
     * 清理孤立檔案
     */
    public function cleanOrphanFiles()
    {
        $uploadDir = WRITEPATH . 'uploads/documents/';
        $cleanedCount = 0;

        if (!is_dir($uploadDir)) {
            return $cleanedCount;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $fileName = $file->getFilename();

                // 檢查檔案是否在資料庫中
                $exists = $this->where('file_name', $fileName)
                              ->where('deleted_at', null)
                              ->countAllResults() > 0;

                if (!$exists) {
                    // 檔案不在資料庫中，刪除它
                    if (unlink($filePath)) {
                        $cleanedCount++;
                    }
                }
            }
        }

        return $cleanedCount;
    }

    /**
     * 取得儲存空間使用情況
     */
    public function getStorageUsage($urbanRenewalId = null)
    {
        $builder = $this->select('SUM(file_size) as total_size, COUNT(*) as total_files')
                       ->where('deleted_at', null);

        if ($urbanRenewalId) {
            $builder->join('meetings', 'meetings.id = meeting_documents.meeting_id', 'inner')
                   ->where('meetings.urban_renewal_id', $urbanRenewalId);
        }

        $result = $builder->first();

        return [
            'total_size' => $result['total_size'] ?: 0,
            'total_files' => $result['total_files'] ?: 0,
            'formatted_size' => $this->formatFileSize($result['total_size'] ?: 0)
        ];
    }
}