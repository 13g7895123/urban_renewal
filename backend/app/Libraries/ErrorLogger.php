<?php

namespace App\Libraries;

use App\Models\ErrorLogModel;
use CodeIgniter\HTTP\RequestInterface;

/**
 * 錯誤記錄器
 * 
 * 捕捉並記錄系統錯誤到資料庫
 */
class ErrorLogger
{
    protected static $instance = null;
    protected $errorLogModel;
    protected $enabled = true;

    public function __construct()
    {
        $this->errorLogModel = new ErrorLogModel();
    }

    /**
     * 取得單例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 停用錯誤記錄（用於測試）
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * 啟用錯誤記錄
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * 記錄異常
     */
    public function logException(\Throwable $exception, string $severity = 'error', array $context = []): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            // 收集請求資訊
            $request = service('request');
            $requestData = $this->getRequestData($request);

            // 準備錯誤資料
            $data = array_merge([
                'severity' => $severity,
                'message' => $exception->getMessage(),
                'exception_class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'context' => !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
            ], $requestData);

            return $this->errorLogModel->logError($data);
        } catch (\Exception $e) {
            // 記錄錯誤失敗時，至少寫入檔案日誌
            log_message('error', 'Failed to log exception to database: ' . $e->getMessage());
            log_message('error', 'Original exception: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * 記錄錯誤訊息
     */
    public function logError(string $message, string $severity = 'error', array $context = [], string $file = null, int $line = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $request = service('request');
            $requestData = $this->getRequestData($request);

            $data = array_merge([
                'severity' => $severity,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'context' => !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
            ], $requestData);

            return $this->errorLogModel->logError($data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log error to database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 記錄 ValidationException
     */
    public function logValidationError(\Throwable $exception, array $validationErrors = []): bool
    {
        $context = [
            'validation_errors' => $validationErrors,
        ];

        return $this->logException($exception, 'warning', $context);
    }

    /**
     * 記錄資料庫錯誤
     */
    public function logDatabaseError(\Throwable $exception, string $query = null): bool
    {
        $context = [];
        if ($query) {
            $context['sql_query'] = $query;
        }

        return $this->logException($exception, 'error', $context);
    }

    /**
     * 取得請求資料
     */
    private function getRequestData(RequestInterface $request): array
    {
        $data = [];

        try {
            $data['request_method'] = $request->getMethod();
            $data['request_uri'] = (string) $request->getUri();
            
            // 取得請求內容
            $body = $request->getBody();
            if (!empty($body)) {
                // 限制大小
                if (strlen($body) > 10240) { // 10KB
                    $body = substr($body, 0, 10240) . '... [TRUNCATED]';
                }
                $data['request_body'] = $body;
            }

            // 取得 IP
            $data['ip_address'] = $request->getIPAddress();

            // 取得 User Agent
            $userAgent = $request->getUserAgent();
            if ($userAgent) {
                $data['user_agent'] = $userAgent->toString();
            }

            // 取得使用者 ID
            if (isset($_SERVER['AUTH_USER']['id'])) {
                $data['user_id'] = $_SERVER['AUTH_USER']['id'];
            }
        } catch (\Exception $e) {
            // 即使取得請求資料失敗，也不應該影響錯誤記錄
            log_message('warning', 'Failed to get request data for error log: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * 註冊全域錯誤處理器
     */
    public static function register(): void
    {
        $logger = self::getInstance();

        // 註冊 Exception Handler
        set_exception_handler(function ($exception) use ($logger) {
            $logger->logException($exception, 'error');
            
            // 繼續拋出異常，讓 CodeIgniter 的錯誤處理器也能處理
            throw $exception;
        });

        // 註冊 Error Handler
        set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
            // 只記錄錯誤和警告
            if (error_reporting() & $severity) {
                $severityMap = [
                    E_ERROR => 'error',
                    E_WARNING => 'warning',
                    E_NOTICE => 'notice',
                    E_USER_ERROR => 'error',
                    E_USER_WARNING => 'warning',
                    E_USER_NOTICE => 'notice',
                ];

                $level = $severityMap[$severity] ?? 'error';
                $logger->logError($message, $level, [], $file, $line);
            }

            // 返回 false 讓 PHP 的預設錯誤處理器也能執行
            return false;
        });
    }
}
