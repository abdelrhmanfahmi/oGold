<?php

namespace App\Repository;

use App\Models\Delivery;
use App\Filters\DeliveryFilter;
use App\Repository\Interfaces\DeliveryRepositoryInterface;
use Illuminate\Support\Collection;

class DeliveryRepository implements DeliveryRepositoryInterface
{
    private $model;

    /**
     * DeliveryRepository constructor.
     *
     * @param Delivery $model
     */
    public function __construct(Delivery $model)
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
        $filter = new DeliveryFilter(Request());
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
        return $this->model->with($relations)->findOrFail($model_id);
    }

    /**
     * @param Delivery  $model
     * @param array $attributes
     * @return object
     */
    public function update(Delivery $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}