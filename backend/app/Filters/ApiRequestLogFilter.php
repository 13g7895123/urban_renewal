<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ApiRequestLogModel;

/**
 * API 請求日誌過濾器
 * 
 * 自動記錄所有 API 請求與回應
 */
class ApiRequestLogFilter implements FilterInterface
{
    private static $startTimes = [];
    private static $requestData = [];

    public function before(RequestInterface $request, $arguments = null)
    {
        $requestId = spl_object_id($request);
        
        // 記錄開始時間
        self::$startTimes[$requestId] = microtime(true);

        // 收集請求資訊
        self::$requestData[$requestId] = [
            'method' => $request->getMethod(),
            'endpoint' => $request->getUri()->getPath(),
            'request_headers' => $this->getHeaders($request),
            'request_query' => $request->getGet() ?: null,
            'request_body' => $this->getRequestBody($request),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->toString(),
        ];

        // 取得使用者 ID（如果已認證）
        if (isset($_SERVER['AUTH_USER']['id'])) {
            self::$requestData[$requestId]['user_id'] = $_SERVER['AUTH_USER']['id'];
        }

        // 不阻擋請求
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $requestId = spl_object_id($request);
        
        // 如果沒有對應的請求資料，直接返回（可能 before 沒有執行）
        if (!isset(self::$requestData[$requestId])) {
            return $response;
        }

        // 計算執行時間
        $startTime = self::$startTimes[$requestId] ?? microtime(true);
        $duration = microtime(true) - $startTime;
        $durationMs = round($duration * 1000);

        // 收集回應資訊
        $logData = array_merge(self::$requestData[$requestId], [
            'response_status' => $response->getStatusCode(),
            'response_headers' => $this->getResponseHeaders($response),
            'response_body' => $this->getResponseBody($response),
            'duration_ms' => $durationMs,
        ]);

        // 如果是錯誤回應，嘗試取得錯誤訊息
        if ($response->getStatusCode() >= 400) {
            $logData['error_message'] = $this->extractErrorMessage($response);
        }

        // 非同步記錄日誌（避免影響回應速度）
        $this->logAsync($logData);

        // 清理已使用的資料
        unset(self::$startTimes[$requestId]);
        unset(self::$requestData[$requestId]);

        return $response;
    }

    /**
     * 取得請求標頭
     */
    private function getHeaders(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->headers() as $key => $value) {
            $headers[$key] = $value->getValue();
        }
        return $headers;
    }

    /**
     * 取得請求內容
     */
    private function getRequestBody(RequestInterface $request)
    {
        $contentType = $request->getHeaderLine('Content-Type');

        // JSON 請求
        if (strpos($contentType, 'application/json') !== false) {
            $body = $request->getBody();
            if ($body) {
                $decoded = json_decode($body, true);
                return $decoded ?: $body;
            }
        }

        // Form 請求
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false ||
            strpos($contentType, 'multipart/form-data') !== false) {
            $post = $request->getPost();
            return !empty($post) ? $post : null;
        }

        return null;
    }

    /**
     * 取得回應標頭
     */
    private function getResponseHeaders(ResponseInterface $response): array
    {
        $headers = [];
        foreach ($response->headers() as $key => $value) {
            $headers[$key] = $value->getValue();
        }
        return $headers;
    }

    /**
     * 取得回應內容
     */
    private function getResponseBody(ResponseInterface $response): ?string
    {
        $body = $response->getBody();
        
        // 限制回應內容大小（避免存儲過大的回應）
        if (strlen($body) > 1048576) { // 1MB
            return substr($body, 0, 1048576) . '... [TRUNCATED]';
        }

        return $body;
    }

    /**
     * 從回應中提取錯誤訊息
     */
    private function extractErrorMessage(ResponseInterface $response): ?string
    {
        $body = $response->getBody();
        
        if ($body) {
            $decoded = json_decode($body, true);
            if (isset($decoded['message'])) {
                return $decoded['message'];
            }
            if (isset($decoded['error'])) {
                return is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']);
            }
        }

        return null;
    }

    /**
     * 非同步記錄日誌
     */
    private function logAsync(array $data): void
    {
        try {
            // 使用 register_shutdown_function 在回應發送後執行
            register_shutdown_function(function () use ($data) {
                try {
                    $model = new ApiRequestLogModel();
                    $model->logRequest($data);
                } catch (\Exception $e) {
                    // 記錄失敗不應影響主要流程
                    log_message('error', 'Failed to log API request: ' . $e->getMessage());
                }
            });
        } catch (\Exception $e) {
            log_message('error', 'Failed to register API log: ' . $e->getMessage());
        }
    }
}
