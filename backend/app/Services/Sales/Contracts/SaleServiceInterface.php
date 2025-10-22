<?php

namespace App\Services\Sales\Contracts;

use App\DTOs\Sales\CreateSaleData;
use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;

interface SaleServiceInterface
{
    public function create(CreateSaleData $data): Sale;
    public function findAll(int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function findAllBySeller(int $sellerId, ?string $date = null, int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function getDailySummaryForSeller(int $sellerId, string $date): array;
    public function resendSaleCommission(int $saleId): void;
}
