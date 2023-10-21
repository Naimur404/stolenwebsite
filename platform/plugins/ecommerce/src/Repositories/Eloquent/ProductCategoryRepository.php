<?php

namespace Botble\Ecommerce\Repositories\Eloquent;

use Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class ProductCategoryRepository extends RepositoriesAbstract implements ProductCategoryInterface
{
    public function getCategories(array $param)
    {
        $param = array_merge([
            'active' => true,
            'order_by' => 'desc',
            'is_child' => null,
            'is_featured' => null,
            'num' => null,
        ], $param);

        $data = $this->model;

        if ($param['active']) {
            $data = $data->wherePublished();
        }

        if ($param['is_child'] !== null) {
            if ($param['is_child'] === true) {
                $data = $data->where('parent_id', '<>', 0);
            } else {
                $data = $data->whereIn('parent_id', [0, null]);
            }
        }

        if ($param['is_featured']) {
            $data = $data->where('is_featured', $param['is_featured']);
        }

        $data = $data->orderBy('order', $param['order_by']);

        if ($param['num'] !== null) {
            $data = $data->limit($param['num']);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getDataSiteMap()
    {
        $data = $this->model
            ->wherePublished()
            ->orderByDesc('created_at');

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getFeaturedCategories($limit)
    {
        $data = $this->model
            ->where('is_featured', true)
            ->wherePublished()
            ->select([
                'id',
                'name',
                'icon',
            ])
            ->with(['slugable'])
            ->orderBy('order')
            ->limit($limit);

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getAllCategories(bool $active = true)
    {
        $data = $this->model;
        if ($active) {
            $data = $data->wherePublished();
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getProductCategories(
        array $conditions = [],
        array $with = [],
        array $withCount = [],
        bool $parentOnly = false,
        array $select = [],
    ) {
        $data = $this->model;

        if (! empty($conditions)) {
            $data = $data->where($conditions);
        }

        if (! empty($with)) {
            $data = $data->with($with);
        }

        if (! empty($withCount)) {
            $data = $data->withCount($withCount);
        }

        if ($parentOnly) {
            $data = $data->where(function ($query) {
                $query
                    ->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            });
        }

        $data = $data
            ->orderBy('order')
            ->orderByDesc('created_at');

        if ($select) {
            $data = $data->select($select);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
