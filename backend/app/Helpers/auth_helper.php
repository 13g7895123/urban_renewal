<?php

if (!function_exists('auth_validate_request')) {
    /**
     * Validate authentication token and check user permissions
     *
     * @param array $requiredRoles Optional array of required roles
     * @return array|false User data if valid, false if invalid
     */
    function auth_validate_request($requiredRoles = [])
    {
        $request = \Config\Services::request();

        // Get Authorization header
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return false;
        }

        $authValue = $authHeader->getValue();
        if (!$authValue || !str_starts_with($authValue, 'Bearer ')) {
            return false;
        }

        $token = substr($authValue, 7); // Remove 'Bearer ' prefix

        try {
            // Validate JWT token
            $jwtConfig = config('JWT');
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtConfig->key, $jwtConfig->algorithm));

            if (!$decoded || !isset($decoded->user_id)) {
                return false;
            }

            // Check if token is expired
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return false;
            }

            // Get user details
            $userModel = model('UserModel');
            $user = $userModel->find($decoded->user_id);

            if (!$user || !$user['is_active']) {
                return false;
            }

            // Check role permissions
            if (!empty($requiredRoles) && !in_array($user['role'], $requiredRoles)) {
                return false;
            }

            // Update last activity
            $sessionModel = model('UserSessionModel');
            if (isset($decoded->session_id)) {
                $sessionModel->updateActivity($decoded->session_id);
            }

            return $user;

        } catch (\Exception $e) {
            log_message('error', 'JWT validation error: ' . $e->getMessage());
            return false;
        }
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