<?php

namespace App\Repository\Interfaces;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface SettingRepositoryInterface
{
    /**
     * @param int $count
     * @param bool $paginate
     * @return object
     */
    public function all(int $count, bool $paginate);

    /**
     * @param int $model_id
     * @return object
     */
    public function find(int $model_id): ?object;

    /**
     * @return object
     */
    public function findByKey(): ?object;

    /**
     * @param array $attributes
     * @return object
     */
    public function create(array $attributes): ?object;

    /**
     * @param Setting  $model
     * @param array $attributes
     * @return object
     */
    public function update(Setting $model, array $attribute): object;

    /**
     * @param int $model_id
     * @return int
     */
    public function delete($mode_id);
}
