<?php

namespace App\Exceptions;

/**
 * 資料驗證例外
 */
class ValidationException extends \Exception
{
    protected $code = 422;
    
    private array $errors = [];

    public function __construct(string $message = '驗證失敗', array $errors = [])
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
