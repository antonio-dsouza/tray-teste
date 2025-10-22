<?php

namespace App\DTOs\Sales;

use App\DTOs\BaseDTO;
use Carbon\Carbon;

class CreateSaleData extends BaseDTO
{
    public function __construct(
        public readonly int $seller_id,
        public readonly float $amount,
        public readonly Carbon $sold_at
    ) {}

    protected static function prepareData(array $data): array
    {
        return [
            'seller_id' => (int) ($data['seller_id'] ?? 0),
            'amount' => (float) ($data['amount'] ?? 0),
            'sold_at' => Carbon::parse($data['sold_at'] ?? now())
        ];
    }
}
