<?php

namespace App\Exceptions;

/**
 * 權限不足例外
 */
class ForbiddenException extends \Exception
{
    protected $code = 403;
}
