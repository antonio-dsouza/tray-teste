<?php

namespace App\Repositories\Implementations\Eloquent\Sellers;

use App\Models\Seller;
use App\Repositories\Contracts\Sellers\SellerRepositoryInterface;
use App\Repositories\Implementations\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class SellerRepository extends BaseRepository implements SellerRepositoryInterface
{
    public function __construct(Seller $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?Seller
    {
        return $this->model->where('email', $email)->first();
    }

    public function getAllIds(): array
    {
        return $this->model->pluck('id')->toArray();
    }

    public function getTopSellersBySalesAmount(int $limit = 5): Collection
    {
        return $this->model
            ->withSum('sales', 'amount')
            ->withSum('sales', 'commission_amount')
            ->withCount('sales')
            ->orderByDesc('sales_sum_amount')
            ->limit($limit)
            ->get()
            ->map(function ($seller) {
                $seller->total_commission = $seller->sales_sum_commission_amount ?? 0;
                return $seller;
            });
    }
}
