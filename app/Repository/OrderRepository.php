<?php

namespace App\Repository;

use App\Models\Order;
use App\Filters\OrderFilter;
use App\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */
    public function allForUsers(int $count, bool $paginate,array $relations): object
    {
        // $filter = new OrderFilter(Request());
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
     * @param  array $relations
     * @return object
     */
    public function find($model_id , array $relations=[]): ?object
    {
        return $this->model->with($relations)->findOrFail($model_id);
    }

    /**
     * @param int $model_id
     * @param  array $relations
     * @return object
     */
    public function findByUserId($user_id): ?object
    {
        return $this->model->where('user_id' , $user_id)->where('is_approved' , '0')->get();
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

    public function getDeliveryOrders(int $count, bool $paginate,array $relations) :object
    {
        if ($paginate == true) {
            return $this->model->with($relations)->where('user_id' , auth()->user()->id)->paginate($count);
        }
        return $this->model->with($relations)->where('user_id' , auth()->user()->id)->get();
    }
}
