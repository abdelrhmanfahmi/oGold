<?php

namespace App\Repository;

use App\Models\Order;
use App\Filters\OrderFilter;
use App\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    private $model;

    /**
     * OrderRepository constructor.
     *
     * @param Order $model
     */
    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */
    public function all(int $count, bool $paginate,array $relations): object
    {
        $filter = new OrderFilter(Request());
        if ($paginate == true) {
            return $this->model->with($relations)->filter($filter)->paginate($count);
        }
        return $this->model->with($relations)->filter($filter)->get();
    }

    /**
     * @param array $attributes
     * @return object
     */
    public function create(array $attributes): object
    {
        return $this->model->create($attributes);
    }
    /**
     * @param int $model_id
     * @param  array $relations
     * @return object
     */
    public function find($model_id , array $relations=[]): ?object
    {
        return $this->model->with($relations)->find($model_id);
    }

    /**
     * @param Product  $model
     * @param array $attributes
     * @return object
     */
    public function update(Order $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}