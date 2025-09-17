<?php

if (!function_exists('response_success')) {
    /**
     * Return standardized success response
     *
     * @param string $message Success message
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_success($message, $data = null, $statusCode = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $response['timestamp'] = date('Y-m-d H:i:s');

        return \Config\Services::response()
            ->setStatusCode($statusCode)
            ->setJSON($response);
    }
}

if (!function_exists('response_error')) {
    /**
     * Return standardized error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param mixed $errors Detailed errors
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_error($message, $statusCode = 400, $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return \Config\Services::response()
            ->setStatusCode($statusCode)
            ->setJSON($response);
    }
}

if (!function_exists('response_validation_error')) {
    /**
     * Return validation error response
     *
     * @param array $errors Validation errors
     * @param string $message Optional custom message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_validation_error($errors, $message = '資料驗證失敗')
    {
        return response_error($message, 422, $errors);
    }
}

if (!function_exists('response_unauthorized')) {
    /**
     * Return unauthorized error response
     *
     * @param string $message Optional custom message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_unauthorized($message = '未經授權的存取')
    {
        return response_error($message, 401);
    }
}

if (!function_exists('response_forbidden')) {
    /**
     * Return forbidden error response
     *
     * @param string $message Optional custom message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_forbidden($message = '權限不足')
    {
        return response_error($message, 403);
    }
}

if (!function_exists('response_not_found')) {
    /**
     * Return not found error response
     *
     * @param string $message Optional custom message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_not_found($message = '資源不存在')
    {
        return response_error($message, 404);
    }
}

if (!function_exists('response_server_error')) {
    /**
     * Return server error response
     *
     * @param string $message Optional custom message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_server_error($message = '伺服器內部錯誤')
    {
        return response_error($message, 500);
    }
}

if (!function_exists('response_paginated')) {
    /**
     * Return paginated response
     *
     * @param string $message Success message
     * @param array $items Data items
     * @param object $pager Pager object
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_paginated($message, $items, $pager)
    {
        $data = [
            'items' => $items,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'last_page' => $pager->getLastPage(),
                'first_page_url' => $pager->getFirstPageUrl(),
                'last_page_url' => $pager->getLastPageUrl(),
                'next_page_url' => $pager->getNextPageUrl(),
                'previous_page_url' => $pager->getPreviousPageUrl()
            ]
        ];

        return response_success($message, $data);
    }
}

if (!function_exists('response_created')) {
    /**
     * Return created response
     *
     * @param string $message Success message
     * @param mixed $data Response data
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_created($message, $data = null)
    {
        return response_success($message, $data, 201);
    }
}

if (!function_exists('response_no_content')) {
    /**
     * Return no content response
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    function response_no_content()
    {
        return \Config\Services::response()
            ->setStatusCode(204);
    }
}