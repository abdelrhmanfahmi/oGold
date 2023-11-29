<?php

namespace App\Repository;

use App\Models\Faq;
use App\Repository\Interfaces\FaqRepositoryInterface;
use Illuminate\Support\Collection;

class FaqRepository implements FaqRepositoryInterface
{
    private $model;

    /**
     * SettingRepository constructor.
     *
     * @param Faq $model
     */
    public function __construct(Faq $model)
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
     * @param Faq  $model
     * @param array $attributes
     * @return object
     */
    public function update(Faq $model, array $attributes): object
    {
        $model->update($attributes);
        return $model;
    }

    public function delete($model_id)
    {
        return $this->model->destroy($model_id);
    }
}