<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CORS 過濾器
 * 統一處理跨域請求設定
 */
class CorsFilter implements FilterInterface
{
    /**
     * 允許的來源清單
     */
    private array $allowedOrigins = [
        'https://urban-renewal.mercylife.cc/',
    ];

    /**
     * 在請求處理之前執行
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');

        // 檢查是否為允許的來源
        if ($origin && in_array($origin, $this->allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400'); // 24 小時
        }

        // 處理 OPTIONS 預檢請求
        if ($request->getMethod() === 'options') {
            exit(0);
        }
    }

    /**
     * 在請求處理之後執行
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');

        // 在響應中設置 CORS headers
        if ($origin && in_array($origin, $this->allowedOrigins, true)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        return $response;
    }
}
