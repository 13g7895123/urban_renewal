<?php

if (!function_exists('auth_validate_request')) {
    /**
     * Validate authentication token and check user permissions with detailed debugging
     *
     * @param array $requiredRoles Optional array of required roles
     * @param bool $enableDebug Enable debug logging (default: true in development)
     * @return array|false User data if valid, false if invalid
     */
    function auth_validate_request($requiredRoles = [], $enableDebug = null)
    {
        // 判斷是否啟用除錯
        if ($enableDebug === null) {
            $enableDebug = ENVIRONMENT !== 'production';
        }
        
        $startTime = microtime(true);
        $request = \Config\Services::request();
        
        // 初始化除錯記錄
        $requestId = uniqid('jwt_', true);
        $debugData = [
            'request_id' => $requestId,
            'request_uri' => $request->getUri()->getPath(),
            'request_method' => $request->getMethod(),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'required_roles' => !empty($requiredRoles) ? json_encode($requiredRoles) : null,
        ];

        // Stage 1: 檢查 Authorization header
        $authHeader = $request->getHeader('Authorization');
        $debugData['has_auth_header'] = $authHeader ? 1 : 0;
        
        if (!$authHeader) {
            $debugData['stage'] = 'header_check';
            $debugData['stage_status'] = 'fail';
            $debugData['stage_message'] = 'Missing Authorization header';
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            return false;
        }

        $authValue = $authHeader->getValue();
        // 脫敏處理：只記錄前後各10個字元
        $debugData['auth_header_value'] = auth_mask_token($authValue);
        
        if (!$authValue || !str_starts_with($authValue, 'Bearer ')) {
            $debugData['stage'] = 'header_check';
            $debugData['stage_status'] = 'fail';
            $debugData['stage_message'] = 'Invalid Authorization header format';
            $debugData['token_type'] = str_starts_with($authValue, 'Bearer ') ? 'Bearer' : 'Unknown';
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            return false;
        }

        // Stage 2: 解析 Token
        $token = substr($authValue, 7); // Remove 'Bearer ' prefix
        $debugData['token_hash'] = hash('sha256', $token);
        $debugData['token_type'] = 'Bearer';

        try {
            // Stage 3: 解碼 JWT
            $jwtConfig = config('JWT');
            $debugData['stage'] = 'token_decode';
            
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtConfig->key, $jwtConfig->algorithm));
            
            $debugData['signature_valid'] = 1;
            
            if (!$decoded || !isset($decoded->user_id)) {
                $debugData['stage_status'] = 'fail';
                $debugData['stage_message'] = 'Invalid token structure: missing user_id';
                auth_save_debug_log($debugData, $startTime, $enableDebug);
                return false;
            }

            // 記錄 JWT payload 資訊
            $debugData['jwt_iss'] = $decoded->iss ?? null;
            $debugData['jwt_aud'] = $decoded->aud ?? null;
            $debugData['jwt_iat'] = $decoded->iat ?? null;
            $debugData['jwt_exp'] = $decoded->exp ?? null;
            $debugData['jwt_user_id'] = $decoded->user_id;
            $debugData['jwt_role'] = $decoded->role ?? null;
            $debugData['jwt_session_id'] = $decoded->session_id ?? null;
            $debugData['jwt_jti'] = $decoded->jti ?? null;

            // Stage 4: 檢查過期時間
            $debugData['stage'] = 'claims_validate';
            $currentTime = time();
            
            if (isset($decoded->exp)) {
                $debugData['is_expired'] = $decoded->exp < $currentTime ? 1 : 0;
                $debugData['time_until_exp'] = $decoded->exp - $currentTime;
            }

            // Stage 5: 驗證使用者
            $debugData['stage'] = 'user_validate';
            $debugData['user_id'] = $decoded->user_id;
            
            $userModel = model('UserModel');
            $user = $userModel->find($decoded->user_id);
            
            $debugData['user_exists'] = $user ? 1 : 0;
            
            if (!$user) {
                $debugData['stage_status'] = 'fail';
                $debugData['stage_message'] = 'User not found';
                auth_save_debug_log($debugData, $startTime, $enableDebug);
                return false;
            }
            
            $debugData['user_is_active'] = $user['is_active'] ?? 0;
            $debugData['user_role'] = $user['role'] ?? null;
            
            if (!$user['is_active']) {
                $debugData['stage_status'] = 'fail';
                $debugData['stage_message'] = 'User is inactive';
                auth_save_debug_log($debugData, $startTime, $enableDebug);
                return false;
            }

            // Stage 6: 檢查 Session（如果有）
            if (isset($decoded->session_id)) {
                $sessionModel = model('UserSessionModel');
                $session = $sessionModel->find($decoded->session_id);
                
                $debugData['session_exists'] = $session ? 1 : 0;
                
                if ($session) {
                    $debugData['session_is_expired'] = ($session['is_expired'] ?? 0) == 1 ? 1 : 0;
                    $debugData['session_logged_out'] = !empty($session['logged_out_at']) ? 1 : 0;
                    
                    if ($session['is_expired'] || !empty($session['logged_out_at'])) {
                        $debugData['stage'] = 'user_validate';
                        $debugData['stage_status'] = 'fail';
                        $debugData['stage_message'] = 'Session is expired or logged out';
                        auth_save_debug_log($debugData, $startTime, $enableDebug);
                        return false;
                    }
                    
                    // 更新最後活動時間
                    $sessionModel->updateActivity($decoded->session_id);
                }
            }

            // Stage 7: 檢查角色權限
            if (!empty($requiredRoles)) {
                $debugData['stage'] = 'permission_check';
                $debugData['role_check_passed'] = in_array($user['role'], $requiredRoles) ? 1 : 0;
                
                if (!in_array($user['role'], $requiredRoles)) {
                    $debugData['stage_status'] = 'fail';
                    $debugData['stage_message'] = sprintf(
                        'Insufficient permissions. User role: %s, Required: %s',
                        $user['role'],
                        implode(', ', $requiredRoles)
                    );
                    auth_save_debug_log($debugData, $startTime, $enableDebug);
                    return false;
                }
            }

            // Stage 8: 成功
            $debugData['stage'] = 'success';
            $debugData['stage_status'] = 'pass';
            $debugData['stage_message'] = 'Authentication successful';
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            
            return $user;

        } catch (\Firebase\JWT\ExpiredException $e) {
            $debugData['stage'] = 'token_decode';
            $debugData['stage_status'] = 'fail';
            $debugData['stage_message'] = 'Token expired';
            $debugData['is_expired'] = 1;
            $debugData['error_type'] = 'ExpiredException';
            $debugData['error_message'] = $e->getMessage();
            if (ENVIRONMENT !== 'production') {
                $debugData['error_trace'] = $e->getTraceAsString();
            }
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            return false;
            
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $debugData['stage'] = 'signature_verify';
            $debugData['stage_status'] = 'fail';
            $debugData['stage_message'] = 'Invalid signature';
            $debugData['signature_valid'] = 0;
            $debugData['error_type'] = 'SignatureInvalidException';
            $debugData['error_message'] = $e->getMessage();
            if (ENVIRONMENT !== 'production') {
                $debugData['error_trace'] = $e->getTraceAsString();
            }
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            return false;
            
        } catch (\Firebase\JWT\BeforeValidException $e) {
            $debugData['stage'] = 'claims_validate';
            $debugData['stage_status'] = 'fail';
            $debugData['stage_message'] = 'Token not yet valid';
            $debugData['error_type'] = 'BeforeValidException';
            $debugData['error_message'] = $e->getMessage();
            if (ENVIRONMENT !== 'production') {
                $debugData['error_trace'] = $e->getTraceAsString();
            }
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            return false;
            
        } catch (\Exception $e) {
            $debugData['stage'] = $debugData['stage'] ?? 'token_decode';
            $debugData['stage_status'] = 'error';
            $debugData['stage_message'] = 'Unexpected error during validation';
            $debugData['error_type'] = get_class($e);
            $debugData['error_message'] = $e->getMessage();
            if (ENVIRONMENT !== 'production') {
                $debugData['error_trace'] = $e->getTraceAsString();
            }
            auth_save_debug_log($debugData, $startTime, $enableDebug);
            log_message('error', 'JWT validation error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('auth_save_debug_log')) {
    /**
     * 儲存除錯日誌
     *
     * @param array $debugData 除錯資料
     * @param float $startTime 開始時間
     * @param bool $enableDebug 是否啟用除錯
     */
    function auth_save_debug_log($debugData, $startTime, $enableDebug)
    {
        if (!$enableDebug) {
            return;
        }
        
        $debugData['validation_time_ms'] = (microtime(true) - $startTime) * 1000;
        $debugData['created_at'] = date('Y-m-d H:i:s');
        
        try {
            $db = \Config\Database::connect();
            $db->table('jwt_debug_logs')->insert($debugData);
        } catch (\Exception $e) {
            log_message('error', 'Failed to save JWT debug log: ' . $e->getMessage());
        }
    }
}

if (!function_exists('auth_mask_token')) {
    /**
     * 遮罩敏感 token 資訊
     *
     * @param string $token Token 字串
     * @return string 遮罩後的 token
     */
    function auth_mask_token($token)
    {
        if (strlen($token) <= 20) {
            return '***masked***';
        }
        return substr($token, 0, 10) . '...' . substr($token, -10);
    }
}

if (!function_exists('auth_generate_token')) {
    /**
     * Generate JWT token for user
     *
     * @param array $user User data
     * @param int $sessionId Session ID
     * @return string JWT token
     */
    function auth_generate_token($user, $sessionId = null)
    {
        $jwtConfig = config('JWT');
        $now = time();

        $payload = [
            'iss' => $jwtConfig->issuer,
            'aud' => $jwtConfig->audience,
            'iat' => $now,
            'exp' => $now + $jwtConfig->expiry,
            'user_id' => $user['id'],
            'role' => $user['role'],
            'urban_renewal_id' => $user['urban_renewal_id'],
            'property_owner_id' => $user['property_owner_id'] ?? null
        ];

        if ($sessionId) {
            $payload['session_id'] = $sessionId;
        }

        return \Firebase\JWT\JWT::encode($payload, $jwtConfig->key, $jwtConfig->algorithm);
    }
}

if (!function_exists('auth_generate_refresh_token')) {
    /**
     * Generate refresh token
     *
     * @param array $user User data
     * @return string Refresh token
     */
    function auth_generate_refresh_token($user)
    {
        $jwtConfig = config('JWT');
        $now = time();

        $payload = [
            'iss' => $jwtConfig->issuer,
            'aud' => $jwtConfig->audience,
            'iat' => $now,
            'exp' => $now + $jwtConfig->refreshExpiry,
            'user_id' => $user['id'],
            'type' => 'refresh'
        ];

        return \Firebase\JWT\JWT::encode($payload, $jwtConfig->key, $jwtConfig->algorithm);
    }
}

if (!function_exists('auth_get_current_user')) {
    /**
     * Get current authenticated user
     *
     * @return array|null User data or null
     */
    function auth_get_current_user()
    {
        return auth_validate_request();
    }
}

if (!function_exists('auth_check_permission')) {
    /**
     * Check if user has specific permission
     *
     * @param string $permission Permission name
     * @param array $user Optional user data (will get current user if not provided)
     * @return bool
     */
    function auth_check_permission($permission, $user = null)
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        // Admin has all permissions
        if ($user['role'] === 'admin') {
            return true;
        }

        // Get user permissions
        $userPermissionModel = model('UserPermissionModel');
        $permissions = $userPermissionModel->getUserPermissions($user['id']);

        return in_array($permission, $permissions);
    }
}

