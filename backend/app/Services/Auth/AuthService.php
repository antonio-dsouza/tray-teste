<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\InvalidTokenException;
use App\Models\User;
use App\Repositories\Contracts\Users\UserRepositoryInterface;
use App\Services\Auth\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function login(array $credentials): array
    {
        if (!$token = $this->userRepository->attempt($credentials)) {
            throw new InvalidCredentialsException();
        }

        $user = Auth::user();

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->userRepository->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user instanceof User ? $user->getRoleNames()->toArray() : [],
                'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->toArray() : [],
            ]
        ];
    }

    public function user(): array
    {
        $user = $this->userRepository->user();

        if (!$user) {
            throw new InvalidTokenException();
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user instanceof User ? $user->getRoleNames()->toArray() : [],
                'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->toArray() : [],
            ]
        ];
    }

    public function logout(): bool
    {
        return $this->userRepository->logout();
    }
}
