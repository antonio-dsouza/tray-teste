<?php

namespace App\Http\Controllers\Api\Sales;

use App\DTOs\Sales\CreateSaleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSaleRequest;
use App\Http\Resources\Sales\SaleResource;
use App\Services\Sales\Contracts\SaleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleServiceInterface $saleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            $sales = $this->saleService->findAll($perPage, $page);

            return $this->paginatedResponse(
                SaleResource::collection($sales),
                'Vendas recuperadas com sucesso'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            $data = CreateSaleData::fromArray($request->validated());
            $sale = $this->saleService->create($data);

            return $this->successResponse($sale, 'Venda criada com sucesso', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function bySeller(Request $request, int $sellerId): JsonResponse
    {
        try {
            $date = $request->input('date');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            $sales = $this->saleService->findAllBySeller($sellerId, $date, $perPage, $page);

            return $this->paginatedResponse(
                SaleResource::collection($sales),
                'Vendas do vendedor recuperadas com sucesso'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function resendSaleCommission(int $saleId): \Illuminate\Http\JsonResponse
    {
        try {
            $this->saleService->resendSaleCommission($saleId);

            return $this->successResponse(
                null,
                'Email de comissão da venda reenviado com sucesso'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
}