if (!function_exists('auth_check_resource_scope')) {
    /**
     * 檢查使用者是否可以存取特定 urban_renewal_id 的資源
     *
     * @param int|null $resourceUrbanRenewalId 資源的 urban_renewal_id
     * @param array|null $user 使用者資料（若未提供則取得當前使用者）
     * @return bool
     */
    function auth_check_resource_scope(?int $resourceUrbanRenewalId, ?array $user = null): bool
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        // 管理員可以存取所有資源
        if ($user['role'] === 'admin') {
            return true;
        }

        // 非管理員使用者只能存取其指派的 urban_renewal_id
        $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;

        if ($userUrbanRenewalId === null) {
            return false;
        }

        return $resourceUrbanRenewalId === $userUrbanRenewalId;
    }
}

if (!function_exists('auth_can_access_resource')) {
    /**
     * 檢查使用者是否可以存取資源（結合角色權限和資源範圍）
     *
     * @param string $requiredPermission 需要的權限
     * @param int|null $resourceUrbanRenewalId 資源的 urban_renewal_id（可選）
     * @param array|null $user 使用者資料
     * @return bool
     */
    function auth_can_access_resource(
        string $requiredPermission,
        ?int $resourceUrbanRenewalId = null,
        ?array $user = null
    ): bool {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        // 檢查角色權限
        if (!auth_check_permission($requiredPermission, $user)) {
            return false;
        }

        // 如果提供了 resourceUrbanRenewalId，檢查資源範圍
        if ($resourceUrbanRenewalId !== null) {
            return auth_check_resource_scope($resourceUrbanRenewalId, $user);
        }

        return true;
    }
}

