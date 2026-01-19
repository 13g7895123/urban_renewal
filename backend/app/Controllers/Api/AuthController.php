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

    // Cookie 設定常數
    private const TOKEN_COOKIE_NAME = 'auth_token';
    private const REFRESH_COOKIE_NAME = 'refresh_token';
    private const TOKEN_EXPIRY = 86400; // 24 小時
    private const REFRESH_EXPIRY = 604800; // 7 天

    public function __construct()
    {
        $this->userModel = new UserModel();

        // Load helper functions
        helper(['auth', 'response', 'audit', 'cookie']);
    }

    /**
     * 設定 CORS headers (支援 credentials)
     */
    private function setCorsHeaders()
    {
        $allowedOrigins = [
            'https://urban.l',
            'http://localhost:9128',
            'http://localhost:3000'
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
    }

    /**
     * Handle preflight OPTIONS requests
     */
    public function options()
    {
        $this->setCorsHeaders();
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

            $json = $this->request->getJSON(true);
            $username = $json['username'] ?? '';
            $password = $json['password'] ?? '';

            // 查找使用者 (不先過濾 is_active，以便提供更精確的錯誤訊息)
            $user = $this->userModel->where('username', $username)->first();

            if (!$user) {
                // 記錄失敗登入事件（使用者不存在）
                log_auth_event('login_failure', null, $username, 'invalid_credentials');
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

                // 記錄失敗登入事件（密碼錯誤）
                $failureReason = $loginAttempts >= $maxAttempts ? 'account_locked' : 'invalid_password';
                log_auth_event('login_failure', $user['id'], $username, $failureReason, [
                    'login_attempts' => $loginAttempts
                ]);

                return response_error('帳號或密碼錯誤', 401);
            }

            // 密碼驗證通過，檢查帳號狀態
            if (!$user['is_active']) {
                $status = $user['approval_status'] ?? 'approved';
                if ($status === 'pending') {
                    return response_error('您的帳號正在等待公司審核，請聯繫公司管理者', 401);
                } elseif ($status === 'rejected') {
                    return response_error('您的帳號申請已被拒絕，請聯繫公司管理者', 401);
                }
                return response_error('帳號已被停用', 401);
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

            // 設定 httpOnly cookies
            $this->setAuthCookies($token, $refreshToken);

            // 記錄成功登入事件
            log_auth_event('login_success', $user['id'], null, null, [
                'role' => $user['role'],
                'urban_renewal_id' => $user['urban_renewal_id']
            ]);

            // 移除敏感資料（保留 is_company_manager 和 user_type 等必要欄位）
            unset($user['password_hash'], $user['password_reset_token'], $user['login_attempts']);

            $this->setCorsHeaders();

            return $this->respond([
                'success' => true,
                'data' => [
                    'user' => $user, // 包含 is_company_manager, user_type 等所有使用者欄位
                    'expires_in' => self::TOKEN_EXPIRY
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
            $token = $this->getTokenFromCookie();
            if (!$token) {
                $token = $this->getTokenFromHeader(); // fallback
            }

            if ($token) {
                // 使 session 失效
                $sessionModel = model('UserSessionModel');
                $session = $sessionModel->where('session_token', $token)->first();
                $sessionModel->where('session_token', $token)->set(['is_active' => 0])->update();

                // 記錄登出事件
                if ($session) {
                    log_auth_event('logout', $session['user_id']);
                }
            }

            // 清除 cookies
            $this->clearAuthCookies();
            $this->setCorsHeaders();

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
            // 優先從 cookie 取得 refresh token
            $refreshToken = $this->getRefreshTokenFromCookie();
            if (!$refreshToken) {
                $json = $this->request->getJSON(true);
                $refreshToken = $json['refresh_token'] ?? '';
            }

            $sessionModel = model('UserSessionModel');
            $session = $sessionModel->where('refresh_token', $refreshToken)
                ->where('is_active', 1)
                ->where('refresh_expires_at >', date('Y-m-d H:i:s'))
                ->first();

            if (!$session) {
                $this->clearAuthCookies();
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
                $this->clearAuthCookies();
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
                'expires_at' => date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY),
                'refresh_expires_at' => date('Y-m-d H:i:s', time() + self::REFRESH_EXPIRY),
                'last_activity_at' => date('Y-m-d H:i:s')
            ]);

            // 設定新的 cookies
            $this->setAuthCookies($newToken, $newRefreshToken);

            // 記錄 token 更新事件
            log_auth_event('token_refresh', $user['id']);

            $this->setCorsHeaders();

            return $this->respond([
                'success' => true,
                'data' => [
                    'expires_in' => self::TOKEN_EXPIRY
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
            $this->setCorsHeaders();

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

            // 移除敏感資料（保留 is_company_manager 和 user_type 等必要欄位）
            unset($user['password_hash'], $user['password_reset_token'], $user['login_attempts']);

            return $this->respond([
                'success' => true,
                'data' => $user, // 包含 is_company_manager, user_type 等所有使用者欄位
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
     * 使用者註冊
     * POST /api/auth/register
     */
    public function register()
    {
        // 初始化日誌
        $registrationLogModel = model('RegistrationLogModel');
        $logId = null;

        try {
            $data = $this->request->getJSON(true);

            // 記錄請求
            $logId = $registrationLogModel->logRequest(
                $data ?? [],
                $this->request->getIPAddress(),
                $this->request->getServer('HTTP_USER_AGENT') ?? ''
            );

            // 基本驗證規則
            $rules = [
                'account' => 'required|max_length[100]|is_unique[users.username]',
                'nickname' => 'required|max_length[100]',
                'password' => 'required|min_length[6]',
                'fullName' => 'required|max_length[100]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'phone' => 'required|max_length[20]',
                'accountType' => 'required|in_list[personal,business]'
            ];

            // 企業帳號額外驗證
            if (isset($data['accountType']) && $data['accountType'] === 'business') {
                $rules['businessName'] = 'required|max_length[255]';
                $rules['taxId'] = 'required|max_length[20]';
            }

            // 個人帳號需填入公司邀請碼
            if (isset($data['accountType']) && $data['accountType'] === 'personal') {
                $rules['companyInviteCode'] = 'required|max_length[50]';
            }

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                if ($logId) {
                    $registrationLogModel->markAsError($logId, 422, '驗證失敗', $errors);
                }
                return response_validation_error('驗證失敗', $errors);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $companyId = null;
            $companyModel = new \App\Models\CompanyModel();

            // 如果是企業帳號，先建立 company 記錄
            if ($data['accountType'] === 'business') {
                $companyData = [
                    'name' => $data['businessName'],
                    'tax_id' => $data['taxId'] ?? null,
                    'company_phone' => $data['businessPhone'] ?? null,
                    'max_renewal_count' => 1, // 預設可建立 1 個更新會
                    'max_issue_count' => 8,   // 預設最多 8 個議題
                    'invite_code' => strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)), // 自動產生初始邀請碼
                ];

                $companyId = $companyModel->insert($companyData);

                if (!$companyId) {
                    $db->transRollback();
                    $errors = $companyModel->errors();
                    $errorMessage = '企業資料建立失敗: ' . implode(', ', $errors);
                    if ($logId) {
                        $registrationLogModel->markAsError($logId, 500, $errorMessage, $errors);
                    }
                    return response_error($errorMessage, 500);
                }
            } else {
                // 個人帳號：驗證邀請碼
                $company = $companyModel->where('invite_code', $data['companyInviteCode'])
                    ->where('invite_code_active', 1)
                    ->first();
                if (!$company) {
                    $db->transRollback();
                    $errorMessage = '公司邀請碼無效或已停用';
                    if ($logId) {
                        $registrationLogModel->markAsError($logId, 400, $errorMessage);
                    }
                    return response_error($errorMessage, 400);
                }
                $companyId = $company['id'];
            }

            // 建立使用者記錄
            $userData = [
                'username' => $data['account'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'full_name' => $data['fullName'],
                'nickname' => $data['nickname'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'line_account' => $data['lineId'] ?? null,
                'position' => $data['jobTitle'] ?? null,
                'user_type' => $data['accountType'] === 'business' ? 'enterprise' : 'general',
                'is_company_manager' => $data['accountType'] === 'business' ? 1 : 0,
                'company_id' => $companyId,
                'company_invite_code' => $data['accountType'] === 'personal' ? $data['companyInviteCode'] : null,
                'urban_renewal_id' => null,
                'role' => 'member',
                // 狀態處理
                'approval_status' => $data['accountType'] === 'business' ? 'approved' : 'pending',
                'is_substantive' => $data['accountType'] === 'business' ? 1 : 0,
                'is_active' => $data['accountType'] === 'business' ? 1 : 0 // 個人帳號需待審核後啟用
            ];

            $userId = $this->userModel->insert($userData);

            if (!$userId) {
                $db->transRollback();
                $errors = $this->userModel->errors();
                $errorMessage = '使用者建立失敗: ' . implode(', ', $errors);
                if ($logId) {
                    $registrationLogModel->markAsError($logId, 500, $errorMessage, $errors);
                }
                return response_error($errorMessage, 500);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                if ($logId) {
                    $registrationLogModel->markAsError($logId, 500, '註冊失敗，請稍後再試');
                }
                return response_error('註冊失敗，請稍後再試', 500);
            }

            // 記錄成功
            if ($logId) {
                $registrationLogModel->markAsSuccess($logId, $userId, '註冊成功');
            }

            // 記錄註冊事件
            log_auth_event('register', $userId, $data['account'], null, [
                'user_type' => $userData['user_type'],
                'company_id' => $companyId,
                'approval_status' => $userData['approval_status']
            ]);

            $message = $data['accountType'] === 'business' ? '註冊成功' : '註冊成功，請等待公司審核';

            return $this->respond([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'username' => $data['account'],
                    'company_id' => $companyId,
                    'approval_status' => $userData['approval_status']
                ],
                'message' => $message
            ], 201);
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            if ($logId) {
                $registrationLogModel->markAsError($logId, 500, '註冊處理失敗: ' . $e->getMessage(), [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return response_error('註冊處理失敗: ' . $e->getMessage(), 500);
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

            $json = $this->request->getJSON(true);
            $email = $json['email'] ?? '';
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

            $json = $this->request->getJSON(true);
            $token = $json['token'] ?? '';
            $password = $json['password'] ?? '';

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
        $jwtConfig = config('JWT');

        $payload = [
            'iss' => $jwtConfig->issuer,
            'aud' => $jwtConfig->audience,
            'iat' => time(),
            'exp' => time() + $jwtConfig->expiry,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'user_type' => $user['user_type'] ?? 'general',
            'is_company_manager' => $user['is_company_manager'] ?? 0,
            'company_id' => $user['company_id'] ?? null,
            'urban_renewal_id' => $user['urban_renewal_id']
        ];

        return JWT::encode($payload, $jwtConfig->key, $jwtConfig->algorithm);
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

        // 先清除該使用者的舊 sessions (註解掉以支援多裝置/多視窗登入)
        // $sessionModel->where('user_id', $userId)->set(['is_active' => 0])->update();

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
     * 設定認證 Cookies
     */
    private function setAuthCookies($token, $refreshToken)
    {
        $secure = true; // HTTPS only
        $httponly = true;
        $samesite = 'Strict';
        $path = '/';

        // Auth token cookie (24 小時)
        setcookie(
            self::TOKEN_COOKIE_NAME,
            $token,
            [
                'expires' => time() + self::TOKEN_EXPIRY,
                'path' => $path,
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]
        );

        // Refresh token cookie (7 天)
        setcookie(
            self::REFRESH_COOKIE_NAME,
            $refreshToken,
            [
                'expires' => time() + self::REFRESH_EXPIRY,
                'path' => $path,
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]
        );
    }

    /**
     * 清除認證 Cookies
     */
    private function clearAuthCookies()
    {
        $path = '/';

        setcookie(self::TOKEN_COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        setcookie(self::REFRESH_COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => $path,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * 從 Cookie 取得 Token
     */
    private function getTokenFromCookie()
    {
        return $_COOKIE[self::TOKEN_COOKIE_NAME] ?? null;
    }

    /**
     * 從 Cookie 取得 Refresh Token
     */
    private function getRefreshTokenFromCookie()
    {
        return $_COOKIE[self::REFRESH_COOKIE_NAME] ?? null;
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
        // 優先從 cookie 取得 token
        $token = $this->getTokenFromCookie();
        if (!$token) {
            $token = $this->getTokenFromHeader(); // fallback
        }

        if (!$token) {
            return null;
        }

        try {
            $jwtConfig = config('JWT');
            $decoded = JWT::decode($token, new Key($jwtConfig->key, $jwtConfig->algorithm));

            return $this->userModel->find($decoded->user_id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
