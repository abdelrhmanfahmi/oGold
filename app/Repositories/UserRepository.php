<?php

namespace App\Repositories;

use App\Models\User;
use App\Filters\UserFilter;
use App\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    private $model;

    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
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
        $filter = new UserFilter(Request());
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
    public function find($model_id): ?object
    {
        return $this->model->find($model_id);
    }

    /**
     * @param User  $model
     * @param array $attributes
     * @return object
     */
    public function update(User $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }

}