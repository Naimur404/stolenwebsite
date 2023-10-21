<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Repositories\Interfaces\BrandInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository extends RepositoriesAbstract implements BrandInterface
{
    public function getAll(array $condition = []): Collection
    {
        $data = $this->model
            ->where($condition)
            ->orderByDesc('is_featured')
            ->orderBy('name', 'ASC');

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
