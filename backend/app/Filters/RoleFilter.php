<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 角色權限過濾器
 * 檢查使用者是否擁有指定的角色權限
 */
class RoleFilter implements FilterInterface
{
    /**
     * 在請求處理之前執行
     *
     * @param RequestInterface $request
     * @param array|null       $arguments 允許的角色陣列，例如: ['admin', 'staff']
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 載入 helper
        helper('auth');
        helper('response');

        // 取得當前使用者
        $user = auth_get_current_user();

        if (!$user) {
            return response_error('未授權存取，請先登入', 401);
        }

        // 如果沒有指定角色要求，直接通過
        if (empty($arguments)) {
            return;
        }

        // 檢查使用者角色是否在允許的角色列表中
        $userRole = $user['role'] ?? null;
        if (!in_array($userRole, $arguments)) {
            return response_error('權限不足，無法存取此資源', 403);
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
