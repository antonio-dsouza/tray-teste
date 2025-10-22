<?php

namespace App\Exceptions\Auth;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Credenciais inválidas')
    {
        parent::__construct($message, 401);
    }
}
