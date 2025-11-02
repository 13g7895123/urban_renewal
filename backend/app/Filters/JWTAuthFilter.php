<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * JWT 認證過濾器
 * 驗證請求中的 JWT Token 是否有效
 */
class JWTAuthFilter implements FilterInterface
{
    /**
     * 在請求處理之前執行
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 載入 auth helper
        helper('auth');
        helper('response');

        // 驗證 Token
        $user = auth_validate_request();

        if (!$user) {
            return response_error('未授權存取，請先登入', 401);
        }

        // 將使用者資訊儲存到全域變數中，供控制器使用
        // 使用 CI4 推薦的方式：透過 header 或自訂屬性
        $_SERVER['AUTH_USER'] = $user;
        
        // 記錄除錯資訊（開發環境）
        if (ENVIRONMENT !== 'production') {
            log_message('debug', 'JWTAuthFilter: User authenticated - ID: ' . ($user['id'] ?? 'unknown') . 
                                ', Role: ' . ($user['role'] ?? 'unknown') . 
                                ', Is Company Manager: ' . ($user['is_company_manager'] ?? '0') .
                                ', Urban Renewal ID: ' . ($user['urban_renewal_id'] ?? 'null'));
        }
    }

    /**
     * 在請求處理之後執行
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 不需要處理
    }
}
