<?php

namespace App\Repositories\Contracts\Users;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function getPermissions(Authenticatable $user): Collection;
    public function attempt(array $credentials): mixed;
    public function logout(): bool;
    public function user(): ?Model;
    public function getTTL(): int;
    public function parseToken(): ?Model;
    public function validateToken(string $token): bool;
}
