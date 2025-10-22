<?php

namespace App\Exceptions\Sales;

use Exception;

class InvalidSaleAmountException extends Exception
{
    protected $message = 'O valor da venda deve ser maior que zero';

    public function __construct(float $amount = null)
    {
        if ($amount !== null) {
            $this->message = "O valor da venda deve ser maior que zero. Valor fornecido: {$amount}";
        }

        parent::__construct($this->message);
    }
}
