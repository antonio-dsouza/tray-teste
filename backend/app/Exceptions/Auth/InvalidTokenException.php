<?php

namespace App\Exceptions\Auth;

use Exception;

class InvalidTokenException extends Exception
{
    public function __construct(string $message = 'Token inválido')
    {
        parent::__construct($message, 401);
    }
}
