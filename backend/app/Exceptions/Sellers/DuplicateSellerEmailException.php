<?php

namespace App\Exceptions\Sellers;

use Exception;

class DuplicateSellerEmailException extends Exception
{
    public function __construct(string $email)
    {
        parent::__construct("Vendedor com email '{$email}' já existe.", 409);
    }
}
