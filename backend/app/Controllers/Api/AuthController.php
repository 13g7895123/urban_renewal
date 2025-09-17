<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends ResourceController
{
    protected $userModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->userModel = new UserModel();

        // Load helper functions
        helper(['auth', 'response']);

        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }

    /**
     * Handle preflight OPTIONS requests
     */
    public function options()
    {
        return $this->response->setStatusCode(200);
    }

    /**
     * 使用者登入
     * POST /api/auth/login
     */
    public function login()
    {
        try {
            $rules = [
                'username' => 'required|max_length[100]',
                'password' => 'required|min_length[6]'
            ];

            if (!$this->validate($rules)) {
                return response_validation_error('驗證失敗', $this->validator->getErrors());
            }

            $username = $this->request->getJSON()->username;
            $password = $this->request->getJSON()->password;

            // 查找使用者
            $user = $this->userModel->where('username', $username)
                                   ->where('is_active', 1)
                                   ->first();

            if (!$user) {
                return response_error('帳號或密碼錯誤', 401);
            }

            // 檢查帳號鎖定狀態
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return response_error('帳號已被鎖定，請稍後再試', 401);
            }

            // 驗證密碼
            if (!password_verify($password, $user['password_hash'])) {
                // 增加登入失敗次數
                $loginAttempts = ($user['login_attempts'] ?? 0) + 1;
                $updateData = ['login_attempts' => $loginAttempts];

                // 檢查是否需要鎖定帳號
                $maxAttempts = 5; // 可從系統設定取得
                if ($loginAttempts >= $maxAttempts) {
                    $lockoutTime = 1800; // 30分鐘，可從系統設定取得
                    $updateData['locked_until'] = date('Y-m-d H:i:s', time() + $lockoutTime);
                }

                $this->userModel->update($user['id'], $updateData);

                return response_error('帳號或密碼錯誤', 401);
            }

            // 重置登入失敗次數
            $this->userModel->update($user['id'], [
                'login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => date('Y-m-d H:i:s')
            ]);

            // 產生 JWT Token
            $token = $this->generateJWT($user);
            $refreshToken = $this->generateRefreshToken($user);

            // 儲存 session
            $this->saveUserSession($user['id'], $token, $refreshToken);

            // 移除敏感資料
            unset($user['password_hash'], $user['password_reset_token'], $user['login_attempts']);

            return $this->respond([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                    'expires_in' => 86400 // 24小時
                ],
                'message' => '登入成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return response_error('登入處理失敗', 500);
        }
    }

    /**
     * 使用者登出
     * POST /api/auth/logout
     */
    public function logout()
    {
        try {
            $token = $this->getTokenFromHeader();
            if (!$token) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => '未提供認證令牌'
                    ]
                ], 401);
            }

            // 使 session 失效
            $sessionModel = model('UserSessionModel');
            $sessionModel->where('session_token', $token)->set(['is_active' => 0])->update();

            return $this->respond([
                'success' => true,
                'message' => '登出成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '登出處理失敗'
                ]
            ], 500);
        }
    }

    /**
     * 刷新 Token
     * POST /api/auth/refresh
     */
    public function refresh()
    {
        try {
            $refreshToken = $this->request->getJSON()->refresh_token ?? '';

            $sessionModel = model('UserSessionModel');
            $session = $sessionModel->where('refresh_token', $refreshToken)
                                   ->where('is_active', 1)
                                   ->where('refresh_expires_at >', date('Y-m-d H:i:s'))
                                   ->first();

            if (!$session) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => 'Refresh token 無效或已過期'
                    ]
                ], 401);
            }

            $user = $this->userModel->find($session['user_id']);
            if (!$user || !$user['is_active']) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => '使用者不存在或已停用'
                    ]
                ], 401);
            }

            // 產生新的 tokens
            $newToken = $this->generateJWT($user);
            $newRefreshToken = $this->generateRefreshToken($user);

            // 更新 session
            $sessionModel->update($session['id'], [
                'session_token' => $newToken,
                'refresh_token' => $newRefreshToken,
                'expires_at' => date('Y-m-d H:i:s', time() + 86400),
                'refresh_expires_at' => date('Y-m-d H:i:s', time() + 604800), // 7天
                'last_activity_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'success' => true,
                'data' => [
                    'token' => $newToken,
                    'refresh_token' => $newRefreshToken,
                    'expires_in' => 86400
                ],
                'message' => 'Token 刷新成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Token refresh error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Token 刷新失敗'
                ]
            ], 500);
        }
    }

    /**
     * 取得當前使用者資訊
     * GET /api/auth/me
     */
    public function me()
    {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => '使用者未認證'
                    ]
                ], 401);
            }

            // 移除敏感資料
            unset($user['password_hash'], $user['password_reset_token'], $user['login_attempts']);

            return $this->respond([
                'success' => true,
                'data' => $user,
                'message' => '取得使用者資訊成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get user info error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '取得使用者資訊失敗'
                ]
            ], 500);
        }
    }

    /**
     * 忘記密碼
     * POST /api/auth/forgot-password
     */
    public function forgotPassword()
    {
        try {
            $rules = [
                'email' => 'required|valid_email'
            ];

            if (!$this->validate($rules)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '驗證失敗',
                        'details' => $this->validator->getErrors()
                    ]
                ], 422);
            }

            $email = $this->request->getJSON()->email;
            $user = $this->userModel->where('email', $email)->where('is_active', 1)->first();

            if (!$user) {
                // 為安全考量，即使使用者不存在也回傳成功訊息
                return $this->respond([
                    'success' => true,
                    'message' => '如果該信箱存在，我們已發送重設密碼連結'
                ]);
            }

            // 產生重設令牌
            $resetToken = bin2hex(random_bytes(32));
            $resetExpires = date('Y-m-d H:i:s', time() + 3600); // 1小時後過期

            $this->userModel->update($user['id'], [
                'password_reset_token' => $resetToken,
                'password_reset_expires' => $resetExpires
            ]);

            // TODO: 發送重設密碼郵件
            // 這裡應該整合郵件服務發送重設連結

            return $this->respond([
                'success' => true,
                'message' => '如果該信箱存在，我們已發送重設密碼連結'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Forgot password error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '重設密碼請求處理失敗'
                ]
            ], 500);
        }
    }

    /**
     * 重設密碼
     * POST /api/auth/reset-password
     */
    public function resetPassword()
    {
        try {
            $rules = [
                'token' => 'required',
                'password' => 'required|min_length[6]',
                'password_confirm' => 'required|matches[password]'
            ];

            if (!$this->validate($rules)) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => '驗證失敗',
                        'details' => $this->validator->getErrors()
                    ]
                ], 422);
            }

            $token = $this->request->getJSON()->token;
            $password = $this->request->getJSON()->password;

            $user = $this->userModel->where('password_reset_token', $token)
                                   ->where('password_reset_expires >', date('Y-m-d H:i:s'))
                                   ->where('is_active', 1)
                                   ->first();

            if (!$user) {
                return $this->fail([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_TOKEN',
                        'message' => '重設令牌無效或已過期'
                    ]
                ], 400);
            }

            // 更新密碼並清除重設令牌
            $this->userModel->update($user['id'], [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'password_reset_token' => null,
                'password_reset_expires' => null,
                'login_attempts' => 0,
                'locked_until' => null
            ]);

            return $this->respond([
                'success' => true,
                'message' => '密碼重設成功'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Reset password error: ' . $e->getMessage());
            return $this->fail([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '密碼重設失敗'
                ]
            ], 500);
        }
    }

    /**
     * 產生 JWT Token
     */
    private function generateJWT($user)
    {
        $payload = [
            'iss' => 'urban-renewal-system',
            'aud' => 'urban-renewal-users',
            'iat' => time(),
            'exp' => time() + 86400, // 24小時
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'urban_renewal_id' => $user['urban_renewal_id']
        ];

        $key = $_ENV['JWT_SECRET'] ?? 'urban_renewal_secret_key_2025';
        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * 產生 Refresh Token
     */
    private function generateRefreshToken($user)
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * 儲存使用者 Session
     */
    private function saveUserSession($userId, $token, $refreshToken)
    {
        $sessionModel = model('UserSessionModel');

        // 先清除該使用者的舊 sessions
        $sessionModel->where('user_id', $userId)->set(['is_active' => 0])->update();

        // 建立新 session
        $sessionData = [
            'user_id' => $userId,
            'session_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => date('Y-m-d H:i:s', time() + 86400),
            'refresh_expires_at' => date('Y-m-d H:i:s', time() + 604800), // 7天
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getServer('HTTP_USER_AGENT'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s')
        ];

        $sessionModel->insert($sessionData);
    }

    /**
     * 從 Header 取得 Token
     */
    private function getTokenFromHeader()
    {
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if ($header && strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        return null;
    }

    /**
     * 取得當前認證使用者
     */
    private function getCurrentUser()
    {
        $token = $this->getTokenFromHeader();
        if (!$token) {
            return null;
        }

        try {
            $key = $_ENV['JWT_SECRET'] ?? 'urban_renewal_secret_key_2025';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            return $this->userModel->find($decoded->user_id);
        } catch (\Exception $e) {
            return null;
        }
    }
}