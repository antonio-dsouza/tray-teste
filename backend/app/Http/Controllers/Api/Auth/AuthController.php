<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTOs\Auth\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginData = LoginData::fromArray($request->validated());
            $result = $this->authService->login($loginData->toArray());

            return $this->successResponse($result, 'Login realizado com sucesso');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 401);
        }
    }

    public function user(): JsonResponse
    {
        try {
            $result = $this->authService->user();

            return $this->successResponse($result, 'Dados do usuário recuperados com sucesso');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 401);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return $this->successResponse(null, 'Logout realizado com sucesso');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
}
