<?php

namespace App\DTOs\Sellers;

use App\DTOs\BaseDTO;

class CreateSellerData extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email
    ) {}

    protected static function prepareData(array $data): array
    {
        return [
            'name' => trim($data['name'] ?? ''),
            'email' => strtolower(trim($data['email'] ?? ''))
        ];
    }
}
