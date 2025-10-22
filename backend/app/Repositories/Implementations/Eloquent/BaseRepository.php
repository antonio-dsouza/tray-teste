<?php

namespace App\Repositories\Implementations\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findAll(int $perPage = 20, $page = 1, array $relations = []): LengthAwarePaginator
    {
        $query = $this->model->orderBy('id', 'desc');

        if (!empty($relations)) $query = $query->with($relations);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $id, array $relations = []): ?Model
    {
        $query = $this->model;

        if (!empty($relations)) $query = $query->with($relations);

        return $query->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Model
    {
        $model = $this->findById($id);

        if (!$model) return null;

        $model->update($data);

        return $model->fresh();
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);

        if (!$model) return false;

        return $model->delete();
    }

    public function count(): int
    {
        return $this->model->count();
    }
}
