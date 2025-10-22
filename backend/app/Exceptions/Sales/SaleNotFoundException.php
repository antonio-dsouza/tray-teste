<?php

namespace App\Exceptions\Sales;

use Exception;

class SaleNotFoundException extends Exception
{
    public function __construct(int $saleId)
    {
        parent::__construct("Venda com ID {$saleId} não encontrada");
    }
}
