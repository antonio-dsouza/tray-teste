<?php

namespace App\Services\Sellers\Contracts;

use App\DTOs\Sellers\CreateSellerData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface SellerServiceInterface
{
    public function create(CreateSellerData $data): Model;
    public function findAll(int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function resendCommission(int $sellerId, ?string $date = null): array;
    public function runDailyMails(?string $date = null): array;
}
