<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\Users\UserRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Token de autenticação não fornecido',
                'message' => 'É necessário fornecer um token Bearer válido no cabeçalho Authorization.'
            ], 401);
        }

        if (!$this->userRepository->validateToken($token)) {
            return response()->json([
                'error' => 'Token de autenticação inválido ou expirado',
                'message' => 'O token fornecido é inválido, expirado ou foi revogado. Faça login novamente.'
            ], 401);
        }

        $user = $this->userRepository->parseToken();

        if (!$user) {
            return response()->json([
                'error' => 'Usuário não encontrado',
                'message' => 'Não foi possível identificar o usuário a partir do token fornecido.'
            ], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
