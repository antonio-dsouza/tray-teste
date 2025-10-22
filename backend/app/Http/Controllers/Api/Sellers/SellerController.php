<?php

namespace App\Http\Controllers\Api\Sellers;

use App\DTOs\Sellers\CreateSellerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sellers\StoreSellerRequest;
use App\Http\Resources\Sellers\SellerResource;
use App\Services\Sellers\Contracts\SellerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        private readonly SellerServiceInterface $sellerService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            $sellers = $this->sellerService->findAll($perPage, $page);

            return $this->paginatedResponse(
                SellerResource::collection($sellers),
                'Vendedores recuperados com sucesso'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function store(StoreSellerRequest $request): JsonResponse
    {
        try {
            $data = CreateSellerData::fromArray($request->validated());
            $seller = $this->sellerService->create($data);

            return $this->createdResponse(
                new SellerResource($seller),
                'Vendedor criado com sucesso'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function resendSellerCommission(int $sellerId, Request $request): JsonResponse
    {
        try {
            $date = $request->input('date');
            $result = $this->sellerService->resendCommission($sellerId, $date);

            return $this->successResponse($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function runDailyMails(): JsonResponse
    {
        try {
            $result = $this->sellerService->runDailyMails();

            return $this->successResponse($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
}
