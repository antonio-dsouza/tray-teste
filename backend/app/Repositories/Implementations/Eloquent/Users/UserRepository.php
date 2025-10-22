<?php

namespace App\Repositories\Implementations\Eloquent\Users;

use App\Models\User;
use App\Repositories\Contracts\Users\UserRepositoryInterface;
use App\Repositories\Implementations\Eloquent\BaseRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getPermissions(Authenticatable $user): Collection
    {
        if ($user instanceof User) {
            return $user->getPermissionsViaRoles();
        }
        return collect();
    }

    public function attempt(array $credentials): mixed
    {
        return JWTAuth::attempt($credentials);
    }

    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return true;
    }

    public function user(): ?Model
    {
        return JWTAuth::user();
    }

    public function getTTL(): int
    {
        return JWTAuth::factory()->getTTL();
    }

    public function parseToken(): ?Model
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return $user;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function validateToken(string $token): bool
    {
        try {
            if (substr_count($token, '.') !== 2) {
                return false;
            }

            $segments = explode('.', $token);
            foreach ($segments as $index => $segment) {
                if (!$this->isValidBase64Url($segment)) {
                    return false;
                }
            }

            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();
            return $user !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function isValidBase64Url(string $data): bool
    {
        if (!preg_match('/^[A-Za-z0-9_-]*$/', $data)) {
            return false;
        }

        if (empty($data)) {
            return false;
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded !== false;
    }
}
