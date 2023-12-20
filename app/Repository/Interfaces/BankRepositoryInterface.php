<?php

namespace App\Repository\Interfaces;

use App\Models\BankDetails;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BankRepositoryInterface
{
    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function all(int $count, bool $paginate, array $relations);

    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function allForUsers(int $count, bool $paginate, array $relations);

    /**
     * @param int $model_id
     * * @param array $relations
     * @return object
     */
    public function find(int $model_id , array $relations=[]): ?object;

    /**
     * @param array $attributes
     * @return object
     */
    public function create(array $attributes): ?object;

    /**
     * @param BankDetails  $model
     * @param array $attributes
     * @return object
     */
    public function update(BankDetails $model, array $attribute): object;

    /**
     * @param int $model_id
     * @return int
     */
    public function delete($mode_id);
}