if (!function_exists('auth_is_company_manager')) {
    /**
     * 檢查使用者是否為企業管理者
     *
     * @param array|null $user 使用者資料（若未提供則取得當前使用者）
     * @return bool
     */
    function auth_is_company_manager(?array $user = null): bool
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        return ($user['user_type'] ?? '') === 'enterprise'
            && ($user['is_company_manager'] ?? 0) == 1;
    }
}

if (!function_exists('auth_is_company_user')) {
    /**
     * 檢查使用者是否為企業使用者（含管理者）
     *
     * @param array|null $user 使用者資料（若未提供則取得當前使用者）
     * @return bool
     */
    function auth_is_company_user(?array $user = null): bool
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        return ($user['user_type'] ?? '') === 'enterprise';
    }
}

if (!function_exists('auth_is_general_user')) {
    /**
     * 檢查使用者是否為一般使用者
     *
     * @param array|null $user 使用者資料（若未提供則取得當前使用者）
     * @return bool
     */
    function auth_is_general_user(?array $user = null): bool
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        return ($user['user_type'] ?? 'general') === 'general';
    }
}

if (!function_exists('auth_can_manage_company')) {
    /**
     * 檢查使用者是否可以管理指定企業
     *
     * @param int $urbanRenewalId 都市更新會ID
     * @param array|null $user 使用者資料（若未提供則取得當前使用者）
     * @return bool
     */
    function auth_can_manage_company(int $urbanRenewalId, ?array $user = null): bool
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        // 系統管理員可以管理所有企業
        if ($user['role'] === 'admin') {
            return true;
        }

        // 企業管理者只能管理自己的企業
        return auth_is_company_manager($user)
            && ($user['urban_renewal_id'] ?? null) == $urbanRenewalId;
    }
}

if (!function_exists('auth_can_manage_user')) {
    /**
     * 檢查使用者是否可以管理指定的使用者
     *
     * @param array $targetUser 目標使用者資料
     * @param array|null $user 操作者資料（若未提供則取得當前使用者）
     * @return bool
     */
    function auth_can_manage_user(array $targetUser, ?array $user = null): bool
    {
        if (!$user) {
            $user = auth_get_current_user();
        }

        if (!$user) {
            return false;
        }

        // 不能管理自己
        if ($user['id'] === $targetUser['id']) {
            return false;
        }

        // 系統管理員可以管理所有使用者
        if ($user['role'] === 'admin') {
            return true;
        }

        // 企業管理者只能管理同企業的使用者
        if (auth_is_company_manager($user)) {
            // 不能管理系統管理員
            if ($targetUser['role'] === 'admin') {
                return false;
            }

            // 必須是同一個企業
            return ($user['urban_renewal_id'] ?? null) === ($targetUser['urban_renewal_id'] ?? null);
        }

        return false;
    }
}