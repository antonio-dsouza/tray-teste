<?php

namespace App\Services\Auth\Contracts;

interface AuthServiceInterface
{
    public function login(array $credentials): array;
    public function user(): array;
    public function logout(): bool;
}
