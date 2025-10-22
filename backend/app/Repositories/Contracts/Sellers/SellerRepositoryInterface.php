<?php

namespace App\Repositories\Contracts\Sellers;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface SellerRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?Model;
    public function getAllIds(): array;
    public function getTopSellersBySalesAmount(int $limit = 5): Collection;
}
