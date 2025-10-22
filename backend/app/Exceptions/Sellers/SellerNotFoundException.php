<?php

namespace App\Exceptions\Sellers;

use Exception;

class SellerNotFoundException extends Exception
{
    public function __construct(int $sellerId)
    {
        parent::__construct("Vendedor com ID {$sellerId} não encontrado.", 404);
    }
}
