<?php

namespace App\Repository;

use App\Filters\ProductFilter;
use App\Models\Product;
use App\Repository\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    private $model;

    /**
     * ProductRepository constructor.
     *
     * @param Product $model
     */
    public function __construct(Product $model)
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
        $filter = new ProductFilter(Request());
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
     * @return object
     */
    public function find($model_id , array $relations=[]): ?object
    {
        return $this->model->with($relations)->findOrFail($model_id);
    }

    /**
     * @param Product  $model
     * @param array $attributes
     * @return object
     */
    public function update(Product $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}