<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Contracts\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardServiceInterface $dashboardService
    ) {}

    public function stats(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getAllStats();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas do dashboard recuperadas com sucesso',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
}
