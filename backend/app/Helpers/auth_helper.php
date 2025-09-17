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