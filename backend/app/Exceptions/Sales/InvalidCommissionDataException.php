<?php

namespace App\Exceptions\Sales;

use Exception;

class InvalidCommissionDataException extends Exception
{
    public function __construct(string $message = 'Os dados de comissão são inválidos.')
    {
        parent::__construct($message, 422);
    }
}
