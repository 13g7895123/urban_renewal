<?php

namespace App\Exceptions;

/**
 * 資源未找到例外
 */
class NotFoundException extends \Exception
{
    protected $code = 404;
}
