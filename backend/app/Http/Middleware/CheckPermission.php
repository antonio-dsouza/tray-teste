<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return response()->json([
                'error' => 'Usuário não autenticado',
                'message' => 'É necessário estar autenticado para acessar este recurso.'
            ], 401);
        }

        if (!$request->user()->hasPermissionTo($permission)) {
            return response()->json([
                'error' => 'Permissões insuficientes',
                'message' => 'Você não tem permissão para realizar esta ação.',
                'details' => [
                    'required_permission' => $permission,
                    'user_roles' => $request->user()->getRoleNames(),
                    'user_name' => $request->user()->name,
                ]
            ], 403);
        }

        return $next($request);
    }
}
