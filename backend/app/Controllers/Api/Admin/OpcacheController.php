<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\BaseController;

/**
 * OPcache 管理控制器
 * 
 * 提供檢查和清除 OPcache 的功能
 */
class OpcacheController extends BaseController
{
    /**
     * 取得 OPcache 狀態
     * 
     * GET /api/admin/opcache/status
     */
    public function status()
    {
        if (!function_exists('opcache_get_status')) {
            return $this->respond([
                'enabled' => false,
                'message' => 'OPcache 未安裝或未啟用'
            ]);
        }

        try {
            $status = opcache_get_status(false);
            $config = opcache_get_configuration();

            $data = [
                'enabled' => true,
                'opcache_enabled' => $status['opcache_enabled'],
                'cache_full' => $status['cache_full'],
                'restart_pending' => $status['restart_pending'],
                'restart_in_progress' => $status['restart_in_progress'],
                'memory_usage' => [
                    'used_memory' => $this->formatBytes($status['memory_usage']['used_memory']),
                    'free_memory' => $this->formatBytes($status['memory_usage']['free_memory']),
                    'wasted_memory' => $this->formatBytes($status['memory_usage']['wasted_memory']),
                    'current_wasted_percentage' => round($status['memory_usage']['current_wasted_percentage'], 2) . '%',
                ],
                'statistics' => [
                    'num_cached_scripts' => $status['opcache_statistics']['num_cached_scripts'],
                    'num_cached_keys' => $status['opcache_statistics']['num_cached_keys'],
                    'max_cached_keys' => $status['opcache_statistics']['max_cached_keys'],
                    'hits' => $status['opcache_statistics']['hits'],
                    'misses' => $status['opcache_statistics']['misses'],
                    'blacklist_misses' => $status['opcache_statistics']['blacklist_misses'],
                    'opcache_hit_rate' => round($status['opcache_statistics']['opcache_hit_rate'], 2) . '%',
                ],
                'interned_strings_usage' => [
                    'buffer_size' => $this->formatBytes($status['interned_strings_usage']['buffer_size']),
                    'used_memory' => $this->formatBytes($status['interned_strings_usage']['used_memory']),
                    'free_memory' => $this->formatBytes($status['interned_strings_usage']['free_memory']),
                    'number_of_strings' => $status['interned_strings_usage']['number_of_strings'],
                ],
                'configuration' => [
                    'validate_timestamps' => $config['directives']['opcache.validate_timestamps'],
                    'revalidate_freq' => $config['directives']['opcache.revalidate_freq'],
                    'max_accelerated_files' => $config['directives']['opcache.max_accelerated_files'],
                    'memory_consumption' => $this->formatBytes($config['directives']['opcache.memory_consumption']),
                ],
            ];

            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->fail('取得 OPcache 狀態失敗: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 清除 OPcache
     * 
     * POST /api/admin/opcache/reset
     */
    public function reset()
    {
        if (!function_exists('opcache_reset')) {
            return $this->fail('OPcache 未安裝或未啟用', 400);
        }

        try {
            $result = opcache_reset();

            if ($result) {
                return $this->respond([
                    'success' => true,
                    'message' => 'OPcache 已成功清除',
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            } else {
                return $this->fail('清除 OPcache 失敗', 500);
            }
        } catch (\Exception $e) {
            return $this->fail('清除 OPcache 失敗: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 使特定檔案的 cache 失效
     * 
     * POST /api/admin/opcache/invalidate
     * Body: { "file": "/path/to/file.php" }
     */
    public function invalidate()
    {
        if (!function_exists('opcache_invalidate')) {
            return $this->fail('OPcache 未安裝或未啟用', 400);
        }

        $data = $this->request->getJSON(true);
        $file = $data['file'] ?? null;

        if (!$file) {
            return $this->fail('請提供要清除的檔案路徑', 400);
        }

        // 轉換為絕對路徑
        if (!str_starts_with($file, '/')) {
            $file = ROOTPATH . $file;
        }

        if (!file_exists($file)) {
            return $this->fail('檔案不存在: ' . $file, 404);
        }

        try {
            $result = opcache_invalidate($file, true);

            if ($result) {
                return $this->respond([
                    'success' => true,
                    'message' => '檔案 cache 已清除',
                    'file' => $file,
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            } else {
                return $this->fail('清除檔案 cache 失敗', 500);
            }
        } catch (\Exception $e) {
            return $this->fail('清除檔案 cache 失敗: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 清除關鍵系統檔案的 cache
     * 
     * POST /api/admin/opcache/reset-system
     */
    public function resetSystem()
    {
        if (!function_exists('opcache_invalidate')) {
            return $this->fail('OPcache 未安裝或未啟用', 400);
        }

        // 需要清除的關鍵檔案
        $systemFiles = [
            'app/Config/Filters.php',
            'app/Config/Events.php',
            'app/Config/Routes.php',
            'app/Filters/ApiRequestLogFilter.php',
            'app/Libraries/ErrorLogger.php',
            'app/Models/ApiRequestLogModel.php',
            'app/Models/ErrorLogModel.php',
        ];

        $results = [];
        foreach ($systemFiles as $file) {
            $fullPath = ROOTPATH . $file;
            if (file_exists($fullPath)) {
                $success = opcache_invalidate($fullPath, true);
                $results[$file] = $success ? 'cleared' : 'failed';
            } else {
                $results[$file] = 'not_found';
            }
        }

        return $this->respond([
            'success' => true,
            'message' => '系統檔案 cache 已清除',
            'files' => $results,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 取得快取的檔案清單
     * 
     * GET /api/admin/opcache/scripts
     */
    public function scripts()
    {
        if (!function_exists('opcache_get_status')) {
            return $this->fail('OPcache 未安裝或未啟用', 400);
        }

        try {
            $status = opcache_get_status(true); // 包含腳本資訊
            
            if (!isset($status['scripts'])) {
                return $this->respond([
                    'scripts' => [],
                    'total' => 0,
                ]);
            }

            $scripts = [];
            foreach ($status['scripts'] as $file => $info) {
                $scripts[] = [
                    'file' => $file,
                    'hits' => $info['hits'],
                    'memory_consumption' => $this->formatBytes($info['memory_consumption']),
                    'last_used_timestamp' => date('Y-m-d H:i:s', $info['last_used_timestamp']),
                ];
            }

            // 按 hits 排序
            usort($scripts, function($a, $b) {
                return $b['hits'] - $a['hits'];
            });

            return $this->respond([
                'scripts' => array_slice($scripts, 0, 50), // 只返回前 50 個
                'total' => count($scripts),
            ]);
        } catch (\Exception $e) {
            return $this->fail('取得快取檔案清單失敗: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 格式化位元組
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
