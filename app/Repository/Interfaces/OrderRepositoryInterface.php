<?php

namespace App\Repository\Interfaces;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface OrderRepositoryInterface
{
    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function all(int $count, bool $paginate , array $relations);

    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function allForUsers(int $count, bool $paginate , array $relations);

    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function getDataByOrdersDate(int $count, bool $paginate, array $relations);

    /**
     * * @param date $date
     *  * @param array $relations
     * @return object
     */
    public function getOrdersPerSpecificDate($date,$relations);

    /**
     * * @param date $date
     * @return object
     */
    public function getOrdersIdsByDate($date);
    /**
     * @param int $model_id
     * @param  array $relations
     * @return object
     */
    public function find(int $model_id , array $relations): ?object;

    /**
     * @param int $user_id
     * @return object
     */
    public function findByUserId(int $user_id): ?object;

    /**
     * @param array $attributes
     * @return object
     */
    public function create(array $attributes): ?object;

    /**
     * @param Product  $model
     * @param array $attributes
     * @return object
     */
    public function update(Order $model, array $attribute): object;

    /**
     * @param int $model_id
     * @return int
     */
    public function delete($mode_id);

    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function getDeliveryOrders(int $count, bool $paginate , array $relations);
}
