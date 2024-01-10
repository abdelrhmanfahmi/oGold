<?php

namespace App\Repository;

use App\Models\Order;
use App\Filters\OrderFilter;
use App\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            return $this->model->with($relations)->filter($filter)->orderBy('id' , 'DESC')->paginate($count);
        }
        return $this->model->with($relations)->filter($filter)->orderBy('id' , 'DESC')->get();
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
            return $this->model->with($relations)->where('user_id' , Auth::id())->orderBy('id' , 'DESC')->paginate($count);
        }
        return $this->model->with($relations)->where('user_id' , Auth::id())->orderBy('id' , 'DESC')->get();
    }

    /**
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */
    public function getDataByOrdersDate(int $count, bool $paginate,array $relations): object
    {
        if ($paginate == true) {
            $ordersByDate = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('sum(total) as Total_Gram'),
            )->groupBy('date')
            ->orderBy('date','desc')
            ->paginate($count);
        }else{
            $ordersByDate = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('sum(total) as Total_Gram'),
            )->groupBy('date')
            ->orderBy('date','desc')
            ->get();
        }
        return $ordersByDate;
    }


    public function getOrdersPerSpecificDate($date,$relations)
    {
        return $this->model->with($relations)->whereDate('created_at' , $date)->get();
    }

    public function getOrdersIdsByDate($date)
    {
        return $this->model->whereDate('created_at' , $date)->where('status' , 'pending')->pluck('id')->toArray();
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
        return $this->model->where('user_id' , $user_id)->where('status' , 'pending')->get();
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
