<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UrbanRenewalModel;
use CodeIgniter\HTTP\ResponseInterface;

class UrbanRenewalController extends BaseController
{
    protected $urbanRenewalModel;
    protected $format = 'json';

    public function __construct()
    {
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
     * Get all urban renewals
     * GET /api/urban-renewals
     */
    public function index()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $search = $this->request->getGet('search');

            if ($search) {
                $data = $this->urbanRenewalModel->searchByName($search, $page, $perPage);
            } else {
                $data = $this->urbanRenewalModel->getUrbanRenewals($page, $perPage);
            }

            $pager = $this->urbanRenewalModel->pager;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $data,
                'pagination' => [
                    'current_page' => $pager->getCurrentPage(),
                    'per_page' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'total_pages' => $pager->getPageCount()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single urban renewal
     * GET /api/urban-renewals/{id}
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少ID參數'
                ]);
            }

            $data = $this->urbanRenewalModel->getUrbanRenewal($id);

            if (!$data) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new urban renewal
     * POST /api/urban-renewals
     */
    public function create()
    {
        try {
            $data = [
                'name' => $this->request->getPost('name'),
                'area' => $this->request->getPost('area'),
                'member_count' => $this->request->getPost('memberCount') ?? $this->request->getPost('member_count'),
                'chairman_name' => $this->request->getPost('chairmanName') ?? $this->request->getPost('chairman_name'),
                'chairman_phone' => $this->request->getPost('chairmanPhone') ?? $this->request->getPost('chairman_phone'),
            ];

            // Handle JSON requests
            if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
                $json = $this->request->getJSON(true);
                $data = [
                    'name' => $json['name'] ?? null,
                    'area' => $json['area'] ?? null,
                    'member_count' => $json['memberCount'] ?? $json['member_count'] ?? null,
                    'chairman_name' => $json['chairmanName'] ?? $json['chairman_name'] ?? null,
                    'chairman_phone' => $json['chairmanPhone'] ?? $json['chairman_phone'] ?? null,
                ];
            }

            if (!$this->urbanRenewalModel->insert($data)) {
                $errors = $this->urbanRenewalModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '資料驗證失敗',
                    'errors' => $errors
                ]);
            }

            $insertId = $this->urbanRenewalModel->getInsertID();
            $newData = $this->urbanRenewalModel->find($insertId);

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'message' => '新增更新會成功',
                'data' => $newData
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '新增失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update urban renewal
     * PUT /api/urban-renewals/{id}
     */
    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少ID參數'
                ]);
            }

            $existing = $this->urbanRenewalModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            // Handle JSON requests
            $data = [];
            if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
                $json = $this->request->getJSON(true);
                $data = [
                    'name' => $json['name'] ?? null,
                    'area' => $json['area'] ?? null,
                    'member_count' => $json['memberCount'] ?? $json['member_count'] ?? null,
                    'chairman_name' => $json['chairmanName'] ?? $json['chairman_name'] ?? null,
                    'chairman_phone' => $json['chairmanPhone'] ?? $json['chairman_phone'] ?? null,
                ];
            } else {
                $data = [
                    'name' => $this->request->getPost('name'),
                    'area' => $this->request->getPost('area'),
                    'member_count' => $this->request->getPost('memberCount') ?? $this->request->getPost('member_count'),
                    'chairman_name' => $this->request->getPost('chairmanName') ?? $this->request->getPost('chairman_name'),
                    'chairman_phone' => $this->request->getPost('chairmanPhone') ?? $this->request->getPost('chairman_phone'),
                ];
            }

            // Remove null values
            $data = array_filter($data, function($value) {
                return $value !== null;
            });

            if (!$this->urbanRenewalModel->update($id, $data)) {
                $errors = $this->urbanRenewalModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '資料驗證失敗',
                    'errors' => $errors
                ]);
            }

            $updatedData = $this->urbanRenewalModel->find($id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '更新成功',
                'data' => $updatedData
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '更新失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete urban renewal
     * DELETE /api/urban-renewals/{id}
     */
    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少ID參數'
                ]);
            }

            $existing = $this->urbanRenewalModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            if (!$this->urbanRenewalModel->delete($id)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => '刪除失敗'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '刪除成功'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '刪除失敗：' . $e->getMessage()
            ]);
        }
    }
}