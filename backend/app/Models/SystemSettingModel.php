<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'setting_type',
        'category',
        'title',
        'description',
        'validation_rules',
        'is_public',
        'is_editable',
        'display_order',
        'created_by',
        'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'setting_key' => 'required|max_length[100]|is_unique[system_settings.setting_key,id,{id}]',
        'setting_type' => 'required|in_list[string,number,boolean,json,encrypted]',
        'category' => 'required|max_length[50]',
        'title' => 'permit_empty|max_length[255]'
    ];

    protected $validationMessages = [
        'setting_key' => [
            'required' => '設定鍵值為必填',
            'max_length' => '設定鍵值不可超過100字元',
            'is_unique' => '設定鍵值已存在'
        ],
        'setting_type' => [
            'required' => '設定類型為必填',
            'in_list' => '設定類型必須為：string, number, boolean, json, encrypted'
        ],
        'category' => [
            'required' => '分類為必填',
            'max_length' => '分類不可超過50字元'
        ],
        'title' => [
            'max_length' => '標題不可超過255字元'
        ]
    ];

    /**
     * 取得設定值
     */
    public function getSetting($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return $this->parseSettingValue($setting['setting_value'], $setting['setting_type']);
    }

    /**
     * 設定值
     */
    public function setSetting($key, $value, $userId = null)
    {
        $setting = $this->where('setting_key', $key)->first();

        if ($setting) {
            // 檢查是否可編輯
            if (!$setting['is_editable']) {
                return false;
            }

            // 更新現有設定
            $updateData = [
                'setting_value' => $this->formatSettingValue($value, $setting['setting_type']),
                'updated_by' => $userId
            ];

            // 記錄變更歷史
            $this->recordSettingHistory($setting['id'], $setting['setting_value'], $updateData['setting_value'], $userId);

            return $this->update($setting['id'], $updateData);
        }

        return false; // 不允許動態建立新設定
    }

    /**
     * 取得分類設定
     */
    public function getSettingsByCategory($category, $publicOnly = false)
    {
        $builder = $this->where('category', $category);

        if ($publicOnly) {
            $builder->where('is_public', 1);
        }

        $settings = $builder->orderBy('display_order', 'ASC')
                           ->orderBy('setting_key', 'ASC')
                           ->findAll();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = [
                'value' => $this->parseSettingValue($setting['setting_value'], $setting['setting_type']),
                'type' => $setting['setting_type'],
                'title' => $setting['title'],
                'description' => $setting['description'],
                'is_editable' => $setting['is_editable'],
                'validation_rules' => $setting['validation_rules']
            ];
        }

        return $result;
    }

    /**
     * 取得所有公開設定
     */
    public function getPublicSettings()
    {
        $settings = $this->where('is_public', 1)
                        ->orderBy('category', 'ASC')
                        ->orderBy('display_order', 'ASC')
                        ->findAll();

        $result = [];
        foreach ($settings as $setting) {
            if (!isset($result[$setting['category']])) {
                $result[$setting['category']] = [];
            }

            $result[$setting['category']][$setting['setting_key']] = [
                'value' => $this->parseSettingValue($setting['setting_value'], $setting['setting_type']),
                'type' => $setting['setting_type'],
                'title' => $setting['title'],
                'description' => $setting['description']
            ];
        }

        return $result;
    }

    /**
     * 批量設定
     */
    public function batchSetSettings($settings, $userId = null)
    {
        $results = [];
        foreach ($settings as $key => $value) {
            $results[$key] = $this->setSetting($key, $value, $userId);
        }
        return $results;
    }

    /**
     * 取得設定管理列表
     */
    public function getSettingsForManagement($category = null)
    {
        $builder = $this->select('id, setting_key, setting_value, setting_type, category,
                                title, description, is_public, is_editable, display_order');

        if ($category) {
            $builder->where('category', $category);
        }

        $settings = $builder->orderBy('category', 'ASC')
                           ->orderBy('display_order', 'ASC')
                           ->orderBy('setting_key', 'ASC')
                           ->findAll();

        foreach ($settings as &$setting) {
            $setting['parsed_value'] = $this->parseSettingValue($setting['setting_value'], $setting['setting_type']);

            // 加密類型不顯示實際值
            if ($setting['setting_type'] === 'encrypted') {
                $setting['display_value'] = '***';
            } else {
                $setting['display_value'] = $setting['parsed_value'];
            }
        }

        return $settings;
    }

    /**
     * 取得分類列表
     */
    public function getCategories()
    {
        return $this->select('category, COUNT(*) as count')
                   ->groupBy('category')
                   ->orderBy('category', 'ASC')
                   ->findAll();
    }

    /**
     * 解析設定值
     */
    private function parseSettingValue($value, $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float)$value : 0;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true) ?: [];
            case 'encrypted':
                return $this->decryptValue($value);
            default:
                return $value;
        }
    }

    /**
     * 格式化設定值
     */
    private function formatSettingValue($value, $type)
    {
        switch ($type) {
            case 'number':
                return (string)$value;
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            case 'encrypted':
                return $this->encryptValue($value);
            default:
                return (string)$value;
        }
    }

    /**
     * 加密值
     */
    private function encryptValue($value)
    {
        $key = env('ENCRYPTION_KEY', 'default-key');
        return base64_encode(openssl_encrypt($value, 'AES-256-CBC', $key, 0, substr(md5($key), 0, 16)));
    }

    /**
     * 解密值
     */
    private function decryptValue($encryptedValue)
    {
        if (empty($encryptedValue)) {
            return '';
        }

        $key = env('ENCRYPTION_KEY', 'default-key');
        $decrypted = openssl_decrypt(base64_decode($encryptedValue), 'AES-256-CBC', $key, 0, substr(md5($key), 0, 16));
        return $decrypted ?: '';
    }

    /**
     * 記錄設定變更歷史
     */
    private function recordSettingHistory($settingId, $oldValue, $newValue, $userId)
    {
        $historyModel = model('SystemSettingHistoryModel');
        return $historyModel->insert([
            'setting_id' => $settingId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $userId,
            'changed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 驗證設定值
     */
    public function validateSettingValue($key, $value)
    {
        $setting = $this->where('setting_key', $key)->first();
        if (!$setting || !$setting['validation_rules']) {
            return true;
        }

        $validation = \Config\Services::validation();
        $rules = json_decode($setting['validation_rules'], true);

        if (!$rules) {
            return true;
        }

        $validation->setRules(['value' => $rules]);
        return $validation->run(['value' => $value]);
    }

    /**
     * 重設設定為預設值
     */
    public function resetToDefault($key, $userId = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        if (!$setting || !$setting['is_editable']) {
            return false;
        }

        // 這裡可以定義預設值邏輯
        $defaultValues = [
            'app.name' => 'Urban Renewal System',
            'app.version' => '1.0.0',
            'mail.enabled' => false,
            'system.debug' => false
        ];

        $defaultValue = $defaultValues[$key] ?? '';
        return $this->setSetting($key, $defaultValue, $userId);
    }

    /**
     * 取得快取設定
     */
    public function getCachedSetting($key, $default = null)
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'setting_' . $key;

        $value = $cache->get($cacheKey);
        if ($value === null) {
            $value = $this->getSetting($key, $default);
            $cache->save($cacheKey, $value, 3600); // 快取1小時
        }

        return $value;
    }

    /**
     * 清除設定快取
     */
    public function clearSettingCache($key = null)
    {
        $cache = \Config\Services::cache();

        if ($key) {
            $cache->delete('setting_' . $key);
        } else {
            // 清除所有設定快取
            $cache->deleteMatching('setting_*');
        }

        return true;
    }
}