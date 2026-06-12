<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;
    public function find(int|string $id, array $columns = ['*']): ?Model;
    public function findOrFail(int|string $id): Model;
    public function findByColumn(string $column, mixed $value, array $columns = ['*']): ?Model;
    public function create(array $data): Model;
    public function update(int|string $id, array $data): bool;
    public function delete(int|string $id): bool;
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}
