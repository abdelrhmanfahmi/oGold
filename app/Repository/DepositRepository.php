<?php

namespace App\Repository;

use App\Models\Deposit;
use App\Repository\Interfaces\DepositRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DepositRepository implements DepositRepositoryInterface
{
    private $model;

    /**
     * SettingRepository constructor.
     *
     * @param Deposit $model
     */
    public function __construct(Deposit $model)
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
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */

     public function allForUsers(int $count, bool $paginate,array $relations): object
     {
         if ($paginate == true) {
             return $this->model->with($relations)->where('user_id' , Auth::id())->paginate($count);
         }
         return $this->model->with($relations)->where('user_id' , Auth::id())->get();
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
        return $this->model->findOrFail($model_id);
    }

    /**
     * @param Deposit  $model
     * @param array $attributes
     * @return object
     */
    public function update(Deposit $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}
