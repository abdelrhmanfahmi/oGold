<?php

namespace App\Repository\Interfaces;

use App\Models\Deposit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface DepositRepositoryInterface
{

    /**
     * @param int $count
     * @param bool $paginate
     * @param array $relations
     * @return object
     */
    public function all(int $count, bool $paginate,array $relations);

    /**
     * @param int $model_id
     * @return object
     */
    public function find(int $model_id): ?object;

    /**
     * @param array $attributes
     * @return object
     */
    public function create(array $attributes): ?object;

    /**
     * @param Deposit  $model
     * @param array $attributes
     * @return object
     */
    public function update(Deposit $model, array $attribute): object;

    /**
     * @param int $model_id
     * @return int
     */
    public function delete($mode_id);

}
