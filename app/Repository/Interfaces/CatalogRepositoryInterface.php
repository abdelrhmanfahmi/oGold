<?php

namespace App\Repository\Interfaces;

use App\Models\Catalog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface CatalogRepositoryInterface
{
    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function all(int $count, bool $paginate , array $relations);

    /**
     * @param int $model_id
     * @return object
     */
    public function find(int $model_id , array $relations): ?object;

    /**
     * @param int $model_id
     * @return object
     */
    public function findByUUID(int $model_id , array $relations): ?object;

    /**
     * @param array $attributes
     * @return object
     */
    public function create(array $attributes): ?object;

    /**
     * @param Catalog  $model
     * @param array $attributes
     * @return object
     */
    public function update(Catalog $model, array $attribute): object;

    /**
     * @param int $model_id
     * @return int
     */
    public function delete($mode_id);
}
