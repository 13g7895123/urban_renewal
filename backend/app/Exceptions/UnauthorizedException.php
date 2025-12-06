<?php

namespace App\Exceptions;

/**
 * 未授權例外
 */
class UnauthorizedException extends \Exception
{
    protected $code = 401;
}
