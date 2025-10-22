<?php

namespace App\Repositories\Contracts\Sales;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySeller(int $sellerId, ?string $date = null): Collection;
    public function findAllBySeller(int $sellerId, ?string $date = null, int $perPage = 20, int $page = 1, array $relations = []): LengthAwarePaginator;
    public function getSalesByDateRange(\DateTime $startDate, \DateTime $endDate): Collection;
    public function getSellerSalesByDateRange(int $sellerId, \DateTime $startDate, \DateTime $endDate): Collection;

    public function sumAmount(): float;
    public function sumCommissions(): float;
    public function countByDate(Carbon $date): int;
    public function sumAmountByDate(Carbon $date): float;
    public function countFromDate(Carbon $date): int;
    public function sumAmountFromDate(Carbon $date): float;
    public function countBetweenDates(Carbon $startDate, Carbon $endDate): int;
    public function sumAmountBetweenDates(Carbon $startDate, Carbon $endDate): float;
}
