<?php

namespace App\Repositories\Implementations\Eloquent\Sales;

use App\Models\Sale;
use App\Repositories\Contracts\Sales\SaleRepositoryInterface;
use App\Repositories\Implementations\Eloquent\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    public function findBySeller(int $sellerId, ?string $date = null): Collection
    {
        $query = $this->model->where('seller_id', $sellerId);

        if ($date) {
            $parsedDate = Carbon::parse($date);
            $query->whereDate('created_at', $parsedDate->toDateString());
        }

        return $query->get();
    }

    public function findAllBySeller(int $sellerId, ?string $date = null, int $perPage = 20, int $page = 1, array $relations = []): LengthAwarePaginator
    {
        $query = $this->model->where('seller_id', $sellerId);

        if (!empty($relations)) $query->with($relations);

        if ($date) {
            $parsedDate = Carbon::parse($date);
            $query->whereDate('created_at', $parsedDate->toDateString());
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSalesByDateRange(\DateTime $startDate, \DateTime $endDate): Collection
    {
        return $this->model
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->with(['seller'])
            ->get();
    }

    public function getSellerSalesByDateRange(int $sellerId, \DateTime $startDate, \DateTime $endDate): Collection
    {
        return $this->model
            ->where('seller_id', $sellerId)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->with(['seller'])
            ->get();
    }

    public function sumAmount(): float
    {
        return (float) $this->model->sum('amount');
    }

    public function sumCommissions(): float
    {
        return (float) $this->model->sum('commission_amount');
    }

    public function countByDate(Carbon $date): int
    {
        return $this->model->whereDate('sold_at', $date)->count();
    }

    public function sumAmountByDate(Carbon $date): float
    {
        return (float) $this->model->whereDate('sold_at', $date)->sum('amount');
    }

    public function countFromDate(Carbon $date): int
    {
        return $this->model->where('sold_at', '>=', $date)->count();
    }

    public function sumAmountFromDate(Carbon $date): float
    {
        return (float) $this->model->where('sold_at', '>=', $date)->sum('amount');
    }

    public function countBetweenDates(Carbon $startDate, Carbon $endDate): int
    {
        return $this->model->whereBetween('sold_at', [$startDate, $endDate])->count();
    }

    public function sumAmountBetweenDates(Carbon $startDate, Carbon $endDate): float
    {
        return (float) $this->model->whereBetween('sold_at', [$startDate, $endDate])->sum('amount');
    }
}
