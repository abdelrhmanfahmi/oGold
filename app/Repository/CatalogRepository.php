<?php

namespace App\Repository;

use App\Models\Catalog;
use App\Repository\Interfaces\CatalogRepositoryInterface;
use Illuminate\Support\Collection;

class CatalogRepository implements CatalogRepositoryInterface
{
    private $model;

    /**
     * CatalogRepository constructor.
     *
     * @param Catalog $model
     */
    public function __construct(Catalog $model)
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
        if ($paginate == true) {
            return $this->model->with($relations)->paginate($count);
        }
        return $this->model->with($relations)->get();
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
     * @param int $model_id
     * @return object
     */
    public function findByUUID($uuid , array $relations=[]): ?object
    {
        return $this->model->with($relations)->where('uuid' , $uuid)->first();
    }

    /**
     * @param Catalog  $model
     * @param array $attributes
     * @return object
     */
    public function update(Catalog $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}
