<?php

namespace App\Repository;

use App\Models\BankDetails;
use App\Repository\Interfaces\BankRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class BankRepository implements BankRepositoryInterface
{
    private $model;

    /**
     * SettingRepository constructor.
     *
     * @param BankDetails $model
     */
    public function __construct(BankDetails $model)
    {
        $this->model = $model;
    }

    /**
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */

    public function all(int $count, bool $paginate, array $relations): object
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
     * @return object
     */
    public function find($model_id , array $relations=[]): ?object
    {
        return $this->model->with($relations)->findOrFail($model_id);
    }

    /**
     * @param Faq  $model
     * @param array $attributes
     * @return object
     */
    public function update(BankDetails $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}
