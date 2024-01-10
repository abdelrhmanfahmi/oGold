<?php

namespace App\Repository\Interfaces;

use App\Models\AddressBook;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AddressBookRepositoryInterface {

    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function all(int $count, bool $paginate);

    /**
     * @param int $count
     * @param bool $paginate
     * * @param array $relations
     * @return object
     */
    public function allForUsers(int $count, bool $paginate);

    /**
     * @param int $model_id
     * @return object
     */
    public function find(int $model_id): ?object;

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
    public function update(AddressBook $model, array $attribute): object;
}
