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

        // 將使用者資訊儲存到請求中，供後續使用
        $request->user = $user;
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
