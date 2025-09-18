<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    /**
     * JWT Secret Key
     * This should be a long, random string. In production, store this in environment variables.
     */
    public string $key = 'your-super-secret-jwt-key-change-this-in-production';

    /**
     * JWT Algorithm
     */
    public string $algorithm = 'HS256';

    /**
     * JWT Issuer
     */
    public string $issuer = 'urban-renewal-system';

    /**
     * JWT Audience
     */
    public string $audience = 'urban-renewal-users';

    /**
     * JWT Expiry time in seconds (24 hours)
     */
    public int $expiry = 86400;

    /**
     * JWT Refresh token expiry time in seconds (7 days)
     */
    public int $refreshExpiry = 604800;

    /**
     * JWT Not Before time in seconds
     */
    public int $notBefore = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Override with environment variables if available
        if ($envKey = env('JWT_SECRET_KEY')) {
            $this->key = $envKey;
        }

        if ($envIssuer = env('JWT_ISSUER')) {
            $this->issuer = $envIssuer;
        }

        if ($envAudience = env('JWT_AUDIENCE')) {
            $this->audience = $envAudience;
        }

        if ($envExpiry = env('JWT_EXPIRY')) {
            $this->expiry = (int) $envExpiry;
        }

        if ($envRefreshExpiry = env('JWT_REFRESH_EXPIRY')) {
            $this->refreshExpiry = (int) $envRefreshExpiry;
        }
    }
}