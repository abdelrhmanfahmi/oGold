<?php

namespace App\Repository;

use App\Models\AddressBook;
use App\Repository\Interfaces\AddressBookRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AddressBookRepository implements AddressBookRepositoryInterface{

    private $model;

    /**
     * SettingRepository constructor.
     *
     * @param AddressBook $model
     */
    public function __construct(AddressBook $model)
    {
        $this->model = $model;
    }

    /**
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */

    public function all(int $count, bool $paginate): object
    {
        if ($paginate == true) {
            return $this->model->paginate($count);
        }
        return $this->model->get();
    }

    /**
     *  @param int $count
     *  @param bool $paginate
     *  @param array $relations
     * @return object
     */
    public function allForUsers(int $count, bool $paginate): object
    {
        // $filter = new OrderFilter(Request());
        if ($paginate == true) {
            return $this->model->where('user_id' , Auth::id())->paginate($count);
        }
        return $this->model->where('user_id' , Auth::id())->get();
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
     * @param AddressBook  $model
     * @param array $attributes
     * @return object
     */
    public function update(AddressBook $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }
}
