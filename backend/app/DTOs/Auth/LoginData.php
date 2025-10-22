<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseDTO;

class LoginData extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    protected static function prepareData(array $data): array
    {
        return [
            'email' => trim($data['email'] ?? ''),
            'password' => $data['password'] ?? ''
        ];
    }
}
