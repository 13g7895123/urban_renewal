<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class SystemSettingsController extends ResourceController
{
    protected $modelName = 'App\Models\SystemSettingModel';
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
     * 取得系統設定列表（管理介面）
     */
    public function index()
    {
        try {
            // 驗證用戶權限（只有管理員可以查看所有設定）
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $category = $this->request->getGet('category');
            $settings = $this->model->getSettingsForManagement($category);

            return response_success('系統設定列表', [
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得系統設定列表失敗: ' . $e->getMessage());
            return response_error('取得系統設定列表失敗', 500);
        }
    }

    /**
     * 取得公開設定
     */
    public function public()
    {
        try {
            // 公開設定不需要驗證用戶
            $settings = $this->model->getPublicSettings();

            return response_success('公開設定', $settings);

        } catch (\Exception $e) {
            log_message('error', '取得公開設定失敗: ' . $e->getMessage());
            return response_error('取得公開設定失敗', 500);
        }
    }

    /**
     * 取得分類設定
     */
    public function category($category = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$category) {
                return response_error('分類為必填', 400);
            }

            // 非管理員只能查看公開設定
            $publicOnly = $user['role'] !== 'admin';
            $settings = $this->model->getSettingsByCategory($category, $publicOnly);

            return response_success('分類設定', [
                'category' => $category,
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得分類設定失敗: ' . $e->getMessage());
            return response_error('取得分類設定失敗', 500);
        }
    }

    /**
     * 取得單一設定值
     */
    public function get($key = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$key) {
                return response_error('設定鍵值為必填', 400);
            }

            // 檢查設定是否存在
            $setting = $this->model->where('setting_key', $key)->first();
            if (!$setting) {
                return response_error('找不到該設定', 404);
            }

            // 檢查權限：非管理員只能查看公開設定
            if ($user['role'] !== 'admin' && !$setting['is_public']) {
                return $this->failForbidden('無權限查看此設定');
            }

            $value = $this->model->getSetting($key);

            return response_success('設定值', [
                'key' => $key,
                'value' => $value,
                'type' => $setting['setting_type'],
                'title' => $setting['title'],
                'description' => $setting['description']
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得設定值失敗: ' . $e->getMessage());
            return response_error('取得設定值失敗', 500);
        }
    }

    /**
     * 更新設定值
     */
    public function set()
    {
        try {
            // 驗證用戶權限（只有管理員可以修改設定）
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            if (!isset($data['key']) || !isset($data['value'])) {
                return response_error('設定鍵值和值為必填', 400);
            }

            $key = $data['key'];
            $value = $data['value'];

            // 檢查設定是否存在
            $setting = $this->model->where('setting_key', $key)->first();
            if (!$setting) {
                return response_error('找不到該設定', 404);
            }

            // 檢查是否可編輯
            if (!$setting['is_editable']) {
                return response_error('該設定不可編輯', 400);
            }

            // 驗證設定值
            if (!$this->model->validateSettingValue($key, $value)) {
                return response_error('設定值驗證失敗', 400);
            }

            $success = $this->model->setSetting($key, $value, $user['id']);
            if (!$success) {
                return response_error('設定更新失敗', 500);
            }

            // 清除快取
            $this->model->clearSettingCache($key);

            $newValue = $this->model->getSetting($key);

            return response_success('設定更新成功', [
                'key' => $key,
                'value' => $newValue
            ]);

        } catch (\Exception $e) {
            log_message('error', '更新設定失敗: ' . $e->getMessage());
            return response_error('更新設定失敗', 500);
        }
    }

    /**
     * 批量更新設定
     */
    public function batchSet()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['settings']) || !is_array($data['settings'])) {
                return response_error('設定資料格式錯誤', 400);
            }

            $results = $this->model->batchSetSettings($data['settings'], $user['id']);

            // 清除所有設定快取
            $this->model->clearSettingCache();

            $successCount = count(array_filter($results));
            $totalCount = count($results);

            return response_success('批量設定完成', [
                'total' => $totalCount,
                'success' => $successCount,
                'failed' => $totalCount - $successCount,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            log_message('error', '批量更新設定失敗: ' . $e->getMessage());
            return response_error('批量更新設定失敗', 500);
        }
    }

    /**
     * 重設設定為預設值
     */
    public function reset($key = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$key) {
                return response_error('設定鍵值為必填', 400);
            }

            $success = $this->model->resetToDefault($key, $user['id']);
            if (!$success) {
                return response_error('重設失敗', 400);
            }

            // 清除快取
            $this->model->clearSettingCache($key);

            $newValue = $this->model->getSetting($key);

            return response_success('設定已重設為預設值', [
                'key' => $key,
                'value' => $newValue
            ]);

        } catch (\Exception $e) {
            log_message('error', '重設設定失敗: ' . $e->getMessage());
            return response_error('重設設定失敗', 500);
        }
    }

    /**
     * 取得設定分類列表
     */
    public function categories()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $categories = $this->model->getCategories();

            return response_success('設定分類列表', $categories);

        } catch (\Exception $e) {
            log_message('error', '取得設定分類失敗: ' . $e->getMessage());
            return response_error('取得設定分類失敗', 500);
        }
    }

    /**
     * 清除設定快取
     */
    public function clearCache()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $key = $this->request->getGet('key');
            $this->model->clearSettingCache($key);

            return response_success('快取已清除');

        } catch (\Exception $e) {
            log_message('error', '清除快取失敗: ' . $e->getMessage());
            return response_error('清除快取失敗', 500);
        }
    }

    /**
     * 驗證設定值
     */
    public function validateSetting()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['key']) || !isset($data['value'])) {
                return response_error('設定鍵值和值為必填', 400);
            }

            $isValid = $this->model->validateSettingValue($data['key'], $data['value']);

            return response_success('驗證結果', [
                'key' => $data['key'],
                'value' => $data['value'],
                'is_valid' => $isValid
            ]);

        } catch (\Exception $e) {
            log_message('error', '驗證設定值失敗: ' . $e->getMessage());
            return response_error('驗證設定值失敗', 500);
        }
    }

    /**
     * 取得系統資訊
     */
    public function systemInfo()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $info = [
                'app_name' => $this->model->getSetting('app.name', 'Urban Renewal System'),
                'app_version' => $this->model->getSetting('app.version', '1.0.0'),
                'php_version' => PHP_VERSION,
                'codeigniter_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
                'server_time' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get(),
                'memory_usage' => memory_get_usage(true),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ];

            return response_success('系統資訊', $info);

        } catch (\Exception $e) {
            log_message('error', '取得系統資訊失敗: ' . $e->getMessage());
            return response_error('取得系統資訊失敗', 500);
        }
    }
}