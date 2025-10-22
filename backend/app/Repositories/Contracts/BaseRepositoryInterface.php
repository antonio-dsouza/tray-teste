<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function findAll(int $perPage = 20, int $page = 1, array $relations = []): LengthAwarePaginator;
    public function findById(int $id, array $relations = []): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): ?Model;
    public function delete(int $id): bool;
    public function count(): int;
}
